<?php

namespace HM\Platform\SSO;

use function HM\Platform\get_config;
use function HM\Platform\register_module;

require_once __DIR__ . '/inc/saml/namespace.php';
require_once __DIR__ . '/inc/wordpress/namespace.php';

add_action( 'hm-platform.modules.init', function () {
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
} );
