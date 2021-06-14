<?php
/**
 * Altis SSO Module.
 *
 * @package altis/sso
 */

namespace Altis\SSO; // phpcs:ignore

use Altis;

add_action( 'altis.modules.init', function () {
	$default_settings = [
		'enabled' => true,
		'saml' => false,
		'wordpress' => false,
		'hide_native' => false,
	];
	Altis\register_module( 'sso', __DIR__, 'SSO', $default_settings, __NAMESPACE__ . '\\bootstrap' );
} );
