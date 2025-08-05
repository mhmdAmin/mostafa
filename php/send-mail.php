<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust if needed


// Handle form POST
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$name         = trim( $_POST['UserName'] ?? '' );
	$sender_email = $_POST['UserEmail'] ?? '';
	$subject      = trim( $_POST['subject'] ?? 'NO MESSAGE FROM THE SENDER' );
	$message      = trim( $_POST['message'] ?? '' );

}


// if ( ! filter_var( $sender_email, FILTER_VALIDATE_EMAIL ) ) {

// echo json_encode(
// array(
// 'status'  => 'error',
// 'message' => 'Invalid email address.',
// )
// );
// exit;
// }


$hoster_email       = 'bastpet.uk@gmail.com';
$gmail_app_password = 'bqqzzthczuaijqht';

$mail = new PHPMailer( true );

try {
	// Server settings
	$mail->isSMTP();
	$mail->Host       = 'smtp.gmail.com';
	$mail->SMTPAuth   = true;
	$mail->Username   = $hoster_email;        // Your Gmail
	$mail->Password   = $gmail_app_password;          // 16-char App Password for Gmail
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port       = 587;

	// Recipients
	$mail->setFrom( $hoster_email, '[ contact Form ]' );
	$mail->addAddress( $hoster_email );         // Send to yourself
	// $mail->addAddress( 'mostafa.amin@bastpet.co.uk' );   // Optional: Forward copy to another email (can also be done via Gmail settings)

	$mail->addReplyTo( $sender_email, $name );         // Reply to sender email address

	// Content
	$mail->isHTML( true );
	$mail->CharSet  = 'UTF-8';
	$mail->Encoding = 'base64';
	$mail->Subject  = $subject;
	$mail->Body     = "Name: $name<br>" .
						"Email: $sender_email<br>" .
						"Subject: $subject<br>" .
						'Message:<br>' . $message;

	// the plain text version of the message, if the client not supports HTML emails.
	$mail->AltBody =
				"Name: $name\n" .
				"Email: $sender_email\n" .
				"Subject: $subject\n" .
				"Message:\n$message";


	// Send

	$mail->send();

	// echo json_encode(['success' => true, 'message' => 'Message has been sent']);
} catch ( Exception $e ) {
	echo json_encode(
		array(
			'success' => false,
			'message' => "Message could not be sent. Error: {$mail->ErrorInfo}",
		)
	);
}
