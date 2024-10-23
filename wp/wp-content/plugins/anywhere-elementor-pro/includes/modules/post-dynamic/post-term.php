<?php
namespace Aepro\Modules\PostDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;


class Post_Term extends Tag {

	public function get_name() {
		return 'ae-post-term';
	}

	public function get_title() {
		return __( '(AE) Post Term', 'ae-pro' );
	}

	public function get_group() {
		return 'ae-post-dynamic';
	}

	public function get_panel_template_setting_key() {
		return 'key';
	}

	public function is_settings_required() {
		return true;
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label'   => __( 'Taxonomy', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->ae_get_post_taxomony(),
				'default' => '',
			]
		);

		$this->add_control(
			'separator',
			[
				'label'   => __( 'Separator', 'ae-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => ', ',
			]
		);

		$this->add_control(
			'link',
			[
				'label'   => __( 'Link', 'ae-pro' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['key'] ) ) {
			return;
		}

		$post_data = Aepro::$_helper->get_demo_post_data();
		$post_id   = $post_data->ID;
		if ( 'yes' === $settings['link'] ) {
			$value = get_the_term_list( $post_id, $settings['key'], '', $settings['separator'] );
		} else {
			$terms = get_the_terms( $post_id, $settings['key'] );

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				return '';
			}

			$term_names = [];

			foreach ( $terms as $term ) {
				$term_names[] = '<span>' . $term->name . '</span>';
			}

			$value = implode( $settings['separator'], $term_names );
		}

		if ( is_wp_error( $value ) ) {
				return '';
		}

		echo wp_kses_post( $value );
	}

	public function ae_get_post_taxomony() {
		$options              = [
			'' => __( 'Select...', 'ae-pro' ),
		];
		$post_data            = Aepro::$_helper->get_demo_post_data();
		$post_type            = get_post_type( $post_data->ID );
		$taxonomy_filter_args = [
			'show_in_nav_menus' => true,
			'object_type'       => [ $post_type ],
		];
		//TODO::Change Filter name

		$taxonomy_filter_args = apply_filters( 'ae_post_dynamic_tax_filter', $taxonomy_filter_args );
		$taxonomies           = $this->get_taxonomies( $taxonomy_filter_args, 'object' );

		foreach ( $taxonomies as $taxonomy => $object ) {
			$options[ $taxonomy ] = $object->label;
		}

		return $options;
	}


	public static function get_taxonomies( $args = [], $output = 'names', $operator = 'and' ) {
		global $wp_taxonomies;
		$field = ( 'names' === $output ) ? 'name' : false;

		// Handle 'object_type' separately.
		if ( isset( $args['object_type'] ) ) {
			$object_type = (array) $args['object_type'];
			unset( $args['object_type'] );
		}

		$taxonomies = wp_filter_object_list( $wp_taxonomies, $args, $operator );
		if ( isset( $object_type ) ) {
			foreach ( $taxonomies as $tax => $tax_data ) {
				if ( ! array_intersect( $object_type, $tax_data->object_type ) ) {
					unset( $taxonomies[ $tax ] );
				}
			}
		}

		if ( $field ) {
			$taxonomies = wp_list_pluck( $taxonomies, $field );
		}

		return $taxonomies;
	}
}
