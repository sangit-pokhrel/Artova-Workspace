<?php
namespace Aepro\Modules\DuplicateTemplate;

use Elementor\Plugin;

class Duplicate {
	private static $_instance;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
		add_filter( 'bulk_actions-edit-ae_global_templates', [ $this, 'admin_duplicate_template_action' ] );
		add_action( 'wp_ajax_ae_duplicate_template', [ $this, 'ae_duplicate_template' ] );
		add_action( 'wp_ajax_nopriv_ae_duplicate_template', [ $this, 'ae_duplicate_template' ] );
		add_action( 'admin_notices', [ $this, 'ae_duplicate_notice' ] );
	}

	public function admin_duplicate_template_action( $actions ) {
		$actions['ae_duplicate_multiple_templates'] = esc_html__( 'Duplicate', 'ae-pro' );
		return $actions;
	}

	public function post_row_actions( $actions, \WP_Post $post ) {
		global $current_screen;

		if ( ! $current_screen ) {
			return $actions;
		}
		if ( 'edit' === $current_screen->base && 'ae_global_templates' === $current_screen->post_type ) {
			$document = Plugin::$instance->documents->get( $post->ID );
			if ( $document && $document->is_built_with_elementor() ) {
				$elementor_data = get_post_meta($post->ID, '_elementor_data', true);
				$elementor_data_arr = json_decode($elementor_data);

				if(isset($elementor_data_arr) && count($elementor_data_arr)){
					$actions['duplicate-template'] = sprintf( '<a href="%1$s">%2$s</a>', $this->get_duplicate_link($post->ID), esc_html__( 'Duplicate', 'ae-pro' ) );
				}
			}
		}

		return $actions;
	}

	public function get_duplicate_link( $post_id ) {
		return add_query_arg(
			[
				'action'         => 'ae_duplicate_template',
				'library_action' => 'duplicate_template',
				'source'         => 'local',
				'_nonce'         => wp_create_nonce( 'aep_nonce' ),
				'post_id'    => $post_id,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	//public function ae_duplicate_template($post_id){
	public function ae_duplicate_template(){
		if ( ! wp_verify_nonce( $_REQUEST['_nonce'], 'aep_nonce' ) ) {
			wp_die();
		}
		$post_id = $_REQUEST['post_id'];
		$title = get_the_title($post_id);
		$oldpost = get_post($post_id);

		$post = [
			'post_title' => $title . '-copy',
			'post_name' => sanitize_title($title . '-copy'),
			'post_status' => 'draft',
			'post_type' => $oldpost->post_type,
		];

		$new_post_id = wp_insert_post($post);

		$meta_data = get_post_meta($post_id);

		foreach($meta_data as $key => $meta){
			$meta_key = $key;
			$meta_value = maybe_unserialize($meta[0]);
			add_post_meta($new_post_id, $meta_key, $meta_value);
		}

		$taxonomies = get_post_taxonomies($post_id);

		if ($taxonomies) {
			foreach ($taxonomies as $taxonomy) {
				wp_set_object_terms(
					$new_post_id,
					wp_get_object_terms(
						$post_id,
						$taxonomy,
						['fields' => 'ids']
					),
					$taxonomy
				);
			}
		}

		//return $new_post_id;
		wp_redirect(admin_url('edit.php?post_type=ae_global_templates&ae_duplicate_template=done'));

		exit;
	}

	public function ae_duplicate_notice(){
		if ( ! isset( $_GET['ae_duplicate_template'] ) || 'done' !== $_GET['ae_duplicate_template'] ) {
			return;
		}
		$class = 'notice notice-success is-dismissible';
		$message = __( 'AnyWhere Elementor Pro: Template duplicated successfully!', 'ae-pro' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}
Duplicate::instance();