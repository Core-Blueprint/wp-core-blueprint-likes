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
						?>
						<details class="cb-core-interactive-row cb-likes-post-type<?php echo $enabled ? ' is-enabled' : ''; ?>" data-cb-likes-post-type>
							<summary class="cb-core-interactive-row__summary cb-likes-post-type__summary">
								<span class="cb-likes-post-type__identity"><strong><?php echo esc_html( $name ); ?></strong><code><?php echo esc_html( $slug ); ?></code></span>
								<span class="cb-likes-post-type__actions">
									<span class="cb-core-status cb-likes-post-type__state-enabled" aria-live="polite">
										<span class="cb-core-status__dot cb-core-status__dot--success" aria-hidden="true"></span>
										<span class="cb-core-status__label"><?php esc_html_e( 'Enabled', 'core-blueprint-likes' ); ?></span>
									</span>
									<span class="cb-core-status cb-likes-post-type__state-disabled" aria-live="polite">
										<span class="cb-core-status__dot cb-core-status__dot--muted" aria-hidden="true"></span>
										<span class="cb-core-status__label"><?php esc_html_e( 'Disabled', 'core-blueprint-likes' ); ?></span>
									</span>
									<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[post_types][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $enabled ); ?> aria-label="<?php esc_attr_e( 'Enable reactions for this post type', 'core-blueprint-likes' ); ?>" data-cb-likes-post-type-toggle>
									<?php echo Fields::disclosure_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Base owns icon output. ?>
								</span>
							</summary>
							<div class="cb-likes-post-type__body"><div class="cb-likes-post-type__body-inner"><?php PostTypeOverrides::render( $slug, $override ); ?></div></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="cb-core-actions">
			<button type="submit" class="button cb-core-button cb-core-button--primary"><?php esc_html_e( 'Save settings', 'core-blueprint-likes' ); ?></button>
		</div>
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
