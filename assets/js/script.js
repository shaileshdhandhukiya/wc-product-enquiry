// jQuery(document).ready(function($) {
//     // Delete Single Message
//     $(".delete-single-message").click(function() {
        
//         if (!confirm("Are you sure you want to delete this message?")) return;

//         var messageId = $(this).data("id");
//         var messageDiv = $(this).closest("div");

//         $.ajax({
//             type: "POST",
//             url: "<?php echo admin_url('admin-ajax.php'); ?>",
//             data: {
//                 action: "delete_single_message",
//                 message_id: messageId,
//                 security: "<?php echo wp_create_nonce('delete_message_nonce'); ?>"
//             },
//             success: function(response) {
//                 if (response.success) {
//                     messageDiv.fadeOut();
//                 } else {
//                     alert(response.data);
//                 }
//             }
//         });
//     });

//     // Delete Entire Thread
//     $("#delete-thread").click(function() {
//         if (!confirm("Are you sure you want to delete the entire conversation?")) return;

//         var productId = $(this).data("product-id");

//         $.ajax({
//             type: "POST",
//             url: "<?php echo admin_url('admin-ajax.php'); ?>",
//             data: {
//                 action: "delete_thread",
//                 product_id: productId,
//                 security: "<?php echo wp_create_nonce('delete_message_nonce'); ?>"
//             },
//             success: function(response) {
//                 if (response.success) {
//                     alert("Thread deleted successfully!");
//                     window.location.href = "<?php echo site_url('your-message-list-page'); ?>";
//                 } else {
//                     alert(response.data);
//                 }
//             }
//         });
//     });
// });