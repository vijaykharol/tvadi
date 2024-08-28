<?php 
if(!defined('ABSPATH')){
    exit();
}
/**
 * CLASS TvadiChatProcessHandler
 */
if(!class_exists('TvadiChatProcessHandler', false)){
    class TvadiChatProcessHandler{
        public static $defaultProfilesrc;

        public static function init(){
            require_once get_template_directory().'/inc/pusher/vendor/autoload.php';

            self::$defaultProfilesrc = site_url() . '/wp-content/themes/tvadimarket/default-profile/defaultProfilePurple.png';

            add_action( 'wp_ajax_add_contact_process', [__CLASS__, 'add_contact_process_cb'] );
            add_action( 'wp_ajax_nopriv_add_contact_process', [__CLASS__, 'add_contact_process_cb'] );

            //SEND MESSAGE PROCESS HANDLER
            add_action( 'wp_ajax_send_message', [__CLASS__, 'send_message_cb'] );
            add_action( 'wp_ajax_nopriv_send_message', [__CLASS__, 'send_message_cb'] );

            //Show user chat messages
            add_action( 'wp_ajax_show_user_chats', [__CLASS__, 'show_user_chats_cb'] );
            add_action( 'wp_ajax_nopriv_show_user_chats', [__CLASS__, 'show_user_chats_cb'] );

            //update user chat screen.
            add_action( 'wp_ajax_update_chat_screen', [__CLASS__, 'update_chat_screen_cb'] );
            add_action( 'wp_ajax_nopriv_update_chat_screen', [__CLASS__, 'update_chat_screen_cb'] );

            //Chat Tab Shortcode
            add_shortcode('tvadi_chat_model', [__CLASS__, 'tvadi_chat_model_cb']);
        }
        
        public static function add_contact_process_cb(){
            $post_author    =   (isset($_POST['post_author'])) ? $_POST['post_author'] : '';
            $user_id        =   (isset($_POST['user_id'])) ? $_POST['user_id'] : '';
            $post_id        =   (isset($_POST['post_id'])) ? $_POST['post_id'] : '';
            $return         =   [];
            global $wpdb;
            if(empty($user_id) || empty($post_author) || empty($post_id)){
                $return['status']   =   false;
                $return['message']  =   'Something went wrong please refresh the page and try again. Thanks!';
            }else{
                $checkContact = $wpdb->get_row("SELECT id FROM `".$wpdb->prefix."chat_main_tbl` WHERE (contact_user_1='$user_id' AND contact_user_2='$post_author') OR (contact_user_1='$post_author' AND contact_user_2='$user_id') LIMIT 1");
                if(empty($checkContact)){
                    $table_name = $wpdb->prefix.'chat_main_tbl';
                    $data = [
                        'contact_user_1'        =>  $user_id,
                        'contact_user_2'        =>  $post_author,
                        'contact_listing_id'    =>  $post_id,
                    ];
                    $wpdb->insert($table_name, $data);
                    $chatid = $wpdb->insert_id;
                    $return['status']       =   true;
                    $return['message']      =   'Connected Succesfully!';
                    $return['chat_url']     =   site_url().'/chat/?chat_id='.$chatid;
                }else{
                    $return['status']       =   true;
                    $return['message']      =   'Connected Succesfully!';
                    $return['chat_url']     =   site_url().'/chat/?chat_id='.$checkContact->id;
                }
            }
            echo json_encode($return);
            exit();
        }

        public static function send_message_cb(){
            global $wpdb;
            $receiver   =   (isset($_POST['receiver']))   ?   $_POST['receiver']  :     '';
            $sender     =   (isset($_POST['sender']))     ?   $_POST['sender']    :     '';
            $message    =   (isset($_POST['message']))    ?   $_POST['message']   :     '';
            $parent     =   (isset($_POST['parent']))     ?   $_POST['parent']    :     '';
            $return     =   [];
            if(empty($receiver) || empty($sender) || (empty($message) && empty($_FILES['attachments'])) || empty($parent)){
                $return['status']   =   false;
                $return['message']  =   'Something is went wrong please try again. Thanks!';
            }else{
                $wpdb->query("SET NAMES utf8mb4");
                $currentDateTime = date('Y-m-d H:i:s');

                // Handle file uploads
                $uploaded_files = [];
                if(!empty($_FILES['attachments'])){
                    $attachments = $_FILES['attachments'];
                    foreach($attachments['name'] as $key => $value){
                        if($attachments['name'][$key]){

                            $file = array(
                                'name'     => $attachments['name'][$key],
                                'type'     => $attachments['type'][$key],
                                'tmp_name' => $attachments['tmp_name'][$key],
                                'error'    => $attachments['error'][$key],
                                'size'     => $attachments['size'][$key]
                            );

                            $_FILES = array("attachment" => $file);
                            foreach($_FILES as $file => $array){
                                $newupload = self::handle_custom_attachment($file);
                                if(!is_wp_error($newupload)){
                                    $uploaded_files[] = basename($newupload);
                                }
                            }
                        }
                    }
                }

                $childTable     =   $wpdb->prefix.'chats_tbl';
                $parentTable    =   $wpdb->prefix.'chat_main_tbl';
                $attachments    =   (is_array(($uploaded_files)) && !empty($uploaded_files)) ? implode(',', $uploaded_files) : '';
                $chatdata       =   [
                    'parent_id'         =>      $parent,
                    'sender'            =>      $sender,
                    'receiver'          =>      $receiver,
                    'message'           =>      $message,
                    'attachments'       =>      $attachments,
                    'sent_datetime'     =>      $currentDateTime,
                ];
                $sended = $wpdb->insert($childTable, $chatdata);
                if($sended){
                    $updateData = [
                        'last_message_time' => $currentDateTime,
                    ];
                    $where = [
                        'id' => $parent,
                    ];
                    $updated = $wpdb->update($parentTable, $updateData, $where);
                    //Retrieve Messages
                    $total_message  =   $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE parent_id='$parent' ORDER BY sent_datetime ASC");
                    $html           =   '';
                    if(!empty($total_message)){
                        $html .='<ul class="submessages">';
                        foreach($total_message as $msg){
                            $attachments = $msg->attachments;
                            if($sender == $msg->sender){
                                $status = 'replies';
                            }else{
                                $status = 'sent';
                            }
                            $msguser = $msg->sender;
                            if($msguser == $sender){
                                $chatmsg_username 	= 	'';
                            }else{
                                $chatmsg_username 	= 	self::get_username_by_id($msguser).',';
                            }
                            $html .= '<li class="'.$status.'">';
                            if(!empty($attachments)){
                                $attachmentsData = explode(',', $attachments);
                                if(!empty($attachmentsData) && is_array(($attachmentsData))){
                                    foreach($attachmentsData as $adata){
                                        $url                =   site_url().'/wp-content/uploads/chat-attachments/'.$adata;
                                        $file_extension     =   pathinfo($url, PATHINFO_EXTENSION);
                                        $image_extensions   =   ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg', 'ico', 'jfif', 'pjpeg', 'pjp'];
                                        if(in_array(strtolower($file_extension), $image_extensions)){
                                            $html .= '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
                                        }else{
                                            $html .= '<span class="attc"><img class="attachments" src="'.site_url().'/wp-content/themes/tvadimarket/images/attached.png" alt="'.$adata.'"><br><a href="'.$url.'" download>'.$adata.'</a></span>';
                                        }
                                    }
                                }
                            }
                            if(!empty($msg->message)){
                                $html .= '<p>'.stripslashes($msg->message).'</p>';
                            }
                            $html .= '<span class="msginfo">'.$chatmsg_username.' '.$msg->sent_datetime.'</span>';
                            $html .= '</li>';    
                        }
                        $html .= '</ul>';
                    }

                    

                    $pusher                           =   new Pusher\Pusher("46a9b86b75fe0364ed37", "1a56e77c16e73ecab2d1", "1841899", array('cluster' => 'ap2'));
                    $pusher_data['parent_id']         =   $parent;
                    $pusher->trigger('aware-gift-30', 'chatbot-new-message', $pusher_data);

                    $return['status']   =   true;
                    $return['message']  =   'Message Sent Successfully!';
                    $return['html']     =   $html;
                }else{
                    $return['status']   =   false;
                    $return['message']  =   'Something is went wrong please try again. Thanks!';
                }
            }
            echo json_encode($return);
            exit();
        }

        public static function show_user_chats_cb(){
            global $wpdb;
            $parent_id  =   (isset($_POST['parent_id'])) ? $_POST['parent_id'] : '';
            $c_user_id  =   (isset($_POST['c_user_id'])) ? $_POST['c_user_id'] : '';
            $return     =   [];
            if(empty($parent_id) || empty($c_user_id)){
                $return['status']   =   false;
                $return['message']  =   'Something went wrong please try again. Thanks!';
            }else{
                $chatUser   =   $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chat_main_tbl` WHERE id='$parent_id' LIMIT 1");
                $html       =   '';
                $html2      =   ''; 
                if(!empty($chatUser)){

                    $updateData = [
                        'status' => '1',
                    ];

                    $whereee = [
                        'receiver'  => $c_user_id,
                        'parent_id' => $parent_id,
                    ];

                    $chattable  =   $wpdb->prefix.'chats_tbl';
                    $seen       =   $wpdb->update($chattable, $updateData, $whereee);

                    if(!empty($chatUser->contact_user_1) && $c_user_id != $chatUser->contact_user_1){
                        $chatuser_id = $chatUser->contact_user_1;
                    }else{
                        $chatuser_id = $chatUser->contact_user_2;
                    }
                    $chatuserprofile_pic  =   (!empty(get_user_meta($chatuser_id, 'profile_picture', true))) ? get_user_meta($chatuser_id, 'profile_picture', true) : self::$defaultProfilesrc;
                    $chat_firstname 	  =   get_user_meta($chatuser_id, 'first_name', true) ? get_user_meta($chatuser_id, 'first_name', true) : '';
                    $chat_lastname 		  =   get_user_meta($chatuser_id, 'last_name', true) ? get_user_meta($chatuser_id, 'last_name', true) : '';
                    $chatuser_info 		  =   get_userdata($chatuser_id);
                    $chat_userDisp	 	  =   (!empty($chat_firstname)  || !empty($chat_lastname)) ? $chat_firstname.' '.$chat_lastname : $chatuser_info->display_name;
                    
                    $html .=
                    '<div class="chat-content-section">
                        <div class="contact-profile">
                            <img src="'.$chatuserprofile_pic.'" alt="" class="img-contact-user">
                            <p>'.$chat_userDisp.'</p>
                        </div>';

                        $messages = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE parent_id='$parent_id' ORDER BY sent_datetime ASC");
                        if(!empty($messages)){
                            $html .= '
                            <div class="messages" id="message-content-section">
                                <ul class="submessages">';
                                    foreach($messages as $msg){
                                        $attachments = $msg->attachments;
                                        if($c_user_id == $msg->sender){
                                            $status = 'replies';
                                        }else{
                                            $status = 'sent';
                                        }
                                        $msguser 			= 	$msg->sender;
                                        if($msguser == $c_user_id){
                                            $chatmsg_username 	= 	'';
                                        }else{
                                            $chatmsg_username 	= 	self::get_username_by_id($msguser).',';
                                        }
                                        $html .= '<li class="'.$status.'">';
                                        if(!empty($attachments)){
                                            $attachmentsData = explode(',', $attachments);
                                            if(!empty($attachmentsData) && is_array(($attachmentsData))){
                                                foreach($attachmentsData as $adata){
                                                    $url                =   site_url().'/wp-content/uploads/chat-attachments/'.$adata;
                                                    $file_extension     =   pathinfo($url, PATHINFO_EXTENSION);
                                                    $image_extensions   =   ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg', 'ico', 'jfif', 'pjpeg', 'pjp'];
                                                    if(in_array(strtolower($file_extension), $image_extensions)){
                                                        $html .= '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
                                                    }else{
                                                        $html .= '<span class="attc"><img class="attachments" src="'.site_url().'/wp-content/themes/tvadimarket/images/attached.png" alt="'.$adata.'"><br><a href="'.$url.'" download>'.$adata.'</a></span>';
                                                    }
                                                }
                                            }
                                        }
                                        if(!empty($msg->message)){
                                            $html .= '<p>'.stripslashes($msg->message).'</p>';
                                        }
                                        $html .= '<span class="msginfo">'.$chatmsg_username.' '.$msg->sent_datetime.'</span>';
                                        $html .= '</li>';
                                    }
                                $html .='
                                </ul>
                            </div>';
                        }else{
                            $html .= '<div class="messages" id="message-content-section"><p class="startconversation">Start Messaging!</p></div>';
                        }
                        $html .= '
                        <div class="message-input">
                            <div id="image-preview"></div>
                            <div class="wrap">
                                <textarea id="message-text" placeholder="Write your message..." name="message"></textarea>
                                <input type="hidden" name="sender_id" id="sender-id" value="'.$c_user_id.'">
                                <input type="hidden" name="receiver_id" id="receiver-id" value="'.$chatuser_id.'">
                                <input type="hidden" name="parent_id" id="parent-id" value="'.$parent_id.'">
                                <input type="file" id="file-input" name="attachments[]" multiple style="display:none;">
                                <button id="attach-file-btn"><i class="fa fa-paperclip" aria-hidden="true"></i></button>
                                <button class="submit" id="send-message-btn"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>';

                    //connected users
                    $connected_users =  $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."chat_main_tbl` WHERE (contact_user_1='$c_user_id') OR (contact_user_2='$c_user_id') ORDER BY last_message_time DESC");
                    if(!empty($connected_users)){
                        foreach($connected_users as $u){
                            if(!empty($u->contact_user_1) && $u->contact_user_1 != $c_user_id){
                                $uid = $u->contact_user_1;
                            }else{
                                $uid = $u->contact_user_2;
                            }
                            $uprofile_picture    =   (!empty(get_user_meta($uid, 'profile_picture', true))) ? get_user_meta($uid, 'profile_picture', true) : self::$defaultProfilesrc;
                            $firstname 			 =   get_user_meta($uid, 'first_name', true) ? get_user_meta($uid, 'first_name', true) : '';
                            $lastname 			 =   get_user_meta($uid, 'last_name', true)  ? get_user_meta($uid, 'last_name', true) : '';
                            $uprofile_info       =   get_user_meta($uid, 'user_profile_info', true);
                            $listuserinfo	     =   get_userdata($uid);
                            $unseenMessages		 =	 $wpdb->get_row("SELECT COUNT(id) as total FROM `".$wpdb->prefix."chats_tbl` WHERE status='0' AND sender='$uid' AND receiver='$c_user_id'");
                            $lasmessage		 	 =	 $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE (sender='$uid' AND receiver='$c_user_id') OR sender='$c_user_id' AND receiver='$uid' ORDER BY id DESC LIMIT 1");
                            $totalunseencounter  =	 $unseenMessages->total;
                            $userDisplayname	 =   (!empty($firstname) || !empty($lastname)) ? $firstname.' '.$lastname : $listuserinfo->display_name;

                            $html2 .= '<li class="contact active" data-userparent="'.$u->id.'" onclick="showUserChat('.$u->id.', '.$c_user_id.', this)">
                                <div class="wrap" id="info-list-u">
                                    <div class="profile_user_outer">
                                        <div class="user_profile">';
                                            if(is_user_online($uid)){
                                                $ustatus = 'online';
                                            }else{
                                                $ustatus = 'offline';
                                            }
                                            $html2 .= '<span class="contact-status '.$ustatus.'"></span>
                                            <img src="'.$uprofile_picture.'" alt=""/>
                                        </div>
                                        <div class="user_details">
                                            <div class="user_name">
                                                <p class="name line-1-text">'.$userDisplayname.'</p>';
                                                $today 		= date('Y-m-d');
                                                $date_part 	= date('Y-m-d', strtotime($lasmessage->sent_datetime));
                                                if($date_part === $today){
                                                    $html2 .= '<span class="l-datetime">'.date('H:i:s', strtotime($lasmessage->sent_datetime)).'</span>';
                                                }else{
                                                    $html2 .= '<span class="l-datetime">'.date('Y-m-d', strtotime($lasmessage->sent_datetime)).'</span>';
                                                }
                                            $html2 .= '</div>
                                            <div class="last-massage">
                                                <span class="l-message line-1-text">'.stripslashes($lasmessage->message).'</span>';
                                                if(!empty($totalunseencounter)){ 
                                                    $html2 .= '<span class="unseen-new">'.number_format($totalunseencounter).'</span>'; 
                                                }
                                            $html2 .= '</div>
                                        </div>
                                    </div>
                                </div>
                            </li>';
                        }
                    }

                    $return['status']           =   true;
                    $return['message']          =   'Processed Successfully!';
                    $return['html']             =   $html;
                    $return['connected_users']  =   $html2;
                }else{
                    $return['status']   =   false;
                    $return['message']  =   'Something went wrong please try again. Thanks!'; 
                }
            }
            echo json_encode($return);
            exit();
        }

        public static function handle_custom_attachment($file_handler){
            $upload_dir     =   wp_upload_dir();
            $custom_dir     =   $upload_dir['basedir'] . '/chat-attachments';
        
            // Create custom directory if it doesn't exist
            if(!file_exists($custom_dir)){
                wp_mkdir_p($custom_dir);
            }
        
            $filetype   =   wp_check_filetype(basename($_FILES[$file_handler]['name']), null);
            $filename   =   wp_unique_filename($custom_dir, $_FILES[$file_handler]['name']);
            $file_path  =   $custom_dir . '/' . $filename;
            // Move the uploaded file to the custom directory
            if(move_uploaded_file($_FILES[$file_handler]['tmp_name'], $file_path)){
                $file_url = $upload_dir['baseurl'] . '/chat-attachments/' . $filename;
                return $file_url;
            }else{
                return new WP_Error('upload_error', 'Failed to move uploaded file.');
            }
        }

        public static function get_username_by_id($id){
            $first 	  		=   get_user_meta($id, 'first_name', true) ? get_user_meta($id, 'first_name', true) : '';
            $last 		  	=   get_user_meta($id, 'last_name', true) ? get_user_meta($id, 'last_name', true) : '';
            $uinfo 		  	=   get_userdata($id);
            $userdisp		=   (!empty($first)  || !empty($last)) ? $first.' '.$last : $uinfo->display_name;
            return $userdisp;
        }

        /**
         * UPDATE CHAT SCREEN ON MESSAGE SEND
         */
        public static function update_chat_screen_cb(){
            global $wpdb;
            $parent_id  =   (isset($_POST['parent_id'])) ? $_POST['parent_id'] : '';
            $c_user_id  =   get_current_user_id();
            $return     =   [];
            if(empty($parent_id) || empty($c_user_id)){
                $return['status']   =   false;
                $return['message']  =   'Something went wrong please try again. Thanks!';
            }else{
                $chatUser       =  $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chat_main_tbl` WHERE id='$parent_id' LIMIT 1");
                $html           =  '';
                $html2          =  '';
                if(!empty($chatUser)){
                    if(!empty($chatUser->contact_user_1) && $c_user_id != $chatUser->contact_user_1){
                        $chatuser_id = $chatUser->contact_user_1;
                    }else{
                        $chatuser_id = $chatUser->contact_user_2;
                    }
                    $chatuserprofile_pic  =   (!empty(get_user_meta($chatuser_id, 'profile_picture', true))) ? get_user_meta($chatuser_id, 'profile_picture', true) : self::$defaultProfilesrc;
                    $chat_firstname 	  =   get_user_meta($chatuser_id, 'first_name', true) ? get_user_meta($chatuser_id, 'first_name', true) : '';
                    $chat_lastname 		  =   get_user_meta($chatuser_id, 'last_name', true) ? get_user_meta($chatuser_id, 'last_name', true) : '';
                    $chatuser_info 		  =   get_userdata($chatuser_id);
                    $chat_userDisp	 	  =   (!empty($chat_firstname)  || !empty($chat_lastname)) ? $chat_firstname.' '.$chat_lastname : $chatuser_info->display_name;
                    
                    $html .=
                    '<div class="chat-content-section">
                        <div class="contact-profile">
                            <img src="'.$chatuserprofile_pic.'" alt="" class="img-contact-user">
                            <p>'.$chat_userDisp.'</p>
                        </div>';

                        $messages = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE parent_id='$parent_id' ORDER BY sent_datetime ASC");
                        if(!empty($messages)){
                            $html .= '
                            <div class="messages" id="message-content-section">
                                <ul class="submessages">';
                                    foreach($messages as $msg){
                                        $attachments = $msg->attachments;
                                        if($c_user_id == $msg->sender){
                                            $status = 'replies';
                                        }else{
                                            $status = 'sent';
                                        }
                                        $msguser 			= 	$msg->sender;
                                        if($msguser == $c_user_id){
                                            $chatmsg_username 	= 	'';
                                        }else{
                                            $chatmsg_username 	= 	self::get_username_by_id($msguser).',';
                                        }
                                        $html .= '<li class="'.$status.'">';
                                        if(!empty($attachments)){
                                            $attachmentsData = explode(',', $attachments);
                                            if(!empty($attachmentsData) && is_array(($attachmentsData))){
                                                foreach($attachmentsData as $adata){
                                                    $url                =   site_url().'/wp-content/uploads/chat-attachments/'.$adata;
                                                    $file_extension     =   pathinfo($url, PATHINFO_EXTENSION);
                                                    $image_extensions   =   ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg', 'ico', 'jfif', 'pjpeg', 'pjp'];
                                                    if(in_array(strtolower($file_extension), $image_extensions)){
                                                        $html .= '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
                                                    }else{
                                                        $html .= '<span class="attc"><img class="attachments" src="'.site_url().'/wp-content/themes/tvadimarket/images/attached.png" alt="'.$adata.'"><br><a href="'.$url.'" download>'.$adata.'</a></span>';
                                                    }
                                                }
                                            }
                                        }
                                        if(!empty($msg->message)){
                                            $html .= '<p>'.stripslashes($msg->message).'</p>';
                                        }
                                        $html .= '<span class="msginfo">'.$chatmsg_username.' '.$msg->sent_datetime.'</span>';
                                        $html .= '</li>';
                                    }
                                $html .='
                                </ul>
                            </div>';
                        }else{
                            $html .= '<div class="messages" id="message-content-section"><p class="startconversation">Start Messaging!</p></div>';
                        }
                        $html .= '
                        <div class="message-input">
                            <div id="image-preview"></div>
                            <div class="wrap">
                                <textarea id="message-text" placeholder="Write your message..." name="message"></textarea>
                                <input type="hidden" name="sender_id" id="sender-id" value="'.$c_user_id.'">
                                <input type="hidden" name="receiver_id" id="receiver-id" value="'.$chatuser_id.'">
                                <input type="hidden" name="parent_id" id="parent-id" value="'.$parent_id.'">
                                <input type="file" id="file-input" name="attachments[]" multiple style="display:none;">
                                <button id="attach-file-btn"><i class="fa fa-paperclip" aria-hidden="true"></i></button>
                                <button class="submit" id="send-message-btn"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>';
                    
                    //connected users
                    $connected_users =  $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."chat_main_tbl` WHERE (contact_user_1='$c_user_id') OR (contact_user_2='$c_user_id') ORDER BY last_message_time DESC");
                    if(!empty($connected_users)){
                        foreach($connected_users as $u){
                            if(!empty($u->contact_user_1) && $u->contact_user_1 != $c_user_id){
                                $uid = $u->contact_user_1;
                            }else{
                                $uid = $u->contact_user_2;
                            }
                            $uprofile_picture    =   (!empty(get_user_meta($uid, 'profile_picture', true))) ? get_user_meta($uid, 'profile_picture', true) : self::$defaultProfilesrc;
                            $firstname 			 =   get_user_meta($uid, 'first_name', true) ? get_user_meta($uid, 'first_name', true) : '';
                            $lastname 			 =   get_user_meta($uid, 'last_name', true)  ? get_user_meta($uid, 'last_name', true) : '';
                            $uprofile_info       =   get_user_meta($uid, 'user_profile_info', true);
                            $listuserinfo	     =   get_userdata($uid);
                            $unseenMessages		 =	 $wpdb->get_row("SELECT COUNT(id) as total FROM `".$wpdb->prefix."chats_tbl` WHERE status='0' AND sender='$uid' AND receiver='$c_user_id'");
                            $lasmessage		 	 =	 $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE (sender='$uid' AND receiver='$c_user_id') OR sender='$c_user_id' AND receiver='$uid' ORDER BY id DESC LIMIT 1");
                            $totalunseencounter  =	 $unseenMessages->total;
                            $userDisplayname	 =   (!empty($firstname) || !empty($lastname)) ? $firstname.' '.$lastname : $listuserinfo->display_name;

                            $html2 .= '<li class="contact active" data-userparent="'.$u->id.'" onclick="showUserChat('.$u->id.', '.$c_user_id.', this)">
                                <div class="wrap" id="info-list-u">
                                    <div class="profile_user_outer">
                                        <div class="user_profile">';
                                            if(is_user_online($uid)){
                                                $ustatus = 'online';
                                            }else{
                                                $ustatus = 'offline';
                                            }
                                            $html2 .= '<span class="contact-status '.$ustatus.'"></span>
                                            <img src="'.$uprofile_picture.'" alt=""/>
                                        </div>
                                        <div class="user_details">
                                            <div class="user_name">
                                                <p class="name line-1-text">'.$userDisplayname.'</p>';
                                                $today 		= date('Y-m-d');
                                                $date_part 	= date('Y-m-d', strtotime($lasmessage->sent_datetime));
                                                if($date_part === $today){
                                                    $html2 .= '<span class="l-datetime">'.date('H:i:s', strtotime($lasmessage->sent_datetime)).'</span>';
                                                }else{
                                                    $html2 .= '<span class="l-datetime">'.date('Y-m-d', strtotime($lasmessage->sent_datetime)).'</span>';
                                                }
                                            $html2 .= '</div>
                                            <div class="last-massage">
                                                <span class="l-message line-1-text">'.stripslashes($lasmessage->message).'</span>';
                                                if(!empty($totalunseencounter)){ 
                                                    $html2 .= '<span class="unseen-new">'.number_format($totalunseencounter).'</span>'; 
                                                }
                                            $html2 .= '</div>
                                        </div>
                                    </div>
                                </div>
                            </li>';
                        }
                    }
                    $return['status']           =   true;
                    $return['message']          =   'Processed Successfully!';
                    $return['html']             =   $html;
                    $return['connected_users']  =   $html2;
                }else{
                    $return['status']   =   false;
                    $return['message']  =   'Something went wrong please try again. Thanks!'; 
                }
            }
            echo json_encode($return);
            exit();
        }
        
        public static function tvadi_chat_model_cb(){
            ob_start();
            ?>
            <meta charset="UTF-8">
            <?php
            global $wpdb;
            $user 				= 	wp_get_current_user();
            $c_user_id 			= 	$user->ID;
            $profile_picture    =   get_user_meta($c_user_id, 'profile_picture', true);
            $profile_info       =   get_user_meta($c_user_id, 'user_profile_info', true);
            $parent_chat_id     =  (isset($_GET['chat_id']) && !empty($_GET['chat_id'])) ? intval($_GET['chat_id']) : '';
            ?>
            <div class="chat-system">
                <section class="chat-section">
                    <div class="container-fluid-1">
                        <div id="chat-frame">
                            <div id="sidepanel">
                                <div id="profile">
                                    <div class="wrap">
                                        <?php 
                                        if($profile_picture){
                                            echo '<img src="'.esc_url($profile_picture).'" id="profile-img" class="online" alt="">';
                                        }else{
                                            echo '<img src="'.DEFAULT_PROFILE_PIC.'" id="profile-img" class="online" alt="">';
                                        }
                                        $cfirstname 		=  get_user_meta($c_user_id, 'first_name', true)  ? get_user_meta($c_user_id, 'first_name', true) : '';
                                        $clastname 			=  get_user_meta($c_user_id, 'last_name', true)   ? get_user_meta($c_user_id, 'last_name', true) : '';
                                        $cUserDisplayname 	=  (!empty($cfirstname) || !empty($clastname))   ? $cfirstname.' '.$clastname : $user->display_name;
                                        $info      			=   get_user_meta($c_user_id, 'user_profile_info', true);
                                        ?>
                                        <p><?= ucfirst($cUserDisplayname) ?></p>
                                    </div>
                                </div>
                                <div id="contacts">
                                    <ul id="connected-users-list">
                                        <?php
                                        $connected_users =  $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."chat_main_tbl` WHERE (contact_user_1='$c_user_id') OR (contact_user_2='$c_user_id') ORDER BY last_message_time DESC");
                                        if(!empty($connected_users)){
                                            foreach($connected_users as $u){
                                                if(!empty($u->contact_user_1) && $u->contact_user_1 != $c_user_id){
                                                    $uid = $u->contact_user_1;
                                                }else{
                                                    $uid = $u->contact_user_2;
                                                }
                                                $uprofile_picture    =   (!empty(get_user_meta($uid, 'profile_picture', true))) ? get_user_meta($uid, 'profile_picture', true) : DEFAULT_PROFILE_PIC;
                                                $firstname 			 =   get_user_meta($uid, 'first_name', true) ? get_user_meta($uid, 'first_name', true) : '';
                                                $lastname 			 =   get_user_meta($uid, 'last_name', true)  ? get_user_meta($uid, 'last_name', true) : '';
                                                $uprofile_info       =   get_user_meta($uid, 'user_profile_info', true);
                                                $listuserinfo	     =   get_userdata($uid);
                                                $unseenMessages		 =	 $wpdb->get_row("SELECT COUNT(id) as total FROM `".$wpdb->prefix."chats_tbl` WHERE status='0' AND sender='$uid' AND receiver='$c_user_id'");
                                                $lasmessage		 	 =	 $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE (sender='$uid' AND receiver='$c_user_id') OR sender='$c_user_id' AND receiver='$uid' ORDER BY id DESC LIMIT 1");
                                                $totalunseencounter  =	 $unseenMessages->total;
                                                $userDisplayname	 =   (!empty($firstname) || !empty($lastname)) ? $firstname.' '.$lastname : $listuserinfo->display_name;
                                                ?>
                                                <li class="contact active <?php if($parent_chat_id == $u->id) echo 'active-list'; ?>" data-userparent="<?= $u->id ?>" onclick="showUserChat(<?= $u->id ?>, <?= $c_user_id ?>, this)">
                                                    <div class="wrap" id="info-list-u">
                                                        <div class="profile_user_outer">
                                                            <div class="user_profile">
                                                                <?php 
                                                                if(is_user_online($uid)){
                                                                    $ustatus = 'online';
                                                                }else{
                                                                    $ustatus = 'offline';
                                                                }?>
                                                                <span class="contact-status <?= $ustatus ?>"></span>
                                                                <img src="<?= $uprofile_picture ?>" alt=""/>
                                                            </div>
                                                            <div class="user_details">
                                                                <div class="user_name">
                                                                    <p class="name line-1-text"><?= $userDisplayname ?></p>
                                                                    <?php 
                                                                    $today 		= date('Y-m-d');
                                                                    $date_part 	= date('Y-m-d', strtotime($lasmessage->sent_datetime));
                                                                    if($date_part === $today){
                                                                        ?>
                                                                        <span class="l-datetime"><?= date('H:i:s', strtotime($lasmessage->sent_datetime)) ?></span>
                                                                        <?php	
                                                                    }else{
                                                                        ?>
                                                                        <span class="l-datetime"><?= date('Y-m-d', strtotime($lasmessage->sent_datetime)) ?></span>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <div class="last-massage">
                                                                    <span class="l-message line-1-text"><?= stripslashes($lasmessage->message) ?></span>
                                                                    <?php 
                                                                    if(!empty($totalunseencounter)){ 
                                                                        ?>
                                                                        <span class="unseen-new"><?= number_format($totalunseencounter) ?></span> 
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="content" id="tvadi-user-messages-section" data-parent="<?= $parent_chat_id ?>">
                                <?php
                                if(!empty($parent_chat_id)){
                                    $chatUser =  $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chat_main_tbl` WHERE id='$parent_chat_id' LIMIT 1");
                                    if(!empty($chatUser)){

                                        //seen message process if parent is trigger trough url 
                                        $updateData = [
                                            'status' => '1',
                                        ];
                    
                                        $whereee = [
                                            'receiver'  => $c_user_id,
                                            'parent_id' => $parent_chat_id,
                                        ];
                    
                                        $chattable  =   $wpdb->prefix.'chats_tbl';
                                        $seen       =   $wpdb->update($chattable, $updateData, $whereee); //seen...

                                        if(!empty($chatUser->contact_user_1) && $c_user_id != $chatUser->contact_user_1){
                                            $chatuser_id = $chatUser->contact_user_1;
                                        }else{
                                            $chatuser_id = $chatUser->contact_user_2;
                                        }
                                        $chatuserprofile_pic  =   (!empty(get_user_meta($chatuser_id, 'profile_picture', true))) ? get_user_meta($chatuser_id, 'profile_picture', true) : DEFAULT_PROFILE_PIC;
                                        $chat_firstname 	  =   get_user_meta($chatuser_id, 'first_name', true) ? get_user_meta($chatuser_id, 'first_name', true) : '';
                                        $chat_lastname 		  =   get_user_meta($chatuser_id, 'last_name', true) ? get_user_meta($chatuser_id, 'last_name', true) : '';
                                        $chatuser_info 		  =   get_userdata($chatuser_id);
                                        $chat_userDisp	 	  =   (!empty($chat_firstname)  || !empty($chat_lastname)) ? $chat_firstname.' '.$chat_lastname : $chatuser_info->display_name;
                                        ?>
                                        <div class="chat-content-section">
                                            <div class="contact-profile">
                                                <img src="<?= $chatuserprofile_pic ?>" alt="" class="img-contact-user">
                                                <p><?= $chat_userDisp ?></p>
                                            </div>
                                            <?php 
                                            $messages = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE parent_id='$parent_chat_id' ORDER BY sent_datetime ASC");
                                            if(!empty($messages)){
                                                ?>
                                                <div class="messages" id="message-content-section">
                                                    <ul class="submessages">
                                                        <?php
                                                        foreach($messages as $msg){
                                                            $attachments = $msg->attachments;

                                                            if($c_user_id == $msg->sender){
                                                                $status 			= 	'replies';
                                                            }else{
                                                                $status 			= 	'sent';
                                                            }
                                                            $msguser 			= 	$msg->sender;
                                                            if($msguser == $c_user_id){
                                                                $chatmsg_username 	= 	'';
                                                            }else{
                                                                $chatmsg_username 	=  self::get_username_by_id($msguser).',';
                                                            }
                                                            echo '<li class="'.$status.'">';
                                                            if(!empty($attachments)){
                                                                $attachmentsData = explode(',', $attachments);
                                                                if(!empty($attachmentsData) && is_array(($attachmentsData))){
                                                                    foreach($attachmentsData as $adata){
                                                                        $url 				= 	site_url().'/wp-content/uploads/chat-attachments/'.$adata;
                                                                        $file_extension     =   pathinfo($url, PATHINFO_EXTENSION);
                                                                        $image_extensions   =   ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg', 'ico', 'jfif', 'pjpeg', 'pjp'];
                                                                        if(in_array(strtolower($file_extension), $image_extensions)){
                                                                            echo '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
                                                                        }else{
                                                                            echo '<span class="attc"><img class="attachments" src="'.site_url().'/wp-content/themes/tvadimarket/images/attached.png" alt="'.$adata.'"><br><a href="'.$url.'" download>'.$adata.'</a></span>';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            if(!empty($msg->message)){
                                                                echo '<p class="msg-text">'.stripslashes($msg->message).'</p>';
                                                            }
                                                            echo '<span class="msginfo">'.$chatmsg_username.' '.$msg->sent_datetime.'</span>';
                                                            echo '</li>';
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                                <?php
                                            }else{
                                                ?>
                                                <div class="messages" id="message-content-section">
                                                    <p class="startconversation">Start Messaging!</p>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                            <div id="nnnnn"></div>
                                            <div class="message-input">
                                                <div id="image-preview"></div>
                                                <div class="wrap">
                                                    <textarea id="message-text" placeholder="Write your message..." name="message"></textarea>
                                                    <input type="hidden" name="sender_id" id="sender-id" value="<?= $c_user_id ?>">
                                                    <input type="hidden" name="receiver_id" id="receiver-id" value="<?= $chatuser_id ?>">
                                                    <input type="hidden" name="parent_id" id="parent-id" value="<?= $parent_chat_id ?>">
                                                    <input type="file" id="file-input" name="attachments[]" multiple style="display:none;">
                                                    <button id="attach-file-btn"><i class="fa fa-paperclip" aria-hidden="true"></i></button>
                                                    <button class="submit" id="send-message-btn"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }else{
                                    ?>
                                    <div class="chat-content-section">
                                        <div class="demo-section">
                                            <div class="">
                                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M304 96H112c-8.832 0-16 7.168-16 16s7.168 16 16 16h192c8.832 0 16-7.168 16-16s-7.168-16-16-16zM240 160H112c-8.832 0-16 7.168-16 16s7.168 16 16 16h128c8.832 0 16-7.168 16-16s-7.168-16-16-16z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M352 0H64C28.704 0 0 28.704 0 64v320c0 6.208 3.584 11.872 9.216 14.496A16.232 16.232 0 0 0 16 400c3.68 0 7.328-1.28 10.24-3.712L117.792 320H352c35.296 0 64-28.704 64-64V64c0-35.296-28.704-64-64-64zm32 256c0 17.632-14.336 32-32 32H112c-3.744 0-7.36 1.312-10.24 3.712L32 349.856V64c0-17.632 14.336-32 32-32h288c17.664 0 32 14.368 32 32v192z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M448 128c-8.832 0-16 7.168-16 16s7.168 16 16 16c17.664 0 32 14.368 32 32v270.688l-54.016-43.2A16.12 16.12 0 0 0 416 416H192c-17.664 0-32-14.368-32-32v-16c0-8.832-7.168-16-16-16s-16 7.168-16 16v16c0 35.296 28.704 64 64 64h218.368l75.616 60.512A16.158 16.158 0 0 0 496 512c2.336 0 4.704-.512 6.944-1.568A16.05 16.05 0 0 0 512 496V192c0-35.296-28.704-64-64-64z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
                                                <p class="startchat">Start a chat by clicking on sidebar users.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <script>
                jQuery(document).ready(function(){
                    trigger_emojis();
                    function trigger_emojis(){
                        jQuery("#message-text").emojioneArea({
                            pickerPosition: "right",
                            tonesStyle: "bullet",
                            events: {
                                keydown: function(editor, event){
                                    if(event.key === 'Enter' && !event.shiftKey){
                                        event.preventDefault(); 
                                        jQuery('#send-message-btn').click();
                                    }
                                }
                            }
                        });
                    }
                });
                //Pusher
                jQuery(document).ready(function(){
                    var pusher = new Pusher('46a9b86b75fe0364ed37', {
                        cluster: 'ap2'
                    });

                    var channel = pusher.subscribe('aware-gift-30');
                    channel.bind('chatbot-new-message', function(data){
                        var chat_parent_id = data.parent_id;
                        jQuery.ajax({
                            type 		:  'POST',
                            url 		:  '<?= admin_url('admin-ajax.php'); ?>',
                            data 		:  {
                                action  			:   'update_chat_screen',
                                parent_id 			: 	data.parent_id,
                            },
                            success: function(response){
                                const resss = JSON.parse(response);
                                if(resss.status){
                                    if(resss.html != ''){
                                        jQuery('.content[data-parent="'+chat_parent_id+'"]').html('');
                                        jQuery('.content[data-parent="'+chat_parent_id+'"]').html(resss.html);
                                        if(jQuery('.content[data-parent="'+chat_parent_id+'"]').length > 0){
                                            jQuery('.active-list').click();
                                        }
                                        emojiTrigger();
                                        scrollToBottom();
                                    }
                                    if(resss.connected_users != ''){
                                        var activeusertab = jQuery('.active-list').attr('data-userparent');
                                        // console.log(activeusertab);
                                        jQuery('#connected-users-list').html('');
                                        jQuery('#connected-users-list').html(resss.connected_users);
                                        jQuery('li[data-userparent="'+activeusertab+'"]').addClass('active-list');
                                    }
                                }else{
                                    console.log(resss.message);
                                    location.reload();
                                }
                            }	
                        });
                    });
                });
            </script>
            <?php
            return ob_get_clean();
        }
    }
    TvadiChatProcessHandler::init();
}