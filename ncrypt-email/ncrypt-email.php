<?php
/*
Plugin Name: Ncrypt Email - Total Protection
Plugin URI: http://the-never-never-land.com/encrypt-email-address-wordpress-plugin/
Description: An email address encryption plugin to protect email addresses from email-harvesting robots. It uses mcrypt php library to encrypt and decrypt the emails and ajax to retrieve them.
Version: 2.3
Author: Efthyoulos Tsouderos
Author URI: http://the-never-never-land.com
License: See licensing folder
License URI: 
*/
/**
 *
 * @package ncrypt-email
 * @copyright 2015 Efthyvoulos Tsouderos
 */

/****** First of all Set cookie ******/
function e01t_set_cookie(){
    if( !isset($_COOKIE['e01t']) )
		setcookie('e01t', 18, time()+(60*60*1) , COOKIEPATH, COOKIE_DOMAIN, false );
}
add_action('init', 'e01t_set_cookie');

/****** Define the plugin version ******/
define( 'NCRYPT_EMAIL_VERSION', '2.3' );

/************************ xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx ************************/
/************************ Require the options page (options panel)  ************************/
require_once 'ncrypt-email-options-page_oop.php';

class NCrypt_Email {

// Reg Exp Constants
const EMAIL_REGEX = '{mailto:?(?:[-!#$%&*+\/=?^_`.{|}~\w\x80-\xFF]+|".*?")\@(?:[-a-z0-9\x80-\xFF]+(?:\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+|\[[\d.a-fA-F:]+\])(\?subject=.+?\s*.[^"\']+)?}xi';
const PTEXT_EMAIL = '{<[^>]+@(*SKIP)(*FAIL)|(?<=[^\w\d\+_.:-])(?:[-!#$%&*+\/=?^_`.{|}~\w\x80-\xFF]+|".*?")\@(?:[-a-z0-9\x80-\xFF]+(?:\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+|\[[\d.a-fA-F:]+\])(?!(?>[^<]*(?:<(?!\/?a\b)[^<]*)*)<\/a>)}i';
const LINKED_TEXT = '/[^>\s+](?:[-!#$%&*+\/=?^_`.{|}~\w\x80-\xFF]+|".*?")\@(?:[-a-z0-9\x80-\xFF]+(?:\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+|\[[\d.a-fA-F:]+\])(?=\s*<\/a>)/xi';

// Private variables used in the constructor for getting the options Object
// and the options variables
private $options_page;
private $e01t_options;


public function __construct() {

	/****** Create an options page object and get the option values ******/
	$this->options_page  = new Ncrypt_Email_Options();
	$this->e01t_options = $this->options_page->get_ncrypt_options();

	/********** On plugin activation and diactivation **********/
	register_activation_hook(__FILE__ ,   array($this->options_page, 'e01t_on_register')    );
	register_deactivation_hook(__FILE__ , array($this->options_page, 'e01t_delete_options') );

	/********** JavaScript & Ajax **********/
	add_action( 'wp_enqueue_scripts', 						array($this, 'e01t_set_javascript') 	  );
	add_action( 'wp_ajax_nopriv_e01t_ajax_decrypt_request', array($this, 'e01t_ajax_decrypt_request') ); // non log-in users only
	add_action( 'wp_ajax_e01t_ajax_decrypt_request',        array($this, 'e01t_ajax_decrypt_request') ); // log-in users only

	/********** Internationalization **********/
	add_action('init', array($this, 'e01t_myplugin_internationalization') );

	/********** Set up shortcode **********/
	add_shortcode('ncrypt_email', array($this, 'e01t_shortcode_callback') );

	/********** Add filters **********/
	$this->addFilters();
	// http://stackoverflow.com/questions/12794152/passing-class-method-as-a-call-back-function-in-wordpress
}

