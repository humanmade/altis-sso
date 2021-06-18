<?php
/**
 * Altis SSO.
 *
 * @package altis/sso
 */

namespace Altis\SSO;

use Altis;

/**
 * Bootstrap the SSO module.
 */
function bootstrap() {
	if ( ! is_sso_active() ) {
		return;
	}

	$config = Altis\get_config()['modules']['sso'];
	if ( $config['saml'] ) {
		SAML\bootstrap();
	}
	if ( $config['wordpress'] ) {
		WordPress\bootstrap();
	}

	add_action( 'login_form', __NAMESPACE__ . '\\output_sso_buttons' );
}

/**
 * Check whether any SSO solution is active.
 *
 * @return bool True if any SSO provider is active, false otherwise.
 */
function is_sso_active() : bool {
	$config = Altis\get_config()['modules']['sso'];
	if ( $config['saml'] || $config['wordpress'] ) {
		return true;
	}
	return false;
}

/**
 * Check whether native (WordPress) login should be hidden.
 *
 * @return bool True if the native login form should be hidden, false otherwise.
 */
function is_native_hidden() : bool {
	$config = Altis\get_config()['modules']['sso'];
	return $config['hide_native'] ?? false;
}

/**
 * Output the single sign-on buttons on the login form.
 *
 * Also handles restyling the form.
 */
function output_sso_buttons() : void {
	$config = Altis\get_config()['modules']['sso'];
	?>
	<div class="altis-sso-options">
		<?php if ( ! is_native_hidden() ) : ?>
			<div class="altis-sso-sep"><?php esc_html_e( 'or', 'altis' ) ?></div>
		<?php endif; ?>

		<?php if ( $config['saml'] ) : ?>
			<?php SAML\render_login_link() ?>
		<?php endif; ?>

		<?php if ( $config['wordpress'] ) : ?>
			<?php WordPress\render_login_link() ?>
		<?php endif; ?>
	</div>
	<style>
		#loginform {
			display: flex;
			flex-direction: column;
		}
		.altis-sso-options {
			order: 100;
			display: flex;
			flex-direction: column;
			align-items: stretch;
			row-gap: 0.5em;
		}
		.altis-sso-sep {
			text-transform: uppercase;
			display: flex;
			height: 1.5em;
			column-gap: 1em;
			margin: 1em 0;
		}
		.altis-sso-sep::before,
		.altis-sso-sep::after {
			display: block;
			content: "";
			border-bottom: 1px solid #f0f0f1;
			flex: 1;
			height: 0.7em;
		}
		.altis-sso-options .button {
			width: 100%;
			text-align: center;
		}

		<?php if ( is_native_hidden() ) : ?>
			#loginform > :first-child,
			#loginform .user-pass-wrap,
			#loginform .forgetmenot,
			#loginform .submit,
			#nav {
				display: none;
			}
		<?php endif; ?>
	</style>
	<?php
}
