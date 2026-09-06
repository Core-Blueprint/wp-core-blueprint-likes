<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Core\UI\IntegrationGrid;
use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class PageContent {
	private const TAB_OVERVIEW      = 'overview';
	private const TAB_GENERAL       = 'general';
	private const TAB_POST_TYPES    = 'post-types';
	private const TAB_USER_PROFILES = 'user-profiles';
	private const TAB_INTEGRATIONS  = 'integrations';

	public static function render(): void {
		$tab = self::current_tab();
		?>
		<div class="wrap cb-core-wrap cb-core-page cb-likes-settings-page">
			<p class="cb-core-eyebrow"><?php esc_html_e( 'Core Blueprint', 'core-blueprint-likes' ); ?></p>
			<h1 class="cb-core-title"><?php esc_html_e( 'Likes', 'core-blueprint-likes' ); ?></h1>
			<p class="cb-core-intro"><?php esc_html_e( 'Privacy-first likes and optional dislikes for selected WordPress content types and, optionally, user profiles. Reactions are account-based and store no IP address, fingerprint, browser, device or location data.', 'core-blueprint-likes' ); ?></p>

			<?php self::render_tabs( $tab ); ?>

			<?php
			switch ( $tab ) {
				case self::TAB_GENERAL:
				case self::TAB_POST_TYPES:
				case self::TAB_USER_PROFILES:
					self::render_settings_tab( $tab );
					break;
				case self::TAB_INTEGRATIONS:
					echo IntegrationGrid::render( IntegrationReadiness::items() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Base owns escaping and presentation.
					FrontendSetup::render();
					break;
				case self::TAB_OVERVIEW:
				default:
					Overview::render();
					break;
			}
			?>
		</div>
		<?php
	}

	/** @return array<string,string> */
	private static function tabs(): array {
		return [
			self::TAB_OVERVIEW      => __( 'Overview', 'core-blueprint-likes' ),
			self::TAB_GENERAL       => __( 'General', 'core-blueprint-likes' ),
			self::TAB_POST_TYPES    => __( 'Post types', 'core-blueprint-likes' ),
			self::TAB_USER_PROFILES => __( 'User profiles', 'core-blueprint-likes' ),
			self::TAB_INTEGRATIONS  => __( 'Integrations', 'core-blueprint-likes' ),
		];
	}

	private static function current_tab(): string {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( (string) wp_unslash( $_GET['tab'] ) ) : self::TAB_OVERVIEW;
		return array_key_exists( $tab, self::tabs() ) ? $tab : self::TAB_OVERVIEW;
	}

	private static function tab_url( string $tab ): string {
		if ( ! array_key_exists( $tab, self::tabs() ) ) {
			$tab = self::TAB_OVERVIEW;
		}

		return add_query_arg(
			[
				'page' => CoreBlueprintPage::SLUG,
				'tab'  => $tab,
			],
			admin_url( 'admin.php' )
		);
	}

	private static function render_tabs( string $active_tab ): void {
		?>
		<nav class="nav-tab-wrapper cb-core-tab-wrapper cb-likes-tabs" aria-label="<?php echo esc_attr__( 'Likes sections', 'core-blueprint-likes' ); ?>">
			<?php foreach ( self::tabs() as $key => $label ) : ?>
				<a class="nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( self::tab_url( $key ) ); ?>"<?php echo $active_tab === $key ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	private static function render_settings_tab( string $tab ): void {
		$settings   = Settings::all();
		$post_types = Settings::available_post_types();
		?>
		<form method="post" action="options.php" class="cb-likes-settings-form">
			<?php settings_fields( 'cb_likes_settings_group' ); ?>
			<?php
			switch ( $tab ) {
				case self::TAB_POST_TYPES:
					PostTypeSettings::render( $settings, $post_types );
					break;
				case self::TAB_USER_PROFILES:
					UserProfileSettings::render( $settings );
					break;
				case self::TAB_GENERAL:
				default:
					GeneralSettings::render( $settings['global'] );
					break;
			}
			?>
		</form>
		<?php
	}
}
