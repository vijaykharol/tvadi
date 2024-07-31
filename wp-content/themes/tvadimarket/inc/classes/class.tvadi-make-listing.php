<?php
// Make sure the file is not accessed directly
if(!defined('ABSPATH')){
    exit; // Exit if accessed directly
}
/**
 * TvadiMakeListing
 */
if(!class_exists('TvadiMakeListing', false)){
    class TvadiMakeListing{
        public static function init(){
            //MAKE LISTING
            add_action( 'wp_ajax_make_listing', [__CLASS__, 'make_listing_cb'] );
            add_action( 'wp_ajax_nopriv_make_listing', [__CLASS__, 'make_listing_cb'] );

            //UPDATE LISTING
            add_action( 'wp_ajax_update_listing', [__CLASS__, 'update_listing_cb'] );
            add_action( 'wp_ajax_nopriv_update_listing', [__CLASS__, 'update_listing_cb'] );

            //Platform process
            add_action( 'wp_ajax_platform_process', [__CLASS__, 'platform_process_cb'] );
            add_action( 'wp_ajax_nopriv_platform_process', [__CLASS__, 'platform_process_cb'] );
        }

        /**
         * MAKE LISTING.......
         */
        public static function make_listing_cb(){
            $return     =   [];
            if(is_user_logged_in()){
                //user id
                $user_id                            =   get_current_user_id();
                $listing_type                       =   (isset($_POST['listing_type'])                      &&   !empty($_POST['listing_type']))                        ?   sanitize_text_field($_POST['listing_type'])                     :   '';
                $title                              =   (isset($_POST['title'])                             &&   !empty($_POST['title']))                               ?   sanitize_text_field($_POST['title'])                            :   '';
                $description                        =   (isset($_POST['description'])                       &&   !empty($_POST['description']))                         ?   sanitize_textarea_field($_POST['description'])                  :   '';
                $keywords                           =   (isset($_POST['keywords'])                          &&   !empty($_POST['keywords']))                            ?   sanitize_text_field($_POST['keywords'])                         :   '';
                $plateform                          =   (isset($_POST['plateform'])                         &&   !empty($_POST['plateform']))                           ?   sanitize_text_field($_POST['plateform'])                        :   '';
                $timeline                           =   (isset($_POST['timeline'])                          &&   !empty($_POST['timeline']))                            ?   sanitize_text_field($_POST['timeline'])                         :   '';
                $auction_listing                    =   (isset($_POST['auction_listing'])                   &&   !empty($_POST['auction_listing']))                     ?   sanitize_text_field($_POST['auction_listing'])                  :   '';
                $auction_length                     =   (isset($_POST['auction_length'])                    &&   !empty($_POST['auction_length']))                      ?   sanitize_text_field($_POST['auction_length'])                   :   '';
                $reserve_amount                     =   (isset($_POST['reserve_amount'])                    &&   !empty($_POST['reserve_amount']))                      ?   (int)sanitize_text_field($_POST['reserve_amount'])              :   '';
                $budget                             =   (isset($_POST['budget'])                            &&   !empty($_POST['budget']))                              ?   (int)sanitize_text_field($_POST['budget'])                      :   '';
                $hourly_onetime                     =   (isset($_POST['hourly_onetime'])                    &&   !empty($_POST['hourly_onetime']))                      ?   sanitize_text_field($_POST['hourly_onetime'])                   :   '';
                $currency                           =   (isset($_POST['currency'])                          &&   !empty($_POST['currency']))                            ?   sanitize_text_field($_POST['currency'])                         :   '';
                $sku                                =   (isset($_POST['sku'])                               &&   !empty($_POST['sku']))                                 ?   sanitize_text_field($_POST['sku'])                              :   '';
                $phone                              =   (isset($_POST['phone'])                             &&   !empty($_POST['phone']))                               ?   sanitize_text_field($_POST['phone'])                            :   '';
                $qr_code                            =   (isset($_POST['qr_code'])                           &&   !empty($_POST['qr_code']))                             ?   sanitize_text_field($_POST['qr_code'])                          :   '';
                $url                                =   (isset($_POST['url'])                               &&   !empty($_POST['url']))                                 ?   esc_url_raw($_POST['url'])                                      :   '';
                $additional_listing_media           =   (isset($_POST['additional_listing_media'])          &&   !empty($_POST['additional_listing_media']))            ?   esc_url_raw($_POST['additional_listing_media'])                 :   '';
                $location                           =   (isset($_POST['location'])                          &&   !empty($_POST['location']))                            ?   sanitize_text_field($_POST['location'])                         :   '';
                $final_product_proof_of_service     =   (isset($_POST['final_product_proof_of_service'])    &&   !empty($_POST['final_product_proof_of_service']))      ?   sanitize_text_field($_POST['final_product_proof_of_service'])   :   '';
                $playlist_id                        =   (isset($_POST['playlist_id'])                       &&   !empty($_POST['playlist_id']))                         ?   sanitize_text_field($_POST['playlist_id'])                      :   '';
                $upload_img                         =   (isset($_FILES['upload_img']))                                                                                  ?   $_FILES['upload_img']                                           :   '';
                // GATHER POST DATA
                $listing_data = [
                    'post_type'     =>  $listing_type,
                    'post_title'    =>  $title,
                    'post_content'  =>  $description,
                    'post_status'   =>  'draft',
                    'post_author'   =>  $user_id,
                ];
                // INSERT THE POST INTO DATABASE.
                $listingID  =   wp_insert_post( $listing_data );
                if(!is_wp_error($listingID)){
                    if(!empty($upload_img['name'])){
                        $upload = wp_handle_upload($upload_img, array('test_form' => false));
                        if($upload && !isset($upload['error'])){
                            // Prepare an array of post data for the attachment
                            $attachment = [
                                'guid'              =>   $upload['url'],
                                'post_mime_type'    =>   $upload['type'],
                                'post_title'        =>   sanitize_file_name($upload_img['name']),
                                'post_content'      =>   '',
                                'post_status'       =>   'inherit',
                            ];
                            
                            // Insert the attachment into the Media Library and generate the metadata
                            $attachment_id = wp_insert_attachment($attachment, $upload['file'], $listingID);
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                            wp_update_attachment_metadata($attachment_id, $attach_data);
                            // Set the featured image for the post
                            set_post_thumbnail($listingID, $attachment_id);
                        }
                    }

                    // Assign the custom taxonomy term to the post
                    if(!empty($plateform)){
                        if($listing_type == 'makers_listing'){
                            $taxonomy = 'platform';
                        }else if($listing_type == 'market_listing'){
                            $taxonomy = 'market_platform';
                        }else if($listing_type == 'outlet_listing'){
                            $taxonomy = 'outlet_platform';
                        }
                        wp_set_post_terms( $listingID, $plateform, $taxonomy );
                    }

                    //INSERT/UPDATE META FIELDS
                    update_post_meta( $listingID, 'post_keywords', $keywords );
                    update_post_meta( $listingID, 'post_plateform', $plateform );
                    update_post_meta( $listingID, 'post_timeline', $timeline );
                    update_post_meta( $listingID, 'post_auction_listing', $auction_listing );
                    update_post_meta( $listingID, 'post_auction_length', $auction_length );
                    update_post_meta( $listingID, 'post_reserve_amount', $reserve_amount );
                    update_post_meta( $listingID, 'price_from', $budget );
                    update_post_meta( $listingID, 'post_hourly_onetime', $hourly_onetime );
                    update_post_meta( $listingID, 'post_currency', $currency );
                    update_post_meta( $listingID, 'post_sku', $sku );
                    update_post_meta( $listingID, 'post_phone', $phone );
                    update_post_meta( $listingID, 'post_qr_code', $qr_code );
                    update_post_meta( $listingID, 'post_url', $url );
                    update_post_meta( $listingID, 'post_additional_listing_media', $additional_listing_media );
                    update_post_meta( $listingID, 'post_location', $location );
                    update_post_meta( $listingID, 'post_final_product_proof_of_service', $final_product_proof_of_service );
                    update_post_meta( $listingID, 'post_playlist_id', $playlist_id );
                    $return['status']   =   true;
                    $return['message']  =   'Listing submitted successfully. Redirecting...';
                    $return['url']      =   "/listing-details/$listing_type/$listingID/";
                }else{
                    $return['status']   =   false;
                    $return['message']  =   'Error creating post: ' .$listingID->get_error_message();
                }
            }else{
                $return['status']       =   false;
                $return['message']      =   'You are currently not logged in.';
            }
            echo json_encode($return);
            exit();
        }

        /**
         * UPDATE LISTING...
         */
        public static function update_listing_cb(){
            $return     =   [];
            $user_id                            =   get_current_user_id();
            $listing_id                         =   (isset($_POST['listing_id'])                        &&   !empty($_POST['listing_id']))                          ?   sanitize_text_field($_POST['listing_id'])                       :   '';
            $listing_type                       =   (isset($_POST['listing_type'])                      &&   !empty($_POST['listing_type']))                        ?   sanitize_text_field($_POST['listing_type'])                     :   '';
            $title                              =   (isset($_POST['title'])                             &&   !empty($_POST['title']))                               ?   sanitize_text_field($_POST['title'])                            :   '';
            $description                        =   (isset($_POST['description'])                       &&   !empty($_POST['description']))                         ?   sanitize_textarea_field($_POST['description'])                  :   '';
            $keywords                           =   (isset($_POST['keywords'])                          &&   !empty($_POST['keywords']))                            ?   sanitize_text_field($_POST['keywords'])                         :   '';
            $plateform                          =   (isset($_POST['plateform'])                         &&   !empty($_POST['plateform']))                           ?   sanitize_text_field($_POST['plateform'])                        :   '';
            $timeline                           =   (isset($_POST['timeline'])                          &&   !empty($_POST['timeline']))                            ?   sanitize_text_field($_POST['timeline'])                         :   '';
            $auction_listing                    =   (isset($_POST['auction_listing'])                   &&   !empty($_POST['auction_listing']))                     ?   sanitize_text_field($_POST['auction_listing'])                  :   '';
            $auction_length                     =   (isset($_POST['auction_length'])                    &&   !empty($_POST['auction_length']))                      ?   sanitize_text_field($_POST['auction_length'])                   :   '';
            $reserve_amount                     =   (isset($_POST['reserve_amount'])                    &&   !empty($_POST['reserve_amount']))                      ?   (int)sanitize_text_field($_POST['reserve_amount'])              :   '';
            $budget                             =   (isset($_POST['budget'])                            &&   !empty($_POST['budget']))                              ?   (int)sanitize_text_field($_POST['budget'])                      :   '';
            $hourly_onetime                     =   (isset($_POST['hourly_onetime'])                    &&   !empty($_POST['hourly_onetime']))                      ?   sanitize_text_field($_POST['hourly_onetime'])                   :   '';
            $currency                           =   (isset($_POST['currency'])                          &&   !empty($_POST['currency']))                            ?   sanitize_text_field($_POST['currency'])                         :   '';
            $sku                                =   (isset($_POST['sku'])                               &&   !empty($_POST['sku']))                                 ?   sanitize_text_field($_POST['sku'])                              :   '';
            $phone                              =   (isset($_POST['phone'])                             &&   !empty($_POST['phone']))                               ?   sanitize_text_field($_POST['phone'])                            :   '';
            $qr_code                            =   (isset($_POST['qr_code'])                           &&   !empty($_POST['qr_code']))                             ?   sanitize_text_field($_POST['qr_code'])                          :   '';
            $url                                =   (isset($_POST['url'])                               &&   !empty($_POST['url']))                                 ?   esc_url_raw($_POST['url'])                                      :   '';
            $additional_listing_media           =   (isset($_POST['additional_listing_media'])          &&   !empty($_POST['additional_listing_media']))            ?   esc_url_raw($_POST['additional_listing_media'])                 :   '';
            $location                           =   (isset($_POST['location'])                          &&   !empty($_POST['location']))                            ?   sanitize_text_field($_POST['location'])                         :   '';
            $final_product_proof_of_service     =   (isset($_POST['final_product_proof_of_service'])    &&   !empty($_POST['final_product_proof_of_service']))      ?   sanitize_text_field($_POST['final_product_proof_of_service'])   :   '';
            $playlist_id                        =   (isset($_POST['playlist_id'])                       &&   !empty($_POST['playlist_id']))                         ?   sanitize_text_field($_POST['playlist_id'])                      :   '';
            $upload_img                         =   (isset($_FILES['upload_img']))                                                                                  ?   $_FILES['upload_img']                                           :   '';
            // Get the author ID of the post
            $author_id = get_post_field('post_author', $listing_id);
            if(!is_user_logged_in()){
                $return['status']   =   false;
                $return['message']  =   'Currently you are not logged in. <a href="/login/">Click here to login</a>';
            }else if(empty($listing_id)){
                $return['status']   =   false;
                $return['message']  =   'Not getting listing id for update, please refresh the page and try again. Thanks';
            }else if($author_id != $user_id){
                $return['status']   =   false;
                $return['message']  =   'You do not have permission to update this listing because you are not the author.';
            }else if(empty($listing_type)){
                $return['status']   =   false;
                $return['message']  =   'Please select type of listing.';
            }else if(empty($title)){
                $return['status']   =   false;
                $return['message']  =   'Listing title is required.';
            }else{
                $listing_data = [
                    'ID'            =>  $listing_id,
                    'post_type'     =>  $listing_type,
                    'post_title'    =>  $title,
                    'post_content'  =>  $description,
                ];
                // Update the post
                $updated_listing_id = wp_update_post($listing_data);
                
                if(is_wp_error($updated_listing_id)){
                    $error_message = $updated_listing_id->get_error_message();
                    $return['status']   =   false;
                    $return['message']  =   "Error updating post: $error_message";
                }else{

                    if(!empty($upload_img['name'])){
                        $upload = wp_handle_upload($upload_img, array('test_form' => false));
                        if($upload && !isset($upload['error'])){
                            $attachment = [
                                'guid'              =>  $upload['url'],
                                'post_mime_type'    =>  $upload['type'],
                                'post_title'        =>  sanitize_file_name($upload_img['name']),
                                'post_content'      =>  '',
                                'post_status'       =>  'inherit',
                            ];

                            $attachment_id = wp_insert_attachment($attachment, $upload['file'], $updated_listing_id);
                            
                            if(!is_wp_error($attachment_id)){
                                require_once(ABSPATH . 'wp-admin/includes/image.php');
                                $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                                wp_update_attachment_metadata($attachment_id, $attach_data);
                                // Update the featured image for the post
                                set_post_thumbnail($updated_listing_id, $attachment_id);
                            }
                        }
                    }

                    // Assign the custom taxonomy term to the post
                    if(!empty($plateform)){
                        if($listing_type == 'makers_listing'){
                            $taxonomy = 'platform';
                        }else if($listing_type == 'market_listing'){
                            $taxonomy = 'market_platform';
                        }else if($listing_type == 'outlet_listing'){
                            $taxonomy = 'outlet_platform';
                        }
                        wp_set_post_terms( $updated_listing_id, array($plateform), $taxonomy );
                    }
                    
                    //UPDATE META FIELDS
                    update_post_meta( $updated_listing_id, 'post_keywords', $keywords );
                    update_post_meta( $updated_listing_id, 'post_plateform', $plateform );
                    update_post_meta( $updated_listing_id, 'post_timeline', $timeline );
                    update_post_meta( $updated_listing_id, 'post_auction_listing', $auction_listing );
                    update_post_meta( $updated_listing_id, 'post_auction_length', $auction_length );
                    update_post_meta( $updated_listing_id, 'post_reserve_amount', $reserve_amount );
                    update_post_meta( $updated_listing_id, 'price_from', $budget );
                    update_post_meta( $updated_listing_id, 'post_hourly_onetime', $hourly_onetime );
                    update_post_meta( $updated_listing_id, 'post_currency', $currency );
                    update_post_meta( $updated_listing_id, 'post_sku', $sku );
                    update_post_meta( $updated_listing_id, 'post_phone', $phone );
                    update_post_meta( $updated_listing_id, 'post_qr_code', $qr_code );
                    update_post_meta( $updated_listing_id, 'post_url', $url );
                    update_post_meta( $updated_listing_id, 'post_additional_listing_media', $additional_listing_media );
                    update_post_meta( $updated_listing_id, 'post_location', $location );
                    update_post_meta( $updated_listing_id, 'post_final_product_proof_of_service', $final_product_proof_of_service );
                    update_post_meta( $updated_listing_id, 'post_playlist_id', $playlist_id );

                    $return['status']   =   true;
                    $return['message']  =   'Post updated successfully!';
                    $return['url']      =   "/listing-details/$listing_type/$updated_listing_id/";
                }
            }
            echo json_encode($return);
            exit();
        }

        /**
         * PLATFORM
         */
        public static function platform_process_cb(){
            $listing_type   =   (isset($_POST['listing_type']) && !empty($_POST['listing_type'])) ? $_POST['listing_type'] : '';
            $html           =   '<option value="">Choose an option</option>';
            if($listing_type == 'makers_listing'){

                $taxonomy_name = 'platform';
                $args = [
                    'taxonomy'      =>  $taxonomy_name,
                    'hide_empty'    =>  false,
                ];
                $terms = get_terms($args);
                if(!empty($terms)){
                    foreach($terms as $t){
                        $html .= '
                            <option value="'.$t->term_id.'">'.$t->name.'</option>
                        ';
                    }
                }

            }else if($listing_type == 'outlet_listing'){

                $taxonomy_name = 'outlet_platform';

                $args = [
                    'taxonomy'      =>  $taxonomy_name,
                    'hide_empty'    =>  false,
                ];
                $terms = get_terms($args);

                if(!empty($terms)){
                    foreach($terms as $t){
                        $html .= '
                            <option value="'.$t->term_id.'">'.$t->name.'</option>
                        ';
                    }
                }


            }else if($listing_type == 'market_listing'){

                $taxonomy_name = 'market_platform';
                
                $args = [
                    'taxonomy'      =>  $taxonomy_name,
                    'hide_empty'    =>  false,
                ];

                $terms  =   get_terms($args);
                
                if(!empty($terms)){
                    foreach($terms as $t){
                        $html .= '
                            <option value="'.$t->term_id.'">'.$t->name.'</option>
                        ';
                    }
                }

            }
            echo $html;
            exit();
        }
    }

    //CALLING TvadiMakeListing INIT METHOD
    TvadiMakeListing::init();
}
