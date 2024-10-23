<?php

namespace Aepro\Admin;

class MetaBoxes {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		add_filter( 'manage_ae_global_templates_posts_columns', [ $this, 'add_column' ] );
		add_action( 'manage_ae_global_templates_posts_custom_column', [ $this, 'column_data' ], 10, 2 );
	}

	public function add_meta_boxes() {
		add_meta_box( 'ae-shortcode-box', 'Anywhere Elementor Usage', [ $this, 'ae_shortcode_box' ], 'ae_global_templates', 'side', 'high' );
	}

	public function ae_shortcode_box( $post ) {
		?>
		<h4 style="margin-bottom:5px;">Shortcode</h4>
		<input type='text' class='widefat' value='[INSERT_ELEMENTOR id="<?php echo esc_attr( $post->ID ); ?>"]' readonly="">
		<h4 style="margin-bottom:5px;">Php Code</h4>
		<input type='text' class='widefat' value="&lt;?php echo do_shortcode('[INSERT_ELEMENTOR id=&quot;<?php echo esc_attr( $post->ID ); ?>&quot;]'); ?&gt;" readonly="">
		<?php
	}

	public function add_column( $columns ) {
		$columns['ae_shortcode_column'] = __( 'Shortcode', 'ae-pro' );
		return $columns;
	}

	public function column_data( $column, $post_id ) {
		switch ( $column ) {

			case 'ae_shortcode_column':
				echo '<input type=\'text\' class=\'widefat\' value=\'[INSERT_ELEMENTOR id="' . esc_html( $post_id ) . '"]\' readonly="">';
				break;
		}
	}

}
