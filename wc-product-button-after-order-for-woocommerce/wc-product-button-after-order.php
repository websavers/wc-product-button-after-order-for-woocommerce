<?php
/*
Plugin Name: WC Product button after order for WooCommerce
Plugin URI: 
Description: Ensures order continuity to a configurable external URL by adding a call-to-action button on the order thank you page, order confirmation email, and in my account
Author: Websavers Inc.
Author URI: https://websavers.ca
Contributors: jas8522
Version: 1.0.0
License: MIT
Text Domain: wc-product-button
*/

// Inspired by https://github.com/Ohar/wc-field-vendor-url/

// Product admin: Display fields
add_action( 'woocommerce_product_options_general_product_data', 'wcpb_add_custom_woocommerce_general_field_cta_fields' );

// Product admin: Save Fields
add_action( 'woocommerce_process_product_meta', 'wcpb_save_custom_woocommerce_general_field_cta_fields' );

// Order placed: Thank You Page AND Order Confirmation Email
add_action( 'woocommerce_order_item_meta_end', 'wcpb_add_to_order_item_meta_end', 10, 3 );

// My Account: Dashboard
add_action( 'woocommerce_account_dashboard', 'wcpb_add_to_my_account_dashboard' );


function wcpb_add_custom_woocommerce_general_field_cta_fields() {

    global $woocommerce, $post;

    $product = get_product($post->ID);

    if ($product->product_type != 'simple' || !$product->is_virtual()) {
        return '';
    }
	
   // $link = wcpb_get_button_html($post->ID);

    echo '<div class="options_group">';

	woocommerce_wp_text_input(
		array(
			'id'                => 'cta_url',
			//'label'             => __( 'After Order CTA URL', 'wc-product-button' ).$link,
            'label'             => __( 'After Order CTA URL', 'wc-product-button' ),
			'placeholder'       => 'https://example.com',
			'desc_tip'          => 'true',
			'description'       => __( 'URL for CTA button to display after product has been ordered', 'wc-product-button' ),
			'type'              => 'url',
			'data_type'         => 'url'
        )
	);
    woocommerce_wp_text_input(
        array(
			'id'                => 'cta_text',
            //'label'             => __( 'After Order CTA Text', 'wc-product-button' ).$link,
			'label'             => __( 'After Order CTA Text', 'wc-product-button' ),
			'placeholder'       => 'Go to URL',
			'desc_tip'          => 'true',
			'description'       => __( 'Text for CTA button to display after product has been ordered', 'wc-product-button' ),
			'type'              => 'text',
			'data_type'         => 'text'
		)
    );

	echo '</div>';
}

function wcpb_save_custom_woocommerce_general_field_cta_fields( $post_id ) {
	update_post_meta( $post_id, 'cta_url', esc_attr( $_POST['cta_url'] ) );
    update_post_meta( $post_id, 'cta_text', esc_attr( $_POST['cta_text'] ) );
}


function wcpb_add_to_order_item_meta_end($item_id, $item, $order) {
    $button = wcpb_get_button_html($item->get_product_id());
    echo "<p>$button</p>";
}

// https://www.businessbloomer.com/woocommerce-display-products-purchased-user/
function wcpb_add_to_my_account_dashboard() {
    // GET CURRENT USER
    if ( 0 == get_current_user_id() ) return;
   
    // GET USER ORDERS (COMPLETED + PROCESSING)
    $customer_orders = wc_get_orders( array(
        'limit' => -1,
        'customer_id' => get_current_user_id(),
        'status' => wc_get_is_paid_statuses(),
        'return' => 'ids',
    ) );
   
    // LOOP THROUGH ORDERS AND GET PRODUCT IDS
    if ( ! $customer_orders ) return;
    foreach ( $customer_orders as $customer_order_id ) {
        $order = wc_get_order( $customer_order_id );
        $items = $order->get_items();
        foreach ( $items as $item ) {
            echo wcpb_get_button_html($item->get_product_id());
        }
    }
}

/**
 * HELPER FUNCTIONS
 * form order: 5 needed for my account page display using Avada theme
 */

function wcpb_get_button_html($product_id){
    $cta_url = get_post_meta($product_id, 'cta_url', true);
    $cta_text = get_post_meta($product_id, 'cta_text', true);
    return $cta_url ? " <form action='$cta_url' target='_blank' style='order: 5'><button class='button fusion-button' type='submit' id='wcpb_cta_button' style='margin: 7px; float: none;'>$cta_text</button></form>" : false;
}