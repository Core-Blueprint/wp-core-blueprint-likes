<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class PostTypeOverrides {
	/** @param array<string,mixed> $override */
	public static function render( string $slug, array $override ): void {
		$prefix = Settings::OPTION . '[post_type_overrides][' . $slug . ']';
		?>
		<div class="cb-likes-reaction-grid cb-likes-reaction-grid--compact">
			<div>
				<h4><?php esc_html_e( 'Like overrides', 'core-blueprint-likes' ); ?></h4>
				<?php Fields::text( $slug . '_like_label', __( 'Label', 'core-blueprint-likes' ), $prefix . '[like_label]', (string) $override['like_label'], __( 'Inherit global label', 'core-blueprint-likes' ) ); ?>
				<?php Fields::text( $slug . '_liked_label', __( 'Active label', 'core-blueprint-likes' ), $prefix . '[liked_label]', (string) $override['liked_label'], __( 'Inherit global active label', 'core-blueprint-likes' ) ); ?>
				<?php IconField::render( $slug . '_like', $prefix, 'like', (string) $override['like_icon_source'], (string) $override['like_icon_builtin'], (int) $override['like_icon_media_id'], (string) $override['like_icon'], true ); ?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Dislike overrides', 'core-blueprint-likes' ); ?></h4>
				<div class="cb-core-field">
					<label class="cb-core-field__label" for="<?php echo esc_attr( $slug ); ?>_dislike_mode"><?php esc_html_e( 'Dislikes', 'core-blueprint-likes' ); ?></label>
					<div class="cb-core-field__control"><select id="<?php echo esc_attr( $slug ); ?>_dislike_mode" name="<?php echo esc_attr( $prefix ); ?>[dislike_mode]">
						<option value="inherit" <?php selected( $override['dislike_mode'], 'inherit' ); ?>><?php esc_html_e( 'Inherit global setting', 'core-blueprint-likes' ); ?></option>
						<option value="enabled" <?php selected( $override['dislike_mode'], 'enabled' ); ?>><?php esc_html_e( 'Enabled', 'core-blueprint-likes' ); ?></option>
						<option value="disabled" <?php selected( $override['dislike_mode'], 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'core-blueprint-likes' ); ?></option>
					</select></div>
				</div>
				<?php Fields::text( $slug . '_dislike_label', __( 'Label', 'core-blueprint-likes' ), $prefix . '[dislike_label]', (string) $override['dislike_label'], __( 'Inherit global label', 'core-blueprint-likes' ) ); ?>
				<?php Fields::text( $slug . '_disliked_label', __( 'Active label', 'core-blueprint-likes' ), $prefix . '[disliked_label]', (string) $override['disliked_label'], __( 'Inherit global active label', 'core-blueprint-likes' ) ); ?>
				<?php IconField::render( $slug . '_dislike', $prefix, 'dislike', (string) $override['dislike_icon_source'], (string) $override['dislike_icon_builtin'], (int) $override['dislike_icon_media_id'], (string) $override['dislike_icon'], true ); ?>
			</div>

			<section class="cb-likes-visibility-panel cb-likes-visibility-panel--post-type">
				<h4><?php esc_html_e( 'Logged-out visitor overrides', 'core-blueprint-likes' ); ?></h4>
				<div class="cb-core-field">
					<label class="cb-core-field__label" for="<?php echo esc_attr( $slug ); ?>_visibility_mode"><?php esc_html_e( 'Reaction visibility', 'core-blueprint-likes' ); ?></label>
					<div class="cb-core-field__control"><select id="<?php echo esc_attr( $slug ); ?>_visibility_mode" name="<?php echo esc_attr( $prefix ); ?>[visibility_mode]">
						<option value="inherit" <?php selected( $override['visibility_mode'], 'inherit' ); ?>><?php esc_html_e( 'Inherit global setting', 'core-blueprint-likes' ); ?></option>
						<option value="logged_in_only" <?php selected( $override['visibility_mode'], 'logged_in_only' ); ?>><?php esc_html_e( 'Logged-in users only', 'core-blueprint-likes' ); ?></option>
						<option value="show" <?php selected( $override['visibility_mode'], 'show' ); ?>><?php esc_html_e( 'Show to everyone with login notice', 'core-blueprint-likes' ); ?></option>
					</select></div>
				</div>
				<?php Fields::text( $slug . '_logged_out_message', __( 'Logged-out message', 'core-blueprint-likes' ), $prefix . '[logged_out_message]', (string) $override['logged_out_message'], __( 'Inherit global logged-out message', 'core-blueprint-likes' ), __( 'Leave empty to inherit the global message.', 'core-blueprint-likes' ) ); ?>
			</section>
		</div>
		<?php
	}
}
