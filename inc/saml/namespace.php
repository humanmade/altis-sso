<?php
/**
 * Altis SSO SAML.
 *
 * @package altis/sso
 */

namespace Altis\SSO\SAML;

use Altis;
use HumanMade\SimpleSaml;

use function Altis\get_environment_type;

/**
 * Bootstrap SAML integration.
 *
 * @return void
 */
function bootstrap() {
	// Set server port to 443 or 80 for SAML, port 8080 breaks validation.
	if ( class_exists( '\\OneLogin\\Saml2\\Utils' ) ) {
		$is_https = ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on';
		\OneLogin\Saml2\Utils::setSelfPort( $is_https ? '443' : '80' );
	}

	add_filter( 'wpsimplesaml_network_activated', '__return_true' );
	add_filter( 'wpsimplesaml_idp_metadata_xml_path', __NAMESPACE__ . '\\get_idp_metadata_file_path' );
	add_filter( 'pre_site_option_sso_sp_base', __NAMESPACE__ . '\\get_sp_client_id' );
	add_filter( 'pre_site_option_sso_enabled', __NAMESPACE__ . '\\get_sso_enabled_option' );
	add_filter( 'hm-require-login.allowed_pages', __NAMESPACE__ . '\\allow_sso_urls', 10, 2 );
	require_once Altis\ROOT_DIR . '/vendor/humanmade/wp-simple-saml/plugin.php';

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\remove_plugin_admin_ui' );
}

/**
 * Get metadata file path.
 *
 * Uses configuration option if available, otherwise falls back to a default.
 *
 * @return string Full path to the metadata file.
 */
function get_idp_metadata_file_path() : string {
	$config = Altis\get_config()['modules']['sso']['saml'];
	if ( isset( $config['metadata_file'] ) ) {
		return Altis\ROOT_DIR . DIRECTORY_SEPARATOR . $config['metadata_file'];
	}

	// Check for env-specific metadata files.
	$env_file = implode( DIRECTORY_SEPARATOR, [ Altis\ROOT_DIR, '.config', 'sso', sprintf( 'saml-idp-metadata-%s.xml', get_environment_type() ) ] );
	if ( file_exists( $env_file ) ) {
		return $env_file;
	}

	// If the legacy-style file exists, load it, but warn.
	$legacy_file = implode( DIRECTORY_SEPARATOR, [ Altis\ROOT_DIR, 'config', 'sso', 'saml-idp-metadata.xml' ] );
	if ( file_exists( $legacy_file ) ) {
		trigger_error( 'The default "config/sso/saml-idp-metadata.xml" path is deprecated as of Altis 2.0. Specify the metadata_file setting manually, or use the default ".config/sso/saml-idp-metadata.xml".', E_USER_DEPRECATED );
		return $legacy_file;
	}

	// Otherwise, use the default.
	return implode( DIRECTORY_SEPARATOR, [ Altis\ROOT_DIR, '.config', 'sso', 'saml-idp-metadata.xml' ] );
}

/**
 * Get the network root site URL as a client ID.
 *
 * @return string
 */
function get_sp_client_id() : string {
	return network_site_url( '/' );
}

/**
 * If SAML is set to "required", turn on the wp-simple-samle force-redirect
 * option which will mean the login for is not shown, and instead it
 * redirects directly to the SAML IdP.
 *
 * @return string
 */
function get_sso_enabled_option() : string {
	$config = Altis\get_config()['modules']['sso']['saml'];
	return  empty( $config['required'] ) ? 'link' : 'force';
}

/**
 * Remove filters that have been added for the admin UI
 */
function remove_plugin_admin_ui() {
	remove_action( 'admin_init', 'HumanMade\\SimpleSaml\\Admin\\settings_fields' );
	remove_action( 'wpmu_options', 'HumanMade\\SimpleSaml\\Admin\\network_settings_fields' );
	remove_action( 'update_wpmu_options', 'HumanMade\\SimpleSaml\\Admin\\save_network_settings_fields' );

	// Remove built-in login form UI.
	remove_action( 'login_message', 'HumanMade\\SimpleSaml\\login_form_link' );
}

/**
 * Ensure SAML endpoints are not redirected when require login is active.
 *
 * @param array $allowed Allowed PHP pages.
 * @param string|null $page The current page.
 * @return array
 */
function allow_sso_urls( array $allowed, ?string $page ) : array {
	if ( $page === 'index.php' && strpos( $_SERVER['REQUEST_URI'], '/sso/' ) !== false ) {
		$allowed[] = $page;
	}

	return $allowed;
}

/**
 * Show SSO login link in login form
 *
 * @action login_form
 */
function render_login_link() {
	/**
	 * Filters whether we should show the SSO login link in login form
	 *
	 * @param bool $force_sso Forces SSO authentication if true, defaults to True.
	 */
	if ( ! apply_filters( 'wpsimplesaml_log_in_link', true ) ) {
		return;
	}

	$redirect_url = SimpleSaml\get_redirection_url();

	printf(
		'<p class="altis-sso-saml"><a class="button button-hero" href="%s" id="login-via-sso">%s</a></p>',
		esc_url( add_query_arg( 'redirect_to', urlencode( $redirect_url ), home_url( 'sso/login/' ) ) ),
		/**
		 * Filters the SSO login button text
		 *
		 * @param string $login_button_text Text to be used for the login button.
		 */
		esc_html( apply_filters( 'wpsimplesaml_log_in_text', __( 'Log in with SAML SSO', 'wp-simple-saml' ) ) )
	);
}
