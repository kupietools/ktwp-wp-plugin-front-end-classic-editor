<?php
/**
 * Plugin Name: KupieTools Front End Classic Editor
 * Description: Adds the WordPress editor directly on the front end of posts
 * Version: 1.0
 * Author: Michael Kupietz
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add the TinyMCE editor immediately after post content
 */
function live_edit_add_editor($content) {
    // Only show on single posts for logged-in users with editing capabilities
    if (!is_single() || !is_user_logged_in() || !current_user_can('edit_posts')) {
        return $content;
    }
    
    // Get post ID
    $post_id = get_the_ID();
    
    // Get raw content directly from the database
    global $wpdb;
    $raw_content = $wpdb->get_var($wpdb->prepare(
        "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d",
        $post_id
    ));
    
    // Start output buffer
    ob_start();
    
    // Add a wrapper div for the editor with spacing for related posts
    ?>
    <div id="live-edit-area" class="live-edit-editor-container" style="position: relative; margin-top: 80px; border-top: 2px solid #ddd; padding-top: 20px; clear: both;">
        <h3>Edit Post Content</h3>
        <form id="live-edit-form" method="post" action="">
            <?php wp_nonce_field('update_post_' . $post_id, 'live_edit_nonce'); ?>
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            
            <?php
            // Output WordPress TinyMCE editor with raw content
            wp_editor(
                $raw_content,
                'post_content',
                array(
                    'media_buttons' => true,
                    'textarea_name' => 'content',
                    'textarea_rows' => 20,
                    'editor_class' => 'live-edit-content',
                    'tinymce' => array(
                        'wpautop' => true, // Enable auto-formatting to preserve line breaks and paragraphs
                        'plugins' => 'charmap colorpicker hr lists paste tabfocus textcolor fullscreen wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
                        'toolbar1' => 'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link unlink wp_more fullscreen wp_adv',
                        'toolbar2' => 'strikethrough hr forecolor backcolor pastetext removeformat charmap outdent indent undo redo wp_help',
                    ),
                    'quicktags' => true,
                )
            );
            ?>
            
            <div class="live-edit-actions">
                <button type="submit" id="live-edit-save" class="live-edit-button">Save Changes</button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle form submission
        $('#live-edit-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitButton = $('#live-edit-save');
            
            // Disable button during save
            $submitButton.prop('disabled', true).text('Saving...');
            
            // Make sure TinyMCE updates the textarea
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('post_content')) {
                tinyMCE.get('post_content').save();
            }
            
            // Get raw form data
            var formData = new FormData($form[0]);
            formData.append('action', 'live_edit_save_post');
            
            // Send AJAX request
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Post updated successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'));
                        $submitButton.prop('disabled', false).text('Save Changes');
                    }
                },
                error: function() {
                    alert('Error saving post');
                    $submitButton.prop('disabled', false).text('Save Changes');
                }
            });
        });
    });
    </script>
    <?php
    
    // Get the editor HTML
    $editor_html = ob_get_clean();
    
    // Return the original content plus our editor
    return $content . $editor_html;
}

// Use the_content filter with high priority to add editor right after post content
add_filter('the_content', 'live_edit_add_editor', 999);

/**
 * Enqueue scripts and styles
 */
function live_edit_enqueue_scripts() {
    // Only enqueue on single posts for logged-in users with edit capabilities
    if (!is_single() || !is_user_logged_in() || !current_user_can('edit_posts')) {
        return;
    }
    
    // Enqueue our custom CSS
    wp_enqueue_style(
        'live-edit-css', 
        plugin_dir_url(__FILE__) . 'css/live-edit.css',
        array(),
        '1.0'
    );
    
    // Load the WordPress editor components
    wp_enqueue_editor();
    
    // Load media handling scripts
    wp_enqueue_media();
}
add_action('wp_enqueue_scripts', 'live_edit_enqueue_scripts');

/**
 * AJAX handler to save the post content
 */
function live_edit_save_post() {
    // Security checks
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    // Check nonce
    if (!isset($_POST['live_edit_nonce']) || !wp_verify_nonce($_POST['live_edit_nonce'], 'update_post_' . $post_id)) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(array('message' => 'You do not have permission to edit this post'));
        return;
    }
    
    // Get raw content and ensure no magic quotes or slashes are applied
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    
    // WordPress automatically adds slashes to POST data (wp_magic_quotes)
    // We need to remove them to prevent escaping quotes in the content
    $content = wp_unslash($content);
    
    // Update the post directly in the database to avoid all WordPress filters
    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "UPDATE $wpdb->posts SET post_content = %s WHERE ID = %d",
        $content,
        $post_id
    ));
    
    // Clear cache for this post
    clean_post_cache($post_id);
    
    // Always return success since the query doesn't return a result we can check
    wp_send_json_success();
}
add_action('wp_ajax_live_edit_save_post', 'live_edit_save_post');