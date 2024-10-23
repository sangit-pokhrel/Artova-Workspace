<?php

namespace Aepro\Modules\DynamicRules;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Repeater;
use Aepro\Aepro_Control_Manager;
use Aepro\Aepro;
use Aepro\Base\RuleBase;
use Aepro\Frontend;
use Aepro\Classes\CacheManager;

class Module extends RuleBase {

	private static $_instance = null;

	protected $rules  = [];
	protected $_rules = [];

	protected $_rules_repeater;

	const USER_GROUP    = 'user';
	const SINGLE_GROUP  = 'single';
	const ARCHIVE_GROUP = 'archive';
	const ACF_GROUP     = 'acf';


	public function get_groups() {
		return [
			self::USER_GROUP => [
				'label' => __( 'User', 'ae-pro' ),
			],
			self::SINGLE_GROUP => [
				'label' => __( 'Single', 'ae-pro' ),
			],
			self::ARCHIVE_GROUP => [
				'label' => __( 'Archive', 'ae-pro' ),
			],
			self::ACF_GROUP => [
				'label' => __( 'Advanced Custom Fields', 'ae-pro' ),
			],
		];
	}

	public function register_rules() {

		$available_rules = [
			// User
			'validation',
			'user',
			'user_role',

			// Singular
			'post',
			//'page',
			'default_page',
			'post_type',
			'post_term',

			// Archive
			'taxonomy_archive',
			'term_archive',
			'post_type_archive',
			'date_archive',
			'author_archive',
			'search_results',

		];
		if ( \Aepro\Plugin::show_acf() || \Aepro\Plugin::show_acf( true ) ) {
			$acf_available_rules = [
				// ACF
				'acf_text',
				'acf_choice',
				'acf_true_false',
				'acf_post',
				'acf_taxonomy',
				'acf_date_time',
				'acf_image',
			];

			$available_rules = array_merge( $acf_available_rules, $available_rules );
		}

		if ( \Aepro\Plugin::show_acf( true ) ) {
			$acf_pro_available_rules = [
				'acf_relationship',
			];

			$available_rules = array_merge( $available_rules, $acf_pro_available_rules );
		}

		foreach ( $available_rules as $rule_name ) {

			$class_name = str_replace( '-', '', $rule_name );
			$class_name = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $rule_name ) ) );
			$class_name = str_replace( ' ', '', ucwords( $class_name ) );
			$class_name = __NAMESPACE__ . '\\Rules\\' . $class_name;

			if ( class_exists( $class_name ) ) {
				if ( $class_name::is_supported() ) {
					$this->_rules[ $rule_name ] = $class_name::instance();
				}
			}
		}
	}

	public function get_all_post_types() {
		$post_types = Aepro::$_helper->get_all_post_types();

		return $post_types;
	}

	public static function get_ae_templates() {
		$block_layouts = [];
		$block_layouts = CacheManager::instance()->get_block_layouts();
		return $block_layouts;
	}

	public function select_elementor_template( $type ) {
		$args = [
			'tax_query'      => [
				[
					'taxonomy' => 'elementor_library_type',
					'field'    => 'slug',
					'terms'    => $type,
				],
			],
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
		];

		$items = [];

		$posts = get_posts( $args );

		foreach ( $posts as $post ) {
			$items[ $post->ID ] = $post->post_title;
		}

		return $items;
	}

	private function get_rules_options() {

		$groups = $this->get_groups();
		foreach ( $this->_rules as $_rules ) {
			$groups[ $_rules->get_group() ]['options'][ $_rules->get_name() ] = $_rules->get_title();
		}

		return $groups;
	}

	public function add_field( $element ) {
		$element_type = $element->get_type();

		$element->start_controls_section(
			'dynamic_rule_section',
			[
				'tab'   => Aepro_Control_Manager::TAB_AE_PRO,
				'label' => __( 'Dynamic Rule', 'ae-pro' ),
			]
		);

		$element->add_control(
			'enable_dynamic_rules',
			[
				'label'        => __( 'Enable', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$element->add_control(
			'rule_relation',
			[
				'label'     => __( 'Relation', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'any' => __( 'Any', 'ae-pro' ),
					'all' => __( 'All', 'ae-pro' ),
				],
				'default'   => 'any',
				'condition' => [
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$element->add_control(
			'ae_show_hide_on_rules',
			[
				'label'        => __( 'Show/Hide', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$element->add_control(
			'show_all_element_notice',
			[
				'type'      => Controls_Manager::RAW_HTML,
				//phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'raw'       => __( '<div class="ae-dynamic-rules-notice">Show the element if all of the conditions are met</div>', 'ae-pro' ),
				'condition' => [
					'enable_dynamic_rules'  => 'yes',
					'ae_show_hide_on_rules' => 'yes',
					'rule_relation'         => 'all',
				],
			]
		);

		$element->add_control(
			'hide_all_element_notice',
			[
				'type'      => Controls_Manager::RAW_HTML,
				//phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'raw'       => __( '<div class="ae-dynamic-rules-notice">Hide the element if all of the conditions are met</div>', 'ae-pro' ),
				'condition' => [
					'enable_dynamic_rules'  => 'yes',
					'ae_show_hide_on_rules' => '',
					'rule_relation'         => 'all',
				],
			]
		);

		$element->add_control(
			'show_any_element_notice',
			[
				'type'      => Controls_Manager::RAW_HTML,
				//phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'raw'       => __( '<div class="ae-dynamic-rules-notice">Show the element if any of the condition is met</div>', 'ae-pro' ),
				'condition' => [
					'enable_dynamic_rules'  => 'yes',
					'ae_show_hide_on_rules' => 'yes',
					'rule_relation'         => 'any',
				],
			]
		);

		$element->add_control(
			'hide_any_element_notice',
			[
				'type'      => Controls_Manager::RAW_HTML,
				//phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'raw'       => __( '<div class="ae-dynamic-rules-notice">Hide the element if any of the condition is met</div>', 'ae-pro' ),
				'condition' => [
					'enable_dynamic_rules'  => 'yes',
					'ae_show_hide_on_rules' => '',
					'rule_relation'         => 'any',
				],
			]
		);

		$this->_rules_repeater = new Repeater();

		$this->_rules_repeater->add_control(
			'ae_rule_type',
			[
				'label'   => __( 'Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'validation',
				'groups'  => $this->get_rules_options(),
			]
		);
		$blank        = [
			' ' => __( 'Select', 'ae-pro' ),
		];
		$post_types   = Aepro::$_helper->get_rule_post_types();
		$post_options = array_merge( $blank, $post_types );
		$this->_rules_repeater->add_control(
			'ae_rule_post_types',
			[
				'label'     => __( 'Post Types', 'ae-pro' ),
				'type'      => Controls_manager::SELECT,
				'options'   => $post_options,
				'condition' => [
					'ae_rule_type' => 'post',
				],
				'default'   => 'post',
			]
		);

		$this->add_name_controls();

		$this->add_multiple_name_controls();

		//Only to Compare Date Picker Type
		$this->_rules_repeater->add_control(
			'ae_rule_datepicker_type',
			[
				'label'     => __( 'Compare', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					' '         => __( 'Select', 'ae-pro' ),
					'time'      => __( 'Time', 'ae-pro' ),
					'date'      => __( 'Date', 'ae-pro' ),
					'date_time' => __( 'DateTime', 'ae-pro' ),
				],
				'default'   => ' ',
				'condition' => [
					'ae_rule_type' => 'acf_date_time',
				],
			]
		);

		$taxonomies              = [];
		$ae_taxonomy_filter_args = [
			'show_in_nav_menus' => true,
		];
		$ae_taxonomies           = get_taxonomies( $ae_taxonomy_filter_args, 'objects' );

		foreach ( $ae_taxonomies as $key => $taxonomy ) {
			$taxonomies[ $key ] = $taxonomy->label;
		}
		$default_taxo = '';
		$taxonomies   = array_merge( $blank, $taxonomies );
		array_key_exists( 'category', $taxonomies ) ? $default_taxo = 'category' : $default_taxo = ' ';

		$this->_rules_repeater->add_control(
			'ae_rule_acf_taxonomy_types',
			[
				'label'     => __( 'Taxonomy', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $taxonomies,
				'condition' => [
					'ae_rule_type' => 'acf_taxonomy',
				],
				'default'   => $default_taxo,
			]
		);

		$this->_rules_repeater->add_control(
			'ae_rule_term_archive_types',
			[
				'label'     => __( 'Taxonomy', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $taxonomies,
				'condition' => [
					'ae_rule_type' => 'term_archive',
				],
				'default'   => $default_taxo,
			]
		);
		$this->_rules_repeater->add_control(
			'ae_rule_post_term_types',
			[
				'label'     => __( 'Taxonomy', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $taxonomies,
				'condition' => [
					'ae_rule_type' => 'post_term',
				],
				'default'   => $default_taxo,
			]
		);

		$this->add_operator_controls();

		$this->add_value_controls();

		$this->add_multiple_value_controls();

		$element->add_control(
			'ae_dynamic_rules',
			[
				'label'       => __( 'Rules', 'ae-pro' ),
				'type'        => Controls_Manager::REPEATER,
				'default'     => [
					[
						'ae_rule_type'                => 'validation',
						'ae_rule_validation_operator' => 'equal',
						'ae_rule_validation_value'    => 'logged_in',
					],
				],
				'condition'   => [
					'enable_dynamic_rules' => 'yes',
				],
				'fields'      => $this->_rules_repeater->get_controls(),
				'title_field' => 'Rule({{{ ae_rule_type }}})',
			]
		);

		$element->add_control(
			'ae_render_html',
			[
				'label'        => __( 'Render HTML', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'description'  => __( "On 'Yes', Renders Html but hide using CSS", 'ae-pro' ),
				'condition'    => [
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$element->add_control(
			'ae_rule_fallback',
			[
				'label'       => __( 'Fallback', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					''              => __( 'Select', 'ae-pro' ),
					'text'          => __( 'Text', 'ae-pro' ),
					'ae_template'   => __( 'AE Template', 'ae-pro' ),
					'saved_section' => __( 'Saved Section', 'ae-pro' ),
				],
				'description' => __( 'If the element is hidden, you can choose to display alternate content to the user', 'ae-pro' ),
				'condition'   => [
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$element->add_control(
			'ae_rule_fallback_text',
			[
				'label'     => __( 'Fallback Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'condition' => [
					'ae_rule_fallback'     => 'text',
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$element->add_control(
			'ae_rule_fallback_ae_template',
			[
				'label'     => __( 'AE Template', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT2,
				'options'   => $this->get_ae_templates(),
				'condition' => [
					'ae_rule_fallback'     => 'ae_template',
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$saved_sections[''] = __( 'Select Section', 'ae-pro' );
		$saved_sections     = $saved_sections + $this->select_elementor_template( 'section' );
		$element->add_control(
			'ae_rule_fallback_saved_sections',
			[
				'label'     => __( 'Select Section', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT2,
				'options'   => $saved_sections,
				'condition' => [
					'ae_rule_fallback'     => 'saved_section',
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$element->add_control(
			'ae_rule_check_filter',
			[
				'label'     => __( 'Rule Filter', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'enable_dynamic_rules' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	public function section_fields( $element, $args ) {
		$this->add_field( $element );
	}

	public function widget_fields( $element, $args ) {
		$_widget_rules = apply_filters( 'aepro/dynamic_rules/widget_rules', true );
		if ( ! $_widget_rules ) {
			return false;
		}
		$this->add_field( $element );
	}

	protected function add_actions() {

		//Activate controls for section
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'section_fields' ], 10, 2 );

		//Activate controls for column
		add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'section_fields' ], 10, 2 );

		//Activate controls for container
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'section_fields' ], 10, 2 );

		//_section_style
		//section_advanced
		//section_custom_css
		//section_custom_css_pro

		//Activate controls for widget
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'widget_fields' ], 10, 2 );

		//$this->register_rules();
		add_action('after_setup_theme', [ $this, 'register_rules' ] );

		//Don't Render Html for Widget
		add_action( 'elementor/widget/render_content', [ $this, 'should_render' ], 10, 2 );

		//Don't Render Html for section
		add_filter( 'elementor/frontend/section/should_render', [ $this, 'should_render' ], 10, 2 );

		//Don't Render Html for column
		add_filter( 'elementor/frontend/column/should_render', [ $this, 'should_render' ], 10, 2 );

		//Don't Render Html for container
		add_filter( 'elementor/frontend/container/should_render', [ $this, 'should_render' ], 10, 2 );

		//Activate rules for section
		add_action( 'elementor/frontend/section/before_render', [ $this, 'set_rules_on_elements' ], 10, 1 );

		//Activate rules for column
		add_action( 'elementor/frontend/column/before_render', [ $this, 'set_rules_on_elements' ], 10, 1 );

		//Activate rules for widget
		add_action( 'elementor/frontend/widget/before_render', [ $this, 'set_rules_on_elements' ], 10, 1 );

		//Activate rules for conatiner
		add_action( 'elementor/frontend/container/before_render', [ $this, 'set_rules_on_elements' ], 10, 1 );
	}

	private function add_name_controls() {
		if ( ! $this->_rules ) {
			return;
		}

		foreach ( $this->_rules as $_rule ) {

			if ( false === $_rule->get_name_control() ) {
				continue;
			}

			$rule_name   = $_rule->get_name();
			$control_key = 'ae_rule_' . $rule_name . '_name';

			$control_settings = $_rule->get_name_control();

			// Show this only if the user select this specific rule
			$control_settings['condition'] = [
				'ae_rule_type' => $rule_name,
			];

			$this->_rules_repeater->add_control( $control_key, $control_settings );
		}
	}

	private function add_operator_controls() {
		if ( ! $this->_rules ) {
			return;
		}

		foreach ( $this->_rules as $_rule ) {

			$rule_name        = $_rule->get_name();
			$control_key      = 'ae_rule_' . $rule_name . '_operator';
			$control_settings = $_rule->add_operator_control();

			$control_settings['condition'] = [
				'ae_rule_type' => $rule_name,
			];

			$this->_rules_repeater->add_control( $control_key, $control_settings );
		}
	}

	private function add_value_controls() {
		if ( ! $this->_rules ) {
			return;
		}

		foreach ( $this->_rules as $_rule ) {

			if ( false === $_rule->get_value_control() ) {
				continue;
			}

			$rule_name        = $_rule->get_name();
			$control_key      = 'ae_rule_' . $rule_name . '_value';
			$control_settings = $_rule->get_value_control();

			// Show this only if the user select this specific condition
			$control_settings['condition'] = [
				'ae_rule_type'                         => $rule_name,
				'ae_rule_' . $rule_name . '_operator!' => [ 'empty', 'not_empty' ],
			];

			$this->_rules_repeater->add_control( $control_key, $control_settings );
		}
	}

	private function add_multiple_name_controls() {
		if ( ! $this->_rules ) {
			return;
		}
		foreach ( $this->_rules as $_rule ) {

			if ( false === $_rule->get_multiple_name_control() ) {
				continue;
			}

			$rule_name              = $_rule->get_name();
			$multiple_name_controls = $_rule->get_multiple_name_control();

			foreach ( $multiple_name_controls as $control ) {
				$object_type = $control['object_type'];
				if ( $object_type ) {
					$object_type = '_' . $object_type;
				}

				$control_key = 'ae_rule_' . $rule_name . $object_type . '_name';

				$control_settings = $control;
				if ( isset( $control['condition'] ) ) {
					$control_settings['condition'] = $control['condition'] + [
						'ae_rule_type' => $rule_name,
					];
				} else {
					$control_settings['condition'] = [
						'ae_rule_type' => $rule_name,
					];
				}

				$this->_rules_repeater->add_control( $control_key, $control_settings );

			}
		}
	}

	private function add_multiple_value_controls() {
		if ( ! $this->_rules ) {
			return;
		}

		foreach ( $this->_rules as $_rule ) {

			if ( false === $_rule->get_multiple_value_control() ) {
				continue;
			}

			$rule_name               = $_rule->get_name();
			$multiple_value_controls = $_rule->get_multiple_value_control();

			foreach ( $multiple_value_controls as $control ) {
				$object_type                   = $control['object_type'];
				$condition_name                = $control['condition_name'];
				$control_key                   = 'ae_rule_' . $rule_name . '_' . $object_type . '_value';
				$control_settings              = $control;
				$control_settings['condition'] = [
					$condition_name                        => $object_type,
					'ae_rule_type'                         => $rule_name,
					'ae_rule_' . $rule_name . '_operator!' => [ 'empty', 'not_empty' ],
				];

				$this->_rules_repeater->add_control( $control_key, $control_settings );
			}
		}
	}

	public function get_rules( $rule_name = null ) {
		if ( $rule_name ) {
			if ( isset( $this->_rules[ $rule_name ] ) ) {
				return $this->_rules[ $rule_name ];
			}
			return null;
		}

		return $this->_rules;
	}

	protected function set_rules( $id, $rules = [], $settings = [] ) {
		if ( ! $rules ) {
			return;
		}

		foreach ( $rules as $index => $rule ) {

			$key = $rule['ae_rule_type'];
			$wpml_translated_ids = [];
			if ( ! isset( $rule[ 'ae_rule_' . $key . '_operator' ] ) ) {
				continue;
			}
			$operator = $rule[ 'ae_rule_' . $key . '_operator' ];
			switch ( $key ) {
				case 'post':
					$post_type = $rule['ae_rule_post_types'];
					$value     = $rule[ 'ae_rule_' . $key . '_' . $post_type . '_value' ];
					if ( is_array( $value ) && ! empty( $value ) ) {
						foreach ($value as $object_id) {
							$wpml_translated_ids[] = apply_filters('wpml_object_id', $object_id, $post_type);
						}
					}
					$value = $wpml_translated_ids;
					break;
				case 'acf_date_time':
					$datepicker_type = $rule['ae_rule_datepicker_type'];
					if ( empty( $datepicker_type ) ) {
						$datepicker_type = 'date';
					}
					$value = $rule[ 'ae_rule_' . $key . '_' . $datepicker_type . '_value' ];
					break;
				case 'acf_taxonomy':
					$acf_taxonomy_types = $rule['ae_rule_acf_taxonomy_types'];
					$value              = $rule[ 'ae_rule_' . $key . '_' . $acf_taxonomy_types . '_value' ];

					break;
				case 'term_archive':
					$term_archive_types = $rule['ae_rule_term_archive_types'];
					$value              = $rule[ 'ae_rule_' . $key . '_' . $term_archive_types . '_value' ];
					break;
				case 'post_term':
					$post_term_types = $rule['ae_rule_post_term_types'];
					$value           = $rule[ 'ae_rule_' . $key . '_' . $post_term_types . '_value' ];
					if( is_array($value) && !empty($value) ){
						foreach ($value as $object_id) {
							$wpml_translated_ids[] = apply_filters('wpml_object_id', $object_id, $post_term_types);
						}
					}
					$value = $wpml_translated_ids;

					break;
				default:
					$value = $rule[ 'ae_rule_' . $key . '_value' ];
			}

			$name = null;

			$_rule = $this->get_rules( $key );

			if ( ! $_rule ) {
				continue;
			}

			if ( array_key_exists( 'ae_rule_' . $key . '_name', $rule ) ) {
				$name = $rule[ 'ae_rule_' . $key . '_name' ];
			}

			//For acf sub fields.
			$acf_keys    = [ 'acf_text', 'acf_choice', 'acf_date_time', 'acf_true_false', 'acf_post', 'acf_relationship', 'acf_taxonomy', 'acf_image' ];
			$parent_type = '';
			//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( in_array( $key, $acf_keys ) ) {
				$parent_type = $rule[ 'ae_rule_' . $key . '_parent_type_name' ];
				switch ( $parent_type ) {
					case 'repeater':
						$parent_field = $rule[ 'ae_rule_' . $key . '_acf_repeater_field_name' ];
						$sub_field    = $rule[ 'ae_rule_' . $key . '_acf_repeater_sub_field_name' ];
						$name         = [
							'parent_field' => $parent_field,
							'sub_field'    => $sub_field,
							'parent_type'  => $parent_type,
						];
						break;
					case 'group':
						$parent_field = $rule[ 'ae_rule_' . $key . '_acf_group_field_name' ];
						$sub_field    = $rule[ 'ae_rule_' . $key . '_acf_group_sub_field_name' ];
						$name         = [
							'parent_field' => $parent_field,
							'sub_field'    => $sub_field,
							'parent_type'  => $parent_type,
						];
						break;
					default:
						if ( array_key_exists( 'ae_rule_' . $key . '_name', $rule ) ) {
							$name = $rule[ 'ae_rule_' . $key . '_name' ];
						}
				}
			}

			$check = $_rule->check( $operator, $value, $name );

			$check = apply_filters( "aepro/dynamic_rules/check_rule/{$settings['ae_rule_check_filter']}", $check );

			$this->rules[ $id ][ $key . '_' . $rule['_id'] ] = $check;
		}
	}

	public function set_rules_on_elements( $element ) {

		if ( $element->get_type() === 'widget' ) {
			$_widget_rules = apply_filters( 'aepro/dynamic_rules/widget_rules', true );
			if ( ! $_widget_rules ) {
				return false;
			}
		}

		$settings = $element->get_settings_for_display();

		if ( 'yes' === $settings['enable_dynamic_rules'] ) {
			// Set the rules
			$this->set_rules( $element->get_id(), $settings['ae_dynamic_rules'], $settings );

			if ( ! $this->is_visible( $element->get_id(), $settings['rule_relation'], $settings['ae_show_hide_on_rules'] ) ) {
				//hidden with CSS
				$element->add_render_attribute( '_wrapper', 'class', 'ae-visibility-hidden' );

				if ( $settings['ae_rule_fallback'] !== '' ) {
					echo $this->get_fallback_content( $settings );
				}
			}
		}
	}

	public function should_render( $should_render, $section ) {

		$settings = $section->get_settings_for_display();

		if ( 'yes' === $settings['enable_dynamic_rules'] ) {
			// Set the rules
			$this->set_rules( $section->get_id(), $settings['ae_dynamic_rules'], $settings );
			if ( ! $this->is_visible( $section->get_id(), $settings['rule_relation'], $settings['ae_show_hide_on_rules'] ) ) {
				if ( $settings['ae_render_html'] !== 'yes' ) {
					$should_render = false; // Html doesn't render here
				}
			}
		}

		return $should_render;
	}

	public function dont_render_html( $widget_content, $element ) {

		$_widget_rules = apply_filters( 'aepro/dynamic_rules/widget_rules', true );
		if ( ! $_widget_rules ) {
			return $widget_content;
		}

		$settings = $element->get_settings_for_display();

		if ( 'yes' === $settings['enable_dynamic_rules'] ) {

			// Set the rules
			$this->set_rules( $element->get_id(), $settings['ae_dynamic_rules'], $settings );

			if ( ! $this->is_visible( $element->get_id(), $settings['rule_relation'], $settings['ae_show_hide_on_rules'] ) ) {
				if ( $settings['ae_render_html'] !== 'yes' ) {
					return; // Html doesn't render here
				}
			}
		}

		return $widget_content;
	}

	protected function is_visible( $id, $relation, $show_hide ) {

		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			if ( ! isset( $this->rules[ $id ] ) ) {
				return true;
			}
			if ( 'any' === $relation ) {
				//Conditon is not equal to true.
				if ( $show_hide === 'yes' ) {
					if ( ! in_array( true, $this->rules[ $id ], true ) ) {
						return false;
					}
				} else {
					if ( in_array( true, $this->rules[ $id ], true ) ) {
						return false;
					}
				}
			} else {
				//Conditon is equal to true.
				if ( $show_hide === 'yes' ) {
					if ( in_array( false, $this->rules[ $id ], true ) ) {
						return false;
					}
				} else {
					if ( ! in_array( false, $this->rules[ $id ], true ) ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	protected function get_fallback_content( $settings ) {
		$ae_rule_fallback = $settings['ae_rule_fallback'];
		$fallback         = '';
		switch ( $ae_rule_fallback ) {
			case 'text':
				$fallback = do_shortcode( $settings['ae_rule_fallback_text'] );
				break;
			case 'ae_template':
				$fallback = Frontend::instance()->render_insert_elementor( $settings['ae_rule_fallback_ae_template'], true );
				break;
			case 'saved_section':
				$fallback = Frontend::instance()->run_elementor_builder( $settings['ae_rule_fallback_saved_sections'], true );
				break;
			default:
				$fallback = '';
		}
		return $fallback;
	}
}
