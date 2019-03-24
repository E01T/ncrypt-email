<?php
 	require_once('../../../wp-load.php');
 	$options = get_option( 'ncrypt_email_options' );
  	$my_answer = $options['ncrypt_answer'];
  	$my_question = $options['ncrypt_question'];
  	$my_cookie_message = $options['ncrypt_cookies'];
 	$my_key = get_option('ncrypt_key');

	function e01t_decrypt($my_data, $my_key) {
		$password = $my_key;
		$key = pack('H*', md5($password));
		$cipher = "rijndael-128";
		$mode = "ecb";

		$my_data = base64_decode($my_data);
		$my_data = mcrypt_decrypt($cipher, $key, $my_data, $mode);

		$block = mcrypt_get_block_size($cipher, $mode);
		$pad = ord($my_data[($len = strlen($my_data)) - 1]);
		return substr($my_data, 0, strlen($my_data) - $pad);
	}

 	function e01t_decrypt_email($question, $answer, $cookie_message, $key){
 		
 		$count = 0;
 		if( isset($_GET['c']) ){ $count = $_GET['c']; }

 		
		if( !isset($_COOKIE['e01t']) && $count == 0 ){ // if cookies are not set...try to set them again
 			if( !isset($_GET['crypt']) || $_GET['crypt'] == "" ) exit('This file can not be accessed directly...');
 			setcookie('e01t', 18, time()+(60*60*24) , '/');
 			header('Location: ' . esc_url( $_SERVER['REQUEST_URI'] ) . '&c=' . urlencode(1) );
			exit();
	 	}else if(!isset($_COOKIE['e01t']) && $count == 1){ // Second attempt to set cookies and echo message 
	 		if( !isset($_GET['crypt']) || $_GET['crypt'] == "" ) exit('This file can not be accessed directly...');
	 		setcookie('e01t', 18, time()+(60*60*24) , '/'); 
			echo $cookie_message;
			exit();
		}else{ // Here cookies have been set/are enabled
			// Now we deal with the users who have javascript disabled and come directly by clicking the link

			if( !isset($_GET['crypt']) || $_GET['crypt'] == "" ) {
				header('Location: ' . esc_url( get_site_url() ) );
				exit();
			}
			else 
				$data  = $_GET['crypt'];

			// this is necessary for the emails that have ?=subject ... 
			list($data, $subject) = array_pad( preg_split("/\?/", $data) , 2, null);
			if($subject != null){
				$new_subject = '?'.$subject;
			}
	
			if( !isset($_POST['links_array']) && 
				(isset($_POST['no_of_houses']) && (strtolower($_POST['no_of_houses']) == $answer)) ){
				$email;  // the whole email string
				// $domain; // the first part of the email address e.g. etsouderos@gmail.com
				// $extra;  // (if any) the extra part ?subject=etc

				$email = e01t_decrypt($data, $key);

				list($domain, $extra) = array_pad( preg_split("/\?/", $email) , 2, null);
				$domain = str_replace("mailto:" , "" , $domain);

				echo '<a href="' . $email . $new_subject . '"/>' . $domain . '</a><br>';
			}else{
				echo '<form action="" method="post"><p>'.$question.'<input type="text" name="no_of_houses" value="" /><input type="submit" name="submit" value="submit" /></p></form>';
			}
		} // END else 
	} // end e01t_decrypt_email function
	e01t_decrypt_email($my_question , $my_answer, $my_cookie_message, $my_key);
?>