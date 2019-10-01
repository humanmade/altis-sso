<?php

namespace Altis\SSO; // @codingStandardsIgnoreLine

use function Altis\get_config;
use function Altis\register_module;

add_action( 'altis.modules.init', function () {
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
