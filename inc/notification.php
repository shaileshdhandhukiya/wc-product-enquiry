<?php

// Shortcode for Message Notification Icon
add_shortcode('message_notification_icon', 'message_notification_icon_shortcode');

function message_notification_icon_shortcode() {

    ob_start();
    ?>
    <style>
        .message-notification .count {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 12px;
        }
        .message-notification a {
            text-decoration: none;
            color: inherit;
        }
    </style>
    <div class="message-notification">
        <a href="<?php echo esc_url(site_url('/messages')); ?>">
            <i class="fa fa-envelope" aria-hidden="true" style="color: #fff;"></i>
            <span class="count" id="message-count" style="display: none;"></span>
        </a>
    </div>

    <script>
        jQuery(document).ready(function($) {
            function updateMessageCount() {
                $.post(
                    '<?php echo admin_url('admin-ajax.php'); ?>',
                    { action: 'get_unread_message_count' },
                    function(response) {
                        if (response.success) {
                            const count = response.data.count;
                            const countElement = $('#message-count');
                            if (count > 0) {
                                countElement.text(count).show();
                            } else {
                                countElement.hide();
                            }
                        }
                    }
                );
            }

            // Initial load
            updateMessageCount();

            // Optionally poll for updates (every 30 seconds)
            setInterval(updateMessageCount, 3000);
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_get_unread_message_count', 'get_unread_message_count');
add_action('wp_ajax_nopriv_get_unread_message_count', 'get_unread_message_count');

function get_unread_message_count() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in.');
    }

    global $wpdb;
    $current_user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'woocommerce_enquiries';

    // Count unread messages
    $message_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE recipient_id = %d AND is_read = 0",
        $current_user_id
    ));

    wp_send_json_success(['count' => $message_count]);
}

