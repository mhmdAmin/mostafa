<?php

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;


require __DIR__ . '/vendor/autoload.php';

// Get access token from Microsoft
function getMicrosoftAccessToken( $tenantId, $clientId, $clientSecret ) {
	$url = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";

	$postFields = array(
		'grant_type'    => 'client_credentials',
		'client_id'     => $clientId,
		'client_secret' => $clientSecret,
		'scope'         => 'https://graph.microsoft.com/.default',
	);

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $postFields ) );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/x-www-form-urlencoded' ) );

	$result = curl_exec( $ch );
	curl_close( $ch );

	$data = json_decode( $result, true );

	return $data['access_token'] ?? null;
}

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


	$dotenv = Dotenv\Dotenv::createImmutable( __DIR__ );
	$dotenv->load();

	$tenantId      = $_ENV['TENANT_ID'];
	$clientId      = $_ENV['CLIENT_ID'];
	$clientSecret  = $_ENV['CLIENT_SECRET'];
	$emailSender   = $_ENV['EMAIL_SENDER'];
	$emailReceiver = $_ENV['EMAIL_RECEIVER'];


	$accessToken = getMicrosoftAccessToken( $tenantId, $clientId, $clientSecret );

	if ( ! $accessToken ) {
		echo json_encode(
			array(
				'status'  => 'error',
				'message' => 'Failed to get access token.',
			)
		);
		exit;
	}

	$mail = new PHPMailer( true );

	try {
		$mail->isSMTP();
		$mail->Host       = 'smtp.office365.com';
		$mail->Port       = 587;
		$mail->SMTPSecure = 'tls';
		$mail->SMTPAuth   = true;
		$mail->AuthType   = 'XOAUTH2';

		$provider = new League\OAuth2\Client\Provider\GenericProvider(
			array(
				'clientId'                => $clientId,
				'clientSecret'            => $clientSecret,
				'redirectUri'             => 'http://localhost',
				'urlAccessToken'          => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token",
				'urlAuthorize'            => '',
				'urlResourceOwnerDetails' => '',
				'scopes'                  => array( 'https://outlook.office365.com/.default' ),
			)
		);

		$mail->setOAuth(
			new OAuth(
				array(
					'provider'    => $provider,
					'userName'    => $emailSender,
					'accessToken' => $accessToken,
				)
			)
		);

		$mail->setFrom( $emailSender, 'Website Contact' );
		$mail->addAddress( $emailReceiver );
		$mail->addReplyTo( $email, $name ); // Optional: allow replies to user

		$mail->isHTML( true );
		$mail->Subject = $subject;
		$mail->Body    = "
            <strong>Name:</strong> $name <br>
            <strong>Email:</strong> $email <br>
            <strong>Subject:</strong> $subject <br><br>
            <strong>Message:</strong> <br>$message
        ";

		$mail->send();
		echo json_encode(
			array(
				'status'  => 'success',
				'message' => 'Message sent successfully.',
			)
		);
	} catch ( Exception $e ) {
		echo json_encode(
			array(
				'status'  => 'error',
				'message' => 'Mailer Error: ' . $mail->ErrorInfo,
			)
		);
	}
}
