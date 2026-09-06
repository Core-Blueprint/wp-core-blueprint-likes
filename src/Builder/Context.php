<?php
declare(strict_types=1);

namespace CB\Likes\Builder;

use CB\Likes\Targets as DomainTargets;

defined( 'ABSPATH' ) || exit;

/** Builder-neutral target context for Likes adapters. */
final class Context {
	/** @return array{type:string,id:int}|null */
	public static function target( mixed $context = null ): ?array {
		$target = DomainTargets::current( $context );
		if ( ! $target || ! DomainTargets::is_enabled( $target['type'], $target['id'] ) ) {
			return null;
		}

		return $target;
	}
}
