<?php

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\GenericProvider;


require __DIR__ . '/vendor/autoload.php';


// Handle form POST
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$name    = trim( $_POST['UserName'] ?? '' );
	$email   = trim( $_POST['UserEmail'] ?? '' );
	$subject = trim( $_POST['subject'] ?? '' );
	$message = trim( $_POST['message'] ?? '' );

if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
	echo json_encode(
		array(
			'status'  => 'error',
			'message' => 'Invalid email address.',
		)
	);
		exit;
}




require 'vendor/autoload.php';
// Make sure this loads PHPMailer + League OAuth2

$mail = new PHPMailer( true );

try {
	$mail->isSMTP();
	$mail->Host       = 'smtp.office365.com';
	$mail->Port       = 587;
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->SMTPAuth   = true;

	$mail->AuthType = 'XOAUTH2';

	$provider = new GenericProvider(
		array(
			'clientId'                => getenv( 'CLIENT_ID' ),
			'clientSecret'            => getenv( 'CLIENT_SECRET' ),
			'redirectUri'             => 'https://localhost', // Not used in this case
			'urlAuthorize'            => 'https://login.microsoftonline.com/' . getenv( 'TENANT_ID' ) . '/oauth2/v2.0/authorize',
			'urlAccessToken'          => 'https://login.microsoftonline.com/' . getenv( 'TENANT_ID' ) . '/oauth2/v2.0/token',
			'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
			'scopes'                  => array( 'https://graph.microsoft.com/.default' ),
		)
	);

	$mail->setOAuth(
		new OAuth(
			array(
				'provider'     => $provider,
				'clientId'     => getenv( 'CLIENT_ID' ),
				'clientSecret' => getenv( 'CLIENT_SECRET' ),
				'refreshToken' => getenv( 'REFRESH_TOKEN' ),
				'userName'     => getenv( 'EMAIL_SENDER' ),
			)
		)
	);

	$mail->setFrom( getenv( 'EMAIL_SENDER' ), 'BastPet Contact' );
	$mail->addAddress( getenv( 'EMAIL_RECEIVER' ) );
	$mail->Subject = 'Test Email';
	$mail->Body    = 'This is a test message sent using Microsoft 365 via OAuth2 and PHPMailer.';

	$mail->send();
	echo json_encode(
		array(
			'success' => true,
			'message' => 'Message has been sent',
		)
	);
} catch ( Exception $e ) {
	echo json_encode(
		array(
			'success' => false,
			'message' => "Mailer Error: {$mail->ErrorInfo}",
		)
	);
}
