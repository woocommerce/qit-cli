<?php
/**
 * Theme functions and definitions
 */

function test_valid_theme_setup() {
    add_theme_support("automatic-feed-links");
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
}
add_action("after_setup_theme", "test_valid_theme_setup");
