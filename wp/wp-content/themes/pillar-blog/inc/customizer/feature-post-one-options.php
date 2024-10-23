<?php

// Feature post one section
$wp_customize->add_section('feature_posts_one_section', array(    
	'title'       => __('Feature Post Options', 'pillar-blog'),
	'panel'       => 'theme_option_panel'    
));

$wp_customize->add_setting('feature_posts_one', 
	array(
		'default' 			=> true,
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'cube_blog_sanitize_checkbox',
		'transport'         => 'refresh',
	)
);

$wp_customize->add_control('feature_posts_one', 
	array(		
		'label' 	=> __('Feature Posts', 'pillar-blog'),
		'section' 	=> 'feature_posts_one_section',
		'settings'  => 'feature_posts_one',
		'type' 		=> 'checkbox',
	)
);

$wp_customize->add_setting('feature_posts_one_category', 
	array(
		'default' 			=> '',
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'cube_blog_sanitize_select',
		'transport'         => 'refresh',
	)
);

$wp_customize->add_control('feature_posts_one_category', 
	array(		
		'label' 	=> __('Select Categories', 'pillar-blog'),
		'section' 	=> 'feature_posts_one_section',
		'settings'  => 'feature_posts_one_category',
		'type' 		=> 'select',
		'choices' 	=> pillar_blog_get_post_categories(),
	)
);