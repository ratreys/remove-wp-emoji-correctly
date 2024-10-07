<?php
/**
 * Plugin Name:       Remove WP Emojis — Correctly
 * Plugin URI:        https://selftawt.com/disable-wpemoji-correctly
 * Description:       The right way to remove or disable emoji support that was added in WordPress v4.2. Make your header clean, lean, and mean.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Rey Sanchez
 * Author URI:        https://selftawt.com
 * License:           GPL-3.0
 */

namespace Selftawt\RWPEC;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Default filters are found at wp-includes/default-filters.php
 * 
 * @link https://core.trac.wordpress.org/browser/tags/6.6/src/wp-includes/default-filters.php
 * 
 * We no longer need to manually remove emoji_svg_url prefetch from wp_resource_hints since it's no longer
 * included by default.
 * 
 * @link https://core.trac.wordpress.org/changeset/53904
 * 
 */
final class Remove_WP_Emojis_Correctly {

    private static $instance = null;
    
    /** Make sure there's only one instance of the class. */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Private constructor to prevent direct instantiation. */
    private function __construct() {/** There is nothing here. */}

    public static function init() {
        /** Actions. */
        remove_action( 'wp_head',            'print_emoji_detection_script', 7 );
        remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
        remove_action( 'wp_print_styles',    'print_emoji_styles' ); // Retained for backwards compatibility.

        /** Prevent conversion of emoji to a static img element. */
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail',          'wp_staticize_emoji_for_email' );

        /** Embeds. */
        remove_action( 'embed_head',            'print_emoji_detection_script' );
        remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
    }

    public static function admin_init() {
        /** Actions. */
        remove_action( 'admin_print_scripts',   'print_emoji_detection_script' );
        remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
        remove_action( 'admin_print_styles',    'print_emoji_styles' ); // Retained for backwards compatibility.

        /** For those using classic editor. */
        add_filter( 'tiny_mce_plugins', array( __CLASS__, 'remove_wpemoji_plugin' ) );
    }

    /**
     * Remove the default 'wpemoji' plugin inside TinyMCE editor.
     * Please see line 421 of wp-includes/class-wp-editor.php
     * 
     * @param array $default_tiny_mce_plugins
     * 
     *      List of default TinyMCE plugins:
     * 
     *      'wptextpattern', 'wpautoresize', 'colorpicker', 'wpeditimage', 'fullscreen', 'textcolor',
     *      'wordpress',     'wpgallery',    'wpdialogs',   'tabfocus',    'charmap',    'wpemoji',
     *      'wplink',        'wpview',       'lists',       'media',       'paste',      'hr',
     * 
    */
    public static function remove_wpemoji_plugin( $default_tiny_mce_plugins ) {
        if ( is_array( $default_tiny_mce_plugins ) && in_array( 'wpemoji', $default_tiny_mce_plugins, true ) ) {
            return array_diff( $default_tiny_mce_plugins, array('wpemoji') );
        }

        return $default_tiny_mce_plugins;
    }
}

if ( is_admin() ) {
    add_action( 'admin_init', array( '\Selftawt\RWPEC\Remove_WP_Emojis_Correctly', 'admin_init' ) );
}  else {
    add_action( 'init', array( '\Selftawt\RWPEC\Remove_WP_Emojis_Correctly', 'init' ) );
}