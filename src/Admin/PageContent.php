<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Core\UI\IntegrationGrid;
use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class PageContent {
	public static function render(): void {
		$settings   = Settings::all();
		$post_types = Settings::available_post_types();
		?>
		<div class="wrap cb-core-wrap cb-core-page cb-likes-settings-page">
			<p class="cb-core-eyebrow"><?php esc_html_e( 'Core Blueprint', 'core-blueprint-likes' ); ?></p>
			<h1 class="cb-core-title"><?php esc_html_e( 'Likes', 'core-blueprint-likes' ); ?></h1>
			<p class="cb-core-intro"><?php esc_html_e( 'Privacy-first likes and optional dislikes for selected WordPress content types and, optionally, user profiles. Reactions are account-based and store no IP address, fingerprint, browser, device or location data.', 'core-blueprint-likes' ); ?></p>

			<nav class="cb-core-tab-wrapper cb-likes-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Likes sections', 'core-blueprint-likes' ); ?>" data-cb-likes-tabs>
				<button type="button" class="nav-tab nav-tab-active" role="tab" aria-selected="true" aria-controls="cb-likes-tab-overview" id="cb-likes-tab-overview-button" data-cb-likes-tab="overview"><?php esc_html_e( 'Overview', 'core-blueprint-likes' ); ?></button>
				<button type="button" class="nav-tab" role="tab" aria-selected="false" aria-controls="cb-likes-tab-general" id="cb-likes-tab-general-button" data-cb-likes-tab="general"><?php esc_html_e( 'General', 'core-blueprint-likes' ); ?></button>
				<button type="button" class="nav-tab" role="tab" aria-selected="false" aria-controls="cb-likes-tab-post-types" id="cb-likes-tab-post-types-button" data-cb-likes-tab="post-types"><?php esc_html_e( 'Post types', 'core-blueprint-likes' ); ?></button>
				<button type="button" class="nav-tab" role="tab" aria-selected="false" aria-controls="cb-likes-tab-user-profiles" id="cb-likes-tab-user-profiles-button" data-cb-likes-tab="user-profiles"><?php esc_html_e( 'User profiles', 'core-blueprint-likes' ); ?></button>
				<button type="button" class="nav-tab" role="tab" aria-selected="false" aria-controls="cb-likes-tab-integrations" id="cb-likes-tab-integrations-button" data-cb-likes-tab="integrations"><?php esc_html_e( 'Integrations', 'core-blueprint-likes' ); ?></button>
			</nav>

			<section id="cb-likes-tab-overview" class="cb-likes-tab-panel" role="tabpanel" aria-labelledby="cb-likes-tab-overview-button" data-cb-likes-tab-panel="overview">
				<?php Overview::render(); ?>
			</section>

			<form method="post" action="options.php" class="cb-likes-settings-form">
				<?php settings_fields( 'cb_likes_settings_group' ); ?>
				<section id="cb-likes-tab-general" class="cb-likes-tab-panel" role="tabpanel" aria-labelledby="cb-likes-tab-general-button" data-cb-likes-tab-panel="general" hidden><?php GeneralSettings::render( $settings['global'] ); ?></section>
				<section id="cb-likes-tab-post-types" class="cb-likes-tab-panel" role="tabpanel" aria-labelledby="cb-likes-tab-post-types-button" data-cb-likes-tab-panel="post-types" hidden><?php PostTypeSettings::render( $settings, $post_types ); ?></section>
				<section id="cb-likes-tab-user-profiles" class="cb-likes-tab-panel" role="tabpanel" aria-labelledby="cb-likes-tab-user-profiles-button" data-cb-likes-tab-panel="user-profiles" hidden><?php UserProfileSettings::render( $settings ); ?></section>
			</form>

			<section id="cb-likes-tab-integrations" class="cb-likes-tab-panel" role="tabpanel" aria-labelledby="cb-likes-tab-integrations-button" data-cb-likes-tab-panel="integrations" hidden>
				<?php echo IntegrationGrid::render( IntegrationReadiness::items() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Base owns escaping and presentation. ?>
				<?php FrontendSetup::render(); ?>
			</section>
		</div>
		<?php
	}
}
