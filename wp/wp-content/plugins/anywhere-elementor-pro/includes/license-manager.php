<?php

namespace Aepro;

use Aepro\Classes\ModuleManager;

class License {


	private static $_instance;

	private static $_store_url = 'https://shop.webtechstreet.com';

	private static $_item_name = 'AnyWhere Elementor Pro';

	private static $_transient_lifetime = 43200;


	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'settings_menu' ] );

		add_action( 'admin_init', [ $this, 'register_license_option' ] );

		add_action( 'wp_ajax_ae_activate_license', [ $this, 'license_update' ] );
	}

	public function settings_menu() {
		add_submenu_page(
			'edit.php?post_type=ae_global_templates',
			__( 'Settings', 'ae-pro' ),
			__( 'Settings', 'ae-pro' ),
			'manage_options',
			'aepro-settings',
			[ $this, 'settings_page' ]
		);
	}

	public function settings_page() {
		$map_key = get_option( 'ae_pro_gmap_api' );

		$enable_generic = get_option( 'ae_pro_generic_theme' );

		$modules = Aepro::$module_manager->get_modules();

		?>
		<div class="aep-wrap">



			<div class="aep-content-wrapper">

				<div class="aep-settings-main-wrapper">

					<div class="aep-tabs tabs">
						<h3 class="aep-title aep-modules active">
							<a href="#" data-tabid="aep-module-manager">Modules</a>
						</h3>
						<h3 class="aep-title aep-config">
							<a href="#" data-tabid="aep-config">Configuration</a>
						</h3>
					</div>

					<div class="aep-settings-box aep-metabox">

						<div class="aep-metabox-content">

							<form class="aep-tab-content active" id="aep-module-manager" method="post">

								<div class="aep-bulk-action aep-module-row">
									<input type="checkbox" id="aep-select-all" />
									<select name="aep-bulk-action">
										<option value="">Bulk Action</option>
										<option value="activate">Activate</option>
										<option value="deactivate">Deactivate</option>
									</select>
									<input id="aep-apply" class="button" type="button" value="<?php echo esc_html__( 'Apply', 'ae-pro' ); ?>" />
								</div>


								<?php $this->core_modules( $modules['core']['modules'] ); ?>

								<?php $this->acf_modules( $modules['acf']['modules'] ); ?>

								<?php $this->pods_module( $modules['pods']['modules'] ); ?>

								<?php $this->woo_module( $modules['woo']['modules'] ); ?>

							</form>

							<form class="aep-tab-content" id="aep-config">

								<?php esc_html_e( 'Google Map Api Key', 'ae-pro' ); ?>
								<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<input type="text" name="ae_pro_gmap_api" id="ae_pro_gmap_api" class="regular-text" value="<?php echo $map_key; ?>">

								<br /><br />
								<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
									<?php echo esc_attr__( 'Click Here', 'ae-pro' ); ?>
								</a> to generate API KEY

								<br /><br />

								<button type="button" value="Save" class="button button-primary" name="save_config" id="save-config" data-action="save-config">
									<span class="aep-action-text">Save</span>
									<span class="aep-action-loading dashicons dashicons-update-alt"></span>
								</button>

							</form>

						</div>

					</div>

				</div>

				<div class="aep-settings-sidebar-wrapper">

					<?php $this->doc_box(); ?>

				</div>

			</div>

		</div>

		<?php
	}

	public function register_license_option() {
		// creates our settings in the options table
		register_setting( 'aepro_edd_license', 'ae_pro_gmap_api', [ $this, 'edd_sanitize_license' ] );
	}

	public function edd_sanitize_license( $new ) {
		return $new;
	}

	protected function license_status() {
		$licence_key = get_option( 'ae_pro_license_key' );
		if ( ! isset( $licence_key ) || empty( $licence_key ) ) {
			// license missing
			return 'missing';
		} else {
			// get transient
			$ae_license_transient = get_site_transient( 'aep_license_status' );
			//phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( isset( $ae_license_transient ) && $ae_license_transient != '' ) {
				return $ae_license_transient;
			}

			// check license status
			$license_status = $this->check_license();
			set_site_transient( 'aep_license_status', $license_status, self::$_transient_lifetime );

			return $license_status;
		}
	}

	protected function check_license() {
		$license = get_option( 'ae_pro_license_key' );

		$api_params = [
			'edd_action' => 'check_license',
			'license'    => $license,
			//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode
			'item_name'  => urlencode( self::$_item_name ),
			'url'        => home_url(),
		];

		// Call the custom API.
		$response = wp_remote_post(
			self::$_store_url,
			[
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'valid' ) {
			return 'valid';
		} else {
			return $license_data->license;
		}
	}

	public function license_update() {
		if ( isset( $_POST['aep_settings_update'] ) ) {
			if ( ! check_ajax_referer( 'aep_license_nonce', 'nonce' ) ) {
				return;
			}

			update_option( 'ae_pro_gmap_api', trim( $_POST['ae_pro_gmap_api'] ) );

			if ( isset( $_POST['enable_generic_theme_support'] ) ) {
				update_option( 'ae_pro_generic_theme', trim( $_POST['enable_generic_theme_support'] ) );
			} else {
				update_option( 'ae_pro_generic_theme', '' );
			}
		}
	}

	static function get_hidden_ae_license_key() {
		$input_string = trim( get_option( 'ae_pro_license_key' ) );

		$start  = 5;
		$length = mb_strlen( $input_string ) - $start - 5;

		$mask_string  = preg_replace( '/\S/', 'X', $input_string );
		$mask_string  = mb_substr( $mask_string, $start, $length );
		$input_string = substr_replace( $input_string, $mask_string, $start, $length );

		return $input_string;
	}

	function core_modules( $modules ) {
		?>
		<div class="aep-module-row aep-module-group">
			<h4 class="aep-group-title"><?php echo __( 'Core', 'aepro' ); ?></h4>
		</div>

		<?php
		foreach ( $modules as $module_key => $module ) {
			if ( isset( $module['hidden'] ) && $module['hidden'] ) {
				continue;
			}
			$class = 'aep-module-row';
			if ( $module['enabled'] === true ) {
				$class      .= ' aep-enabled';
				$action_text = __( 'Deactivate', 'aepro' );
				$action      = 'deactivate';
			} else {
				$class      .= ' aep-disabled';
				$action_text = __( 'Activate', 'aepro' );
				$action      = 'activate';
			}

			?>
			<div class="<?php echo $class; ?>">
				<input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
				<?php echo $module['label']; ?>

				<div class="aep-module-action">
					<a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a>
				</div>
			</div>
			<?php
		}
	}

	function acf_modules( $modules ) {

		$not_available = __( 'Not Available <a title="%s">[?]</a>', 'ae-pro' );
		?>

		<div class="aep-module-row aep-module-group">
			<h4 class="aep-group-title"><?php echo __( 'Advanced Custom Fields', 'aepro' ); ?></h4>
		</div>
		<?php
		foreach ( $modules as $module_key => $module ) {
			if ( isset( $module['hidden'] ) && $module['hidden'] ) {
				continue;
			}
			$class = 'aep-module-row';
			if ( $module['enabled'] === true ) {
				$class      .= ' aep-enabled';
				$action_text = __( 'Deactivate', 'aepro' );
				$action      = 'deactivate';
			} else {
				$class      .= ' aep-disabled';
				$action_text = __( 'Activate', 'aepro' );
				$action      = 'activate';
			}

			?>
			<div class="<?php echo $class; ?>">
				<input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
				<?php echo $module['label']; ?>

				<div class="aep-module-action">
					<?php
					if ( AE_ACF === false ) {
						echo sprintf( $not_available, $module['not-available'] );
					} else {
						if ( ( AE_ACF_PRO === false ) && in_array( $module_key, [ 'acf-gallery', 'acf-repeater', 'acf-flexible-content' ] ) ) {
							echo sprintf( $not_available, $module['not-available'] );
						} else {
							?>
					<a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a>
							<?php
						}
					}
					?>

				</div>
			</div>
			<?php
		}
	}

	function pods_module( $modules ) {

		$not_available = __( 'Not Available <a title="%s">[?]</a>', 'ae-pro' );
		?>

		<div class="aep-module-row aep-module-group">
			<h4 class="aep-group-title"><?php echo __( 'Pods', 'aepro' ); ?></h4>
		</div>
		<?php
		foreach ( $modules as $module_key => $module ) {

			$class = 'aep-module-row';
			if ( $module['enabled'] === true ) {
				$class      .= ' aep-enabled';
				$action_text = __( 'Deactivate', 'aepro' );
				$action      = 'deactivate';
			} else {
				$class      .= ' aep-disabled';
				$action_text = __( 'Activate', 'aepro' );
				$action      = 'activate';
			}

			?>
			<div class="<?php echo $class; ?>">
				<input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
				<?php echo $module['label']; ?>

				<div class="aep-module-action">
					<?php
					if ( AE_PODS === false ) {
						echo sprintf( $not_available, $module['not-available'] );
					} else {
						?>
					<a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a>
						<?php
					}
					?>

				</div>
			</div>
			<?php
		}
	}

	function woo_module( $modules ) {

		$not_available = __( 'Not Available <a title="%s">[?]</a>', 'ae-pro' );
		?>

		<div class="aep-module-row aep-module-group">
			<h4 class="aep-group-title"><?php echo __( 'WooCommerce', 'aepro' ); ?></h4>
		</div>
		<?php
		foreach ( $modules as $module_key => $module ) {
			if ( isset( $module['hidden'] ) && $module['hidden'] ) {
				continue;
			}
			$class = 'aep-module-row';
			if ( $module['enabled'] === true ) {
				$class      .= ' aep-enabled';
				$action_text = __( 'Deactivate', 'aepro' );
				$action      = 'deactivate';
			} else {
				$class      .= ' aep-disabled';
				$action_text = __( 'Activate', 'aepro' );
				$action      = 'activate';
			}

			?>
			<div class="<?php echo $class; ?>">
				<input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
				<?php echo $module['label']; ?>

				<div class="aep-module-action">
					<?php
					if ( AE_WOO === false ) {
						echo sprintf( $not_available, $module['not-available'] );
					} else {
						?>
					<a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a>
						<?php
					}
					?>

				</div>
			</div>
			<?php
		}
	}

	function misc_modules( $modules ) {

		$not_available = __( 'Not Available <a title="%s">[?]</a>', 'ae-pro' );
		?>

		<div class="aep-module-row aep-module-group">
			<h4 class="aep-group-title"><?php echo __( 'Miscellaneous', 'aepro' ); ?></h4>
		</div>

		<?php
		foreach ( $modules as $module_key => $module ) {

			$class = 'aep-module-row';
			if ( $module['enabled'] === true ) {
				$class      .= ' aep-enabled';
				$action_text = __( 'Deactivate', 'aepro' );
				$action      = 'deactivate';
			} else {
				$class      .= ' aep-disabled';
				$action_text = __( 'Activate', 'aepro' );
				$action      = 'activate';
			}

			?>
			<div class="<?php echo $class; ?>">
				<input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
				<?php echo $module['label']; ?>

				<div class="aep-module-action">
					<?php
					if ( AE_WOO === false ) {
						echo sprintf( $not_available, $module['not-available'] );
					} else {
						?>
					<a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a>
						<?php
					}
					?>

				</div>
			</div>
			<?php
		}
	}

	function doc_box() {
		?>

		<div class="aep-sidebar-box aep-metabox">
			<h3 class="aep-title">Getting Started</h3>
			<div class="aep-metabox-content">
				<ul>
					<li><a target="_blank" href="https://wpvibes.link/go/installation/">Activating License</a></li>
					<li><a target="_blank" href="https://wpvibes.link/go/how-to/">How To's</li>
				</ul>
				<a class="button button-primary ae-support" target="_blank" title="Get Support" href="https://wpvibes.link/go/ea-support/">
					Get Support
				</a>
			</div>
		</div>

		<?php
	}
}

License::instance();
