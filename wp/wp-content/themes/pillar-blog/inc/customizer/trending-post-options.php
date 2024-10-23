<?php

// Trending Post section
$wp_customize->add_section('trending_posts_section', array(    
	'title'       => __('Trending Post Options', 'pillar-blog'),
	'panel'       => 'theme_option_panel'    
));

$wp_customize->add_setting('trending_posts', 
	array(
		'default' 			=> true,
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'cube_blog_sanitize_checkbox',
		'transport'         => 'refresh',
	)
);

$wp_customize->add_control('trending_posts', 
	array(		
		'label' 	=> __('Trending Posts', 'pillar-blog'),
		'section' 	=> 'trending_posts_section',
		'settings'  => 'trending_posts',
		'type' 		=> 'checkbox',
	)
);

$wp_customize->add_setting('trending_posts_section_title', 
	array(
		'default'           => esc_html__('Trending Posts', 'pillar-blog'),
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',	
		'sanitize_callback' => 'sanitize_text_field'
	)
);

$wp_customize->add_control('trending_posts_section_title', 
	array(
		'label'       => __('Section Title', 'pillar-blog'),
		'section'     => 'trending_posts_section',   
		'settings'    => 'trending_posts_section_title',	
		'type'        => 'text'
	)
);

$wp_customize->add_setting('trending_posts_category', 
	array(
		'default' 			=> '',
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'cube_blog_sanitize_select',
		'transport'         => 'refresh',
	)
);

$wp_customize->add_control('trending_posts_category', 
	array(		
		'label' 	=> __('Select Categories', 'pillar-blog'),
		'section' 	=> 'trending_posts_section',
		'settings'  => 'trending_posts_category',
		'type' 		=> 'select',
		'choices' 	=> pillar_blog_get_post_categories(),
	)
);