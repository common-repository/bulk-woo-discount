<?php
/**
 * Woocommerce admin functinality.
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Pages Class
 *
 * Handles generic Admin functionailties
 *
 * @package Bulk_Woo_Discount
 * @since 1.1.0
 */
class BWDP_Admin {

	/**
	 * Enqueue a script in the WordPress admin on edit.php.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_enqueue_admin_script() {
		wp_register_style( 'bwdp-admin', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', false, '1.1.0' );
		wp_enqueue_style( 'bwdp-admin' );
	}

	/**
	 * Product cpt add meta box
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_meta_box() {
		add_meta_box(
			'bwdp-product-discount',
			__( 'Product Price Discount', 'bulk-woo-discount' ),
			array( $this, 'bwdp_render_custom_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Prints the box content.
	 *
	 * @param WP_Post $post The object for the current post/page.
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_render_custom_meta_box( $post ) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field( 'bwdp_meta_box_data', 'bwdp_meta_box_data' );

		/*
		* Use get_post_meta() to retrieve an existing value
		* from the database and use the value for the form.
		*/
		$value = get_post_meta( $post->ID, 'bwdp-product-discount', true );

		echo '<label for="member_new_field">';
		esc_html_e( 'Product Discount', 'bulk-woo-discount' );
		echo '</label> ';
		echo '<input type="text" id="bwdp-product-discount" name="bwdp-product-discount" value="' . esc_attr( $value ) . '" size="25" />';
	}

	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_meta_box_data( $post_id ) {

		if ( ! isset( $_POST['bwdp_meta_box_data'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bwdp_meta_box_data'] ) ), 'bwdp_meta_box_data' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
		}

		if ( ! isset( $_POST['bwdp-product-discount'] ) ) {
			return;
		}

		$product_discount = sanitize_text_field( wp_unslash( $_POST['bwdp-product-discount'] ) );

		update_post_meta( $post_id, 'bwdp-product-discount', $product_discount );
	}

	/**
	 * An option in the dropdown.
	 *
	 * @param array $bulk_actions register discount.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_register_actions( $bulk_actions ) {
		$bulk_actions['product-discount-action'] = __( 'Product discount', 'bulk-woo-discount' );
		$bulk_actions['product-removediscount-action'] = __( 'Remove discount', 'bulk-woo-discount' );
		return $bulk_actions;
	}

	/**
	 * Handles the custom bulk action for product discounts.
	 *
	 * @param string $redirect_to The URL to redirect to after the bulk action is processed.
	 * @param string $doaction    The name of the bulk action being processed.
	 * @param array  $post_ids    Array of post IDs on which the bulk action is performed.
	 *
	 * @return string The modified redirect URL.
	 */
	public function bwdp_action_handler( $redirect_to, $doaction, $post_ids ) {
		if ( 'product-discount-action' === $doaction ) {
			$bwdp_price = get_option( 'bwdp_price' );
			if ( ! empty( $bwdp_price ) ) {
				foreach ( $post_ids as $post_id ) {
					update_post_meta( $post_id, 'bwdp-product-discount', $bwdp_price );
				}
			}
			$redirect_to = add_query_arg( 'bulk_product-discount', count( $post_ids ), $redirect_to );
		}
		if ( 'product-removediscount-action' === $doaction ) {
			foreach ( $post_ids as $post_id ) {
				update_post_meta( $post_id, 'bwdp-product-discount', null );
			}
			$redirect_to = add_query_arg( 'bulk_product-discount', count( $post_ids ), $redirect_to );
		}
		return $redirect_to;
	}

	/**
	 * An setting option is empty show admin notice.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_action_notices() {
		if ( ! empty( $_REQUEST['bulk_product-discount'] ) ) {
			$bwdp_price = get_option( 'bwdp_price' );
			if ( empty( $bwdp_price ) ) { ?>
				<div class="updated notice is-dismissible">
					<p><?php esc_html_e( 'Woocommerce price discount Settings field is empty please check.', 'bulk-woo-discount' ); ?></p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Register an Admin Menu.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_menu_page() {
		add_menu_page(
			__( 'Product Discount ( In Percentage (%))', 'bulk-woo-discount' ),    // Page Title.
			__( 'Woocommerce Products Discount', 'bulk-woo-discount' ),        // Menu Title.
			'manage_options',     // Capability (who can access).
			'bulk-woo-discount-settings',    // Menu Slug.
			array( $this, 'bwdp_menu_page_callback' ), // Callback function to display content.
			'dashicons-admin-settings', // Icon (optional).
			30                     // Position in the menu.
		);
	}

	/**
	 * Create a Callback Function for the Admin Menu Page.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_menu_page_callback() {
		?>
		<div class="wrap">
			<form method="post" action="options.php">
				<?php
				settings_fields( 'bwdp_settings_group' );
				do_settings_sections( 'bwdp_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add Settings Fields.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_settings_init() {

		// Register a setting and its sanitization callback.
		register_setting(
			'bwdp_settings_group',
			'bwdp_price',
			array( $this, 'bwdp_sanitize_callback' )
		);

		// Add a section for your settings.
		add_settings_section(
			'bwdp_settings_section',
			__( 'Product Discount (In Percentage (%))', 'bulk-woo-discount' ),
			array( $this, 'bwdp_section_callback' ),
			'bwdp_settings'
		);

		// Add a field for your setting.
		add_settings_field(
			'bwdp_price',
			__( 'Price Discount', 'bulk-woo-discount' ),
			array( $this, 'bwdp_field_callback' ),
			'bwdp_settings',
			'bwdp_settings_section'
		);
	}

	/**
	 * Sanitize and validate the input here.
	 *
	 *  @param string $value return value.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_sanitize_callback( $value ) {
		return $value;
	}

	/**
	 * Section description (if needed).
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_section_callback() {
	}

	/**
	 * Section field call back function.
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_field_callback() {
		$bwdp_price = get_option( 'bwdp_price' );
		printf(
			'<input type="number" name="bwdp_price" required value="%s" />',
			esc_attr( $bwdp_price )
		);
	}

	/**
	 * Add custom column to the admin table for Products post type.
	 *
	 * @param array $columns Columns array.
	 * @return array Modified columns array.
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdp_columns_head($columns) {
		$new_columns = array();
		// Copy existing columns
		foreach ($columns as $key => $value) {
			$new_columns[$key] = $value;
			if ($key === 'sku') {
				// Add custom column after 'sku' column
				$new_columns['bwdp-pro-dis'] = __('Product Discount', 'your-text-domain');
			}
		}
		return $new_columns;
	}
	
	/**
	 * Display content for custom column in the admin table.
	 *
	 * @param string $column_name Column name.
	 * @param int $post_id Post ID.
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function bwdb_columns_discount_price($column_name, $post_id) {
		if ($column_name === 'bwdp-pro-dis') {
			$bwdp_dis_data = get_post_meta($post_id, 'bwdp-product-discount', true);
			if(!empty($bwdp_dis_data)){
				echo esc_html($bwdp_dis_data."%"); // Display custom data in the column
			}
			else{
				echo esc_html('—');
			}
		}
	}
	
	/**
	 * Adding Hooks
	 *
	 * @package Bulk_Woo_Discount
	 * @since 1.1.0
	 */
	public function add_hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'bwdp_enqueue_admin_script' ) );

		add_action( 'add_meta_boxes', array( $this, 'bwdp_meta_box' ) );

		add_action( 'save_post', array( $this, 'bwdp_meta_box_data' ) );

		add_filter( 'bulk_actions-edit-product', array( $this, 'bwdp_register_actions' ) );

		add_filter( 'handle_bulk_actions-edit-product', array( $this, 'bwdp_action_handler' ), 10, 3 );

		add_action( 'admin_menu', array( $this, 'bwdp_menu_page' ) );

		add_action( 'admin_init', array( $this, 'bwdp_settings_init' ) );

		add_action( 'admin_notices', array( $this, 'bwdp_action_notices' ) );
		
		add_filter('manage_edit-product_columns', array( $this, 'bwdp_columns_head') );

		add_action('manage_product_posts_custom_column', array( $this, 'bwdb_columns_discount_price'), 10, 2  );

	}
}
