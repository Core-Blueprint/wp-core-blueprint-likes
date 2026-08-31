<?php
declare(strict_types=1);

namespace CB\Likes\Rest;

use CB\Likes\Service;

defined( 'ABSPATH' ) || exit;

final class Controller {
	public static function init(): void {
		add_action( 'rest_api_init', [ __CLASS__, 'routes' ] );
	}

	public static function routes(): void {
		register_rest_route( 'core-blueprint-likes/v1', '/state', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'set_state' ],
			'permission_callback' => static fn(): bool => is_user_logged_in(),
			'args'                => [
				'target_type' => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_key' ],
				'target_id'   => [ 'required' => true, 'type' => 'integer', 'sanitize_callback' => 'absint' ],
				'reaction'    => [ 'required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_key' ],
				'liked'       => [ 'required' => false, 'type' => 'boolean' ],
			],
		] );
	}

	public static function set_state( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$reaction = $request->get_param( 'reaction' );
		if ( null !== $reaction ) {
			$result = Service::set_reaction(
				get_current_user_id(),
				(string) $request->get_param( 'target_type' ),
				(int) $request->get_param( 'target_id' ),
				'' === (string) $reaction ? null : (string) $reaction
			);
		} else {
			$result = Service::set_liked(
				get_current_user_id(),
				(string) $request->get_param( 'target_type' ),
				(int) $request->get_param( 'target_id' ),
				(bool) $request->get_param( 'liked' )
			);
		}
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}
}
