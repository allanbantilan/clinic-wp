<?php
$key = trim((string) getenv('BREVO_API_KEY'));
$sender = sanitize_email((string) getenv('BREVO_SENDER_EMAIL'));
$name = trim((string) getenv('BREVO_SENDER_NAME'));
if (!str_starts_with($key, 'xkeysib-') || !is_email($sender) || $name === '') {
    throw new RuntimeException('Brevo configuration is incomplete');
}

$settings = new \FluentMail\App\Models\Settings();
$settings->store([
    'connection_key' => md5($sender),
    'valid_senders' => [],
    'connection' => [
        'provider' => 'sendinblue',
        'sender_name' => $name,
        'sender_email' => $sender,
        'force_from_name' => 'yes',
        'api_key' => $key,
        'key_store' => 'db',
    ],
]);
if (!$settings->getConnection($sender)) {
    throw new RuntimeException('Brevo connection was not saved');
}
if (!wp_mail(get_option('admin_email'), "[$name] Delivery test", 'This is a delivery test from the live WordPress site.')) {
    throw new RuntimeException('WordPress could not submit the test email');
}
echo "Brevo test email submitted for $name.\n";
