<?php
// Make sure the file is not accessed directly
if(!defined('ABSPATH')){
    exit; // Exit if accessed directly
}

/**
 * CLASS TvadiFrontEnd
 */
if(!class_exists('TvadiFrontEnd', false)){
    class TvadiFrontEnd{
        public static function init(){
            // Like Request
            add_action( 'wp_ajax_process_tvadiLike', [__CLASS__, 'process_tvadiLike_cb'] );
            add_action( 'wp_ajax_nopriv_process_tvadiLike', [__CLASS__, 'process_tvadiLike_cb'] );

            // Term Like Request
            add_action( 'wp_ajax_tvadi_term_wishlist', [__CLASS__, 'tvadi_term_wishlist_cb'] );
            add_action( 'wp_ajax_nopriv_tvadi_term_wishlist', [__CLASS__, 'tvadi_term_wishlist_cb'] );

            // Remove Admin bar
            add_action( 'after_setup_theme', [__CLASS__, 'tvadi_remove_admin_bar'] );

            // Filter Maker
            add_action( 'wp_ajax_filter_makers', [__CLASS__, 'filter_makers_cb'] );
            add_action( 'wp_ajax_nopriv_filter_makers', [__CLASS__, 'filter_makers_cb'] );

            // FILTER OUTLET
            add_action( 'wp_ajax_filter_outlet', [__CLASS__, 'filter_outlet_cb'] );
            add_action( 'wp_ajax_nopriv_filter_outlet', [__CLASS__, 'filter_outlet_cb'] );

            //FILTER MARKET
            add_action( 'wp_ajax_filter_market_listing', [__CLASS__, 'filter_market_listing_cb'] );
            add_action( 'wp_ajax_nopriv_filter_market_listing', [__CLASS__, 'filter_market_listing_cb'] );
            
            //REWRITE RULE
            add_action( 'init', [__CLASS__, 'custom_dynamic_rewrite_rule'] );
            add_filter( 'query_vars', [__CLASS__, 'custom_query_vars'] );
            add_action( 'template_redirect', [__CLASS__, 'custom_template_redirect'] );
            // add_action('init', [__CLASS__, 'flush_rewrite_rules_on_init']);

            //LISTINGS IMPORT
            add_action( 'admin_menu', [__CLASS__, 'tvadi_listing_import_admin_menu'] );
            
            add_filter('comment_form_defaults', [__CLASS__, 'custom_comment_form_defaults']);

            add_filter('comment_form_fields', [__CLASS__, 'custom_comment_form_fields']);

            //featured lisitngs
            add_action( 'wp_ajax_featured_listings_process', [__CLASS__, 'featured_listings_process_cb'] );
            add_action( 'wp_ajax_nopriv_featured_listings_process', [__CLASS__, 'featured_listings_process_cb'] );
        }   

        /**
         * CALCULATE VIEWS ON VIDEO
         */
        public static function calculatePlaylistviews($number){
            if($number >= 1000000){
                return number_format($number / 1000000, 1) . 'M';
            }else if($number >= 1000){
                return number_format($number / 1000, 0) . 'k';
            }else{
                return $number;
            }
        }

        /**
         * LIKE REQUEST HANDLER
         */
        public static function process_tvadiLike_cb(){
            $postid =   (isset($_POST['postid']) && !empty($_POST['postid'])) ? $_POST['postid'] : '';
            $return =   [];
            if(!empty($postid) && is_user_logged_in()){
                $current_user_id    =   get_current_user_id();
                $likesData          =   get_user_meta($current_user_id, 'user_wishlist_detail', true);
                $likesDataArray     =   (!empty($likesData) && is_array($likesData)) ? (array) $likesData : [];
                if(!empty($likesDataArray) && in_array($postid, $likesDataArray)){
                    $key = array_search($postid, $likesDataArray);
                    if($key !== false) {
                        unset($likesDataArray[$key]);

                        $return['process'] = 'unlike';
                    } 
                }else{
                    $likesDataArray[] = $postid;
                    $return['process'] = 'like';
                }
                $updatelikes        =   update_user_meta($current_user_id, 'user_wishlist_detail', $likesDataArray);
                $return['status']   =   true;
            }else{
                $return['status'] = false;
            }
            echo json_encode($return);
            exit();
        }

        /**
         * TERM LIKE REQUEST HANDLER
         */
        public static function tvadi_term_wishlist_cb(){
            $term_id    =   (isset($_POST['term_id']) && !empty($_POST['term_id'])) ? $_POST['term_id'] : '';
            $wpQuery    =   new WP_Query([
                'post_type'         =>  'playlist',
                'post_status'       =>  'publish',
                'posts_per_page'    =>  -1,
                'fields'            =>  'ids', 
                'tax_query'         =>  [
                    [
                        'taxonomy'  =>  'trending', 
                        'field'     =>  'term_id',
                        'terms'     =>  $term_id,
                    ],
                ],
            ]);

            // Get post IDs from the query
            $post_ids   =   $wpQuery->posts;
            $return     =   [];
            if(!empty($term_id) && is_user_logged_in()){
                $current_user_id    =   get_current_user_id();
                //term
                $likesData          =   get_user_meta($current_user_id, 'user_term_wishlist', true);
                $likesDataArray     =   (!empty($likesData) && is_array($likesData)) ? (array) $likesData : [];
                //post
                $userlikedposts          =   get_user_meta($current_user_id, 'user_wishlist_detail', true);
                $userlikedpostsArray     =   (!empty($userlikedposts) && is_array($userlikedposts)) ? (array) $userlikedposts : [];

                if(!empty($likesDataArray) && in_array($term_id, $likesDataArray)){
                    //term unlike
                    $key = array_search($term_id, $likesDataArray);
                    if($key !== false){
                        unset($likesDataArray[$key]);
                    }
                    //post unlike
                    if(!empty($post_ids) && is_array($post_ids)){
                        foreach($post_ids as $pid){
                            if(in_array($pid, $userlikedpostsArray)){
                                $pkey = array_search($pid, $userlikedpostsArray);
                                if($pkey !== false){
                                    unset($userlikedpostsArray[$pkey]);
                                }
                            }   
                        }
                    }
                    $return['process'] = 'unlike';
                }else{
                    //Term Like
                    $likesDataArray[]   =   $term_id;

                    //Post Like
                    if(!empty($post_ids) && is_array($post_ids)){
                        foreach($post_ids as $pid){
                            if(!in_array($pid, $userlikedpostsArray)){
                                $userlikedpostsArray[] = $pid; 
                            }   
                        }
                    }
                    $return['process']  =   'like';
                }
                //Term Like
                $updatelikes        =   update_user_meta($current_user_id, 'user_term_wishlist', $likesDataArray);

                //Posts Like
                $updatelikes2       =   update_user_meta($current_user_id, 'user_wishlist_detail', $userlikedpostsArray);
                
                $return['status']   =   true;
            }else{
                $return['status'] = false;
            }
            echo json_encode($return);
            exit();
        }

        public static function tvadi_remove_admin_bar(){
            if(!is_admin()){
                show_admin_bar(false);
            }
        }

        /**
         * FILTER MAKERS REQUEST HANDLER
         */
        public static function filter_makers_cb(){
            $search         =   (isset($_POST['search']) && !empty($_POST['search'])) ? $_POST['search'] : '';
            //price
            $price_f_m      =   (isset($_POST['price_f_m']) && !empty($_POST['price_f_m'])) ? $_POST['price_f_m'] : '';
            $min_price      =   (isset($_POST['min_price'])) ? (int) $_POST['min_price'] : 0;
            $max_price      =   (isset($_POST['max_price'])) ? (int) $_POST['max_price'] : 1000;
            //date
            $start_date     =   (isset($_POST['start_date']) && !empty($_POST['start_date'])) ? $_POST['start_date'] : '';
            $end_date       =   (isset($_POST['end_date']) && !empty($_POST['end_date'])) ? $_POST['end_date'] : '';

            //plateforms
            $plateforms     =   (isset($_POST['plateforms']) && !empty($_POST['plateforms'])) ? (array) $_POST['plateforms'] : '';
            $listing_type   =   (isset($_POST['listing_type']) && !empty($_POST['listing_type'])) ? $_POST['listing_type'] : 'makers_listing';

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
                    'relation' => 'AND',
                    [
                        'taxonomy' => $taxonomy_name,
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
         * FILTER OUTLET REQUEST HANDLER
         */
        public static function filter_outlet_cb(){
            $search         =   (isset($_POST['search']) && !empty($_POST['search'])) ? $_POST['search'] : '';
            //price
            $price_f_m      =   (isset($_POST['price_f_m']) && !empty($_POST['price_f_m'])) ? $_POST['price_f_m'] : '';
            $min_price      =   (isset($_POST['min_price'])) ? (int) $_POST['min_price'] : 0;
            $max_price      =   (isset($_POST['max_price'])) ? (int) $_POST['max_price'] : 1000;
            //date
            $start_date     =   (isset($_POST['start_date']) && !empty($_POST['start_date'])) ? $_POST['start_date'] : '';
            $end_date       =   (isset($_POST['end_date']) && !empty($_POST['end_date'])) ? $_POST['end_date'] : '';

            //plateforms
            $plateforms     =   (isset($_POST['plateforms']) && !empty($_POST['plateforms'])) ? (array) $_POST['plateforms'] : '';

            $listing_type   =   (isset($_POST['listing_type']) && !empty($_POST['listing_type'])) ? $_POST['listing_type'] : 'outlet_listing';
            
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
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'outlet_platform',
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
         * FILTER MARKET LISTINGS REQUEST HANDLER
         */
        public static function filter_market_listing_cb(){
            $search         =   (isset($_POST['search']) && !empty($_POST['search'])) ? $_POST['search'] : '';
            //price
            $price_f_m      =   (isset($_POST['price_f_m']) && !empty($_POST['price_f_m'])) ? $_POST['price_f_m'] : '';
            $min_price      =   (isset($_POST['min_price'])) ? (int) $_POST['min_price'] : 0;
            $max_price      =   (isset($_POST['max_price'])) ? (int) $_POST['max_price'] : 1000;
            //date
            $start_date     =   (isset($_POST['start_date']) && !empty($_POST['start_date'])) ? $_POST['start_date'] : '';
            $end_date       =   (isset($_POST['end_date']) && !empty($_POST['end_date'])) ? $_POST['end_date'] : '';

            //plateforms
            $plateforms     =   (isset($_POST['plateforms']) && !empty($_POST['plateforms'])) ? (array) $_POST['plateforms'] : '';
            $listing_type   =   (isset($_POST['listing_type']) && !empty($_POST['listing_type'])) ? $_POST['listing_type'] : 'market_listing';
            
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

            $arguements     =   [
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
                    'relation' => 'AND',
                    [
                        'taxonomy' =>   $taxonomy_name,
                        'field'    =>   'term_id',
                        'terms'    =>   $plateforms,
                        'operator' =>   'IN',
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
         * REWRITE RULE..
         */
        public static function custom_dynamic_rewrite_rule(){
            add_rewrite_rule(
                '^listing-details/([^/]+)/([0-9]+)/?$',
                'index.php?pagename=listing-details&listing_type=$matches[1]&listing_id=$matches[2]',
                'top'
            );

            add_rewrite_rule('^profile/([0-9]+)/?$', 'index.php?pagename=profile&puserid=$matches[1]', 'top');
        }

        public static function custom_query_vars($vars){
            $vars[] = 'listing_type';
            $vars[] = 'listing_id';
            $vars[] = 'puserid';
            return $vars;
        }
        
        public static function custom_template_redirect(){
            if (get_query_var('type') && get_query_var('id') && is_page('listing-details')) {
                include(get_template_directory() . '/listing-details.php');
                exit;
            }

            if(get_query_var('puserid') && get_query_var('id') && is_page('profile')){
                include(get_template_directory() . '/profile-page.php');
                exit;
            }
        }

        public static function flush_rewrite_rules_on_init(){
            flush_rewrite_rules();
        }

        /**
         * IMPORT PLAYLISTS ADMIN MENU..
         */
        public static function tvadi_listing_import_admin_menu(){
            // Add the parent menu
            add_menu_page(
                'Import Listings',    
                'Import Listings',    
                'manage_options',     
                'import-listings',    
                [__CLASS__, 'makers_posts_import_cb'],                   
                'dashicons-upload',   
                30                    
            );

            
            add_submenu_page(
                'import-listings',             
                'Import Playlists',            
                'Import Playlists',            
                'manage_options',              
                'playlists-post-importer',     
                [__CLASS__, 'playlists_posts_import_cb']  
            );

            add_submenu_page(
                'import-listings',             
                'Import Makers',               
                'Import Makers',               
                'manage_options',              
                'makers-post-importer',        
                [__CLASS__, 'makers_posts_import_cb']  
            );

            add_submenu_page(
                'import-listings',             
                'Import Outlets',              
                'Import Outlets',              
                'manage_options',              
                'outlets-post-importer',       
                [__CLASS__, 'outlets_posts_import_cb']  
            );

            add_submenu_page(
                'import-listings',      
                'Import Articles',     
                'Import Articles',     
                'manage_options',      
                'import-articles',     
                [__CLASS__, 'import_articles_page_cb']
            );

        }

        /**
         * IMPORT ARTICLES PAGE
         */
        public static function import_articles_page_cb(){
            ?>
            <div class="wrap" id="importarticles-section">
                <h1>Articles Importer</h1>
                <form id="articles-importForm" enctype="multipart/form-data" method="POST">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="articles_import_file">Import File (CSV):</label></th>
                            <td><input type="file" name="articles_import_file" id="articles_import_file" accept=".csv"/></td>
                        </tr>
                    </table>
                    <?php submit_button('Import'); ?>
                </form>
                <?php self::articles_handle_file_upload(); ?>
            </div>
            <?php
        }

        public static function articles_handle_file_upload(){
            if(isset($_FILES['articles_import_file']) && $_FILES['articles_import_file']['error'] == 0){
                // Check the file extension and MIME type
                $file_info              =   pathinfo($_FILES['articles_import_file']['name']);
                $file_extension         =   strtolower($file_info['extension']);
                $mime_type              =   mime_content_type($_FILES['articles_import_file']['tmp_name']);
                $allowed_extensions     =   ['csv'];
                $allowed_mime_types     =   ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'];

                if(!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)){
                    echo '<div class="error"><p>Please upload a valid CSV file.</p></div>';
                    return;
                }

                $file   =   $_FILES['articles_import_file']['tmp_name'];
                $handle =   fopen($file, 'r');
                if($handle !== false){
                    $counter = 0;
                    // Loop through CSV rows
                    while(($data = fgetcsv($handle, 1000, ',')) !== false){
                        if($counter != 0){
                            $title        =  $data[0];
                            $description  =  $data[1];
                            $image_url    =  $data[2];
                            $user_id      =  $data[3];
                            $posted_date  =  $data[4];

                            // Insert the post
                            $post_id = wp_insert_post([
                                'post_title'    =>  wp_strip_all_tags($title),
                                'post_content'  =>  $description,
                                'post_status'   =>  'publish',
                                'post_type'     =>  'post',
                                'post_author'   =>  $user_id,
                                'post_date'     =>  date('Y-m-d H:i:s', strtotime($posted_date)),
                            ]);

                            // If post was successfully inserted
                            if($post_id){
                                // Set featured image
                                if(!empty($image_url)){
                                    self::playlists_set_featured_image($post_id, $image_url);
                                }
                            }
                        }
                        $counter++;
                    }
                    fclose($handle);
                    echo '<div class="updated"><p>Import completed.</p></div>';
                }else{
                    echo '<div class="error"><p>Unable to open the file.</p></div>';
                }
            }
        }

        /**
         * IMPORT PLAYLIST PAGE
         */
        public static function playlists_posts_import_cb(){
            ?>
            <div class="wrap" id="importplaylists-section">
                <h1>Playlists Importer</h1>
                <form id="playlists-importForm" enctype="multipart/form-data" method="POST">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="plyalists_import_file">Import File (CSV):</label></th>
                            <td><input type="file" name="plyalists_import_file" id="plyalists_import_file" accept=".csv"/></td>
                        </tr>
                    </table>
                    <?php submit_button('Import'); ?>
                </form>
                <?php self::playlists_handle_file_upload(); ?>
            </div>
            <?php
        }

        /**
         * Handle the file upload and process the CSV file
         */
        public static function playlists_handle_file_upload(){
            if(isset($_FILES['plyalists_import_file']) && $_FILES['plyalists_import_file']['error'] == 0){
                // Check the file extension and MIME type
                $file_info          =   pathinfo($_FILES['plyalists_import_file']['name']);
                $file_extension     =   strtolower($file_info['extension']);
                $mime_type          =   mime_content_type($_FILES['plyalists_import_file']['tmp_name']);
                $allowed_extensions =   ['csv'];
                $allowed_mime_types =   ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'];

                if(!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)){
                    echo '<div class="error"><p>Please upload a valid CSV file.</p></div>';
                    return;
                }

                $file   =   $_FILES['plyalists_import_file']['tmp_name'];
                $handle =   fopen($file, 'r');
                if($handle !== false){
                    $counter = 0;
                    // Loop through CSV rows
                    while(($data = fgetcsv($handle, 1000, ',')) !== false){
                        if($counter != 0){
                            $title             =   $data[0];
                            $description       =   $data[1];
                            $video_url         =   $data[2];
                            $views             =   $data[3];
                            $channel_url       =   $data[4];
                            $embeded_code      =   $data[5];
                            $posted_date       =   $data[6];
                            $channel_name      =   $data[7];
                            $plays_at          =   $data[8];
                            $captions          =   $data[9];
                            $play_log          =   $data[10];
                            $clip_cleared      =   $data[11];
                            $clip_contact      =   $data[12];
                            $check_featured    =   $data[13];
                            $featured_text     =   $data[14];
                            $image_url         =   $data[15];
                            //categories
                            $devices           =   $data[16];
                            $featured_cat      =   $data[17];
                            $playlist_cat      =   $data[18];
                            $trending_type     =   $data[19];

                            // Insert the post
                            $post_id = wp_insert_post([
                                'post_title'    =>  wp_strip_all_tags($title),
                                'post_content'  =>  $description,
                                'post_status'   =>  'publish',
                                'post_type'     =>  'playlist',
                                'post_date'     =>  date('Y-m-d H:i:s', strtotime($posted_date)),
                            ]);

                            // If post was successfully inserted
                            if($post_id){
                                // Set featured image
                                self::playlists_set_featured_image($post_id, $image_url);
                                // Add/Update post meta
                                update_post_meta($post_id, 'video_url', $video_url);
                                update_post_meta($post_id, 'views', $views);
                                update_post_meta($post_id, 'channel_url', $channel_url);
                                update_post_meta($post_id, 'embeded_code', $embeded_code);
                                update_post_meta($post_id, 'channel_name', $channel_name);
                                update_post_meta($post_id, 'plays_at', $plays_at);
                                update_post_meta($post_id, 'captions', $captions);
                                update_post_meta($post_id, 'play_log', $play_log);
                                update_post_meta($post_id, 'clip_cleared', $clip_cleared);
                                update_post_meta($post_id, 'clip_contact', $clip_contact);
                                update_post_meta($post_id, 'check_featured', $check_featured);
                                update_post_meta($post_id, 'featured_text', $featured_text);

                                // channel
                                if(!empty($channel_name)){
                                    self::cpi_assign_custom_taxonomies($post_id, $channel_name, 'channel');
                                }
                                // devices
                                if(!empty($devices)){
                                    self::cpi_assign_custom_taxonomies($post_id, $devices, 'device');
                                }
                                // Featured Category
                                if(!empty($featured_cat)){
                                    self::cpi_assign_custom_taxonomies($post_id, $featured_cat, 'featured');
                                }
                                // playlists category
                                if(!empty($playlist_cat)){
                                    self::cpi_assign_custom_taxonomies($post_id, $playlist_cat, 'playlists_category');
                                }
                                // playlists category
                                if(!empty($playlist_cat)){
                                    self::cpi_assign_custom_taxonomies($post_id, $trending_type, 'trending');
                                }
                                
                            }
                        }
                        $counter++;
                    }
                    fclose($handle);
                    echo '<div class="updated"><p>Import completed.</p></div>';
                }else{
                    echo '<div class="error"><p>Unable to open the file.</p></div>';
                }
            }
        }

        /**
         * Function to set the featured image
         */
        public static function playlists_set_featured_image($post_id, $image_url){
            if(filter_var($image_url, FILTER_VALIDATE_URL)){
                $upload_dir     =   wp_upload_dir();
                $image_data     =   file_get_contents($image_url);
                $filename       =   basename($image_url);

                if(wp_mkdir_p($upload_dir['path'])){
                    $file = $upload_dir['path'] . '/' . $filename;
                }else{
                    $file = $upload_dir['basedir'] . '/' . $filename;
                }

                file_put_contents($file, $image_data);

                $wp_filetype = wp_check_filetype($filename, null);
                $attachment = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => sanitize_file_name($filename),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ];

                $attach_id = wp_insert_attachment($attachment, $file, $post_id);

                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($post_id, $attach_id);
            }
        }

        /**
         * Function to assign custom taxonomies
         */
        public static function cpi_assign_custom_taxonomies($post_id, $custom_taxonomy, $taxonomy){
            if(!empty($custom_taxonomy)){
                $taxonomy_list = explode(',', $custom_taxonomy);
                foreach($taxonomy_list as $taxonomy_name){
                    $taxonomy_name = trim($taxonomy_name);
                    $taxonomy_term = get_term_by('name', $taxonomy_name, $taxonomy);
                    if(!$taxonomy_term){
                        // If taxonomy term doesn't exist, create it
                        $taxonomy_term = wp_insert_term($taxonomy_name, $taxonomy);
                        if(is_wp_error($taxonomy_term)){
                            continue;
                        }
                        $taxonomy_term_id = $taxonomy_term['term_id'];
                    }else{
                        $taxonomy_term_id = $taxonomy_term->term_id;
                    }
                    wp_set_post_terms($post_id, [$taxonomy_term_id], $taxonomy, true);
                }
            }
        }

        /**
         * Makers import admin page..
         */
        public static function makers_posts_import_cb(){
            ?>
            <div class="wrap" id="importmaker-section">
                <h1>Makers Importer</h1>
                <form id="maker-importForm" enctype="multipart/form-data" method="POST">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="makers_import_file">Import File (CSV):</label></th>
                            <td><input type="file" name="makers_import_file" id="makers_import_file" accept=".csv"/></td>
                        </tr>
                    </table>
                    <?php submit_button('Import'); ?>
                </form>
                <?php self::makers_handle_file_upload(); ?>
            </div>
            <?php
        }

        /**
         * Handle the Makers file upload and process the CSV file
         */
        public static function makers_handle_file_upload(){
            if(isset($_FILES['makers_import_file']) && $_FILES['makers_import_file']['error'] == 0){
                // Check the file extension and MIME type
                $file_info          =   pathinfo($_FILES['makers_import_file']['name']);
                $file_extension     =   strtolower($file_info['extension']);
                $mime_type          =   mime_content_type($_FILES['makers_import_file']['tmp_name']);
                $allowed_extensions =   ['csv'];
                $allowed_mime_types =   ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'];

                if(!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)){
                    echo '<div class="error"><p>Please upload a valid CSV file.</p></div>';
                    return;
                }

                $file   =   $_FILES['makers_import_file']['tmp_name'];
                $handle =   fopen($file, 'r');
                if($handle !== false){
                    $counter = 0;
                    // Loop through CSV rows
                    while(($data = fgetcsv($handle, 1000, ',')) !== false){
                        if($counter != 0){
                            $title                              =   $data[0];
                            $subtitle                           =   $data[1];
                            $description                        =   $data[2];
                            $budget                             =   $data[3];
                            $total_comments                     =   $data[4];
                            $average_rating                     =   $data[5];
                            $playlist_title                     =   $data[6];
                            $keywords                           =   $data[7];
                            $timeline                           =   $data[8];
                            $auction_listing                    =   $data[9];
                            $auction_length_days                =   $data[10];
                            $reserve_amount                     =   $data[11];
                            $hourly_onetime                     =   $data[12];
                            $currency                           =   $data[13];
                            $sku                                =   $data[14];
                            $phone                              =   $data[15];
                            $qr_code                            =   $data[16];
                            $url                                =   $data[17];
                            $additional_listing_media           =   $data[18];
                            $post_location                      =   $data[19];
                            $final_product_proof_of_service     =   $data[20];
                            $featured_image_url                 =   $data[21];
                            $platform                           =   $data[22];
                            $posted_date                        =   $data[23];

                            // Insert the post
                            $post_id = wp_insert_post([
                                'post_title'    =>  wp_strip_all_tags($title),
                                'post_content'  =>  $description,
                                'post_status'   =>  'publish',
                                'post_type'     =>  'makers_listing',
                                'post_date'     =>  date('Y-m-d H:i:s', strtotime($posted_date)),
                            ]);

                            // If post was successfully inserted
                            if($post_id){
                                // Set featured image
                                if(!empty($featured_image_url)){
                                    self::playlists_set_featured_image($post_id, $featured_image_url);
                                }
                                // Add/Update post meta
                                update_post_meta($post_id, 'subtitle', $subtitle);
                                update_post_meta($post_id, 'price_from', $budget);
                                update_post_meta($post_id, 'total_ratings', $total_comments);
                                update_post_meta($post_id, 'average_rating', $average_rating);
                                $playlist       =   get_page_by_title($playlist_title, OBJECT, 'playlist');
                                $plyalist_id    =   (!empty($playlist)) ? $playlist->ID  : '';
                                update_post_meta($post_id, 'post_playlist_id', $plyalist_id);
                                update_post_meta($post_id, 'post_keywords', $keywords);
                                update_post_meta($post_id, 'post_timeline', $timeline);
                                update_post_meta($post_id, 'post_auction_listing', $auction_listing);
                                update_post_meta($post_id, 'post_auction_length', $auction_length_days);
                                update_post_meta($post_id, 'post_reserve_amount', $reserve_amount);
                                update_post_meta($post_id, 'post_hourly_onetime', $hourly_onetime);
                                update_post_meta($post_id, 'post_currency', $currency);
                                update_post_meta($post_id, 'post_sku', $sku);
                                update_post_meta($post_id, 'post_phone', $phone);
                                update_post_meta($post_id, 'post_qr_code', $qr_code);
                                update_post_meta($post_id, 'post_url', $url);
                                update_post_meta($post_id, 'post_additional_listing_media', $additional_listing_media);
                                update_post_meta($post_id, 'post_location', $post_location);
                                update_post_meta($post_id, 'post_final_product_proof_of_service', $final_product_proof_of_service);

                                // channel
                                if(!empty($platform)){
                                    self::cpi_assign_custom_taxonomies($post_id, $platform, 'platform');
                                }
                            }
                        }
                        $counter++;
                    }
                    fclose($handle);
                    echo '<div class="updated"><p>Import completed.</p></div>';
                }else{
                    echo '<div class="error"><p>Unable to open the file.</p></div>';
                }
            }
        }

        /**
         * OUTLET IMPORT ADMIN PAGE
         */
        public static function outlets_posts_import_cb(){
            ?>
            <div class="wrap" id="importoutlet-section">
                <h1>Outlets Importer</h1>
                <form id="outlet-importForm" enctype="multipart/form-data" method="POST">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="outlets_import_file">Import File (CSV):</label></th>
                            <td><input type="file" name="outlets_import_file" id="outlets_import_file" accept=".csv"/></td>
                        </tr>
                    </table>
                    <?php submit_button('Import'); ?>
                </form>
                <?php self::outlets_handle_file_upload(); ?>
            </div>
            <?php
        }

        /**
         * HANDLE OUTLETS IMPORT PROCESS
         */
        public static function outlets_handle_file_upload(){
            if(isset($_FILES['outlets_import_file']) && $_FILES['outlets_import_file']['error'] == 0){
                // Check the file extension and MIME type
                $file_info          =   pathinfo($_FILES['outlets_import_file']['name']);
                $file_extension     =   strtolower($file_info['extension']);
                $mime_type          =   mime_content_type($_FILES['outlets_import_file']['tmp_name']);
                $allowed_extensions =   ['csv'];
                $allowed_mime_types =   ['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel'];

                if(!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mime_types)){
                    echo '<div class="error"><p>Please upload a valid CSV file.</p></div>';
                    return;
                }

                $file   =   $_FILES['outlets_import_file']['tmp_name'];
                $handle =   fopen($file, 'r');
                if($handle !== false){
                    $counter = 0;
                    // Loop through CSV rows
                    while(($data = fgetcsv($handle, 1000, ',')) !== false){
                        if($counter != 0){
                            $title                              =   $data[0];
                            $subtitle                           =   $data[1];
                            $description                        =   $data[2];
                            $budget                             =   $data[3];
                            $total_comments                     =   $data[4];
                            $average_rating                     =   $data[5];
                            $playlist_title                     =   $data[6];
                            $keywords                           =   $data[7];
                            $timeline                           =   $data[8];
                            $auction_listing                    =   $data[9];
                            $auction_length_days                =   $data[10];
                            $reserve_amount                     =   $data[11];
                            $hourly_onetime                     =   $data[12];
                            $currency                           =   $data[13];
                            $sku                                =   $data[14];
                            $phone                              =   $data[15];
                            $qr_code                            =   $data[16];
                            $url                                =   $data[17];
                            $additional_listing_media           =   $data[18];
                            $post_location                      =   $data[19];
                            $final_product_proof_of_service     =   $data[20];
                            $featured_image_url                 =   $data[21];
                            $platform                           =   $data[22];
                            $posted_date                        =   $data[23];

                            // Insert the post
                            $post_id = wp_insert_post([
                                'post_title'    =>  wp_strip_all_tags($title),
                                'post_content'  =>  $description,
                                'post_status'   =>  'publish',
                                'post_type'     =>  'outlet_listing',
                                'post_date'     =>  date('Y-m-d H:i:s', strtotime($posted_date)),
                            ]);

                            // If post was successfully inserted
                            if($post_id){
                                // Set featured image
                                if(!empty($featured_image_url)){
                                    self::playlists_set_featured_image($post_id, $featured_image_url);
                                }
                                // Add/Update post meta
                                update_post_meta($post_id, 'subtitle', $subtitle);
                                update_post_meta($post_id, 'price_from', $budget);
                                update_post_meta($post_id, 'total_ratings', $total_comments);
                                update_post_meta($post_id, 'average_rating', $average_rating);
                                $playlist       =   get_page_by_title($playlist_title, OBJECT, 'playlist');
                                $plyalist_id    =   (!empty($playlist)) ? $playlist->ID  : '';
                                update_post_meta($post_id, 'post_playlist_id', $plyalist_id);
                                update_post_meta($post_id, 'post_keywords', $keywords);
                                update_post_meta($post_id, 'post_timeline', $timeline);
                                update_post_meta($post_id, 'post_auction_listing', $auction_listing);
                                update_post_meta($post_id, 'post_auction_length', $auction_length_days);
                                update_post_meta($post_id, 'post_reserve_amount', $reserve_amount);
                                update_post_meta($post_id, 'post_hourly_onetime', $hourly_onetime);
                                update_post_meta($post_id, 'post_currency', $currency);
                                update_post_meta($post_id, 'post_sku', $sku);
                                update_post_meta($post_id, 'post_phone', $phone);
                                update_post_meta($post_id, 'post_qr_code', $qr_code);
                                update_post_meta($post_id, 'post_url', $url);
                                update_post_meta($post_id, 'post_additional_listing_media', $additional_listing_media);
                                update_post_meta($post_id, 'post_location', $post_location);
                                update_post_meta($post_id, 'post_final_product_proof_of_service', $final_product_proof_of_service);

                                // platform
                                if(!empty($platform)){
                                    self::cpi_assign_custom_taxonomies($post_id, $platform, 'outlet_platform');
                                }
                            }
                        }
                        $counter++;
                    }
                    fclose($handle);
                    echo '<div class="updated"><p>Import completed.</p></div>';
                }else{
                    echo '<div class="error"><p>Unable to open the file.</p></div>';
                }
            }
        }

        public static function custom_comment_form_defaults($defaults){
            $defaults['title_reply']    = __('', 'tvadimarket');
            $defaults['label_submit']   = __('Send Inquiry', 'tvadimarket');
            return $defaults;
        }

        public static function custom_comment_form_fields($fields){
            $fields['comment'] = '<p class="comment-form-comment"><label for="comment">Inquiry <span class="required">*</span></label> <textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required=""></textarea></p>';
            return $fields;
        }

        public static function featured_listings_process_cb(){
            $args = [
                'post_type'         => ['makers_listing', 'outlet_listing', 'market_listing', 'auction_listing'],
                'post_status'       =>  'publish',
                'posts_per_page'    =>  -1,
                'order'             =>  'date',
                'orderby'           =>  'DESC',
                'meta_query'    => [
                    [
                        'key'       => 'is_featured',
                        'value'     => '1',
                        'compare'   => '='
                    ]
                ]
            ];
            $featuredListings = get_posts($args);
            if(!empty($featuredListings)){
                foreach($featuredListings as $maK){
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

    }
    
    /**
     * Calling Class init method..
     */
    TvadiFrontEnd::init();
}