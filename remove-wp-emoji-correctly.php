<?php
/**
 * Plugin Name:       Remove WP Emojis — Correctly
 * Plugin URI:        https://selftawt.com/disable-wpemoji-correctly
 * Description:       The correct way to remove or disable emoji support that was added in WordPress v4.2. Make your header clean, lean, and mean.
 * Author:            Rey Sanchez
 * Author URI:        https://selftawt.com/about/
 * Version:           1.1.2
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * License:           GPL-3.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-3.0-or-later.html
 */

namespace Remove_WP_Emoji_Correctly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default filters are found at wp-includes/default-filters.php
 * 
 * We no longer need to manually remove emoji_svg_url prefetch from wp_resource_hints since it's no longer
 * included by default.
 * 
 * @link https://core.trac.wordpress.org/changeset/53904
 */
final class Remove_WPEmojis {

	public static function disable_emojis_frontend(): void {
		/** Actions. */
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' ); // Retained for backwards compatibility.

		/** Prevent conversion of emoji to a static img element. */
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		/** Embeds. */
		remove_action( 'embed_head', 'print_emoji_detection_script' );
		remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
	}

	public static function disable_emojis_loggedin(): void {
		/** Actions. */
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' ); // Retained for backwards compatibility.

		/** For those using classic editor. */
		add_filter( 'tiny_mce_plugins', [ __CLASS__, 'disable_emojis_tinymce' ] );
	}

	/**
	 * Remove the 'wpemoji' plugin inside TinyMCE editor.
	 * Please see line 421 of wp-includes/class-wp-editor.php
	 * 
	 * @param array $tinymce_plugins
	 */
	public static function disable_emojis_tinymce( $tinymce_plugins ): array {

		if ( ! is_array( $tinymce_plugins ) ) {
			return $tinymce_plugins;
		}

		return array_diff( $tinymce_plugins, [ 'wpemoji' ] );
	}

	public static function load(): void {
		if ( is_admin() ) {
			add_action( 'admin_init', [ __CLASS__, 'disable_emojis_loggedin' ] );
		} else {
			add_action( 'init', [ __CLASS__, 'disable_emojis_frontend' ] );
		}
	}
}

Remove_WPEmojis::load();