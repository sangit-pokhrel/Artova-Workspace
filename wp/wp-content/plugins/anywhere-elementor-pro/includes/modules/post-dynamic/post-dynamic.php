<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Core\DynamicTags\Base_Tag;

class AcfDynamicHelper {

	public static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function ae_get_post_data() {
	}
}
