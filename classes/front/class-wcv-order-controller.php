<?php
namespace WC_Vendors\Classes\Front;

/**
 * The WC Vendors order Controller class
 *
 * This is the order controller class for all front end order management
 *
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCV_Order_Controller {

    /**
     * Instance of this class
     *
     * @var WCV_Order_Controller
     * @version 2.5.2
     * @since   2.5.2
     */
    public static $instance = null;

    /**
     * The ID of this plugin.
     *
     * @since    2.5.2
     * @access   private
     * @var      string $wcvendors The ID of this plugin.
     */
    private $wcvendors;

    /**
     * The version of this plugin.
     *
     * @since    2.5.2
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Is the plugin in debug mode
     *
     * @since    2.5.2
     * @access   private
     * @var      bool $debug plugin is in debug mode
     */
    private $debug;

    /**
     * Is the plugin base directory
     *
     * @since    2.5.2
     * @access   private
     * @var      string $base_dir string path for the plugin directory
     */
    private $base_dir;

    /**
     * The tables header rows
     *
     * @since    2.5.2
     * @access   private
     * @var      array $columns The table columns
     */
    private $columns;

    /**
     * The table rows
     *
     * @since    2.5.2
     * @access   private
     * @var      array $rows The table rows
     */
    private $rows;

    /**
     * The ID of this plugin.
     *
     * @since    2.5.2
     * @access   private
     * @var      string $wcvendors The ID of this plugin.
     */
    private $controller_type;

    /**
     * Billing fields.
     *
     * @var array
     * @version 2.5.2
     */
    private static $billing_fields;

    /**
     * Shipping fields.
     *
     * @var array
     * @version 2.5.2
     */
    private static $shipping_fields;

    /**
     * Default start date.
     *
     * @var string
     * @version 2.5.2
     */
    private $default_start;

    /**
     * Max number of pages for pagination
     *
     * @since    2.5.2
     * @access   public
     * @var      int $max_num_pages integer for max number of pages for the query
     */
    public $max_num_pages;

    /**
     * Total number of orders for the query
     *
     * @since    2.5.2
     * @access   public
     * @var      int $total_order_count integer for total number of orders for the query
     */
    public $total_order_count;

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.5.2
     *
     * @param      string $wcvendors The name of the plugin.
     * @param      string $version       The version of this plugin.
     * @param   bool   $debug         Whether debug is enabled or not.
     */
    public static function get_instance( $wcvendors, $version, $debug ) {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self( $wcvendors, $version, $debug );
        }

        return self::$instance;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since    2.5.2
     *
     * @param      string $wcvendors The name of the plugin.
     * @param      string $version       The version of this plugin.
     * @param   bool   $debug         Whether debug is enabled or not.
     */
    public function __construct( $wcvendors, $version, $debug ) {

        $this->wcvendors       = $wcvendors;
        $this->version         = $version;
        $this->debug           = $debug;
        $this->base_dir        = plugin_dir_path( WCV_PLUGIN_FILE );
        $this->controller_type = 'order';

        $orders_sales_range  = get_option( 'wcvendors_orders_sales_range', 'monthly' );
        $this->default_start = '';

        switch ( $orders_sales_range ) {
            case 'annually':
                $this->default_start = '-1 year';
                break;
            case 'quarterly':
                $this->default_start = '-3 month';
                break;
            case 'monthly':
                $this->default_start = '-1 month';
                break;
            case 'weekly':
                $this->default_start = '-1 week';
                break;
            case 'daily':
                $this->default_start = '-1 day';
                break;
            case 'custom':
                $this->default_start = '-1 year';
                break;
            default:
                $this->default_start = '-1 month';
                break;
        }

        $this->default_start = apply_filters( 'wcv_default_order_start_date', $this->default_start );

        self::$billing_fields = apply_filters(
            'wcv_order_billing_fields',
            array(
                'first_name' => array(
                    'label' => __( 'First Name', 'wc-vendors' ),
                    'show'  => false,
                ),
                'last_name'  => array(
                    'label' => __( 'Last Name', 'wc-vendors' ),
                    'show'  => false,
                ),
                'company'    => array(
                    'label' => __( 'Company', 'wc-vendors' ),
                    'show'  => false,
                ),
                'address_1'  => array(
                    'label' => __( 'Address 1', 'wc-vendors' ),
                    'show'  => false,
                ),
                'address_2'  => array(
                    'label' => __( 'Address 2', 'wc-vendors' ),
                    'show'  => false,
                ),
                'city'       => array(
                    'label' => __( 'City', 'wc-vendors' ),
                    'show'  => false,
                ),
                'postcode'   => array(
                    'label' => __( 'Postcode', 'wc-vendors' ),
                    'show'  => false,
                ),
                'country'    => array(
                    'label'   => __( 'Country', 'wc-vendors' ),
                    'show'    => false,
                    'class'   => 'js_field-country select short',
                    'type'    => 'select',
                    'options' => array_merge( array( '' => __( 'Select a country&hellip;', 'wc-vendors' ) ), WCV_Form_Helper::countries() ),
                ),
                'state'      => array(
                    'label' => __( 'State/County', 'wc-vendors' ),
                    'class' => 'js_field-state select short',
                    'show'  => false,
                ),
                'email'      => array(
                    'label' => __( 'Email', 'wc-vendors' ),
                ),
                'phone'      => array(
                    'label' => __( 'Phone', 'wc-vendors' ),
                ),
            )
        );

        self::$shipping_fields = apply_filters(
            'wcv_order_shipping_fields',
            array(
                'first_name' => array(
                    'label' => __( 'First name', 'wc-vendors' ),
                    'show'  => false,
                ),
                'last_name'  => array(
                    'label' => __( 'Last name', 'wc-vendors' ),
                    'show'  => false,
                ),
                'company'    => array(
                    'label' => __( 'Company', 'wc-vendors' ),
                    'show'  => false,
                ),
                'address_1'  => array(
                    'label' => __( 'Address 1', 'wc-vendors' ),
                    'show'  => false,
                ),
                'address_2'  => array(
                    'label' => __( 'Address 2', 'wc-vendors' ),
                    'show'  => false,
                ),
                'city'       => array(
                    'label' => __( 'City', 'wc-vendors' ),
                    'show'  => false,
                ),
                'postcode'   => array(
                    'label' => __( 'Postcode', 'wc-vendors' ),
                    'show'  => false,
                ),
                'country'    => array(
                    'label'   => __( 'Country', 'wc-vendors' ),
                    'show'    => false,
                    'type'    => 'select',
                    'class'   => 'js_field-country select short',
                    'options' => array( '' => __( 'Select a country&hellip;', 'wc-vendors' ) ) + WCV_Form_Helper::countries(),
                ),
                'state'      => array(
                    'label' => __( 'State/County', 'wc-vendors' ),
                    'class' => 'js_field-state select short',
                    'show'  => false,
                ),
            )
        );
    }

    /**
     * Display the custom order table
     *
     * @since    2.5.2
     */
    public function display() {

        // Use the internal table generator to create object list.
        $order_table = new WCV_Table_Helper( $this->wcvendors, $this->version, $this->controller_type, null, get_current_user_id() );

        $order_table->set_columns( $this->table_columns() );
        if ( empty( $order_table->get_rows() ) ) {
            $order_table->set_rows( $this->table_rows() );
        }

        // display the table.
        $order_table->display();
    }

    /**
     *  Process the form submission from the front end.
     *
     * @since   2.5.2
     * @since   2.5.2 - Added extra checks.
     */
    public function process_submit() {

        if ( isset( $_GET['wcv_mark_shipped'] ) ) {

            if ( ! \WCV_Vendor_Dashboard::check_object_permission( 'order', absint( $_GET['wcv_mark_shipped'] ) ) ) { // phpcs:ignore
                return false;
            }

            $vendor_id = get_current_user_id();
            $order_id  = $_GET['wcv_mark_shipped']; // phpcs:ignore

            self::mark_shipped( $vendor_id, $order_id );
        }

        if ( isset( $_GET['wcv_shipping_label'] ) ) {

            $vendor_id = get_current_user_id();
            $order_id  = absint( $_GET['wcv_shipping_label'] );

            if ( ! \WCV_Vendor_Dashboard::check_object_permission( 'order', $order_id ) ) {
                return false;
            }

            self::shipping_label( $vendor_id, $order_id );
        }

        if ( isset( $_GET['wcv_export_orders'] ) ) {

            $vendor_id = get_current_user_id();
            $this->export_csv();
        }

        if ( isset( $_POST['wcv_order_id'] ) && isset( $_POST['wcv_add_note'] ) ) {

            if ( ! wp_verify_nonce( $_POST['wcv_add_note'], 'wcv-add-note' ) ) {
                return false;
            }

            $order_id = (int) $_POST['wcv_order_id'];
            $comment  = $_POST['wcv_comment_text'];

            if ( ! \WCV_Vendor_Dashboard::check_object_permission( 'order', absint( $order_id ) ) ) {
                return false;
            }

            if ( empty( $comment ) ) {
                wc_add_notice( __( 'You need type something in the note field', 'wc-vendors' ), 'error' );

                return false;
            }

            $this->add_order_note( $order_id, $comment );
        }

        if ( isset( $_POST['wcv_add_tracking_number_nonce'] ) ) {
            if ( ! \WCV_Vendor_Dashboard::check_object_permission( 'order', absint( $_POST['_wcv_order_id'] ) ) ) {
                return false;
            }

            self::update_shipment_tracking();
        }

        // Process the date updates for the form.
        if ( isset( $_POST['wcv_order_date_update'] ) ) {

            if ( ! wp_verify_nonce( $_POST['wcv_order_date_update'], 'wcv-order-date-update' ) ) {
                return;
            }

            $update_button = isset( $_POST['update_button'] ) ? $_POST['update_button'] : '';

            // Start Date.
            if ( isset( $_POST['_wcv_order_start_date_input'] ) || '' === $_POST['_wcv_order_start_date_input'] ) {
                WC()->session->set( 'wcv_order_start_date', strtotime( $_POST['_wcv_order_start_date_input'] ) );
            }

            // End Date.
            if ( isset( $_POST['_wcv_order_end_date_input'] ) || '' === $_POST['_wcv_order_end_date_input'] ) {
                WC()->session->set( 'wcv_order_end_date', strtotime( $_POST['_wcv_order_end_date_input'] ) );
            }

            // Order status.
            if ( isset( $_POST['_wcv_order_status_input'] ) && '' !== $_POST['_wcv_order_status_input'] && '' !== $update_button ) {
                WC()->session->set( 'wcv_order_filter_status', $_POST['_wcv_order_status_input'] );
            } else {
                WC()->session->set( 'wcv_order_filter_status', '' );
            }
        }

        do_action( 'wcvendors_orders_process_submit' );
    }

    /**
     *  Process the delete action
     *
     * @since    2.5.2
     */
    public function process_delete() {}

    /**
     *  Update Table Headers for display.
     *
     * @since 2.5.2
     *
     * @return array $headers array passed via filter.
     */
    public function table_columns() {

        $columns = apply_filters(
            'wcv_order_table_columns',
            array(
                'ID'           => __( 'ID', 'wc-vendors' ),
                'order_number' => __( 'Order', 'wc-vendors' ),
                'customer'     => __( 'Customer', 'wc-vendors' ),
                'products'     => __( 'Products', 'wc-vendors' ),
                'total'        => __( 'Total', 'wc-vendors' ),
                'status'       => __( 'Shipped', 'wc-vendors' ),
                'order_date'   => __( 'Order Date', 'wc-vendors' ),
            )
        );

        return $columns;
    }

    /**
     * Create the table data
     *
     * @since    2.5.2
     * @version  1.7.9
     * @return   array  $new_rows   array of stdClass objects passed back to the filter.
     */
    public function table_rows() {

        $date_range = array(
            'before' => gmdate( 'Y-m-d', $this->get_end_date() ),
            'after'  => gmdate( 'Y-m-d', $this->get_start_date() ),
        );

        /**
         * By default reversed commissions are not shown on the vendor order table
         *
         * Changing this to false will show refunded orders no matter which payment gateway is used.
         *
         * @since 1.7.10
         */
        $show_refunded_orders = wcv_is_show_reversed_order() ? true : false;

        $_all_orders             = WCV_Vendor_Controller::get_orders2( get_current_user_id(), $date_range, $show_refunded_orders, true );
        $this->max_num_pages     = $_all_orders['max_pages'];
        $this->total_order_count = $_all_orders['total_order_count'];
        $rows                    = array();
        $all_orders              = $_all_orders['total_orders'];

        if ( ! empty( $all_orders ) ) {

            foreach ( $all_orders as $order_row ) {

                $vendor_order = $order_row->order;

                $parent_order = $vendor_order->get_parent_order();

                if ( ! $parent_order ) {
                    continue;
                }

                $products_html           = '';
                $needs_shipping          = false;
                $downloadable            = false;
                $refunded_status         = '';
                $item_tax_refunded_total = 0;
                $refunded_total          = 0;
                $row_order_items         = isset( $order_row->order_items ) && is_array( $order_row->order_items ) ? $order_row->order_items : array();

                if ( ! empty( $row_order_items ) ) {

                    $refunded_count = count( $parent_order->get_refunds() );
                    $order_total    = 0;

                    foreach ( $row_order_items as $item ) {

                        $product_id     = $item->get_product_id();
                        $item_id        = $item->get_id();
                        $product        = $item->get_product();
                        $needs_shipping = $product_id ? $product->needs_shipping() : false;
                        $order_total   += $vendor_order->get_item_total( $item, false, true );
                        $item_name      = sprintf( '%s x %s ', $item->get_quantity(), $item->get_name() );

                        if ( $refunded_count > 0 ) {
                            $item_refunded_total = $parent_order->get_total_refunded_for_item( $item_id );

                            if ( absint( $item_refunded_total ) > 0 ) {
                                $item_name = sprintf( '<del>%s</del>', $item_name );
                                $item_tax  = wc_tax_enabled() ? $item->get_taxes() : false;
                                if ( $item_tax && $order_row->total_tax > 0 ) {
                                    foreach ( $parent_order->get_taxes() as $tax ) {
                                        $tax_id                  = $tax['rate_id'];
                                        $item_tax_refunded_total = $parent_order->get_tax_refunded_for_item( $item_id, $tax_id );
                                    }
                                }

                                $refunded_total += absint( $item_refunded_total ) + absint( $item_tax_refunded_total );
                            }
                        }

                        if ( null === $downloadable ) {
                            $downloadable = 0;
                        }

                        $products_html .= '<strong>' . $item_name . '</strong><br />';

                        if ( $product_id && $product->get_sku() ) {
                            $products_html .= sprintf(
                                // translators: %1$s: Product SKU, %2$s: HTML Line break.
                                __( 'SKU: %1$s %2$s', 'wc-vendors' ),
                                $product->get_sku(),
                                '<br />'
                            );
                        }
                        $products_html .= wc_display_item_meta( $item, array( 'echo' => false ) );
                        $products_html  = apply_filters( 'wcv_orders_order_item_meta_end', $products_html, $item->get_id(), $item, $vendor_order );

                    }

                    $total_shipping_refunded = $this->get_refunded_shipping_total( $parent_order );
                    $refunded_total         += $total_shipping_refunded;

                    if ( $refunded_total > 0 ) {

                        if ( $refunded_total < $order_total ) {
                            $refunded_status = '<span class="wcv-order-refunded"><strong>' . __( 'Partially Refunded', 'wc-vendors' ) . '</strong></span>';
                        } else {
                            $refunded_status = '<span class="wcv-order-refunded"><strong>' . __( 'Refunded', 'wc-vendors' ) . '</strong></span>';
                        }
                    }
                }

                $shippers    = $parent_order ? array_filter( (array) $parent_order->get_meta( 'wc_pv_shipped' ) ) : array();
                $has_shipped = in_array( get_current_user_id(), $shippers, true ) ? __( 'Yes', 'wc-vendors' ) : __( 'No', 'wc-vendors' );
                $shipped     = ( $needs_shipping ) ? $has_shipped : __( 'NA', 'wc-vendors' );

                $parent_order_number = $parent_order->get_order_number();

                $row_actions = apply_filters(
                    'wcv_orders_row_actions_' . $parent_order_number,
                    array(
                        'view_details' =>
                            array(
                                'label'  => __( 'View Order Details', 'wc-vendors' ),
                                'url'    => '#',
                                'custom' => array(
                                    'id'            => 'open-order-details-modal-' . $parent_order_number,
                                    'data-order-id' => $parent_order_number,
                                ),
                            ),
                        'print_label'  =>
                        array(
                            'label'  => __( 'Shipping label', 'wc-vendors' ),
                            'url'    => '?wcv_shipping_label=' . $parent_order_number,
                            'target' => '_blank',
                            'custom' => array( 'data-order-id' => $parent_order_number ),
                        ),
                        'add_note'     =>
                        array(
                            'label'  => __( 'Order note', 'wc-vendors' ),
                            'url'    => '#',
                            'custom' => array(
                                'id'            => 'open-order-note-modal-' . $parent_order_number,
                                'data-order-id' => $parent_order_number,
                            ),
                        ),
                        'add_tracking' =>
                            array(
                                'label'  => __( 'Tracking number', 'wc-vendors' ),
                                'url'    => '#',
                                'custom' => array(
                                    'id'            => 'open-tracking-modal-' . $parent_order_number,
                                    'data-order-id' => $parent_order_number,
                                ),
                            ),

                    ),
                    $parent_order_number
                );

                if ( ! $needs_shipping ) {
                    if ( isset( $row_actions['print_label'] ) ) {
                        unset( $row_actions['print_label'] );
                    }
                    if ( isset( $row_actions['add_tracking'] ) ) {
                        unset( $row_actions['add_tracking'] );
                    }
                }

                if ( ! is_wcv_pro_active() ) {
                    if ( isset( $row_actions['print_label'] ) ) {
                        unset( $row_actions['print_label'] );
                    }
                }

                // If it hasn't been shipped then provide a link to mark as shipped.
                if ( __( 'No', 'wc-vendors' ) === $shipped ) {
                    $row_actions['mark_shipped'] = array(
                        'label'  => __( 'Mark shipped', 'wc-vendors' ),
                        'url'    => '?wcv_mark_shipped=' . $parent_order->get_id(),
                        'custom' => array(
                            'class' => 'mark-order-shipped',
                        ),
                    );
                }

                // If the order is any of the following status, remove order actions.
                if ( in_array(
                    $vendor_order->get_status(),
                    apply_filters(
                        'wcv_order_status_action_hide',
                        array(
                            'refunded',
                            'cancelled',
                        )
                    ),
                    true
                ) ) {
                    if ( isset( $row_actions['print_label'] ) ) {
                        unset( $row_actions['print_label'] );
                    }
                    if ( isset( $row_actions['add_note'] ) ) {
                        unset( $row_actions['add_note'] );
                    }
                    if ( isset( $row_actions['add_tracking'] ) ) {
                        unset( $row_actions['add_tracking'] );
                    }
                    if ( isset( $row_actions['mark_shipped'] ) ) {
                        unset( $row_actions['mark_shipped'] );
                    }
                }

                $row_actions = apply_filters( 'wcv_order_row_actions', $row_actions, $parent_order->get_order_number() );

                $hide_view_details    = wc_string_to_bool( get_option( 'wcvendors_hide_order_view_details', 'no' ) );
                $hide_shipping_label  = wc_string_to_bool( get_option( 'wcvendors_hide_order_shipping_label', 'no' ) );
                $hide_tracking_number = wc_string_to_bool( get_option( 'wcvendors_hide_order_tracking_number', 'no' ) );
                $hide_mark_shipped    = wc_string_to_bool( get_option( 'wcvendors_hide_order_mark_shipped', 'no' ) );
                $order_currency       = $vendor_order->get_currency();

                $allow_update_order_note = wc_string_to_bool( get_option( 'wcvendors_capability_order_update_notes', 'no' ) );

                if ( $hide_view_details && array_key_exists( 'view_details', $row_actions ) ) {
                    unset( $row_actions['view_details'] );
                }
                if ( $hide_shipping_label && array_key_exists( 'print_label', $row_actions ) ) {
                    unset( $row_actions['print_label'] );
                }
                if ( ! $allow_update_order_note && array_key_exists( 'add_note', $row_actions ) ) {
                    unset( $row_actions['add_note'] );
                }
                if ( $hide_tracking_number && array_key_exists( 'add_tracking', $row_actions ) ) {
                    unset( $row_actions['add_tracking'] );
                }
                if ( $hide_mark_shipped && array_key_exists( 'mark_shipped', $row_actions ) ) {
                    unset( $row_actions['mark_shipped'] );
                }
                $refund_text    = '';
                $commission_due = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order_currency ), number_format( $order_row->total_due, 2 ) );
                $shipping_due   = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order_currency ), number_format( $order_row->total_shipping, 2 ) );
                $tax_due        = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order_currency ), number_format( $order_row->total_tax, 2 ) );
                $commission     = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order_currency ), number_format( $order_row->commission_total, 2 ) );
                $product_price  = sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order_currency ), number_format( $order_row->total - $order_row->total_shipping, 2 ) );
                $price_total    = wc_price( $order_row->total );
                if ( $refunded_total > 0 ) {
                    $refund_text = __( 'Refunded: ', 'wc-vendors' ) . sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol( $order_currency ), number_format( $refunded_total, 2 ) );
                    $price_total = sprintf( '<del>%s</del> %s', wc_price( $order_row->total ), wc_price( $order_row->total - $refunded_total ) );
                }

                $total_text = '<span class="wcv-tooltip" data-tip-text="' . sprintf( "%s %s\n %s %s\n %s %s\n %s %s\n %s %s \n %s", __( 'Full Commission: ', 'wc-vendors' ), $commission, __( 'Commission Due: ', 'wc-vendors' ), $commission_due, __( 'Product: ', 'wc-vendors' ), $product_price, __( 'Shipping: ', 'wc-vendors' ), $shipping_due, __( 'Tax: ', 'wc-vendors' ), $tax_due, $refund_text ) . '">' . $price_total . '</span>';

                $new_row = new \stdClass();

                $can_view_emails   = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_email', 'no' ) );
                $hide_phone        = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_phone', 'no' ) );
                $override_shipping = wc_string_to_bool( get_option( 'wcvendors_orders_override_empty_shipping', 'no' ) );
                $customer_address  = get_option( 'wcvendors_order_customer_address', 'shipping' );
                $customer_details  = '';

                switch ( $customer_address ) {
                    case 'billing':
                        $customer_details = $parent_order->get_formatted_billing_address();
                        break;

                    default:
                        $customer_details = $parent_order->get_formatted_shipping_address();
                        break;
                }

                if ( 'shipping' === $customer_address && '' === $customer_details && $override_shipping ) {
                    $customer_details = $parent_order->get_formatted_billing_address();
                }

                if ( $can_view_emails ) {
                    $billing_email = $parent_order->get_billing_email();
                    if ( ! empty( $customer_details ) ) {
                        $customer_details .= '<br />';
                    }
                    $customer_details .= $billing_email . '<br />';
                }

                if ( $hide_phone ) {
                    $billing_phone     = $parent_order->get_billing_phone();
                    $customer_details .= $billing_phone;
                }

                $order_date = $vendor_order->get_date_created();

                $order_details = sprintf(
                    '
                <div class="order_id">#%s</div>
                <div class="product_list wcv_mobile">%s</div>
                <div class="order_total wcv_mobile">%s</div>
                ',
                    $parent_order->get_order_number(),
                    $products_html,
                    sprintf(
                        // translators: %s Text displaying total.
                        __( 'Total: %s', 'wc-vendors' ),
                        $total_text
                    )
                );

                $new_row->ID           = $parent_order->get_order_number();
                $new_row->order_number = $order_details;
                $new_row->customer     = $customer_details;
                $new_row->products     = $products_html;
                $new_row->total        = $total_text;
                $new_row->status       = $shipped;

                $new_row->order_date = date_i18n(
                    get_option( 'date_format', 'F j, Y' ),
                    $order_date->getOffsetTimestamp()
                );

                if ( $refunded_total > 0 ) {
                    $new_row->order_date .= '</strong><br />' . $refunded_status;
                } else {
                    $new_row->order_date .= '<br /></strong><strong>' . ucfirst( wc_get_order_status_name( $parent_order->get_status() ) ) . '</strong>';
                }

                $new_row->row_actions = $row_actions;

                $new_row->action_after  = $this->order_details_template( $order_row );
                $new_row->action_after .= $this->order_note_template( $parent_order );
                $new_row->action_after .= $this->tracking_number_template( $parent_order, get_current_user_id() );

                do_action_deprecated(
                    'wcv_orders_add_new_row',
                    array( $new_row, $order_row, $parent_order, $row_order_items ),
                    '1.7.10',
                    'wcvendors_orders_add_new_row'
                );

                do_action(
                    'wcvendors_orders_add_new_row',
                    $new_row,
                    $order_row,
                    $parent_order,
                    $row_order_items
                );

                $rows[] = $new_row;

            }
        }

        return apply_filters( 'wcv_orders_table_rows', $rows );
    }

    /**
     *  Change the column that actions are displayed in
     *
     * @since    2.5.2
     *
     * @param     string $column The column passed from filter.
     *
     * @return   string $column New column passed back to filter.
     */
    public function table_action_column( $column ) {

        $column = 'order_date';

        return $column;
    }

    /**
     *  Add actions before and after the table
     *
     * @since    2.5.2
     */
    public function table_actions() {

        $can_export_csv = wc_string_to_bool( get_option( 'wcvendors_capability_orders_export', 'no' ) );
        $add_url        = '?wcv_export_orders';

        $search             = isset( $_POST['wcv-search'] ) ? wp_unslash( sanitize_text_field( $_POST['wcv-search'] ) ) : ''; // phpcs:ignore
        $pagination_wrapper = apply_filters(
            'wcv_order_paginate_wrapper',
            array(
                'wrapper_start' => '<nav class="woocommerce-pagination">',
                'wrapper_end'   => '</nav>',
            )
        );

        include wcv_deprecated_filter( 'wcvendors_pro_table_actions_path', '2.5.2', 'wcvendors_table_actions_path', 'partials/order/wcvendors-order-table-actions.php' );
    }

    /**
     *  Change the notice that actions are displayed in
     *
     * @since    2.5.2
     *
     * @param string $notice Notice passed from filter.
     *
     * @return string $notice Notice passed back to filter.
     */
    public function table_no_data_notice( $notice ) {

        $notice = apply_filters( 'wcv_orders_table_no_data_notice', __( 'No orders found.', 'wc-vendors' ) );

        return $notice;
    }

    /**
     *  Get the store id of the vendor
     *
     * @version  2.5.2
     * @since    2.5.2
     *
     * @param     int $vendor_id which vendor is being mark shipped.
     * @param     int $order_id  which order is being marked shipped.
     */
    public static function mark_shipped( $vendor_id, $order_id ) {

        $order = wc_get_order( $order_id );

        $store_name    = \WCV_Vendors::get_vendor_shop_name( $vendor_id );
        $shippers      = array_filter( (array) $order->get_meta( 'wc_pv_shipped' ) );
        $auto_complete = wc_string_to_bool( get_option( 'wcvendors_auto_complete_order_shipped', 'no' ) );

        if ( ! in_array( $vendor_id, $shippers, true ) ) {

            $shippers[] = $vendor_id;
            $user_id    = $order->get_user_id();
            if ( ! empty( $mails ) ) {
                WC()->mailer()->emails['WC_Email_Notify_Shipped']->trigger( $order_id, $user_id );
            }

            $order->update_meta_data( 'wc_pv_shipped', $shippers );
            $order->save();

            do_action( 'wcvendors_vendor_ship', $order_id, $vendor_id, $order );

            wc_add_notice( __( 'Order marked shipped.', 'wc-vendors' ), 'success' );
        }

        if ( $auto_complete ) {
            do_action( 'wcvendors_mark_shipped', $order_id, $shippers, $vendor_id );
        }
    }

    /**
     * Disable vendor shipped customer notification.
     *
     * @param bool $enabled Whether vendor notification is enabled or not.
     * @return bool
     * @version 2.5.2
     * @since   2.5.2
     * @deprecated 1.7.9
     */
    public function disable_notify_shipped( $enabled ) {
        $enabled = apply_filters( 'wcvendors_vendor_shipped_customer_notification_enabled', false );
        return $enabled;
    }

    /**
     *  Get the store id of the vendor
     *
     * @since    2.5.2
     *
     * @param     int $vendor_id which vendor is being mark shipped.
     * @param     int $order_id  which order is being marked shipped.
     */
    public static function shipping_label( $vendor_id, $order_id ) {

        do_action( 'wcvendors_shipping_label', $vendor_id, $order_id );
    }

    /**
     *  Add an order note
     *
     * @since    2.5.2
     *
     * @param int    $order_id The order ID.
     * @param string $comment The comment to add as order note.
     */
    public function add_order_note( $order_id, $comment ) {
        $order = wc_get_order( $order_id );

        if ( is_a( $order, 'WC_Order' ) ) {
            add_filter( 'woocommerce_new_order_note_data', array( $this, 'filter_order_note' ), 10 );
            $order->add_order_note( apply_filters( 'wcv_add_order_note', $comment, $order_id ), 1 );
            remove_filter( 'woocommerce_new_order_note_data', array( $this, 'filter_order_note' ), 10 );
            wc_add_notice( __( 'The customer has been notified.', 'wc-vendors' ), 'success' );
        }
    }

    /**
     *  Filter the order note
     *
     * @since    2.5.2
     *
     * @param     array $comment_data Comment data.
     */
    public function filter_order_note( $comment_data ) {
        $user_id = get_current_user_id();

        $comment_data['user_id']              = $user_id;
        $comment_data['comment_author']       = \WCV_Vendors::get_vendor_shop_name( $user_id );
        $comment_data['post_author']          = $user_id;
        $comment_data['comment_author_url']   = \WCV_Vendors::get_vendor_shop_page( $user_id );
        $comment_data['comment_author_email'] = wp_get_current_user()->user_email;

        return $comment_data;
    }

    /**
     *  Order Note Template
     *
     * @since    2.5.2
     * @version  2.5.2
     *
     * @param     WC_Order $order order to reference the notes.
     */
    public function order_note_template( $order ) {

        $can_add_comments = get_option( 'wcvendors_capability_order_update_notes', 'no' );

        $form = '';

        if ( $can_add_comments ) {
            ob_start();
            $notes = $this->existing_order_notes( $order->get_id() );
            wc_get_template(
                'order_note_form.php',
                array(
                    'order_number' => $order->get_order_number(),
                    'order_id'     => $order->get_id(),
                    'notes'        => $notes,
                ),
                'wc-vendors/dashboard/order/',
                $this->base_dir . 'templates/dashboard/order/'
            );
            $form = ob_get_contents();
            ob_end_clean();
        }

        return $form;
    }

    /**
     *  Order Details Template
     *
     * @since    2.5.2
     *
     * @param     object $order_row order id for notes.
     */
    public function order_details_template( $order_row ) {
        global $wpdb;

        $form = '';

        // Get line items.
        $order           = $order_row->order->get_parent_order();
        $line_items      = isset( $order_row->order_items ) && is_array( $order_row->order_items ) ? $order_row->order_items : array();
        $billing_fields  = $order->get_formatted_billing_address();
        $shipping_fields = $order->get_formatted_shipping_address();
        $is_order_refund = false;
        $total_refund    = 0;
        $order_currency  = $order->get_currency();
        $shipping_tax    = 0;
        $vendor_id       = get_current_user_id();

        $order_item_details = array();

        $order_taxes = array();

        if ( wc_tax_enabled() ) {
            $order_taxes         = $order->get_taxes();
            $tax_classes         = \WC_Tax::get_tax_classes();
            $classes_options     = array();
            $classes_options[''] = __( 'Standard', 'wc-vendors' );

            if ( ! empty( $tax_classes ) ) {
                foreach ( $tax_classes as $class ) {
                    $classes_options[ sanitize_title( $class ) ] = $class;
                }
            }

            $show_tax_columns = count( $order_taxes ) === 1;
        }

        foreach ( $line_items as $item_id => $item ) {

            $order_item_detail = array();
            $line_item_taxes   = array();
            // Check if this is a variation and get the parent id, this ensures that the correct vendor id is retrieved.
            $product_id         = ( $item['variation_id'] > 0 ) ? $item['variation_id'] : $item['product_id'];
            $_product           = $item->get_product();
            $item_qty           = $item->get_quantity();
            $product_commission = ( $item_qty > 1 ) ? $order_row->product_commissions[ $product_id ] / $item_qty : $order_row->product_commissions[ $product_id ];
            $sku                = ( $_product && $_product->get_sku() ) ? esc_html( $_product->get_sku() ) . ' &ndash; ' : '';
            $_item_id           = $item->get_id();

            $order_item_detail['thumbnail']    = ( $_product ) ? $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ) : wc_placeholder_img( 'shop_thumbnail' );
            $order_item_detail['product_name'] = ( $sku ) ? $sku . esc_html( $item->get_name() ) : esc_html( $item->get_name() );
            $order_item_detail['product_meta'] = wc_display_item_meta( $item, array( 'echo' => false ) );
            $order_item_detail['commission']   = wc_price( $product_commission, array( 'currency' => $order_currency ) );
            $order_item_detail['qty']          = ( $item->get_quantity() ) ? esc_html( $item->get_quantity() ) : '';
            $order_item_detail['cost']         = wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency() ) );
            $order_item_detail['total']        = wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );
            $order_item_detail['item']         = $item;

            if ( $order->get_total_refunded_for_item( $_item_id ) > 0 ) {
                $is_order_refund                   = true;
                $refund_qty                        = $order->get_qty_refunded_for_item( $_item_id );
                $refund_total                      = $order->get_total_refunded_for_item( $_item_id );
                $order_item_detail['refund_total'] = $refund_total;
                $order_item_detail['refund_qty']   = $refund_qty;
                $total_refund                     += $refund_total;
            }

            $tax_data = wc_tax_enabled() ? $item->get_taxes() : false;
            if ( $tax_data ) {
                foreach ( $order_taxes as $tax_item ) {
                    $tax_item_id       = $tax_item->get_rate_id();
                    $tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
                    $tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';

                    if ( '' !== $tax_item_total ) {
                        $item_tax = wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    } else {
                        $item_tax = '&ndash;';
                    }

                    if ( $is_order_refund && $order_row->total_tax > 0 ) {
                        $order_item_detail['refund_tax'] = $order->get_tax_refunded_for_item( $_item_id, $tax_item_id );
                        $total_refund                   += $order_item_detail['refund_tax'];
                    }

                    $line_item_taxes[] = $item_tax;
                }
            }

            if ( wc_tax_enabled() ) {
                $item_shipping = $wpdb->get_var( $wpdb->prepare( "SELECT total_shipping FROM {$wpdb->prefix}pv_commission WHERE product_id = %d AND vendor_id = %d AND order_id = %d", $product_id, $vendor_id, $order->get_id() ) );
                $shipping_tax += \WCV_Shipping::calculate_shipping_tax( $item_shipping, $order, $item->get_tax_class() );
            }

            $order_item_detail['tax_items'] = $line_item_taxes;

            $order_item_details[ $item_id ] = $order_item_detail;
        }

        $total_shipping_refunded = $this->get_refunded_shipping_total( $order );
        $total_refund           += $total_shipping_refunded;

        $customer_note = $order->get_customer_note();
        $customer_note = ( $customer_note ) ?
            sprintf( '<p>%s</p>', wp_kses( $order->get_customer_note(), array( 'br' => array() ) ) ) :
            sprintf( '<p>%s</p>', __( 'No customer notes.', 'wc-vendors' ) );
        ob_start();

        wc_get_template(
            'order_details.php',
            array(
                'order'              => $order,
                '_order'             => $order_row,
                'order_currency'     => $order->get_currency(),
                'order_date'         => $order->get_date_created(),
                'order_id'           => $order->get_order_number(),
                'line_items'         => $line_items,
                'shipping_tax'       => $shipping_tax,
                'order_taxes'        => $order_taxes,
                'billing_fields'     => $billing_fields,
                'shipping_fields'    => $shipping_fields,
                'order_item_details' => $order_item_details,
                'customer_note'      => $customer_note,
                'is_order_refund'    => $is_order_refund,
                'total_refund'       => $total_refund,
            ),
            'wc-vendors/dashboard/order/',
            $this->base_dir . 'templates/dashboard/order/'
        );

        $form = ob_get_contents();
        ob_end_clean();

        return $form;
    }

    /**
     *  Existing Order Notes
     *
     * @since    2.5.2
     *
     * @param     int $order_id order id for notes.
     */
    public function existing_order_notes( $order_id ) {

        $can_view_comments = wc_string_to_bool( get_option( 'wcvendors_capability_order_read_notes', 'no' ) );

        $notes = '';

        if ( ! $can_view_comments ) {
            return;
        }

        $order_notes = $this->get_vendor_order_notes( $order_id );

        if ( ! empty( $order_notes ) ) {
            ob_start();
            foreach ( $order_notes as $order_note ) {
                $time_posted = human_time_diff( strtotime( $order_note->comment_date_gmt ), time() );
                $note_text   = $order_note->comment_content;
                wc_get_template(
                    'order_note.php',
                    array(
                        'time_posted' => $time_posted,
                        'note_text'   => $note_text,
                    ),
                    'wc-vendors/dashboard/order/',
                    $this->base_dir . 'templates/dashboard/order/'
                );
            }
            $notes = ob_get_contents();
            ob_end_clean();
        }

        return $notes;
    }

    /**
     *  Get the vendor notes for an order
     *
     * @since    2.5.2
     *
     * @param     int $order_id order id for notes.
     */
    public function get_vendor_order_notes( $order_id ) {

        $notes = array();

        $args = array(
            'user_id' => get_current_user_id(),
            'post_id' => $order_id,
            'approve' => 'approve',
            'type'    => '',
        );

        remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

        $comments = get_comments( $args );

        foreach ( $comments as $comment ) {

            $is_customer_note = get_comment_meta( $comment->comment_ID, 'is_customer_note', true );

            if ( $is_customer_note ) {
                $notes[] = $comment;
            }
        }

        add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

        return (array) $notes;
    }

    /**
     *  Trigger the csv export
     *
     * @version 2.5.2
     * @since   2.5.2
     */
    public function export_csv() {

        include_once 'class-wcv-export-helper.php';

        $date_range = array(
            'before' => gmdate( 'Y-m-d', $this->get_end_date() ),
            'after'  => gmdate( 'Y-m-d', $this->get_start_date() ),
        );

        $csv_output  = new \WCV_Export_Helper();
        $csv_headers = $csv_output->get_export_headers();
        $orders      = WCV_Vendor_Controller::get_orders2( get_current_user_id(), $date_range, true );
        $rows        = $csv_output->format_orders_export( $orders );

        // Remove the ID column as its not required.
        unset( $csv_headers['ID'] );
        $csv_headers  = apply_filters( 'wcv_order_export_csv_headers', $csv_headers );
        $csv_rows     = apply_filters( 'wcv_order_export_csv_rows', $rows, $orders, $date_range );
        $csv_filename = apply_filters( 'wcv_order_export_csv_filename', 'orders' );

        $csv_output->download_csv( $csv_headers, $csv_rows, $csv_filename );
    }

    /**
     *  Tracking Number Template
     *
     * @since    2.5.2
     * @version  2.5.2
     *
     * @param WC_Order $order order id for notes.
     * @param int      $vendor_id The vendor ID.
     */
    public function tracking_number_template( $order, $vendor_id ) {

        $form = '';

        ob_start();

        $tracking_details = $this->get_vendor_tracking_details( $order->get_id(), $vendor_id );

        // Clean up any empty indexes.
        if ( ! isset( $tracking_details['_wcv_shipping_provider'] ) ) {
            $tracking_details['_wcv_shipping_provider'] = '';
        }
        if ( ! isset( $tracking_details['_wcv_tracking_number'] ) ) {
            $tracking_details['_wcv_tracking_number'] = '';
        }
        if ( ! isset( $tracking_details['_wcv_date_shipped'] ) ) {
            $tracking_details['_wcv_date_shipped'] = '';
        }

        wc_get_template(
            'tracking_number.php',
            array(
                'order_number'     => $order->get_order_number(),
                'order_id'         => $order->get_id(),
                'tracking_details' => $tracking_details,
            ),
            'wc-vendors/dashboard/order/',
            $this->base_dir . 'templates/dashboard/order/'
        );

        $form = ob_get_contents();
        ob_end_clean();

        return $form;
    }

    /**
     *  Tracking Number Template
     *
     * @since    2.5.2
     *
     * @param WC_Order|int|array|WP_Post $order The order object, ID, details.
     * @param int                        $vendor_id The vendor ID.
     */
    public function get_vendor_tracking_details( $order, $vendor_id ) {

        $order = ! is_a( $order, 'WC_Order' ) ? wc_get_order( $order ) : $order;

        $order_tracking_details = (array) $order->get_meta( '_wcv_tracking_details', true );
        $order_tracking_details = array_filter( $order_tracking_details );

        if ( empty( $order_tracking_details ) ) {
            return array();
        }

        if ( array_key_exists( $vendor_id, $order_tracking_details ) ) {
            return $order_tracking_details[ $vendor_id ];
        } else {
            return array();
        }
    }

    /**
     * Update the order shipment tracking
     *
     * @version 2.5.2
     * @since   2.5.2
     */
    public function update_shipment_tracking() {
        if ( ! isset( $_POST['wcv_add_tracking_number_nonce'] ) || ! wp_verify_nonce( $_POST['wcv_add_tracking_number_nonce'], 'wcv_add_tracking_number' ) ) {
            wc_add_notice(
                __( 'Security check failed. Please refresh the page and try again.', 'wc-vendors' ),
                'error'
            );
            return;
        }

        $order_id = (int) absint( sanitize_text_field( $_POST['_wcv_order_id'] ) );
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }
        $order_tracking_details = array_filter( (array) $order->get_meta( '_wcv_tracking_details', true ) );
        $vendor_id              = isset( $_POST['vendor_id'] ) ? (int) $_POST['vendor_id'] : get_current_user_id();
        $store_name             = \WCV_Vendors::get_vendor_shop_name( $vendor_id );
        $order_tracking_details = is_array( $order_tracking_details ) && ! empty( $order_tracking_details ) ? $order_tracking_details : array();

        $shipping_postcode = $order->get_shipping_postcode();
        $country_code      = $order->get_shipping_country();

        $vendor_tracking_details = array(
            '_wcv_shipping_provider' => $_POST[ '_wcv_shipping_provider_' . $order_id ],
            '_wcv_tracking_number'   => $_POST[ '_wcv_tracking_number_' . $order_id ],
            '_wcv_date_shipped'      => $_POST[ '_wcv_date_shipped_' . $order_id ],
        );

        $order_tracking_details[ $vendor_id ] = $vendor_tracking_details;

        $tracking_base_url = '';
        $tracking_provider = '';

        // Loop through providers and get the URL to input.
        foreach ( $this->shipping_providers() as $provider_countries ) {

            foreach ( $provider_countries as $provider => $url ) {

                if ( sanitize_title( $provider ) === sanitize_title( $vendor_tracking_details['_wcv_shipping_provider'] ) ) {
                    $tracking_base_url = $url;
                    $tracking_provider = $provider;
                    break;
                }
            }

            if ( $tracking_base_url ) {
                break;
            }
        }

        $order_note = sprintf(
            // translators: %s is the name of the store.
            __( '%s has added a tracking number to your order. You can track this at the following url. ', 'wc-vendors' ),
            esc_html( $store_name )
        );

        $full_link       = sprintf( $tracking_base_url, $vendor_tracking_details['_wcv_tracking_number'], $shipping_postcode, $country_code );
        $order_note     .= sprintf( '<a href="%s" target="_blank">%s</a>', $full_link, $full_link, $full_link );
        $tracking_number = esc_attr__( 'Tracking Number:', 'wc-vendors' );
        $order_note     .= sprintf( '<br /><strong>%1$s</strong> %2$s', $tracking_number, $vendor_tracking_details['_wcv_tracking_number'] );

        $order_note = apply_filters(
            'wcv_shipping_tracking_update_note',
            $order_note,
            $full_link,
            $vendor_id,
            $order_id,
            $store_name,
            $order_tracking_details
        );

        $order->update_meta_data( '_wcv_tracking_details', $order_tracking_details );
        $order->save();

        $this->add_order_note( $order_id, $order_note );

        // Mark as shipped as tracking information has been added.
        self::mark_shipped( $vendor_id, $order_id );

        do_action( 'wcv_update_shipment_tracking', $vendor_tracking_details );
    }

    /**
     *  Shipment tracking providers
     *
     * @since    2.5.2
     * @return     array    shipping providers
     */
    public static function shipping_providers() {

        $shipping_providers = array(
            'Australia'           => array(
                'Australia Post'   => 'https://auspost.com.au/mypost/track/#/details/%1$s',
                'FedEx'            => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=%1$s&cntry_code=au',
                'Fastway Couriers' => 'https://www.fastway.com.au/tools/track/?l=%1$s',
            ),
            'Austria'             => array(
                'post.at' => 'https://www.post.at/sv/sendungsdetails?snr=%1$s',
                'dhl.at'  => 'https://www.dhl.at/content/at/de/express/sendungsverfolgung.html?brand=DHL&AWB=%1$s',
                'DPD.at'  => 'https://tracking.dpd.de/parcelstatus?locale=de_AT&query=%1$s',
            ),
            'Brazil'              => array(
                'Correios' => 'http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=%1$s',
            ),
            'Belgium'             => array(
                'bpost' => 'https://track.bpost.be/btr/web/#/search?itemCode=%1$s',
            ),
            'Canada'              => array(
                'Canada Post' => 'https://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=%1$s',
                'Fedex'       => 'http://www.fedex.com/Tracking?action=track&tracknumbers=%1$s',
                'UPS'         => 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=%1$s',
                'Purolator'   => 'https://www.purolator.com/purolator/ship-track/tracking-summary.page?pin=%1$s',
            ),
            'Germany'             => array(
                'DHL Intraship (DE)' => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=%1$s&rfn=&extendedSearch=true',
                'Hermes'             => 'https://tracking.hermesworld.com/?TrackID=%1$s',
                'Deutsche Post DHL'  => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=%1$s',
                'UPS Germany'        => 'http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&tracknums_displayed=1&TypeOfInquiryNumber=T&loc=de_DE&InquiryNumber1=%1$s',
                'DPD'                => 'https://tracking.dpd.de/parcelstatus?query=%1$s&locale=en_DE',
            ),
            'Czech Republic'      => array(
                'PPL.cz'      => 'https://www.ppl.cz/main2.aspx?cls=Package&idSearch=%1$s',
                'Česká pošta' => 'https://www.postaonline.cz/trackandtrace/-/zasilka/cislo?parcelNumbers=%1$s',
                'DHL.cz'      => 'https://www.dhl.cz/cs/express/sledovani_zasilek.html?AWB=%1$s',
                'DPD.cz'      => 'https://tracking.dpd.de/parcelstatus?locale=cs_CZ&query=%1$s',
            ),
            'Finland'             => array(
                'Itella' => 'https://www.posti.fi/itemtracking/posti/search_by_shipment_id?lang=en&ShipmentId=%1$s',
            ),
            'France'              => array(
                'Colissimo' => 'https://www.laposte.fr/outils/suivre-vos-envois?code=%1$s',
            ),
            'Ireland'             => array(
                'DPD' => 'http://www2.dpd.ie/Services/QuickTrack/tabid/222/ConsignmentID/%1$s/Default.aspx',
            ),
            'Italy'               => array(
                'BRT (Bartolini)' => 'https://as777.brt.it/vas/sped_det_show.hsm?referer=sped_numspe_par.htm&Nspediz=%1$s',
                'DHL Express'     => 'https://www.dhl.it/it/express/ricerca.html?AWB=%1$s&brand=DHL',
            ),
            'India'               => array(
                'DTDC' => 'https://www.dtdc.in/dtdcTrack/Tracking/consignInfo.asp?strCnno=%1$s',
            ),
            'Netherlands'         => array(
                'PostNL'          => 'https://postnl.nl/tracktrace/?B=%1$s&P=%2$s&D=%3$s&T=C',
                'DPD.NL'          => 'https://tracking.dpd.de/status/en_US/parcel/%1$s',
                'UPS Netherlands' => 'https://wwwapps.ups.com/WebTracking?sort_by=status&tracknums_displayed=1&TypeOfInquiryNumber=T&loc=nl_NL&InquiryNumber1=%1$s',
            ),
            'New Zealand'         => array(
                'Courier Post' => 'https://trackandtrace.courierpost.co.nz/Search/%1$s',
                'NZ Post'      => 'https://www.nzpost.co.nz/tools/tracking?trackid=%1$s',
                'Fastways'     => 'http://www.fastway.co.nz/courier-services/track-your-parcel?l=%1$s',
                'PBT Couriers' => 'http://www.pbt.com/nick/results.cfm?ticketNo=%1$s',
                'Aramex'       => 'https://www.aramex.co.nz/tools/track?l=%1$s',
            ),
            'Poland'              => array(
                'InPost'        => 'https://inpost.pl/sledzenie-przesylek?number=%1$s',
                'DPD.PL'        => 'https://tracktrace.dpd.com.pl/parcelDetails?p1=%1$s',
                'Poczta Polska' => 'https://emonitoring.poczta-polska.pl/?numer=%1$s',
            ),
            'Romania'             => array(
                'Fan Courier'   => 'https://www.fancourier.ro/awb-tracking/?xawb=%1$s',
                'DPD Romania'   => 'https://tracking.dpd.de/parcelstatus?query=%1$s&locale=ro_RO',
                'Urgent Cargus' => 'https://app.urgentcargus.ro/Private/Tracking.aspx?CodBara=%1$s',
            ),
            'South Africa'        => array(
                'SAPO'    => 'http://sms.postoffice.co.za/TrackingParcels/Parcel.aspx?id=%1$s',
                'Fastway' => 'https://fastway.co.za/our-services/track-your-parcel?l=%1$s',
            ),
            'Sweden'              => array(
                'PostNord Sverige AB' => 'https://portal.postnord.com/tracking/details/%1$s',
                'DHL.se'              => 'https://www.dhl.se/content/se/sv/express/godssoekning.shtml?AWB=%1$s&brand=DHL',
                'Bring.se'            => 'https://tracking.bring.se/tracking/%1$s',
                'UPS.se'              => 'https://www.ups.com/track?loc=sv_SE&tracknum=%1$s&requester=WT/',
                'DB Schenker'         => 'http://privpakportal.schenker.nu/TrackAndTrace/packagesearch.aspx?packageId=%1$s',
            ),
            'United Kingdom (UK)' => array(
                'DHL'                       => 'http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=%1$s',
                'DPD'                       => 'http://www.dpd.co.uk/tracking/trackingSearch.do?search.searchType=0&search.parcelNumber=%1$s',
                'InterLink'                 => 'http://www.interlinkexpress.com/apps/tracking/?reference=%1$s&postcode=%2$s#results',
                'ParcelForce'               => 'http://www.parcelforce.com/portal/pw/track?trackNumber=%1$s',
                'Royal Mail'                => 'https://www.royalmail.com/track-your-item/?trackNumber=%1$s',
                'TNT Express (consignment)' => 'http://www.tnt.com/webtracker/tracking.do?requestType=GEN&searchType=CON&respLang=en&respCountry=GENERIC&sourceID=1&sourceCountry=ww&cons=%1$s&navigation=1&genericSiteIdent=',
                'TNT Express (reference)'   => 'http://www.tnt.com/webtracker/tracking.do?requestType=GEN&searchType=REF&respLang=en&respCountry=GENERIC&sourceID=1&sourceCountry=ww&cons=%1$s&navigation=1&genericSiteIdent=',
                'UK Mail'                   => 'https://old.ukmail.com/ConsignmentStatus/ConsignmentSearchResults.aspx?SearchType=Reference&SearchString=%1$s',
                'DPD.co.uk'                 => 'https://www.dpd.co.uk/apps/tracking/?reference=%1$s#results',
                'DHL Parcel UK'             => 'https://track.dhlparcel.co.uk/?con=%1$s',
            ),
            'United States (US)'  => array(
                'Fedex'         => 'https://www.fedex.com/Tracking?action=track&tracknumbers=%1$s',
                'FedEx Sameday' => 'https://www.fedexsameday.com/fdx_dotracking_ua.aspx?tracknum=%1$s',
                'OnTrac'        => 'http://www.ontrac.com/trackingdetail.asp?tracking=%1$s',
                'UPS'           => 'https://www.ups.com/track?loc=en_US&tracknum=%1$s',
                'USPS'          => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=%1$s',
                'DHL US'        => 'https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=%1$s',
            ),
        );

        $ship_to_countries      = get_option( 'woocommerce_ship_to_countries' );
        $countries              = new \WC_Countries();
        $shipping_countries     = $countries->get_shipping_countries();
        $shipping_country_names = array();

        foreach ( $shipping_countries as $country_code => $country_name ) {
            $shipping_country_names[] = $country_name;
        }

        if ( 'specific' === $ship_to_countries ) {
            foreach ( $shipping_providers as $country_name => $providers ) {
                if ( ! in_array( $country_name, $shipping_country_names, true ) ) {
                    unset( $shipping_providers[ $country_name ] );
                }
            }
        }

        return apply_filters( 'wcv_shipping_providers_list', $shipping_providers );
    }

    /**
     * Filter the customers shipping address for the order table and view order details
     *
     * @since   2.5.2
     * @version 2.5.2
     * @access  public
     *
     * @param  array $address The customer's address.
     * @return array
     */
    public function filter_formatted_shipping_address( $address ) {

        $show_name             = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_name', 'no' ) );
        $show_shipping_address = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping', 'no' ) );

        if ( ! $show_name ) {
            if ( array_key_exists( 'first_name', $address ) ) {
                unset( $address['first_name'] );
            }

            if ( array_key_exists( 'last_name', $address ) ) {
                unset( $address['last_name'] );
            }
        }

        if ( ! $show_shipping_address ) {
            if ( array_key_exists( 'company', $address ) ) {
                unset( $address['company'] );
            }

            if ( array_key_exists( 'address_1', $address ) ) {
                unset( $address['address_1'] );
            }

            if ( array_key_exists( 'address_2', $address ) ) {
                unset( $address['address_2'] );
            }

            if ( array_key_exists( 'city', $address ) ) {
                unset( $address['city'] );
            }

            if ( array_key_exists( 'state', $address ) ) {
                unset( $address['state'] );
            }

            if ( array_key_exists( 'postcode', $address ) ) {
                unset( $address['postcode'] );
            }

            if ( array_key_exists( 'country', $address ) ) {
                unset( $address['country'] );
            }
        }

        return $address;
    } // filter_formatted_shipping_address

    /**
     * Filter the customers billing address for view order details
     *
     * @since   2.5.2
     * @version 2.5.2
     * @access  public
     *
     * @param  array $address The customer's address.
     * @return array
     */
    public function filter_formatted_billing_address( $address ) {

        $show_name            = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_name', 'no' ) );
        $show_billing_address = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_billing', 'no' ) );

        if ( ! $show_name ) {
            if ( array_key_exists( 'first_name', $address ) ) {
                unset( $address['first_name'] );
            }

            if ( array_key_exists( 'last_name', $address ) ) {
                unset( $address['last_name'] );
            }
        }

        if ( ! $show_billing_address ) {
            if ( array_key_exists( 'company', $address ) ) {
                unset( $address['company'] );
            }

            if ( array_key_exists( 'address_1', $address ) ) {
                unset( $address['address_1'] );
            }

            if ( array_key_exists( 'address_2', $address ) ) {
                unset( $address['address_2'] );
            }

            if ( array_key_exists( 'city', $address ) ) {
                unset( $address['city'] );
            }

            if ( array_key_exists( 'state', $address ) ) {
                unset( $address['state'] );
            }

            if ( array_key_exists( 'postcode', $address ) ) {
                unset( $address['postcode'] );
            }

            if ( array_key_exists( 'country', $address ) ) {
                unset( $address['country'] );
            }
        }

        return $address;
    } // filter_formatted_billing_address

    /**
     * Filter the order meta to remove hidden keys
     *
     * @param array $formatted_meta the formatted order meta.
     *
     * @since   2.5.2
     * @version 2.5.2
     */
    public function filter_order_item_get_formatted_meta_data( $formatted_meta ) {
        $dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id', array() );

        foreach ( $dashboard_page_ids as $dashboard_page_id ) {
            if ( isset( $dashboard_page_id ) && is_page( $dashboard_page_id ) ) {
                $hide_meta = apply_filters(
                    'wcvendors_hide_order_meta_data',
                    array(
                        'Sold By',
                        __( get_option( 'wcvendors_label_sold_by', 'Sold By' ), 'wc-vendors' ), // phpcs:ignore
                    )
                );

                // Filter any meta not to show.
                foreach ( $formatted_meta as $key => $meta ) {
                    if ( in_array( $meta->key, $hide_meta, true ) ) {
                        unset( $formatted_meta[ $key ] );
                    }
                }
            }
        }

        return $formatted_meta;
    }

    /**
     * Get order start date.
     *
     * @return array|string
     */
    public function get_start_date() {
        return WC()->session->get( 'wcv_order_start_date', strtotime( apply_filters( 'wcv_order_start_date', $this->default_start ) ) );
    }

    /**
     * Get order end date.
     *
     * @return array|string
     */
    public function get_end_date() {
        return WC()->session->get( 'wcv_order_end_date', strtotime( apply_filters( 'wcv_order_end_date', 'now' ) ) );
    }

    /**
     * Get default start date.
     *
     * @return string
     */
    public function get_default_start_date() {
        return strtotime( apply_filters( 'wcv_order_start_date', $this->default_start ) );
    }

    /**
     * Get the order status from the order filter
     *
     * @return string $order_statuses The comma separated list of order statuses to filter by.
     * @version 2.5.2
     * @since   2.5.2 - Added
     */
    public function get_order_filter_status() {
        $order_statuses = WC()->session->get( 'wcv_order_filter_status', '' );

        return apply_filters( 'wcv_order_filter_status', $order_statuses );
    }

    /**
     * Recursive get key value from array
     *
     * @param  array  $data  array to search.
     * @param  string $key    key to search for.
     *
     * @return string|bool string if found, false if not.
     */
    public function get_key_value( $data, $key ) {

        if ( ! is_array( $data ) ) {
            return false;
        }

        if ( array_key_exists( $key, $data ) ) {
            return $data[ $key ];
        }

        foreach ( $data as $sub_array ) {
            if ( is_array( $sub_array ) ) {
                $value = $this->get_key_value( $sub_array, $key );
                if ( $value ) {
                    return $value;
                }
            }
        }

        return false;
    }

    /**
     * Get refunded shipping total for order.
     *
     * @param WC_Order $order vendor order.
     */
    public function get_refunded_shipping_total( $order ) {
        $shipping_items          = $order->get_items( 'shipping' );
        $total_shipping_refunded = 0;
        foreach ( $shipping_items as $item_id => $shipping_item ) {
            $data = $shipping_item->get_meta_data();
            foreach ( $data as $value ) {
                $product_id = $this->get_key_value( $value->get_data(), 'product_id' );
                $author     = get_post_field( 'post_author', $product_id );
                if ( get_current_user_id() === $author ) {
                    $total_shipping_refunded += $order->get_total_refunded_for_item( $shipping_item->get_id(), 'shipping' );
                    break;
                }
            }
        }
        return $total_shipping_refunded;
    }

    /**
     * Automatic complete orders when all vendor marked shipped.
     *
     * @param  int   $order_id order id.
     * @param  array $shippers vendors who have marked shipped.
     * @param  int   $vendor_id vendor id.
     * @return void
     *
     * @since 2.5.2
     * @version 2.5.2
     */
    public function auto_complete_order( $order_id, $shippers, $vendor_id ) {
        $order       = wc_get_order( $order_id );
        $vendors     = \WCV_Vendors::get_vendors_from_order( $order );
        $vendor_ids  = array_keys( $vendors );
        $all_shipped = true;

        foreach ( $vendor_ids as $vendor_id ) {
            if ( ! in_array( $vendor_id, $shippers, true ) ) {
                $all_shipped = false;
                break;
            }
        }

        if ( $all_shipped ) {
            $order->update_status( 'completed' );
        }
    }

    /** Mark all order items shipped when order is marked as complete.
     *
     * @param int $order_id order id.
     *
     * @version 2.5.2
     * @since   2.5.2
     */
    public function mark_shipped_completed_order( $order_id ) {
        $mark_all_shipped = wc_string_to_bool( get_option( 'wcvendors_mark_all_items_shipped_on_completed_order', 'no' ) );

        if ( ! $mark_all_shipped ) {
            return;
        }

        $order       = wc_get_order( $order_id );
        $vendors     = \WCV_Vendors::get_vendors_from_order( $order );
        $shippers    = array_unique( (array) $order->get_meta( 'wc_pv_shipped' ) );
        $vendors_ids = array_unique( array_keys( $vendors ) );

        sort( $vendors_ids );
        sort( $shippers );

        if ( count( array_diff( $vendors_ids, $shippers ) ) !== 0 ) {
            $order->add_meta_data( 'wc_pv_shipped', $vendors_ids, true );
            $order->save();
        }
    }
}
