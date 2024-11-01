<?php
/**
 * Plugin Name:          WC Vendors Marketplace
 * Requires Plugins:     woocommerce
 * Plugin URI:           https://www.wcvendors.com
 * Description:          Create a marketplace with WooCommerce and allow vendors to sell their own products and receive a commission for each sale.
 * Author:               WC Vendors
 * Author URI:           https://www.wcvendors.com
 * GitHub Plugin URI:    https://github.com/wcvendors/wcvendors
 *
 * Version:              2.5.3
 * Requires at least:    5.3.0
 * Tested up to:         6.6
 * WC requires at least: 5.0
 * WC tested up to:      9.3
 *
 * Text Domain:          wc-vendors
 * Domain Path:          /languages/
 *
 * @category             Plugin
 * @copyright            Copyright © 2012 Matt Gates, Copyright © 2021 WC Vendors
 * @author               Matt Gates, WC Vendors
 * @package              WCVendors
 * @license              GPL2
 *
 * WC Vendors Marketplace is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WC Vendors Marketplace is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WC Vendors Marketplace. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * Declare HPOS Compatibility.
 *
 * @since 2.4.7
 */
add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( FeaturesUtil::class ) ) {
            FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
        }
    }
);

/**
 * Declare Cart and Checkout Blocks Compatibility.
 *
 * @since 2.4.8
 */
add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( FeaturesUtil::class ) ) {
            FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
        }
    }
);

/**
 * Required functions
 */
require_once trailingslashit( __DIR__ ) . 'classes/includes/class-functions.php';
require_once trailingslashit( __DIR__ ) . 'classes/class-activation.php';
require_once trailingslashit( __DIR__ ) . 'classes/class-deactivation.php';
register_activation_hook( __FILE__, array( 'WCV_Activate', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WCV_Deactivate', 'deactivate' ) );
add_action( 'wcvendors_notice_scheduled_action', 'wcv_trigger_admin_notice' );


/**
 * The main WC Vendors plugin class
 *
 * @version 2.4.8
 * @since   2.4.8
 */
class WC_Vendors {

    /**
     * The current version
     *
     * @var string
     * @version 2.4.8
     * @since   2.4.8
     */
    public $version = '';

    /**
     * Construct an instance of this class.
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    public function __construct() {
        $this->run();
    }

    /**
     * Run the plugin
     *
     * @return void
     * @version 2.4.8
     * @since   2.4.8
     */
    public function run() {

        /* Define an absolute path to our plugin directory. */
        if ( ! defined( 'WCV_PLUGIN_DIR' ) ) {
            define( 'WCV_PLUGIN_DIR', trailingslashit( __DIR__ ) );
        }
        if ( ! defined( 'WCV_ASSETS_URL' ) ) {
            define( 'WCV_ASSETS_URL', trailingslashit( plugins_url( 'assets', __FILE__ ) ) );
        }
        /**
         * Backwards compatibility with Pro 1.8.7.3 and below since we uppercase the constant on v2.4.8
         */
        if ( ! defined( 'wcv_assets_url' ) ) {
            define( 'wcv_assets_url', trailingslashit( plugins_url( 'assets', __FILE__ ) ) ); //phpcs:ignore
        }
        if ( ! defined( 'WCV_PLUGIN_BASE' ) ) {
            define( 'WCV_PLUGIN_BASE', plugin_basename( __FILE__ ) );
        }
        if ( ! defined( 'WCV_PLUGIN_DIR_PATH' ) ) {
            define( 'WCV_PLUGIN_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
        }

        if ( ! defined( 'WCV_VERSION' ) ) {
            define( 'WCV_VERSION', '2.5.3' );
        }

        if ( ! defined( 'WCV_TEMPLATE_BASE' ) ) {
            define( 'WCV_TEMPLATE_BASE', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
        }

        if ( ! defined( 'WCV_ABSPATH_ADMIN' ) ) {
            define( 'WCV_ABSPATH_ADMIN', __DIR__ . '/classes/admin/' );
        }

        if ( ! defined( 'WCV_PLUGIN_APPS_PATH' ) ) {
            define( 'WCV_PLUGIN_APPS_PATH', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'apps/' );
        }

        if ( ! defined( 'WCV_PLUGIN_FILE' ) ) {
            define( 'WCV_PLUGIN_FILE', __FILE__ );
        }

        $this->version = WCV_VERSION;
        $wcv_boot      = null;
        if ( maybe_load_new_dashboard() ) {
            require_once WCV_PLUGIN_DIR . 'classes/class-wc-vendors-bootstrap.php';
            $wcv_boot = new WC_Vendors_Bootstrap();
        } else {
            require_once WCV_PLUGIN_DIR . 'legacy/classes/class-wc-vendors-bootstrap-legacy.php';
            $wcv_boot = new WC_Vendors_Bootstrap_Legacy();
        }
    }

    /**
     * Logger so that we can keep our debug and logging information cleaner
     *
     * @since   2.0.0
     * @version 2.0.0
     * @access  public
     *
     * @param string $data   The data to go to the error log could be string, array or object.
     * @param string $prefix The prefix to add to the error log.
     */
    public static function log( $data = '', $prefix = '' ) {
      // phpcs:disable

      $trace  = debug_backtrace( false, 2 );
      $caller = ( isset( $trace[1]['class'] ) ) ? $trace[1]['class'] : basename( $trace[1]['file'] );

      if ( is_array( $data ) || is_object( $data ) ) {
        if ( $prefix ) {
          error_log( '===========================' );
          error_log( $prefix );
          error_log( '===========================' );
        }
        error_log( $caller . ' : ' . print_r( $data, true ) );
      } else {
        if ( $prefix ) {
          error_log( '===========================' );
          error_log( $prefix );
          error_log( '===========================' );
        }
        error_log( $caller . ' : ' . $data );
      }
      // phpcs:enable
    }
}

/**
 * Initialize the plugin
 *
 * @since 2.5.2
 */
function run_wcvendors() { //phpcs:ignore
    $GLOBALS['wc_vendors'] = new WC_Vendors();
}

add_action( 'setup_theme', 'run_wcvendors', 20 );

/**
 * Schedule the admin notice
 */
function wcv_schedule_admin_notice() {
    wcvendors_schedule_display_notice( 'cart_and_checkout', 0 );
    wcvendors_schedule_display_notice( 'review_request', 10 );
}
add_action( 'admin_init', 'wcv_schedule_admin_notice' );
