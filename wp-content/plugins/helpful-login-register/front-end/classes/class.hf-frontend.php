<?php
if(!defined('ABSPATH')){
    exit;
}

/**
 * Class HplrFrontEnd
 */
if(!class_exists('HplrFrontEnd', false)){
    class HplrFrontEnd{
        public static function init(){
            // Hook into the frontend_enqueue_scripts action
            add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_scripts'] );
            
            //login shortcode
            add_shortcode( 'helpful_login', [__CLASS__, 'helpful_login_cb'] );

            //Register shortcode
            add_shortcode( 'helpful_register', [__CLASS__, 'helpful_register_cb'] );

             //fortgot password shortcode
             add_shortcode( 'helpful_forgot_password', [__CLASS__, 'helpful_forgot_password_cb'] );

            //Handle login Ajax Request..
            add_action( 'wp_ajax_hp_login_process', [__CLASS__, 'hp_login_process_callback'] );
            add_action( 'wp_ajax_nopriv_hp_login_process', [__CLASS__, 'hp_login_process_callback'] );

            //Handle Register Ajax Request..
            add_action( 'wp_ajax_hp_register_process', [__CLASS__, 'hp_register_process_callback'] );
            add_action( 'wp_ajax_nopriv_hp_register_process', [__CLASS__, 'hp_register_process_callback'] );

            //Handle Forgot Password Request..
            add_action( 'wp_ajax_hp_forgotp_process', [__CLASS__, 'hp_forgotp_process_callback'] );
            add_action( 'wp_ajax_nopriv_hp_forgotp_process', [__CLASS__, 'hp_forgotp_process_callback'] );
        }

        /**
         * EnQueue scripts and style
         */
        public static function enqueue_frontend_scripts(){
            // Enqueue your frontend stylesheet
            wp_enqueue_style( 'tvadimarket-frontend-style', HelpfulLoginRegister::get_plugin_url().'front-end/assets/style/front-end.css?'.time() );

            // Enqueue the script
            wp_enqueue_script('tvadimarket-scripts', HelpfulLoginRegister::get_plugin_url().'front-end/assets/script/front-script.js?', array('jquery'), time(), true);

            // Pass AJAX URL to script
            wp_localize_script('tvadimarket-scripts', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        }

        /**
         * LOGIN PAGE SHORTCODE
         */
        public static function helpful_login_cb(){
            ob_start();
            if(!is_user_logged_in()){
                ?>
                <div class="hp-login-form">
                    <form id="hp-login-form">
                        <div class="form-group">
                            <div id="l-success" style="display:none;"></div>
                            <div id="l-error" style="display:none;"></div>
                        </div>
                        <div class="form-group">
                            <label for="username">Username or Email</label>
                            <input type="text" id="username" name="username" placeholder="Username or Email">
                        </div>
                        <div class="form-group">
                            <label for="username">Password</label>
                            <input type="password" id="password" name="password" placeholder="Password">
                        </div>
                        <div class="form-group d-flex justify-content-between align-items-center rememberme">
                            <label><input type="checkbox" id="remember" name="remember"> <span class="title">Remember Me</span></label>
                            <a href="<?= site_url() ?>/forgot-password/">Forgot Password?</a>
                        </div>
                        <input type="hidden" name="security_nonce" id="security_nonce" value="<?= wp_create_nonce('ajax-login-nonce') ?>">
                        <div class="form-group">
                            <button id="login-btn" class="btn btn-primary btn-large">Login</button>
                        </div>
                        <div class="form-group mb-0">
                            <div class="form-bottom-actions">
                                <p>If You Don't Have An Account?<a href="<?= site_url() ?>/register/"> Sign up</a></p>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
            }else{
               ?>
               <script>
                window.location.href = "<?= site_url() ?>/dashboard/";
               </script>
               <?php
            }
            return ob_get_clean();  
        }

        /**
         * Handle login Ajax Request
         */
        public static function hp_login_process_callback(){
            check_ajax_referer('ajax-login-nonce', 'security_nonce');
            $username   =   (isset($_POST['username'])) ? $_POST['username'] : '';
            $password   =   (isset($_POST['password'])) ? $_POST['password'] : '';
            $remember   =   (isset($_POST['remember']) && $_POST['remember'] === 'true') ? true : false;

            $credentials = [
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => $remember
            ];

            $user = wp_signon($credentials, false);
            $return = [];
            if(is_wp_error($user)){
                $return['status']       =   false;
                $return['message']      =   'Wrong username or password.';
            }else{
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, true);
                $return['status']       =   true;
                $return['message']      =   'Login successful, redirecting...';
            }

            echo json_encode($return);
            exit;
        }

        /**
         * REGISTER PAGE SHORTCODE
         */
        public static function helpful_register_cb(){
            ob_start();
            if(!is_user_logged_in()){
                ?>
                <div class="helpful-register-form">
                    <form id="custom-registration-form" action="" method="post">
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <div id="l-success" style="display:none;"></div>
                                    <div id="l-error" style="display:none;"></div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="firstname">First Name <span style="color:red;">*</span></label>
                                    <input type="text" name="firstname" id="firstname">
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="lastname">Last Name <span style="color:red;">*</span></label>
                                    <input type="text" name="lastname" id="lastname">
                                </div>
                            </div>
                            <!-- <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="username">Username <span style="color:red;">*</span></label>
                                    <input type="text" name="username" id="username">
                                </div>
                            </div> -->
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <label for="email">Email <span style="color:red;">*</span></label>
                                    <input type="email" name="email" id="email">
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <label for="password">Password <span style="color:red;">*</span></label>
                                    <input type="password" name="password" id="password">
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password <span style="color:red;">*</span></label>
                                    <input type="password" name="confirm_password" id="confirm_password">
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <label for="confirm_password">Role <span style="color:red;">*</span></label>
                                    <div class="roles-section">
                                        <label><input type="radio" name="user_role" value="makers"> <span class="title">Maker</span></label>
                                        <label><input type="radio" name="user_role" value="outlets"> <span class="title">Outlet</span></label>
                                        <label><input type="radio" name="user_role" value="subscriber"> <span class="title">Universal User</span></label>
                                    </div>
                                </div>
                                <input type="hidden" name="security_nonce" value="<?= wp_create_nonce('ajax-register-nonce') ?>">
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <button id="hplr-register-btn" class="btn btn-primary btn-large">Register</button>
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group mb-0">
                                    <div class="form-bottom-actions">
                                        <p>If you already Have an account?<a href="<?= site_url() ?>/login/"> Login</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
            }else{
                ?>
               <script>
                window.location.href = "<?= site_url() ?>/dashboard/?dpage=edit-profile";
               </script>
               <?php
            }
            return ob_get_clean();
        }

        /**
         * HANDLE REGISTER AJAX REQUEST
         */
        public static function hp_register_process_callback(){
            check_ajax_referer('ajax-register-nonce', 'security_nonce');
            
            $firstname          =   (isset($_POST['firstname']))  ?   sanitize_text_field($_POST['firstname'])   : '';
            $lastname           =   (isset($_POST['lastname']))   ?   sanitize_text_field($_POST['lastname'])    : '';
            $email              =   (isset($_POST['email']))      ?   sanitize_email($_POST['email'])            : '';
            $password           =   (isset($_POST['password']))   ?   sanitize_text_field($_POST['password'])    : '';
            $confirm_password   =   (isset($_POST['confirm_password']))     ?   sanitize_text_field($_POST['confirm_password']) : '';
            $user_role          =   (isset($_POST['user_role']))            ?   sanitize_text_field($_POST['user_role']) : '';
            // Assuming confirm password is checked on client-side
            
            // Check for required fields
            if(empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($confirm_password) || empty($user_role)){
                $return['status']   = false;
                $return['message']  = 'Please fill all fields.';
            }else if(username_exists($username)){
                $return['status']   =   false;
                $return['message']  =   'Username already exists.';
            }else if(!is_email($email)){
                $return['status']   =   false;
                $return['message']  =   'Invalid email address.';
            }else if(email_exists($email)){
                $return['status']   =   false;
                $return['message']  =   'Email already registered.';
            }else if($password !== $confirm_password){
                $return['status']   =   false;
                $return['message']  =   'Password and Confirm password should be same.';
            }else{
                $username           =   sanitize_user(current(explode('@', $email)), true);
                 
                $username           =   preg_replace('/[^A-Za-z0-9]/', '', $username); // Further sanitize to remove special characters

                // Ensure the username is unique
                $original_username = $username;
                $i = 1;
                while(username_exists($username)){
                    $username = $original_username . $i;
                    $i++;
                }
                // Create the user
                $user_id = wp_create_user($username, $password, $email);
                if(is_wp_error($user_id)){

                    $return['status']   =   false;
                    $return['message']  =   $user_id->get_error_message();

                }else{

                    // Assign the user role
                    $user = new WP_User($user_id);
                    $user->set_role($user_role); // Change to the desired role

                    update_user_meta($user_id, 'first_name', $firstname);
                    update_user_meta($user_id, 'last_name', $lastname);

                    $admin_email    =   get_option('admin_email');
                    $subject        =   'New User Registration';
                    $message        =   'A new user has registered on your website. Username: ' .$username. ', Email: ' .$email. ', User type: '.$user_role;
                    $mail           =   wp_mail($admin_email, $subject, $message);

                    $return['status']       =   true;
                    $return['message']      =   'Registration successful!';
                    $return['redirecturl']  =   site_url().'/login/';
                }
            }
            echo json_encode($return);
            exit();
        }

        /**
         * Forgot password 
         */
        public static function helpful_forgot_password_cb(){
            ob_start();
            if(!is_user_logged_in()){
                ?>
                <div class="helpful-forgot-password">
                    <form id="forgot_password_form" method="POST">
                        <div class="form-group">
                            <div id="l-success" style="display:none;"></div>
                            <div id="l-error" style="display:none;"></div>
                        </div>
                        <div class="form-group">
                            <label>Enter your email <span style="color:red;">*</span></label>
                            <input type="email" name="user_email" id="user_email" placeholder="Enter your email">
                        </div>
                        <input type="hidden" name="security_nonce" value="<?= wp_create_nonce('ajax-forgotp-nonce') ?>">
                        <div class="form-group">
                            <button id="submit-forgotpass" class="btn btn-primary btn-large">Submit</button>
                        </div>
                    </form>
                </div>
                <?php
            }else{
                ?>
                <script>
                    window.location.href = '<?= site_url() ?>/dashboard/';
                </script>
                <?php
            }
            return ob_get_clean();
        }

        /**
         * HANDLE FORGOT PASSWORD REQUEST
         */
        public static function hp_forgotp_process_callback(){
            check_ajax_referer('ajax-forgotp-nonce', 'security_nonce');
            $user_email = (isset($_POST['user_email']) && !empty($_POST['user_email'])) ? $_POST['user_email'] : '';
            $return     = [];
            if(empty($user_email)){
                $return['status']   =   false;
                $return['message']  =   'Please provide your email.';
            }else if(!is_email($user_email)){
                $return['status']   =   false;
                $return['message']  =   'Invalid email address.';
            }else{

                $user = get_user_by('email', $user_email);

                if(!$user){
                    $return['status']   =   false;
                    $return['message']  =   'User does not exist.';
                }else{

                    $reset_key = get_password_reset_key($user);

                    if(is_wp_error($reset_key)){
                        $return['status']   =   false;
                        $return['message']  =   'Error generating password reset key.';
                    }else{
                        $reset_url      =   '<a href="'.site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login').'">Reset Password</a>';
                        $mail_result    =   wp_mail($user_email, 'Password Reset', 'Click on the following link to reset your password: ' . $reset_url);
                        
                        if($mail_result){
                            $return['status']   =   true;
                            $return['message']  =   'Instructions to reset your password have been sent to your email.';
                        }else{
                            $return['status']   =   false;
                            $return['message']  =   'Failed to send email. Please try again later.';
                        }   
                    }
                }
            }

            echo json_encode($return);
            exit();
        }
    }

    /**
     * Initialize the class init method
     */
    HplrFrontEnd::init();

}