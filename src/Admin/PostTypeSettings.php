<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class PostTypeSettings {
	/**
	 * @param array<string,mixed> $settings
	 * @param array<string,\WP_Post_Type> $post_types
	 */
	public static function render( array $settings, array $post_types ): void {
		?>
		<div class="cb-core-card cb-likes-section-card">
			<header class="cb-core-card__header"><h2 class="cb-core-card__title"><?php esc_html_e( 'Post types', 'core-blueprint-likes' ); ?></h2></header>
			<div class="cb-core-card__body">
				<p class="cb-core-card__lead"><?php esc_html_e( 'Choose which public post types may receive reactions. Expand a row only when that post type needs its own presentation or visibility rules.', 'core-blueprint-likes' ); ?></p>
				<div class="cb-likes-post-types">
					<?php foreach ( $post_types as $slug => $object ) :
						$override = $settings['post_type_overrides'][ $slug ] ?? self::default_override();
						$name     = (string) ( $object->labels->name ?? $slug );
						$enabled  = in_array( $slug, $settings['post_types'] ?? [], true );
						$body_id  = 'cb-likes-post-type-' . $slug . '-body';
						?>
						<section class="cb-likes-post-type<?php echo $enabled ? ' is-enabled' : ''; ?>" data-cb-likes-post-type>
							<header class="cb-likes-post-type__header">
								<div class="cb-likes-post-type__identity"><strong><?php echo esc_html( $name ); ?></strong><code><?php echo esc_html( $slug ); ?></code></div>
								<div class="cb-likes-post-type__actions">
									<span class="cb-likes-post-type__state" aria-live="polite"><span class="cb-likes-post-type__state-enabled"><?php esc_html_e( 'Enabled', 'core-blueprint-likes' ); ?></span><span class="cb-likes-post-type__state-disabled"><?php esc_html_e( 'Disabled', 'core-blueprint-likes' ); ?></span></span>
									<label class="cb-core-rack-toggle cb-likes-post-type__toggle">
										<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[post_types][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $enabled ); ?> data-cb-likes-post-type-toggle>
										<span class="cb-core-rack-toggle-track" aria-hidden="true"><span class="cb-core-rack-toggle-thumb"></span></span>
										<span class="screen-reader-text"><?php esc_html_e( 'Enable reactions for this post type', 'core-blueprint-likes' ); ?></span>
									</label>
									<button type="button" class="cb-core-module-collapse cb-likes-post-type__collapse" aria-expanded="false" aria-controls="<?php echo esc_attr( $body_id ); ?>" aria-label="<?php esc_attr_e( 'Toggle post type settings', 'core-blueprint-likes' ); ?>" data-cb-likes-post-type-collapse><?php echo Fields::disclosure_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Base owns icon output. ?></button>
								</div>
							</header>
							<div id="<?php echo esc_attr( $body_id ); ?>" class="cb-likes-post-type__body" aria-hidden="true" inert data-cb-likes-post-type-body><div class="cb-likes-post-type__body-inner"><?php PostTypeOverrides::render( $slug, $override ); ?></div></div>
						</section>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php submit_button( __( 'Save settings', 'core-blueprint-likes' ) ); ?>
		<?php
	}

	/** @return array<string,mixed> */
	private static function default_override(): array {
		return [
			'like_label' => '', 'liked_label' => '', 'like_icon_source' => 'inherit', 'like_icon_builtin' => 'heart', 'like_icon_media_id' => 0, 'like_icon' => '',
			'dislike_mode' => 'inherit', 'dislike_label' => '', 'disliked_label' => '', 'dislike_icon_source' => 'inherit', 'dislike_icon_builtin' => 'thumbs-down', 'dislike_icon_media_id' => 0, 'dislike_icon' => '',
			'visibility_mode' => 'inherit', 'logged_out_message' => '',
		];
	}
}
