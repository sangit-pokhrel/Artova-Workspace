<?php
$trending_posts_section_title = get_theme_mod( 'trending_posts_section_title', 'Trending Posts' );
$trending_posts_id 			  = get_theme_mod( 'trending_posts_category', '' );

$query = new WP_Query( apply_filters( 'pillar_blog_trending_posts_args', array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 4,
	'cat'                 => $trending_posts_id,
	'offset'              => 0,
	'ignore_sticky_posts' => 1
)));

$posts_array = $query->get_posts();
$show_trending_posts = count( $posts_array ) > 0 && is_home();

if( get_theme_mod( 'trending_posts', true ) && $show_trending_posts ){
	?>
	<section class="section-trending-posts">
		<div class="section-header">
			<h2 class="section-title"><?php echo esc_html($trending_posts_section_title); ?></h2>
		</div><!-- .section-header -->

		<div class="columns-4 clear">
			<?php
			while ( $query->have_posts() ) : $query->the_post(); ?>

	            <article>
		        	<?php if ( has_post_thumbnail() ) : ?>
						<div class="featured-image" style="background-image: url('<?php the_post_thumbnail_url( 'full' ); ?>');">
							<a href="<?php the_permalink();?>" class="post-thumbnail-link"></a>
						</div><!-- .featured-image -->
					<?php endif; ?>

		            <div class="entry-container">
		            	<div class="entry-meta">
			        		<?php if( 'post' == get_post_type() ): 
								$categories_list = get_the_category_list( ' ' );
								if( $categories_list ):
								printf( '<span class="cat-links">' . '%1$s' . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							endif; endif; ?>
			        	</div><!-- .entry-meta -->

						<header class="entry-header">
							<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						</header>

						<?php cube_blog_posted_on() ?>
			        </div><!-- .entry-container -->
	            </article>
		        
			<?php
			endwhile; 
			wp_reset_postdata(); ?>
		</div><!-- .columns-4 -->
	</section>
<?php } ?>