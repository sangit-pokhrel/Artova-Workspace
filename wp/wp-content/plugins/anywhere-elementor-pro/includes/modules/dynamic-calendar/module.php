<?php

namespace Aepro\Modules\DynamicCalendar;

use Aepro\Base\ModuleBase;
use Elementor\Plugin;

class Module extends ModuleBase {

	public function get_widgets() {
		return [
			'ae-dynamic-calendar',
		];
	}

	public function __construct() {
		parent::__construct();
		add_action( 'wp_ajax_ae_dynamic_calendar_data', [ $this, 'ajax_dynamic_calendar_data' ] );
		add_action( 'wp_ajax_nopriv_ae_dynamic_calendar_data', [ $this, 'ajax_dynamic_calendar_data' ] );
	}

	public function ajax_dynamic_calendar_data() {
		ob_start();
		$this->get_widget_output( $_POST['pid'], $_POST['wid'] );
		$results = ob_get_contents();
		ob_end_clean();
		wp_send_json_success( $results );
	}

	public function get_widget_output( $post_id, $widget_id ) {
		$elementor = Plugin::$instance;

		$meta = $elementor->documents->get( $post_id )->get_elements_data();

		$widget = $this->find_element_recursive( $meta, $widget_id );

		$widget_instance = $elementor->elements_manager->create_element_instance( $widget );

		$widget['settings'] = $widget_instance->get_active_settings();

		if ( isset( $widget['settings'] ) ) {

			if ( $widget['widgetType'] === 'ae-dynamic-calendar' ) {

				$current_skin = $widget_instance->get_current_skin();
				$current_skin->set_parent( $widget_instance );
				$current_skin->generate_output( $widget['settings'], false );

			}
		}
	}

	private function find_element_recursive( $elements, $widget_id ) {
		foreach ( $elements as $element ) {
			if ( $widget_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $widget_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}
}
