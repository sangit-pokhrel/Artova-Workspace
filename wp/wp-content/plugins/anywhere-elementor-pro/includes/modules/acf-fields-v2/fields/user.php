<?php
namespace Aepro\Modules\AcfFieldsV2\Fields;

use Elementor\Controls_Manager;
use Elementor\Plugin as EPlugin;
use Aepro\Aepro;
use Aepro\Classes\AcfMaster;
use Aepro\Modules\AcfFieldsV2\Fields\ACF_Choice;
use ElementorPro\Modules\DynamicTags\ACF\Tags\ACF_COLOR;

class ACF_User {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	//All Styles Controls are from ACF_Choice

	public static function render($widget, $settings) {
		

		$user_data_type = $settings['user_data_type'];
		$user_data = $widget->get_raw_acf_field_value($settings);

		if ( empty( $user_data ) ) {
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( $settings['enable_fallback'] != 'yes' ) {
				return;
			} else {
				$widget->render_fallback_content( $settings );
				return;
			}
		}

		$widget->add_render_attribute( 'wrapper', 'class', 'ae-acf-wrapper' );
		$widget->add_render_attribute( 'wrapper', 'class', 'ae-icon-list-items' );

		?>
		<ul <?php echo $widget->get_render_attribute_string( 'wrapper' ); ?>>
			<?php

			foreach($user_data_type as $value){

				switch($value){
					case 'avatar' :	$data = get_avatar( $user_data['ID'], 96 );
									continue;
					case 'first_name' : $data = $user_data['user_firstname'];
									continue;
					case 'last_name' : $data = $user_data['user_lastname'];
									continue;
					case 'first_last' :	$data = $user_data['display_name'];
									continue;
					case 'nickname' : $data = $user_data['user_nickname'];
									continue;
					case 'description' : $data = $user_data['user_description'];
									continue;
				}
				$widget->set_render_attribute( 'item_wrapper', 'class', 'ae-icon-list-item ae-user-' . $value );
				?>
				<li <?php echo $widget->get_render_attribute_string( 'item_wrapper' ); ?>>
					<div class="ae-icon-list-item-inner">
						<span class="ae-icon-list-text">
							<?php echo $data; ?>
						</span>
					</div>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}

	public static function register_controls($widget){

		$widget->add_control(
			'user_data_type',
			[
				'label'       => __( 'Data Type', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'placeholder' => __( 'Select', 'ae-pro' ),
				'default'     => 'avatar',
				'options' => [
					'avatar'      => __( 'Avatar', 'ae-pro' ),
					'first_name'  => __( 'First Name', 'ae-pro' ),
					'last_name'   => __( 'Last Name', 'ae-pro' ),
					'first_last'  => __( 'Full Name', 'ae-pro' ),
					'nickname'    => __( 'Nick Name', 'ae-pro' ),
					'description' => __( 'Biography', 'ae-pro' ),
				],
				'required' => true,
				'condition'   => [
					'field_type' => 'user',
				],
			]
		);

		$widget->add_control(
			'user_meta_order',
			[
				'label'     => __( 'Order', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
				'condition' => [
					'field_type' => 'user',
				],
			]
		);


		$widget->add_control(
			'avatar_order',
			[
				'label'       => __( 'Avatar', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'1' => __( '1', 'ae-pro' ),
					'2' => __( '2', 'ae-pro' ),
					'3' => __( '3', 'ae-pro' ),
					'4' => __( '4', 'ae-pro' ),
					'5' => __( '5', 'ae-pro' ),
					'6' => __( '6', 'ae-pro' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .ae-acf-wrapper .ae-user-avatar' => 'order: {{VALUE}}',
				],
				'condition'   => [
					'field_type' => 'user',
				],
			]
		);

		$widget->add_control(
			'first_name_order',
			[
				'label'       => __( 'First Name', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'1' => __( '1', 'ae-pro' ),
					'2' => __( '2', 'ae-pro' ),
					'3' => __( '3', 'ae-pro' ),
					'4' => __( '4', 'ae-pro' ),
					'5' => __( '5', 'ae-pro' ),
					'6' => __( '6', 'ae-pro' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .ae-acf-wrapper .ae-user-first_name' => 'order: {{VALUE}}',
				],
				'condition'   => [
					'field_type' => 'user',
				],
			]
		);

		$widget->add_control(
			'last_name_order',
			[
				'label'       => __( 'Last Name', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'1' => __( '1', 'ae-pro' ),
					'2' => __( '2', 'ae-pro' ),
					'3' => __( '3', 'ae-pro' ),
					'4' => __( '4', 'ae-pro' ),
					'5' => __( '5', 'ae-pro' ),
					'6' => __( '6', 'ae-pro' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .ae-acf-wrapper .ae-user-last_name' => 'order: {{VALUE}}',
				],
				'condition'   => [
					'field_type' => 'user',
				],
			]
		);

		$widget->add_control(
			'first_last_order',
			[
				'label'       => __( 'First & Last Name', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'1' => __( '1', 'ae-pro' ),
					'2' => __( '2', 'ae-pro' ),
					'3' => __( '3', 'ae-pro' ),
					'4' => __( '4', 'ae-pro' ),
					'5' => __( '5', 'ae-pro' ),
					'6' => __( '6', 'ae-pro' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .ae-acf-wrapper .ae-user-first_last' => 'order: {{VALUE}}',
				],
				'condition'   => [
					'field_type' => 'user',
				],
			]
		);

		$widget->add_control(
			'nickname_order',
			[
				'label'       => __( 'Nickname', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'1' => __( '1', 'ae-pro' ),
					'2' => __( '2', 'ae-pro' ),
					'3' => __( '3', 'ae-pro' ),
					'4' => __( '4', 'ae-pro' ),
					'5' => __( '5', 'ae-pro' ),
					'6' => __( '6', 'ae-pro' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .ae-acf-wrapper .ae-user-nickname' => 'order: {{VALUE}}',
				],
				'condition'   => [
					'field_type' => 'user',
				],
			]
		);

		$widget->add_control(
			'discription_order',
			[
				'label'       => __( 'Discription', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'1' => __( '1', 'ae-pro' ),
					'2' => __( '2', 'ae-pro' ),
					'3' => __( '3', 'ae-pro' ),
					'4' => __( '4', 'ae-pro' ),
					'5' => __( '5', 'ae-pro' ),
					'6' => __( '6', 'ae-pro' ),
				],
				'selectors'            => [
					'{{WRAPPER}} .ae-acf-wrapper .ae-user-discription' => 'order: {{VALUE}}',
				],
				'condition'   => [
					'field_type' => 'user',
				],
			]
		);

	}
}