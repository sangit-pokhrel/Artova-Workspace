<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Elastic Blog
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="post-item">
		<?php if ( has_post_thumbnail() ) : ?>
            <div class="featured-image" style="background-image: url('<?php echo the_post_thumbnail_url(); ?>') ;">
                <a href="<?php the_permalink();?>" class="post-thumbnail-link"></a>
            </div><!-- .featured-image -->
        <?php endif; ?>

		<div class="entry-container">    
			<?php elastic_blog_entry_meta(); ?>  
			                      
			<header class="entry-header">
				<?php
				if ( is_single() ) :
					the_title( '<h1 class="entry-title">', '</h1>' );
				else :
					the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
				endif; ?>
			</header><!-- .entry-header -->

			<div class="entry-content">
                <?php the_excerpt(); ?>
            </div><!-- .entry-content -->

			<?php elastic_blog_posted_on(); ?>
		</div><!-- .entry-container -->
	</div><!-- .post-item -->
</article><!-- #post-## -->