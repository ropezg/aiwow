<?php
/**
 * Plugin Name: AI Wow Plugin
 * Plugin URI: https://aiprojectpad.com/ai-wow-plugin
 * Description: AI Wow Plugin utilizes OpenAI's API to generate key takeaways for your WordPress posts.
 * Version: 1.0
 * Author: Petar R
 * Author URI: https://aiprojectpad.com
 */


function ai_wow_plugin_info_page() {
    ?>
    <div class="wrap">
        <h2>AI Wow Plugin</h2>
        <p>This plugin provides advanced AI features to enhance your WordPress experience.</p>
        <p>Features include:</p>
        <ul>
            <li>Generate key takeaways for your articles automatically.</li>
            <li>Configure your OpenAI API key for personalized AI services.</li>
        </ul>
        <p>For more information and documentation, visit the <a href="http://yourwebsite.com/ai-wow-plugin">official plugin page</a>.</p>
    </div>
    <?php
}

function ai_wow_plugin_add_admin_menu() {
    // Main menu for the AI Wow Plugin
    add_menu_page(
        'AI Wow Plugin Settings', // Page title
        'AI Wow Plugin', // Menu title
        'manage_options', // Capability required to see this option
        'ai-wow-plugin', // Menu slug
        'ai_wow_plugin_info_page', // Function that outputs the main plugin info page content
        null, // Icon URL (optional)
        6 // Position (optional)
    );

    // Submenu for API Key Settings
    add_submenu_page(
        'ai-wow-plugin', // Parent slug
        'API Key Settings', // Page title
        'API Key Settings', // Menu title
        'manage_options', // Capability
        'ai-wow-plugin-api-key-settings', // Menu slug
        'ai_wow_plugin_settings_page' // Function that outputs the settings page content
    );

    // Submenu for Generating Key Takeaways
    add_submenu_page(
        'ai-wow-plugin', // Parent slug
        'Generate Key Takeaways', // Page title
        'Generate Key Takeaways', // Menu title
        'edit_posts', // Capability
        'ai-wow-plugin-generate-key-takeaways', // Menu slug
        'ai_wow_plugin_generate_key_takeaways_page' // Function that outputs the key takeaways page content
    );
}
add_action('admin_menu', 'ai_wow_plugin_add_admin_menu');

function ai_wow_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h2>AI Wow Plugin API Key Settings</h2>
        <?php
        // Check if the settings have been updated
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            // Add settings saved message
            echo '<div id="message" class="updated notice is-dismissible"><p>The key has been added.</p></div>';
        }
        ?>
        <form action="options.php" method="POST">
            <?php
            settings_fields('ai_wow_plugin_options_group');
            do_settings_sections('ai-wow-plugin-api-key-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function ai_wow_plugin_settings_init() {
    register_setting('ai_wow_plugin_options_group', 'ai_wow_plugin_options', 'ai_wow_plugin_options_validate');

    add_settings_section(
        'ai_wow_plugin_main_section', // Section ID
        'API Settings', // Title
        'ai_wow_plugin_main_section_callback', // Callback for rendering the description of the section
        'ai-wow-plugin-api-key-settings' // Page on which to add this section
    );

    add_settings_field(
        'ai_wow_plugin_api_key', // Field ID
        'OpenAI API Key', // Label
        'ai_wow_plugin_api_key_callback', // Callback for rendering the field
        'ai-wow-plugin-api-key-settings', // Page on which to add this field
        'ai_wow_plugin_main_section' // Section in which to add this field
    );
}
add_action('admin_init', 'ai_wow_plugin_settings_init');


function ai_wow_plugin_main_section_callback() {
    echo '<p>Enter your OpenAI API details below:</p>';
}

function ai_wow_plugin_api_key_callback() {
    $options = get_option('ai_wow_plugin_options');
    $api_key = $options['api_key'] ?? '';
    $masked_api_key = str_repeat('*', strlen($api_key) - 4) . substr($api_key, -4); // Mask all but the last 4 characters

    echo '<input id="ai_wow_plugin_api_key" name="ai_wow_plugin_options[api_key]" type="password" value="' . esc_attr($api_key) . '" autocomplete="off" />';
    echo '<p>Current Key: ' . esc_html($masked_api_key) . '</p>';
}


function ai_wow_plugin_options_validate($input) {
    $new_input = array();
    if (isset($input['api_key'])) {
        // Sanitize the input before saving
        $new_input['api_key'] = sanitize_text_field($input['api_key']);
    }
    // Return the sanitized input
    return $new_input;
}


