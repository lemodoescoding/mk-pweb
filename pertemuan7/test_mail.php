<?php

  header("Access-Control-Allow-Origin: http://127.0.0.1:3000");
  header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
  header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

  // Handle preflight OPTIONS request (important!)
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      http_response_code(200);
      exit();
  }
  echo("test");

  if(isset($_POST['name']) && isset($_POST['email'])){
    if ( ($_POST['name']!="") && ($_POST['email']!="")){
      $name = $_POST['name'];
      $email = $_POST['email'];
      $message = $_POST['message'];

      $to = "satrio310807@gmail.com";
      $subject = "AllPHPTricks Contact Form Email";
      $message = "<p>New email is received from $name.</p>
      <p>$message</p>";

      // Always set content-type when sending HTML email
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      $headers .= "From: <".$email.">" . "\r\n";
      $sent = mail($to,$subject,$message,$headers);

      if($sent){
	      echo "<span style='color:green; font-weight:bold;'>
      	Thank you for contacting us, we will get back to you shortly.
      	</span>";
      }
      else {
      	echo "<span style='color:red; font-weight:bold;'>
      	  Sorry! Your form submission is failed.
      	</span>";
    	}
    }
  }
?>
