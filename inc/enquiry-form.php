<?php


// Hook the custom button function to the YITH Wishlist table

add_action('woocommerce_single_product_summary', 'add_enquiry_button', 35);

function add_enquiry_button()
{

    if (!is_user_logged_in()) {
        // echo '<p><a href="' . wc_get_page_permalink('myaccount') . '" class="button">Please log in to send an enquiry</a></p>';
        echo '<p><a href="#" class="button woo-login-popup">Please log in to send an enquiry</a></p>';
        return;
    }

    if (is_singular('product')) {
        $product_id = get_the_ID();
    } else {
        $product_id = isset( $item['prod_id'] ) ? $item['prod_id'] : get_query_var( 'product_id' );
    }
    
    if (!$product_id) {
        return; // Stop if no product ID is found
    }

    // $product_id = get_the_ID();

    // Get current user ID
    $current_user_id = get_current_user_id();

    // Get the product author's ID
    $author_id = get_post_field('post_author', $product_id);

    // If the logged-in user is the author of this product, show warning
    if ($current_user_id == $author_id) {
        echo '<p class="warning-message">You cannot send an enquiry for your own product.</p>';
        return;
    } 
    ?>

    <button id="enquiry-button" class="button">Send Enquiry</button>
    <div id="enquiry-popup-overlay"></div> <!-- Overlay -->
    <div id="enquiry-popup">
        <span id="close-enquiry-popup">&times;</span> <!-- Close Button -->
        <form id="enquiry-form">
            <label>Message:</label>
            <textarea name="message" required></textarea>
            <input type="hidden" name="product_id" value="<?php echo get_the_ID(); ?>">
            <button type="submit" class="button">Send</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const enquiryButton = document.getElementById('enquiry-button');
            const enquiryPopup = document.getElementById('enquiry-popup');
            const overlay = document.getElementById('enquiry-popup-overlay');
            const closePopup = document.getElementById('close-enquiry-popup');
            const enquiryForm = document.getElementById('enquiry-form');

            // Show Modal
            enquiryButton.addEventListener('click', function() {
                enquiryPopup.style.display = 'block';
                overlay.style.display = 'block';
            });

            // Close Modal on overlay click
            overlay.addEventListener('click', function() {
                enquiryPopup.style.display = 'none';
                overlay.style.display = 'none';
            });

            // Close Modal on close button click
            closePopup.addEventListener('click', function() {
                enquiryPopup.style.display = 'none';
                overlay.style.display = 'none';
            });

            // Submit the enquiry form
            enquiryForm.addEventListener('submit', function(e) {

                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'submit_enquiry');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // alert(data.data);
                        if (data.success) {
                            enquiryPopup.style.display = 'none';
                            overlay.style.display = 'none';

                            // Reset the form fields
                            enquiryForm.reset();
                        }
                    });
            });
        });
    </script>
    <?php
}


// Handle AJAX Enquiry Submission
add_action('wp_ajax_submit_enquiry', 'handle_enquiry_submission');

function handle_enquiry_submission()
{

    if (!is_user_logged_in()) {
        wp_send_json_error('Please log in to send an enquiry.');
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'woocommerce_enquiries';

    $product_id = intval($_POST['product_id']);
    $message = sanitize_textarea_field($_POST['message']);
    $subject = 'Enquiry About ' . get_the_title($product_id);
    $sender_id = get_current_user_id();
    $recipient_id = get_post_field('post_author', $product_id);

    if ($sender_id === $recipient_id) {
        wp_send_json_error('You cannot send an enquiry to yourself.');
    }

    $result = $wpdb->insert($table_name, [
        'product_id' => $product_id,
        'sender_id' => $sender_id,
        'recipient_id' => $recipient_id,
        'subject' => $subject,
        'message' => $message,
        'created_at' => current_time('mysql'),
    ]);

    if ($result) {

        send_enquiry_notification($recipient_id, $subject, $message);

        wp_send_json_success('Enquiry sent successfully!');
    } else {

        wp_send_json_error('Failed to send enquiry.');
    }
}

/**
 * Send email notifications to both recipient and sender about the enquiry
 * 
 * @param int $recipient_id The user ID of the recipient
 * @param string $subject The subject of the enquiry
 * @param string $message The message content of the enquiry
 * @return void
 */
function send_enquiry_notification($recipient_id, $subject, $message)
{
    $recipient_email = get_the_author_meta('user_email', $recipient_id);
    $sender = wp_get_current_user();

    // Email details
    $sender_name = $sender->display_name;
    $sender_email = $sender->user_email;
    $product_id = intval($_POST['product_id']);
    $product_name = get_the_title($product_id);

    // Email content
    $email_message = "
        Hi Admin,

        Please find the details of the customer enquiry below:

        Name: {$sender_name}
        Product: {$product_name}

        Message:
        {$message}

        Regards,
        WatchMarket Team
    ";

    // Send email to the recipient
    wp_mail($recipient_email, $subject, $email_message);

    // Send confirmation to the sender
    $confirmation_subject = "Enquiry Sent: {$product_name}";
    $confirmation_message = "
        Hi {$sender_name},

        Thank you for your enquiry. Below are the details of your message:

        Product: {$product_name}
        Message:
        {$message}

        Regards,
        WatchMarket Team
    ";
    wp_mail($sender_email, $confirmation_subject, $confirmation_message);
}

