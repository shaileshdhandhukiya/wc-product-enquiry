<?php

add_action('wp_ajax_delete_single_message', 'delete_single_message_callback');
add_action('wp_ajax_nopriv_delete_single_message', 'delete_single_message_callback');

function delete_single_message_callback() {
    check_ajax_referer('delete_message_nonce', 'security');

    if (!isset($_POST['message_id']) || !is_numeric($_POST['message_id'])) {
        wp_send_json_error("Invalid message ID.");
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'woocommerce_enquiries';
    $message_id = intval($_POST['message_id']);
    $current_user_id = get_current_user_id();

    // Check if user has permission
    $message = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d AND (sender_id = %d OR recipient_id = %d)",
        $message_id,
        $current_user_id,
        $current_user_id
    ));

    if (!$message) {
        wp_send_json_error("You don't have permission to delete this message.");
    }

    // Delete message
    $wpdb->delete($table_name, ['id' => $message_id]);

    wp_send_json_success("Message deleted successfully.");
}


add_action('wp_ajax_delete_thread', 'delete_thread_callback');
add_action('wp_ajax_nopriv_delete_thread', 'delete_thread_callback');

function delete_thread_callback() {
    
    check_ajax_referer('delete_message_nonce', 'security');

    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        wp_send_json_error("Invalid product ID.");
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'woocommerce_enquiries';
    $product_id = intval($_POST['product_id']);
    $current_user_id = get_current_user_id();

    // Delete all messages related to the product between the users
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_name 
         WHERE product_id = %d 
         AND (sender_id = %d OR recipient_id = %d)",
        $product_id,
        $current_user_id,
        $current_user_id
    ));

    wp_send_json_success("Conversation deleted successfully.");
}
