<?php
/**
 * Plugin Name: Mihdan: Redis
 * Description: Добавляет меню управления кешем редиски и статистику с ее метабоксом
 * Version: 1.2
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-redi
 *
 * @link https://github.com/pantheon-systems/wp-redis/wiki
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'wp_cache_flush' ) ) {

	/**
	 * Redis object cache: Delete the alloptions cache whenever it is updated (SET) in the
	 * object cache.
	 *
	 * Fixes bug in core that creates race condition when multiple processes update options in the alloptions cache.
	 * By deleting the cache for all options we can be confident that any mistakes (inserting the new value in an out-of-date
	 * version of the alloptions array) won't carry forward into other processes and cause WSOD.
	 *
	 * Shouldn't be necessary if that bug is ever fixed.
	 *
	 * Relies on 'redis_object_cache_set' action in redis' object-cache.php (WP_Object_Cache->set()) which we added manually for now (2015-12-09)
	 *
	 * @see https://core.trac.wordpress.org/ticket/31245
	 */
	function mihdan_redis_object_cache_delete_alloptions( $key, $value, $group, $expiration ) {
		if ( 'alloptions' == $key && 'options' == $group ) {
			wp_cache_delete( 'alloptions', 'options' );
		}
	}
	add_action( 'redis_object_cache_set', 'mihdan_redis_object_cache_delete_alloptions', 10, 4 );

	/**
	 * Fix a race condition in alloptions caching
	 *
	 * @see https://core.trac.wordpress.org/ticket/31245
	 */
	function mihdan_ticket_31245_patch( $option ) {
		if ( ! wp_installing() ) {
			$alloptions = wp_load_alloptions(); //alloptions should be cached at this point
			if ( isset( $alloptions[ $option ] ) ) { //only if option is among alloptions
				wp_cache_delete( 'alloptions', 'options' );
			}
		}
	}
	add_action( 'added_option',   'mihdan_ticket_31245_patch' );
	add_action( 'updated_option', 'mihdan_ticket_31245_patch' );
	add_action( 'deleted_option', 'mihdan_ticket_31245_patch' );
}
