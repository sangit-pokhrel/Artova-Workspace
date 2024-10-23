<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pillar_blog
 */

get_header();
?>

<div id="content-wrap" class="container">
	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<?php get_template_part( 'template-parts/feature-posts/feature-posts', 'one' ); ?>
			<?php get_template_part( 'template-parts/top-stories/top', 'stories' ); ?>
			<?php get_template_part( 'template-parts/popular-posts/popular', 'posts' ); ?>
			<?php get_template_part( 'template-parts/trending-posts/trending', 'posts' ); ?>
			<?php get_template_part( 'template-parts/recent-posts/recent', 'posts' ); ?>

			<div class="blog-archive columns-3 clear">
				<?php
				if ( have_posts() ) :
					/* Start the Loop */
					while ( have_posts() ) :
						the_post();

						/*
						 * Include the Post-Type-specific template for the content.
						 * If you want to override this in a child theme, then include a file
						 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
						 */
						get_template_part( 'template-parts/content', get_post_type() );

					endwhile;

				else :

					get_template_part( 'template-parts/content', 'none' );

				endif;
				?>
			</div><!-- .blog-archive -->

			<?php
			the_posts_pagination(
				array(
					'prev_text'          => cube_blog_get_svg( array( 'icon' => 'arrow-left' ) ) . '<span class="screen-reader-text">' . __( 'Previous page', 'pillar-blog' ) . '</span>',
					'next_text'          => '<span class="screen-reader-text">' . __( 'Next page', 'pillar-blog' ) . '</span>' . cube_blog_get_svg( array( 'icon' => 'arrow-right' ) ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'pillar-blog' ) . ' </span>',
				)
			); ?>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>

</div><!-- .container -->

<?php
get_footer();
