<?php
/*
Plugin Name: FB Post Fetcher by CodeWooInsider
Description: Fetches and displays Facebook posts using Graph API.
Version: 1.1
Author: Urooj Shafait
Company: CodeWooInsider
*/

// Add shortcode to display Facebook posts
add_shortcode('custom_facebook_posts', 'display_custom_facebook_posts');

function display_custom_facebook_posts() {
    // Get access token from plugin settings
    $access_token = get_option('custom_facebook_posts_access_token');

    // Check if access token is set
    if (empty($access_token)) {
        return 'Access token is not set. Please enter your access token in plugin settings.';
    }

    // Construct the Graph API URL with the access token
    $graph_api_url = "https://graph.facebook.com/me/posts?fields=message,created_time,full_picture,link&access_token=$access_token";

    // Fetch data from the Graph API
    $response = @file_get_contents($graph_api_url);

    // Check if the request was successful
    if ($response === FALSE) {
        // Error handling if request failed
        return 'Error fetching Facebook posts. Please check your access token and permissions.';
    }

    // Decode JSON response
    $posts_data = json_decode($response, true);

    // Check if data retrieval was successful
    if ($posts_data && isset($posts_data['data'])) {
        // Start output buffer
        ob_start();
        
        // Output each post
        foreach ($posts_data['data'] as $post) {
            ?>
            <div class="facebook-post">
                <?php if (isset($post['message'])) : ?>
                    <p><?php echo $post['message']; ?></p>
                <?php endif; ?>
                <?php if (isset($post['full_picture'])) : ?>
                    <img src="<?php echo $post['full_picture']; ?>" alt="Post Image">
                <?php endif; ?>
                <?php if (isset($post['link'])) : ?>
                    <a href="<?php echo $post['link']; ?>" target="_blank">Read More</a>
                <?php endif; ?>
                <?php if (isset($post['created_time'])) : ?>
                    <p class="post-date"><?php echo date('F j, Y', strtotime($post['created_time'])); ?></p>
                <?php endif; ?>
            </div>
            <?php
        }

        // End output buffer and return content
        return ob_get_clean();
    } else {
        // Error handling if data retrieval failed
        return 'Error fetching Facebook posts. Please check your access token and permissions.';
    }
}

// Create plugin settings page
add_action('admin_menu', 'custom_facebook_posts_menu');

function custom_facebook_posts_menu() {
    add_options_page(
        'Custom Facebook Posts Settings',
        'Facebook Posts',
        'manage_options',
        'custom-facebook-posts',
        'custom_facebook_posts_settings_page'
    );
}

function custom_facebook_posts_settings_page() {
    ?>
    <div class="wrap">
        <h1>Custom Facebook Posts Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_facebook_posts_settings_group');
            do_settings_sections('custom_facebook_posts');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register plugin settings
add_action('admin_init', 'custom_facebook_posts_settings_init');

function custom_facebook_posts_settings_init() {
    register_setting('custom_facebook_posts_settings_group', 'custom_facebook_posts_access_token');

    add_settings_section(
        'custom_facebook_posts_settings_section',
        'Facebook Access Token',
        'custom_facebook_posts_settings_section_callback',
        'custom_facebook_posts'
    );

    add_settings_field(
        'custom_facebook_posts_access_token_field',
        'Access Token',
        'custom_facebook_posts_access_token_field_callback',
        'custom_facebook_posts',
        'custom_facebook_posts_settings_section'
    );
}

function custom_facebook_posts_settings_section_callback() {
    echo 'Enter your Facebook Graph API access token below:';
}

function custom_facebook_posts_access_token_field_callback() {
    $access_token = get_option('custom_facebook_posts_access_token');
    echo '<input type="text" name="custom_facebook_posts_access_token" value="' . esc_attr($access_token) . '" size="50">';
}
?>
