<?php

namespace Altis\SSO; // @codingStandardsIgnoreLine

use function Altis\get_config;
use function Altis\register_module;

require_once __DIR__ . '/inc/saml/namespace.php';
require_once __DIR__ . '/inc/wordpress/namespace.php';

function register() {
	$default_settings = [
		'enabled'   => true,
		'saml'      => false,
		'wordpress' => false,
	];
	register_module( 'sso', __DIR__, 'SSO', $default_settings, function () {
		$config = get_config()['modules']['sso'];
		if ( $config['saml'] ) {
			SAML\bootstrap();
		}
		if ( $config['wordpress'] ) {
			WordPress\bootstrap();
		}
	} );
}

// Don't self-initialize if this is not an Altis execution.
if ( ! function_exists( 'add_action' ) ) {
	return;
}

add_action( 'altis.modules.init', __NAMESPACE__ . '\\register' );