	public function e01t_myplugin_internationalization(){
	    load_plugin_textdomain('wp-email-address-encryption', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	public function e01t_set_javascript(){

		$option = $this->e01t_options;
		wp_enqueue_script( 'decrypt-scipt', plugins_url('javascript/ncrypt_js_min.js' , __FILE__), array(), NCRYPT_EMAIL_VERSION, true );
	    wp_enqueue_script( 'pointer-events-script', plugins_url('javascript/pointerevents.js' , __FILE__), array(), NCRYPT_EMAIL_VERSION, false );


		// See Professional WP Plugin Development pages 865 - 866 for an explanation on 
		// admin_url('/admin-ajax.php', $protocol) 
		$protocol = isset( $_SERVER["HTTPS"] ) ? 'https://' : 'http://';
		$params = array('ajaxURL' 	   => admin_url('/admin-ajax.php', $protocol ),
						'no_of_houses' => $option['ncrypt_answer'] , 
						'cookieString' => $option['ncrypt_cookies'] , 
						'linkedText'   => $option['ncrypt_linked_text'] ,
						'hovered'      => $option['ncrypt_hover'] ,
						'post_code'    => wp_create_nonce('my-special-string')
	 					);
		wp_localize_script('decrypt-scipt', 'MyScriptParams', $params);	
	}


	private function addFilters(){

		// Encrypt mailto emails
		if ($this->e01t_options['ncrypt_mailto']){
			foreach (array('the_content', 'the_excerpt', 'widget_text', 'comment_text', 'comment_excerpt', 'comment_post') as $filter) {
				add_filter($filter, array($this, 'e01t_encrypt_mailto_emails'), 1901); // execute first
				add_filter($filter, array($this, 'e01t_change_linkedtext_emails'), 1902); // execute second
			}
		}

		// Encrypt plaintext emails
		if ($this->e01t_options['ncrypt_plaintext']){
			foreach (array('the_content', 'the_excerpt', 'widget_text', 'comment_text', 'comment_excerpt', 'comment_post') as $filter) {
				add_filter($filter, array($this,'e01t_encrypt_plaintext_emails'), 1903); // execute third
			}
		}

	}

	/**
	 * Searches for linked email addresses in given $string that have this form
	 * the linked text is e.g. example_mail@server.net 
	 * changes the text to the one specified in the options panel(option three)
	 * if provided otherwise leave it as it is
	 * Is calling e01t_change_linkedtext_emails_callback function which in turn
	 * calls e01t_linked_text to do the swap (if any)
	 * 
	 * @param string $string Text with email addresses to encode
	 * @return string $string text with swapped text if provided
	 */
	public function e01t_change_linkedtext_emails($string){
		return preg_replace_callback(self::LINKED_TEXT,  array($this,'e01t_change_linkedtext_emails_callback'), $string);
	}

	private function e01t_change_linkedtext_emails_callback($matches){
				$str = $this->e01t_linked_text($matches[0]);
	 			return $str;
	}
	private function e01t_linked_text($string){
		$string = $this->e01t_options['ncrypt_linked_text'] ? $this->e01t_options['ncrypt_linked_text'] : $string;
		return $string;
	}

	/**
	 * Searches for linked email addresses in given $string and
	 * encrypts them with the help of e01t_encrypt_mailto_emails_callback function
	 * which in turn calls e01t_encrypt_email function
	 * 
	 * @param string $string Text with email addresses to encode
	 * @return string $string text with encrypted email addresses
	 */
	public function e01t_encrypt_mailto_emails($string) {
			return preg_replace_callback(self::EMAIL_REGEX, array($this, 'e01t_encrypt_mailto_emails_callback') , $string);
	} 
	private function e01t_encrypt_mailto_emails_callback($matches){
			$str = $this->e01t_encrypt_email($matches[0]);
			return $str;
	}

	/**
	 * Searches for plaintext email addresses in given $string and
	 * encrypts them with the help of e01t_encrypt_plaintext_emails_callback function
	 * which in turn calls e01t_assemble_plaintext_email function to construct the linked email 
	 * and then calls the e01t_encrypt_mailto_emails function to encrypt them
	 * 
	 * @param string $string Text with email addresses to encode
	 * @return string $string text with encrypted email addresses
	 */
	public function e01t_encrypt_plaintext_emails($string) {
			return preg_replace_callback(self::PTEXT_EMAIL, array($this, 'e01t_encrypt_plaintext_emails_callback') , $string);
	}

	private function e01t_encrypt_plaintext_emails_callback($matches){
			$str = $this->e01t_assemble_plaintext_emails($matches[0]);
			$str = $this->e01t_encrypt_mailto_emails($str);
			return $str;
	}

	/**
	 * This function is used for plaintext emails and for shortcodes
	 * encrypts them with the help of e01t_encrypt_plaintext_emails_callback function
	 * which in turn calls e01t_assemble_plaintext_email function to construct the linked email 
	 * and then calls the e01t_encrypt_mailto_emails function to encrypt them
	 * 
	 * @param string $email Text with email address to encode
	 * @param string $linked_text with (optional for e01t_encrypt_plaintext_emails and 
	 *			 not optional() for e01t_shortcode_callback functions) text to appear on the screen
	 * @param string $subject Text with an optional email subject
	 * @param string $title Text with an optional title name for the html attribute
	 * @param string $class Text with an optional html class name for the html attribute
	 * @param string $is_shortcode is a flag to let the function know if it was called
	 *		 	from e01t_shortcode_callback function OR from e01t_encrypt_plaintext_emails
	 * @return string $string text with assembled linked email address
	 */
	private function e01t_assemble_plaintext_emails($email, $linked_text = "", $subject = "", $title = "", $class= "", $is_shortcode = false) {
	  
	  $content = '<a ';

	  if($is_shortcode){
	  	if( !empty($class) ) $content .= "class='".$class."' ";
	  }else{ 
	  	$class = $this->e01t_options['ncrypt_class_name'];
	  	if ($class) $content .= "class='".$class."' ";
	  }

	  $content .= 'href="mailto:' . $email;
	  
	  if ($subject) $content .= "?subject=$subject";
	  
	  $content .= '"';

	  if ($title) $content .= " title=\"$title\""; 
	  
	  $content .= " target=\"_blank\"";

	  $content .= " rel=\"nofollow\"";

	  if($is_shortcode){ 
	  	$content .= ">". $linked_text. "</a>";
	  }else{
		  $linked_text = $this->e01t_options['ncrypt_linked_text'];
		  $linked_text = !empty($linked_text) ? $linked_text : $email;
		  $content .= ">". $linked_text. "</a>"; 
	  }

	  return $content; 
	}

	/* shortcode support */
	public function e01t_shortcode_callback($atts, $content) {
	  if (empty($atts['email'])) return;
	  if (empty($content)) return;

	  extract( shortcode_atts(array('email' => '',
	  								'content' => $content,
	                               	'subject' => '',
	                               	'title' => '',
	                               	'class' => '',
	                               	'is_shortcode' => true),
	                         $atts)
	  );

	  $mailto_email = $this->e01t_assemble_plaintext_emails($email,$content,$subject,$title,$class,$is_shortcode);
	  return $this->e01t_encrypt_mailto_emails($mailto_email);
	}


	/**
	 * Encrypts each email string provided by e01t_encrypt_mailto_emails function
	 *
	 * @param string $data Text with email address to encode
	 * @return string $output text with encprypted email address
	 */
	private function e01t_encrypt_email($data) {
		$password = get_option('ncrypt_key');
		$cipher = "rijndael-128";
		$mode = "ecb"; 
		$key = pack('H*', md5($password));

		$block = mcrypt_get_block_size($cipher, $mode);
	    $pad = $block - (strlen($data) % $block);
	    $data .= str_repeat(chr($pad), $pad);

		$data = mcrypt_encrypt($cipher,$key, $data, $mode);
		$base64_encoded_data = base64_encode($data);

		$output .= plugins_url('dcrypt-email.php?crypt=' , __FILE__);
		$output .= urlencode($base64_encoded_data);
		return esc_url($output);
	}

	public function e01t_ajax_decrypt_request(){
		
		if ( !wp_verify_nonce( $_REQUEST["post_code"], "my-special-string")) {
	      exit("No naughty business please");
		} 

		$answer = $this->e01t_options['ncrypt_answer'];
	
		if( isset($_REQUEST['links_array']) && ( isset($_REQUEST['no_of_houses']) && $_REQUEST['no_of_houses'] == $answer) ){
	 			// if cookies are disabled echo the message and exit
				if( !isset($_COOKIE['e01t']) ) { 
					$cookie_message = $this->e01t_options['ncrypt_cookies']; // We don't have to send the cookie message through ajax
					echo $cookie_message; 
					exit(); 
				}
				else{
					$the_array = explode(',' , $_REQUEST['links_array']);
					$html = "";
					for($i = 0; $i < sizeof($the_array); $i++){
						if( $i == sizeof($the_array) -1 )
							$html .= $this->e01t_decrypt_email($the_array[$i]);
						else 
							$html .= $this->e01t_decrypt_email($the_array[$i]) . ',';
					}
					echo $html; 
					exit(); // exit function and script
				}			
		}
		else
			exit();
		
	}

	private function e01t_decrypt_email($my_data) {
			$password = get_option('ncrypt_key');
			$key = pack('H*', md5($password));
			$cipher = "rijndael-128";
			$mode = "ecb";

			$my_data = base64_decode($my_data);
			$my_data = mcrypt_decrypt($cipher, $key, $my_data, $mode);

			$block = mcrypt_get_block_size($cipher, $mode);
			$pad = ord($my_data[($len = strlen($my_data)) - 1]);
			return substr($my_data, 0, strlen($my_data) - $pad);
	}

} // END of class NCrypt_Email 

new NCrypt_Email();