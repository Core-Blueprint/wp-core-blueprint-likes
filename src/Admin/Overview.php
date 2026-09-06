<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Repository;
use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class Overview {
	public static function render(): void {
		$enabled_targets = count( Settings::enabled_post_types() ) + ( Settings::users_enabled() ? 1 : 0 );
		?>
		<div class="cb-core-tiles cb-likes-overview" aria-label="<?php echo esc_attr__( 'Likes at a glance', 'core-blueprint-likes' ); ?>">
			<div class="cb-core-tile cb-core-tile--metric cb-core-tile--neutral">
				<span class="cb-core-tile__label"><?php esc_html_e( 'Likes', 'core-blueprint-likes' ); ?></span>
				<strong class="cb-core-tile__value"><?php echo esc_html( number_format_i18n( Repository::total_count( Repository::LIKE ) ) ); ?></strong>
			</div>
			<div class="cb-core-tile cb-core-tile--metric cb-core-tile--neutral">
				<span class="cb-core-tile__label"><?php esc_html_e( 'Dislikes', 'core-blueprint-likes' ); ?></span>
				<strong class="cb-core-tile__value"><?php echo esc_html( number_format_i18n( Repository::total_count( Repository::DISLIKE ) ) ); ?></strong>
			</div>
			<div class="cb-core-tile cb-core-tile--metric cb-core-tile--neutral">
				<span class="cb-core-tile__label"><?php esc_html_e( 'Users who reacted', 'core-blueprint-likes' ); ?></span>
				<strong class="cb-core-tile__value"><?php echo esc_html( number_format_i18n( Repository::unique_user_count() ) ); ?></strong>
			</div>
			<div class="cb-core-tile cb-core-tile--metric cb-core-tile--neutral">
				<span class="cb-core-tile__label"><?php esc_html_e( 'Enabled target types', 'core-blueprint-likes' ); ?></span>
				<strong class="cb-core-tile__value"><?php echo esc_html( number_format_i18n( $enabled_targets ) ); ?></strong>
			</div>
		</div>
		<div class="cb-core-card cb-likes-overview-card">
			<header class="cb-core-card__header"><h2 class="cb-core-card__title"><?php esc_html_e( 'Reaction model', 'core-blueprint-likes' ); ?></h2></header>
			<div class="cb-core-card__body">
				<p class="cb-core-card__lead"><?php esc_html_e( 'Likes stores account-based reactions only. No IP address, fingerprint, browser, device or location data is stored.', 'core-blueprint-likes' ); ?></p>
				<p><?php esc_html_e( 'Use General for global behavior, Post types for target-specific overrides, User profiles for member reactions, and Integrations for builder and shortcode usage.', 'core-blueprint-likes' ); ?></p>
			</div>
		</div>
		<?php
	}
}
