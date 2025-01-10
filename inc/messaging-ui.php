<?php

// Shortcode for Listing Messages
add_shortcode('messaging_ui', 'messaging_ui_shortcode');

function messaging_ui_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your messages.</p>';
    }

    global $wpdb;
    $current_user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'woocommerce_enquiries';

    // Fetch unique threads for the current user
    $messages = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE recipient_id = %d 
         GROUP BY product_id 
         ORDER BY MAX(created_at) DESC",
        $current_user_id
    ));

    ob_start();
    ?>
    <h2>Your Messages</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Last Message</th>
                <th>Date</th>
                <th>Unread</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($messages)) : ?>
                <?php foreach ($messages as $message): ?>
                    <?php
                    $unread_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_name 
                         WHERE product_id = %d AND recipient_id = %d AND is_read = 0",
                        $message->product_id,
                        $current_user_id
                    ));
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url(get_permalink($message->product_id)); ?>">
                                <?php echo esc_html(get_the_title($message->product_id)); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html(wp_trim_words($message->message, 10)); ?></td>
                        <td><?php echo esc_html(date('Y-m-d H:i', strtotime($message->created_at))); ?></td>
                        <td><?php echo esc_html($unread_count); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['message_id' => $message->id, 'product_id' => $message->product_id], site_url('view-message'))); ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No messages found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

// Shortcode for Viewing and Replying to a Message
add_shortcode('view_message_page', 'view_message_page_shortcode');

function view_message_page_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view this message.</p>';
    }

    if (!isset($_GET['product_id'])) {
        return '<p>No product conversation selected.</p>';
    }

    global $wpdb;
    $product_id = intval($_GET['product_id']);
    $current_user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'woocommerce_enquiries';

    // Fetch messages for this product conversation
    $messages = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE product_id = %d AND (recipient_id = %d OR sender_id = %d) ORDER BY created_at ASC",
        $product_id,
        $current_user_id,
        $current_user_id
    ));

    // Mark messages as read
    $wpdb->query($wpdb->prepare(
        "UPDATE $table_name SET is_read = 1 WHERE product_id = %d AND recipient_id = %d",
        $product_id,
        $current_user_id
    ));

    ob_start();
    ?>
    <h2>Conversation for: <?php echo esc_html(get_the_title($product_id)); ?></h2>
    <div>
        <?php if (!empty($messages)) : ?>
            <?php foreach ($messages as $message): ?>
                <div>
                    <strong><?php echo esc_html(get_userdata($message->sender_id)->display_name); ?>:</strong>
                    <p><?php echo nl2br(esc_html($message->message)); ?></p>
                    <small><?php echo esc_html(date('Y-m-d H:i', strtotime($message->created_at))); ?></small>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No messages found in this conversation.</p>
        <?php endif; ?>
    </div>

    <h3>Reply</h3>
    <form method="post">
        <textarea name="reply_message" rows="5" style="width: 100%;" required></textarea>
        <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
        <button type="submit">Send Reply</button>
    </form>
    <?php

    // Handle reply submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
        $reply_message = sanitize_textarea_field($_POST['reply_message']);

        $wpdb->insert($table_name, [
            'sender_id' => $current_user_id,
            'recipient_id' => $messages[0]->sender_id,
            'message' => $reply_message,
            'subject' => 'Re: ' . $messages[0]->subject,
            'product_id' => $product_id,
            'created_at' => current_time('mysql'),
            'is_read' => 0,
        ]);

        echo '<p>Reply sent successfully!</p>';
    }

    return ob_get_clean();
}
