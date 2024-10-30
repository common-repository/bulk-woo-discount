<?php
/**
 * Woocommerce frontent functinality
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Admin Pages Class.
 *
 * Handles generic Admin functionailties.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
class BWDP_Public {

	/**
	 * Function for `woocommerce_cart_calculate_fees` action-hook.
	 *
	 * @param object $cart return cart object.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_apply_discount_on_checkout( $cart ) {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Check if we are on the checkout page.
		if ( is_checkout() ) {
			$discount_amount = 0;

			// Calculate the discount amount for each item in the cart.
			foreach ( $cart->get_cart() as $cart_item ) {
				$product_id       = $cart_item['product_id'];
				$product_discount = get_post_meta( $product_id, 'bwdp-product-discount', true );

				// Check if the custom field is not empty and is a valid number.
				if ( ! empty( $product_discount ) && is_numeric( $product_discount ) ) {
					$product_discount = floatval( $product_discount );
					$product_price    = $cart_item['data']->get_price();
					$discount_amount += ( $product_price * $product_discount ) / 100;
				}
			}

			// Apply the discount to the cart total.
			$cart->add_fee( __( 'Discount', 'bulk-woo-discount' ), -$discount_amount );
		}
	}


	/**
	 * Adding Hooks
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function add_hooks() {

		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'bwdp_apply_discount_on_checkout' ) );
	}
}
