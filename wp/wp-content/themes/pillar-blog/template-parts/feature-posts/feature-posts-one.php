<?php
$feature_posts_id = get_theme_mod( 'feature_posts_one_category', '' );

$query = new WP_Query( apply_filters( 'pillar_blog_feature_posts_one_args', array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 5,
	'cat'                 => $feature_posts_id,
	'offset'              => 0,
	'ignore_sticky_posts' => 1
)));

$posts_array = $query->get_posts();
$show_feature_posts_one = count( $posts_array ) > 0 && is_home();

if( get_theme_mod( 'feature_posts_one', true ) && $show_feature_posts_one ){
	?>
	<section class="section-feature-posts-one-area">
		<div class="custom-row">
		<?php
			$main_post = true;
			$i 		   = 1;
			while ( $query->have_posts() ) : $query->the_post();
			$image 	= get_the_post_thumbnail_url( get_the_ID(), 'large' );

			if( $main_post ){ $main_post = false; ?>
				<div class="custom-col-12 custom-col-lg-5">
			        <article class="post feature-posts-content-wrap feature-big-posts">
			        	<div class="feature-posts-image" style="background-image: url( <?php echo esc_url( $image ); ?> );">
				        	<div class="feature-posts-content">
					          	<?php if( 'post' == get_post_type() ): 
									$categories_list = get_the_category_list( ' ' );
									if( $categories_list ):
									printf( '<span class="cat-links">' . '%1$s' . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								endif; endif; ?>
								<h3 class="feature-posts-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
					            <div class="entry-meta">
									<?php cube_blog_posted_on() ?>
								</div>
				        	</div>
			        	</div>
			        </article>
			    </div>
			<?php }else{
				if( $i == 2 ){ ?>
				<div class="custom-col-md-12 custom-col-lg-7">
			        <div class="custom-row">
			        <?php } ?>
			        	<div class="custom-col-md-6">
				            <article class="post feature-posts-content-wrap">
				            	<div class="feature-posts-image" style="background-image: url( <?php echo esc_url( $image ); ?> );">
					            	<div class="feature-posts-content">
							          	<?php if( 'post' == get_post_type() ): 
											$categories_list = get_the_category_list( ' ' );
											if( $categories_list ):
											printf( '<span class="cat-links">' . '%1$s' . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										endif; endif; ?>
										<h3 class="feature-posts-title">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h3>
							            <div class="entry-meta">
											<?php cube_blog_posted_on() ?>
										</div>
						        	</div>
					        	</div>
				            </article>
			        	</div>
			        <?php if( count( $posts_array ) == $i ){ ?>
			        </div>
			    </div>
				<?php } ?>
			<?php } ?>
			<?php
			$i++;
			endwhile; 
			wp_reset_postdata();
		?>
		</div>
	</section>
<?php } ?>