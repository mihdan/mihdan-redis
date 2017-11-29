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
	//add_action( 'redis_object_cache_set', 'mihdan_redis_object_cache_delete_alloptions', 10, 4 );

	/**
	 * Fixme: Заплатка - очистим транзитный кеш во время добавления
	 * ссылки на post public preview
	 */
	function mihdan_redis_flush_cache( $option ) {
		wp_cache_delete( 'public_post_preview', 'options' );
		wp_cache_delete( 'alloptions', 'options' );
	}
	add_action( 'add_option_public_post_preview', 'mihdan_redis_flush_cache' );
	add_action( 'update_option_public_post_preview', 'mihdan_redis_flush_cache' );
	//add_action( 'update_option_cron', 'mihdan_redis_flush_cache' );
}