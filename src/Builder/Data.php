<?php
declare(strict_types=1);

namespace CB\Likes\Builder;

use CB\Likes\Repository;
use CB\Likes\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Builder-neutral read contract for Likes reaction data.
 */
final class Data {
	public const LIKE_COUNT    = 'like_count';
	public const DISLIKE_COUNT = 'dislike_count';
	public const HAS_LIKED     = 'has_liked';
	public const HAS_DISLIKED  = 'has_disliked';

	public static function value( string $field, mixed $context = null ): string|int|null {
		$target = Context::target( $context );
		if ( null === $target ) {
			return null;
		}

		$dislike_enabled = Settings::dislike_enabled_for_target( $target['type'], $target['id'] );
		$user_id         = get_current_user_id();

		return match ( $field ) {
			self::LIKE_COUNT => Repository::count( $target['type'], $target['id'] ),
			self::DISLIKE_COUNT => $dislike_enabled ? Repository::dislike_count( $target['type'], $target['id'] ) : 0,
			self::HAS_LIKED => $user_id > 0 && Repository::user_has_liked( $user_id, $target['type'], $target['id'] ) ? '1' : '0',
			self::HAS_DISLIKED => $dislike_enabled && $user_id > 0 && Repository::user_has_disliked( $user_id, $target['type'], $target['id'] ) ? '1' : '0',
			default => null,
		};
	}
}
