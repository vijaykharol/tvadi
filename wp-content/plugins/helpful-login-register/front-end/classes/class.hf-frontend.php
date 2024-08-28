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

            //Helpful Verify Account
            add_shortcode( 'helpful_verify_account', [__CLASS__, 'helpful_verify_account_cb'] );

            //Handle login Ajax Request..
            add_action( 'wp_ajax_hp_login_process', [__CLASS__, 'hp_login_process_callback'] );
            add_action( 'wp_ajax_nopriv_hp_login_process', [__CLASS__, 'hp_login_process_callback'] );

            //Handle Register Ajax Request..
            add_action( 'wp_ajax_hp_register_process', [__CLASS__, 'hp_register_process_callback'] );
            add_action( 'wp_ajax_nopriv_hp_register_process', [__CLASS__, 'hp_register_process_callback'] );

            //Handle Forgot Password Request..
            add_action( 'wp_ajax_hp_forgotp_process', [__CLASS__, 'hp_forgotp_process_callback'] );
            add_action( 'wp_ajax_nopriv_hp_forgotp_process', [__CLASS__, 'hp_forgotp_process_callback'] );

            //OTP Verification..
            add_action( 'wp_ajax_hp_otp_verification_process', [__CLASS__, 'hp_otp_verification_process_callback'] );
            add_action( 'wp_ajax_nopriv_hp_otp_verification_process', [__CLASS__, 'hp_otp_verification_process_callback'] );
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
            $username   =   (isset($_POST['username'])) ? sanitize_text_field($_POST['username']) : '';
            $password   =   (isset($_POST['password'])) ? $_POST['password'] : '';
            $remember   =   (isset($_POST['remember']) && $_POST['remember'] === 'true') ? true : false;

            if(is_email($username)){
                $user = get_user_by('email', $username);
            }else{
                $user = get_user_by('login', $username);
            }

            if($user){
                $user_id            =   $user->ID;
                $isVerifiedUser     =   get_user_meta($user_id, 'user_verification_status', true);
                if(!empty($isVerifiedUser) && $isVerifiedUser == 1){
                    $credentials = [
                        'user_login'    => $username,
                        'user_password' => $password,
                        'remember'      => $remember
                    ];
        
                    $signon     =   wp_signon($credentials, false);
                    $return     =   [];
                    if(is_wp_error($signon)){
                        $return['status']       =   false;
                        $return['message']      =   'Wrong username or password.';
                    }else{
                        wp_set_current_user($signon->ID);
                        wp_set_auth_cookie($signon->ID, true);
                        $return['status']       =   true;
                        $return['message']      =   'Login successful, redirecting...';
                    }
                }else{
                    $return['status']   =   false;
                    $return['message']  =   'Your account is not verified. Please Verify your account by clicking verify. <a class="verify-account-link" href="'.site_url().'/verify-account/?id='.$user_id.'">Verify Account</a>';
                }
            }else{
                $return['status']   =   false;
                $return['message']  =   'User does not exist.';
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
                    <!-- Registration Form -->
                    <form id="custom-registration-form" action="" method="POST">
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
                    <!-- OTP Verication Form -->
                    <form action="" id="custom-register-otp-verification" method="POST" style="display: none;">
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <div id="o-success" style="display:none;"></div>
                                    <div id="o-error" style="display:none;"></div>
                                </div>
                            </div>
                            <input type="hidden" name="registered_user_id" id="registered-user-id">
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <span class="frndly-msg">Please enter the OTP we’ve sent to your email address.</span>
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <label for="otp_number">Enter OTP(6 Digit) Number <span style="color:red;">*</span></label>
                                    <input type="text" name="otp_number" id="otp_number" maxlength="6">
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <button id="submit-otp-register-btn" class="btn btn-primary btn-large">Submit</button>
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

                    $otp = rand(100000, 999999);
                    update_user_meta($user_id, 'user_verification_status', 0);
                    update_user_meta($user_id, 'verification_otp', $otp);

                    //Admin email
                    $admin_email    =   get_option('admin_email');
                    $subject        =   'New User Registration';
                    $message        =   'A new user has registered on your website. Username: ' .$username. ', Email: ' .$email. ', User type: '.$user_role;
                    $mail           =   wp_mail($admin_email, $subject, $message);

                    //User email
                    $site_name      =   get_bloginfo('name');
                    $subject2       =   'Your OTP Verification Code for '.$site_name.'';
                    $message2       =   "
                        <!DOCTYPE html>
                        <html lang='en'>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <title>OTP Verification</title>
                        </head>
                        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                <tr>
                                    <td style='padding: 20px;'>
                                        <h2 style='color: #333;'>Dear ".$firstname." ".$lastname.",</h2>
                                        <p>Thank you for signing up at <strong>".$site_name."</strong>. To complete your registration, please verify your email address by entering the following One-Time Password (OTP) in the verification field:</p>
                                        <p style='font-size: 24px; font-weight: bold; color: #007BFF;'>Your OTP Code: ".$otp."</p>
                                        <p>If you did not initiate this request, please disregard this email.</p>
                                        <p>Thank you for choosing <strong>".$site_name."</strong>.</p>
                                        <p>Best regards,<br>
                                        <strong>".$site_name." Team</strong></p>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>
                    ";
                    //Send Email To User
                    $mail2                  =   wp_mail($email, $subject2, $message2);

                    $return['status']       =   true;
                    $return['message']      =   'Thank you for signing up! Please check your email for the One-Time Password (OTP) to complete your registration.';
                    $return['redirecturl']  =   site_url().'/login/';
                    $return['user_id']      =   $user_id;
                }
            }
            echo json_encode($return);
            exit();
        }

        public static function hp_otp_verification_process_callback(){
            $otp        =   (isset($_POST['otp_number']))            ?   $_POST['otp_number']            : '';
            $user_id    =   (isset($_POST['registered_user_id']))    ?   $_POST['registered_user_id']    : '';
            $return     =   [];
            if(empty($otp)){
                $return['status']   =   false;
                $return['msg']      =   'Please fill out all required fields!';
            }else if(empty($user_id)){
                $return['status']   =   false;
                $return['msg']      =   'Oop\'s Something went wrong please try after some time. Thank You!';
            }else{
                
                $userotp = get_user_meta($user_id, 'verification_otp', $otp);
                if($userotp === $otp){
                    update_user_meta($user_id, 'user_verification_status', 1);
                    delete_user_meta($user_id, 'verification_otp');
                    $return['status']   =   true;
                    $return['msg']      =   'Registration successfully completed. Redirecting...';
                    $return['url']      =   site_url().'/login';
                }else{
                    $return['status']   =   false;
                    $return['msg']      =   'Wrong OTP!';
                }
            }
            echo json_encode($return);
            exit;
        }

        /**
         * VERIFY ACCOUNT
         */
        public static function helpful_verify_account_cb(){
            $user_id = (isset($_GET['id'])) ? $_GET['id'] : '';
            ob_start();
            if(!is_user_logged_in() && !empty($user_id)){
                ?>
                <div class="helpful-register-form">
                    <form action="" id="custom-register-otp-verification" method="POST">
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <div id="o-success" style="display:none;"></div>
                                    <div id="o-error" style="display:none;"></div>
                                </div>
                            </div>
                            <input type="hidden" name="registered_user_id" id="registered-user-id" value="<?= $user_id ?>">
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <span class="frndly-msg">Please enter the OTP we’ve sent to your email address.</span>
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <label for="otp_number">Enter OTP(6 Digit) Number <span style="color:red;">*</span></label>
                                    <input type="text" name="otp_number" id="otp_number" maxlength="6">
                                </div>
                            </div>
                            <div class="col-12 col-lg-12">
                                <div class="form-group">
                                    <button id="submit-otp-register-btn" class="btn btn-primary btn-large">Submit</button>
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