<?php
/*
Plugin Name: WooCommerce Product Enquiry
Description: Adds a custom messaging system with notifications and email alerts for woocommerce product enquiries.
Version: 1.0.0
Author: Shailesh Dhandhukiya
Author URI: https://github.com/shaileshdhandhukiya/
Text Domain: wc-product-enquiry
Domain Path: /languages
woocommerce: 5.0.0
php: 7.4.0
tested: 5.7.2
*/

// Ensure direct access is restricted
if (!defined('ABSPATH')) {
    exit;
}

// enqueue css and js
add_action('wp_enqueue_scripts', 'enqueue_wc_product_enquiry_assets');

function enqueue_wc_product_enquiry_assets() {
    wp_enqueue_style('wc-product-enquiry', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], '1.0.0');
    wp_enqueue_script('wc-product-enquiry', plugin_dir_url(__FILE__) . 'assets/js/script.js', ['jquery'], '1.0.0', true);
}


// Notification
require_once plugin_dir_path(__FILE__) . 'inc/notification.php';

// Enquiry Button and Popup Form
require_once plugin_dir_path(__FILE__) . 'inc/enquiry-form.php';

// Messaging UI
require_once plugin_dir_path(__FILE__) . 'inc/messaging-ui.php';

/* 
* Create custom enquiries table
 */
register_activation_hook(__FILE__, 'create_custom_enquiries_table');

function create_custom_enquiries_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'woocommerce_enquiries';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        sender_id bigint(20) NOT NULL,
        recipient_id bigint(20) NOT NULL,
        product_id bigint(20) NOT NULL,
        subject varchar(255) NOT NULL,
        message text NOT NULL,
        is_read tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/* 
* delete custom enquiries table
 */
register_deactivation_hook(__FILE__, 'delete_custom_enquiries_table');

function delete_custom_enquiries_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'woocommerce_enquiries';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
