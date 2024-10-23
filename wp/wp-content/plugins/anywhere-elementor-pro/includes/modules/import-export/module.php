<?php

namespace Aepro\Modules\ImportExport;

use Aepro\Base\ModuleBase;
use Aepro\Modules\ImportExport\ImportTemplate;
use Aepro\Modules\ImportExport\ExportTemplate;

class Module extends ModuleBase {
	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_filter(
			'aepro/admin/ui/header_menu',
			function( $nav_links ) {
				$nav_links[6] = [
					'id'    => 'import-ae_global_templates',
					'label' => __( 'Import Template', 'ae-pro' ),
					'link'  => admin_url( 'edit.php?post_type=ae_global_templates&ae_import_template=1' ),
				];
				return $nav_links;
			}
		);
		ImportTemplate::instance();
		ExportTemplate::instance();
	}
}
