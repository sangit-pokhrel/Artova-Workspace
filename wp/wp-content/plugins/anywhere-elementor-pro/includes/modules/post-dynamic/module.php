<?php

namespace Aepro\Modules\PostDynamic;

use Aepro\Base\ModuleBase;

class Module extends ModuleBase {
	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );
	}

	public function register_dynamic_tags( $dynamic_tags ) {

		\Elementor\Plugin::$instance->dynamic_tags->register_group(
			'ae-post-dynamic',
			[
				'title' => __( 'Post (AE)', 'ae-pro' ),
			]
		);

		if ( \Aepro\Plugin::$_level >= 1 ) {
			//--Post Dynamic
			$dynamic_tags->register( new Post_Title() );
			$dynamic_tags->register( new Post_Featured_Image() );
			$dynamic_tags->register( new Post_Custom_Field() );
			$dynamic_tags->register( new Post_Term() );
			$dynamic_tags->register( new Post_Url() );
			$dynamic_tags->register( new Post_Date() );
			$dynamic_tags->register( new Post_Time() );
			$dynamic_tags->register( new Post_Excerpt() );
			$dynamic_tags->register( new Post_Gallery() );
		}
	}

}
