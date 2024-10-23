<?php

namespace Aepro\Modules\DuplicateTemplate;

use Aepro\Base\ModuleBase;
use Aepro\Modules\DuplicateTemplate\Duplicate;

class Module extends ModuleBase {
	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		Duplicate::instance();
	}
}
