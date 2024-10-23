<?php
/**
 * Pillar Blog Theme Customizer
 *
 * @package pillar_blog
 */

/**
 * Customizer options
 */
function pillar_blog_customize_register( $wp_customize ) {
	include get_stylesheet_directory() . '/inc/customizer/feature-post-one-options.php';
    include get_stylesheet_directory() . '/inc/customizer/top-stories-options.php';
    include get_stylesheet_directory() . '/inc/customizer/popular-post-options.php';
    include get_stylesheet_directory() . '/inc/customizer/trending-post-options.php';
    include get_stylesheet_directory() . '/inc/customizer/recent-post-options.php';
}
add_action( 'customize_register', 'pillar_blog_customize_register' );