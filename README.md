# WooCommerce Product Enquiry - Messaging System

## Overview

This plugin adds a messaging system where customers can send product inquiries, and sellers can reply. Messages are grouped into separate threads per product. The plugin includes two shortcodes:

1. **[messaging_ui]**: Displays the list of message threads for the logged-in user.
2. **[view_message_page]**: Displays the details of a selected message thread and allows the user to reply to messages.

## Features

- Separate threads for each product inquiry.
- Real-time message updates for both customers and sellers.
- Clear reply form and message list refresh after a reply.
- Unread message tracking.
  
## Installation

1. Download or clone this repository to your WordPress installation.
2. Place the files in the `wp-content/plugins` directory.
3. Activate the plugin from the WordPress admin panel.
4. The shortcodes `[messaging_ui]` and `[view_message_page]` can be used in posts, pages, or widgets.

## Shortcodes

### [messaging_ui]

**Purpose**: Displays a list of message threads for the logged-in user.

#### Usage

Place the shortcode `[messaging_ui]` where you want to display the list of messages (e.g., in a page or post).

```html
[messaging_ui]
```

#### Features

- Displays a table with columns:
  - **Product**: The product associated with the inquiry.
  - **Last Message**: The timestamp of the last message in the thread.
  - **Participant**: The sender or recipient of the message.
  - **Actions**: A link to view the full conversation.
  
- If no messages are found, a "No messages found" message will be displayed.

#### Requirements

- Logged-in user: Only logged-in users can access this page.
- Thread system: Messages are grouped based on the `thread_id` that associates them with a particular product.

---

### [view_message_page]

**Purpose**: Displays the details of a selected message thread and allows the user to reply to messages.

#### Usage

Place the shortcode `[view_message_page]` in a page or post where you want users to view individual threads.

```html
[view_message_page]
```

#### Parameters

- The `thread_id` parameter is passed in the URL (e.g., `view-message?thread_id=123`).
- If no `thread_id` is passed, an error message will be shown: "No thread selected."

#### Features

- Displays the entire conversation for the selected thread, including:
  - The sender's name and message content.
  - The timestamp of each message.
  - A reply form for sending a new message.

- Upon submission, the reply is added to the database, and the message form is cleared. The page is automatically reloaded to show the new message.

- When the thread is viewed by the seller, all unread messages are marked as read.

#### Requirements

- Logged-in user: Only logged-in users can view message threads.
- `thread_id`: The unique identifier of the message thread passed in the URL.

---

## Database Table Structure

To support the messaging system, you will need a table `wp_woocommerce_enquiries` with the following structure:

```sql
CREATE TABLE wp_woocommerce_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    product_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0
);
```

#### Columns Description:

- **id**: The unique identifier for each message.
- **thread_id**: A unique ID to group messages in the same thread.
- **sender_id**: The ID of the user sending the message.
- **recipient_id**: The ID of the user receiving the message.
- **product_id**: The ID of the product related to the inquiry.
- **message**: The content of the message.
- **created_at**: The timestamp when the message was created.
- **is_read**: A flag to indicate whether the message has been read (1 for read, 0 for unread).

---

## How to Use

1. **Create a New Page for Listing Messages**: 
   - Add the `[messaging_ui]` shortcode to a new or existing page where you want the user to see the list of message threads.
   
2. **Create a New Page for Viewing Messages**: 
   - Add the `[view_message_page]` shortcode to another page where users will view individual message threads.
   
3. **Test the Functionality**: 
   - Ensure that when a customer sends a message, the seller can see it in the list and view the conversation.
   - Ensure that when the seller replies, the conversation is updated and displayed correctly.

---

## Troubleshooting

1. **Messages Not Displaying Correctly**:
   - Check the `thread_id` to ensure that messages are grouped correctly.
   - Verify that the database table has been created with the required structure.
   
2. **No Messages Found**:
   - Ensure that users have sent messages or inquiries and that the `sender_id` and `recipient_id` are correctly set.
   
3. **Form Not Clearing After Submission**:
   - Ensure that the JavaScript for clearing the form and reloading the page is correctly added to the `view_message_page` shortcode.

---

## Conclusion

This messaging system allows customers to send product inquiries and receive replies in a seamless and organized manner. The two shortcodes provided allow you to manage the list of messages and view message threads, all while tracking unread messages and grouping them by product.