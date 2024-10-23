<?php
/**
 * The template for displaying home page.
 * @package Elastic Blog
 */

if ( 'posts' != get_option( 'show_on_front' ) ) { 
    get_header(); ?>
        <?php include( get_page_template() ); ?>
    <?php
    get_footer();
} 
elseif ('posts' == get_option( 'show_on_front' ) ) {
    include( get_home_template() );
}