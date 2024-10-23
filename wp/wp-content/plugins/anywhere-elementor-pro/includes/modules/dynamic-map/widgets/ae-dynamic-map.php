<?php
namespace Aepro\Modules\DynamicMap\Widgets;

use Aepro\Aepro;
use Elementor\Plugin;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Aepro\Post_Helper;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Group_Control_Background;
use Aepro\Modules\DynamicMap\Classes\Query;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Text_Shadow;
use Aepro\Frontend;
use Aepro\Classes\CacheManager;

class AeDynamicMap extends Widget_Base {

	protected $_access_level = 1;

	private $map_options = [];

	public function get_name() {
		return 'ae-dynamic-map';
	}

	public function get_title() {
		return __( 'AE - Dynamic Map', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-google-maps';
	}

	public function get_script_depends() {
		return [ 'ae-gmap' ];
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords() {
		return [
			'google',
			'open map',
			'map',
			'embed',
			'location',
			'marker',
		];
	}

    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {

		$this->get_layout_section();
		$this->get_query_section();
		$this->get_map_options_section();
		$this->get_marker_settings_section();
		$this->get_marker_listing_section();
		$this->get_carousel_section();
		$this->get_map_styles_section();
		$this->get_widget_title_controls();
		$this->get_map_style_section();
		$this->get_marker_listing_style_section();
		$this->get_responsive_btn_style_section();
		$this->get_carousel_style_section();
		$this->no_post_msg_style_section();
		$this->get_widget_title_style_controls();
	}

	public function get_layout_section() {

		$this->start_controls_section(
			'section_data_source',
			[
				'label' => __( 'Data Source', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'map_type',
			[
				'label'   => __( 'Map Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'google_map' => __( 'Google Map', 'ae-pro' ),
				],
				'default' => 'google_map',
			]
		);

		$this->add_control(
			'map_source',
			[
				'label'   => __( 'Map Source', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'current_post'  => __( 'Current Post', 'ae-pro' ),
					'post_query'    => __( 'Post Query', 'ae-pro' ),
					'post_repeater' => __( 'Repeater', 'ae-pro' ),
				],
				'default' => 'current_post',
			]
		);

		if ( \Aepro\Plugin::show_acf() || \Aepro\Plugin::show_acf( true ) ) {
			$map_source['acf_map_field'] = __( 'ACF Map Field', 'ae-pro' );
		}
		$map_source['custom_field'] = __( 'Custom Field', 'ae-pro' );

		$this->add_control(
			'field_type',
			[
				'label'     => __( 'Field', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $map_source,
				'default'   => 'acf_map_field',
				'condition' => [
					'map_source' => [ 'current_post', 'post_query' ],
				],
			]
		);

		$repeater_fields = Aepro::$_helper->get_acf_repeater_field();
		$this->add_control(
			'acf_repeater_field_name',
			[
				'label'       => __( 'Repeater Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => $repeater_fields,
				'placeholder' => __( 'Repeater Field', 'ae-pro' ),
				'default'     => '',
				'condition'   => [
					'map_source' => 'post_repeater',
				],
			]
		);

		$this->add_control(
			'repeater_sub_field',
			[
				'label'        => __( 'Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'acf_repeater_field_name',
				'query_type'   => 'repeater-sub-fields',
				'placeholder'  => 'Select',
				'condition'    => [
					'field_type' => 'acf_map_field',
					'map_source' => 'post_repeater',
				],
			]
		);

		$map_field = Aepro::$_helper->get_acf_field_groups( [ 'google_map' ] );
		$this->add_control(
			'acf_map_field',
			[
				'label'     => __( 'Field Key', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'groups'    => $map_field,
				'condition' => [
					'field_type' => 'acf_map_field',
					'map_source' => [ 'current_post', 'post_query' ],
				],
			]
		);

		$this->add_control(
			'custom_field_lat',
			[
				'label'       => __( 'Latitude', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your custom field key', 'ae-pro' ),
				'condition'   => [
					'field_type' => 'custom_field',
				],
			]
		);

		$this->add_control(
			'custom_field_lng',
			[
				'label'       => __( 'Longitude', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your custom field key', 'ae-pro' ),
				'condition'   => [
					'field_type' => 'custom_field',
				],
			]
		);

		$this->add_control(
			'custom_field_address',
			[
				'label'       => __( 'Address', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter your custom field key', 'ae-pro' ),
				'condition'   => [
					'field_type' => 'custom_field',
				],
			]
		);

		$this->add_control(
			'no_posts_message',
			[
				'label'     => __( 'No Data Found Message', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	public function get_query_section() {

		$this->start_controls_section(
			'section_post_query',
			[
				'label'     => __( 'Post Query', 'ae-pro' ),
				'condition' => [
					'map_source' => 'post_query',
				],
			]
		);

		$source                                = Aepro::$_helper->get_rule_post_types();
		$ae_source_options                     = $source;
		$ae_source_options['current_loop']     = __( 'Current Archive', 'ae-pro' );
		$ae_source_options['manual_selection'] = __( 'Manual Selection', 'ae-pro' );
		$ae_source_options['related']          = __( 'Related Posts', 'ae-pro' );

		if ( \Aepro\Plugin::show_acf() || is_plugin_active( 'pods/init.php' ) ) {
			$ae_source_options['relation']    = __( 'Relationship', 'ae-pro' );
			$ae_source_options['post_object'] = __( 'Post (ACF)', 'ae-pro' );
		}

		$this->add_control(
			'source',
			[
				'label'   => __( 'Source', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $ae_source_options,
				'default' => key( $source ),
			]
		);

		$this->add_control(
			'select_post_ids',
			[
				'label'       => __( 'Posts', 'ae-pro' ),
				'type'        => 'aep-query',
				'label_block' => true,
				'query_type'  => 'post',
				'multiple'    => true,
				'condition'   => [
					'source' => 'manual_selection',
				],
			]
		);

		$this->add_control(
			'related_by',
			[
				'label'       => __( 'Related By', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'placeholder' => __( 'Select Taxonomies', 'ae-pro' ),
				'default'     => '',
				'options'     => Aepro::$_helper->get_rules_taxonomies(),
				'condition'   => [
					'source' => 'related',
				],
			]
		);
		$this->add_control(
			'related_match_with',
			[
				'label'     => __( 'Match With', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'OR',
				'options'   => [
					'OR'  => __( 'Anyone Term', 'ae-pro' ),
					'AND' => __( 'All Terms', 'ae-pro' ),
				],
				'condition' => [
					'source' => 'related',
				],
			]
		);

		if ( \Aepro\Plugin::show_acf() && is_plugin_active( 'pods/init.php' ) ) {
			$this->add_control(
				'relationship_type',
				[
					'label'     => __( 'Relationship Type', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'acf',
					'options'   => [
						'acf'  => __( 'ACF', 'ae-pro' ),
						'pods' => __( 'Pods', 'ae-pro' ),
					],
					'condition' => [
						'source' => 'relation',
					],
				]
			);
		}

		if ( \Aepro\Plugin::show_acf() || is_plugin_active( 'pods/init.php' ) ) {
			$this->add_control(
				'acf_relation_field',
				[
					'label'       => __( 'Relationship Field', 'ae-pro' ),
					'tyoe'        => Controls_Manager::TEXT,
					'description' => __( 'Key of ACF / Pods Relationship Field', 'ae-pro' ),
					'condition'   => [
						'source' => 'relation',
					],
				]
			);

			$this->add_control(
				'reverse_relation',
				[
					'label'        => __( 'Reverse Relation', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
					'condition'    => [
						'source' => 'relation',
					],
				]
			);
		}

		if ( \Aepro\Plugin::show_acf() || is_plugin_active( 'pods/init.php' ) ) {
			$this->add_control(
				'acf_post_field',
				[
					'label'       => __( 'Post Field', 'ae-pro' ),
					'tyoe'        => Controls_Manager::TEXT,
					'description' => __( 'Key of ACF Post Field', 'ae-pro' ),
					'condition'   => [
						'source' => 'post_object',
					],
				]
			);
		}

		$this->add_control(
			'taxonomy_divider',
			[
				'type'      => Controls_Manager::DIVIDER,
				'style'     => 'thick',
				'condition' => [
					'source!' => [ 'manual_selection', 'current_loop', 'related', 'relation', 'post_object' ],
				],
			]
		);

		$this->add_control(
			'taxonomy_heading',
			[
				'label'     => __( 'Taxonomy Query', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'source!' => [ 'current_loop', 'related', 'relation', 'post_object', 'manual_selection' ],
				],
			]
		);

		$ae_taxonomies = Post_Helper::instance()->get_all_taxonomies();

		$post_types = Aepro::$_helper->get_rule_post_types();

		foreach ( $post_types as $key => $post_type ) {
			$this->add_control(
				$key . '_tax_ids',
				[
					'label'       => 'Taxonomies',
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'placeholder' => __( 'Enter Taxnomies ID Separated by Comma', 'ae-pro' ),
					'options'     => Post_Helper::instance()->get_taxonomies_by_post_type( $key ),
					'condition'   => [
						'source' => $key,
					],
				]
			);

			$this->add_control(
				$key . '_tax_relation',
				[
					'label'     => __( 'Relation', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'OR',
					'options'   => [
						'OR'  => __( 'Anyone Term', 'ae-pro' ),
						'AND' => __( 'All Terms', 'ae-pro' ),
					],
					'condition' => [
						'source' => $key,
					],
				]
			);
		}

		$this->start_controls_tabs( 'tabs_include_exclude' );

		$this->start_controls_tab(
			'tab_query_include',
			[
				'label'     => __( 'Include', 'ae-pro' ),
				'condition' => [
					'source!' => [ 'manual_selection', 'current_loop', 'related', 'relation', 'post_object' ],
				],
			]
		);

		foreach ( $ae_taxonomies as $ae_taxonomy => $object ) {
			foreach ( $object->object_type as $object_type ) {
				$this->add_control(
					$ae_taxonomy . '_' . $object_type . '_include_term_ids',
					[
						'label'       => $object->label,
						'type'        => Controls_Manager::SELECT2,
						'multiple'    => true,
						'label_block' => true,
						//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
						'placeholder' => __( 'Enter ' . $object->label . ' ID Separated by Comma', 'ae-pro' ),
						'object_type' => $ae_taxonomy,
						'options'     => Post_Helper::instance()->get_taxonomy_terms( $ae_taxonomy ),
						'condition'   => [
							'source'                  => $object_type,
							$object_type . '_tax_ids' => $ae_taxonomy,
						],
					]
				);

				$this->add_control(
					$ae_taxonomy . '_' . $object_type . '_term_operator',
					[
						'label'     => __( 'Operator', 'ae-pro' ),
						'type'      => Controls_Manager::SELECT,
						'default'   => 'IN',
						'options'   => [
							'IN'         => __( 'IN', 'ae-pro' ),
							'NOT IN'     => __( 'NOT IN', 'ae-pro' ),
							'AND'        => __( 'AND', 'ae-pro' ),
							'EXISTS'     => __( 'EXISTS', 'ae-pro' ),
							'NOT EXISTS' => __( 'NOT EXISTS', 'ae-pro' ),
						],
						'condition' => [
							'source'                  => $object_type,
							$object_type . '_tax_ids' => $ae_taxonomy,
						],
					]
				);
			}
		}

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_query_exclude',
			[
				'label'     => __( 'Exclude', 'ae-pro' ),
				'condition' => [
					'source!' => [ 'manual_selection', 'current_loop', 'related', 'relation', 'post_object' ],
				],
			]
		);

		foreach ( $ae_taxonomies as $ae_taxonomy => $object ) {
			foreach ( $object->object_type as $object_type ) {
				$this->add_control(
					$ae_taxonomy . '_' . $object_type . '_exclude_term_ids',
					[
						'label'       => $object->label,
						'type'        => Controls_Manager::SELECT2,
						'multiple'    => true,
						'label_block' => true,
						//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
						'placeholder' => __( 'Enter ' . $object->label . ' ID Separated by Comma', 'ae-pro' ),
						'object_type' => $ae_taxonomy,
						'options'     => Post_Helper::instance()->get_taxonomy_terms( $ae_taxonomy ),
						'condition'   => [
							'source'                  => $object_type,
							$object_type . '_tax_ids' => $ae_taxonomy,
						],
					]
				);
			}
		}

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'author_divider',
			[
				'type'      => Controls_Manager::DIVIDER,
				'style'     => 'thick',
				'condition' => [
					'source!' => [ 'current_loop', 'manual_selection' ],
				],
			]
		);

		$this->add_control(
			'author_query_heading',
			[
				'label'     => __( 'Author', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'source!' => [ 'current_loop', 'manual_selection' ],
				],
			]
		);

		$this->start_controls_tabs( 'author_query_tabs' );

		$this->start_controls_tab(
			'tab_author_include',
			[
				'label'     => __( 'Include', 'ae-pro' ),
				'condition' => [
					'source!' => [ 'manual_selection', 'current_loop' ],
				],
			]
		);

		$this->add_control(
			'include_author_ids',
			[
				'label'       => 'Authors',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'show_label'  => false,
				'placeholder' => __( 'Enter Author ID Separated by Comma', 'ae-pro' ),
				'options'     => Post_Helper::instance()->get_authors(),
				'condition'   => [
					'source!' => [ 'manual_selection', 'current_loop' ],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_author_exclude',
			[
				'label'     => __( 'Exclude', 'ae-pro' ),
				'condition' => [
					'source!' => [ 'manual_selection', 'current_loop' ],
				],
			]
		);

		$this->add_control(
			'exclude_author_ids',
			[
				'label'       => 'Authors',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'show_label'  => false,
				'placeholder' => __( 'Enter Author ID Separated by Comma', 'ae-pro' ),
				'options'     => Post_Helper::instance()->get_authors(),
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'date_divider',
			[
				'type'      => Controls_Manager::DIVIDER,
				'style'     => 'thick',
				'condition' => [
					'source!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'date_query_heading',
			[
				'label'     => __( 'Date Query', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'source!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'select_date',
			[
				'label'     => __( 'Date', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'post_type' => '',
				'options'   => [
					'anytime' => __( 'All', 'ae-pro' ),
					'today'   => __( 'Past Day', 'ae-pro' ),
					'week'    => __( 'Past Week', 'ae-pro' ),
					'month'   => __( 'Past Month', 'ae-pro' ),
					'quarter' => __( 'Past Quarter', 'ae-pro' ),
					'year'    => __( 'Past Year', 'ae-pro' ),
					'exact'   => __( 'Custom', 'ae-pro' ),
				],
				'default'   => 'anytime',
				'multiple'  => false,
				'condition' => [
					'source!' => [
						'manual_selection',
						'current_loop',
					],
				],
			]
		);

		$this->add_control(
			'post_status',
			[
				'label'       => 'Post Status',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => [
					'publish' => __( 'Publish', 'ae-pro' ),
					'future'  => __( 'Schedule', 'ae-pro' ),
				],
				'condition'   => [
					'select_date' => 'exact',
					'source!'     => [
						'manual_selection',
						'current_loop',
					],
				],
			]
		);

		$this->add_control(
			'date_before',
			[
				'label'       => __( 'Before', 'ae-pro' ),
				'type'        => Controls_Manager::DATE_TIME,
				'post_type'   => '',
				'label_block' => false,
				'multiple'    => false,
				'placeholder' => __( 'Choose', 'ae-pro' ),
				'condition'   => [
					'select_date' => 'exact',
					'source!'     => [
						'manual_selection',
						'current_loop',
					],
				],
				'description' => __( 'Setting a ‘Before’ date will show all the posts published until the chosen date (inclusive).', 'ae-pro' ),
			]
		);

		$this->add_control(
			'date_after',
			[
				'label'       => __( 'After', 'ae-pro' ),
				'type'        => Controls_Manager::DATE_TIME,
				'post_type'   => '',
				'label_block' => false,
				'multiple'    => false,
				'placeholder' => __( 'Choose', 'ae-pro' ),
				'condition'   => [
					'select_date' => 'exact',
					'source!'     => [
						'manual_selection',
						'current_loop',
					],
				],
				'description' => __( 'Setting an ‘After’ date will show all the posts published since the chosen date (inclusive).', 'ae-pro' ),
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'           => __( 'Order By', 'ae-pro' ),
				'type'            => Controls_Manager::SELECT,
				'content_classes' => 'ae_conditional_fields',
				'default'         => 'post_date',
				'options'         => [
					'post_date'      => __( 'Date', 'ae-pro' ),
					'post_title'     => __( 'Title', 'ae-pro' ),
					'menu_order'     => __( 'Menu Order', 'ae-pro' ),
					'rand'           => __( 'Random', 'ae-pro' ),
					'post__in'       => __( 'Manual', 'ae-pro' ),
					'meta_value'     => __( 'Custom Field', 'ae-pro' ),
					'meta_value_num' => __( 'Custom Field (Numeric)', 'ae-pro' ),
				],
				'condition'       => [
					'source!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'orderby_alert',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae_order_by_alert',
				'raw'             => __( "<div class='elementor-control-field-description'>Note: Order By 'Manual' is only applicable when Source is 'Manual Selection' and 'Relationship' </div>", 'ae-pro' ),
				'separator'       => 'none',
				'condition'       => [
					'orderby' => 'post__in',
				],
			]
		);

		$this->add_control(
			'orderby_metakey',
			[
				'label'       => __( 'Meta Key Name', 'ae-pro' ),
				'tyoe'        => Controls_Manager::TEXT,
				'description' => __( 'Custom Field Key', 'ae-pro' ),
				'condition'   => [
					'source!' => 'current_loop',
					'orderby' => [ 'meta_value', 'meta_value_num' ],
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'     => __( 'Order', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'desc',
				'options'   => [
					'asc'  => __( 'ASC', 'ae-pro' ),
					'desc' => __( 'DESC', 'ae-pro' ),
				],
				'condition' => [
					'source!'  => 'current_loop',
					'orderby!' => 'post__in',
				],
			]
		);

		$this->add_control(
			'current_post',
			[
				'label'        => __( 'Exclude Current Post', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'source!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'offset',
			[
				'label'       => __( 'Offset', 'ae-pro' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'condition'   => [
					'source!' => [ 'current_loop', 'manual_selection' ],
				],
				'description' => __( 'Use this setting to skip over posts (e.g. \'2\' to skip over 2 posts).', 'ae-pro' ),
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'     => __( 'Posts Count', 'ae-pro' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6,
				'condition' => [
					'source!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'query_filter',
			[
				'label'       => __( 'Query Filter', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'description' => __( Aepro::$_helper->get_widget_admin_note_html( '<span style="color:red">Danger Ahead!!</span> It is a developer oriented feature. Only use if you know how exaclty WordPress queries and filters works.', 'https://wpvibes.link/go/feature-post-blocks-query-filter', 'Read Instructions' ), 'ae-pro' ),
			]
		);

		$this->end_controls_section();
	}

	public function get_map_options_section() {
		$this->start_controls_section(
			'map_options_section',
			[
				'label' => __( 'Map Options', 'ae-pro' ),
			]
		);

		$this->add_responsive_control(
			'map_height',
			[
				'label'      => __( 'Height', 'ae-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1440,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'unit' => 'px',
					'size' => 400,
				],
				'selectors'  => [
					'{{WRAPPER}} .ae-dynamic-map-wrapper .ae-map-render' => 'height:{{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'backgroundColor',
			[
				'label'   => 'Background Color',
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			]
		);

		$this->add_control(
			'mapTypeId',
			[
				'label'   => __( 'Map Type ID', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'hybrid'    => __( 'HYBRID', 'ae-pro' ),
					'roadmap'   => __( 'ROADMAP', 'ae-pro' ),
					'satellite' => __( 'SATELLITE', 'ae-pro' ),
					'terrain'   => __( 'TERRAIN', 'ae-pro' ),
				],
				'default' => 'roadmap',
			]
		);

		$this->add_control(
			'zoom',
			[
				'label'   => __( 'Zoom', 'ae-pro' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
			]
		);

		$this->add_control(
			'auto_center',
			[
				'label'        => __( 'Auto Center', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => 'label_on',
				'return_value' => 'yes',
				'description'  => __( 'Generally, the map with multiple markers is center aligned. If you wish make your first marker as a center point. then disable it', 'ae-pro' ),
			]
		);

		$this->add_control(
			'disableDefaultUI',
			[
				'label'        => __( 'Disable Default UI', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => 'label_on',
				'return_value' => 'true',
			]
		);

		$this->add_control(
			'clickableIcons',
			[
				'label'        => __( 'Clickable Icons', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => [
					'disableDefaultUI!' => 'true',
				],
			]
		);

		$this->add_control(
			'streetViewControl',
			[
				'label'        => __( 'Street View', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => 'true',
				'return_value' => 'true',
				'condition'    => [
					'disableDefaultUI!' => 'true',
				],
			]
		);

		$this->add_control(
			'mapTypeControl',
			[
				'label'        => __( 'Map Type', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => 'true',
				'return_value' => 'true',
				'condition'    => [
					'disableDefaultUI!' => 'true',
				],
			]
		);

		$this->add_control(
			'zoomControl',
			[
				'label'        => __( 'Zoom', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => [
					'disableDefaultUI!' => 'true',
				],
			]
		);

		$this->add_control(
			'fullscreenControl',
			[
				'label'        => __( 'Full Screen', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'default'      => 'true',
				'return_value' => 'true',
				'condition'    => [
					'disableDefaultUI!' => 'true',
				],
			]
		);

		$this->add_control(
			'gestureHandling',
			[
				'label'     => __( 'Gesture Handling', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'cooperative' => __( 'Cooperative', 'ae-pro' ),
					'greedy'      => __( 'Greedy', 'ae-pro' ),
					'auto'        => __( 'Auto', 'ae-pro' ),
					'none'        => __( 'None', 'ae-pro' ),
				],
				'default'   => 'cooperative',
				'condition' => [
					'disableDefaultUI!' => 'true',
				],
			]
		);

		$this->add_control(
			'disableDoubleClickZoom',
			[
				'label'        => __( 'Disable Double Click Zoom', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'true',
				'condition'    => [
					'disableDefaultUI!' => 'true',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_marker_settings_section() {

		$this->start_controls_section(
			'marker_settings_section',
			[
				'label' => __( 'Map Marker', 'ae-pro' ),
			]
		);

		$this->add_control(
			'marker_click_action',
			[
				'label'     => __( 'Click Action', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'info_window',
				'options'   => [
					'none'        => __( 'None', 'ae-pro' ),
					'info_window' => __( 'Info Window', 'ae-pro' ),
				],
				'condition' => [
					'map_source' => [ 'current_post', 'post_repeater' ],
				],
			]
		);

		$this->add_control(
			'marker_click_action_post_query',
			[
				'label'     => __( 'Click Action', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'info_window',
				'options'   => [
					'none'        => __( 'None', 'ae-pro' ),
					'info_window' => __( 'Info Window', 'ae-pro' ),
					'post_link'   => __( 'Link to Post', 'ae-pro' ),
				],
				'condition' => [
					'map_source' => 'post_query',
				],
			]
		);

		$this->add_control(
			'marker_link_open_in_new_window',
			[
				'label'        => __( 'Open in Window', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'marker_click_action_post_query' => 'post_link',
				],
			]
		);

		$this->add_control(
			'info_window_type',
			[
				'label'     => __( 'Info Window Type', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'default'       => __( 'Default', 'ae-pro' ),
					'custom_layout' => __( 'Block Layout', 'ae-pro' ),
				],
				'default'   => 'default',
				'condition' => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->get_block_layouts(
			'info_window_block_layout',
			[
				'map_source'                     => [ 'post_query', 'current_post' ],
				'info_window_type'               => 'custom_layout',
				'marker_click_action'            => 'info_window',
				'marker_click_action_post_query' => 'info_window',
			]
		);

		$this->get_repeater_blocks(
			'info_window_repeater_block_layout',
			[
				'map_source'                     => 'post_repeater',
				'info_window_type'               => 'custom_layout',
				'marker_click_action'            => 'info_window',
				'marker_click_action_post_query' => 'info_window',
			]
		);

		$this->add_control(
			'marker_type',
			[
				'label'        => esc_html__( 'Type', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'default' => [
						'title' => esc_html__( 'Default', 'ae-pro' ),
						'icon'  => 'eicon-ban',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'ae-pro' ),
						'icon'  => 'eicon-image',
					],
					'icon' => [
						'title' => esc_html__( 'Icon', 'ae-pro' ),
						'icon'  => 'eicon-star',
					],
					'dynamic' => [
						'title' => esc_html__( 'Dynamic', 'ae-pro' ),
						'icon'  => 'eicon-custom',
					],
				],
				'default'      => 'default',
				'toggle'       => false,
				'prefix_class' => 'ae-map-marker-',
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'marker_image',
			[
				'label'     => __( 'Image', 'ae-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'marker_type' => 'image',
				],
			]
		);

		$this->add_control(
			'marker_icon',
			[
				'label'            => esc_html__( 'Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'marker_type' => 'icon',
				],
			]
		);

		$this->add_control(
			'dynamic_marker_image_source',
			[
				'label'     => esc_html__( 'Source', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'featured_image' => __( 'Featured Image', 'ae-pro' ),
					'acf_field'      => __( 'ACF Field', 'ae-pro' ),
					'custom_field'   => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'acf_field',
				'condition' => [
					'marker_type' => 'dynamic',
				],
			]
		);

		$this->add_control(
			'dynamic_cf_marker_image',
			[
				'label'       => __( 'Custom Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field Key', 'ae-pro' ),
				'condition'   => [
					'marker_type'                 => 'dynamic',
					'map_source'                  => [ 'post_query', 'current_post' ],
					'dynamic_marker_image_source' => 'custom_field',
					'field_type'                  => 'custom_field',
				],
			]
		);

		$this->add_control(
			'dynamic_marker_image',
			[
				'label'     => esc_html__( 'ACF Field Key', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'groups'    => Aepro::$_helper->get_acf_field_groups( [ 'image', 'text', 'url' ] ),
				'condition' => [
					'marker_type'                 => 'dynamic',
					'map_source'                  => [ 'post_query', 'current_post' ],
					'dynamic_marker_image_source' => 'acf_field',
				],
			]
		);

		$this->add_control(
			'dynamic_sub_field_marker_image',
			[
				'label'        => __( 'Repeater Sub Field', 'ae-pro' ),
				'type'         => 'aep-query',
				'parent_field' => 'acf_repeater_field_name',
				'query_type'   => 'repeater-sub-fields',
				'placeholder'  => 'Select',
				'condition'    => [
					'marker_type'                 => 'dynamic',
					'field_type'                  => 'acf_map_field',
					'map_source'                  => 'post_repeater',
					'dynamic_marker_image_source' => 'acf_field',
				],
			]
		);

		$this->add_control(
			'marker_size',
			[
				'label'     => __( 'Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 10,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}}.ae-map-marker-icon .ae-map-marker i' => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.ae-map-marker-icon .ae-map-marker svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.ae-map-marker-image .ae-map-marker img' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-map-marker-dynamic .ae-map-marker img' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-map-marker-default .ae-map-marker img' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-map-render .ae-map-infowindow' => 'bottom: calc({{SIZE}}{{UNIT}} + 15px + ({{marker_padding.TOP}}{{marker_padding.UNIT}} + {{marker_padding.BOTTOM}}{{marker_padding.UNIT}} + {{marker_border_width.TOP}}{{marker_border_width.UNIT}} + {{marker_border_width.BOTTOM}}{{marker_border_width.UNIT}}));',
				],
			]
		);

		$this->add_control(
			'markerAnimation',
			[
				'label'   => __( 'Animation', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'none'   => __( 'None', 'ae-pro' ),
					'drop'   => __( 'Drop', 'ae-pro' ),
					'bounce' => __( 'Bounce', 'ae-pro' ),
					'pulse'  => __( 'Pulse', 'ae-pro' ),
					'flash'  => __( 'Flash', 'ae-pro' ),
					'shake'  => __( 'Shake', 'ae-pro' ),
				],
				'default' => 'none',
			]
		);

		$this->add_control(
			'markerCluster',
			[
				'label'        => __( 'Cluster', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'marker_listing',
			[
				'label'        => __( 'Marker Listing', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'map_source' => [ 'post_query', 'post_repeater' ],
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_marker_listing_section() {

		$this->start_controls_section(
			'marker_listing_section',
			[
				'label'     => __( 'Marker Listing', 'ae-pro' ),
				'condition' => [
					'map_source'     => [ 'post_query', 'post_repeater' ],
					'marker_listing' => 'yes',
				],
			]
		);

		$this->add_control(
			'marker_layout',
			[
				'label'   => __( 'Layout', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'grid'     => __( 'Grid', 'ae-pro' ),
					'carousel' => __( 'Carousel', 'ae-pro' ),
				],
				'default' => 'grid',
			]
		);

		$this->get_block_layouts(
			'marker_listing_block_layout',
			[
				'map_source' => 'post_query',
			]
		);

		$this->get_repeater_blocks(
			'marker_listing_repeater_block_layout',
			[
				'map_source' => 'post_repeater',
			]
		);

		$this->add_control(
			'block_layout_divider',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_control(
			'marker_listing_position',
			[
				'label'        => esc_html__( 'Listing Position', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'top' => [
						'title' => esc_html__( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
					'left' => [
						'title' => esc_html__( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
				],
				'default'      => 'left',
				'toggle'       => false,
				'prefix_class' => 'ae-map-marker-listing-align-',
			]
		);

		$this->add_responsive_control(
			'marker_listing_width',
			[
				'label'     => __( 'Width', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default'   => [
					'unit' => '%',
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-map-render'  => 'width: calc(100{{UNIT}} - {{SIZE}}{{UNIT}})',
					'{{WRAPPER}} .ae-map-listing' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'marker_listing_position' => [ 'left', 'right' ],
				],
			]
		);

		$this->add_responsive_control(
			'marker_listing_height',
			[
				'label'      => __( 'Height', 'ae-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1440,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', '%', 'vh', 'vw' ],
				'default'    => [
					'unit' => 'px',
					'size' => 400,
				],
				'selectors'  => [
					'{{WRAPPER}} .ae-dynamic-map-wrapper .ae-map-listing' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'marker_listing_position' => [ 'left', 'right' ],
				],
			]
		);

		$this->get_grid_controls();

		$this->add_control(
			'grid_controls_divider',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->get_responsive_mode_controls();

		$this->add_control(
			'listing_marker_sync',
			[
				'label'        => __( 'Listing Marker Sync', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();
	}

	public function get_grid_controls() {

		$this->add_responsive_control(
			'columns',
			[
				'label'           => __( 'Columns', 'ae-pro' ),
				'type'            => Controls_Manager::NUMBER,
				'desktop_default' => '3',
				'tablet_default'  => '2',
				'mobile_default'  => '1',
				'min'             => 1,
				'max'             => 12,
				'selectors'       => [
					'{{WRAPPER}} .ae-map-marker-wrapper' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); display:grid',
				],
				'condition'       => [
					'marker_layout' => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'item_col_gap',
			[
				'label'     => __( 'Column Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-map-marker-wrapper' => 'column-gap: {{SIZE}}{{UNIT}}; grid-column-gap: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'marker_layout' => 'grid',
					'columns!'      => 1,
				],
			]
		);

		$this->add_responsive_control(
			'item_row_gap',
			[
				'label'     => __( 'Row Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-map-marker-wrapper' => 'row-gap: {{SIZE}}{{UNIT}}; grid-row-gap: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'marker_layout' => 'grid',
				],
			]
		);
	}

	public function get_responsive_mode_controls() {
		$this->add_control(
			'listing_responsive_mode',
			[
				'label'        => __( 'Responsive Mode', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$breakpoints                            = Plugin::$instance->breakpoints->get_active_devices_list();
		$responsive_mode_breakpoints['desktop'] = __( 'Desktop', 'ae-pro' );
		foreach ( $breakpoints as $breakpoint ) {
			//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			$responsive_mode_breakpoints[ $breakpoint ] = __( ucwords( str_replace( '_', ' ', $breakpoint ) ), 'ae-pro' );
		}

		$this->add_control(
			'listing_responsive_mode_option',
			[
				'label'       => __( 'Device', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $responsive_mode_breakpoints,
				'condition'   => [
					'listing_responsive_mode' => 'yes',
				],
				'default'     => 'mobile',
			]
		);

		$this->add_control(
			'show_list_text',
			[
				'label'       => __( 'Show List', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Button Text', 'ae-pro' ),
				'default'     => __( 'Show List', 'ae-pro' ),
				'condition'   => [
					'listing_responsive_mode' => 'yes',
				],
			]
		);

		$this->add_control(
			'close_list_text',
			[
				'label'       => __( 'Close List', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Button Text', 'ae-pro' ),
				'default'     => __( 'Close List', 'ae-pro' ),
				'condition'   => [
					'listing_responsive_mode' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'responsive_btn_align',
			[
				'label'        => esc_html__( 'Alignment', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'    => [
						'title' => esc_html__( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'ae-pro' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'      => 'justify',
				'prefix_class' => 'ae-responsive-btn%s-align-',
				'selectors'    => [
					'{{WRAPPER}} .ae-responsive-btn' => 'text-align: {{VALUE}}',
				],
				'condition'    => [
					'listing_responsive_mode' => 'yes',
				],
			]
		);

		$this->add_control(
			'responsive_mode_divider',
			[
				'type'      => Controls_Manager::DIVIDER,
				'style'     => 'thick',
				'condition' => [
					'listing_responsive_mode' => 'yes',
				],
			]
		);
	}

	public function get_carousel_section() {

		$this->start_controls_section(
			'section_carousel',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'marker_listing' => 'yes',
					'marker_layout'  => 'carousel',
				],
			]
		);

		$this->add_control(
			'direction',
			[
				'label'        => __( 'Direction', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'toggle'       => false,
				'classes'      => 'ae-widget-dynamic-map',
				'options'      => [
					'horizontal' => [
						'title' => esc_html__( 'Horizontal', 'ae-pro' ),
						'icon'  => 'eicon-slides',
					],
					'vertical' => [
						'title' => esc_html__( 'Vertical', 'ae-pro' ),
						'icon'  => 'eicon-slider-vertical',
					],
				],
				'default'      => 'horizontal',
				'render_type'  => 'template',
				'prefix_class' => 'ae-listing-swiper-dir-',
			]
		);

		$this->add_control(
			'layout_mode_alert',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae_layout_mode_alert',
				//phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				'raw'             => __( '<p class="ae-editor-note"><i><b>Note:</b> Vertical Direction only used in Left / Right Listing Position</i></p>', 'ae-pro' ),
				'separator'       => 'none',
				'condition'       => [
					'direction' => 'vertical',
				],
			]
		);

		$this->add_control(
			'effect',
			[
				'label'   => __( 'Effects', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'fade'      => __( 'Fade', 'ae-pro' ),
					'slide'     => __( 'Slide', 'ae-pro' ),
					'coverflow' => __( 'Coverflow', 'ae-pro' ),
					'flip'      => __( 'Flip', 'ae-pro' ),
				],
				'default' => 'slide',
			]
		);

		$this->add_responsive_control(
			'slide_per_view',
			[
				'label'              => __( 'Slides Per View', 'ae-pro' ),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 1,
				'max'                => 100,
				'default'            => 3,
				'tablet_default'     => 2,
				'mobile_default'     => 1,
				'conditions'         => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'effect',
							'operator' => '==',
							'value'    => 'slide',
						],
						[
							'name'     => 'effect',
							'operator' => '==',
							'value'    => 'coverflow',
						],
					],
				],
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'slides_per_group',
			[
				'label'              => __( 'Slides Per Group', 'ae-pro' ),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 1,
				'max'                => 100,
				'default'            => 1,
				'tablet_default'     => 1,
				'mobile_default'     => 1,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'speed',
			[
				'label'       => __( 'Speed', 'ae-pro' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => 1000,
				],
				'description' => __( 'Duration of transition between slides (in ms)', 'ae-pro' ),
				'range'       => [
					'px' => [
						'min'  => 1000,
						'max'  => 10000,
						'step' => 1000,
					],
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'ae-pro' ),
				'label_off'    => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'duration',
			[
				'label'       => __( 'Duration', 'ae-pro' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => 900,
				],
				'description' => __( 'Delay between transitions (in ms)', 'ae-pro' ),
				'range'       => [
					'px' => [
						'min'  => 300,
						'max'  => 3000,
						'step' => 300,
					],
				],
				'condition'   => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label'              => __( 'Space Between Slides', 'ae-pro' ),
				'type'               => Controls_Manager::SLIDER,
				'default'            => [
					'size' => 15,
				],
				'tablet_default'     => [
					'size' => 10,
				],
				'mobile_default'     => [
					'size' => 5,
				],
				'range'              => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 5,
					],
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'auto_height',
			[
				'label'        => __( 'Auto Height', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'        => __( 'Pause on Hover', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'marker_layout' => 'carousel',
				],
			]
		);

		$this->add_control(
			'ptype',
			[
				'label'   => __( ' Pagination Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' =>
					[
						''         => __( 'None', 'ae-pro' ),
						'bullets'  => __( 'Bullets', 'ae-pro' ),
						'fraction' => __( 'Fraction', 'ae-pro' ),
						'progress' => __( 'Progress', 'ae-pro' ),
					],
				'default' => 'bullets',
			]
		);

		$this->add_control(
			'clickable',
			[
				'label'     => __( 'Clickable', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Yes', 'ae-pro' ),
				'label_off' => __( 'No', 'ae-pro' ),
				'condition' => [
					'ptype' => 'bullets',
				],
			]
		);

		$this->add_control(
			'keyboard',
			[
				'label'        => __( 'Keyboard Control', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'scrollbar',
			[
				'label'        => __( 'Scroll bar', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'navigation_heading',
			[
				'label' => __( 'Navigation', 'ae-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'navigation_button',
			[
				'label'        => __( 'Enable', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'arrows_layout',
			[
				'label'     => __( 'Position', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inside',
				'options'   => [
					'inside'  => __( 'Inside', 'ae-pro' ),
					'outside' => __( 'Outside', 'ae-pro' ),
				],
				'condition' => [
					'navigation_button' => 'yes',
				],

			]
		);

		$this->add_control(
			'arrow_icon_left',
			[
				'label'            => __( 'Icon Prev', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-left',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'navigation_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'arrow_icon_right',
			[
				'label'            => __( 'Icon Next', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-right',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'navigation_button' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_horizontal_position',
			[
				'label'       => __( 'Horizontal Position', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'     => 'center',
				'condition'   => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_vertical_position',
			[
				'label'       => __( 'Vertical Position', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'     => 'center',
				'condition'   => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',

				],
			]
		);

		$this->end_controls_section();
	}

	public function get_map_styles_section() {

		$this->start_controls_section(
			'section_map_style',
			[
				'label' => __( 'Map Style', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'snazzy_map_style',
			[
				'label'       => __( 'Snazzy Style', 'ae-pro' ),
				'type'        => Controls_Manager::TEXTAREA,
				'description' => __( 'Add style from Snazzy Maps. Copy and Paste style array from here -> <a href="https://snazzymaps.com/explore" target="_blank">Snazzy Maps</a>', 'ae-pro' ),
			]
		);

		$this->end_controls_section();
	}

	public function get_map_style_section() {
		$this->start_controls_section(
			'map_style_section',
			[
				'label' => __( 'Map', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_marker',
			[
				'label' => __( 'Marker', 'ae-pro' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'          => 'marker_border',
				'label'         => __( 'Border', 'ae-pro' ),
				'fields_option' => [
					'width' => [
						'default' => [
							'top'    => 0,
							'right'  => 0,
							'bottom' => 0,
							'left'   => 0,
							'unit'   => 'px',
						],
					],
				],
				'selector'      => '{{WRAPPER}} .ae-map-render .ae-map-marker',
			]
		);

		$this->add_control(
			'marker_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-map-render .ae-map-marker' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_control(
			'marker_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'    => 0,
					'right'  => 0,
					'bottom' => 0,
					'left'   => 0,
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .ae-map-render .ae-map-marker' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'marker_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-map-render .ae-map-marker',
			]
		);

		$this->add_control(
			'info_window_divider',
			[
				'type'      => Controls_Manager::DIVIDER,
				'style'     => 'thick',
				'condition' => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_control(
			'heading_infowindow',
			[
				'label'     => __( 'Info Window', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_control(
			'info_window_width',
			[
				'label'     => __( 'Width', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 250,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-map-render .ae-map-infowindow' => 'width: {{SIZE}}{{UNIT}}; max-width: 800px !important;',
				],
				'condition' => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_responsive_control(
			'infowindow_text_align',
			[
				'label'     => __( 'Text Alignment', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-map-render .ae-map-infowindow .infowindow' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'info_window_type'               => 'default',
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'infowindow_typography',
				'label'     => __( 'Typography', 'ae-pro' ),
				'selector'  => '{{WRAPPER}} .ae-map-render .ae-map-infowindow',
				'condition' =>
					[
						'info_window_type'               => 'default',
						'marker_click_action'            => 'info_window',
						'marker_click_action_post_query' => 'info_window',
					],
			]
		);

		$this->add_control(
			'infowindow_bg',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-map-render .ae-map-infowindow' => 'background: {{VALUE}};',
					'{{WRAPPER}} .ae-map-render .ae-map-infowindow::after' => 'background: linear-gradient(45deg,{{VALUE}} 50%,rgba(255,255,255,0) 51%,rgba(255,255,255,0) 100%);',
				],
				'default'   => '#fff',
				'condition' => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'           => 'infowindow_border',
				'label'          => __( 'Border', 'ae-pro' ),
				'selector'       => '{{WRAPPER}} .ae-map-render .ae-map-infowindow, {{WRAPPER}} .ae-map-render .ae-map-infowindow::after',
				'fields_options' => [
					'color' => [
						'default' => '#000',
					],
					'width' => [
						'default' => [
							'top'    => '1',
							'bottom' => '1',
							'right'  => '1',
							'left'   => '1',
						],
					],
				],
				'condition'      => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_control(
			'infowindow_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-map-render .ae-map-infowindow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_control(
			'infowindow_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-map-render .ae-map-infowindow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
				'condition'  => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'infowindow_box_shadow',
				'label'     => __( 'Item Shadow', 'ae-pro' ),
				'selector'  => '{{WRAPPER}} .ae-map-render .ae-map-infowindow',
				'condition' => [
					'marker_click_action'            => 'info_window',
					'marker_click_action_post_query' => 'info_window',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_marker_listing_style_section() {
		$this->start_controls_section(
			'marker_listing_grid_style_section',
			[
				'label'     => __( 'Marker Listing', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'marker_listing' => 'yes',
				],
			]
		);

		$this->add_control(
			'space_between_map_listing',
			[
				'label'     => __( 'Map - Listing Space', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}}.ae-map-marker-listing-align-bottom .ae-map-render' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-map-marker-listing-align-top .ae-map-render' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-map-marker-listing-align-right .ae-map-render' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-map-marker-listing-align-left .ae-map-render' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'marker_listing_style' );
		$this->start_controls_tab( 'marker_listing_style_normal', [ 'label' => __( 'Normal', 'ae-pro' ) ] );
			$this->add_group_control(
				Group_Control_Background::get_type(),
				[
					'name'     => 'item_bg',
					'label'    => __( 'Item Background', 'ae-pro' ),
					'types'    => [ 'none', 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .ae-marker-item-inner',
					'default'  => '#fff',
				]
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name'     => 'item_border',
					'label'    => __( 'Border', 'ae-pro' ),
					'selector' => '{{WRAPPER}} .ae-marker-item-inner',
				]
			);

			$this->add_control(
				'item_border_radius',
				[
					'label'      => __( 'Border Radius', 'ae-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .ae-marker-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'item_box_shadow',
					'label'    => __( 'Item Shadow', 'ae-pro' ),
					'selector' => '{{WRAPPER}} .ae-marker-item-inner',
				]
			);

		$this->end_controls_tab();
		$this->start_controls_tab( 'marker_listing_style_hover', [ 'label' => __( 'Hover', 'ae-pro' ) ] );
			$this->add_group_control(
				Group_Control_Background::get_type(),
				[
					'name'     => 'item_bg_hover',
					'label'    => __( 'Item Background', 'ae-pro' ),
					'types'    => [ 'none', 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .ae-marker-item-inner:hover',
					'default'  => '#fff',
				]
			);

			$this->add_control(
				'item_border_hover',
				[
					'label'     => __( 'Border Color', 'ae-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ae-marker-item-inner:hover' => 'border-color: {{VALUE}};',
					],
				]
			);
			$this->add_control(
				'item_border_radius_hover',
				[
					'label'      => __( 'Border Radius', 'ae-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .ae-marker-item-inner:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'item_box_shadow_hover',
					'label'    => __( 'Item Shadow', 'ae-pro' ),
					'selector' => '{{WRAPPER}} .ae-marker-item-inner:hover',
				]
			);

		$this->end_controls_tab();
		$this->start_controls_tab( 'marker_listing_style_active', [ 'label' => __( 'Active', 'ae-pro' ) ] );

			$this->add_group_control(
				Group_Control_Background::get_type(),
				[
					'name'     => 'item_bg_active',
					'label'    => __( 'Item Background', 'ae-pro' ),
					'types'    => [ 'none', 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .ae-marker-active .ae-marker-item-inner', //{{WRAPPER}} .ae-marker-item.swiper-slide-active .ae-marker-item-inner',
					'default'  => '#fff',
				]
			);

			$this->add_control(
				'item_border_active',
				[
					'label'     => __( 'Border Color', 'ae-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ae-marker-active .ae-marker-item-inner' => 'border-color: {{VALUE}};',
					],
				]
			);
			$this->add_control(
				'item_border_radius_active',
				[
					'label'      => __( 'Border Radius', 'ae-pro' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors'  => [
						'{{WRAPPER}} .ae-marker-active .ae-marker-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name'     => 'item_box_shadow_active',
					'label'    => __( 'Item Shadow', 'ae-pro' ),
					'selector' => '{{WRAPPER}} .ae-marker-active .ae-marker-item-inner',
				]
			);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function get_responsive_btn_style_section() {
		$this->start_controls_section(
			'section_responsive_btn_style',
			[
				'label' => esc_html__( 'Responsive Mode Button', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'responsive_btn_typography',
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
				'selector' => '{{WRAPPER}} .ae-responsive-btn .button',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'responsive_btn_text_shadow',
				'selector' => '{{WRAPPER}} .ae-responsive-btn .button',
			]
		);

		$this->start_controls_tabs( 'tabs_responsive_btn_style' );

		$this->start_controls_tab(
			'tab_responsive_btn_normal',
			[
				'label' => esc_html__( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'responsive_btn_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .ae-responsive-btn .button' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'responsive_btn_background',
				'label'          => esc_html__( 'Background', 'ae-pro' ),
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'selector'       => '{{WRAPPER}} .ae-responsive-btn .button',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'global' => [
							'default' => Global_Colors::COLOR_ACCENT,
						],
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_responsive_btn_hover',
			[
				'label' => esc_html__( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-responsive-btn .button:hover, {{WRAPPER}} .ae-responsive-btn .button:focus' => 'color: {{VALUE}};',
					'{{WRAPPER}} .ae-responsive-btn .button:hover svg, {{WRAPPER}} .ae-responsive-btn .button:focus svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'responsive_btn_background_hover',
				'label'          => esc_html__( 'Background', 'ae-pro' ),
				'types'          => [ 'classic', 'gradient' ],
				'exclude'        => [ 'image' ],
				'selector'       => '{{WRAPPER}} .ae-responsive-btn .button:hover, {{WRAPPER}} .ae-responsive-btn .button:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'responsive_btn_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-responsive-btn .button:hover, {{WRAPPER}} .ae-responsive-btn .button:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'responsive_btn_border',
				'selector'  => '{{WRAPPER}} .ae-responsive-btn .button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'responsive_btn_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-responsive-btn .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'responsive_btn_button_box_shadow',
				'selector' => '{{WRAPPER}} .ae-responsive-btn .button',
			]
		);

		$this->add_responsive_control(
			'responsive_btn_text_padding',
			[
				'label'      => esc_html__( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-responsive-btn .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section();
	}

	public function get_carousel_style_section() {
		$this->start_controls_section(
			'carousel_style',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'marker_layout'  => 'carousel',
					'marker_listing' => 'yes',
				],
			]
		);

		$this->add_control(
			'heading_style_arrow',
			[
				'label'     => __( 'Prev/Next Navigation', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);
		$this->start_controls_tabs( 'tabs_arrow_styles' );

		$this->start_controls_tab(
			'tab_arrow_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'arrow_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-prev svg' => 'fill:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next svg' => 'fill:{{VAlUE}};',
				],
				'default'   => '#444',
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_bg_color',
			[
				'label'     => __( ' Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev' => 'background-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'arrow_border',
				'label'     => __( 'Border', 'ae-pro' ),
				'selector'  => '{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-container .ae-swiper-button-next, {{WRAPPER}} .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-button-next',
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-next, {{WRAPPER}} .ae-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
				'condition'  =>
					[
						'navigation_button' => 'yes',
					],
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrow_hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);
		$this->add_control(
			'arrow_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-prev:hover svg' => 'fill:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover svg' => 'fill:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_bg_color_hover',
			[
				'label'     => __( ' Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover' => 'background-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_color_hover',
			[
				'label'     => __( ' Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover' => 'border-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover' => 'border-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_radius_hover',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-next:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
				'condition'  =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'arrow_size',
			[
				'label'     => __( 'Arrow Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 50,
					],
				'range'     =>
					[
						'min'  => 20,
						'max'  => 100,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-swiper-button-next i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-swiper-button-prev svg' => 'width : {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-swiper-button-next svg' => 'width : {{SIZE}}{{UNIT}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_responsive_control(
			'horizontal_arrow_offset',
			[
				'label'          => __( 'Horizontal Offset', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}} .ae-hpos-left .ae-swiper-button-wrapper' => 'left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-right .ae-swiper-button-wrapper' => 'right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-center .ae-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-center .ae-swiper-button-next' => 'right: {{SIZE}}{{UNIT}}',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
			]
		);
		$this->add_responsive_control(
			'vertical_arrow_offset',
			[
				'label'          => __( 'Vertical Offset', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}} .ae-vpos-top .ae-swiper-button-wrapper' => 'top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-bottom .ae-swiper-button-wrapper' => 'bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-middle .ae-swiper-button-prev' => 'top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-middle .ae-swiper-button-next' => 'top: {{SIZE}}{{UNIT}}',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_gap',
			[
				'label'          => __( 'Arrow Gap', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => 'px',
					'size' => '25',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}}.ae-listing-swiper-dir-horizontal .ae-swiper-container'     => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.ae-listing-swiper-dir-horizontal .ae-swiper-outer-wrapper' => 'position: relative',
					'{{WRAPPER}}.ae-listing-swiper-dir-horizontal .ae-swiper-button-prev'   => 'left: 0',
					'{{WRAPPER}}.ae-listing-swiper-dir-horizontal .ae-swiper-button-next'   => 'right: 0',
					'{{WRAPPER}}.ae-listing-swiper-dir-vertical .ae-swiper-outer-wrapper' => ' position: relative; padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.ae-listing-swiper-dir-vertical .ae-swiper-button-prev'   => 'top: 0; left: 50%; transform: translateX(-50%);',
					'{{WRAPPER}}.ae-listing-swiper-dir-vertical .ae-swiper-button-next'   => 'bottom: 0; right: 50%; top: unset; transform: translateX(50%);',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'outside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-button-prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .ae-swiper-button-next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_style_dots',
			[
				'label'     => __( 'Dots', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'dots_size',
			[
				'label'     => __( 'Dots Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'width:{{SIZE}}px; height:{{SIZE}}px;',
				],
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_responsive_control(
			'dot_top_offset',
			[
				'label'     => __( 'Top Offset', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-post-widget-wrapper' => 'margin-bottom:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'ptype' => 'bullets',
				],
			]
		);

		$this->add_control(
			'dots_color',
			[
				'label'     => __( 'Active Dot Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet-active' => 'background-color:{{VAlUE}} !important;',
				],
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'inactive_dots_color',
			[
				'label'     => __( 'Inactive Dot Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_responsive_control(
			'pagination_bullet_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'heading_style_fraction',
			[
				'label'     => __( 'Fraction', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_control(
			'fraction_bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-current, {{WRAPPER}} .swiper-pagination-total' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_control(
			'fraction_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-fraction' => 'color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'pagination_typography',
				'label'     => __( 'Typography', 'ae-pro' ),
				'selector'  => '{{WRAPPER}} .swiper-pagination-fraction',
				'condition' =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_responsive_control(
			'fraction_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .swiper-pagination-fraction .swiper-pagination-current, {{WRAPPER}} .swiper-pagination-fraction .swiper-pagination-total' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						'ptype' => 'fraction',
					],
			]
		);

		$this->add_control(
			'heading_style_scroll',
			[
				'label'     => __( 'Scrollbar', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);
		$this->add_control(
			'scroll_size',
			[
				'label'     => __( 'Scrollbar Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-container-vertical .ae-swiper-scrollbar' => 'width:{{SIZE}}px;',
					'{{WRAPPER}} .swiper-container-horizontal .ae-swiper-scrollbar' => 'height:{{SIZE}}px;',
				],
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);

		$this->add_control(
			'scrollbar_color',
			[
				'label'     => __( 'Scrollbar Drag Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-scrollbar-drag' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);

		$this->add_control(
			'scroll_color',
			[
				'label'     => __( 'Scrollbar Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-scrollbar' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);

		$this->add_control(
			'heading_style_progress',
			[
				'label'     => __( 'Progress Bar', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'ptype' => 'progressbar',
					],
			]
		);
		$this->add_control(
			'progressbar_color',
			[
				'label'     => __( 'Prgress Bar Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'progressbar',
					],
			]
		);

		$this->add_control(
			'progress_color',
			[
				'label'     => __( 'Prgress Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar-fill' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'progressbar',
					],
			]
		);

		$this->add_control(
			'progressbar_size',
			[
				'label'     => __( 'Prgress Bar Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-container-horizontal .swiper-pagination-progressbar' => 'height:{{SIZE}}px;',
					'{{WRAPPER}} .swiper-container-vertical .swiper-pagination-progressbar' => 'width:{{SIZE}}px;',
				],
				'condition' =>
					[
						'ptype' => 'progressbar',
					],
			]
		);

		$this->add_responsive_control(
			'pagination_progress_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						'ptype' => 'progressbar',
					],
			]
		);

		$this->end_controls_section();
	}

	public function no_post_msg_style_section() {
		$this->start_controls_section(
			'no_posts_message_style',
			[
				'label'     => __( 'No Data Found Message', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'no_posts_message!' => '',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'no_posts_msg_typography',
				'selector' => '{{WRAPPER}} .ae-no-posts',
			]
		);
		$this->add_responsive_control(
			'no_posts_msg_align',
			[
				'label'     => __( 'Alignment', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'fa fa-align-right',
					],
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .ae-no-posts' => 'text-align: {{VALUE}}; width: 100%;',
				],
			]
		);
		$this->add_control(
			'no_posts_msg_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-no-posts' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'no_posts_bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-no-posts' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'no_posts_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-no-posts',
			]
		);

		$this->add_responsive_control(
			'no_posts_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-no-posts' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'no_posts_padding',
			[
				'label'     => __( 'Padding', 'ae-pro' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .ae-no-posts' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_block_layouts( $contol_name, $condition = [] ) {
		$block_layouts[''] = 'Select Block Layout';
		$block_layouts     = $block_layouts + CacheManager::instance()->get_block_layouts();

		$this->add_control(
			$contol_name,
			[
				'label'       => __( 'Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'description' => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Block Layouts', 'https://wpvibes.link/go/feature-creating-block-layout/' ), 'ae-pro' ),
				'condition'   => $condition,
			]
		);
	}

	public function get_repeater_blocks( $control_name, $condition = [] ) {
		$repeater_block_layout[''] = 'Select Template';
		$repeater_block_layout     = $repeater_block_layout + Aepro::$_helper->ae_acf_repeater_layouts();

		$this->add_control(
			$control_name,
			[
				'label'       => __( 'Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $repeater_block_layout,
				'description' => __( 'Know more about layouts <a href="https://wpvibes.link/go/feature-creating-repeater-block-layout" target="_blank">Click Here</a>', 'ae-pro' ),
				'condition'   => $condition,
			]
		);
	}

	protected function render() {

		if ( $this->is_debug_on() ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$this->get_dynamic_map( $settings );
	}

	public function get_dynamic_map( $settings ) {
		$locations = $this->get_map_listing( $settings );
		$this->add_render_attribute( 'map-wrapper', 'class', 'ae-dynamic-map-wrapper' );
		$this->add_render_attribute( 'map-wrapper', 'data-map_type', $settings['map_type'] );
		if ( $settings['map_type'] === 'google_map' ) {
			$this->add_render_attribute( 'map-wrapper', 'data-map_options', wp_json_encode( $this->prepare_map_options() ) );
		}

		if ( ( count( $locations ) ) || ! empty( $settings['no_posts_message'] ) ) {
			$this->get_widget_title_html();
		}
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $settings['listing_responsive_mode'] == 'yes' ) {
			$breakpoints = [];
			if ( is_array( $settings['listing_responsive_mode_option'] ) ) {
				$breakpoints = $settings['listing_responsive_mode_option'];
			} else {
				$breakpoints[] = $settings['listing_responsive_mode_option'];
			}
			?>
			<style>
			<?php
			foreach ( $breakpoints as $breakpoint ) {
				?>
				body[data-elementor-device-mode="<?php echo $breakpoint; ?>"] <?php echo '.elementor-element-' . $this->get_id(); ?> .ae-listing-responsive-mode-yes.ae-dynamic-map-wrapper {
					flex-direction: column;
				}

				body[data-elementor-device-mode="<?php echo $breakpoint; ?>"] <?php echo '.elementor-element-' . $this->get_id(); ?> .ae-dynamic-map-wrapper.ae-listing-responsive-mode-yes .ae-map-render {
					width: 100%;
					position: absolute;
					z-index: 9;
					transition: opacity 0.5s ease;
					opacity: 1;
					margin: 0;
				}
				body[data-elementor-device-mode="<?php echo $breakpoint; ?>"] <?php echo '.elementor-element-' . $this->get_id(); ?>  .ae-dynamic-map-wrapper.ae-listing-responsive-mode-yes .ae-map-listing {
					width: 100% ;
					background-color: #fff;
					height: inherit;
					transition: opacity 0.5s ease;
					opacity: 0;
				}
				body[data-elementor-device-mode="<?php echo $breakpoint; ?>"] <?php echo '.elementor-element-' . $this->get_id(); ?> .ae-listing-responsive-mode-yes .ae-responsive-btn{
					display: block;
				}
				<?php
			}
			?>
			</style>
			<?php
			$this->add_render_attribute( 'map-wrapper', 'class', 'ae-listing-responsive-mode-yes ' . implode( ' ', $breakpoints ) );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'map-wrapper' ); ?>>
			<?php
			if ( ! count( $locations ) ) {
				echo $this->ae_no_post_message( $settings );
				?>
				</div> <!-- end .ae-dynamic-map-wrapper -->
				<?php
				return;
			}
			$this->add_render_attribute( 'map-render', 'class', 'ae-map-render' );
			$this->add_render_attribute( 'map-listing', 'class', 'ae-map-listing' );
			?>
			<div <?php echo $this->get_render_attribute_string( 'map-render' ); ?>>
				<?php $this->get_map_render( $locations ); ?>
			</div>
			<!-- Map Render -->
			<?php //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison ?>
			<?php if ( $settings['marker_listing'] == 'yes' ) { ?>
			<div <?php echo $this->get_render_attribute_string( 'map-listing' ); ?>>
				<?php
				$this->add_render_attribute( 'collection', 'class', 'ae-map-collection' );
				$this->add_render_attribute( 'marker-wrapper', 'class', 'ae-map-marker-wrapper' );
				$this->add_render_attribute( 'marker-wrapper', 'class', 'ae-height-100' );
				?>
				<?php
				if ( $settings['marker_layout'] === 'carousel' ) {
					$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-swiper-outer-wrapper' );
					$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-carousel-yes' );
					$this->remove_render_attribute( 'marker-wrapper', 'class', 'ae-height-100' );
					$this->add_render_attribute( 'collection', 'class', 'ae-swiper-container swiper-container' );
					$this->add_render_attribute( 'marker-wrapper', 'class', 'ae-swiper-wrapper swiper-wrapper' );
					$swiper_data = $this->get_swiper_data( $settings );
					$this->add_render_attribute( 'outer-wrapper', 'data-swiper-settings', wp_json_encode( $swiper_data ) );
					if ( $settings['arrows_layout'] === 'inside' ) {
						$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-hpos-' . $settings['arrow_horizontal_position'] );
						$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-vpos-' . $settings['arrow_vertical_position'] );
					}
					?>
				<div <?php echo $this->get_render_attribute_string( 'outer-wrapper' ); ?> >
					<div <?php echo $this->get_render_attribute_string( 'collection' ); ?>> 
				<?php } ?>
						<div <?php echo $this->get_render_attribute_string( 'marker-wrapper' ); ?> >
							<?php
								$marker_index = 0;

							foreach ( $locations as $location ) {
								if ( ! isset( $location['custom_listing_layout_id'] ) || empty( $location['custom_listing_layout_id'] ) || get_post_status( $location['custom_listing_layout_id'] ) !== 'publish' ) {
									//phpcs:ignore WordPress.Security.NonceVerification.Recommended
									if ( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() || ( isset( $_GET['preview'] ) && $_GET['preview'] === 'true' ) ) {
										printf( '<div class"message"><p class="%1$s">%2$s</p></div>', esc_attr( 'elementor-alert elementor-alert-warning' ), esc_html( __( "Please select a Block Layout first from 'Content > Marker Listing > Block Layout'", 'ae-pro' ) ) );
										$this->set_render_attribute( 'marker-wrapper', 'class', 'column-full-width' );
									}
									return;
								}
								$template           = $location['custom_listing_layout_id'];
								$item_classes       = [ 'ae-marker-item' ];
								$item_inner_classes = [ 'ae-marker-item-inner' ];
								if ( $settings['marker_layout'] === 'carousel' ) {
									$item_classes[]       = 'ae-swiper-slide swiper-slide';
									$item_inner_classes[] = 'ae-swiper-slide-wrapper';
								}
								$this->set_render_attribute( 'item', 'class', $item_classes );
								$this->set_render_attribute( 'item', 'data-id', $marker_index );
								$this->set_render_attribute( 'item-inner', 'class', $item_inner_classes );
								?>
									<article <?php echo $this->get_render_attribute_string( 'item' ); ?> >
										<div <?php echo $this->get_render_attribute_string( 'item-inner' ); ?>>
											<?php if ( !Plugin::$instance->preview->is_preview() && !Plugin::$instance->editor->is_edit_mode()) { ?>
											<div class="ae_data elementor elementor-<?php echo $template; ?>">
											<?php } ?>
											<?php
											if ( isset( $location['marker_listing'] ) && $location['marker_listing'] ) {
												echo $location['marker_listing'];
											}
											?>
											<?php if ( !Plugin::$instance->preview->is_preview() && !Plugin::$instance->editor->is_edit_mode()) { ?>
											</div>
											<?php } ?>
										</div>
									</article>
								<?php
								++$marker_index;
							}
							?>
						</div>
						<!-- Marker Wrapper -->
				<?php if ( $settings['marker_layout'] === 'carousel' ) { ?>
					<?php
					if ( $settings['ptype'] !== '' ) {
						?>
							<div class = "ae-swiper-pagination swiper-pagination"></div>
						<?php
					}
						/** Arrows Inside **/
					if ( $settings['navigation_button'] === 'yes' && $settings['arrows_layout'] === 'inside' ) {
						$this->get_swiper_arrows( $settings );
					}

					if ( $settings['scrollbar'] === 'yes' ) {
						?>
							<div class = "ae-swiper-scrollbar swiper-scrollbar"></div>
						<?php
					}
					?>
					</div>
					<!-- Collection -->
					<?php
					if ( $settings['navigation_button'] === 'yes' && $settings['arrows_layout'] === 'outside' ) {
						/** Arrows Outside **/
						$this->get_swiper_arrows( $settings );
					}
					?>
					</div>
					<!-- Outer Wrapper -->
				<?php } ?>
			</div>
			<!-- Map Listing -->
				<?php
				if ( $settings['listing_responsive_mode'] === 'yes' ) {
					$show_list_btn_text = $settings['show_list_text'];
					if ( $show_list_btn_text === '' ) {
						$show_list_btn_text = 'Show List';
					}
					$close_list_btn_text = $settings['close_list_text'];
					if ( $close_list_btn_text === '' ) {
						$close_list_btn_text = 'Close List';
					}
					?>
				<div class="ae-responsive-btn hide-list">
					<a href="#" class="button" data-close_list_text="<?php echo $close_list_btn_text; ?>" data-show_list_text="<?php echo $show_list_btn_text; ?>"><?php echo $show_list_btn_text; ?></a>
				</div>
					<?php
				}
				?>
			<?php } ?>
		</div>
		<!-- Map Wrapper -->
		<?php
	}

	public function get_swiper_arrows( $settings ) {

		if ( $settings['arrow_horizontal_position'] !== 'center' && $settings['arrows_layout'] === 'inside' ) {
			?>
			<div class="ae-swiper-button-wrapper">
			<?php
		}
		?>
		<div class = "ae-swiper-button-prev swiper-button-prev">
			<?php
			if ( $settings['direction'] === 'vertical' ) {
				Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
			} else {
				if ( is_rtl() ) {
					Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
				} else {
					Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
				}
			}
			?>
		</div>
		<div class = "ae-swiper-button-next swiper-button-next">
			<?php
			if ( $settings['direction'] === 'vertical' ) {
				Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
			} else {
				if ( is_rtl() ) {
					Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
				} else {
					Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
				}
			}
			?>
		</div>
		<?php
		if ( $settings['arrow_horizontal_position'] !== 'center' && $settings['arrows_layout'] === 'inside' ) {
			;
			?>
			</div>
			<?php
		}
	}

	public function get_map_render( $locations ) {
		$this->add_render_attribute( 'map-marker', 'class', 'marker' );
		$marker_index = 0;
		foreach ( $locations as $location ) {
			if ( ! empty( $location ) ) {
				$location['map']['marker_index'] = $marker_index;
				$this->set_render_attribute( 'map-marker', 'data-marker', wp_json_encode( $location['map'] ) );
				$this->set_render_attribute( 'map-marker', 'style', 'display: none;' );
				?>
				<div <?php echo $this->get_render_attribute_string( 'map-marker' ); ?>>
					<?php echo $location['address']; ?>
				</div>	
				<?php
				++$marker_index;
			}
		}
	}

	public function get_map_listing( $settings ) {
		return $this->get_map_data( $settings );
	}

	public function get_map_data( $settings ) {
		$post_data = Aepro::$_helper->get_demo_post_data();
		$post_ID   = $post_data->ID;
		$locations = [];

		switch ( $settings['map_source'] ) {
			case 'current_post':
				$value     = '';
				$map_field = $this->get_map_field_data( $settings, $post_ID );
				if ( $map_field ) {
					$locations[ $post_ID ]['map']                     = [
						'id'  => $post_ID,
						'lat' => $map_field['lat'],
						'lng' => $map_field['lng'],
					];
					$locations[ $post_ID ]['address']                 = '';
					$locations[ $post_ID ]['map']['info_window_type'] = $settings['info_window_type'];
					if ( $settings['info_window_type'] === 'custom_layout' ) {
						$locations[ $post_ID ]['address'] = Plugin::instance()->frontend->get_builder_content( $settings['info_window_block_layout'], true );
					} else {
						if ( isset( $map_field['address'] ) ) {
							$locations[ $post_ID ]['address'] = $map_field['address'];
						}
					}
					if ( $settings['marker_type'] === 'dynamic' ) {
						if ( $settings['dynamic_marker_image_source'] === 'featured_image' ) {
							$value = get_the_post_thumbnail( $post_ID );
						} elseif ( $settings['dynamic_marker_image_source'] === 'custom_field' ) {
							$value = '<img src="' . wp_get_attachment_image_url( get_post_meta( $post_ID, $settings['dynamic_cf_marker_image'], true ) ) . '" />';
						} elseif ( $settings['dynamic_marker_image_source'] === 'acf_field' ) {
                            if ( get_field( $settings['dynamic_marker_image'], $post_ID ) ) {
								$value = '<img src="' . Aepro::$_helper->get_ae_acf_image_value( $settings['dynamic_marker_image'], $post_ID ) . '" />';
							}
                        }
						if ( $value !== '' ) {
							$locations[ $post_ID ]['map']['marker'] = '<div class="ae-map-marker">' . $value . '</div>';
						}
					}
				}
				break;
			case 'post_query':
				$query = new Query( $settings );
				$posts = $query->get_posts();
				if ( $posts->have_posts() ) {
					while ( $posts->have_posts() ) {
						$value = '';
						$posts->the_post();
						$post_ID   = get_the_ID();
						$post_link = get_the_permalink();
						$map_field = $this->get_map_field_data( $settings, $post_ID );
						if ( $map_field ) {
							//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
							if ( isset( $settings['marker_listing_block_layout'] ) && $settings['marker_listing_block_layout'] != '' ) {
								$locations[ $post_ID ]['marker_listing']           = Plugin::instance()->frontend->get_builder_content( $settings['marker_listing_block_layout'], true );
								$locations[ $post_ID ]['custom_listing_layout_id'] = $settings['marker_listing_block_layout'];
							}

							$locations[ $post_ID ]['map']                     = [
								'id'  => $post_ID,
								'lat' => $map_field['lat'],
								'lng' => $map_field['lng'],
							];
							$locations[ $post_ID ]['map']['info_window_type'] = $settings['info_window_type'];
							if ( $settings['info_window_type'] === 'custom_layout' ) {
								$locations[ $post_ID ]['address'] = Plugin::instance()->frontend->get_builder_content( $settings['info_window_block_layout'], true );
							} else {
								$locations[ $post_ID ]['address'] = $map_field['address'];
							}

							if ( $settings['marker_type'] === 'dynamic' ) {
								if ( $settings['dynamic_marker_image_source'] === 'featured_image' ) {
									$value = get_the_post_thumbnail( $post_ID );
								} elseif ( $settings['dynamic_marker_image_source'] === 'custom_field' ) {
									$value = '<img src="' . wp_get_attachment_image_url( get_post_meta( $post_ID, $settings['dynamic_cf_marker_image'], true ) ) . '" />';
								} elseif ( $settings['dynamic_marker_image_source'] === 'acf_field' ) {
									if ( get_field( $settings['dynamic_marker_image'], $post_ID ) ) {
										$value = '<img src="' . Aepro::$_helper->get_ae_acf_image_value( $settings['dynamic_marker_image'], $post_ID ) . '" />';
									}
								}
								if ( $value !== '' ) {
									$locations[ $post_ID ]['map']['marker'] = '<div class="ae-map-marker">' . $value . '</div>';
								}
							}

							if ( $settings['marker_click_action_post_query'] === 'post_link' ) {
								$locations[ $post_ID ]['map']['post_link']          = $post_link;
								$locations[ $post_ID ]['map']['open_in_new_window'] = $settings['marker_link_open_in_new_window'];
							}
						}
					}
					wp_reset_postdata();
				}
				break;
			case 'post_repeater':
				if ( have_rows( $settings['acf_repeater_field_name'], $post_ID ) ) {
					Frontend::$_in_repeater_block = true;
					$index                        = 0;
					while ( have_rows( $settings['acf_repeater_field_name'], $post_ID ) ) {
						the_row();
						$value     = '';
						$map_field = get_sub_field( $settings['repeater_sub_field'] );
						if ( $map_field ) {
							$locations[ $index ]['map'] = [
								'lat' => $map_field['lat'],
								'lng' => $map_field['lng'],
							];
							//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
							if ( isset( $settings['marker_listing_repeater_block_layout'] ) && $settings['marker_listing_repeater_block_layout'] != '' ) {
								$locations[ $index ]['marker_listing']           = Plugin::instance()->frontend->get_builder_content( $settings['marker_listing_repeater_block_layout'], true );
								$locations[ $index ]['custom_listing_layout_id'] = $settings['marker_listing_repeater_block_layout'];
							}

							$locations[ $index ]['map']['info_window_type'] = $settings['info_window_type'];
							if ( $settings['info_window_type'] === 'custom_layout' ) {
								$locations[ $index ]['address'] = Plugin::instance()->frontend->get_builder_content( $settings['info_window_repeater_block_layout'], true );
							} else {
								$locations[ $index ]['address'] = $map_field['address'];
							}
							if ( $settings['marker_type'] === 'dynamic' ) {
								if ( $settings['dynamic_marker_image_source'] === 'featured_image' ) {
									$value = get_the_post_thumbnail( $post_ID );
								} elseif ( $settings['dynamic_marker_image_source'] === 'acf_field' ) {
									if ( get_sub_field( $settings['dynamic_sub_field_marker_image'] ) ) {
										$value = '<img src="' . Aepro::$_helper->get_ae_acf_image_value( $settings['dynamic_sub_field_marker_image'], 0 ) . '" />';
									}
								}
								if ( $value !== '' ) {
									$locations[ $index ]['map']['marker'] = '<div class="ae-map-marker">' . $value . '</div>';
								}
							}

							++$index;
						}
					}
					Frontend::$_in_repeater_block = false;
				}
				break;
			default:
				break;
		}

		return $locations;
	}

	public function get_map_field_data( $settings, $post_ID ) {

		if ( $settings['field_type'] === 'acf_map_field' ) {

			$location = get_field( $settings['acf_map_field'], $post_ID );

		} elseif ( $settings['field_type'] === 'custom_field' ) {
			if ( $settings['custom_field_lat'] && $settings['custom_field_lng'] ) {
				if ( get_post_meta( $post_ID, $settings['custom_field_address'], true ) || get_post_meta( $post_ID, $settings['custom_field_lat'], true ) || get_post_meta( $post_ID, $settings['custom_field_lng'], true ) ) {
					$location = [
						'lat' => get_post_meta( $post_ID, $settings['custom_field_lat'], true ),
						'lng' => get_post_meta( $post_ID, $settings['custom_field_lng'], true ),
					];
					if ( $settings['custom_field_address'] ) {
						$location['address'] = get_post_meta( $post_ID, $settings['custom_field_address'], true );
					}
				}
			}
		}
		return $location;
	}

	public function prepare_map_options() {
		$settings    = $this->get_settings_for_display();
		$map_options = [
			'backgroundColor'        => $settings['backgroundColor'],
			'disableDefaultUI'       => $settings['disableDefaultUI'],
			'disableDoubleClickZoom' => $settings['disableDoubleClickZoom'],
			'mapTypeId'              => $settings['mapTypeId'],
			'zoom'                   => $settings['zoom']['size'],
			'marker_type'            => $settings['marker_type'],
			'auto_center'            => $settings['auto_center'],
			'markerAnimation'        => $settings['markerAnimation'],
			'markerCluster'          => $settings['markerCluster'],
		];

		if ( $settings['disableDefaultUI'] !== 'true' ) {
			$map_options['mapTypeControl']    = $settings['mapTypeControl'];
			$map_options['streetViewControl'] = $settings['streetViewControl'];
			$map_options['zoomControl']       = $settings['zoomControl'];
			$map_options['fullscreenControl'] = $settings['fullscreenControl'];
			$map_options['gestureHandling']   = $settings['gestureHandling'];

			if ( $settings['clickableIcons'] === 'yes' ) {
				$map_options['clickableIcons'] = true;
			} else {
				$map_options['clickableIcons'] = false;
			}
		}

		if ( $settings['snazzy_map_style'] !== '' ) {
			$map_options['styles'] = json_decode( $settings['snazzy_map_style'] );
		}

		if ( $settings['map_source'] === 'post_query' ) {
			$map_options['marker_click_action'] = $settings['marker_click_action_post_query'];
		} else {
			$map_options['marker_click_action'] = $settings['marker_click_action'];
		}
		//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $settings['listing_marker_sync'] == 'yes' ) {
			$map_options['listing_marker_sync'] = true;
		} else {
			$map_options['listing_marker_sync'] = false;
		}

		if ( $settings['marker_type'] === 'image' ) {
			$map_options['marker'] = [
				'type' => 'image',
				'icon' => '<div class="ae-map-marker"><img src="' . $settings['marker_image']['url'] . '" /></div>',
			];
		} elseif ( $settings['marker_type'] === 'icon' ) {
			if ( is_array( $settings['marker_icon']['value'] ) && isset( $settings['marker_icon']['value']['url'] ) ) {
				$map_options['marker'] = [
					'type' => 'svg',
					'icon' => '<div class="ae-map-marker">' . Icons_Manager::render_uploaded_svg_icon( $settings['marker_icon']['value'] ) . '</div>',
				];
			} else {
				$map_options['marker'] = [
					'type' => 'icon',
					'icon' => '<div class="ae-map-marker">' . Icons_Manager::render_font_icon( $settings['marker_icon'], [], 'i' ) . '</div>',
				];
			}
		} elseif ( $settings['marker_type'] === 'dynamic' ) {
			$map_options['marker'] = [
				'type' => 'image',
				'size' => $settings['marker_size']['size'],
			];
		}
		return $map_options;
	}

	public function get_swiper_data( $settings ) {

		if ( $settings['speed']['size'] ) {
			$swiper_data['speed'] = $settings['speed']['size'];
		} else {
			$swiper_data['speed'] = 1000;
		}
		$swiper_data['speed'] = $settings['speed']['size'];
		//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $settings['marker_listing_position'], [ 'left', 'right' ] ) ) {
			$swiper_data['direction'] = $settings['direction'];
		} else {
			$swiper_data['direction'] = 'horizontal';
		}

		if ( $settings['autoplay'] === 'yes' ) {
			$swiper_data['autoplay']['duration'] = $settings['duration']['size'];
		} else {
			$swiper_data['autoplay'] = false;
		}

		if ( $settings['pause_on_hover'] === 'yes' ) {
			$swiper_data['pause_on_hover'] = $settings['pause_on_hover'];
		}

		$swiper_data['effect'] = $settings['effect'];

		$swiper_data['loop']       = false;
		$swiper_data['autoHeight'] = ( $settings['auto_height'] === 'yes' ) ? true : false;

		$active_devices = Plugin::$instance->breakpoints->get_active_devices_list();

		if ( $settings['effect'] === 'fade' || $settings['effect'] === 'flip' ) {
			foreach ( $active_devices as $break_key => $active_device ) {
				if ( $active_device === 'desktop' ) {
					$active_device = 'default';
				}
				$swiper_data['spaceBetween'][ $active_device ] = 0;
			}
			foreach ( $active_devices as $break_key => $active_device ) {
				if ( $active_device === 'desktop' ) {
					$active_device = 'default';
				}
				$swiper_data['slidesPerView'][ $active_device ] = 1;
			}
		} else {
			foreach ( $active_devices as $break_key => $active_device ) {
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['spaceBetween'][ $active_device ] = intval( $settings[ 'space_' . $active_device ]['size'] !== '' ? $settings[ 'space_' . $active_device ]['size'] : 5 );
							break;
						case 'tablet':
							$swiper_data['spaceBetween'][ $active_device ] = intval( $settings[ 'space_' . $active_device ]['size'] !== '' ? $settings[ 'space_' . $active_device ]['size'] : 10 );
							break;
						case 'desktop':
							$swiper_data['spaceBetween']['default'] = intval( $settings['space']['size'] !== '' ? $settings['space']['size'] : 15 );
							break;
					}
				} else {
					$swiper_data['spaceBetween'][ $active_device ] = intval( $settings[ 'space_' . $active_device ]['size'] !== '' ? $settings[ 'space_' . $active_device ]['size'] : 15 );
				}
			}

			// SlidesPerView
			foreach ( $active_devices as $break_key => $active_device ) {
				//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
					switch ( $active_device ) {
						case 'mobile':
							$swiper_data['slidesPerView'][ $active_device ] = intval( $settings[ 'slide_per_view_' . $active_device ] !== '' ? $settings[ 'slide_per_view_' . $active_device ] : 1 );
							break;
						case 'tablet':
							$swiper_data['slidesPerView'][ $active_device ] = intval( $settings[ 'slide_per_view_' . $active_device ] !== '' ? $settings[ 'slide_per_view_' . $active_device ] : 2 );
							break;
						case 'desktop':
							$swiper_data['slidesPerView']['default'] = intval( $settings['slide_per_view'] !== '' ? $settings['slide_per_view'] : 3 );
							break;
					}
				} else {
					$swiper_data['slidesPerView'][ $active_device ] = intval( $settings[ 'slide_per_view_' . $active_device ] !== '' ? $settings[ 'slide_per_view_' . $active_device ] : 2 );
				}
			}
		}
		// SlidesPerGroup
		foreach ( $active_devices as $break_key => $active_device ) {
			//phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( in_array( $active_device, [ 'mobile', 'tablet', 'desktop' ] ) ) {
				switch ( $active_device ) {
					case 'mobile':
						$swiper_data['slidesPerGroup'][ $active_device ] = $settings[ 'slides_per_group_' . $active_device ] !== '' ? $settings[ 'slides_per_group_' . $active_device ] : 1;
						break;
					case 'tablet':
						$swiper_data['slidesPerGroup'][ $active_device ] = $settings[ 'slides_per_group_' . $active_device ] !== '' ? $settings[ 'slides_per_group_' . $active_device ] : 1;
						break;
					case 'desktop':
						$swiper_data['slidesPerGroup']['default'] = $settings['slides_per_group'] !== '' ? $settings['slides_per_group'] : 1;
						break;
				}
			} else {
				$swiper_data['slidesPerGroup'][ $active_device ] = $settings[ 'slides_per_group_' . $active_device ] !== '' ? $settings[ 'slides_per_group_' . $active_device ] : 1;
			}
		}

		$swiper_data['ptype'] = $settings['ptype'];
		if ( $settings['ptype'] !== '' ) {
			if ( $settings['ptype'] === 'progress' ) {
				$swiper_data['ptype'] = 'progressbar';
			}
		}
		$swiper_data['breakpoints_value'] = Aepro::$_helper->get_breakpoints();
		$swiper_data['clickable']         = isset( $settings['clickable'] ) ? $settings['clickable'] : false;
		$swiper_data['navigation']        = $settings['navigation_button'];
		$swiper_data['scrollbar']         = $settings['scrollbar'];
		$swiper_data['keyboard']          = $settings['keyboard'];

		return $swiper_data;
	}

	public function ae_no_post_message( $settings ) {
		if ( trim( $settings['no_posts_message'] ) === '' ) {
			return false;
		}
		return '<div class="ae-no-posts">' . do_shortcode( $settings['no_posts_message'] ) . '</div>';
	}
}
