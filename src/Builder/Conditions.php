<?php
declare(strict_types=1);

namespace CB\Likes\Builder;

use CB\Likes\Repository;
use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Builder-neutral visibility predicates for Likes adapters.
 */
final class Conditions {
	public static function current_user_has_liked( mixed $context = null ): bool {
		$target  = Context::target( $context );
		$user_id = get_current_user_id();

		return null !== $target
			&& $user_id > 0
			&& Repository::user_has_liked( $user_id, $target['type'], $target['id'] );
	}

	public static function current_user_has_disliked( mixed $context = null ): bool {
		$target  = Context::target( $context );
		$user_id = get_current_user_id();

		return null !== $target
			&& $user_id > 0
			&& Settings::dislike_enabled_for_target( $target['type'], $target['id'] )
			&& Repository::user_has_disliked( $user_id, $target['type'], $target['id'] );
	}
}
