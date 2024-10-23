<?php
namespace Aepro\Modules\ImportExport;

use Elementor\Plugin;

class ExportTemplate {
	private static $_instance;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
		add_filter( 'bulk_actions-edit-ae_global_templates', [ $this, 'admin_add_bulk_export_action' ] );
		add_filter( 'handle_bulk_actions-edit-ae_global_templates', [ $this, 'admin_export_multiple_templates' ], 10, 3 );
		add_action( 'wp_ajax_ae_export_template', [ $this, 'ae_export_template' ] );
		add_action( 'wp_ajax_nopriv_ae_export_template', [ $this, 'ae_export_template' ] );
	}

	public function admin_add_bulk_export_action( $actions ) {
		$actions['ae_export_multiple_templates'] = esc_html__( 'Export', 'ae-pro' );
		return $actions;
	}

	public function post_row_actions( $actions, \WP_Post $post ) {
		global $current_screen;

		if ( ! $current_screen ) {
			return $actions;
		}
		if ( 'edit' === $current_screen->base && 'ae_global_templates' === $current_screen->post_type ) {
			$document = Plugin::$instance->documents->get( $post->ID );
			if ( $document || $document->is_built_with_elementor() ) {
				$elementor_data = get_post_meta($post->ID, '_elementor_data', true);
				$elementor_data_arr = json_decode($elementor_data);

				if(isset($elementor_data_arr) && count($elementor_data_arr)){
					$actions['export-template'] = sprintf( '<a href="%1$s">%2$s</a>', $this->get_export_link( $post->ID ), esc_html__( 'Export Template', 'ae-pro' ) );
				}
			}
		}

		return $actions;
	}

	public function get_export_link( $template_id ) {
		return add_query_arg(
			[
				'action'         => 'ae_export_template',
				'library_action' => 'export_template',
				'source'         => 'local',
				'_nonce'         => wp_create_nonce( 'aep_ajax_nonce' ),
				'template_id'    => $template_id,
			],
			admin_url( 'admin-ajax.php' )
		);
	}

	public function ae_export_template() {
		$args = $_REQUEST;

		$not_specified_args = array_diff( [ 'source', 'template_id' ], array_keys( array_filter( $args ) ) );

		if ( $not_specified_args ) {
			return new \WP_Error( 'arguments_not_specified', sprintf( 'The required argument(s) "%s" not specified.', implode( ', ', $not_specified_args ) ) );
		}

		$source = $args['source'];

		if ( ! $source ) {
			return new \WP_Error( 'template_error', 'Template source not found' );
		}

		$file_data = $this->prepare_template_export( $args['template_id'] );

		if ( is_wp_error( $file_data ) ) {
			return $file_data;
		}

		$this->send_file_headers( $file_data['name'], strlen( $file_data['content'] ) );

		// Clear buffering just in case.
		@ob_end_clean();

		flush();

		// Output file contents.
		// PHPCS - Export widget json

		echo $file_data['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		die;
	}

	public function prepare_template_export( $template_id ) {

		$document = Plugin::$instance->documents->get( $template_id );

		$template_data = $document->get_export_data();

		if ( empty( $template_data['content'] ) ) {
			return new \WP_Error( 'empty_template', 'The template is empty' );
		}

		$export_data = [
			'content'       => $template_data['content'],
			'page_settings' => $template_data['settings'],
			'meta_data'     => $template_data['metadata'],
			'version'       => '0.4',
			'title'         => $document->get_main_post()->post_title,
			'type'          => 'ae_global_templates',
		];

		return [
			'name'    => 'aepro-' . $template_id . '-' . gmdate( 'Y-m-d' ) . '.json',
			'content' => wp_json_encode( $export_data ),
		];
	}

	public function send_file_headers( $file_name, $file_size ) {
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $file_name );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . $file_size );
	}

	public function admin_export_multiple_templates( $redirect_to, $action, $post_ids ) {
		if ( 'ae_export_multiple_templates' === $action ) {
			$result = $this->export_multiple_templates( $post_ids );

			// If you reach this line, the export failed
			// PHPCS - Not user input.
			wp_die( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function export_multiple_templates( array $template_ids ) {
		$files = [];

		$wp_upload_dir = wp_upload_dir();

		$temp_path = $wp_upload_dir['basedir'] . '/aepro/tmp';

		// Create temp path if it doesn't exist
		wp_mkdir_p( $temp_path );

		// Create all json files
		foreach ( $template_ids as $template_id ) {
			$file_data = $this->prepare_template_export( $template_id );

			if ( is_wp_error( $file_data ) ) {
				continue;
			}

			$complete_path = $temp_path . '/' . $file_data['name'];

			$put_contents = file_put_contents( $complete_path, $file_data['content'] );

			if ( ! $put_contents ) {
				return new \WP_Error( '404', sprintf( 'Cannot create file "%s".', $file_data['name'] ) );
			}

			$files[] = [
				'path' => $complete_path,
				'name' => $file_data['name'],
			];
		}

		if ( ! $files ) {
			return new \WP_Error( 'empty_files', 'There is no files to export (probably all the requested templates are empty).' );
		}

		// Create temporary .zip file
		$zip_archive_filename = 'aepro-templates-' . gmdate( 'Y-m-d' ) . '.zip';

		$zip_archive = new \ZipArchive();

		$zip_complete_path = $temp_path . '/' . $zip_archive_filename;

		$zip_archive->open( $zip_complete_path, \ZipArchive::CREATE );

		foreach ( $files as $file ) {
			$zip_archive->addFile( $file['path'], $file['name'] );
		}

		$zip_archive->close();

		foreach ( $files as $file ) {
			unlink( $file['path'] );
		}

		$this->send_file_headers( $zip_archive_filename, filesize( $zip_complete_path ) );

		@ob_end_flush();

		@readfile( $zip_complete_path );

		unlink( $zip_complete_path );

		die;
	}
}
ExportTemplate::instance();
