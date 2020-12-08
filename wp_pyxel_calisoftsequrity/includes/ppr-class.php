<?php

require_once(ABSPATH . 'wp-includes/class-phpass.php');

class ppr
{
    protected $user;
    protected $hasher;
    protected $table;
    protected $old_user_details;

    public function __construct($user)
    {
        $this->user = $user;
        $this->hasher = new PasswordHash(8, TRUE);
        $this->table = "password_log";
        $this->old_user_details = get_userdata( $user->ID );
    }

    public function get_passwords()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table;

        $query = "SELECT * FROM " . $table_name . " WHERE `user` = " . $this->user->ID;

        $password_history = $wpdb->get_results($query);

        return $password_history;

    }

    public function check_previous_passwords($new_password)
    {
        //Check against previous passwords
        foreach ($this->get_passwords() as $password) {
            if ($this->hasher->CheckPassword($new_password, $password->password)) {
                return false;
            }
        }
        return true;
    }

    public function check_current_password($new_password)
    {
        //Check against current password
        if ( $this->hasher->CheckPassword($new_password, $this->old_user_details->data->user_pass) ) {
            return false;
        }
        return true;
    }

    public function store_old_password()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table;

        $wpdb->insert( $table_name, array(
            'created' => date('Y-m-d H:i:s'),
            'user' => $this->user->ID,
            'password' => $this->old_user_details->data->user_pass ) );
    }
}