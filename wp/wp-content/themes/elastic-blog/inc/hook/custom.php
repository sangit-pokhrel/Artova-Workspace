<?php
/**
 * Custom theme functions.
 *
 * This file contains hook functions attached to theme hooks.
 *
 * @package Elastic Blog
 */

if( ! function_exists( 'elastic_blog_site_branding' ) ) :
/**
* Site Branding
*
* @since 1.0.0
*/
function elastic_blog_site_branding() { ?>
    <div class="wrapper">
        <div class="site-branding">
            <div class="site-logo">
                <?php if(has_custom_logo()):?>
                    <?php the_custom_logo();?>
                <?php endif;?>
            </div><!-- .site-logo -->

            <div id="site-identity">
                <h1 class="site-title">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">  <?php bloginfo( 'name' ); ?></a>
                </h1>

                <?php 
                    $description = get_bloginfo( 'description', 'display' );
                    if ( $description || is_customize_preview() ) : ?>
                    <p class="site-description"><?php echo esc_html($description);?></p>
                <?php endif; ?>
            </div><!-- #site-identity -->
        </div> <!-- .site-branding -->

        <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Primary Menu">
            <button type="button" class="menu-toggle">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_id'        => 'primary-menu',
                'menu_class'     => 'nav-menu',
                'fallback_cb'    => 'elastic_blog_primary_navigation_fallback',
            ) );
            ?>
        </nav><!-- #site-navigation -->
    </div><!-- .wrapper -->
<?php }
endif;
add_action( 'elastic_blog_action_header', 'elastic_blog_site_branding', 10 );

if ( ! function_exists( 'elastic_blog_footer_top_section' ) ) :

  /**
   * Top  Footer 
   *
   * @since 1.0.0
   */
  function elastic_blog_footer_top_section() {
      $footer_sidebar_data = elastic_blog_footer_sidebar_class();
      $footer_sidebar    = $footer_sidebar_data['active_sidebar'];
      $footer_class      = $footer_sidebar_data['class'];
      if ( empty( $footer_sidebar ) ) {
        return;
      }      ?>
      <div class="footer-widgets-area section-gap <?php echo esc_attr( $footer_class ); ?>"> <!-- widget area starting from here -->
          <div class="wrapper">
            <?php
              for ( $i = 1; $i <= 4 ; $i++ ) {
                if ( is_active_sidebar( 'footer-' . $i ) ) {
                ?>
                  <div class="hentry">
                    <?php dynamic_sidebar( 'footer-' . $i ); ?>
                  </div>
                  <?php
                }
              }
            ?>
            </div>
          
      </div> <!-- widget area starting from here -->
    <?php
 }
endif;

add_action( 'elastic_blog_action_footer', 'elastic_blog_footer_top_section', 10 );

if ( ! function_exists( 'elastic_blog_footer_section' ) ) :

  /**
   * Footer copyright
   *
   * @since 1.0.0
   */
  function elastic_blog_footer_section() { ?>
    <div class="site-info">    
        <?php 
            $copyright_footer = elastic_blog_get_option('copyright_text'); 
            if ( ! empty( $copyright_footer ) ) {
                $copyright_footer = wp_kses_data( $copyright_footer );
            }
            // Powered by content.
            $powered_by_text = sprintf( __( ' Theme Elastic Blog by %s', 'elastic-blog' ), '<a target="_blank" rel="designer" href="'. esc_url( 'http://creativthemes.com/' ) .'">Creativ Themes</a>' );
        ?>
        <div class="wrapper">
            <span class="copy-right"><?php echo esc_html($copyright_footer);?><?php echo $powered_by_text;?></span>
        </div><!-- .wrapper --> 
    </div> <!-- .site-info -->
    
  <?php }

endif;
add_action( 'elastic_blog_action_footer', 'elastic_blog_footer_section', 20 );

if ( ! function_exists( 'elastic_blog_footer_sidebar_class' ) ) :
  /**
   * Count the number of footer sidebars to enable dynamic classes for the footer
   *
   * @since elastic_blog 0.1
   */
  function elastic_blog_footer_sidebar_class() {
    $data = array();
    $active_sidebar = array();
      $count = 0;

      if ( is_active_sidebar( 'footer-1' ) ) {
        $active_sidebar[]   = 'footer-1';
          $count++;
      }

      if ( is_active_sidebar( 'footer-2' ) ){
        $active_sidebar[]   = 'footer-2';
          $count++;
    }

      if ( is_active_sidebar( 'footer-3' ) ){
        $active_sidebar[]   = 'footer-3';
          $count++;
      }

      if ( is_active_sidebar( 'footer-4' ) ){
        $active_sidebar[]   = 'footer-4';
          $count++;
      }

      $class = '';

      switch ( $count ) {
          case '1':
            $class = 'col-1';
            break;
          case '2':
            $class = 'col-2';
            break;
          case '3':
            $class = 'col-3';
            break;
            case '4':
            $class = 'col-4';
            break;
      }

    $data['active_sidebar'] = $active_sidebar;
    $data['class']        = $class;

      return $data;
  }
endif;

if ( ! function_exists( 'elastic_blog_excerpt_length' ) ) :

  /**
   * Implement excerpt length.
   *
   * @since 1.0.0
   *
   * @param int $length The number of words.
   * @return int Excerpt length.
   */
  function elastic_blog_excerpt_length( $length ) {

    if ( is_admin() ) {
      return $length;
    }

    $excerpt_length = elastic_blog_get_option( 'excerpt_length' );

    if ( absint( $excerpt_length ) > 0 ) {
      $length = absint( $excerpt_length );
    }

    return $length;
  }

endif;

add_filter( 'excerpt_length', 'elastic_blog_excerpt_length', 999 );

if( ! function_exists( 'elastic_blog_banner_header' ) ) :
    /**
     * Page Header
    */
    function elastic_blog_banner_header() { ?>

        <div id="header-image">
            <?php the_custom_header_markup(); ?>
        </div><!-- #header-image -->
        <?php echo '<div class="wrapper"><div class="section-gap clear">';
    }
endif;
add_action( 'elastic_blog_banner_header', 'elastic_blog_banner_header', 10 );

if ( ! function_exists( 'elastic_blog_posts_tags' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time and author.
     */
    function elastic_blog_posts_tags() {
        // Hide category and tag text for pages.
        if ( 'post' === get_post_type() && has_tag() ) { ?>
                <div class="tags-links">

                    <?php /* translators: used between list items, there is a space after the comma */
                    $tags = get_the_tags();
                    if ( $tags ) {

                        foreach ( $tags as $tag ) {
                            echo '<span><a href="' . esc_url( get_tag_link( $tag->term_id ) ) .'">' . esc_html( $tag->name ) . '</a></span>'; // WPCS: XSS OK.
                        }
                    } ?>
                </div><!-- .tags-links -->
        <?php } 
    }
endif;