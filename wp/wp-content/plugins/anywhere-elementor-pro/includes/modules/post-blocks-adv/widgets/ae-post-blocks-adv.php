<?php
namespace Aepro\Modules\PostBlocksAdv\Widgets;

use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Aepro\Classes\CacheManager;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Aepro\Post_Helper;
use Aepro\Modules\PostBlocksAdv\Skins;

class AePostBlocksAdv extends Widget_Base {

	protected $_access_level = 1;

	public function get_name() {
		return 'ae-post-blocks-adv';
	}

	public function get_title() {
		return __( 'AE - Post Blocks Adv', 'ae-pro' );
	}

	public function get_icon() {
		return 'ae-pro-icon eicon-post-list';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords() {
		return [
			'posts',
			'cpt',
			'item',
			'blocks',
			'loop',
			'query',
			'grid',
			'carousel',
			'portfolio',
			'custom post type',
		];
	}

	public function get_script_depends() {

		// load all scripts in editor and preview mode
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return [ 'jquery-masonry', 'ae-infinite-scroll', 'swiper' ];

		}

		$scripts  = [];
		$settings = $this->get_settings();

		if ( $settings['grid_masonry'] === 'yes' ) {
			$scripts[] = 'jquery-masonry';
		}

		if ( $settings['grid_show_infinite_scroll'] === 'yes' ) {
			$scripts[] = 'ae-infinite-scroll';
		}
		return $scripts;
	}

