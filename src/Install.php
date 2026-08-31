<?php
declare(strict_types=1);

namespace CB\Likes;

defined( 'ABSPATH' ) || exit;

final class Install {
	private const DB_VERSION = '2';
	private const DB_OPTION  = 'cb_likes_db_version';

	public static function activate(): void {
		self::install_schema();
		$administrator = get_role( 'administrator' );
		if ( $administrator ) {
			$administrator->add_cap( Capabilities::MANAGE );
		}
		if ( false === get_option( Settings::OPTION, false ) ) {
			add_option( Settings::OPTION, [ 'post_types' => [ 'post' ], 'users_enabled' => false, 'global' => [ 'logged_in_only' => true ] ], '', false );
		}
	}

	public static function maybe_upgrade(): void {
		if ( self::DB_VERSION !== (string) get_option( self::DB_OPTION, '' ) ) {
			self::install_schema();
		}
	}

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'cb_likes';
	}

	private static function install_schema(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			target_type varchar(16) NOT NULL,
			target_id bigint(20) unsigned NOT NULL,
			reaction varchar(16) NOT NULL DEFAULT 'like',
			created_at datetime NOT NULL,
			updated_at datetime NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_target (user_id,target_type,target_id),
			KEY target (target_type,target_id),
			KEY target_reaction (target_type,target_id,reaction),
			KEY user_created (user_id,created_at),
			KEY created_at (created_at)
		) {$charset};";
		dbDelta( $sql );
		update_option( self::DB_OPTION, self::DB_VERSION, false );
	}
}
