<?php
declare(strict_types=1);

namespace CB\Likes\Admin;
defined( 'ABSPATH' ) || exit;

final class Fields {
	public static function text( string $id, string $label, string $name, string $value, string $placeholder, string $description = '' ): void {
		?>
		<div class="cb-core-field">
			<label class="cb-core-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<div class="cb-core-field__control"><input class="regular-text" type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"></div>
			<?php if ( '' !== $description ) : ?><p class="description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
		</div>
		<?php
	}

	public static function disclosure_icon(): string {
		return \CB\Core\UI\Icon::render( 'expand', [
			'size'  => \CB\Core\UI\Icon::SIZE_COMPACT,
			'class' => 'cb-core-interactive-row__icon cb-core-chevron cb-likes-disclosure-icon',
		] );
	}
}