	protected $_has_template_content = false;
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Grid( $this ) );
		$this->add_skin( new Skins\Skin_Carousel( $this ) );
		/* Tab skin moved for next release(for better functionality) */
		$this->add_skin( new Skins\Skin_Accordion( $this ) );
	}
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function register_controls() {
		$this->get_layout_section();
		$this->get_query_section();
		$this->get_filter_section();
		$this->sale_badge_controls();
		$this->get_widget_title_controls();
		$this->layout_style_section();
		$this->noPostMsg_style_section();
		$this->filter_bar_style_controls();
		$this->sale_badge_styles();
		$this->get_widget_title_style_controls();
	}

	protected function render() {
	}

	public function get_layout_section() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'ae-pro' ),
			]
		);

		$block_layouts[''] = 'Select Block Layout';
		$block_layouts     = $block_layouts + CacheManager::instance()->get_block_layouts();
		$this->add_control(
			'layout',
			[
				'label'       => __( 'Primary Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'description' => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Block Layouts', 'https://wpvibes.link/go/feature-creating-block-layout/' ), 'ae-pro' ),
			]
		);

		$this->add_control(
			'tab_title',
			[
				'label'     => __( 'Tab Title', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'post_title'         => __( 'Post Title', 'ae-pro' ),
					'custom_field_title' => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'post_title',
				'condition' => [
					'_skin' => [ 'tabs', 'accordion' ],
				],
			]
		);

		$this->add_control(
			'tab_title_custom_field',
			[
				'label'       => __( 'Custom Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => 'Sub Field name for tab title',
				'condition'   => [
					'tab_title' => 'custom_field_title',
				],
			]
		);

		$this->add_control(
			'title_html_tag',
			[
				'label'     => __( 'Title HTML Tag', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'  => 'H1',
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'h5'  => 'H5',
					'h6'  => 'H6',
					'div' => 'div',
				],
				'default'   => 'div',
				'separator' => 'before',
				'condition' => [
					'_skin' => [ 'accordion', 'tabs' ],
				],
			]
		);

		$this->add_control(
			'show_filters',
			[
				'label'        => __( 'Show Filter Bar', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'_skin'   => 'grid',
					'source!' => 'current_loop',
				],
			]
		);

		$this->end_controls_section();
	}

	public function get_query_section() {

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'ae-pro' ),
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

		if ( \Aepro\Plugin::show_acf() || is_plugin_active( 'pods/init.php' ) ) {
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
				'option_page_relationship_field',
				[
					'label'       => __( 'Option Page', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
					'description' => __( 'Check for Relationship Field located at Option Page', 'ae-pro' ),
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
						'option_page_relationship_field!' => 'yes',
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
		//}

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
		$authors                     = [];
		$authors                     = Post_Helper::instance()->get_authors();
		$authors['current_author']   = __( 'Current Author', 'ae-pro' );
		$authors['logged_in_author'] = __( 'Logged In Author', 'ae-pro' );
		$this->add_control(
			'include_author_ids',
			[
				'label'       => 'Authors',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'show_label'  => false,
				'placeholder' => __( 'Enter Author ID Separated by Comma', 'ae-pro' ),
				'options'     => $authors,
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
				'options'     => $authors,
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
			'ignore_sticky_posts',
			[
				'label'        => __( 'Ignore Sticky Posts', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'source!' => [ 'current_loop', 'manual_selection', 'related', 'relation', 'post_object' ],
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

		$this->add_control(
			'no_posts_message',
			[
				'label'     => __( 'No Posts Message', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	public function get_filter_section() {
		$this->start_controls_section(
			'section_filter',
			[
				'label'     => __( 'Filters', 'ae-pro' ),
				'condition' => [
					'source!'      => 'current_loop',
					'show_filters' => 'yes',
					'_skin'        => 'grid',
				],
			]
		);

		$this->add_control(
			'filter_taxonomy',
			[
				'label'   => __( 'Taxonomy', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => Aepro::$_helper->get_rules_taxonomies(),
			]
		);

		foreach ( Aepro::$_helper->get_rules_taxonomies() as $key => $tax ) {
			$tax            = get_terms( $key );
			$tax_terms      = [];
			$tax_terms['0'] = __( '-- Select Term --', 'ae-pro' );
			foreach ( $tax as $terms ) {
				$tax_terms[ $terms->term_id ] = $terms->name;
			}
			$this->add_control(
				$key . '_filter_default_term',
				[
					'label'     => __( 'Default Term', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => $tax_terms,
					'condition' => [
						'filter_taxonomy' => $key,
					],
				]
			);

			$this->add_control(
				$key . '_filter_exclude_term_ids',
				[
					'label'       => __( 'Exclude Terms', 'ae-pro' ),
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'placeholder' => __( 'Enter Exclude Terms Separated by Comma', 'ae-pro' ),
					'options'     => $tax_terms,
					'condition'   => [
						'filter_taxonomy' => $key,
					],
				]
			);
		}

		$this->add_control(
			'filter_term_order_by',
			[
				'label'   => __( 'Order By', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'name',
				'options' => [
					'name'        => __( 'Name', 'ae-pro' ),
					'term_id'     => __( 'Term ID', 'ae-pro' ),
					'count'       => __( 'Post Count', 'ae-pro' ),
					'slug'        => __( 'Slug', 'ae-pro' ),
					'description' => __( 'Description', 'ae-pro' ),
					'parent'      => __( 'Term Parent', 'ae-pro' ),
					'menu_order'  => __( 'Menu Order', 'ae-pro' ),
				],
			]
		);

		$this->add_control(
			'filter_term_order',
			[
				'label'   => __( 'Order', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'DESC' => __( 'Descending', 'ae-pro' ),
					'ASC'  => __( 'Ascending', 'ae-pro' ),
				],
			]
		);

		$this->add_control(
			'only_parent_term',
			[
				'label'        => __( 'Only Parent', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '0',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => '0',
			]
		);

		$this->add_control(
			'hide_empty_term',
			[
				'label'        => __( 'Hide Empty', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => true,
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => true,
			]
		);

		$this->add_control(
			'show_all_divider',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_control(
			'show_all',
			[
				'label'        => __( "Show 'All' ", 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'tab_all_text',
			[
				'label'     => __( "Tab 'All' Text", 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'All',
				'condition' => [
					'show_all' => 'yes',
				],
			]
		);

		$this->add_control(
			'filter_label',
			[
				'label' => __( 'Label', 'ae-pro' ),
				'type'  => Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'collapse_filter',
			[
				'label'        => __( 'Collapse Filter', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_responsive_control(
			'collapse_filter_after',
			[
				'label'              => __( 'Collapse After', 'ae-pro' ),
				'type'               => Controls_Manager::NUMBER,
				'desktop_default'    => '3',
				'tablet_default'     => '2',
				'mobile_default'     => '1',
				'min'                => 0,
				'max'                => 100,
				'condition'          => [
					'collapse_filter' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'collapse_filter_text',
			[
				'label'     => __( 'Collapse Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Others', 'ae-pro' ),
				'condition' => [
					'collapse_filter' => 'yes',
				],
			]
		);

		$this->add_control(
			'collapse_filter_icon',
			[
				'label'            => __( 'Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-down',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'collapse_filter' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'filter_align',
			[
				'label'                => __( 'Alignment', 'ae-pro' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [
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
				'prefix_class'         => 'filter-align-',
				'default'              => 'left',
				'selectors_dictionary' => [
					'left'   => 'justify-content: flex-start;',
					'right'  => 'justify-content: flex-end;',
					'center' => 'justify-content: center;',
				],
				'selectors'            => [
					'{{WRAPPER}} .aep-filter-bar' => '{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'enable_url_hashtag',
			[
				'label'        => __( 'Enable Hashtag', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'_skin' => [ 'grid' ],
				],
			]
		);

		$this->add_control(
			'fragment_type',
			[
				'label'     => __( 'Fragment', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'default'      => __( 'Default', 'ae-pro' ),
					'term_id'      => __( 'Term ID', 'ae-pro' ),
					'term_slug'    => __( 'Term Slug', 'ae-pro' ),
					'custom_field' => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'default',
				'condition' => [
					'_skin'              => [ 'grid' ],
					'enable_url_hashtag' => 'yes',
				],
			]
		);

		$this->add_control(
			'fragment_custom_field',
			[
				'label'       => __( 'Fragment Custom Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field', 'ae-pro' ),
				'condition'   => [
					'_skin'              => [ 'grid' ],
					'enable_url_hashtag' => 'yes',
					'fragment_type'      => 'custom_field',
				],
			]
		);

		$this->end_controls_section();
	}

	public function layout_style_section() {

		$this->start_controls_section(
			'layout_style',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin!' => [ 'accordion', 'tabs' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg',
				'label'    => __( 'Item Background', 'ae-pro' ),
				'types'    => [ 'none', 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ae-post-item-inner',
				'default'  => '#fff',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-post-item-inner',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-post-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-post-item-inner',
			]
		);

		$this->end_controls_section();
	}

	public function noPostMsg_style_section() {
		$this->start_controls_section(
			'noPostMsg_style',
			[
				'label'     => __( 'No Post Message', 'ae-pro' ),
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
					'{{WRAPPER}} .ae-no-posts' => 'text-align: {{VALUE}};',
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

	public function filter_bar_style_controls() {

		$this->start_controls_section(
			'filter_bar_style',
			[
				'label'     => __( 'Filter Bar', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_filters' => 'yes',
					'_skin'        => 'grid',
				],
			]
		);

		$this->add_control(
			'filter_bar_bg',
			[
				'label'     => __( 'Filter Bar Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aep-filter-bar' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'filter_bar_padding',
			[
				'label'      => __( 'Filter Bar Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .aep-filter-bar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filter_bar_spacing',
			[
				'label'     => __( 'Spacing', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 300,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .aep-filter-bar' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'label_heading',
			[
				'label'     => __( 'Label', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			]
		);

		$this->style_model_controls(
			$this,
			[
				'name'          => 'filter_label',
				'label'         => __( 'Filter Label ', 'ae-pro' ),
				'typography'    => true,
				'color'         => true,
				'background'    => true,
				'border'        => true,
				'border-radius' => true,
				'margin'        => true,
				'padding'       => true,
				'box-shadow'    => true,
				'selector'      => '{{WRAPPER}} .filter-label',
			]
		);

		$this->add_control(
			'separator_filter_label',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_control(
			'item_heading',
			[
				'label'     => __( 'Items', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			]
		);

		$this->add_responsive_control(
			'hor_gap',
			[
				'label'     => __( 'Horizontal Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 300,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}}.filter-align-left .filter-items, {{WRAPPER}}.filter-align-left .filter-label' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.filter-align-right .filter-items, {{WRAPPER}}.filter-align-right .filter-label' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.filter-align-center .filter-items, {{WRAPPER}}.filter-align-center .filter-label' => 'margin-right: calc({{SIZE}}{{UNIT}}/2); margin-left: calc({{SIZE}}{{UNIT}}/2);',
				],
			]
		);

		$this->add_responsive_control(
			'ver_gap',
			[
				'label'     => __( 'Vertical Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 300,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .filter-items, {{WRAPPER}} .filter-label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [ '5' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-items a, {{WRAPPER}} .filter-label a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'filter_item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .filter-items',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'content_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
				'selector' => '{{WRAPPER}} .filter-items',
			]
		);

		$this->start_controls_tabs( 'filter_styles' );

		$this->start_controls_tab( 'filter_style_normal', [ 'label' => __( 'Normal', 'ae-pro' ) ] );

		$this->add_control(
			'filter-color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .filter-items a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_item_bg',
			[
				'label'     => __( 'Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .filter-items' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-items' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'filter_style_hover', [ 'label' => __( 'Hover/Active', 'ae-pro' ) ] );

		$this->add_control(
			'filter-hover-color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'{{WRAPPER}} .filter-items:hover > a, {{WRAPPER}} .filter-items > a .filter-items:hover > a, {{WRAPPER}} .filter-items.active > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_item_hover_bg',
			[
				'label'     => __( 'Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .filter-items:hover, {{WRAPPER}} .filter-items.active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter-hover-border-color',
			[
				'label'     => __( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'{{WRAPPER}} .filter-items:hover, {{WRAPPER}} .filter-items.active' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'filter_item_hover_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-items:hover, {{WRAPPER}} .filter-items.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'filter_bar_collapsed_style',
			[
				'label'     => __( 'Filter Bar - Collapsed', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_filters'    => 'yes',
					'collapse_filter' => 'yes',
					'_skin'           => 'grid',
				],
			]
		);

		$this->add_control(
			'collapsed_label_heading',
			[
				'label'     => __( 'Collapsed Label', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'collapsed_label_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .filter-items.ae-dropdown',
			]
		);

		$this->start_controls_tabs( 'collapse_filter_label_styles' );

		$this->start_controls_tab( 'collapse_filter_label_normal', [ 'label' => __( 'Normal', 'ae-pro' ) ] );

		$this->style_model_controls(
			$this,
			[
				'name'          => 'collapse_filter_label',
				'label'         => __( 'Collapsed Filter', 'ae-pro' ),
				'typography'    => false,
				'color'         => true,
				'background'    => true,
				'border'        => true,
				'border-radius' => true,
				'margin'        => false,
				'padding'       => false,
				'box-shadow'    => true,
				'selector'      => '{{WRAPPER}} .filter-items.ae-dropdown',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'collapse_filter_label_hover', [ 'label' => __( 'hover', 'ae-pro' ) ] );

		$this->style_model_controls(
			$this,
			[
				'name'          => 'collapse_filter_label_hover',
				'label'         => __( 'Collapsed Filter ', 'ae-pro' ),
				'typography'    => false,
				'color'         => true,
				'background'    => true,
				'border'        => true,
				'border-radius' => false,
				'margin'        => false,
				'padding'       => false,
				'box-shadow'    => true,
				'selector'      => '{{WRAPPER}} .filter-items.ae-dropdown:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'collapse_filter_label_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-items.ae-dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'separator_label',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->add_control(
			'collapsed_item_heading',
			[
				'label'     => __( 'Collapsed Items', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'collapsed_sub_item_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .filter-items.ae-dropdown .filter-items',
			]
		);

		$this->start_controls_tabs( 'collapse_sub_item_styles' );

		$this->start_controls_tab( 'collapse_sub_item_normal', [ 'label' => __( 'Normal', 'ae-pro' ) ] );

		$this->style_model_controls(
			$this,
			[
				'name'          => 'collapse_filter_sub_item',
				'label'         => __( 'Sub-Item ', 'ae-pro' ),
				'typography'    => false,
				'color'         => true,
				'background'    => true,
				'border'        => true,
				'border-radius' => true,
				'margin'        => false,
				'padding'       => false,
				'box-shadow'    => true,
				'selector'      => '{{WRAPPER}} .filter-items.ae-dropdown .filter-items',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'collapse_sub_item_hover', [ 'label' => __( 'Hover', 'ae-pro' ) ] );

		$this->style_model_controls(
			$this,
			[
				'name'          => 'collapse_filter_sub_item_hover',
				'label'         => __( 'Sub-Item ', 'ae-pro' ),
				'typography'    => false,
				'color'         => true,
				'background'    => true,
				'border'        => true,
				'border-radius' => false,
				'margin'        => false,
				'padding'       => false,
				'box-shadow'    => true,
				'selector'      => '{{WRAPPER}} .filter-items.ae-dropdown .filter-items:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'collapse_filter_sub_item_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .filter-items.ae-dropdown .filter-items' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'collapse_filter_sub_item_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'    => 0,
					'right'  => 0,
					'bottom' => 5,
					'left'   => 0,
				],
				'selectors'  => [
					'{{WRAPPER}} .filter-items.ae-dropdown .filter-items' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filter_collapsed_text_align',
			[
				'label'     => __( 'Text Alignment', 'ae-pro' ),
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
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .filter-items.ae-dropdown .filter-items' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function style_model_controls( $widget, $args ) {

		$defaults = [
			'typography'    => false,
			'color'         => true,
			'background'    => true,
			'border'        => true,
			'border-radius' => true,
			'margin'        => true,
			'padding'       => true,
			'box-shadow'    => true,
			'selector'      => '',
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['typography'] ) {
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'name'     => $args['name'] . '_typography',
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'label'    => __( $args['label'] . 'Typography', 'ae-pro' ),
					'global'   => [
						'default' => Global_Typography::TYPOGRAPHY_TEXT,
					],
					'selector' => $args['selector'],
				]
			);
		}

		if ( $args['color'] ) {
			$this->add_control(
				$args['name'] . '_color',
				[
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'label'     => __( $args['label'] . 'Color', 'ae-pro' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => Global_Colors::COLOR_SECONDARY,
					'selectors' => [
						$args['selector'] . ', ' . $args['selector'] . ' a' => 'color: {{VALUE}};',
					],
				]
			);
		}

		if ( $args['background'] ) {
			$this->add_control(
				$args['name'] . '_bg',
				[
					//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					'label'     => __( $args['label'] . ' Background', 'ae-pro' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$args['selector'] => 'background-color: {{VALUE}}',
					],
				]
			);

			if ( $args['border'] ) {
				$widget->add_group_control(
					Group_Control_Border::get_type(),
					[
						'name'     => $args['name'] . '_border',
						//phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
						'label'    => __( $args['label'] . ' Border', 'ae-pro' ),
						'selector' => $args['selector'],
					]
				);
			}

			if ( $args['border-radius'] ) {
				$widget->add_control(
					$args['name'] . '_border_radius',
					[
						'label'      => __( 'Border Radius', 'ae-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%' ],
						'selectors'  => [
							$args['selector'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
			}

			if ( $args['box-shadow'] ) {
				$widget->add_group_control(
					Group_Control_Box_Shadow::get_type(),
					[
						'name'     => $args['name'] . '_box_shadow',
						'label'    => __( 'Box Shadow', 'ae-pro' ),
						'selector' => $args['selector'],
					]
				);
			}

			if ( $args['padding'] ) {
				$widget->add_control(
					$args['name'] . '_padding',
					[
						'label'      => __( 'Padding', 'ae-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%' ],
						'selectors'  => [
							$args['selector'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
			}

			if ( $args['margin'] ) {
				$widget->add_control(
					$args['name'] . '_margin',
					[
						'label'      => __( 'Margin', 'ae-pro' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%' ],
						'selectors'  => [
							$args['selector'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
			}
		}
	}

	public function sale_badge_controls() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->start_controls_section(
			'sale_badge_layout',
			[
				'label' => __( 'Woocommerce', 'ae-pro' ),
			]
		);

		$this->add_control(
			'show_featured',
			[
				'label'        => __( 'Featured Only', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'hide_out_of_stock',
			[
				'label'        => __( 'Hide Out of Stock', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'sale_badge_switcher',
			[
				'label'        => __( 'Enable Sales Badge', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'sale_badge_type',
			[
				'label'     => __( ' Type ', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   =>
					[
						'ribbon' => __( 'Ribbon', 'ae-pro' ),
						'badge'  => __( 'Badge', 'ae-pro' ),
						'image'  => __( 'Image', 'ae-pro' ),
					],
				'default'   => 'ribbon',
				'condition' => [
					'sale_badge_switcher' => 'yes',
				],
			]
		);

		$this->add_control(
			'sale_badge_source',
			[
				'label'     => __( ' Source ', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   =>
					[
						'default'      => __( 'Default', 'ae-pro' ),
						'custom_field' => __( 'Custom Field', 'ae-pro' ),
					],
				'default'   => 'default',
				'condition' => [
					'sale_badge_switcher' => 'yes',
				],
			]
		);

		$this->add_control(
			'sale_badge_title',
			[
				'label'       => __( 'Title', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Sale Badge Title', 'ae-pro' ),
				'default'     => __( 'Sale!', 'ae-pro' ),
				'description' => __( "Use shortcode '[ae_woo_product_discount_percentage]' to display discount percentage.", 'ae-pro' ),
				'condition'   => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'ribbon', 'badge' ],
					'sale_badge_source'   => [ 'default' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_custom_field_text',
			[
				'label'       => __( 'Custom Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field Name', 'ae-pro' ),
				'condition'   => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'ribbon', 'badge' ],
					'sale_badge_source'   => [ 'custom_field' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_custom_field_text_fallback',
			[
				'label'       => __( 'Fallback', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field Name', 'ae-pro' ),
				'condition'   => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'ribbon', 'badge' ],
					'sale_badge_source'   => [ 'custom_field' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_prefix',
			[
				'label'       => __( 'Prefix', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Prefix', 'ae-pro' ),
				'condition'   => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'ribbon', 'badge' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_suffix',
			[
				'label'       => __( 'Suffix', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Suffix', 'ae-pro' ),
				'condition'   => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'ribbon', 'badge' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_icon',
			[
				'label'     => __( 'Icon', 'ae-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'image' ],
					'sale_badge_source'   => [ 'default' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_custom_field_image',
			[
				'label'       => __( 'Custom Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field Name', 'ae-pro' ),
				'condition'   => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'image' ],
					'sale_badge_source'   => [ 'custom_field' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_custom_field_image_fallback',
			[
				'label'     => __( 'Fallback', 'ae-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'image' ],
					'sale_badge_source'   => [ 'custom_field' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_horizontal_position',
			[
				'label'        => __( 'Horizontal Position', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => true,
				'options'      => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],

				],
				'prefix_class' => 'badge-h-',
				'default'      => 'left',
				'condition'    => [
					'sale_badge_switcher' => 'yes',
				],
			]
		);

		$this->add_control(
			'sale_badge_vertical_position',
			[
				'label'        => __( 'Vertical Position', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => true,
				'options'      => [
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],

				],
				'prefix_class' => 'badge-v-',
				'default'      => 'top',
				'condition'    => [
					'sale_badge_switcher' => 'yes',
					'sale_badge_type'     => [ 'badge', 'image' ],
				],
			]
		);

		$this->end_controls_section();
	}
	public function sale_badge_styles() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$this->start_controls_section(
			'sale_badge_style',
			[
				'label'     => __( 'Sale Badge', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'sale_badge_switcher' => 'yes',
				],
			]
		);

		$this->add_control(
			'sale_badge_bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} span.onsale' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$ribbon_distance_transform = is_rtl() ? 'translateY(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)' : 'translateY(-50%) translateX(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)';

		$this->add_responsive_control(
			'sale_badge_distance',
			[
				'label'     => __( 'Distance', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} span.onsale' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .badge-type-ribbon .ae-sale-badge-wrapper span.onsale' => 'transform: ' . $ribbon_distance_transform,
				],
				'condition' => [
					'sale_badge_type' => 'ribbon',
				],
			]
		);

		$this->add_responsive_control(
			'sale_badge_size',
			[
				'label'          => __( 'Size', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ 'em', 'px' ],
				'default'        => [
					'unit' => 'em',
				],
				'tablet_default' => [
					'unit' => 'em',
				],
				'mobile_default' => [
					'unit' => 'em',
				],
				'range'          => [
					'em' => [
						'min'  => 0,
						'max'  => 25,
						'step' => 0.1,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} span.onsale' => 'min-height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}; line-height: {{SIZE}};',
				],
				'condition'      => [
					'sale_badge_type' => [ 'badge', 'image' ],
				],
			]
		);

		$this->add_control(
			'sale_badge_text_color',
			[
				'label'     => __( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .ae-sale-badge-wrapper span.onsale' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sale_badge_typography',
				'selector' => '{{WRAPPER}} span.onsale',
				'global'   => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
			]
		);

		Aepro::$_helper->box_model_controls(
			$this,
			[
				'name'          => 'badge_style',
				'label'         => __( 'Badge', 'ae-pro' ),
				'border'        => true,
				'border-radius' => true,
				'margin'        => true,
				'padding'       => true,
				'box-shadow'    => true,
				'selector'      => '{{WRAPPER}} .ae-sale-badge-wrapper  span.onsale',
			]
		);

		$this->end_controls_section();
	}
}
