<?php
$top_stories_section_title = get_theme_mod( 'top_stories_section_title', 'Top Stories' );
$top_stories_id 			  = get_theme_mod( 'top_stories_category', '' );

$query = new WP_Query( apply_filters( 'pillar_blog_top_stories_args', array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 4,
	'cat'                 => $top_stories_id,
	'offset'              => 0,
	'ignore_sticky_posts' => 1
)));

$posts_array = $query->get_posts();
$show_top_stories = count( $posts_array ) > 0 && is_home();

if( get_theme_mod( 'top_stories', true ) && $show_top_stories ){
	?>
	<section class="section-top-stories">
		<div class="section-header">
			<h2 class="section-title"><?php echo esc_html($top_stories_section_title); ?></h2>
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