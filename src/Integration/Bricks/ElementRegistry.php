<?php
declare(strict_types=1);

namespace CB\Likes\Integration\Bricks;

defined( 'ABSPATH' ) || exit;

/** Registers Likes elements only when Bricks is actually available. */
final class ElementRegistry {
	/** @var array<string,array{name:string,class:class-string}> */
	private const ELEMENTS = [
		'Elements/Like.php' => [ 'name' => 'cb-likes-like', 'class' => Elements\Like::class ],
		'Elements/Dislike.php' => [ 'name' => 'cb-likes-dislike', 'class' => Elements\Dislike::class ],
	];

	public static function register(): void {
		if ( ! class_exists( '\\Bricks\\Elements' ) || ! class_exists( '\\Bricks\\Element' ) ) {
			return;
		}

		foreach ( self::ELEMENTS as $relative_file => $definition ) {
			$file = __DIR__ . '/' . $relative_file;
			if ( is_readable( $file ) ) {
				\Bricks\Elements::register_element( $file, $definition['name'], $definition['class'] );
			}
		}
	}
}
