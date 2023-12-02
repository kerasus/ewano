<?php
include_once('EwanoAssist.php');

class EwanoAuth {
    public function __construct($usre = null){
        $this->assist = new EwanoAssist();
        $this->user = $usre;
        if (isset($this->user) && isset($this->user['mobile'])) {
            $this->username = $this->user['mobile'];
        } else {
            $this->username = null;
        }
    }

    public function isUserLoggedIn () {
        $user = $this->getOrCreateUser();
        if (is_wp_error($user) || empty($user)) {
            return false;
        }
        return $user->ID === $this->getCurrentUserId();
    }

    public function login () {
        $user = $this->getOrCreateUser();

        if (is_wp_error($user)) {
            // Handle the WP_Error object, the user either could not be retrieved or created
            return false;
        }

        // Setup WordPress user object
        wp_set_current_user($user->ID, $user->user_login);

        // Make WordPress recognize the new user
        wp_set_auth_cookie($user->ID);

        // Do the login
        do_action('wp_login', $user->user_login, $user);
    }

    private function getCurrentUserId () {
        return get_current_user_id();
    }

    private function register () {
        $username = $this->user['mobile'] ?? false;
        $password = $this->user['national_code'] ?? false;
        $lName = $this->user['last_name'] ?? ' ';
        $fName = $this->user['first_name'] ?? 'کاربر ایوانو';
        $displayName = $fName . ' ' . (!$this->user['last_name'] ? $username : $lName);
        $nationalCode = $this->user['national_code'] ?? '0000000000';
        $placeholder_email = $this->user['email'] ?? ($username . '@placeholder.email'); // Generate a unique placeholder email

        if (!$username || !$password) {
            return false;
        }

        // Create a new user
        $userId = wp_create_user($username, $nationalCode, $placeholder_email);

        if (is_wp_error($userId)) {
            // Handle errors (e.g., username already exists), perhaps return the error
            return $userId;
        }

        wp_update_user([
            'ID' => $userId,
            'display_name' => $displayName,
            'user_nicename' => $displayName
        ]);

//        update_user_meta($userId, 'first_name', $fName);
//        update_user_meta($userId, 'last_name', $lName);
//        update_user_meta($userId, 'national_code', $nationalCode);
//        update_user_meta($userId, 'from_ewano', 1);

        return $userId;
    }

    private function getOrCreateUser () {
        $user = get_user_by('login', $this->username);

        // Check if the user exists
        if (!$user) {
            $userId = $this->register();
            if (is_wp_error($userId)) {
                return $userId;
            }
            $user = get_user_by('ID', $userId);
        }
        return $user;
    }
}
