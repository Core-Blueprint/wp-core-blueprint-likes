<?php
declare(strict_types=1);

namespace CB\Likes\Integration\Bricks;

use CB\Likes\Builder\Conditions;
use CB\Likes\Builder\Data;
use CB\Likes\Builder\Queries;

defined( 'ABSPATH' ) || exit;

/** Optional Bricks adapter over builder-neutral Likes contracts. */
final class Integration {
	private static function group(): string {
		return __( 'Core Blueprint Likes', 'core-blueprint-likes' );
	}

	public static function init(): void {
		add_filter( 'bricks/builder/i18n', [ __CLASS__, 'builder_i18n' ] );
		add_filter( 'bricks/dynamic_tags_list', [ __CLASS__, 'dynamic_tags' ] );
		add_filter( 'bricks/dynamic_data/render_tag', [ __CLASS__, 'render_tag' ], 20, 3 );
		add_filter( 'bricks/dynamic_data/render_content', [ __CLASS__, 'render_content' ], 20, 3 );
		add_filter( 'bricks/frontend/render_data', [ __CLASS__, 'render_content' ], 20, 2 );
		add_filter( 'bricks/conditions/groups', [ __CLASS__, 'condition_groups' ] );
		add_filter( 'bricks/conditions/options', [ __CLASS__, 'condition_options' ] );
		add_filter( 'bricks/conditions/result', [ __CLASS__, 'condition_result' ], 20, 3 );
		add_filter( 'bricks/setup/control_options', [ __CLASS__, 'query_types' ] );
		add_filter( 'bricks/query/run', [ __CLASS__, 'run_query' ], 20, 2 );
		add_action( 'init', [ ElementRegistry::class, 'register' ], 11 );
	}

	/** @param array<string,string> $i18n
	 *  @return array<string,string>
	 */
	public static function builder_i18n( array $i18n ): array {
		$i18n['core-blueprint-likes'] = self::group();
		return $i18n;
	}

	/** @param array<int,array<string,mixed>> $tags
	 *  @return array<int,array<string,mixed>>
	 */
	public static function dynamic_tags( array $tags ): array {
		foreach ( [
			'cb_likes_count' => __( 'Like count', 'core-blueprint-likes' ),
			'cb_likes_dislike_count' => __( 'Dislike count', 'core-blueprint-likes' ),
			'cb_likes_has_liked' => __( 'Current user has liked', 'core-blueprint-likes' ),
			'cb_likes_has_disliked' => __( 'Current user has disliked', 'core-blueprint-likes' ),
		] as $name => $label ) {
			$tags[] = [ 'name' => '{' . $name . '}', 'label' => $label, 'group' => self::group() ];
		}
		return $tags;
	}

	public static function render_tag( mixed $tag, mixed $post, string $context = 'text' ): mixed {
		if ( ! is_string( $tag ) ) {
			return $tag;
		}
		$name = trim( $tag, '{}' );
		$field = self::data_field( $name );
		if ( null === $field ) {
			return $tag;
		}
		$value = Data::value( $field, $post );
		return null === $value ? $tag : $value;
	}

	public static function render_content( mixed $content, mixed $post = null, string $context = 'text' ): mixed {
		if ( ! is_string( $content ) || ! str_contains( $content, '{cb_likes_' ) ) {
			return $content;
		}
		return preg_replace_callback( '/\{(cb_likes_[a-z0-9_]+)\}/', static function ( array $matches ) use ( $post ): string {
			$field = self::data_field( $matches[1] );
			if ( null === $field ) {
				return $matches[0];
			}
			$value = Data::value( $field, $post );
			return null === $value ? $matches[0] : (string) $value;
		}, $content ) ?? $content;
	}

	/** @param array<int,array<string,string>> $groups
	 *  @return array<int,array<string,string>>
	 */
	public static function condition_groups( array $groups ): array {
		$groups[] = [ 'name' => 'cb_likes', 'label' => self::group() ];
		return $groups;
	}

	/** @param array<int,array<string,mixed>> $options
	 *  @return array<int,array<string,mixed>>
	 */
	public static function condition_options( array $options ): array {
		foreach ( [
			'cb_likes_has_liked' => __( 'Current user has liked target', 'core-blueprint-likes' ),
			'cb_likes_has_disliked' => __( 'Current user has disliked target', 'core-blueprint-likes' ),
		] as $key => $label ) {
			$options[] = [
				'key' => $key,
				'group' => 'cb_likes',
				'label' => $label,
				'compare' => [ 'type' => 'select', 'options' => [ '==' => __( 'is', 'core-blueprint-likes' ) ] ],
				'value' => [ 'type' => 'select', 'options' => [ 'true' => __( 'True', 'core-blueprint-likes' ), 'false' => __( 'False', 'core-blueprint-likes' ) ] ],
			];
		}
		return $options;
	}

	/** @param array<string,mixed> $condition */
	public static function condition_result( bool $result, string $condition_key, array $condition ): bool {
		if ( 'cb_likes_has_liked' === $condition_key ) {
			$actual = Conditions::current_user_has_liked();
		} elseif ( 'cb_likes_has_disliked' === $condition_key ) {
			$actual = Conditions::current_user_has_disliked();
		} else {
			return $result;
		}
		return 'false' === (string) ( $condition['value'] ?? 'true' ) ? ! $actual : $actual;
	}

	/** @param array<string,mixed> $options
	 *  @return array<string,mixed>
	 */
	public static function query_types( array $options ): array {
		$options['queryTypes']['cb_likes_liked_posts'] = __( 'Likes: Posts liked by current user', 'core-blueprint-likes' );
		$options['queryTypes']['cb_likes_most_liked'] = __( 'Likes: Most liked posts', 'core-blueprint-likes' );
		return $options;
	}

	/** @return array<int,mixed> */
	public static function run_query( array $results, object $query_obj ): array {
		$type = (string) ( $query_obj->object_type ?? '' );
		if ( 'cb_likes_liked_posts' === $type ) {
			return Queries::liked_posts( get_current_user_id() );
		}
		if ( 'cb_likes_most_liked' === $type ) {
			return Queries::most_liked_posts();
		}
		return $results;
	}

	private static function data_field( string $name ): ?string {
		return match ( $name ) {
			'cb_likes_count' => Data::LIKE_COUNT,
			'cb_likes_dislike_count' => Data::DISLIKE_COUNT,
			'cb_likes_has_liked' => Data::HAS_LIKED,
			'cb_likes_has_disliked' => Data::HAS_DISLIKED,
			default => null,
		};
	}
}
