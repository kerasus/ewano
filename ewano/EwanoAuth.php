<?php
include_once('EwanoAssist.php');

class EwanoAuth {
    public function __construct($username){
        $this->assist = new EwanoAssist();
        $this->user = [
            'username'=> '09358745928',
            'password'=> '0000000000',
            'first_name'=> 'Ali',
            'last_name'=> 'Esmaeeli',
        ];
        $this->username = $username;
    }

    public function getCurrentUserId () {
        return get_current_user_id();
    }

    public function login () {
        $user = get_user_by('login', $this->username);
        // Check if the user exists
        if (!isset($user)) {
            $this->registetr();
            $user = get_user_by('login', $this->user['username']);
        }

        // Setup WordPress user object
        wp_set_current_user($user->ID, $user->user_login);

        // Make WordPress recognize the new user
        wp_set_auth_cookie($user->ID);

        // Do the login
        do_action('wp_login', $user->user_login, $user);
    }

    public function registetr () {
        // Create a new user
        $userId = wp_create_user($this->user['username'], $this->user['password']);

        // Set first and last names
        update_user_meta($userId, 'first_name', $this->user['first_name']);
        update_user_meta($userId, 'last_name', $this->user['last_name']);

        return $userId;
    }
}
