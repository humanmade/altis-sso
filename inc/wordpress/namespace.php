<?php

namespace Altis\SSO\WordPress;

use function Altis\get_config;
use const Altis\ROOT_DIR;

function bootstrap() {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin' );
}

function load_plugin() {
	$config = get_config()['modules']['sso']['wordpress'];

	define( 'HM_DELEGATED_AUTH_REST_BASE', $config['server-rest-base'] );
	define( 'HM_DELEGATED_AUTH_CLIENT_ID', $config['oauth2-client-id'] );
	define( 'HM_DELEGATED_AUTH_LOGIN_TEXT', __( 'Login with WordPress SSO', 'altis' ) );
	add_filter( 'delegated_oauth.sync-roles', ! empty( $config['sync-roles'] ) ? '__return_false' : '__return_false' );

	require_once ROOT_DIR . '/vendor/humanmade/delegated-oauth/plugin.php';
}
