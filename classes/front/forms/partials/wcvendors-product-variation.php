<?php

/**
 * A single product variation
 *
 * This file is used to load a single product variation for a product
 *
 * @link       http://www.wcvendors.com
 * @since      1.3.0
 * @since      1.8.8 - Added decimal validation for weight fields.
 * @version    1.8.8
 */
use function WC_Vendors\Classes\Includes\variation_option_required;
extract( $variation_data ); //phpcs:ignore

// Fix false data added to db.
$_download_expiry       = ( $_download_expiry == -1 ) ? '' : $_download_expiry; //phpcs:ignore
$_download_limit        = ( $_download_limit == -1 ) ? '' : $_download_limit; //phpcs:ignore
$variations_options     = (array) get_option( 'wcvendors_hide_product_variations', array() );
$decimal_separator      = get_option( 'woocommerce_price_decimal_sep' );
$decimal_number         = get_option( 'woocommerce_price_num_decimals' );
$weight_parsley_pattern = '/^\d{1,3}([' . $decimal_separator . ']\d{1,' . $decimal_number . '})?$/';

?>

<div class="wcv_variation wcv-metabox closed all-100" rel="<?php echo esc_attr( $variation_id ); ?>"
    data-loop="<?php echo esc_attr( $loop ); ?>">
    <div class="wcv-cols-group wcv_variation_inner">
        <div class="all-100">
            <h5 class="variation_title">
                <span class="wcv-sort"><i class="wcv-icon wcv-icon-sort"></i></span>
                <strong>#<?php echo esc_html( $variation_data['id'] ); ?> : </strong>
                <span class="variations_wrapper">
                <?php

                $attributes = WCV_Utils::array_sort( $attributes, 'position' );

                foreach ( $attributes as $key => $attribute ) {
                    // Get current value for variation (if set).
                    $variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ] : '';

					if ( array_key_exists( 'is_variation', $attribute ) && 0 == $attribute['is_variation'] ) { //phpcs:ignore
                        continue;
                    }
                    // Name will be something like attribute_pa_color.
                    echo '<select data-taxonomy="' . esc_attr( sanitize_title( $attribute['name'] ) ) . '" class="variation_attribute ' . esc_attr( sanitize_title( $key ) ) . '" name="attribute_' . esc_attr( sanitize_title( $key ) ) . '[' . esc_attr( $loop ) . ']">
					<option value="">' . esc_html__( 'Any', 'wc-vendors' ) . ' ' . esc_html( wc_attribute_label( $key ) ) . '&hellip;</option>';


                    if ( array_key_exists( 'values', $attribute ) && is_array( $attribute['values'] ) ) {

                        foreach ( $attribute['values'] as $key => $value ) {
                            echo '<option ' . selected( $variation_selected_value, $key, false ) . ' value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
                        }
					} elseif ( array_key_exists( 'value', $attribute ) && '' != $attribute['value'] ) { //phpcs:ignore
                        $attribute_values = explode( '|', $attribute['value'] );

                        if ( is_array( $attribute_values ) ) {
                            foreach ( $attribute_values as $value ) {
                                echo '<option ' . selected( $variation_selected_value, trim( $value ), false ) . ' value="' . esc_attr( trim( $value ) ) . '">' . esc_html( $value ) . '</option>';
                            }
                        } else {
                            $value = trim( $attribute_values );
                            echo '<option ' . selected( $variation_selected_value, trim( $value ), false ) . ' value="' . esc_attr( trim( $value ) ) . '">' . esc_html( $value ) . '</option>';
                        }
                    } else {

                        $post_terms = wp_get_post_terms( $parent_data['id'], $attribute['name'] );

						foreach ( $post_terms as $term ) { //phpcs:ignore
                            echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
                        }
                    }

                    echo '</select>';
                }
                ?>
                </span>
                <span><i class="wcv-icon wcv-icon-caret-down" aria-hidden="true"></i></span>
                <a href="#" class="remove_variation delete" rel="<?php echo esc_attr( $variation_id ); ?>"
                    data-loop="<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Remove', 'wc-vendors' ); ?></a>
                <input type="hidden" name="variable_post_id[<?php echo esc_attr( $loop ); ?>]"
                        value="<?php echo esc_attr( $variation_id ); ?>"/>
                <input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo esc_attr( $loop ); ?>]"
                        value="<?php echo isset( $menu_order ) ? absint( $menu_order ) : 0; ?>"/>
            </h5>
        </div>
    </div>
    <div class="wcv_variable_attributes wcv-metabox-content" style="display: none;">

        <?php do_action( 'wcv_product_variation_before_general', $loop, $variation_id, $variation_data, $variation ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters">
            <div class="all-50 upload_image">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_featured', 'no' ) ) : ?>
                    <a href="#" class="upload_image_button
                    <?php
                    if ( $_thumbnail_id > 0 ) {
                        echo 'wcv_remove';
                    }
                    ?>
                    " rel="<?php echo esc_attr( $variation_id ); ?>">
                        <img class="wc_placeholder_img" src="
                        <?php
                        if ( ! empty( $image ) ) {
                            echo esc_attr( $image );
                        } else {
                            echo esc_attr( wc_placeholder_img_src() );
                        }
                        ?>
                        "/>
                        <input type="hidden" name="upload_image_id[<?php echo esc_attr( $loop ); ?>]" class="upload_image_id"
                                value="<?php echo esc_attr( $_thumbnail_id ); ?>"
								<?php echo variation_option_required( 'featured' ); //phpcs:ignore ?>
                                />
                    </a>
                <?php endif; ?>
            </div>
            <div class="all-50 sku">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_sku', 'no' ) ) : ?>
                    <?php if ( wc_product_sku_enabled() ) : ?>
                        <div class="control-group">
                        <label><?php esc_html_e( 'SKU', 'wc-vendors' ); ?>: </label>
                        <div class="control">
                            <input type="text" name="variable_sku[<?php echo esc_attr( $loop ); ?>]"
                                    value="<?php echo isset( $_sku ) ? esc_attr( $_sku ) : ''; ?>"
                                    placeholder="<?php echo esc_attr( $parent_data['sku'] ); ?>"
									<?php echo variation_option_required( 'sku' ); //phpcs:ignore ?> />
                        </div>
                    <?php else : ?>
                        <input type="hidden" name="variable_sku[<?php echo esc_attr( $loop ); ?>]"
                                value="<?php echo isset( $_sku ) ? esc_attr( $_sku ) : ''; ?>"/>
                    <?php endif; ?>
                    </div>
                <?php else : ?>
                    <input type="hidden" name="variable_sku[<?php echo esc_attr( $loop ); ?>]" value="<?php echo isset( $_sku ) ? esc_attr( $_sku ) : ''; ?>"/>
                <?php endif; ?>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_general', $loop, $variation_id, $variation_data, $variation ); ?>

        <hr style="clear: both;"/>

        <?php do_action( 'wcv_product_variation_before_options', $loop, $variation_id, $variation_data, $variation ); ?>

        <!-- Variable options  -->
        <div class="wcv-cols-group wcv-horizontal-gutters">
            <div class="all-100">

                <div class="wcv-column-group">
                    <div class="all-25">
                        <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_enabled', 'no' ) ) : ?>
                            <input type="checkbox" class="checkbox variable_enabled"
                                    name="variable_enabled[<?php echo esc_attr( $loop ); ?>]" <?php checked( $_enabled ); ?>
									<?php echo variation_option_required( 'enabled' ); //phpcs:ignore ?> />
                            <label><?php esc_html_e( 'Enabled', 'wc-vendors' ); ?></label>
                        <?php endif; ?>
                    </div>
                    <div class="all-25">
                        <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_downloadable', 'no' ) ) : ?>
                            <input type="checkbox" class="checkbox variable_is_downloadable"
                                    name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]"
                                    <?php checked( isset( $_downloadable ) ? $_downloadable : '', 'yes' ); ?>
									<?php echo variation_option_required( 'downloadable' ); //phpcs:ignore ?> />
                            <label><?php esc_html_e( 'Downloadable', 'wc-vendors' ); ?> </label>
                        <?php endif; ?>
                    </div>
                    <div class="all-25">
                        <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_virtual', 'no' ) ) : ?>
                            <input type="checkbox" class="checkbox variable_is_virtual"
                                    name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]"
                                    <?php checked( isset( $_virtual ) ? $_virtual : '', 'yes' ); ?>
									<?php echo variation_option_required( 'virtual' ); //phpcs:ignore ?> />
                            <label><?php esc_html_e( 'Virtual', 'wc-vendors' ); ?></label>
                        <?php endif; ?>
                    </div>
                    <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_manage_stock', 'no' ) ) : ?>
                        <?php if ( get_option( 'woocommerce_manage_stock' ) === 'yes' ) : ?>
                            <div class="all-25">
                                <input type="checkbox" class="checkbox variable_manage_stock"
                                        name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]"
                                        <?php checked( isset( $_manage_stock ) ? $_manage_stock : '', 'yes' ); ?>
										<?php echo variation_option_required( 'manage_stock' ); //phpcs:ignore ?>/>
                                <label><?php esc_html_e( 'Manage stock?', 'wc-vendors' ); ?></label>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_options', $loop, $variation_id, $variation_data, $variation ); ?>

        <hr style="clear: both;"/>

        <?php do_action( 'wcv_product_variation_before_pricing', $loop, $variation_id, $variation_data, $variation ); ?>

        <!-- Variable pricing  -->
        <div class="wcv-cols-group wcv-horizontal-gutters variable_pricing">
            <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_price', 'no' ) ) : ?>
                <div class="all-50">
                    <div class="control-group">
                        <label><?php esc_html_e( 'Regular price:', 'wc-vendors' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                        <div class="control">
                            <input type="text" size="5" name="variable_regular_price[<?php echo esc_attr( $loop ); ?>]"
									value="<?php echo isset( $_regular_price ) ? wc_format_localized_price( $_regular_price ) : ''; //phpcs:ignore ?>"
                                    class="wc_input_price variable_regular_price"
                                    placeholder="<?php esc_attr_e( 'Variation price (required)', 'wc-vendors' ); ?>"
									<?php echo variation_option_required( 'price' ); //phpcs:ignore ?>
                                    data-parsley-price
                            />
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="all-50">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_sale_price', 'no' ) ) : ?>
                    <div class="control-group">
                        <label><?php esc_html_e( 'Sale price:', 'wc-vendors' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                        <div class="control">
                            <input type="text" size="5" name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]"
                                    class="variable_sale_price wc_input_price"
									value="<?php echo isset( $_sale_price ) ? wc_format_localized_price( $_sale_price ) : ''; //phpcs:ignore ?>"
									<?php echo variation_option_required( 'sale_price' ); //phpcs:ignore ?>
                                    data-parsley-price
                            />
                        </div>
                        <p class="tip"><a href="#" class="sale_schedule"><?php esc_html_e( 'Schedule', 'wc-vendors' ); ?></a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="wcv-cols-group wcv-horizontal-gutters sale_price_dates_fields" style="display:none;">
            <div class="all-50">
                <div class="control-group">
                    <label><?php esc_html_e( 'Sale start date:', 'wc-vendors' ); ?></label>
                    <div class="control">
                        <input type="text" class="sale_price_dates_from wcv-datepicker wcv-init-picker"
                                name="variable_sale_price_dates_from[<?php echo esc_attr( $loop ); ?>]"
								value="<?php echo ! empty( $_sale_price_dates_from ) ? date_i18n( 'Y-m-d', $_sale_price_dates_from ) : ''; //phpcs:ignore ?>"
                                placeholder="<?php echo esc_attr_x( 'From&hellip;', 'placeholder', 'wc-vendors' ); ?> YYYY-MM-DD"
                                maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
								<?php echo variation_option_required( 'sale_start_date' ); //phpcs:ignore ?> />
                    </div>
                </div>
            </div>

            <div class="all-50">
                <div class="control-group">
                    <label><?php esc_html_e( 'Sale end date:', 'wc-vendors' ); ?></label>
                    <div class="control">
                        <input type="text" class="sale_price_dates_to wcv-datepicker wcv-init-picker"
                                name="variable_sale_price_dates_to[<?php echo esc_attr( $loop ); ?>]"
								value="<?php echo ! empty( $_sale_price_dates_to ) ? date_i18n( 'Y-m-d', $_sale_price_dates_to ) : ''; //phpcs:ignore ?>"
                                placeholder="<?php echo esc_attr_x( 'To&hellip;', 'placeholder', 'wc-vendors' ); ?> YYYY-MM-DD"
                                maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
								<?php echo variation_option_required( 'sale_end_date' ); //phpcs:ignore ?>/>
                    </div>
                    <p class="tip"><a href="#" class="cancel_sale_schedule"
                                        style="display:none"><?php esc_html_e( 'Cancel schedule', 'wc-vendors' ); ?></a></p>
                </div>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_pricing', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php do_action( 'wcv_product_variation_before_stock', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>
            <div class="wcv-cols-group wcv-horizontal-gutters show_if_variation_manage_stock" style="display: none;">
                <div class="all-50">
                    <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_stock_qty', 'no' ) ) : ?>
                        <div class="control-group">
                            <label><?php esc_html_e( 'Stock qty:', 'wc-vendors' ); ?></label>
                            <div class="control">
                                <?php $_stock = isset( $_stock ) ? esc_attr( wc_stock_amount( $_stock ) ) : 0; ?>
                                <input type="number" size="5" name="variable_stock[<?php echo esc_attr( $loop ); ?>]"
                                        class="variable_stock" value="<?php echo esc_attr( $_stock ); ?>" step="any"
										<?php echo variation_option_required( 'stock_qty' ); //phpcs:ignore ?> />
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="all-50">
                    <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_allow_backorders', 'no' ) ) : ?>
                        <div class="control-group">
                            <label><?php esc_html_e( 'Allow backorders?', 'wc-vendors' ); ?></label>
                            <div class="control">
								<select name="variable_backorders[<?php echo esc_attr( $loop ); ?>]" <?php echo variation_option_required( 'allow_vackorders' ); //phpcs:ignore ?>>
                                    <?php
                                    foreach ( $parent_data['backorder_options'] as $key => $value ) {
                                        echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key === $_backorders, true, false ) . '>' . esc_html( $value ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="wcv-cols-group wcv-horizontal-gutters hide_if_variation_manage_stock">
            <div class="all-100">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_stock_status', 'no' ) ) : ?>
                    <div class="control-group">
                        <label><?php esc_html_e( 'Stock status', 'wc-vendors' ); ?></label>
                        <div class="control">
							<select name="variable_stock_status[<?php echo esc_attr( $loop ); ?>]" <?php echo variation_option_required( 'stock_status' ); //phpcs:ignore ?> style="width:100%">
                                <?php
                                foreach ( $parent_data['stock_status_options'] as $key => $value ) {
                                    echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key === $_stock_status, true, false ) . '>' . esc_html( $value ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_stock', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php do_action( 'wcv_product_variation_before_weight_dimensions', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php if ( wc_product_weight_enabled() || wc_product_dimensions_enabled() ) : ?>
            <div class="wcv-cols-group wcv-horizontal-gutters">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_weight', 'no' ) ) : ?>
                    <?php if ( wc_product_weight_enabled() ) : ?>
                        <div class="all-50 hide_if_variation_virtual">
                            <div class="control-group">
                                <label><?php echo esc_html_e( 'Weight', 'wc-vendors' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . '):'; ?></label>
                                <div class="control">
                                    <input type="text" size="5" name="variable_weight[<?php echo esc_attr( $loop ); ?>]"
                                            class="variable_weight"
                                            data-parsley-pattern="<?php echo esc_attr( $weight_parsley_pattern ); ?>"
                                            placeholder="<?php echo esc_attr( $parent_data['weight'] ); ?>"
                                            value="<?php echo isset( $_weight ) ? esc_attr( $_weight ) : ''; ?>"
											<?php echo variation_option_required( 'weight' ); //phpcs:ignore ?> />
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_dimensions', 'no' ) ) : ?>
                    <?php if ( wc_product_dimensions_enabled() ) : ?>
                        <div class="all-50 dimensions_field hide_if_variation_virtual">
                            <div class="control-group">
                                <label for="product_length"><?php echo esc_html__( 'Dimensions (L&times;W&times;H)', 'wc-vendors' ) . ' (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . '):'; ?></label>
                                <div class="wcv-cols-group wcv-horizontal-gutters">
                                    <div class="all-33">
                                        <div class="control">
                                            <input id="product_length" class="variable_length" type="text"
                                                    name="variable_length[<?php echo esc_attr( $loop ); ?>]" class="" 
                                                    data-parsley-pattern="<?php echo esc_attr( $weight_parsley_pattern ); ?>"
                                                    value="<?php echo isset( $_length ) ? esc_attr( $_length ) : ''; ?>"
                                                    placeholder="<?php echo esc_attr( $parent_data['length'] ); ?>"
													<?php echo variation_option_required( 'dimensions' ); //phpcs:ignore ?> />
                                        </div>
                                    </div>
                                    <div class="all-33">
                                        <div class="control">
                                            <input class="variable_width" type="text"
                                                    name="variable_width[<?php echo esc_attr( $loop ); ?>]" 
                                                    data-parsley-pattern="<?php echo esc_attr( $weight_parsley_pattern ); ?>"
                                                    value="<?php echo isset( $_width ) ? esc_attr( $_width ) : ''; ?>"
                                                    placeholder="<?php echo esc_attr( $parent_data['width'] ); ?>"
													<?php echo variation_option_required( 'dimensions' ); //phpcs:ignore ?> />
                                        </div>
                                    </div>
                                    <div class="all-33">
                                        <div class="control">
                                            <input class="variable_height" type="text"
                                                    name="variable_height[<?php echo esc_attr( $loop ); ?>]" 
                                                    data-parsley-pattern="<?php echo esc_attr( $weight_parsley_pattern ); ?>"
                                                    value="<?php echo isset( $_height ) ? esc_attr( $_height ) : ''; ?>"
                                                    placeholder="<?php echo esc_attr( $parent_data['height'] ); ?>"
													<?php echo variation_option_required( 'dimensions' ); //phpcs:ignore ?> />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- <p>&nbsp;</p> -->
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php do_action( 'wcv_product_variation_after_weight_dimensions', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php do_action( 'wcv_product_variation_before_shipping_class', $loop, $variation_id, $variation_data, $variation ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters hide_if_variation_virtual">
            <div class="all-100">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_shipping_class', 'no' ) ) : ?>
                    <div class="control-group">
                        <label><?php esc_html_e( 'Shipping class:', 'wc-vendors' ); ?></label>
                        <div class="control">
                            <?php
                            $args = array(
                                'taxonomy'         => 'product_shipping_class',
                                'hide_empty'       => 0,
                                'show_option_none' => __( 'Same as parent', 'wc-vendors' ),
                                'name'             => 'variable_shipping_class[' . $loop . ']',
                                'id'               => '',
                                'selected'         => isset( $shipping_class ) ? esc_attr( $shipping_class ) : '',
                                'echo'             => 0,
                                'required'         => wc_string_to_bool( get_option( 'wcvendors_required_product_variations_shipping_class', 'no' ) ),
                            );

                            echo wp_dropdown_categories( $args );
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <?php do_action( 'wcv_product_variation_after_shipping_class', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php do_action( 'wcv_product_variation_before_tax_class', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php if ( wc_tax_enabled() ) : ?>
            <div class="wcv-cols-group wcv-horizontal-gutters">
                <div class="all-100">
                    <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_tax_class', 'no' ) ) : ?>
                        <div class="control-group">
                            <label><?php esc_html_e( 'Tax class:', 'wc-vendors' ); ?></label>
                            <div class="control">
								<select name="variable_tax_class[<?php echo esc_attr( $loop ); ?>]" <?php echo variation_option_required( 'tax_class' ); //phpcs:ignore ?>>
                                    <option value="parent" <?php selected( is_null( $_tax_class ), true ); ?>><?php esc_html_e( 'Same as parent', 'wc-vendors' ); ?></option>
                                    <?php
                                    foreach ( $parent_data['tax_class_options'] as $key => $value ) {
                                        echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key === $_tax_class, true, false ) . '>' . esc_html( $value ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php do_action( 'wcv_product_variation_after_tax_class', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php do_action( 'wcv_product_variation_before_shipping_class', $loop, $variation_id, $variation_data, $variation ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters">
            <div class="all-100">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_tax_class', 'no' ) ) : ?>
                    <div class="control-group">
                        <label><?php esc_html_e( 'Variation description:', 'wc-vendors' ); ?></label>
                        <div class="control">
                            <textarea name="variable_description[<?php echo esc_attr( $loop ); ?>]" rows="3"
                                        style="width:100%;"
										<?php echo variation_option_required( 'description' ); //phpcs:ignore ?>><?php echo isset( $variation_data['_variation_description'] ) ? esc_textarea( $variation_data['_variation_description'] ) : ''; ?></textarea>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_shipping_class', $loop, $variation_id, $variation_data, $variation ); ?>

        <?php do_action( 'wcv_product_variation_before_download_files', $loop, $variation_id, $variation_data, $variation ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters show_if_variation_downloadable" style="display: none;">
            <div class="all-100">
                <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_download_files', 'no' ) ) : ?>
                    <div class="control-group downloadable_files">
                        <label><?php esc_html_e( 'Downloadable files', 'wc-vendors' ); ?>:</label>
                        <div class="control">
                            <table>
                                <thead>
                                <div>
                                    <th><?php esc_html_e( 'Name', 'wc-vendors' ); ?></th>
                                    <th colspan="2"><?php esc_html_e( 'File URL', 'wc-vendors' ); ?></th>
                                    <th>&nbsp;</th>
                                </div>
                                </thead>
                                <tbody>
                                <?php
                                if ( $_downloadable_files ) {

                                    $file_display_type = get_option( 'wcvendors_file_display', '' );
                                    $input_type        = array(
                                        'file_url'  => 'hidden',
                                        'file_name' => 'text',
                                    );
                                    if ( 'file_url' === $file_display_type ) {
                                        $input_type = array(
                                            'file_url'  => 'text',
                                            'file_name' => 'hidden',
                                        );
                                    }
                                    foreach ( $_downloadable_files as $key => $file ) {

                                        $file_id      = WCVendors_Pro::get_attachment_id( $key );
                                        $file_display = ( 'file_url' === $file_display_type ) ? $file['file'] : basename( $file['file'] );


                                        if ( ! is_array( $file ) ) {
                                            $file = array(
                                                'file' => $file,
                                                'name' => '',
                                            );
                                        }

                                        ?>
                                        <tr>
                                            <td class="file_name">
                                                <div class="control">
                                                    <input type="text" class="input_text" placeholder="<?php esc_attr_e( 'File name', 'wc-vendors' ); ?>"
                                                        name="_wc_variation_file_names[<?php echo esc_attr( $variation_id ); ?>][]"
                                                        value="<?php echo esc_attr( $file['name'] ); ?>"/>
                                                </div>
                                            </td>
                                            <td class="file_url">
                                                <div class="control">
                                                    <input type="<?php echo esc_attr( $input_type['file_url'] ); ?>" class="file_url" name="_wc_variation_file_urls[<?php echo esc_attr( $variation_id ); ?>][]"
                                                        value="<?php echo esc_attr( $file['file'] ); ?>"/>
                                                    <input type="hidden" class="file_id" name="_wc_variation_file_ids[<?php echo esc_attr( $variation_id ); ?>][]"
                                                        value="<?php echo esc_attr( $file_id ); ?>"/>
                                                    <input type="<?php echo esc_attr( $input_type['file_name'] ); ?>" class="file_display" placeholder="<?php esc_attr_e( 'http://', 'wc-vendors' ); ?>"
                                                        name="_wc_variation_file_display[<?php echo esc_attr( $variation_id ); ?>][]"
                                                        value="<?php echo esc_attr( $file_display ); ?>"
														<?php echo variation_option_required( 'download_files' ); //phpcs:ignore ?> />
                                                </div>
                                            </td>

                                            <td class="file_url_choose" width="1%">
                                                <a href="#" class="button upload_file_button"
                                                            data-choose="<?php esc_attr_e( 'Choose file', 'wc-vendors' ); ?>"
                                                            data-update="<?php esc_attr_e( 'Insert file URL', 'wc-vendors' ); ?>">
                                                            <?php echo esc_html( str_replace( ' ', '&nbsp;', __( 'Choose file', 'wc-vendors' ) ) ); ?>
                                                </a>
                                            </td>
                                            <td width="1%">
                                                <a href="#" class="delete">
                                                    <svg class="wcv-icon wcv-icon-md">
                                                        <use xlink:href="<?php echo esc_url( WCV_ASSETS_URL ); ?>svg/wcv-icons.svg#wcv-icon-times"></use>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                                </tbody>
                                <tfoot>
                                <div>
                                    <th colspan="4">
                                        <a href="#" class="button insert" data-row="
                                        <?php
                                        $file         = array(
                                            'file' => '',
                                            'name' => '',
                                        );
                                        $file_id      = '';
                                        $file_display = '';
                                        ob_start();
                                        include 'wcvendors-product-variation-download.php';
                                        echo esc_attr( ob_get_clean() );
                                        ?>
                                        "><?php esc_html_e( 'Add File', 'wc-vendors' ); ?></a>
                                    </th>
                                </div>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="wcv-cols-group wcv-horizontal-gutters show_if_variation_downloadable" style="display: none;">
            <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_download_files', 'no' ) ) : ?>
                <div class="all-50">
                    <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_download_limit', 'no' ) ) : ?>
                        <div class="control-group">
                            <label><?php esc_html_e( 'Download limit:', 'wc-vendors' ); ?></label>
                            <div class="control">
                                <input type="number" size="5" name="variable_download_limit[<?php echo esc_attr( $loop ); ?>]"
                                        class="variable_download_limit"
                                        value="<?php echo isset( $_download_limit ) ? esc_attr( $_download_limit ) : ''; ?>"
                                        placeholder="<?php esc_attr_e( 'Unlimited', 'wc-vendors' ); ?>" step="1"
                                        min="0"
										<?php echo variation_option_required( 'download_limit' ); // phpcs:ignore ?> />
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="all-50">
                    <?php if ( 'yes' !== get_option( 'wcvendors_hide_product_variations_download_expiry', 'no' ) ) : ?>
                        <div class="control-group">
                            <label><?php esc_html_e( 'Download expiry:', 'wc-vendors' ); ?></label>
                            <div class="control">
                                <input type="number" size="5" name="variable_download_expiry[<?php echo esc_attr( $loop ); ?>]"
                                        class="variable_download_expiry" value="<?php echo isset( $_download_expiry ) ? esc_attr( $_download_expiry ) : ''; ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'wc-vendors' ); ?>" step="1"
                                        min="0"
										<?php echo variation_option_required( 'download_expiry' ); //phpcs:ignore ?> />
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php do_action( 'wcv_product_variation_after_download_files', $loop, $variation_id, $variation_data, $variation ); ?>
    </div>
</div>
