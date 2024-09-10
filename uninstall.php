<?php

// Exit if uninstall.php is called outside of WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Don't run uninstall.php if another version of the plugin is active.
if ( class_exists( 'Remove_WPEmoji_Correctly' ) ) {
    return;
}