function ai_wow_plugin_generate_key_takeaways_page() {
    ?>
    <div class="wrap">
        <h2>Generate Key Takeaways for Posts</h2>
        <form id="ai-wow-plugin-form">
            <!-- Container for posts list -->
            <div id="ai-wow-plugin-posts-container">
                <?php
                // Fetch and list your posts here, using WP_Query or get_posts
                $args = [
                    'post_type' => 'post',
                    'posts_per_page' => -1 // Adjust based on your needs or implement pagination
                ];
                $posts = get_posts($args);
                foreach ($posts as $post) {
                    echo '<div><input type="radio" name="post" value="' . esc_attr($post->ID) . '"> ' . esc_html($post->post_title) . '</div>';
                }
                ?>
            </div>
            <button type="button" id="ai-wow-plugin-generate">Generate Key Takeaway</button>
        </form>
    </div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#ai-wow-plugin-generate').click(function() {
            var $button = $(this); // Cache the button
            var selectedPost = $('#ai-wow-plugin-form input[type="radio"]:checked').val(); // Get the selected post ID
            if (!selectedPost) {
                alert('Please select a post.');
                return;
            }
            // Disable the button to prevent multiple submissions and change the text
            $button.prop('disabled', true).text('Generating...');

            $.ajax({
                url: ai_wow_plugin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'generate_key_takeaways',
                    nonce: ai_wow_plugin_ajax.nonce,
                    post_id: selectedPost // Send the selected post ID
                },
                success: function(response) {
                    if (response.success) {
                        alert('Key takeaway generated successfully!');
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                    // Re-enable the button after the AJAX call completes
                    $button.prop('disabled', false).text('Generate Key Takeaway');
                },
                error: function() {
                    alert('An error occurred while generating the key takeaway.');
                    // Re-enable the button if there's an AJAX error
                    $button.prop('disabled', false).text('Generate Key Takeaway');
                }
            });
        });
    });
</script>
    <?php
}


function ai_wow_plugin_ajax_generate_key_takeaways() {
    // Check for user permissions and nonce for security
    if (!current_user_can('edit_posts')) {
        error_log('User does not have permission to edit posts.');
        wp_send_json_error('You do not have permission to edit posts.');
        return;
    }
    
    // Verify the nonce passed in the request
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_wow_plugin_generate_key_takeaways_nonce')) {
        error_log('Nonce verification failed for key takeaway generation.');
        wp_send_json_error(['message' => 'Nonce verification failed.']);
        return;
    }

    // Process the selected post
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if ($post_id <= 0) {
        error_log('Invalid post ID received: ' . $post_id);
        wp_send_json_error(['message' => 'Invalid post ID.']);
        return;
    }

    // Retrieve the post content
    $post = get_post($post_id);
    if (!$post) {
        error_log('Post not found with ID: ' . $post_id);
        wp_send_json_error(['message' => 'Post not found.']);
        return;
    }

    // Prepare the data for the OpenAI API request
    $api_key = get_option('ai_wow_plugin_options')['api_key'] ?? '';
    $api_url = 'https://api.openai.com/v1/chat/completions'; // Correct endpoint for chat API
    $messages = [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => 'Generate a key takeaway for the following article: ' . substr(wp_strip_all_tags($post->post_content), 0, 8000)]
    ];

    $body = json_encode([
        'model' => 'gpt-3.5-turbo', // Confirm this is a correct chat model
        'messages' => $messages,
    ]);

    $response = wp_remote_post($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => $body,
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 45
    ]);

    if (is_wp_error($response)) {
        error_log('Failed to connect to OpenAI API: ' . $response->get_error_message());
        wp_send_json_error(['message' => 'Failed to connect to OpenAI API: ' . $response->get_error_message()]);
        return;
    }

    $response_body = wp_remote_retrieve_body($response);
    error_log('OpenAI API response: ' . $response_body);
    $data = json_decode($response_body, true);

    // Check if the API returned a key takeaway
    if (isset($data['choices'][0]['message']['content'])) {
        $key_takeaway = trim($data['choices'][0]['message']['content']);

        // Prepend the key takeaway to the post content and update the post
        $new_content = '<p><div id="key-ta"><em>' . esc_html($key_takeaway) . '</em></div></p>' . $post->post_content;
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $new_content
        ]);

        wp_send_json_success(['message' => 'Key takeaway generated successfully.']);
    } else {
        error_log('Failed to generate key takeaway from the API response.');
        wp_send_json_error(['message' => 'Failed to generate key takeaway from the API response.']);
    }
}
add_action('wp_ajax_generate_key_takeaways', 'ai_wow_plugin_ajax_generate_key_takeaways');


function ai_wow_plugin_enqueue_scripts($hook) {
    // Only add to the admin area and on your specific plugin's pages
    if ($hook == 'toplevel_page_ai-wow-plugin' || $hook == 'ai-wow-plugin_page_ai-wow-plugin-api-key-settings' || $hook == 'ai-wow-plugin_page_ai-wow-plugin-generate-key-takeaways') {
        wp_enqueue_script('ai-wow-plugin-script', plugin_dir_url(__FILE__) . 'js/ai-wow-plugin.js', array('jquery'), '1.0.0', true);
        wp_localize_script('ai-wow-plugin-script', 'ai_wow_plugin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_wow_plugin_generate_key_takeaways_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'ai_wow_plugin_enqueue_scripts');


