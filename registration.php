<?php
/*
Plugin Name: Demo 
Description: A simple user registration and login plugin.
Version: 1.0
Author: Addweb Solution 
*/

// Include the functions file
require_once(plugin_dir_path(__FILE__) . 'functions.php');
// require_once(plugin_dir_path(__FILE__) . 'custom-list-table.php');

function enqueue_custom_css() {
    wp_enqueue_style('Custom-style', plugins_url('Custom-style.css', __FILE__));
}
    
add_action('wp_enqueue_scripts', 'enqueue_custom_css');

// Include the custom List Table file
// require_once(plugin_dir_path(__FILE__) . 'custom-list-table.php');


function custom_registration_enqueue_scripts() {
    wp_enqueue_style('custom-registration-style', plugins_url('Custom-style.css', __FILE__));
    wp_enqueue_script('custom-registration-validation', plugins_url('custom-registration-validation.js', __FILE__), array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'custom_registration_enqueue_scripts');

function custom_list_users() {
    $users = get_users(); // Retrieve all registered users

    if ($users) {
        echo '<ul>';
        foreach ($users as $user) {
           echo '<li>' . esc_html($user->user_login) . '</li>';
        }
        echo '</ul>';
    } else {
        echo 'No users found.';
    }
    function custom_list_users() {
        $users = get_users(); // Retrieve all registered users
    
        if ($users) {
            echo '<table class="custom-user-list">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Username</th>';
            echo '<th>Email</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($users as $user) {
                // Generate HTML for each user row
                
            }
            
            echo '</tbody>';
            echo '</table>';
        } else {
            echo 'No users found.';
        }
    }
    
    add_shortcode('custom_user_list', 'custom_list_users');    
}

// Function to create the user listing page
function custom_admin_menu() {
    add_menu_page(
        'User Listing',          // Page title
        'User Listing',          // Menu title
        'manage_options',        // Capability required to access
        'custom-user-listing',   // Menu slug
        'custom_user_listing_page' // Callback function to display content
    );
}

// Function to display the user listing page
function custom_user_listing_page() {
    ?>
    <div class="wrap">
        <h2>User Listing</h2>
        <?php
        // Include your user listing function here
        custom_list_users();
        ?>
    </div>
    <?php
}

// Hook the admin_menu action to create the menu
add_action('admin_menu', 'custom_admin_menu');

add_shortcode('custom_user_list', 'custom_list_users');

?>