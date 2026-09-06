<?php
declare(strict_types=1);

namespace CB\Likes\Builder;

use CB\Likes\Frontend\Renderer;

defined( 'ABSPATH' ) || exit;

/** Builder-neutral interactive Likes components. */
final class Components {
	/** @param array{type:string,id:int}|null $target */
	public static function like_button( ?array $target = null, bool $show_count = true ): string {
		return Renderer::like_button( self::attributes( $target, $show_count ) );
	}

	/** @param array{type:string,id:int}|null $target */
	public static function dislike_button( ?array $target = null, bool $show_count = true ): string {
		return Renderer::dislike_button( self::attributes( $target, $show_count ) );
	}

	/** @param array{type:string,id:int}|null $target
	 *  @return array<string,string>
	 */
	private static function attributes( ?array $target, bool $show_count ): array {
		$attributes = [ 'show_count' => $show_count ? '1' : '0' ];
		if ( null !== $target ) {
			$attributes['target_type'] = (string) $target['type'];
			$attributes['target_id']   = (string) $target['id'];
		}
		return $attributes;
	}
}
