<?php
/**
 * Altis SSO SAML.
 *
 * @package altis/sso
 */

namespace Altis\SSO\SAML;

use Altis;
use HumanMade\SimpleSaml;

/**
 * Bootstrap SAML integration.
 *
 * @return void
 */
function bootstrap() {
	add_filter( 'wpsimplesaml_network_activated', '__return_true' );
	add_filter( 'wpsimplesaml_idp_metadata_xml_path', __NAMESPACE__ . '\\get_idp_metadata_file_path' );
	add_filter( 'pre_site_option_sso_sp_base', __NAMESPACE__ . '\\get_sp_client_id' );
	add_filter( 'pre_site_option_sso_enabled', __NAMESPACE__ . '\\get_sso_enabled_option' );
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

	// If the legacy-style file exists, load it, but warn.
	$legacy_file = Altis\ROOT_DIR . '/config/sso/saml-idp-metadata.xml';
	if ( file_exists( $legacy_file ) ) {
		trigger_error( 'The default "config/sso/saml-idp-metadata.xml" path is deprecated as of Altis 2.0. Specify the metadata_file setting manually, or use the default ".config/sso/saml-idp-metadata.xml".', E_USER_DEPRECATED );
		return $legacy_file;
	}

	// Otherwise, use the default.
	return Altis\ROOT_DIR . '/.config/sso/saml-idp-metadata.xml';
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
 * Show SSO login link in login form
 *
 * @action login_form
 */
function render_login_link() {
	/**
	 * Filters whether we should show the SSO login link in login form
	 *
	 * @return bool  Forces SSO authentication if true, defaults to True
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
		 * @return string Text to be used for the login button
		 */
		esc_html( apply_filters( 'wpsimplesaml_log_in_text', __( 'Log in with SAML SSO', 'wp-simple-saml' ) ) )
	);
}
