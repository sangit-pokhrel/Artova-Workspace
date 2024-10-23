<?php

if ( function_exists( 'wpv_ae' ) ) {
    wpv_ae()->set_basename( true, __FILE__ );
} else {
    
    if ( !function_exists( 'wpv_ae' ) ) {
        // Create a helper function for easy SDK access.
        function wpv_ae()
        {
            
            if ( ! class_exists( 'wpv_ae_FsNull' ) ) {
                class wpv_ae_FsNull {

                    public function is_registered() {
                        return true;
                    }
                    public function has_api_connectivity() {
                        return true;
                    }

                    public function is_not_paying() {
                        return false;
                    }

                    public function can_use_premium_code__premium_only() {
                        return true;
                    }

                    public function add_filter() {
                        return;
                    }
                }
            }
            
            return new wpv_ae_FsNull();
        }
        
        // Init Freemius.
        wpv_ae();
        // Signal that SDK was initiated.
        do_action( 'wpv_ae_loaded' );
        wpv_ae()->add_filter( 'show_deactivation_subscription_cancellation', '__return_false' );
    }

}

// EDD License Migration Code
function aep_fs_license_key_migration()
{
    if ( !wpv_ae()->has_api_connectivity() || wpv_ae()->is_registered() ) {
        // No connectivity OR the user already opted-in to Freemius.
        //return;
    }
    if ( 'pending' != get_option( 'aep_fs_migrated2fs', 'pending' ) ) {
        return;
    }
    // Get the license key from the previous eCommerce platform's storage.
    $license_key = get_option( 'ae_pro_license_key', '' );
    if ( empty($license_key) ) {
        // No key to migrate.
        return;
    }
    // Get the first 32 characters.
    $license_key = substr( $license_key, 0, 32 );
    if ( strlen( $licence_key ) < 32 ) {
        $license_key = str_pad( $license_key, 32, '0' );
    }
    try {
        $next_page = wpv_ae()->activate_migrated_license( $license_key );
    } catch ( Exception $e ) {
        update_option( 'aep_fs_migrated2fs', 'unexpected_error' );
        return;
    }
    
    if ( wpv_ae()->can_use_premium_code() ) {
        update_option( 'aep_fs_migrated2fs', 'done' );
        if ( is_string( $next_page ) ) {
            fs_redirect( $next_page );
        }
    } else {
        update_option( 'aep_fs_migrated2fs', 'failed' );
    }

}

add_action( 'admin_init', 'aep_fs_license_key_migration' );
// Admin Notice for missing license
function aep_fs_missing_license()
{
    
    if ( wpv_ae()->is_not_paying() ) {
        $url = admin_url( 'plugins.php?activate-ae=1' );
        $upgrade_url = wpv_ae()->get_upgrade_url();
        ?>
	<div class="error aep-license-error">
		<p>
			<strong>AnyWhere Elementor Pro</strong><br />
			You license key is missing or invalid. Please <a href="<?php 
        echo  esc_attr( $url ) ;
        ?>">activate</a> your license.<br/>
			Don't have a license yet? <a href="<?php 
        echo  esc_attr( $upgrade_url ) ;
        ?>">Get it Now</a>
		</p>
	</div>
		<?php 
    }

}

add_action( 'admin_notices', 'aep_fs_missing_license' );
add_action( 'plugins_loaded', function () {
    add_filter( 'aepro/plan1_widgets/flag', '__return_false' );
    add_filter( 'aepro/plan2_widgets/flag', '__return_false' );
    Aepro\Plugin::$_level = 2;
} );