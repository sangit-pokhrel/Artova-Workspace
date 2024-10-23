<?php

// Recent Post section
$wp_customize->add_section('recent_posts_section', array(    
	'title'       => __('Recent Post Options', 'pillar-blog'),
	'panel'       => 'theme_option_panel'    
));

$wp_customize->add_setting('recent_posts', 
	array(
		'default' 			=> true,
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'cube_blog_sanitize_checkbox',
		'transport'         => 'refresh',
	)
);

$wp_customize->add_control('recent_posts', 
	array(		
		'label' 	=> __('Recent Posts', 'pillar-blog'),
		'section' 	=> 'recent_posts_section',
		'settings'  => 'recent_posts',
		'type' 		=> 'checkbox',
	)
);

$wp_customize->add_setting('recent_posts_section_title', 
	array(
		'default'           => esc_html__('Recent Posts', 'pillar-blog'),
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',	
		'sanitize_callback' => 'sanitize_text_field'
	)
);

$wp_customize->add_control('recent_posts_section_title', 
	array(
		'label'       => __('Section Title', 'pillar-blog'),
		'section'     => 'recent_posts_section',   
		'settings'    => 'recent_posts_section_title',	
		'type'        => 'text'
	)
);

$wp_customize->add_setting('recent_posts_category', 
	array(
		'default' 			=> '',
		'type'              => 'theme_mod',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'cube_blog_sanitize_select',
		'transport'         => 'refresh',
	)
);

$wp_customize->add_control('recent_posts_category', 
	array(		
		'label' 	=> __('Select Categories', 'pillar-blog'),
		'section' 	=> 'recent_posts_section',
		'settings'  => 'recent_posts_category',
		'type' 		=> 'select',
		'choices' 	=> pillar_blog_get_post_categories(),
	)
);