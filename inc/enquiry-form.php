<?php
add_action('woocommerce_single_product_summary', 'add_enquiry_button', 35);

function add_enquiry_button()
{
    if (!is_user_logged_in()) {
        echo '<p><a href="' . wp_login_url(get_permalink()) . '" class="button">Please log in to send an enquiry</a></p>';
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
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const enquiryButton = document.getElementById('enquiry-button');
            const enquiryPopup = document.getElementById('enquiry-popup');
            const overlay = document.getElementById('enquiry-popup-overlay');
            const closePopup = document.getElementById('close-enquiry-popup');

            // Show Modal
            enquiryButton.addEventListener('click', function () {
                enquiryPopup.style.display = 'block';
                overlay.style.display = 'block';
            });

            // Close Modal on overlay click
            overlay.addEventListener('click', function () {
                enquiryPopup.style.display = 'none';
                overlay.style.display = 'none';
            });

            // Close Modal on close button click
            closePopup.addEventListener('click', function () {
                enquiryPopup.style.display = 'none';
                overlay.style.display = 'none';
            });

            // Submit the enquiry form
            document.getElementById('enquiry-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'submit_enquiry');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.data);
                    if (data.success) {
                        enquiryPopup.style.display = 'none';
                        overlay.style.display = 'none';
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
    $recipient_email    = get_the_author_meta('user_email', $recipient_id);
    $sender_email       = wp_get_current_user()->user_email;

    wp_mail($recipient_email, 'New Enquiry Received', $message);
    wp_mail($sender_email, 'Enquiry Sent', 'Your enquiry has been sent successfully.');
}
