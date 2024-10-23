<?php

namespace Aepro\Admin;

class PostType {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {

		add_action( 'init', [ $this, 'register_post_type' ], 0 );
	}

	public function register_post_type() {

		$labels = [
			'name'               => _x( 'AE Global Templates', 'Post Type General Name', 'ae-pro' ),
			'singular_name'      => _x( 'AE Template', 'Post Type Singular Name', 'ae-pro' ),
			'menu_name'          => __( 'AE Templates', 'ae-pro' ),
			'name_admin_bar'     => __( 'AE Templates', 'ae-pro' ),
			'archives'           => __( 'List Archives', 'ae-pro' ),
			'parent_item_colon'  => __( 'Parent List:', 'ae-pro' ),
			'all_items'          => __( 'All AE Templates', 'ae-pro' ),
			'add_new_item'       => __( 'Add New AE Template', 'ae-pro' ),
			'add_new'            => __( 'Add New', 'ae-pro' ),
			'new_item'           => __( 'New AE Template', 'ae-pro' ),
			'edit_item'          => __( 'Edit AE Template', 'ae-pro' ),
			'update_item'        => __( 'Update AE Template', 'ae-pro' ),
			'view_item'          => __( 'View AE Template', 'ae-pro' ),
			'search_items'       => __( 'Search AE Template', 'ae-pro' ),
			'not_found'          => __( 'Not found', 'ae-pro' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'ae-pro' ),
		];
		$args   = [
			'label'               => __( 'Post List', 'ae-pro' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor' ],
			'public'              => true,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode(
				'<svg width="640px" height="778px" viewBox="0 0 640 778" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g id="aep-logo" fill-rule="nonzero">
                    <path d="M148,432.6 C148,450.1 153.3,464.1 163.9,474.6 C174.5,485.1 189.1,490.3 207.6,490.3 C232.7,490.3 251.6,479.6 264.4,458.2 L265.1,458.2 L265.1,490.3 L320.8,490.3 L320.8,444.6 C324.6,452.1 329.4,458.8 335.3,464.7 C352.3,481.8 376.2,490.3 407.1,490.3 L475.9,490.3 L475.9,447.8 L420.5,448.5 C387.6,448.5 370.1,434.6 368.1,406.9 L492.6,406.9 L492.6,382.1 C492.6,353.8 485,331.3 469.8,314.8 C454.6,298.3 433.1,290 405.2,290 C378.4,290 355.7,299.3 337.4,317.8 C329.2,326 323,335.6 318.5,346.4 C310.9,308.9 284.5,290 239.3,290 C227.6,290 214.7,291.6 200.4,294.8 C186.2,298 175,301.8 166.8,306 L166.8,350.4 C187.3,336.9 208.9,330.1 231.7,330.1 C254.3,330.1 265.6,340.5 265.6,361.4 L213.8,368.3 C170,374.2 148,395.5 148,432.6 Z M380.5,341.6 C387.4,333.8 395.6,329.9 405,329.9 C427.1,329.9 438.2,343.6 438.2,370.9 L367.9,370.9 C369.4,359.2 373.6,349.4 380.5,341.6 Z M265.7,395.3 L265.7,408.2 C265.7,419.9 262.2,429.5 255.3,437.2 C248.3,444.8 239.3,448.7 228.3,448.7 C220.4,448.7 214,446.6 209.4,442.3 C204.7,438 202.4,432.5 202.4,425.8 C202.4,411 212,402.4 231.1,399.9 L265.7,395.3 Z" id="Shape"></path>
                    <path d="M480.2,85.8 C479.8,85.8 479.5,85.8 479.1,85.8 L161.4,85.8 C72.7,85.8 0.5,157.9 0.5,246.7 L0.5,533.7 L0.5,572.8 L78.3,572.8 L77.8,533.7 L77.8,246.7 C77.8,200.5 115.3,163.1 161.4,163.1 L616.6,163.1 L640,163.1 L480.2,0.7 L480.2,85.8 Z" id="Path"></path>
                    <path d="M562.7,246.7 L562.7,533.7 C562.7,579.9 525.2,617.3 479.1,617.3 L162.8,617.3 L23.9,617.3 L0.5,617.3 L162.9,777.1 L162.9,694.6 L479.1,694.6 C567.8,694.6 640,622.5 640,533.7 L640,246.7 L640,207.6 L562.3,207.6 L562.7,246.7 Z" id="Path"></path>
                </g>
            </g>
        </svg>'
			),
		];
		register_post_type( 'ae_global_templates', $args );
	}
}
