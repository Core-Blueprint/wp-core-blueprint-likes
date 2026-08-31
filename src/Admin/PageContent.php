<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Icons;
use CB\Likes\Repository;
use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

final class PageContent {
	public static function render( bool $core_presentation = true ): void {
		$settings   = Settings::all();
		$post_types = Settings::available_post_types();
		$global     = $settings['global'];
		$wrap_class = $core_presentation ? 'wrap cb-core-wrap' : 'wrap cb-likes-native';
		$tabs_class = $core_presentation ? 'cb-core-tab-wrapper cb-likes-tabs' : 'nav-tab-wrapper wp-clearfix cb-likes-tabs';
		?>
		<div class="<?php echo esc_attr( $wrap_class ); ?>">
			<h1<?php echo $core_presentation ? ' class="cb-core-title"' : ''; ?>><?php esc_html_e( 'Likes', 'core-blueprint-likes' ); ?></h1>
			<?php if ( ! $core_presentation ) : ?>
				<?php settings_errors(); ?>
			<?php endif; ?>
			<p<?php echo $core_presentation ? ' class="cb-core-intro"' : ' class="description"'; ?>><?php esc_html_e( 'Privacy-first likes and optional dislikes for selected WordPress content types and, optionally, user profiles. Reactions are always account-based and store no IP address, fingerprint, browser, device or location data.', 'core-blueprint-likes' ); ?></p>
			<ul class="<?php echo esc_attr( $core_presentation ? 'cb-core-meta' : 'cb-likes-native-meta' ); ?>">
				<li class="<?php echo esc_attr( $core_presentation ? 'cb-core-meta__item' : 'cb-likes-native-meta__item' ); ?>"><strong><?php echo esc_html( (string) Repository::total_count( Repository::LIKE ) ); ?></strong> <?php esc_html_e( 'likes', 'core-blueprint-likes' ); ?></li>
				<li class="<?php echo esc_attr( $core_presentation ? 'cb-core-meta__item' : 'cb-likes-native-meta__item' ); ?>"><strong><?php echo esc_html( (string) Repository::total_count( Repository::DISLIKE ) ); ?></strong> <?php esc_html_e( 'dislikes', 'core-blueprint-likes' ); ?></li>
				<li class="<?php echo esc_attr( $core_presentation ? 'cb-core-meta__item' : 'cb-likes-native-meta__item' ); ?>"><strong><?php echo esc_html( (string) Repository::unique_user_count() ); ?></strong> <?php esc_html_e( 'users who reacted', 'core-blueprint-likes' ); ?></li>
			</ul>

			<nav class="<?php echo esc_attr( $tabs_class ); ?>" role="tablist" aria-label="<?php esc_attr_e( 'Likes settings', 'core-blueprint-likes' ); ?>" data-cb-likes-tabs>
				<button type="button" class="nav-tab nav-tab-active" role="tab" aria-selected="true" aria-controls="cb-likes-tab-general" id="cb-likes-tab-general-button" data-cb-likes-tab="general"><?php esc_html_e( 'General', 'core-blueprint-likes' ); ?></button>
				<button type="button" class="nav-tab" role="tab" aria-selected="false" aria-controls="cb-likes-tab-post-types" id="cb-likes-tab-post-types-button" data-cb-likes-tab="post-types"><?php esc_html_e( 'Post types', 'core-blueprint-likes' ); ?></button>
				<button type="button" class="nav-tab" role="tab" aria-selected="false" aria-controls="cb-likes-tab-user-profiles" id="cb-likes-tab-user-profiles-button" data-cb-likes-tab="user-profiles"><?php esc_html_e( 'User profiles', 'core-blueprint-likes' ); ?></button>
				<button type="button" class="nav-tab" role="tab" aria-selected="false" aria-controls="cb-likes-tab-usage" id="cb-likes-tab-usage-button" data-cb-likes-tab="usage"><?php esc_html_e( 'Usage', 'core-blueprint-likes' ); ?></button>
			</nav>

			<form method="post" action="options.php" class="cb-likes-settings-form">
				<?php settings_fields( 'cb_likes_settings_group' ); ?>

				<section id="cb-likes-tab-general" class="cb-likes-tab-panel" role="tabpanel" aria-labelledby="cb-likes-tab-general-button" data-cb-likes-tab-panel="general">
				<div class="<?php echo esc_attr( $core_presentation ? 'cb-core-card' : 'cb-likes-native-section' ); ?>">
					<header<?php echo $core_presentation ? ' class="cb-core-card__header"' : ''; ?>>
						<h2<?php echo $core_presentation ? ' class="cb-core-card__title"' : ''; ?>><?php esc_html_e( 'Global defaults', 'core-blueprint-likes' ); ?></h2>
					</header>
					<div<?php echo $core_presentation ? ' class="cb-core-card__body"' : ''; ?>>
						<p<?php echo $core_presentation ? ' class="cb-core-card__lead"' : ''; ?>><?php esc_html_e( 'These settings are used everywhere unless a post type overrides them. Leave a label or message empty to use its translated default, and choose where each reaction icon comes from.', 'core-blueprint-likes' ); ?></p>
						<div class="cb-likes-reaction-grid">
							<section class="cb-likes-reaction-panel">
								<h3><?php esc_html_e( 'Like', 'core-blueprint-likes' ); ?></h3>
								<?php self::text_field( 'global_like_label', __( 'Label', 'core-blueprint-likes' ), Settings::OPTION . '[global][like_label]', $global['like_label'], __( 'Like', 'core-blueprint-likes' ) ); ?>
								<?php self::text_field( 'global_liked_label', __( 'Active label', 'core-blueprint-likes' ), Settings::OPTION . '[global][liked_label]', $global['liked_label'], __( 'Liked', 'core-blueprint-likes' ) ); ?>
								<?php self::icon_field( 'global_like', Settings::OPTION . '[global]', 'like', $global['like_icon_source'], $global['like_icon_builtin'], $global['like_icon_media_id'], $global['like_icon'] ); ?>
							</section>

							<section class="cb-likes-reaction-panel">
								<h3><?php esc_html_e( 'Dislike', 'core-blueprint-likes' ); ?></h3>
								<div class="cb-core-field cb-core-field--enable">
									<label class="cb-core-field__label"><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[global][dislike_enabled]" value="1" <?php checked( $global['dislike_enabled'] ); ?>> <?php esc_html_e( 'Enable dislikes by default for enabled post types', 'core-blueprint-likes' ); ?></label>
									<p class="description"><?php esc_html_e( 'Each post type can inherit, enable or disable dislikes independently.', 'core-blueprint-likes' ); ?></p>
								</div>
								<?php self::text_field( 'global_dislike_label', __( 'Label', 'core-blueprint-likes' ), Settings::OPTION . '[global][dislike_label]', $global['dislike_label'], __( 'Dislike', 'core-blueprint-likes' ) ); ?>
								<?php self::text_field( 'global_disliked_label', __( 'Active label', 'core-blueprint-likes' ), Settings::OPTION . '[global][disliked_label]', $global['disliked_label'], __( 'Disliked', 'core-blueprint-likes' ) ); ?>
								<?php self::icon_field( 'global_dislike', Settings::OPTION . '[global]', 'dislike', $global['dislike_icon_source'], $global['dislike_icon_builtin'], $global['dislike_icon_media_id'], $global['dislike_icon'] ); ?>
							</section>
						</div>

						<hr class="cb-core-divider">
						<section class="cb-likes-visibility-panel">
							<h3><?php esc_html_e( 'Logged-out visitors', 'core-blueprint-likes' ); ?></h3>
							<div class="cb-core-field cb-core-field--enable">
								<label class="cb-core-field__label"><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[global][logged_in_only]" value="1" <?php checked( $global['logged_in_only'] ); ?>> <?php esc_html_e( 'Show reactions only for logged-in users', 'core-blueprint-likes' ); ?></label>
								<p class="description"><?php esc_html_e( 'When enabled, logged-out visitors do not see reaction buttons or counts. When disabled, they can see reactions but must log in before reacting.', 'core-blueprint-likes' ); ?></p>
							</div>
							<?php self::text_field(
								'global_logged_out_message',
								__( 'Logged-out message', 'core-blueprint-likes' ),
								Settings::OPTION . '[global][logged_out_message]',
								$global['logged_out_message'],
								__( 'Please log in to react to this content.', 'core-blueprint-likes' ),
								__( 'Shown when a logged-out visitor clicks a visible Like or Dislike button.', 'core-blueprint-likes' )
							); ?>
						</section>
					</div>
				</div>
				<?php submit_button( __( 'Save settings', 'core-blueprint-likes' ) ); ?>
				</section>

				<section id="cb-likes-tab-post-types" class="cb-likes-tab-panel" role="tabpanel" aria-labelledby="cb-likes-tab-post-types-button" data-cb-likes-tab-panel="post-types" hidden>
				<div class="<?php echo esc_attr( $core_presentation ? 'cb-core-card cb-likes-section-card' : 'cb-likes-native-section cb-likes-section-card' ); ?>">
					<header<?php echo $core_presentation ? ' class="cb-core-card__header"' : ''; ?>>
						<h2<?php echo $core_presentation ? ' class="cb-core-card__title"' : ''; ?>><?php esc_html_e( 'Post types', 'core-blueprint-likes' ); ?></h2>
					</header>
					<div<?php echo $core_presentation ? ' class="cb-core-card__body"' : ''; ?>>
						<p><?php esc_html_e( 'Choose which public post types may receive reactions. Optional overrides let each post type use its own wording, icons, dislike setting and logged-out visibility.', 'core-blueprint-likes' ); ?></p>
						<div class="cb-likes-post-types">
						<?php foreach ( $post_types as $slug => $object ) :
							$override = $settings['post_type_overrides'][ $slug ] ?? [
								'like_label' => '', 'liked_label' => '', 'like_icon_source' => 'inherit', 'like_icon_builtin' => 'heart', 'like_icon_media_id' => 0, 'like_icon' => '', 'dislike_mode' => 'inherit',
								'dislike_label' => '', 'disliked_label' => '', 'dislike_icon_source' => 'inherit', 'dislike_icon_builtin' => 'thumbs-down', 'dislike_icon_media_id' => 0, 'dislike_icon' => '',
								'visibility_mode' => 'inherit', 'logged_out_message' => '',
							];
							$name    = (string) ( $object->labels->name ?? $slug );
							$enabled = in_array( $slug, $settings['post_types'], true );
							$body_id = 'cb-likes-post-type-' . $slug . '-body';
							?>
							<section class="cb-likes-post-type<?php echo $enabled ? ' is-enabled' : ''; ?>" data-cb-likes-post-type>
								<header class="cb-likes-post-type__header">
									<div class="cb-likes-post-type__identity">
										<strong><?php echo esc_html( $name ); ?></strong>
										<code><?php echo esc_html( $slug ); ?></code>
									</div>
									<div class="cb-likes-post-type__actions">
										<span class="cb-likes-post-type__state" aria-live="polite">
											<span class="cb-likes-post-type__state-enabled"><?php esc_html_e( 'Enabled', 'core-blueprint-likes' ); ?></span>
											<span class="cb-likes-post-type__state-disabled"><?php esc_html_e( 'Disabled', 'core-blueprint-likes' ); ?></span>
										</span>
									<label class="<?php echo esc_attr( $core_presentation ? 'cb-core-rack-toggle cb-likes-post-type__toggle' : 'cb-likes-post-type__toggle cb-likes-post-type__toggle--native' ); ?>">
										<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[post_types][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $enabled ); ?> data-cb-likes-post-type-toggle>
										<?php if ( $core_presentation ) : ?><span class="cb-core-rack-toggle-track" aria-hidden="true"><span class="cb-core-rack-toggle-thumb"></span></span><?php endif; ?>
										<span class="screen-reader-text"><?php esc_html_e( 'Enable reactions for this post type', 'core-blueprint-likes' ); ?></span>
									</label>
									<button type="button" class="<?php echo esc_attr( $core_presentation ? 'cb-core-module-collapse cb-likes-post-type__collapse' : 'button cb-likes-post-type__collapse' ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $body_id ); ?>" aria-label="<?php esc_attr_e( 'Toggle post type settings', 'core-blueprint-likes' ); ?>" data-cb-likes-post-type-collapse>
										<?php echo self::disclosure_icon( $core_presentation ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted Foundation/Dashicons markup. ?>
									</button>
									</div>
								</header>
								<div id="<?php echo esc_attr( $body_id ); ?>" class="cb-likes-post-type__body" aria-hidden="true" inert data-cb-likes-post-type-body>
									<div class="cb-likes-post-type__body-inner">
										<div class="cb-likes-reaction-grid cb-likes-reaction-grid--compact">
											<div>
												<h4><?php esc_html_e( 'Like overrides', 'core-blueprint-likes' ); ?></h4>
												<?php self::text_field( $slug . '_like_label', __( 'Label', 'core-blueprint-likes' ), Settings::OPTION . '[post_type_overrides][' . $slug . '][like_label]', $override['like_label'], __( 'Inherit global label', 'core-blueprint-likes' ) ); ?>
												<?php self::text_field( $slug . '_liked_label', __( 'Active label', 'core-blueprint-likes' ), Settings::OPTION . '[post_type_overrides][' . $slug . '][liked_label]', $override['liked_label'], __( 'Inherit global active label', 'core-blueprint-likes' ) ); ?>
												<?php self::icon_field( $slug . '_like', Settings::OPTION . '[post_type_overrides][' . $slug . ']', 'like', $override['like_icon_source'], $override['like_icon_builtin'], $override['like_icon_media_id'], $override['like_icon'], true ); ?>
											</div>
											<div>
												<h4><?php esc_html_e( 'Dislike overrides', 'core-blueprint-likes' ); ?></h4>
												<div class="cb-core-field">
													<label class="cb-core-field__label" for="<?php echo esc_attr( $slug ); ?>_dislike_mode"><?php esc_html_e( 'Dislikes', 'core-blueprint-likes' ); ?></label>
													<div class="cb-core-field__control">
														<select id="<?php echo esc_attr( $slug ); ?>_dislike_mode" name="<?php echo esc_attr( Settings::OPTION ); ?>[post_type_overrides][<?php echo esc_attr( $slug ); ?>][dislike_mode]">
															<option value="inherit" <?php selected( $override['dislike_mode'], 'inherit' ); ?>><?php esc_html_e( 'Inherit global setting', 'core-blueprint-likes' ); ?></option>
															<option value="enabled" <?php selected( $override['dislike_mode'], 'enabled' ); ?>><?php esc_html_e( 'Enabled', 'core-blueprint-likes' ); ?></option>
															<option value="disabled" <?php selected( $override['dislike_mode'], 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'core-blueprint-likes' ); ?></option>
														</select>
													</div>
												</div>
												<?php self::text_field( $slug . '_dislike_label', __( 'Label', 'core-blueprint-likes' ), Settings::OPTION . '[post_type_overrides][' . $slug . '][dislike_label]', $override['dislike_label'], __( 'Inherit global label', 'core-blueprint-likes' ) ); ?>
												<?php self::text_field( $slug . '_disliked_label', __( 'Active label', 'core-blueprint-likes' ), Settings::OPTION . '[post_type_overrides][' . $slug . '][disliked_label]', $override['disliked_label'], __( 'Inherit global active label', 'core-blueprint-likes' ) ); ?>
												<?php self::icon_field( $slug . '_dislike', Settings::OPTION . '[post_type_overrides][' . $slug . ']', 'dislike', $override['dislike_icon_source'], $override['dislike_icon_builtin'], $override['dislike_icon_media_id'], $override['dislike_icon'], true ); ?>
											</div>
											<section class="cb-likes-visibility-panel cb-likes-visibility-panel--post-type">
												<h4><?php esc_html_e( 'Logged-out visitor overrides', 'core-blueprint-likes' ); ?></h4>
												<div class="cb-core-field">
													<label class="cb-core-field__label" for="<?php echo esc_attr( $slug ); ?>_visibility_mode"><?php esc_html_e( 'Reaction visibility', 'core-blueprint-likes' ); ?></label>
													<div class="cb-core-field__control">
														<select id="<?php echo esc_attr( $slug ); ?>_visibility_mode" name="<?php echo esc_attr( Settings::OPTION ); ?>[post_type_overrides][<?php echo esc_attr( $slug ); ?>][visibility_mode]">
															<option value="inherit" <?php selected( $override['visibility_mode'], 'inherit' ); ?>><?php esc_html_e( 'Inherit global setting', 'core-blueprint-likes' ); ?></option>
															<option value="logged_in_only" <?php selected( $override['visibility_mode'], 'logged_in_only' ); ?>><?php esc_html_e( 'Logged-in users only', 'core-blueprint-likes' ); ?></option>
															<option value="show" <?php selected( $override['visibility_mode'], 'show' ); ?>><?php esc_html_e( 'Show to everyone with login notice', 'core-blueprint-likes' ); ?></option>
														</select>
													</div>
												</div>
												<?php self::text_field(
													$slug . '_logged_out_message',
													__( 'Logged-out message', 'core-blueprint-likes' ),
													Settings::OPTION . '[post_type_overrides][' . $slug . '][logged_out_message]',
													$override['logged_out_message'],
													__( 'Inherit global logged-out message', 'core-blueprint-likes' ),
													__( 'Leave empty to inherit the global message.', 'core-blueprint-likes' )
												); ?>
											</section>
										</div>
									</div>
								</div>
							</section>
						<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php submit_button( __( 'Save settings', 'core-blueprint-likes' ) ); ?>
				</section>

				<section id="cb-likes-tab-user-profiles" class="<?php echo esc_attr( $core_presentation ? 'cb-likes-tab-panel cb-core-section cb-likes-simple-section' : 'cb-likes-tab-panel cb-likes-simple-section' ); ?>" role="tabpanel" aria-labelledby="cb-likes-tab-user-profiles-button" data-cb-likes-tab-panel="user-profiles" hidden>
					<h2<?php echo $core_presentation ? ' class="cb-core-section-title"' : ''; ?>><?php esc_html_e( 'User targets', 'core-blueprint-likes' ); ?></h2>
					<div class="cb-core-field cb-core-field--enable">
						<label class="cb-core-field__label"><input type="checkbox" name="<?php echo esc_attr( Settings::OPTION ); ?>[users_enabled]" value="1" <?php checked( $settings['users_enabled'] ); ?>> <?php esc_html_e( 'Enable likes for user profiles', 'core-blueprint-likes' ); ?></label>
						<p class="description"><?php esc_html_e( 'Allow logged-in users to like other WordPress users. This is useful for public member/profile pages, for example with Core Blueprint Profiles. Users cannot like themselves. Dislikes are not available for user profiles.', 'core-blueprint-likes' ); ?></p>
					</div>
					<?php submit_button( __( 'Save settings', 'core-blueprint-likes' ) ); ?>
				</section>
			</form>

			<section id="cb-likes-tab-usage" class="<?php echo esc_attr( $core_presentation ? 'cb-likes-tab-panel cb-core-section cb-likes-simple-section cb-likes-usage' : 'cb-likes-tab-panel cb-likes-simple-section cb-likes-usage' ); ?>" role="tabpanel" aria-labelledby="cb-likes-tab-usage-button" data-cb-likes-tab-panel="usage" hidden>
				<h2<?php echo $core_presentation ? ' class="cb-core-section-title"' : ''; ?>><?php esc_html_e( 'Frontend usage', 'core-blueprint-likes' ); ?></h2>
				<div class="cb-likes-shortcode-list">
					<?php self::shortcode_example( '[cb_like_button]', __( 'like button for the current target.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode_example( '[cb_like_count]', __( 'like count for the current target.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode_example( '[cb_dislike_button]', __( 'dislike button when dislikes are enabled for the current post type.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode_example( '[cb_dislike_count]', __( 'dislike count when dislikes are enabled for the current post type.', 'core-blueprint-likes' ) ); ?>
					<?php self::shortcode_example( '[cb_like_button target_type="user" target_id="123"]', __( 'explicit user target when user likes are enabled.', 'core-blueprint-likes' ) ); ?>
				</div>
			</section>
		</div>
		<?php
	}

	private static function shortcode_example( string $shortcode, string $description ): void {
		$clipboard_available = class_exists( '\\CB\\Core\\UI\\Assets' )
			&& method_exists( '\\CB\\Core\\UI\\Assets', 'enqueue_clipboard' );
		?>
		<div class="cb-likes-shortcode-row">
			<div class="cb-likes-shortcode-copy-group">
				<code><?php echo esc_html( $shortcode ); ?></code>
				<?php if ( $clipboard_available ) : ?>
					<button
						type="button"
						class="button button-secondary button-small cb-likes-copy-shortcode"
						data-cb-likes-copy-shortcode
						data-cb-likes-shortcode="<?php echo esc_attr( $shortcode ); ?>"
						data-cb-likes-copy-label="<?php esc_attr_e( 'Copy shortcode', 'core-blueprint-likes' ); ?>"
						data-cb-likes-copy-success="<?php esc_attr_e( 'Shortcode copied.', 'core-blueprint-likes' ); ?>"
						aria-label="<?php esc_attr_e( 'Copy shortcode', 'core-blueprint-likes' ); ?>"
					><?php esc_html_e( 'Copy', 'core-blueprint-likes' ); ?></button>
				<?php endif; ?>
			</div>
			<p>— <?php echo esc_html( $description ); ?></p>
		</div>
		<?php
	}

	private static function disclosure_icon( bool $core_presentation ): string {
		if ( $core_presentation && class_exists( '\\CB\\Core\\UI\\Icon' ) ) {
			return \CB\Core\UI\Icon::render( 'collapse', [
				'size'  => \CB\Core\UI\Icon::SIZE_COMPACT,
				'class' => 'cb-core-chevron cb-likes-disclosure-icon',
			] );
		}

		return '<span class="dashicons dashicons-arrow-down-alt2 cb-likes-disclosure-icon" aria-hidden="true"></span>';
	}

	private static function text_field( string $id, string $label, string $name, string $value, string $placeholder, string $description = '' ): void {
		?>
		<div class="cb-core-field">
			<label class="cb-core-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<div class="cb-core-field__control"><input class="regular-text" type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"></div>
			<?php if ( '' !== $description ) : ?><p class="description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
		</div>
		<?php
	}

	private static function icon_field( string $id, string $name_prefix, string $reaction, string $source, string $built_in, int $media_id, string $svg, bool $allow_inherit = false ): void {
		$media_url = $media_id > 0 ? wp_get_attachment_url( $media_id ) : '';
		$media_url = is_string( $media_url ) ? $media_url : '';
		?>
		<div class="cb-core-field cb-likes-icon-field" data-cb-likes-icon-field>
			<label class="cb-core-field__label" for="<?php echo esc_attr( $id ); ?>_source"><?php esc_html_e( 'Icon source', 'core-blueprint-likes' ); ?></label>
			<div class="cb-core-field__control">
				<select id="<?php echo esc_attr( $id ); ?>_source" name="<?php echo esc_attr( $name_prefix . '[' . $reaction . '_icon_source]' ); ?>" data-cb-likes-icon-source>
					<?php if ( $allow_inherit ) : ?><option value="inherit" <?php selected( $source, 'inherit' ); ?>><?php esc_html_e( 'Inherit global icon', 'core-blueprint-likes' ); ?></option><?php endif; ?>
					<option value="built_in" <?php selected( $source, 'built_in' ); ?>><?php esc_html_e( 'Built-in icon', 'core-blueprint-likes' ); ?></option>
					<option value="media" <?php selected( $source, 'media' ); ?>><?php esc_html_e( 'Media Library', 'core-blueprint-likes' ); ?></option>
					<option value="svg" <?php selected( $source, 'svg' ); ?>><?php esc_html_e( 'Inline SVG', 'core-blueprint-likes' ); ?></option>
				</select>
			</div>

			<div class="cb-likes-icon-source-panel" data-cb-likes-icon-panel="built_in">
				<label class="cb-core-field__label" for="<?php echo esc_attr( $id ); ?>_built_in"><?php esc_html_e( 'Built-in icon', 'core-blueprint-likes' ); ?></label>
				<div class="cb-likes-built-in-picker">
					<select id="<?php echo esc_attr( $id ); ?>_built_in" name="<?php echo esc_attr( $name_prefix . '[' . $reaction . '_icon_builtin]' ); ?>" data-cb-likes-built-in-select>
						<?php foreach ( Icons::labels() as $icon_name => $icon_label ) : ?>
							<option value="<?php echo esc_attr( $icon_name ); ?>" <?php selected( $built_in, $icon_name ); ?>><?php echo esc_html( $icon_label ); ?></option>
						<?php endforeach; ?>
					</select>
					<span class="cb-likes-built-in-preview" data-cb-likes-built-in-preview aria-hidden="true"><?php echo Icons::svg( $built_in ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted bundled SVG catalog. ?></span>
				</div>
				<p class="description"><?php esc_html_e( 'Choose from the small bundled Lucide icon set. No external icon library is loaded.', 'core-blueprint-likes' ); ?></p>
			</div>

			<div class="cb-likes-icon-source-panel" data-cb-likes-icon-panel="media">
				<input type="hidden" name="<?php echo esc_attr( $name_prefix . '[' . $reaction . '_icon_media_id]' ); ?>" value="<?php echo esc_attr( (string) $media_id ); ?>" data-cb-likes-media-id>
				<div class="cb-likes-media-picker">
					<div class="cb-likes-media-preview" data-cb-likes-media-preview<?php echo '' === $media_url ? ' hidden' : ''; ?>>
						<?php if ( '' !== $media_url ) : ?><img src="<?php echo esc_url( $media_url ); ?>" alt=""><?php endif; ?>
					</div>
					<div class="cb-likes-media-actions">
						<button type="button" class="button button-secondary" data-cb-likes-media-select><?php esc_html_e( 'Choose from Media Library', 'core-blueprint-likes' ); ?></button>
						<button type="button" class="button button-link-delete" data-cb-likes-media-remove<?php echo $media_id > 0 ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'core-blueprint-likes' ); ?></button>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Choose an image or SVG stored in your WordPress Media Library.', 'core-blueprint-likes' ); ?></p>
			</div>

			<div class="cb-likes-icon-source-panel" data-cb-likes-icon-panel="svg">
				<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>_svg"><?php esc_html_e( 'Inline SVG', 'core-blueprint-likes' ); ?></label>
				<textarea class="large-text code" rows="4" id="<?php echo esc_attr( $id ); ?>_svg" name="<?php echo esc_attr( $name_prefix . '[' . $reaction . '_icon]' ); ?>"><?php echo esc_textarea( $svg ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Paste safe inline SVG markup. External URL references are not stored.', 'core-blueprint-likes' ); ?></p>
			</div>
		</div>
		<?php
	}

}
