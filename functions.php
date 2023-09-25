<?php
// User Registration Form Shortcode 
function custom_registration_form() { 
    if (is_user_logged_in()) {
        // User is already logged in, show a message
        return '<p>You Are Already Registrater In.</p>';
    }
     ob_start();
    ?>
    <form id="registration-form" method="post">
        <p>
            <label for="username">Username:</label>
            <input type="text" name="username" required>
        </p>
        <p>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </p>
        <p>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password"  name="confirm_password" required>
            <div class="error-message"></div>
        </p>
        <p>
            <input type="submit" name="submit" value="Register">
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_registration', 'custom_registration_form');

// Handle Registration Form Submission
function custom_register_user() {
    if (isset($_POST['submit'])) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        $confirm_password = sanitize_text_field($_POST['confirm_password']);
        
        if ($password !== $confirm_password) {
            echo "Passwords do not match.";
            return;
        }
        
        $user_id = wp_create_user($username, $password, $email, $confirm_password,);
        
        if (!is_wp_error($user_id)) {
            wp_redirect(home_url('/login')); // Redirect to login page
            exit;
        }
    }
}
add_action('init', 'custom_register_user');

// User Login Form Shortcode
function custom_login_form() {
    if (is_user_logged_in()) {
        // User is already logged in, show a message
        return '<p>You Are Already Logged In.</p>';
    }
    ob_start();
    ?>
    <div class="login-error-message"></div> <!-- Error message container -->
    <form id="login-form" method="post">
        <p>
            <label for="username">Username:</label>
            <input type="text" name="username" required>
        </p>
        <p>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </p>
        <p>
            <input type="submit" name="login_submit" value="Login">
        </p>
        <p><a href="<?php echo home_url('/lost'); ?>">Lost your password?</a></p> <!-- Lost password link -->
        </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_login_form', 'custom_login_form');

// Handle Login Form Submission
function custom_login_form_handler() {
    if (isset($_POST['login_submit'])) {
        $username = sanitize_user($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        
        $user = wp_signon(array(
            'user_login' => $username,  
            'user_password' => $password,
            'remember' => true,
        ));
        
        if (!is_wp_error($user)) {
            wp_redirect(home_url('/home')); // Redirect to dashboard or desired page
            exit;
        }
    }
}
add_action('init', 'custom_login_form_handler');

// Lost Password

function custom_username_input_form() {
    if (is_user_logged_in()) {
        // User is already logged in, show a message
        return '<p>You Are Already Logged In.</p>';
    }
    ob_start();
    ?>
    <form id="username-form" method="post">
          <p>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <input type="submit" name="submit_email" value="Submit">
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_username_input', 'custom_username_input_form');

function handle_email_submission() {
    if (isset($_POST['submit_email'])) {
        $email = sanitize_email($_POST['email']);

        // Check if the email exists
        $user = get_user_by('email', $email);

        if ($user) {
            // Email exists, create a unique token
            $reset_key = wp_generate_password(20, false);

            // Store the reset token in user's metadata
            update_user_meta($user->ID, 'password_reset_key', $reset_key);

            // Redirect to a custom password reset page with the token
            $reset_link = home_url("/reset-password/?key=$reset_key");
            wp_redirect($reset_link);
            exit;
        } else {
            // Email doesn't exist
            echo '<p>Email address not found. Please enter a valid email address.</p>';
        }
    }
}
add_action('init', 'handle_email_submission');

// Reset Password

function custom_reset_password_form() {
    if (is_user_logged_in()) {
        // User is already logged in, show a message
        return '<p>You Are Already Logged In.</p>';
    }
    ob_start();
    ?>
    <form id="reset-password-form" method="post">
        <p>
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>
        </p>
        <p>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        </p>
        <p>
            <input type="hidden" name="reset_key" value="<?php echo $_GET['key']; ?>">
            <input type="submit" name="reset_password_submit" value="Reset Password">
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_reset_password', 'custom_reset_password_form');

function handle_password_reset_submission() {
    if (isset($_POST['reset_password_submit'])) {
        $reset_key = sanitize_text_field($_POST['reset_key']);
        $new_password = sanitize_text_field($_POST['new_password']);
        $confirm_password = sanitize_text_field($_POST['confirm_password']);

        // Verify the reset key
        $user = get_users(array(
            'meta_key' => 'password_reset_key',
            'meta_value' => $reset_key,
        ));

        if (!empty($user) && $new_password === $confirm_password) {
            // Reset the user's password
            wp_set_password($new_password, $user[0]->ID);

            // Remove the reset key from user's metadata
            delete_user_meta($user[0]->ID, 'password_reset_key');

            // Password reset successful
            wp_redirect(home_url('/home'));
            exit;
        } else {
            // Invalid reset key or passwords don't match
            echo '<p>Invalid reset key or passwords don\'t match. Please try again.</p>';
        }
    }
}
add_action('init', 'handle_password_reset_submission');

// Login attempts 3 Time to redirect to lost password 

function clear_login_attempts_after_reset($user_id) {
    // Start a session if not already started
    if (!session_id()) {
        session_start();
    }

    // Clear login attempts for the user
    unset($_SESSION['login_attempts'][$user_id]);
}
add_action('password_reset', 'clear_login_attempts_after_reset');

function track_login_attempts($username) {
    // Start a session if not already started
    if (!session_id()) {
        session_start();
    }

    $user = get_user_by('login', $username);

    if ($user) {
        $login_attempts = isset($_SESSION['login_attempts'][$user->ID]) ? $_SESSION['login_attempts'][$user->ID] : 0;
        $login_attempts++;

        $_SESSION['login_attempts'][$user->ID] = $login_attempts;

        if ($login_attempts >= 3) {
            // User has reached 3 or more failed attempts, initiate the password reset process
            echo '<p>You have reached the maximum number of login attempts. Please reset your password.</p>';
            echo wp_redirect(home_url('/lost'));
            // echo do_shortcode('[custom_username_input]'); // Display the password reset form
            die(); // Stop further login attempts
        }
    }
}
add_action('wp_login_failed', 'track_login_attempts', 10, 1);

//in session store a password  
function store_password_in_session($password) {
    // Start a session if not already started
    if (!session_id()) {
        session_start();
    }

    // Store the password and timestamp in the session
    $_SESSION['stored_password'] = $password;
    $_SESSION['password_expiration'] = time() + (2 * 60); // 30 minutes in seconds
}

function get_stored_password() {
    // Start a session if not already started
    if (!session_id()) {
        session_start();
    }

    // Check if the password is stored and not expired
    if (isset($_SESSION['stored_password']) && isset($_SESSION['password_expiration'])) {
        $current_time = time();
        $expiration_time = $_SESSION['password_expiration'];

        if ($current_time <= $expiration_time) {
            // Password is still valid
            return $_SESSION['stored_password'];
        } else {
            // Password has expired, remove it from the session
            unset($_SESSION['stored_password']);
            unset($_SESSION['password_expiration']);
        }
    }

    // If no valid password is found, return false
    return false;
}

?>