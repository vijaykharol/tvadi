<?php 
if(!defined('ABSPATH')){
    exit();
}
/**
 * CLASS TvadiChatProcessHandler
 */
if(!class_exists('TvadiChatProcessHandler', false)){
    class TvadiChatProcessHandler{
        public static function init(){
            require_once get_template_directory().'/inc/pusher/vendor/autoload.php';
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
                                        $url = site_url().'/wp-content/uploads/chat-attachments/'.$adata;
                                        $html .= '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
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
                    $chatuserprofile_pic  =   (!empty(get_user_meta($chatuser_id, 'profile_picture', true))) ? get_user_meta($chatuser_id, 'profile_picture', true) : get_avatar_url($chatuser_id);
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
                                                    $url = site_url().'/wp-content/uploads/chat-attachments/'.$adata;
                                                    $html .= '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
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
                                <input type="file" id="file-input" name="attachments[]" accept="image/*" multiple style="display:none;">
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
                            $uprofile_picture    =   (!empty(get_user_meta($uid, 'profile_picture', true))) ? get_user_meta($uid, 'profile_picture', true) : get_avatar_url($uid);
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
                    $chatuserprofile_pic  =   (!empty(get_user_meta($chatuser_id, 'profile_picture', true))) ? get_user_meta($chatuser_id, 'profile_picture', true) : get_avatar_url($chatuser_id);
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
                                                    $url = site_url().'/wp-content/uploads/chat-attachments/'.$adata;
                                                    $html .= '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
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
                                <input type="file" id="file-input" name="attachments[]" accept="image/*" multiple style="display:none;">
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
                            $uprofile_picture    =   (!empty(get_user_meta($uid, 'profile_picture', true))) ? get_user_meta($uid, 'profile_picture', true) : get_avatar_url($uid);
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
    }
    TvadiChatProcessHandler::init();
}