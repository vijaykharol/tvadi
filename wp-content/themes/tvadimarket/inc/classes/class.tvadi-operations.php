<?php 
if(!defined('ABSPATH')){
    exit();
}
/**
 * CLASS TvadiOperations
 */
if(!class_exists('TvadiOperations', false)){
    class TvadiOperations{
        public static function init(){
            add_action( 'admin_menu', [__CLASS__, 'stripe_settings_menu_cb'] );
            add_action( 'admin_init', [__CLASS__, 'stripe_settings_init_cb'] );

            //ADVANCE SEARCH
            add_action( 'wp_ajax_adv_search', [__CLASS__, 'adv_search_cb'] ); 
            add_action( 'wp_ajax_nopriv_adv_search', [__CLASS__, 'adv_search_cb'] );

            //DUPLICATE POSTS
            add_filter( 'post_row_actions', [__CLASS__, 'add_duplicate_post_link'], 10, 2 );
            add_action( 'admin_init', [__CLASS__, 'handle_duplicate_post'] );

            //Checkout Ajax Request Handler
            add_action( 'wp_ajax_process_payment', [__CLASS__, 'process_payment_cb'] );
            add_action( 'wp_ajax_nopriv_process_payment', [__CLASS__, 'process_payment_cb'] );
            add_action( 'admin_init', [__CLASS__, 'playlists_handle_csv_export'] );
        }

        public static function stripe_settings_menu_cb(){
            add_menu_page(
                'Stripe Settings',
                'Stripe Settings',
                'manage_options',
                'stripe-settings',
                [__CLASS__, 'stripe_settings_page_cb'],
                'dashicons-admin-generic',
                90
            );

            add_submenu_page(
                'stripe-settings',
                'Commission Settings',
                'Commission Settings',
                'manage_options',
                'commission-settings',
                [__CLASS__, 'commission_settings_page_cb']
            );


            //For Export Listings..
            add_menu_page(
                'Export Listings',          
                'Export Listings',          
                'manage_options',           
                'export-listings',          
                [__CLASS__, 'export_listings_page_cb'],     
                'dashicons-download',       
                31                          
            );
            //export playlists
            add_submenu_page(
                'export-listings', 
                'Export Playlists', 
                'Export Playlists', 
                'manage_options', 
                'export-playlists', 
                [__CLASS__, 'playlists_export_page'] 
            );
            //export makers
            add_submenu_page(
                'export-listings', 
                'Export Makers', 
                'Export Makers', 
                'manage_options', 
                'export-makers-listings', 
                [__CLASS__, 'makers_listings_export_page'] 
            );

            //Export Outlets
            add_submenu_page(
                'export-listings', 
                'Export Outlets', 
                'Export Outlets', 
                'manage_options', 
                'export-outlets-listings', 
                [__CLASS__, 'outlets_listings_export_page'] 
            );

            //Export Articles
            add_submenu_page(
                'export-listings', 
                'Export Articles', 
                'Export Articles', 
                'manage_options', 
                'export-articles-listings', 
                [__CLASS__, 'articles_listings_export_page'] 
            );
        }

        public static function stripe_settings_page_cb(){
            ?>
            <style>
                div#admin-stripe-setting-section table.form-table input.stripe-field {
                    width: 100%;
                }
            </style>
            <div class="wrap">
                <h1>Stripe Settings</h1>
                <div class="row" id="admin-stripe-setting-section">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('stripe_settings_group');
                        do_settings_sections('stripe-settings');
                        submit_button();
                        ?>
                    </form>
                </div>
            </div>
            <?php
        }

        public static function stripe_settings_init_cb(){
            //Stripe Setting
            register_setting('stripe_settings_group', 'stripe_settings');

            add_settings_section(
                'stripe_settings_section',
                'Stripe API Settings',
                null,
                'stripe-settings'
            );

            add_settings_field(
                'stripe_api_key',
                'Stripe API Publishable key',
                [__CLASS__, 'stripe_api_key_render'],
                'stripe-settings',
                'stripe_settings_section'
            );

            add_settings_field(
                'stripe_api_secret',
                'Stripe API Secret key',
                [__CLASS__, 'stripe_api_secret_render'],
                'stripe-settings',
                'stripe_settings_section'
            ); 

            add_settings_field(
                'stripe_client_id',
                'Stripe OAuth Client ID',
                [__CLASS__, 'stripe_client_id_render'],
                'stripe-settings',
                'stripe_settings_section'
            ); 

            // Commission settings
            register_setting('commission_settings_group', 'commission_settings');

            add_settings_section(
                'commission_settings_section',
                'Commission and Taxes',
                null,
                'commission-settings'
            );

            add_settings_field(
                'commission_rate',
                'Commission Rate (%)',
                [__CLASS__, 'commission_rate_render'],
                'commission-settings',
                'commission_settings_section'
            );

            add_settings_field(
                'tax_rate',
                'Other Taxes Rate (%)',
                [__CLASS__, 'tax_rate_render'],
                'commission-settings',
                'commission_settings_section'
            );
        }

        public static function stripe_api_key_render(){
            $options = get_option('stripe_settings');
            ?>
            <input type="text" class="stripe-field" id="stripe_api_key" name="stripe_settings[stripe_api_key]" value="<?php echo isset($options['stripe_api_key']) ? esc_attr($options['stripe_api_key']) : ''; ?>" />
            <?php
        }

        public static function stripe_api_secret_render(){
            $options = get_option('stripe_settings');
            ?>
            <input type="password" class="stripe-field" id="stripe_api_secret" name="stripe_settings[stripe_api_secret]" value="<?php echo isset($options['stripe_api_secret']) ? esc_attr($options['stripe_api_secret']) : ''; ?>" />
            <?php
        }

        public static function stripe_client_id_render(){
            $options = get_option('stripe_settings');
            ?>
            <input type="text" class="stripe-field" id="stripe_client_id" name="stripe_settings[stripe_client_id]" value="<?php echo isset($options['stripe_client_id']) ? esc_attr($options['stripe_client_id']) : ''; ?>" />
            <?php
        }

        public static function commission_settings_page_cb(){
            ?>
            <div class="wrap">
                <h1>Commission Settings</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('commission_settings_group');
                    do_settings_sections('commission-settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        public static function commission_rate_render(){
            $options = get_option('commission_settings');
            ?>
            <input type="number" step="0.01" name="commission_settings[commission_rate]" value="<?php echo isset($options['commission_rate']) ? esc_attr($options['commission_rate']) : ''; ?>" /> %
            <?php
        }

        public static function tax_rate_render(){
            $options = get_option('commission_settings');
            ?>
            <input type="number" step="0.01" name="commission_settings[tax_rate]" value="<?php echo isset($options['tax_rate']) ? esc_attr($options['tax_rate']) : ''; ?>" /> %
            <?php
        }

        /**
         * ADVANCE SEARCH PROCESS HANDLER
         */
        public static function adv_search_cb(){
            $search         =   (isset($_POST['search']) && !empty($_POST['search'])) ? $_POST['search'] : '';
            //price
            $price_f_m      =   (isset($_POST['price_f_m']) && !empty($_POST['price_f_m'])) ? $_POST['price_f_m'] : '';
            $min_price      =   (isset($_POST['min_price'])) ? (int) $_POST['min_price'] : 0;
            $max_price      =   (isset($_POST['max_price'])) ? (int) $_POST['max_price'] : 1000;
            //date
            $start_date     =   (isset($_POST['start_date']) && !empty($_POST['start_date'])) ? $_POST['start_date'] : '';
            $end_date       =   (isset($_POST['end_date']) && !empty($_POST['end_date'])) ? $_POST['end_date'] : '';

            //plateforms
            $plateforms     =   (isset($_POST['plateforms']) && !empty($_POST['plateforms'])) ? (array) $_POST['plateforms']    : '';
            $listing_type   =   (isset($_POST['listing_type']) && !empty($_POST['listing_type'])) ? $_POST['listing_type']      : ['makers_listing', 'outlet_listing', 'market_listing', 'auction_listing'];

            $sortby         =   (isset($_POST['sortby']) && !empty($_POST['sortby'])) ? $_POST['sortby'] : 'date_desc';

            //rating
            $rating_filter_mode    =   (isset($_POST['rating_filter_mode']) && !empty($_POST['rating_filter_mode'])) ? $_POST['rating_filter_mode'] : '';
            $min_rating            =   (isset($_POST['min_rating'])) ? (int) $_POST['min_rating'] : '';
            $max_rating            =   (isset($_POST['max_rating'])) ? (int) $_POST['max_rating'] : '';

            //MetaQuery variable
            $meta_query = ['relation'  =>  'AND',];
            
            //Taxonomy Name
            if($listing_type == 'makers_listing'){
                $taxonomy_name = 'platform';
            }else if($listing_type == 'outlet_listing'){
                $taxonomy_name = 'outlet_platform';
            }else{
                $taxonomy_name = 'market_platform';
            }

            $arguements = [
                'post_status'       =>  'publish',
                'posts_per_page'    =>  -1,
            ];
            
            //post type
            if(!empty($listing_type)){
                $arguements['post_type'] = $listing_type;
            }

            //search
            if(!empty($search)){ 
                $arguements['s'] = $search;
            }

            //sortby
            if($sortby == 'date_desc'){
               $arguements['orderby']   =   'date';
               $arguements['order']     =   'DESC';
            }else if($sortby == 'date_asc'){
                $arguements['orderby']   =   'date';
                $arguements['order']     =   'ASC';
            }else if($sortby == 'price_asc'){
                $arguements['meta_key']     =   'price_from';
                $arguements['orderby']      =   'meta_value_num';
                $arguements['order']        =   'ASC';
            }else if($sortby == 'price_desc'){
                $arguements['meta_key']     =   'price_from';
                $arguements['orderby']      =   'meta_value_num';
                $arguements['order']        =   'DESC';
            }else if($sortby == 'popularity'){
                $arguements['meta_key']     =   'total_ratings';
                $arguements['orderby']      =   'meta_value_num';
                $arguements['order']        =   'DESC';
            }

            //price
            if(!empty($price_f_m)){
                $meta_query[] = [
                    'key'       =>  'price_from',
                    'value'     =>  $min_price,
                    'compare'   =>  '>=',
                    'type'      =>  'NUMERIC'
                ];

                $meta_query[] = [
                    'key'       =>  'price_from',
                    'value'     =>  $max_price,
                    'compare'   =>  '<=',
                    'type'      =>  'NUMERIC'
                ];
            }

            //Rating
            if(!empty($rating_filter_mode)){
                $meta_query[] = [
                    'key'       =>  'average_rating',
                    'value'     =>  $min_rating,
                    'compare'   =>  '>=',
                    'type'      =>  'NUMERIC'
                ];
                $meta_query[] = [
                    'key'       =>  'average_rating',
                    'value'     =>  $max_rating,
                    'compare'   =>  '<=',
                    'type'      =>  'NUMERIC'
                ];
            }

            //metaQuery
            if(!empty($meta_query)){
                $arguements['meta_query'] = $meta_query;
            }

            //Date Query
            if(!empty($start_date) && !empty($end_date)){
                $arguements['date_query'] = [
                    [
                        'after'     => $start_date,
                        'before'    => $end_date,
                        'inclusive' => true,
                    ],
                ];
            }

            //plateforms term query
            if(!empty($plateforms) && is_array($plateforms)){
                $arguements['tax_query'] = [
                    'relation' => 'OR',
                    [
                        'taxonomy' => 'market_platform',
                        'field'    => 'term_id',
                        'terms'    => $plateforms,
                        'operator' => 'IN',
                    ],
                    [
                        'taxonomy' => 'outlet_platform',
                        'field'    => 'term_id',
                        'terms'    => $plateforms,
                        'operator' => 'IN',
                    ],
                    [
                        'taxonomy' => 'platform',
                        'field'    => 'term_id',
                        'terms'    => $plateforms,
                        'operator' => 'IN',
                    ],
                ];
            }
            $makers = get_posts($arguements);
            if(!empty($makers)){
                foreach($makers as $maK){
                    $mkID               =   $maK->ID;
                    $maker_image_url    =   wp_get_attachment_url(get_post_thumbnail_id($mkID));
                    $price_from         =   get_post_meta($mkID, 'price_from', true);
                    $total_ratings      =   get_post_meta($mkID, 'total_ratings', true);
                    $average_rating     =   get_post_meta($mkID, 'average_rating', true);
                    ?>
                    <a href="<?= get_permalink($mkID) ?>" class="listing-item">
                        <div class="item-img"><img src="<?= $maker_image_url ?>" class="img-fluid" alt="<?= ucfirst($maK->post_title) ?>"/></div>
                        <div class="item-content">
                            <h4 class="title"><?= ucfirst($maK->post_title) ?></h4>
                            <div class="maK-desc"><p><?= substr(strip_tags($maK->post_content), 0, 80).'...' ?></p></div>
                            <div class="inner-content">
                                <div class="rating-comment">
                                    <span class="rating"><?= $average_rating ?> <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
                                    <span class="comments">(<?= $total_ratings ?>)</span>
                                </div>
                                <div class="price">
                                    From $<?= $price_from ?>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php
                }
            }else{
                echo '<p class="notfound-makers">No search results found.</p>';
            }
            exit();
        }

        /**
         * DUPLICATE POST ACTION..
         */
        public static function add_duplicate_post_link($actions, $post){
            if(!current_user_can('administrator')){
                return $actions;
            }
            // Check if this is your custom post type
            if($post->post_type == 'makers_listing' || $post->post_type == 'market_listing' || $post->post_type == 'outlet_listing' || $post->post_type == 'auction_listing' || $post->post_type == 'post'){ 
                $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=duplicate_post&post=' . $post->ID, 'duplicate_post_' . $post->ID) . '" title="Duplicate this post">Duplicate</a>';
            }
            return $actions;
        }
        
        /**
         * DUPLICATE PARTICULAR POST REQUEST HANDLER...
         */
        public static function handle_duplicate_post(){
            if(!isset($_GET['action']) || $_GET['action'] != 'duplicate_post'){
                return;
            }
        
            if(!isset($_GET['post']) || !is_numeric($_GET['post'])){
                return;
            }
        
            $post_id = intval($_GET['post']);
            if(!current_user_can('edit_post', $post_id)){
                wp_die('You are not allowed to duplicate this post.');
            }
        
            if(!check_admin_referer('duplicate_post_' . $post_id)){
                wp_die('Nonce verification failed.');
            }
        
            $post = get_post($post_id);
            if(!$post){
                wp_die('Post not found.');
            }
        
            $new_post_id = wp_insert_post(array(
                'post_title'   => $post->post_title . ' (Copy)',
                'post_content' => $post->post_content,
                'post_status'  => 'draft',
                'post_type'    => $post->post_type,
            ));
        
            if(is_wp_error($new_post_id)){
                wp_die('Error duplicating post.');
            }
        
            // Copy post metadata
            $meta_data = get_post_meta($post_id);
            foreach($meta_data as $key => $value){
                if('_edit_lock' == $key || '_edit_last' == $key){
                    continue;
                }
                foreach($value as $single_value){
                    add_post_meta($new_post_id, $key, $single_value);
                }
            }

            // Redirect to the edit screen for the new post
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit();
        }

        /**
         * CHECKOUT FORM PROCESS HANDLER..
         */
        // public static function process_payment_cb(){
        //     require_once get_template_directory() . '/stripe/init.php';
        //     $options            =   get_option('stripe_settings');
        //     $stripe_api_key     =   isset($options['stripe_api_key']) ? esc_attr($options['stripe_api_key']) : '';
        //     $stripe_api_secret  =   isset($options['stripe_api_secret']) ? esc_attr($options['stripe_api_secret']) : '';
        //     $stripe_client_id   =   isset($options['stripe_client_id']) ? esc_attr($options['stripe_client_id']) : '';

        //     \Stripe\Stripe::setApiKey($stripe_api_secret);

        //     $first_name     =   (isset($_POST['first_name']))       ?   sanitize_text_field($_POST['first_name'])       :   '';
        //     $last_name      =   (isset($_POST['last_name']))        ?   sanitize_text_field($_POST['last_name'])        :   '';
        //     $email          =   (isset($_POST['email']))            ?   sanitize_email($_POST['email'])                 :   '';
        //     $total_amount   =   (isset($_POST['total_amount']))     ?   floatval($_POST['total_amount']) * 100          :   0; // Convert to cents
        //     $seller_amount  =   (isset($_POST['seller_amount']))    ?   floatval($_POST['seller_amount']) * 100         :   0; // Convert to cents
        //     $token          =   (isset($_POST['stripeToken']))      ?   sanitize_text_field($_POST['stripeToken'])      :   '';
        //     $postAuthor     =   (isset($_POST['lisitng_author']))   ?   sanitize_text_field($_POST['lisitng_author'])   :   '';
        //     $lisitng_id     =   (isset($_POST['lisitng_id']))       ?   sanitize_text_field($_POST['lisitng_id'])       :   '';
        //     $listingData    =   get_post($lisitng_id);
        //     $product_title  =   (!empty($listingData)) ? $listingData->post_title   .' - '. $listingData->ID : '';
        //     $return         =   [];
        //     $author_stripe_account_id = get_user_meta($postAuthor, 'connected_stripe_account_id', true);
        //     if(empty($email)){
        //         $return['status']   = false;
        //         $return['message']  = 'Email is required!';
        //     }else if(empty($total_amount) || empty($postAuthor) || empty($lisitng_id) || empty($token) || empty($seller_amount)){
        //         $return['status']   = false;
        //         $return['message']  = 'Something is missing please restart process from listing page!';
        //     }else if(empty($author_stripe_account_id)){
        //         $return['status'] = false;
        //         $return['message'] = "The seller's Stripe account is not yet connected to the platform, so you cannot make payments yet.";
        //     }else{
        //         // Create a Customer
        //         $customer = \Stripe\Customer::create([
        //             'email'     =>  $email,
        //             'source'    =>  $token,
        //             'name'      =>  $first_name . ' ' . $last_name,
        //         ]);
                
        //         // Charge the Customer
        //         $charge = \Stripe\Charge::create([
        //             'amount'        =>  $total_amount,
        //             'currency'      =>  'usd',
        //             'customer'      =>  $customer->id,
        //             'description'   =>  $product_title,
        //             'metadata' => [
        //                 'first_name'    =>  $first_name,
        //                 'last_name'     =>  $last_name,
        //             ],
        //         ]);

        //         // Transfer to Seller
        //         $transfer = \Stripe\Transfer::create([
        //             'amount'            =>  $seller_amount,
        //             'currency'          =>  'usd',
        //             'destination'       =>  $author_stripe_account_id,
        //             'transfer_group'    =>  $charge->id,
        //         ]);

        //         $return['status']   =   true;
        //         $return['message']  =   'Process completed!';
        //     }
        //     echo json_encode($return);
        //     exit();
        // }

        public static function process_payment_cb(){
            require_once get_template_directory() . '/stripe/init.php';
        
            $options            =   get_option('stripe_settings');
            $stripe_api_key     =   isset($options['stripe_api_key'])       ? esc_attr($options['stripe_api_key'])      : '';
            $stripe_api_secret  =   isset($options['stripe_api_secret'])    ? esc_attr($options['stripe_api_secret'])   : '';
            $stripe_client_id   =   isset($options['stripe_client_id']) ? esc_attr($options['stripe_client_id']) : '';
        
            \Stripe\Stripe::setApiKey($stripe_api_secret);
        
            $first_name         =   isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
            $last_name          =   isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
            $email              =   isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $total_amount       =   isset($_POST['total_amount']) ? floatval($_POST['total_amount']) * 100 : 0; // Convert to cents
            $seller_amount      =   isset($_POST['seller_amount']) ? floatval($_POST['seller_amount']) * 100 : 0; // Convert to cents
            $token              =   isset($_POST['stripeToken']) ? sanitize_text_field($_POST['stripeToken']) : '';
            $postAuthor         =   isset($_POST['lisitng_author']) ? sanitize_text_field($_POST['lisitng_author']) : '';
            $lisitng_id         =   isset($_POST['lisitng_id']) ? sanitize_text_field($_POST['lisitng_id']) : '';
            $listingData        =   get_post($lisitng_id);
            $product_title      =   !empty($listingData) ? $listingData->post_title . ' - ' . $listingData->ID : '';
            $return             =   [];
            $author_stripe_account_id = get_user_meta($postAuthor, 'connected_stripe_account_id', true);
        
            try {
                if (empty($email)) {
                    throw new Exception('Email is required!');
                } elseif (empty($total_amount) || empty($postAuthor) || empty($lisitng_id) || empty($token) || empty($seller_amount)) {
                    throw new Exception('Something is missing. Please restart the process from the listing page!');
                } elseif (empty($author_stripe_account_id)) {
                    throw new Exception("The seller's Stripe account is not yet connected to the platform, so you cannot make payments yet.");
                } else {
                    // Create a Customer
                    $customer = \Stripe\Customer::create([
                        'email'     =>  $email,
                        'source'    =>  $token,
                        'name'      =>  $first_name . ' ' . $last_name,
                    ]);
        
                    // Charge the Customer
                    $charge = \Stripe\Charge::create([
                        'amount'        => $total_amount,
                        'currency'      => 'usd',
                        'customer'      => $customer->id,
                        'description'   => $product_title,
                        'metadata'      => [
                            'first_name'    => $first_name,
                            'last_name'     => $last_name,
                        ],
                    ]);
        
                    // Transfer to Seller
                    // $transfer = \Stripe\Transfer::create([
                    //     'amount'            =>  $seller_amount,
                    //     'currency'          =>  'usd',
                    //     'destination'       =>  $author_stripe_account_id,
                    //     'transfer_group'    =>  $charge->id,
                    // ]);
        
                    $return['status']   =   true;
                    $return['message']  =   'Process completed!';
                }
            } catch (Exception $e) {
                $return['status'] = false;
                $return['message'] = $e->getMessage();
            }
        
            echo json_encode($return);
            exit();
        }

        /**
         * EXPORT LISTINGS OPERATIONS
         */
        public static function export_listings_page_cb(){
            ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <div class="conte">
                    <p>Select an export option from the submenus.</p>
                </div>
            </div>
            <?php
        }

        public static function playlists_export_page(){
            ?>
            <div class="wrap">
                <h1>Export Playlists</h1>
                <form method="POST" action="">
                    <input type="hidden" name="export_playlists" value="1">
                    <input type="submit" class="button button-primary" value="Export CSV">
                </form>
            </div>
            <?php
        }

        public static function playlists_handle_csv_export(){
            //Export Playlists
            if(isset($_POST['export_playlists'])){
                $args = array(
                    'post_type'   => 'playlist',
                    'post_status' => 'publish',
                    'numberposts' => -1
                );
        
                $posts      =   get_posts($args);
                $date       =   date('Y-m-d');
                $filename   =   'playlists-export-'. $date .'.csv';
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                $output = fopen('php://output', 'w');
        
                // Add CSV header
                fputcsv($output, [
                    'Title', 'Description', 'Video URL', 'Views', 'Channel URL',
                    'Embedded Code', 'Posted Date', 'Channel Name', 'Plays At', 'Captions',
                    'Play Log', 'Clip Cleared', 'Clip Contact', 'Check Featured', 'Featured Text',
                    'Image URL', 'Devices', 'Featured Category', 'Playlist Category', 'Trending Type'
                ]);
        
                foreach($posts as $post){
                    $meta                   =   get_post_meta($post->ID);
                    $featured_image_url     =   get_the_post_thumbnail_url($post->ID, 'full');
                    fputcsv($output, [
                        $post->post_title,
                        $post->post_content,
                        $meta['video_url'][0] ?? '',
                        $meta['views'][0] ?? '',
                        $meta['channel_url'][0] ?? '',
                        $meta['embeded_code'][0] ?? '',
                        $post->post_date,
                        $meta['channel_name'][0] ?? '',
                        $meta['plays_at'][0] ?? '',
                        $meta['captions'][0] ?? '',
                        $meta['play_log'][0] ?? '',
                        $meta['clip_cleared'][0] ?? '',
                        $meta['clip_contact'][0] ?? '',
                        $meta['check_featured'][0] ?? '',
                        $meta['featured_text'][0] ?? '',
                        $featured_image_url ?? '',
                        implode(', ', wp_get_post_terms($post->ID, 'device', ['fields' => 'names'])),
                        implode(', ', wp_get_post_terms($post->ID, 'featured', ['fields' => 'names'])),
                        implode(', ', wp_get_post_terms($post->ID, 'playlists_category', ['fields' => 'names'])),
                        implode(', ', wp_get_post_terms($post->ID, 'trending', ['fields' => 'names']))
                    ]);
                }
                fclose($output);
                exit;
            }

            //Export Makers
            if(isset($_POST['export_makers'])){
                $args = array(
                    'post_type'   => 'makers_listing',
                    'post_status' => 'publish',
                    'numberposts' => -1
                );
        
                $posts      =   get_posts($args);
                $date       =   date('Y-m-d');
                $filename   =   'makers-export-' . $date . '.csv';
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $output = fopen('php://output', 'w');
        
                // Add CSV header
                fputcsv($output, [
                    'Title', 'Subtitle', 'Description', 'Budget', 'Total Comments',
                    'Average Rating', 'Playlist Title', 'Keywords', 'Timeline', 'Auction Listing',
                    'Auction Length Days', 'Reserve Amount', 'Hourly/One-time', 'Currency', 'SKU',
                    'Phone', 'QR Code', 'URL', 'Additional Listing Media', 'Post Location',
                    'Final Product Proof of Service', 'Featured Image URL', 'Platform', 'Posted Date'
                ]);
        
                foreach($posts as $post){
                    $meta                   =   get_post_meta($post->ID);
                    $featured_image_url     =   get_the_post_thumbnail_url($post->ID, 'full');
                    $playlist               =   get_post($meta['post_playlist_id'][0] ?? '');
                    $playlist_title         =   $playlist ? $playlist->post_title : '';
        
                    fputcsv($output, [
                        $post->post_title,
                        $meta['subtitle'][0] ?? '',
                        $post->post_content,
                        $meta['price_from'][0] ?? '',
                        $meta['total_ratings'][0] ?? '',
                        $meta['average_rating'][0] ?? '',
                        $playlist_title,
                        $meta['post_keywords'][0] ?? '',
                        $meta['post_timeline'][0] ?? '',
                        $meta['post_auction_listing'][0] ?? '',
                        $meta['post_auction_length'][0] ?? '',
                        $meta['post_reserve_amount'][0] ?? '',
                        $meta['post_hourly_onetime'][0] ?? '',
                        $meta['post_currency'][0] ?? '',
                        $meta['post_sku'][0] ?? '',
                        $meta['post_phone'][0] ?? '',
                        $meta['post_qr_code'][0] ?? '',
                        $meta['post_url'][0] ?? '',
                        $meta['post_additional_listing_media'][0] ?? '',
                        $meta['post_location'][0] ?? '',
                        $meta['post_final_product_proof_of_service'][0] ?? '',
                        $featured_image_url ?? '',
                        implode(', ', wp_get_post_terms($post->ID, 'platform', ['fields' => 'names'])),
                        $post->post_date
                    ]);
                }
                fclose($output);
                exit;
            }

            //Export Outlets
            if(isset($_POST['export_outlets'])){
                $args = array(
                    'post_type'   => 'outlet_listing',
                    'post_status' => 'publish',
                    'numberposts' => -1
                );
        
                $posts      = get_posts($args);
                $date       = date('Y-m-d');
                $filename   = 'outlets-export-' . $date . '.csv';
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $output = fopen('php://output', 'w');
        
                // Add CSV header
                fputcsv($output, [
                    'Title', 'Subtitle', 'Description', 'Budget', 'Total Comments',
                    'Average Rating', 'Playlist Title', 'Keywords', 'Timeline', 'Auction Listing',
                    'Auction Length Days', 'Reserve Amount', 'Hourly/One-time', 'Currency', 'SKU',
                    'Phone', 'QR Code', 'URL', 'Additional Listing Media', 'Post Location',
                    'Final Product Proof of Service', 'Featured Image URL', 'Platform', 'Posted Date'
                ]);
        
                foreach($posts as $post){
                    $meta                   =   get_post_meta($post->ID);
                    $featured_image_url     =   get_the_post_thumbnail_url($post->ID, 'full');
                    $playlist               =   get_post($meta['post_playlist_id'][0] ?? '');
                    $playlist_title         =   $playlist ? $playlist->post_title : '';
        
                    fputcsv($output, [
                        $post->post_title,
                        $meta['subtitle'][0] ?? '',
                        $post->post_content,
                        $meta['price_from'][0] ?? '',
                        $meta['total_ratings'][0] ?? '',
                        $meta['average_rating'][0] ?? '',
                        $playlist_title,
                        $meta['post_keywords'][0] ?? '',
                        $meta['post_timeline'][0] ?? '',
                        $meta['post_auction_listing'][0] ?? '',
                        $meta['post_auction_length'][0] ?? '',
                        $meta['post_reserve_amount'][0] ?? '',
                        $meta['post_hourly_onetime'][0] ?? '',
                        $meta['post_currency'][0] ?? '',
                        $meta['post_sku'][0] ?? '',
                        $meta['post_phone'][0] ?? '',
                        $meta['post_qr_code'][0] ?? '',
                        $meta['post_url'][0] ?? '',
                        $meta['post_additional_listing_media'][0] ?? '',
                        $meta['post_location'][0] ?? '',
                        $meta['post_final_product_proof_of_service'][0] ?? '',
                        $featured_image_url ?? '',
                        implode(', ', wp_get_post_terms($post->ID, 'outlet_platform', ['fields' => 'names'])),
                        $post->post_date
                    ]);
                }
                fclose($output);
                exit;
            }

            //Export Articles..
            if(isset($_POST['export_articles'])){
                $args = array(
                    'post_type'   => 'post',
                    'post_status' => 'publish',
                    'numberposts' => -1
                );
        
                $posts      = get_posts($args);
                $date       = date('Y-m-d');
                $filename   = 'articles-export-' . $date . '.csv';
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $output = fopen('php://output', 'w');
        
                // Add CSV header
                fputcsv($output, ['Title', 'Description', 'Image URL', 'User ID', 'Posted Date']);
        
                foreach($posts as $post){
                    $image_url = get_the_post_thumbnail_url($post->ID, 'full');
                    fputcsv($output, [
                        $post->post_title,
                        $post->post_content,
                        $image_url,
                        $post->post_author,
                        $post->post_date
                    ]);
                }
                fclose($output);
                exit;
            }
        }
        
        /**
         * export makers listings
         */
        public static function makers_listings_export_page(){
            ?>
            <div class="wrap">
                <h1>Export Makers</h1>
                <form method="POST" action="">
                    <input type="hidden" name="export_makers" value="1">
                    <input type="submit" class="button button-primary" value="Export CSV">
                </form>
            </div>
            <?php
        }

        /**
         * Export Outlets 
         */
        public static function outlets_listings_export_page(){
            ?>
            <div class="wrap">
                <h1>Export Outlets</h1>
                <form method="POST" action="">
                    <input type="hidden" name="export_outlets" value="1">
                    <input type="submit" class="button button-primary" value="Export CSV">
                </form>
            </div>
            <?php
        }

        /**
         * EXPORT ARTICLES
         */
        public static function articles_listings_export_page(){
            ?>
            <div class="wrap">
                <h1>Export Articles</h1>
                <form method="POST" action="">
                    <input type="hidden" name="export_articles" value="1">
                    <input type="submit" class="button button-primary" value="Export CSV">
                </form>
            </div>
            <?php
        }
    }
    TvadiOperations::init();
}