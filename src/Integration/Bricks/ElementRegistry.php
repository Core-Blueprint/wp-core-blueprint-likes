<?php
declare(strict_types=1);

namespace CB\Likes\Integration\Bricks;

defined( 'ABSPATH' ) || exit;

/**
 * Registers optional Bricks elements without making Bricks a dependency.
 */
final class ElementRegistry {
	public const CATEGORY = 'core-blueprint-likes';

	/** @var array<string,array{name:string,class:class-string}> */
	private const ELEMENTS = [
		'Elements/Like.php' => [ 'name' => 'cb-likes-like', 'class' => Elements\Like::class ],
		'Elements/Dislike.php' => [ 'name' => 'cb-likes-dislike', 'class' => Elements\Dislike::class ],
	];

	public static function register(): void {
		if ( ! class_exists( '\\Bricks\\Elements' ) || ! class_exists( '\\Bricks\\Element' ) ) {
			return;
		}

		add_filter( 'bricks/builder/i18n', [ self::class, 'builder_i18n' ] );

		foreach ( self::ELEMENTS as $relative_file => $definition ) {
			$file = __DIR__ . '/' . $relative_file;
			if ( is_readable( $file ) ) {
				\Bricks\Elements::register_element( $file, $definition['name'], $definition['class'] );
			}
		}
	}

	/** @param array<string,string> $i18n @return array<string,string> */
	public static function builder_i18n( array $i18n ): array {
		$i18n[ self::CATEGORY ] = esc_html__( 'Core Blueprint Likes', 'core-blueprint-likes' );
		return $i18n;
	}
}
