<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Elastic Blog
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="single-page-title">
		<h1><?php the_title(); ?></h1>
	</div><!-- .single-page-title -->

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="single-featured-image">
			<?php the_post_thumbnail(); ?>
		</div><!-- .single-featured-image -->
	<?php endif; ?>

	<div class="single-entry-content">
		<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'elastic-blog' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .single-entry-content -->

	<?php if ( get_edit_post_link() ) : ?>
		<footer class="entry-footer">
			<?php
				edit_post_link(
					sprintf(
						/* translators: %s: Name of current post */
						esc_html__( 'Edit %s', 'elastic-blog' ),
						the_title( '<span class="screen-reader-text">"', '"</span>', false )
					),
					'<span class="edit-link">',
					'</span>'
				);
			?>
		</footer><!-- .entry-footer -->
	<?php endif; ?>
</article><!-- #post-## -->