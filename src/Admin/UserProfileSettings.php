<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class UserProfileSettings {
	/** @param array<string,mixed> $settings */
	public static function render( array $settings ): void {
		?>
		<div class="cb-core-card">
			<header class="cb-core-card__header"><h2 class="cb-core-card__title"><?php esc_html_e( 'User profiles', 'core-blueprint-likes' ); ?></h2></header>
			<div class="cb-core-card__body">
				<p class="cb-core-card__lead"><?php esc_html_e( 'Optionally allow Likes to target public WordPress user profiles.', 'core-blueprint-likes' ); ?></p>
				<div class="cb-core-field cb-core-field--enable">
					<label class="cb-core-field__label"><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[users_enabled]" value="1" <?php checked( ! empty( $settings['users_enabled'] ) ); ?>> <?php esc_html_e( 'Enable likes for user profiles', 'core-blueprint-likes' ); ?></label>
					<p class="description"><?php esc_html_e( 'Logged-in users may like other WordPress users. Users cannot like themselves, and dislikes are never available for user profiles.', 'core-blueprint-likes' ); ?></p>
				</div>
			</div>
		</div>
		<?php submit_button( __( 'Save settings', 'core-blueprint-likes' ) ); ?>
		<?php
	}
}
