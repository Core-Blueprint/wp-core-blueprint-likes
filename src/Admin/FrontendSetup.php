<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

defined( 'ABSPATH' ) || exit;

final class FrontendSetup {
	public static function render(): void {
		?>
		<div class="cb-core-card">
			<header class="cb-core-card__header"><h2 class="cb-core-card__title"><?php esc_html_e( 'Bricks Builder', 'core-blueprint-likes' ); ?></h2></header>
			<div class="cb-core-card__body">
				<p class="cb-core-card__lead"><?php esc_html_e( 'Bricks is optional. When active, Likes exposes builder-native data and interaction contracts without moving reaction logic into the builder.', 'core-blueprint-likes' ); ?></p>
				<ul class="cb-likes-capability-list">
					<li><strong><?php esc_html_e( 'Elements:', 'core-blueprint-likes' ); ?></strong> <?php esc_html_e( 'Like and Dislike.', 'core-blueprint-likes' ); ?></li>
					<li><strong><?php esc_html_e( 'Dynamic data:', 'core-blueprint-likes' ); ?></strong> <?php esc_html_e( 'Like count, Dislike count, current user has liked, and current user has disliked.', 'core-blueprint-likes' ); ?></li>
					<li><strong><?php esc_html_e( 'Conditions:', 'core-blueprint-likes' ); ?></strong> <?php esc_html_e( 'Current user has liked or disliked the current target.', 'core-blueprint-likes' ); ?></li>
					<li><strong><?php esc_html_e( 'Query Loops:', 'core-blueprint-likes' ); ?></strong> <?php esc_html_e( 'Posts liked by the current user and most liked posts.', 'core-blueprint-likes' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Dedicated elements use the current template or Query Loop target by default. A specific content or user target is available only as an advanced override.', 'core-blueprint-likes' ); ?></p>
			</div>
		</div>

		<div class="cb-core-card cb-likes-shortcodes-card">
			<header class="cb-core-card__header"><h2 class="cb-core-card__title"><?php esc_html_e( 'Shortcodes', 'core-blueprint-likes' ); ?></h2></header>
			<div class="cb-core-card__body">
				<p class="cb-core-card__lead"><?php esc_html_e( 'Shortcodes remain available for builder-neutral templates and content.', 'core-blueprint-likes' ); ?></p>
				<div class="cb-likes-shortcode-list">
					<?php self::shortcode( '[cb_like_button]', __( 'Like button for the current target.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode( '[cb_like_count]', __( 'Like count for the current target.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode( '[cb_dislike_button]', __( 'Dislike button when dislikes are enabled for the current post type.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode( '[cb_dislike_count]', __( 'Dislike count when dislikes are enabled for the current post type.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode( '[cb_like_button target_type="user" target_id="123"]', __( 'Specific user target when user profile likes are enabled.', 'core-blueprint-likes' ) ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	private static function shortcode( string $shortcode, string $description ): void {
		?>
		<div class="cb-likes-shortcode-row">
			<div class="cb-likes-shortcode-copy-group">
				<code><?php echo esc_html( $shortcode ); ?></code>
				<button type="button" class="button button-secondary button-small cb-likes-copy-shortcode" data-cb-likes-copy-shortcode data-cb-likes-shortcode="<?php echo esc_attr( $shortcode ); ?>" data-cb-likes-copy-label="<?php esc_attr_e( 'Copy shortcode', 'core-blueprint-likes' ); ?>" data-cb-likes-copy-success="<?php esc_attr_e( 'Shortcode copied.', 'core-blueprint-likes' ); ?>" aria-label="<?php esc_attr_e( 'Copy shortcode', 'core-blueprint-likes' ); ?>"><?php esc_html_e( 'Copy', 'core-blueprint-likes' ); ?></button>
			</div>
			<p>— <?php echo esc_html( $description ); ?></p>
		</div>
		<?php
	}
}
