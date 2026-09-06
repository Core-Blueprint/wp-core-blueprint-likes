<?php
declare(strict_types=1);

namespace CB\Likes\Admin;

use CB\Likes\Icons;

defined( 'ABSPATH' ) || exit;

final class IconField {
	public static function render( string $id, string $name_prefix, string $reaction, string $source, string $built_in, int $media_id, string $svg, bool $allow_inherit = false ): void {
		$media_url = $media_id > 0 ? wp_get_attachment_url( $media_id ) : '';
		$media_url = is_string( $media_url ) ? $media_url : '';
		?>
		<div class="cb-core-field cb-likes-icon-field" data-cb-likes-icon-field>
			<label class="cb-core-field__label" for="<?php echo esc_attr( $id ); ?>_source"><?php esc_html_e( 'Icon source', 'core-blueprint-likes' ); ?></label>
			<div class="cb-core-field__control"><select id="<?php echo esc_attr( $id ); ?>_source" name="<?php echo esc_attr( $name_prefix . '[' . $reaction . '_icon_source]' ); ?>" data-cb-likes-icon-source>
				<?php if ( $allow_inherit ) : ?><option value="inherit" <?php selected( $source, 'inherit' ); ?>><?php esc_html_e( 'Inherit global icon', 'core-blueprint-likes' ); ?></option><?php endif; ?>
				<option value="built_in" <?php selected( $source, 'built_in' ); ?>><?php esc_html_e( 'Built-in icon', 'core-blueprint-likes' ); ?></option>
				<option value="media" <?php selected( $source, 'media' ); ?>><?php esc_html_e( 'Media Library', 'core-blueprint-likes' ); ?></option>
				<option value="svg" <?php selected( $source, 'svg' ); ?>><?php esc_html_e( 'Inline SVG', 'core-blueprint-likes' ); ?></option>
			</select></div>

			<div class="cb-likes-icon-source-panel" data-cb-likes-icon-panel="built_in">
				<label class="cb-core-field__label" for="<?php echo esc_attr( $id ); ?>_built_in"><?php esc_html_e( 'Built-in icon', 'core-blueprint-likes' ); ?></label>
				<div class="cb-likes-built-in-picker">
					<select id="<?php echo esc_attr( $id ); ?>_built_in" name="<?php echo esc_attr( $name_prefix . '[' . $reaction . '_icon_builtin]' ); ?>" data-cb-likes-built-in-select>
						<?php foreach ( Icons::labels() as $icon_name => $icon_label ) : ?><option value="<?php echo esc_attr( $icon_name ); ?>" <?php selected( $built_in, $icon_name ); ?>><?php echo esc_html( $icon_label ); ?></option><?php endforeach; ?>
					</select>
					<span class="cb-likes-built-in-preview" data-cb-likes-built-in-preview aria-hidden="true"><?php echo Icons::svg( $built_in ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted bundled icon catalog. ?></span>
				</div>
				<p class="description"><?php esc_html_e( 'Choose from the bundled icon set. No external icon library is loaded.', 'core-blueprint-likes' ); ?></p>
			</div>

			<div class="cb-likes-icon-source-panel" data-cb-likes-icon-panel="media">
				<input type="hidden" name="<?php echo esc_attr( $name_prefix . '[' . $reaction . '_icon_media_id]' ); ?>" value="<?php echo esc_attr( (string) $media_id ); ?>" data-cb-likes-media-id>
				<div class="cb-likes-media-picker">
					<div class="cb-likes-media-preview" data-cb-likes-media-preview<?php echo '' === $media_url ? ' hidden' : ''; ?>><?php if ( '' !== $media_url ) : ?><img src="<?php echo esc_url( $media_url ); ?>" alt=""><?php endif; ?></div>
					<div class="cb-likes-media-actions"><button type="button" class="button button-secondary" data-cb-likes-media-select><?php esc_html_e( 'Choose from Media Library', 'core-blueprint-likes' ); ?></button><button type="button" class="button button-link-delete" data-cb-likes-media-remove<?php echo $media_id > 0 ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'core-blueprint-likes' ); ?></button></div>
				</div>
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
