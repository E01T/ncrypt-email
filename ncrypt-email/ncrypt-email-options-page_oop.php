<?php 
class Ncrypt_Email_Options {

	private $options; 

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'e01t_email_encryption_config_page' ) ); 
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		$this->options = (array)get_option( 'ncrypt_email_options' );
	}

	public function e01t_email_encryption_config_page() {
		// add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function )
		if (function_exists('add_options_page')) {
		    add_options_page(__('Ncrypt Email' , 'wp-email-address-encryption'), 
		                     __('Ncrypt Email' , 'wp-email-address-encryption'), 
		                     'manage_options', 
		                     basename(__FILE__),
		                     array($this,'create_plugin_settings_page')); 
	  	}
	}

	function create_plugin_settings_page() { ?>

	<div class="wrap ncrypt-email" style="line-height:1; float:left; width:70%; font-size: 1.2em; border: 1px dashed #2ea2cc; padding:1em;">
	    <h2 style="font-size: 1.3em"><?php _e('The ultimate e-mail encryption tool will put a stop to spam.' , 'wp-email-address-encryption'); ?></h2>
	    <form method="post" action="options.php">
	    <?php
			// This prints out all hidden setting fields
			// settings_fields( $option_group )
			settings_fields( 'e01t_settings-group' );
			// do_settings_sections( $page )
			do_settings_sections( basename(__FILE__));
	    ?>
		<input type="submit" name="ncrypt_email_options[submit]" class="button-primary"	  value="<?php esc_attr_e('Save Settings &raquo;', 'wp-email-address-encryption'); ?>" />
     	<input type="submit" name="ncrypt_email_options[reset]"  class="button-secondary" value="<?php esc_attr_e('Reset Defaults &raquo;', 'wp-email-address-encryption');?>"/>
	    </form>
	</div>
	<!-- Explanation Section Bellow -->
	<div class="wrap ncrypt-email" style="line-height:1; float:left; width:70%; border: 1px dashed #2ea2cc; padding:1em; font-size:1.2em"><br>
  <h2 style="font-size: 1.3em"><?php _e('Ncrypt Email Useful Info' , 'wp-email-address-encryption'); ?></h2>
    <h3><?php _e('Which areas the plug-in covers?' , 'wp-email-address-encryption'); ?></h3>
    <p>the_content, the_excerpt, widget_text, comment_text, comment_excerpt, comment_post</p>
    <p>the_content<?php _e(' is the contents of posts and pages.' , 'wp-email-address-encryption'); ?></p>
    <p>the_excerpt<?php _e(' is the excerpt of a post with the "[...]" text at the end.' , 'wp-email-address-encryption'); ?></p>
    <p>widget_text<?php _e(' applies to the text of the WordPress Text widgets. And the rest refer to the comments.' , 'wp-email-address-encryption'); ?></p>
  <h3><?php _e('Shortcodes how to:' , 'wp-email-address-encryption'); ?></h3>
  <p><?php _e('Ncrypt Email shortcode has this form:' , 'wp-email-address-encryption'); ?><br> 
  <b>[ncrypt_email email="myemail@example.com" subject="Contact sales" title="e-mail" class="style-email"]Contact Us[/ncrypt_email]</b><br>
    <?php _e('NOTE: The curly brackets below are not required for the shortcode, I use them for clarity purposes.' , 'wp-email-address-encryption'); ?><br>
    <?php _e('It consists of the following parts: the name of the shortcode ' , 'wp-email-address-encryption'); ?><b>{ncrypt_email}</b><?php _e(', the ' , 'wp-email-address-encryption'); ?><b>{email}</b> 
    <?php _e('attribute, which is required, an optional email ' , 'wp-email-address-encryption'); ?><b>{subject}</b><?php _e(' attribute, an optional ' , 'wp-email-address-encryption'); ?><b>{title}</b> 
    <?php _e('attribute which is the text(tooltip) that appears when you hover over the link, and an optional ' , 'wp-email-address-encryption'); ?> 
    <b>{class}</b> <?php _e(' attribute in case you want to style it. The text that appears between the opening and closing square brackets, the content, ' , 'wp-email-address-encryption'); ?><b>]<?php _e('Contact Us' , 'wp-email-address-encryption'); ?>[/</b>
    <?php _e(' in this case is required too. It is highly recommended to provide text like contact us or e-mail us and not the actual e-mail address. If you provide the email address e.g. myemail@example.com and if you have provided the "Text to appear in place of plaintext e-mails" in the text field aside, it will use this text, and  when the user hovers over the text, the actual e-mail will appear.' , 'wp-email-address-encryption'); ?><br>
    <?php _e('So to recap in order to use the shortcode you must provide the name  ' , 'wp-email-address-encryption'); ?><b>{ncrypt_email}</b><?php _e(' and at least two required fields the ' , 'wp-email-address-encryption') ?>
    <b>{email}</b><?php _e(' attribute and the ' , 'wp-email-address-encryption'); ?>
    <b><?php _e('content(text between opening and closing square brackets).' , 'wp-email-address-encryption'); ?></b><?php _e(' The shortest form can be e.g.' , 'wp-email-address-encryption'); ?>
    <b>[ncrypt_email email="joe_doe@mycompany.com"]Email Joe[/ncrypt_email]</b><br>
    <b><?php _e('With attributes Remember to use the equal sign(=) and enclose them in double quotes ("") e.g. email="somemail@some.com"' , 'wp-email-address-encryption'); ?></b><br>
    <?php _e('If you want to use the shortcode directly into php you must edit the php file and use this code:' , 'wp-email-address-encryption'); ?><br>
    <p><pre style="font-size: .9em; white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;"><b>&lt;?php echo do_shortcode( '[ncrypt_email email="joe_doe@somemail.com" subject="Call me today" 
    title="e-mail" class="style-me"]Contact Webmaster[/ncrypt_email]' ); ?&gt;</b></pre></p>
    <p><?php _e('You must use the ' , 'wp-email-address-encryption'); ?><i>do_shortcode</i><?php _e(' function and  ' , 'wp-email-address-encryption'); ?><i>echo</i><?php _e(' back the result.' , 'wp-email-address-encryption'); ?><br>
    <?php _e('This is useful for areas that the plugin cannot reach e.g. if you theme does not have a widget area in the footer
    and you want to put an email there, then the above function should be useful.' , 'wp-email-address-encryption'); ?></p>
  </p>
 </div>
	<?php
	}

	public function register_settings() {

		// register_setting( $option_group, $option_name, $sanitize_callback )
		register_setting( 'e01t_settings-group', 'ncrypt_email_options' , array($this, 'plugin_main_settings_validate') );

		/****** SECTION ******/
		// add_settings_section( $id, $title, $callback, $page )
		add_settings_section(
			'ncrypt-email-settings-section',
			__('Ncrypt Email Configuration.', 'wp-email-address-encryption'),
			array($this, 'print_encrypt_email_section_info'),
			basename(__FILE__)
		);

		/****** FIELDS ******/
		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_mailto', 
			__('Encrypt mailto: emails', 'wp-email-address-encryption'), 
			array($this, 'ncrypt_mailto_links_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_plaintext', 
			__('Encrypt plaintext emails', 'wp-email-address-encryption'), 
			array($this, 'ncrypt_plaintext_emails_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_linked_text' , 
			__('Text to appear in place of plaintext e-mails?', 'wp-email-address-encryption'), 
			array($this, 'counter_ocr_text_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_class_name' , 
			__('Class attribute (name) for the generated plaintext e-mail links.', 'wp-email-address-encryption'), 
			array($this, 'ncrypt_class_name_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_question' , 
			__('Question for non-JavaScript-capable browsers.', 'wp-email-address-encryption'), 
			array($this, 'ncrypt_question_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_answer' , 
			__('Answer:', 'wp-email-address-encryption'), 
			array($this, 'ncrypt_answer_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_cookies' , 
			__('Cookies prompt:', 'wp-email-address-encryption'), 
			array($this, 'ncrypt_cookies_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args )
		add_settings_field(
			'ncrypt_hover', 
			__('Reveal Emails on hover/tap', 'wp-email-address-encryption'), 
			array($this, 'ncrypt_hover_links_settings'), 
			basename(__FILE__), 
			'ncrypt-email-settings-section'
		);
			$this->options = (array)get_option( 'ncrypt_email_options' );


	} // END register_settings()
	
	/****** Functions for sections and Fields ******/
	public function print_encrypt_email_section_info() {
		$html  = '<h4 style="letter-spacing:.09em; display:inline; color:#fff; background-color: #2ea2cc; font-family: sans-serif; padding:.2em;">';
		$html .= __('Encrypting options' , 'wp-email-address-encryption');
		$html .= '</h4>';
		echo $html;
	}

	public function ncrypt_mailto_links_settings() {
		$ncrypt_mailto = $this->options['ncrypt_mailto'];
		
		$html = '<label for="ncrypt_email_options[ncrypt_mailto]">';
		$html .= '<input type="checkbox" name="ncrypt_email_options[ncrypt_mailto]" id="ncrypt_email_options[ncrypt_mailto]" value="1" ' . checked( 1, $ncrypt_mailto, false ) . ' />';
		$html .= '&nbsp;';
		$html .= __('Encrypt emails that appear as hyperlinks');
		$html .= '</label>';
		
		echo $html;
	}

	public function ncrypt_plaintext_emails_settings(){
		$ncrypt_plaintext = $this->options['ncrypt_plaintext'];
		
		$html = '<label for="ncrypt_email_options[ncrypt_plaintext]">';
		$html .= '<input type="checkbox" name="ncrypt_email_options[ncrypt_plaintext]" id="ncrypt_email_options[ncrypt_plaintext]" value="1" ' . checked( 1, $ncrypt_plaintext, false ) . ' />';
		$html .= '&nbsp;';
		$html .= __('Encrypt emails that appear as plain text');
		$html .= '</label>';
		
		echo $html;
	}

	public function counter_ocr_text_settings(){ 
		$ncrypt_linked_text = $this->options['ncrypt_linked_text'];

		$html = '<div style="width:99%">';
		$html .= '<label for="ncrypt_email_options[ncrypt_linked_text]">';
		$html .= __('It is highly recommended to provide it. If you don\'t, proper encryption cannot occur. The e-mail is still susceptible to OCR (Optical Character Recognition) attacks. When the user hovers over the text, the actual e-mail will appear. The same text will appear in a mailto e-mail, if it has text in the form of myemail@example.com. Again when the user hovers over the text, the actual e-mail will appear. ' , 'wp-email-address-encryption');
		$html .= '<input style="width:99%" type="text" name="ncrypt_email_options[ncrypt_linked_text]" id="ncrypt_email_options[ncrypt_linked_text]" value="' . $ncrypt_linked_text . '" />';
		$html .= '</label>'; 
		$html .= '</div>';

		echo $html;
	}

	public function ncrypt_class_name_settings(){
		$ncrypt_class_name = $this->options['ncrypt_class_name'];
		
		$html  = '<div style="width:99%">';
		$html .= '<label for="ncrypt_email_options[ncrypt_class_name]">';
		$html .= __('In case you want to style them via css.');
		$html .= '<input style="width:99%" type="text" name="ncrypt_email_options[ncrypt_class_name]" id="ncrypt_email_options[ncrypt_class_name]" value="' . $ncrypt_class_name . '" />';
		$html .= '&nbsp;';
		$html .= '</label>';
		$html .= '</div>';
		
		echo $html;
	}

	public function ncrypt_question_settings(){
		$ncrypt_question = $this->options['ncrypt_question'];
		
		$html  = '<div style="width:99%">';
		$html .= '<label for="ncrypt_email_options[ncrypt_question]">';
		$html .= __('A question for usage in case that browser does not support javascript or is disabled.');
		$html .= '<input style="width:99%" type="text" name="ncrypt_email_options[ncrypt_question]" id="ncrypt_email_options[ncrypt_question]" value="' . $ncrypt_question . '" />';
		$html .= '&nbsp;';
		$html .= '</label>';
		$html .= '</div>';
		
		echo $html;
	}

	public function ncrypt_answer_settings(){
		$ncrypt_answer = $this->options['ncrypt_answer'];
		
		$html  = '<div style="width:99%">';
		$html .= '<label for="ncrypt_email_options[ncrypt_answer]">';
		$html .= __('The answer for the above question.');
		$html .= '<input style="width:99%" type="text" name="ncrypt_email_options[ncrypt_answer]" id="ncrypt_email_options[ncrypt_answer]" value="' . $ncrypt_answer . '" />';
		$html .= '&nbsp;';
		$html .= '</label>';
		$html .= '</div>';
		
		echo $html;
	}

	public function ncrypt_cookies_settings(){
		$ncrypt_cookies = $this->options['ncrypt_cookies'];
		
		$html  = '<div style="width:99%">';
		$html .= '<label for="ncrypt_email_options[ncrypt_cookies]">';
		$html .= __('For extra protection the encrypter sets a cookie. Most if not all of spam bots do not have the capability to support cookies. So in case that cookies are disabled by the user, a message will appear.');
		$html .= '<input style="width:99%" type="text" name="ncrypt_email_options[ncrypt_cookies]" id="ncrypt_email_options[ncrypt_cookies]" value="' . $ncrypt_cookies . '" />';
		$html .= '&nbsp;';
		$html .= '</label>';
		$html .= '</div>';
		
		echo $html;
	}

	public function ncrypt_hover_links_settings() {
		$ncrypt_hover = $this->options['ncrypt_hover'];
		
		$html = '<label for="ncrypt_email_options[ncrypt_hover]">';
		$html .= '<input type="checkbox" name="ncrypt_email_options[ncrypt_hover]" id="ncrypt_email_options[ncrypt_hover]" value="1" ' . checked( 1, $ncrypt_hover, false ) . ' />';
		$html .= '&nbsp;';
		$html .= __('When the user hovers over an e-mail, the actual e-mail address will appear in place of text.');
		$html .= '</label>';
		
		echo $html;
	}

	/* Form Validation */
	public function plugin_main_settings_validate($arr_input) {
		// if the clicked button was submit...
		if( !empty($arr_input['submit']) ){
			// Define the array for the updated options
   			$output = array();
		    // Loop through each of the options sanitizing the data
		    foreach( $arr_input as $key => $val ) {
		     
		        if( isset ( $arr_input[$key] ) ) {
		        	/* if the value is ncrypt_mailto OR ncrypt_plaintext OR ncrypt_hover
		        	 * it is a checkbox so the only accepted values is either 1  
		        	 * OR an empty string.
		        	*/
		        	if ($arr_input[$key] == 'ncrypt_mailto')
		        		$output[$key] = $arr_input[$key] ? 1 : '';
	        		elseif ($arr_input[$key] == 'ncrypt_plaintext')
	        			$output[$key] = $arr_input[$key] ? 1 : '';
	        		elseif ($arr_input[$key] == 'ncrypt_hover')
	        			$output[$key] = $arr_input[$key] ? 1 : '';
	        		else
		           		$output[$key] = strip_tags( stripslashes ( trim( $arr_input[$key] ) ) );
		        } // end outer if 
		     
		    } // end foreach
     
		    // Return the new collection
		    return apply_filters( 'plugin_main_settings_validate', $output, $arr_input );
		} else { // else reset was clicked...
			$arr_input["ncrypt_mailto"]      = 1; 
			$arr_input["ncrypt_plaintext"]   = 1; 
			$arr_input["ncrypt_linked_text"] = "email-me"; 
			$arr_input["ncrypt_class_name"]  = "ncrypt-email";
			$arr_input["ncrypt_question"]    = "How many legs a dog has?";
			$arr_input["ncrypt_answer"]      = "four";
			$arr_input["ncrypt_cookies"]     = "You need to enable cookies to view this email! Enable Cookies and refresh the page!";
			$arr_input["ncrypt_hover"]       = 1;

	    	return $arr_input;
		}
	} // END plugin_main_settings_validate()

	/****** HELPER FUNCTIONS 
		*	TO BE CALLED ON
		*	ON PLUGIN ACTIVATION - DEACTIVATION
	******/

	// On plugin deactivation delete options
	public function e01t_delete_options(){
		delete_option( 'ncrypt_email_options' );
		delete_option( 'ncrypt_key' );
	}

	// On plugin activation call e01t_rand_char() 
	// and e01t_default_values() functions
	public function e01t_on_register(){
		$this->e01t_rand_char();
		$this->e01t_default_values();
	}

	private function e01t_rand_char() {
	  	$length=45;
	    $random = '';
	    for ($i = 0; $i < $length; $i++) {
	      $random .= chr(mt_rand(33, 126));
	    } 
    	add_option('ncrypt_key' , $random);
	}

	private function e01t_default_values(){
			$this->options = array( "ncrypt_mailto"      => 1, 
									"ncrypt_plaintext"   => 1, 
									"ncrypt_linked_text" =>"email-me", 
									"ncrypt_class_name"  => "ncrypt-email",
									"ncrypt_question"    => "How many legs a dog has?",
									"ncrypt_answer"      => "four",
									"ncrypt_cookies"     => "You need to enable cookies to view this email! Enable Cookies and refresh the page!",
									"ncrypt_hover"       => 1
									);
	    	add_option( 'ncrypt_email_options', $this->options );
	}

	// Return an array with the plugin settings options
	// NOT the 'ncrypt_key' though, you have to take this seperatelly
	public function get_ncrypt_options(){
		return $this->options;
	}

} // END class