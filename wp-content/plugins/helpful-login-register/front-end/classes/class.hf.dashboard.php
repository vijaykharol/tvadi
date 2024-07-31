<?php
if(!defined('ABSPATH')){
    exit;
}
/**
 * Class HplrDashboard
 */
if(!class_exists('HplrDashboard', false)){
    class HplrDashboard{
        public static function init(){
            add_shortcode( 'helpful_dashboard', [__CLASS__, 'helpful_dashboard_cb'] );
            //HANDLE EDIT PROFILE AJAX REQUEST
            add_action( 'wp_ajax_hp_edit_profile', [__CLASS__, 'hp_edit_profile_cb'] );
            add_action( 'wp_ajax_nopriv_hp_edit_profile', [__CLASS__, 'hp_edit_profile_cb'] );
            //HANDLE CHANGE PASS AJAX REQUEST
            add_action( 'wp_ajax_hp_change_pass', [__CLASS__, 'hp_change_pass_cb'] );
            add_action( 'wp_ajax_nopriv_hp_change_pass', [__CLASS__, 'hp_change_pass_cb'] );
            //HANDLE PROFILE PIC AJAX REQUEST..
            add_action( 'wp_ajax_upload_profile', [__CLASS__, 'upload_profile_cb'] );
            add_action( 'wp_ajax_nopriv_upload_profile', [__CLASS__, 'upload_profile_cb'] );
            //HANDLE UPLOAD COVER AJAX REQUEST
            //HANDLE PROFILE PIC AJAX REQUEST..
            add_action( 'wp_ajax_upload_cover', [__CLASS__, 'upload_cover_cb'] );
            add_action( 'wp_ajax_nopriv_upload_cover', [__CLASS__, 'upload_cover_cb'] );
        }

        /**
         * DASHBOARD
         */
        public static function helpful_dashboard_cb(){
            ob_start();
            if(is_user_logged_in()){
                $current_user       =   wp_get_current_user();
                $user_id            =   get_current_user_id();
                $profile_picture    =   get_user_meta($user_id, 'profile_picture', true);
                $profile_cover      =   get_user_meta($user_id, 'profile_cover_picture', true);
                $profile_info       =   get_user_meta($user_id, 'user_profile_info', true);
                ?>
                <div class="hplr-dashboard">
                    <div class="make" id="dashboard-make-button-section">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-large dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                Make <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""/>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li><a class="dropdown-item" href="/tools/#playlist-maker">Make Playlist</a></li>
                                <li><a class="dropdown-item" href="/make-listing/">Make Listing</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="dashboard-container">
                        <aside class="sidebar">
                            <a href="<?= site_url() ?>/dashboard/"><h2>Dashboard</h2></a>
                            <nav>
                                <ul>
                                    <?php 
                                    $cPage = (isset($_REQUEST['dpage']) && !empty($_REQUEST['dpage'])) ? $_REQUEST['dpage'] : 'profile';
                                    ?>
                                    <li><a href="?dpage=profile" class="<?php if($cPage == 'profile'){ echo 'active';} ?>">Profile</a></li>
                                    <li><a href="?dpage=edit-profile" class="<?php if($cPage == 'edit-profile'){ echo 'active';} ?>">Edit Profile</a></li>
                                    <li><a href="?dpage=c-password" class="<?php if($cPage == 'c-password'){ echo 'active';} ?>">Change Password</a></li>
                                    <li><a href="/tools/#playlist-maker">Make Playlist</a></li>
                                    <li><a href="/make-listing/">Make Listing</a></li>
                                    <?php 
                                    if(!in_array('administrator', $current_user->roles)){
                                        ?>
                                        <li><a href="?dpage=stripe-connect" class="<?php if($cPage == 'stripe-connect'){ echo 'active';} ?>">Stripe Account</a></li>
                                        <?php
                                    }
                                    ?>
                                    <li><a href="<?= wp_logout_url(home_url()) ?>">Logout</a></li>
                                </ul>
                            </nav>
                        </aside>
                        <main class="main-content">
                            <div id="overview">
                                <div class="hplr-dashboard-content">
                                    <?php 
                                    if($cPage == 'profile'){
                                        ?>
                                        <h2>Profile</h2>
                                        <div class="profile-card">
                                            <div class="profile-cover">
                                                <?php 
                                                if(!empty($profile_cover)){
                                                    ?>
                                                    <img src="<?= esc_url($profile_cover) ?>" alt="Cover" class="cover" width='100' height='100'>
                                                    <?php
                                                }else{
                                                    ?>
                                                    <img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/placeholder.jpg" alt="Cover" class="cover" width='100' height='100'>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="profile-card-box">
                                                
                                                <div class="profi-pic-top">
                                                    <?php 
                                                    if($profile_picture){
                                                        echo '<img src="'.esc_url($profile_picture).'" class="das-u-profile" height="100" width="100" alt="Profile Picture">';
                                                    }else{
                                                        echo '<img src="'.get_avatar_url($user_id).'" class="das-u-profile" height="100" width="100" alt="Profile Picture">';
                                                    }
                                                    ?>
                                                </div>
                                                <h3><?php echo esc_html( $current_user->display_name ); ?></h3>
                                            </div>
                                            <div class="card-data">
                                                <p><strong>Profile Info:</strong> <?php echo esc_html( $profile_info ); ?></p>
                                                <p><strong>Email:</strong> <?php echo esc_html( $current_user->user_email ); ?></p>
                                                <p><strong>Username:</strong> <?php echo esc_html( $current_user->user_login ); ?></p>
                                                <?php 
                                                if(isset($current_user->user_firstname) && !empty($current_user->user_firstname)){
                                                    ?>
                                                    <p><strong>First Name:</strong> <?php echo esc_html( $current_user->user_firstname ); ?></p>
                                                    <?php
                                                }
                                                if(isset($current_user->user_lastname) && !empty($current_user->user_lastname)){
                                                    ?>
                                                    <p><strong>Last Name:</strong> <?php echo esc_html( $current_user->user_lastname ); ?></p>
                                                    <?php
                                                }
                                                ?>
                                                <p><strong>Role:</strong> <?php echo implode(', ', $current_user->roles); ?></p>
                                                <div class="profile-url-section">
                                                    <div>
                                                        <span><strong>Public Profile URL:</strong>&nbsp;<a href="<?= site_url() ?>/profile/<?= $user_id ?>/" target="_blank" class="url" id="profile-url"><?= site_url() ?>/profile/<?= $user_id ?>/</a></span>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-primary" id="copy-profile-url-button">Copy URL</button>
                                                        <p id="copyMessage" style="display:none; color: green;">URL copied to clipboard!</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }else if($cPage == 'edit-profile'){
                                        ?>
                                        <div class="edit-profile">
                                            <h3>Edit Profile</h3>
                                            <form id="d-edit-profile" action="" method="post">
                                                <div class="row">
                                                    <div class="col-12 col-lg-12">
                                                        <div class="form-group">
                                                            <div id="l-success" style="display:none; color:green;"></div>
                                                            <div id="l-error" style="display:none; color:red;"></div>
                                                        </div>
                                                    </div>
                                                    <!-- PROFILE PIC -->
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-group">
                                                            <div class="prfilehome">
                                                                <div id="profile-pic-preview">
                                                                    <?php
                                                                    if($profile_picture){
                                                                        echo '<img src="'.esc_url($profile_picture).'" class="das-u-profile" height="100" width="100" alt="Profile Picture">';
                                                                    }else{
                                                                        echo '<img src="'.get_avatar_url($user_id).'" class="das-u-profile" height="100" width="100" alt="Profile Picture">';
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <div class="upload-profile-area">
                                                                    <input type="file" id="profile-pic" name="profile-pic" accept="image/*">
                                                                    <span class="upload-profile" id="upload-profile-pic-btn">Upload Profile Pic</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- PROFILE COVER IMAGE -->
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-group">
                                                            <div class="prfilehome">
                                                                <div id="profile-cover-preview">
                                                                    <?php
                                                                    if($profile_cover){
                                                                        echo '<img src="'.esc_url($profile_cover).'" class="das-u-profile-cover" height="100" width="100" alt="Profile Picture">';
                                                                    }else{
                                                                        echo '<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/placeholder.jpg" class="das-u-profile-cover" height="100" width="100" alt="Profile Picture">';
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <div class="upload-profile-area">
                                                                    <input type="file" id="profile-cover-pic" name="profile-cover-pic" accept="image/*">
                                                                    <span class="upload-profile" id="upload-profile-cover-img-btn">Upload cover image</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-group">
                                                            <label for="first_name">First Name</label>
                                                            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $current_user->user_firstname ); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-group">
                                                            <label for="last_name">Last Name</label>
                                                            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $current_user->user_lastname ); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label for="email">Email</label>
                                                            <input type="email" id="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-12">
                                                        <div class="form-group">
                                                            <label for="userprofile-info">Profile Info</label>
                                                            <textarea name="profile_info" id="userprofile-info"><?= $profile_info ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-12">
                                                        <div class="form-group">
                                                            <button type="submit" id="updateprofile" class="btn btn-primary btn-large" name="update_profile">Update Profile</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <?php
                                    }else if($cPage == 'c-password'){
                                        ?>
                                        <div class="profile-card">
                                            <h3>Change Password</h3>
                                            <form id="d-change-password" method="POST">
                                                <div class="row">
                                                    <div class="col-12 col-lg-12">
                                                        <div class="form-group">
                                                            <div id="l-success" style="display:none; color:green;"></div>
                                                            <div id="l-error" style="display:none; color:red;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-group">
                                                            <label for="new_password">New Password:</label>
                                                            <input type="password" id="new_password" name="new_password">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <div class="form-group">
                                                            <label for="confirm_new_password">Confirm New Password:</label>
                                                            <input type="password" id="confirm_new_password" name="confirm_new_password">
                                                        </div>
                                                        <?php wp_nonce_field('change_password_action', 'change_password_nonce'); ?>
                                                    </div>
                                                    <div class="col-12 col-lg-12">
                                                        <div class="form-group">
                                                            <button id="d-change-password-btn" class="btn btn-primary btn-large">Change Password</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php
                                    }else if($cPage == 'stripe-connect'){
                                        $options            =   get_option('stripe_settings');
                                        $stripe_api_secret  =   isset($options['stripe_api_secret']) ? esc_attr($options['stripe_api_secret']) : '';
                                        $stripe_client_id   =   isset($options['stripe_client_id']) ? esc_attr($options['stripe_client_id']) : '';
                                        ?>
                                        <div class="profile-card">
                                            <h3>Stripe Account</h3>
                                            <div class="connect-stripe-account-section">
                                                <script src="https://js.stripe.com/v3/"></script>
                                                <div id="tvadi-checkout-page" class="content-area">
                                                    <?php
                                                    if(!empty($stripe_api_secret) && !empty($stripe_client_id)){
                                                        require_once get_template_directory() . '/stripe/init.php';
                                                        \Stripe\Stripe::setApiKey($stripe_api_secret);
                                                        /**
                                                         * CONNECT ACCOUNT PROCESS....
                                                         */
                                                        if(isset($_GET['code']) && !empty($_GET['code'])){
                                                            $code = $_GET['code'];
                                                            
                                                            try {
                                                                $response = \Stripe\OAuth::token([
                                                                    'grant_type' => 'authorization_code',
                                                                    'code' => $code,
                                                                ]);

                                                                // You have the response containing the connected account ID
                                                                $connectedAccountId = $response->stripe_user_id;

                                                                // Save the connected account ID to your database or do something with it
                                                                // For demonstration, we just print it
                                                                // echo 'Connected Account ID: ' . $connectedAccountId;
                                                                $true = update_user_meta($user_id, 'connected_stripe_account_id', $connectedAccountId);
                                                                if($true){
                                                                    ?>
                                                                    <script>
                                                                        window.location = "<?= site_url() ?>/dashboard/?dpage=stripe-connect";
                                                                    </script>
                                                                    <?php
                                                                }

                                                            } catch (\Stripe\Exception\ApiErrorException $e) {
                                                                // Handle error
                                                                echo 'Error: ' . $e->getMessage();
                                                            }
                                                        }else{
                                                            // Handle the case where the authorization code is missing
                                                            // echo 'Authorization code not provided';
                                                        }

                                                        /**
                                                         * DISCONNECT ACCOUNT PROCESS...
                                                         */
                                                        if(isset($_POST['disconnect'])){
                                                            
                                                            $connectedAccountId = get_user_meta($user_id, 'connected_stripe_account_id', true);

                                                            try {
                                                                // Deauthorize the connected account
                                                                $response = \Stripe\OAuth::deauthorize([
                                                                    'client_id' => $stripe_client_id,
                                                                    'stripe_user_id' => $connectedAccountId,
                                                                ]);

                                                                $deleteaccount = delete_user_meta($user_id, 'connected_stripe_account_id');
                                                                if($deleteaccount){
                                                                    ?>
                                                                    <script>
                                                                        window.location = "<?= site_url() ?>/dashboard/?dpage=stripe-connect";
                                                                    </script>
                                                                    <?php
                                                                }
                                                                
                                                            } catch (\Stripe\Exception\ApiErrorException $e) {
                                                                // Handle error (e.g., display an error message)
                                                                echo 'Error disconnecting Stripe account: ' . $e->getMessage();
                                                            }
                                                        }

                                                        $checkisconnected = get_user_meta($user_id, 'connected_stripe_account_id', true);
                                                        if(empty($checkisconnected)){
                                                            echo '
                                                            <a href="https://connect.stripe.com/oauth/authorize?response_type=code&client_id='.$stripe_client_id.'&scope=read_write">
                                                                <button class="btn btn-primary">Connect Stripe Account</button>
                                                            </a>';
                                                        }else{
                                                            echo '
                                                            <form method="post">
                                                                <button type="submit" class="btn btn-primary" id="disconnect-account-btn" name="disconnect">Disconnect Stripe Account</button>
                                                            </form>
                                                            ';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
                <?php
            }else{
                ?>
                <script>
                    window.location.href='<?= site_url() ?>/login/';
                </script>
                <?php
            }
            return ob_get_clean();
        }

        /**
         * Edit profile ajax request handler
         */
        public static function hp_edit_profile_cb(){
            $current_user   =   wp_get_current_user();
            $user_id        =   (isset($current_user->ID) && !empty($current_user->ID)) ? $current_user->ID : '';
            $useremail      =   (isset($current_user->user_email) && !empty($current_user->user_email)) ? $current_user->user_email : '';
            $first_name     =   (isset($_POST['first_name']) && !empty($_POST['first_name'])) ? sanitize_text_field( $_POST['first_name'] ) : '';
            $last_name      =   (isset($_POST['last_name']) && !empty($_POST['last_name'])) ? sanitize_text_field( $_POST['last_name'] ) : '';
            $email          =   (isset($_POST['email']) && !empty($_POST['email'])) ? sanitize_email( $_POST['email'] ) : '';
            $profile_info   =   (isset($_POST['profile_info']) && !empty($_POST['profile_info'])) ? $_POST['profile_info'] : '';
            $return         =   [];
            if(empty($user_id)){
                $return['status']   = false;
                $return['message']  = 'You are not logged in!';
            }else if(empty($first_name) || empty($last_name) || empty($email)){
                $return['status']   = false;
                $return['message']  = 'All fields are required.';
            }else if($email != $useremail && email_exists($email)){
                $return['status']   = false;
                $return['message']  = 'Email id is already exists.';
            }else if(!is_email($email)){
                $return['status']   = false;
                $return['message']  = 'Please Enter Valid email address.';
            }else{
                $data = [
                    'ID'            =>  $user_id,
                    'first_name'    =>  $first_name,
                    'last_name'     =>  $last_name,
                ];
                if($email != $useremail){
                    $data['user_email'] = $email;
                }
                
                $updateUser = wp_update_user($data);

                //profile info
                update_user_meta($user_id, 'user_profile_info', $profile_info);
                
                //Upload User Profile Pic
                // if(isset($_FILES['profile-pic']) && !empty($_FILES['profile-pic']['name'])){
                //     $uploadedfile       =   $_FILES['profile-pic'];
                //     $upload_overrides   =   array('test_form' => false);
                //     $movefile           =   wp_handle_upload($uploadedfile, $upload_overrides);
                //     if($movefile && !isset($movefile['error'])){
                //         $url = $movefile['url'];
                //         update_user_meta($user_id, 'profile_picture', $url);

                //         $return['status']   =   true;
                //         $return['message']  =   'Profile updated successfully!';
                //     }else{
                //         $return['status']   =   false;
                //         $return['message']  =   'There was an error uploading the file: '.$movefile['error'];
                //     } 
                // }else{
                    $return['status']   =   true;
                    $return['message']  =   'Profile updated successfully!';
                // }
            }
            echo json_encode($return);
            exit;
        }

        /**
         * Change password ajax request handler
         */
        public static function hp_change_pass_cb(){
            // Get the current user ID
            $current_user_id    =   get_current_user_id();
            $password           =   (isset($_POST['new_password']) && !empty($_POST['new_password'])) ? $_POST['new_password'] : '';
            $confirm_password   =   (isset($_POST['confirm_new_password']) && !empty($_POST['confirm_new_password'])) ? $_POST['confirm_new_password'] : '';
            $return             =   [];
            if(!$current_user_id){
                $return['status'] = false;
                $return['message'] = 'You must be logged in!';
            }else if(empty($password) || empty($confirm_password)){
                $return['status'] = false;
                $return['message'] = 'Please fill all fields.';
            }else if($password !== $confirm_password){
                $return['status'] = false;
                $return['message'] = 'Passwords do not match.';
            }else if(strlen($password) < 6){
                $return['status'] = false;
                $return['message'] = 'Password must be 6-16 characters long.';
            }else if(strlen($password) > 16){
                $return['status'] = false;
                $return['message'] = 'Password must be 6-16 characters long.';
            }else{
                // Change user password
                wp_set_password($password, $current_user_id);
                $return['status'] = true;
                $return['message'] = 'Password changed successfully.';
            }

            echo json_encode($return);
            exit;
        }

        /**
         * UPDATE PROFILE IMAGE AJAX REQUEST HANDLER
         */
        public static function upload_profile_cb(){
            $return = [];
            if(!is_user_logged_in()){
                $return['status']   =   false;
                $return['msg']      =   'Please log in.';
            }else if(!isset($_FILES['file']) || empty($_FILES['file']['name'])){
                $return['status']   =   false;
                $return['msg']      =   'Please upload an image.';
            }else{
                $user_id                =   get_current_user_id();
                $uploadedfile           =   $_FILES['file'];
                $upload_overrides       =   array('test_form' => false);
                $movefile               =   wp_handle_upload($uploadedfile, $upload_overrides);
                if($movefile && !isset($movefile['error'])){
                    $url = $movefile['url'];
                    update_user_meta($user_id, 'profile_picture', $url);
                    $return['status']   =   true;
                    $return['msg']      =   'Profile pic updated successfully!';
                    $return['url']      =   $url;
                }else{
                    $return['status']   =   false;
                    $return['msg']      =   'There was an error uploading the file: '.$movefile['error'];
                } 
            }
            echo json_encode($return);
            exit();
        }

        /**
         * UPDATE PROFILE AJAX REQUEST HANDLER..
         */
        public static function upload_cover_cb(){
            $return = [];
            if(!is_user_logged_in()){
                $return['status']   =   false;
                $return['msg']      =   'Please log in.';
            }else if(!isset($_FILES['cover']) || empty($_FILES['cover']['name'])){
                $return['status']   =   false;
                $return['msg']      =   'No file selected!';
            }else{
                $user_id                =   get_current_user_id();
                $uploadedfile           =   $_FILES['cover'];
                $upload_overrides       =   array('test_form' => false);
                $movefile               =   wp_handle_upload($uploadedfile, $upload_overrides);
                if($movefile && !isset($movefile['error'])){
                    $url = $movefile['url'];
                    update_user_meta($user_id, 'profile_cover_picture', $url);
                    $return['status']   =   true;
                    $return['msg']      =   'Profile cover updated successfully!';
                    $return['url']      =   $url;
                }else{
                    $return['status']   =   false;
                    $return['msg']      =   'There was an error uploading the file: '.$movefile['error'];
                } 
            }
            echo json_encode($return);
            exit();
        }
    }

    HplrDashboard::init();
}