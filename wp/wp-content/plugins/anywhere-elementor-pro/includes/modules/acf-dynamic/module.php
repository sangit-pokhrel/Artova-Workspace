<?php

namespace Aepro\Modules\AcfDynamic;

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

		if ( \Aepro\Plugin::$_level >= 2 ) {
			if ( AE_ACF ) {

				\Elementor\Plugin::$instance->dynamic_tags->register_group(
					'ae-dynamic',
					[
						'title' => __( 'ACF (AE)', 'ae-pro' ),
					]
				);

				//--Acf Dynamic
				$dynamic_tags->register( new  Text() );
				$dynamic_tags->register( new  Number() );
				$dynamic_tags->register( new  Url() );
				$dynamic_tags->register( new  Image() );
				$dynamic_tags->register( new  Color() );

				// ACF-Group Dynamic
				$dynamic_tags->register( new  Group\Text() );
				$dynamic_tags->register( new  Group\Image() );
				$dynamic_tags->register( new  Group\Url() );
				$dynamic_tags->register( new  Group\Number() );
				$dynamic_tags->register( new  Group\Color() );
			}

			if ( AE_ACF_PRO ) {
				//--Acf Dynamic
				$dynamic_tags->register( new Gallery() );
				//--ACF Group Dynamic
				$dynamic_tags->register( new Group\Gallery() );
				//--Acf Repeater Fields
				$dynamic_tags->register( new Repeater\Text() );
				$dynamic_tags->register( new Repeater\Option() );
				$dynamic_tags->register( new Repeater\Url() );
				$dynamic_tags->register( new Repeater\Image() );
				$dynamic_tags->register( new Repeater\Gallery() );
				$dynamic_tags->register( new Repeater\Boolean() );

				//--Acf Flexible Fields
				$dynamic_tags->register( new Flexible\Text() );
				$dynamic_tags->register( new Flexible\Image() );
				$dynamic_tags->register( new Flexible\Url() );
				$dynamic_tags->register( new Flexible\Number() );
				$dynamic_tags->register( new Flexible\Gallery() );
			}
		}
	}

}
