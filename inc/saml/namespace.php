<?php

namespace Altis\SSO\SAML;

use const Altis\ROOT_DIR;
use function Altis\get_config;

function bootstrap() {
	$config = get_config()['modules']['sso']['saml'];

	add_filter( 'wpsimplesaml_network_activated', '__return_true' );
	add_filter( 'wpsimplesaml_idp_metadata_xml_path', __NAMESPACE__ . '\\get_idp_metadata_file_path' );
	add_filter( 'pre_site_option_sso_sp_base', __NAMESPACE__ . '\\get_sp_client_id' );
	add_filter( 'pre_site_option_sso_enabled', __NAMESPACE__ . '\\get_sso_enabled_option' );
	require_once ROOT_DIR . '/vendor/humanmade/wp-simple-saml/plugin.php';

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\remove_plugin_admin_ui' );
}

function get_idp_metadata_file_path() : string {
	return ROOT_DIR . '/config/sso/saml-idp-metadata.xml';
}

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
	$config = get_config()['modules']['sso']['saml'];
	return  empty( $config['required'] ) ? 'link' : 'force';
}

/**
 * Remove filters that have been added for the admin UI
 */
function remove_plugin_admin_ui() {
	remove_action( 'admin_init', 'HumanMade\\SimpleSaml\\Admin\\settings_fields' );
	remove_action( 'wpmu_options', 'HumanMade\\SimpleSaml\\Admin\\network_settings_fields' );
	remove_action( 'update_wpmu_options', 'HumanMade\\SimpleSaml\\Admin\\save_network_settings_fields' );
}
