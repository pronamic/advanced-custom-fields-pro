<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included template file.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$acf_opt_in_privacy_link = sprintf(
	'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
	esc_url( 'https://wpengine.com/legal/privacy/' ),
	esc_html__( 'Privacy Policy', 'acf' )
);

$acf_opt_in_fine_print = sprintf(
	/* translators: %s - Privacy Policy link, opens in a new tab. */
	__( 'We respect your inbox. Unsubscribe anytime. By signing up, you agree to our %s.', 'acf' ),
	$acf_opt_in_privacy_link
);

?>
<div id="acf-email-opt-in-banner-wrap" class="acf-email-opt-in-banner-wrap" style="display: none;">
	<section
		id="acf-email-opt-in-banner"
		class="acf-email-opt-in-banner"
		aria-labelledby="acf-email-opt-in-banner-heading"
	>
		<div class="acf-email-opt-in-banner__content">
			<h2 id="acf-email-opt-in-banner-heading" class="acf-email-opt-in-banner__heading">
				<?php esc_html_e( 'Join the ACF community.', 'acf' ); ?>
			</h2>
			<p class="acf-email-opt-in-banner__body">
				<?php esc_html_e( 'Get critical ACF security updates, releases, news, and workflow improvements.', 'acf' ); ?>
			</p>
			<hr class="acf-email-opt-in-banner__divider" />
			<p class="acf-email-opt-in-banner__fine-print">
				<?php echo acf_esc_html( $acf_opt_in_fine_print ); ?>
			</p>
		</div>

		<div class="acf-email-opt-in-banner__states">
			<div class="acf-email-opt-in-banner__form">
				<div class="acf-email-opt-in-banner__form-row">
					<label for="acf-email-opt-in-banner-email" class="screen-reader-text">
						<?php esc_html_e( 'Email address', 'acf' ); ?>
					</label>
					<div class="acf-email-opt-in-banner__input-wrap">
						<span class="acf-email-opt-in-banner__input-icon" aria-hidden="true"></span>
						<input
							type="email"
							id="acf-email-opt-in-banner-email"
							class="acf-email-opt-in-banner__input"
							name="email"
							placeholder="<?php esc_attr_e( 'you@example.com', 'acf' ); ?>"
							autocomplete="email"
							maxlength="254"
							aria-describedby="acf-email-opt-in-banner-error"
						/>
					</div>
					<button type="button" class="acf-btn acf-email-opt-in-banner__submit">
						<?php esc_html_e( 'Join the list', 'acf' ); ?>
					</button>
				</div>
				<p
					id="acf-email-opt-in-banner-error"
					class="acf-email-opt-in-banner__error"
					role="alert"
					hidden
				>
					<span
						class="acf-email-opt-in-banner__error-icon"
						aria-hidden="true"
					></span>
					<span class="acf-email-opt-in-banner__error-text"></span>
				</p>
			</div>

			<div
				class="acf-email-opt-in-banner__success"
				role="status"
				aria-live="polite"
				hidden
			>
				<span class="acf-email-opt-in-banner__success-icon" aria-hidden="true"></span>
				<span class="acf-email-opt-in-banner__success-text">
					<?php esc_html_e( "You're on the list! Keep an eye out for our next update.", 'acf' ); ?>
				</span>
			</div>
		</div>

		<button
			type="button"
			class="acf-email-opt-in-banner__dismiss"
			aria-label="<?php esc_attr_e( 'Dismiss email opt-in banner', 'acf' ); ?>"
		>
			<span class="acf-email-opt-in-banner__dismiss-icon" aria-hidden="true"></span>
		</button>
	</section>
</div>
