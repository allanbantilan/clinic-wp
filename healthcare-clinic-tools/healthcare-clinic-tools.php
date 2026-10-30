<?php
/**
 * Plugin Name: Healthcare Clinic Tools
 * Description: Interactive shortcodes for a healthcare clinic landing page.
 * Version: 1.0.2
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hc_packages() {
	return array(
		'basic' => array( 'name' => 'Basic', 'price' => 1490, 'includes' => array(), 'tag' => 'A good start', 'description' => 'Routine basics for a clear starting point.', 'tests' => array( 'General consultation', 'Complete blood count', 'Urinalysis' ) ),
		'standard' => array( 'name' => 'Standard', 'price' => 2990, 'includes' => array( 'sugar', 'cholesterol' ), 'tag' => 'Balanced choice', 'description' => 'A broader overview for everyday preventive care.', 'tests' => array( 'Everything in Basic', 'Blood sugar', 'Lipid profile', 'Kidney function' ) ),
		'executive' => array( 'name' => 'Executive', 'price' => 4990, 'includes' => array( 'sugar', 'cholesterol', 'ecg' ), 'tag' => 'More detail', 'description' => 'A more comprehensive conversation and screening.', 'tests' => array( 'Everything in Standard', 'ECG', 'Liver function', 'Follow-up review' ) ),
	);
}

function hc_addons() {
	return array( 'sugar' => array( 'name' => 'Blood sugar', 'price' => 350 ), 'cholesterol' => array( 'name' => 'Lipid profile', 'price' => 650 ), 'ecg' => array( 'name' => 'ECG', 'price' => 800 ) );
}

add_action( 'wp_enqueue_scripts', function () {
	$base = plugin_dir_url( __FILE__ ) . 'assets/';
	wp_enqueue_style( 'hc-clinic', $base . 'clinic.css', array(), '1.0.2' );
	wp_enqueue_script( 'hc-clinic', $base . 'clinic.js', array(), '1.0.0', true );
	wp_localize_script( 'hc-clinic', 'hcPrices', array( 'packages' => hc_packages(), 'addons' => hc_addons() ) );
} );

add_action( 'wp_head', function () {
	if ( is_front_page() ) { echo '<meta name="description" content="Compare checkup packages, explore estimated prices, and request information about your next visit.">' . "\n"; }
} );

add_shortcode( 'hc_calculator', function () {
	ob_start(); ?>
	<div class="hc-tool hc-calculator" aria-labelledby="hc-calc-title">
		<h3 id="hc-calc-title">A starting point for your checkup</h3>
		<p>Answer a few questions to explore a package. Your answers stay in this browser and are not sent with your request.</p>
		<div class="hc-field-grid">
			<label>Age <input name="age" type="number" min="18" max="120" inputmode="numeric" required></label>
			<label>Height (cm) <input name="height" type="number" min="100" max="250" step="0.1" inputmode="decimal" required></label>
			<label>Weight (kg) <input name="weight" type="number" min="25" max="350" step="0.1" inputmode="decimal" required></label>
			<label>Activity level <select name="activity"><option value="active">Often active</option><option value="some">Sometimes active</option><option value="low">Mostly seated</option></select></label>
		</div>
		<label class="hc-check"><input type="checkbox" name="family"> Family history of heart disease or diabetes</label>
		<button class="hc-button" type="button" data-hc-calculate>See my suggestion</button>
		<p class="hc-result" aria-live="polite"></p>
		<p class="hc-fine">This is a general planning tool, not a diagnosis or medical advice. A clinician can advise which tests are appropriate.</p>
	</div>
	<?php return ob_get_clean();
} );

add_shortcode( 'hc_builder', function () {
	$packages = hc_packages();
	$addons = hc_addons();
	ob_start(); ?>
	<div class="hc-tool hc-builder" aria-labelledby="hc-builder-title">
		<h3 id="hc-builder-title">Build your checkup</h3>
		<p>Choose a starting package, then add only the tests you want to ask about.</p>
		<fieldset><legend>Choose a package</legend><div class="hc-options">
		<?php foreach ( $packages as $key => $package ) : ?>
			<label class="hc-option"><input type="radio" name="hc-package" value="<?php echo esc_attr( $key ); ?>" <?php checked( $key, 'standard' ); ?>><span><strong><?php echo esc_html( $package['name'] ); ?></strong><small>₱<?php echo esc_html( number_format( $package['price'] ) ); ?></small></span></label>
		<?php endforeach; ?>
		</div></fieldset>
		<fieldset><legend>Optional tests</legend><div class="hc-options">
		<?php foreach ( $addons as $key => $addon ) : ?>
			<label class="hc-option"><input type="checkbox" name="hc-addon" value="<?php echo esc_attr( $key ); ?>"><span><strong><?php echo esc_html( $addon['name'] ); ?></strong><small>+ ₱<?php echo esc_html( number_format( $addon['price'] ) ); ?></small></span></label>
		<?php endforeach; ?>
		</div></fieldset>
		<div class="hc-total"><span>Estimated total</span><strong aria-live="polite">₱2,990</strong></div>
		<p class="hc-fine">Estimated prices in PHP. Final availability and price are confirmed before booking.</p>
		<button class="hc-button" type="button" data-hc-request>Request this package <span aria-hidden="true">→</span></button>
	</div>
	<?php return ob_get_clean();
} );

add_shortcode( 'hc_packages', function () {
	ob_start(); ?>
	<div class="hc-site"><section class="hc-section" id="packages"><div class="hc-wrap"><p class="hc-kicker">Simple choices</p><h2>Compare the packages.</h2><div class="hc-card-grid">
	<?php foreach ( hc_packages() as $key => $package ) : ?>
		<article class="hc-card <?php echo 'standard' === $key ? 'hc-card-featured' : ''; ?>"><span class="hc-tag"><?php echo esc_html( $package['tag'] ); ?></span><h3><?php echo esc_html( $package['name'] ); ?></h3><p><?php echo esc_html( $package['description'] ); ?></p><div class="hc-price">₱<?php echo esc_html( number_format( $package['price'] ) ); ?></div><ul><?php foreach ( $package['tests'] as $test ) : ?><li><?php echo esc_html( $test ); ?></li><?php endforeach; ?></ul><a href="#request" data-hc-package="<?php echo esc_attr( $key ); ?>" class="hc-button <?php echo 'standard' === $key ? '' : 'hc-button-secondary'; ?>">Request <?php echo esc_html( $package['name'] ); ?></a></article>
	<?php endforeach; ?>
	</div><p class="hc-fine">Package details and prices are estimates. A licensed clinician can advise which tests are appropriate.</p></div></section></div>
	<?php return ob_get_clean();
} );

add_shortcode( 'hc_hmo', function () {
	return '<div class="hc-tool hc-hmo"><label for="hc-hmo-search">Search common HMO providers</label><input id="hc-hmo-search" type="search" placeholder="Type an HMO name" autocomplete="off"><ul class="hc-hmo-list"><li>Maxicare</li><li>Intellicare</li><li>Medicard</li><li>PhilCare</li><li>Etiqa</li><li>ValuCare</li></ul><p class="hc-hmo-empty" hidden>No provider match. Please contact the clinic to check coverage.</p><p class="hc-fine">Listing a provider does not confirm acceptance or coverage. Please verify your plan before booking.</p></div>';
} );

add_shortcode( 'hc_contact', function ( $atts ) {
	$atts = shortcode_atts( array( 'phone' => '', 'whatsapp' => '', 'address' => '', 'hours' => '' ), $atts, 'hc_contact' );
	$phone = preg_replace( '/[^0-9+]/', '', $atts['phone'] );
	$whatsapp = preg_replace( '/\D/', '', $atts['whatsapp'] );
	$address = sanitize_text_field( $atts['address'] );
	$hours = sanitize_text_field( $atts['hours'] );
	ob_start(); ?>
	<div class="hc-site"><section class="hc-section"><div class="hc-wrap hc-contact-grid"><div><p class="hc-kicker">Find us</p><h2>A calmer kind of clinic visit.</h2><p>Use the request form above to start a conversation about your checkup.</p><?php if ( $phone ) : ?><p><a class="hc-button" href="tel:<?php echo esc_attr( $phone ); ?>">Call <?php echo esc_html( $atts['phone'] ); ?></a></p><?php endif; ?><?php if ( $whatsapp && strlen( $whatsapp ) >= 8 && strlen( $whatsapp ) <= 15 ) : ?><p><a class="hc-button hc-button-secondary" href="https://wa.me/<?php echo esc_attr( $whatsapp ); ?>" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a></p><?php endif; ?></div><div class="hc-contact-card"><h3>Health Checkup Clinic</h3><?php if ( $address ) : ?><p><?php echo esc_html( $address ); ?></p><iframe class="hc-map" title="Map of clinic location" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=<?php echo esc_attr( rawurlencode( $address ) ); ?>&amp;output=embed"></iframe><?php else : ?><p>Ask for location details when planning your visit.</p><?php endif; ?><?php if ( $hours ) : ?><p><strong>Hours:</strong> <?php echo esc_html( $hours ); ?></p><?php endif; ?></div></div></section></div>
	<?php if ( $whatsapp && strlen( $whatsapp ) >= 8 && strlen( $whatsapp ) <= 15 ) : ?><a class="hc-chat" href="https://wa.me/<?php echo esc_attr( $whatsapp ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat with Health Checkup Clinic on WhatsApp">Chat with us</a><?php endif; ?>
	<?php if ( $phone && $address ) : ?><script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'MedicalClinic', 'name' => 'Health Checkup Clinic', 'url' => home_url( '/' ), 'telephone' => $phone, 'address' => array( '@type' => 'PostalAddress', 'streetAddress' => $address ) ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script><?php endif; ?>
	<?php return ob_get_clean();
} );

add_shortcode( 'hc_request', function () {
	$packages = hc_packages();
	$status = isset( $_GET['hc_status'] ) && is_string( $_GET['hc_status'] ) ? sanitize_key( wp_unslash( $_GET['hc_status'] ) ) : '';
	ob_start(); ?>
	<div class="hc-tool hc-request" id="request" aria-labelledby="hc-request-title">
		<h3 id="hc-request-title">Request a checkup</h3>
		<p>Tell us how to reach you. This is a request, not a confirmed appointment.</p>
		<?php if ( 'sent' === $status ) : ?><p class="hc-message" role="status">Thanks. Your request was sent. Please check your email for preparation guidance.</p><?php elseif ( 'partial' === $status ) : ?><p class="hc-message" role="status">Your request reached the clinic, but the preparation email could not be sent. Please contact the clinic for instructions.</p><?php elseif ( 'error' === $status ) : ?><p class="hc-message hc-error" role="alert">We could not send your request. Please check the form and try again.</p><?php endif; ?>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="hc_request">
			<?php wp_nonce_field( 'hc_request', 'hc_nonce' ); ?>
			<div class="hc-field-grid"><label>Full name <input name="name" type="text" maxlength="100" autocomplete="name" required></label><label>Email <input name="email" type="email" maxlength="190" autocomplete="email" required></label><label>Phone <input name="phone" type="tel" maxlength="30" pattern="(?=(?:.*[0-9]){7})[0-9\+\(\)\. \-]{7,30}" title="Enter a phone number with at least 7 digits." autocomplete="tel" required></label><label>Package <select name="package" required><?php foreach ( $packages as $key => $package ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $package['name'] ); ?></option><?php endforeach; ?></select></label></div>
			<input type="hidden" name="addons" value="">
			<p class="hc-request-summary" aria-live="polite">Selected: Standard — ₱2,990</p>
			<label class="hc-check"><input type="checkbox" name="consent" value="yes" required> I agree to be contacted about this request. See the privacy note below.</label>
			<div class="hc-honeypot" aria-hidden="true"><label>Leave this blank <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<p class="hc-fine">We use your name, email, phone, and package choice only to respond to this request. Do not send symptoms or test results. The request is emailed to the WordPress site administrator; no request database is created by this plugin. A temporary hashed network identifier limits repeated submissions.</p>
			<button class="hc-button" type="submit">Send request <span aria-hidden="true">→</span></button>
		</form>
	</div>
	<?php return ob_get_clean();
} );

function hc_handle_request() {
	$redirect = wp_get_referer() ?: home_url( '/' );
	$redirect = remove_query_arg( 'hc_status', $redirect );
	$fail = function () use ( $redirect ) { wp_safe_redirect( add_query_arg( 'hc_status', 'error', $redirect ) . '#request' ); exit; };
	foreach ( array( 'hc_nonce', 'name', 'email', 'phone', 'package', 'addons', 'consent' ) as $field ) { if ( isset( $_POST[ $field ] ) && ! is_string( $_POST[ $field ] ) ) { $fail(); } }
	if ( isset( $_POST['addons'] ) && strlen( $_POST['addons'] ) > 80 ) { $fail(); }
	if ( ! isset( $_POST['hc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hc_nonce'] ) ), 'hc_request' ) ) { $fail(); }
	if ( ! empty( $_POST['website'] ) ) { $fail(); }
	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$package_key = isset( $_POST['package'] ) ? sanitize_key( wp_unslash( $_POST['package'] ) ) : '';
	$addon_keys = isset( $_POST['addons'] ) ? array_filter( array_map( 'sanitize_key', explode( ',', wp_unslash( $_POST['addons'] ) ) ) ) : array();
	$packages = hc_packages(); $addons = hc_addons();
	if ( ! $name || strlen( $name ) > 100 || ! is_email( $email ) || strlen( $email ) > 190 || ! preg_match( '/^[0-9+(). -]{7,30}$/', $phone ) || strlen( preg_replace( '/\D/', '', $phone ) ) < 7 || ! isset( $packages[ $package_key ] ) || count( $addon_keys ) > count( $addons ) || array_diff( $addon_keys, array_keys( $addons ) ) || ! isset( $_POST['consent'] ) || 'yes' !== $_POST['consent'] ) { $fail(); }
	$remote = isset( $_SERVER['REMOTE_ADDR'] ) && is_string( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	// shortcut: a shared network can submit once per minute; use dedicated abuse controls for a busy clinic.
	$rate_key = $remote ? 'hc_rate_' . substr( hash_hmac( 'sha256', $remote, wp_salt( 'auth' ) ), 0, 32 ) : '';
	if ( $rate_key && get_transient( $rate_key ) ) { $fail(); }
	$addon_keys = array_diff( array_unique( $addon_keys ), $packages[ $package_key ]['includes'] );
	$total = $packages[ $package_key ]['price'];
	$addon_names = array();
	foreach ( $addon_keys as $key ) { $total += $addons[ $key ]['price']; $addon_names[] = $addons[ $key ]['name']; }
	$summary = $packages[ $package_key ]['name'] . ' — ₱' . number_format( $total ) . ( $addon_names ? ' (plus ' . implode( ', ', $addon_names ) . ')' : '' );
	$admin_message = "New checkup request\n\nName: $name\nEmail: $email\nPhone: $phone\nPackage: $summary\n\nPlease confirm availability and price directly with the visitor.";
	if ( ! wp_mail( get_option( 'admin_email' ), 'New checkup request — Health Checkup Clinic', $admin_message, array( 'Reply-To: ' . $email ) ) ) { $fail(); }
	if ( $rate_key ) { set_transient( $rate_key, 1, MINUTE_IN_SECONDS ); }
	$prep = 'For blood tests, ask the clinic whether fasting is required. If advised to fast, drink water unless a clinician tells you otherwise. Bring a valid ID, medication list, and HMO details if applicable. Never stop prescribed medicine without medical advice.';
	$reply = "Hello $name,\n\nWe received your request for $summary. This is not a confirmed appointment; the clinic will contact you.\n\nPreparation: $prep\n\nPlease do not reply with sensitive medical information.\n\nHealth Checkup Clinic";
	$status = wp_mail( $email, 'Your checkup request — Health Checkup Clinic', $reply ) ? 'sent' : 'partial';
	wp_safe_redirect( add_query_arg( 'hc_status', $status, $redirect ) . '#request' );
	exit;
}
add_action( 'admin_post_hc_request', 'hc_handle_request' );
add_action( 'admin_post_nopriv_hc_request', 'hc_handle_request' );
