<?php
/** Local testing only: capture WordPress mail in the Mailpit container. */
add_filter( 'wp_mail_from', static function () { return 'no-reply@health-checkup-clinic.test'; } );
add_filter( 'wp_mail_from_name', static function () { return 'Health Checkup Clinic Local'; } );
add_action( 'phpmailer_init', static function ( $mailer ) {
	$mailer->isSMTP();
	$mailer->Host = 'mailpit';
	$mailer->Port = 1025;
	$mailer->SMTPAuth = false;
	$mailer->SMTPSecure = '';
} );
