<?php
namespace Aepro\Modules\ImportExport;

use Elementor\Core\DocumentTypes\Post;
use Elementor\Plugin;
use Elementor\Utils;

class ImportTemplate {
	private static $_instance;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'views_edit-ae_global_templates', [ $this, 'admin_import_template_form' ] );
		add_action( 'wp_ajax_ae_import_template', [ $this, 'ae_import_template' ] );
		add_action( 'wp_ajax_nopriv_ae_import_template', [ $this, 'ae_import_template' ] );
	}

	public function admin_import_template_form( array $views ) {
		global $current_screen;

		if ( ! $current_screen ) {
			return false;
		}

		if ( 'edit' !== $current_screen->base && 'ae_global_templates' !== $current_screen->post_type ) {
			return;
		}

		?>
		<div id="elementor-hidden-area" class="ae-import-form">
			<!-- <a id="elementor-import-template-trigger" class="page-title-action ae-import-template-btn"><?php echo esc_html__( 'Import Templates', 'ae-pro' ); ?></a> -->
			<div id="elementor-import-template-area">
				<div id="elementor-import-template-title"><?php echo esc_html__( 'Choose an AnyWhere Elementor Pro template JSON file or a .zip archive of AEPro templates, and add them to the list of templates available in AE Templates.', 'ae-pro' ); ?></div>
				<form id="elementor-import-template-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="ae_import_template">
					<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce( 'aep_ajax_nonce' ); ?>">
					<fieldset id="elementor-import-template-form-inputs">
						<input type="file" name="file" accept=".json,application/json,.zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed" required>
						<input id="e-import-template-action" type="submit" class="button" value="<?php echo esc_attr__( 'Import Now', 'ae-pro' ); ?>">
					</fieldset>
				</form>
			</div>
		</div>
		<?php
		return $views;
	}

	public function ae_import_template() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_nonce'], 'aep_ajax_nonce' ) ) {
			_default_wp_die_handler( 'Access Denied', 'ae-pro' );
		}

		$result = $this->import_template( $_FILES['file']['name'], $_FILES['file']['tmp_name'] );

		if ( is_wp_error( $result ) ) {
			_default_wp_die_handler( $result->get_error_message() . '.' );
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=ae_global_templates' ) );

		die();
	}

	public function import_template( $name, $path ) {

		if ( empty( $path ) ) {
			return new \WP_Error( 'file_error', 'Please upload a file to import' );
		}

		$items = [];

		if ( 'zip' === pathinfo( $name, PATHINFO_EXTENSION ) ) {
			$extracted_files = Plugin::$instance->uploads_manager->extract_and_validate_zip( $path, [ 'json' ] );

			if ( is_wp_error( $extracted_files ) ) {
				// Delete the temporary extraction directory, since it's now not necessary.
				Plugin::$instance->uploads_manager->remove_file_or_dir( $extracted_files['extraction_directory'] );

				return $extracted_files;
			}

			foreach ( $extracted_files['files'] as $file_path ) {
				$import_result = $this->import_single_template( $file_path );

				if ( is_wp_error( $import_result ) ) {
					// Delete the temporary extraction directory, since it's now not necessary.
					Plugin::$instance->uploads_manager->remove_file_or_dir( $extracted_files['extraction_directory'] );

					return $import_result;
				}

				$items[] = $import_result;
			}

			// Delete the temporary extraction directory, since it's now not necessary.
			Plugin::$instance->uploads_manager->remove_file_or_dir( $extracted_files['extraction_directory'] );
		} else {

			// If the import file is a single JSON file
			$import_result = $this->import_single_template( $path );

			if ( is_wp_error( $import_result ) ) {
				return $import_result;
			}

			$items[] = $import_result;
		}

		return $items;
	}

	public function import_single_template( $path ) {

		$data = json_decode( file_get_contents( $path ), true );

		if ( empty( $data ) ) {
			return new \WP_Error( 'file_error', 'Invalid File' );
		}

		$content = $data['content'];

		if ( ! is_array( $content ) ) {
			return new \WP_Error( 'file_error', 'Invalid Content In File' );
		}

		$template_id = $this->save_item(
			[
				'content'       => $content,
				'title'         => $data['title'],
				'type'          => $data['type'],
				'meta_data'     => $data['meta_data'],
				'page_settings' => $data['page_settings'],
			]
		);

		// Remove the temporary file, now that we're done with it.
		Plugin::$instance->uploads_manager->remove_file_or_dir( $path );

		if ( is_wp_error( $template_id ) ) {
			return $template_id;
		}

		return $template_id;
	}

	public function save_item( $template_data ) {

		$defaults = [
			'title'         => esc_html__( '(no title)', 'ae-pro' ),
			'page_settings' => [],
			'status'        => current_user_can( 'publish_posts' ) ? 'publish' : 'pending',
		];

		$template_data = wp_parse_args( $template_data, $defaults );

		$post_data = [
			'post_title'   => $template_data['title'],
			'post_content' => json_encode( $template_data['content'] ),
			'post_status'  => $template_data['status'],
			'meta_data'    => $template_data['meta_data'],
			'post_type'    => $template_data['type'],
			'meta_input'   => [
				'_elementor_edit_mode'     => 'builder',
				'_elementor_template_type' => 'post',
			],
		];

		if ( empty( $post_data['post_title'] ) ) {
			$post_data['post_title'] = esc_html__( 'AE Template', 'ae-pro' );
			$update_title            = true;
		}

		$post_id = wp_insert_post( $post_data );

		if ( isset( $post_data['meta_data'] ) ) {
			foreach ( $post_data['meta_data'] as $key => $item ) {
				$key = sanitize_text_field( $key );
				update_post_meta( $post_id, $key, sanitize_text_field( $item ) );
			}
		}

		if ( $update_title ) {
			$post_data['ID']          = $post_id;
			$post_data['post_title'] .= ' #' . $post_id;
			// The meta doesn't need update.
			unset( $post_data['meta_input'] );
			wp_update_post( $post_data );
		}

		$document = new Post(
			[
				'post_id' => $post_id,
			]
		);

		$document->save( [] );

		if ( is_wp_error( $document ) ) {
			/**
			 * @var \WP_Error $document
			 */
			return $document;
		}

		if ( ! empty( $template_data['content'] ) ) {
			$template_data['content'] = Plugin::$instance->db->iterate_data(
				$template_data['content'],
				function( $element ) {
									$element['id'] = Utils::generate_random_string();
									return $element;
				}
			);
		}

		$document->save(
			[
				'elements' => $template_data['content'],
				'settings' => $template_data['page_settings'],
			]
		);

		$template_id = $document->get_main_id();

		return $template_id;
	}
}
ImportTemplate::instance();
