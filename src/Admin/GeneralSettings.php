<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class GeneralSettings {
	/** @param array<string,mixed> $global */
	public static function render( array $global ): void {
		?>
		<div class="cb-core-card">
			<header class="cb-core-card__header"><h2 class="cb-core-card__title"><?php esc_html_e( 'Global defaults', 'core-blueprint-likes' ); ?></h2></header>
			<div class="cb-core-card__body">
				<p class="cb-core-card__lead"><?php esc_html_e( 'These settings are used everywhere unless a post type overrides them.', 'core-blueprint-likes' ); ?></p>
				<div class="cb-likes-reaction-grid">
					<section class="cb-likes-reaction-panel">
						<h3><?php esc_html_e( 'Like', 'core-blueprint-likes' ); ?></h3>
						<?php Fields::text( 'global_like_label', __( 'Label', 'core-blueprint-likes' ), Settings::OPTION . '[global][like_label]', (string) $global['like_label'], __( 'Like', 'core-blueprint-likes' ) ); ?>
						<?php Fields::text( 'global_liked_label', __( 'Active label', 'core-blueprint-likes' ), Settings::OPTION . '[global][liked_label]', (string) $global['liked_label'], __( 'Liked', 'core-blueprint-likes' ) ); ?>
						<?php IconField::render( 'global_like', Settings::OPTION . '[global]', 'like', (string) $global['like_icon_source'], (string) $global['like_icon_builtin'], (int) $global['like_icon_media_id'], (string) $global['like_icon'] ); ?>
					</section>
					<section class="cb-likes-reaction-panel">
						<h3><?php esc_html_e( 'Dislike', 'core-blueprint-likes' ); ?></h3>
						<div class="cb-core-field cb-core-field--enable"><label class="cb-core-field__label"><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[global][dislike_enabled]" value="1" <?php checked( ! empty( $global['dislike_enabled'] ) ); ?>> <?php esc_html_e( 'Enable dislikes by default for enabled post types', 'core-blueprint-likes' ); ?></label><p class="description"><?php esc_html_e( 'Each post type can inherit, enable or disable dislikes independently.', 'core-blueprint-likes' ); ?></p></div>
						<?php Fields::text( 'global_dislike_label', __( 'Label', 'core-blueprint-likes' ), Settings::OPTION . '[global][dislike_label]', (string) $global['dislike_label'], __( 'Dislike', 'core-blueprint-likes' ) ); ?>
						<?php Fields::text( 'global_disliked_label', __( 'Active label', 'core-blueprint-likes' ), Settings::OPTION . '[global][disliked_label]', (string) $global['disliked_label'], __( 'Disliked', 'core-blueprint-likes' ) ); ?>
						<?php IconField::render( 'global_dislike', Settings::OPTION . '[global]', 'dislike', (string) $global['dislike_icon_source'], (string) $global['dislike_icon_builtin'], (int) $global['dislike_icon_media_id'], (string) $global['dislike_icon'] ); ?>
					</section>
				</div>
				<hr class="cb-core-divider">
				<section class="cb-likes-visibility-panel">
					<h3><?php esc_html_e( 'Logged-out visitors', 'core-blueprint-likes' ); ?></h3>
					<div class="cb-core-field cb-core-field--enable"><label class="cb-core-field__label"><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[global][logged_in_only]" value="1" <?php checked( ! empty( $global['logged_in_only'] ) ); ?>> <?php esc_html_e( 'Show reactions only for logged-in users', 'core-blueprint-likes' ); ?></label><p class="description"><?php esc_html_e( 'When disabled, logged-out visitors may see reactions but must log in before reacting.', 'core-blueprint-likes' ); ?></p></div>
					<?php Fields::text( 'global_logged_out_message', __( 'Logged-out message', 'core-blueprint-likes' ), Settings::OPTION . '[global][logged_out_message]', (string) $global['logged_out_message'], __( 'Please log in to react to this content.', 'core-blueprint-likes' ), __( 'Shown when a logged-out visitor clicks a visible reaction button.', 'core-blueprint-likes' ) ); ?>
				</section>
			</div>
		</div>
		<?php submit_button( __( 'Save settings', 'core-blueprint-likes' ) ); ?>
		<?php
	}
}
