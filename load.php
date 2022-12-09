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
		'hide_native' => false,
	];
	$options = [
		'defaults' => $default_settings,
	];
	Altis\register_module( 'sso', __DIR__, 'SSO', $options, __NAMESPACE__ . '\\bootstrap' );
} );
