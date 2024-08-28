<?php
/**
 * Plugin Name: Helpful Login Register 
 * Description: This is a custom plugin for extending WordPress functionality.
 * Version: 1.1
 * Author: Helpful Login Register
 * Author URI: https://www.helpfulinsightsolution.com/
 */

/**
 * Class HelpfulLoginRegister
 */


if(!class_exists('HelpfulLoginRegister', false)){
    class HelpfulLoginRegister{
        public static function init(){
            /**
             * Include Frontend Files
             */
            include_once self::get_plugin_path(). 'front-end/classes/class.hf-frontend.php';
            include_once self::get_plugin_path(). 'front-end/classes/class.hf.dashboard.php';
        }

        /**
         * Get the plugin directory path
         */
        public static function get_plugin_path(){
            $plugin_dir = plugin_dir_path( __FILE__ );
            return $plugin_dir;
        }

        /**
         * Get the plugin file path
         */
        public static function get_plugin_file_path(){
            // Get the plugin directory path
            $plugin_dir         =   self::get_plugin_path();
            // Get the plugin file path
            $plugin_file        =   plugin_basename( __FILE__ );
            $plugin_file_path   =   $plugin_dir . $plugin_file;

            return $plugin_file_path;
        }

        /**
         * Get the plugin URL
         */
        public static function get_plugin_url(){
            $plugin_url = plugin_dir_url( __FILE__ );
            return $plugin_url;
        }
    }

    HelpfulLoginRegister::init();
}

/**
 * On plugin Activation
 */
register_activation_hook(__FILE__, 'hp_on_plugin_activation_cb');
function hp_on_plugin_activation_cb(){
    // Create pages and add shortcodes
    $pages = [  
        [
            'title'     =>  'Login',
            'content'   =>  '[helpful_login]',
        ],
        [
            'title'     =>  'Register',
            'content'   =>  '[helpful_register]',
        ],
        [
            'title'     =>  'Forgot Password',
            'content'   =>  '[helpful_forgot_password]',
        ],
        [
            'title'     =>  'Dashboard',
            'content'   =>  '[helpful_dashboard]',
        ],
        [
            'title'     =>  'Verify Account',
            'content'   =>  '[helpful_verify_account]',
        ],
    ];

    foreach($pages as $page){
        $post = [
            'post_title'    =>  $page['title'],
            'post_content'  =>  $page['content'],
            'post_status'   =>  'publish',
            'post_type'     =>  'page',
        ];
        wp_insert_post($post);
    }
    
    //Creating Custom Roles
    
    $subscriber = get_role('subscriber'); // Get the subscriber role object

    if(!$subscriber){
        $subscriber_caps = [
            'read'         => true,  
            'edit_posts'   => true,
        ];
    }else{
        $subscriber_caps = $subscriber->capabilities;
    }
    
    add_role('makers', __('Makers'), $subscriber_caps); //Role Makers
    add_role('outlets', __('Outlets'), $subscriber_caps); // Role Outlets
}
