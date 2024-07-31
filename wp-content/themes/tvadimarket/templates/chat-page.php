<?php
/*
* Template Name: Chat Page
*/
if(!defined('ABSPATH')){
    exit(); 
}
if(!is_user_logged_in()){
    header("Location: /login/");
    exit();
}
get_header();
?>
<meta charset="UTF-8">
<link rel="stylesheet" href="<?= get_template_directory_uri() ?>/css/chatstyle.css?<?= time() ?>">
<link rel="stylesheet" href="<?= get_template_directory_uri() ?>/emojionearea-master/dist/emojionearea.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<?php 
echo 'vijay';
global $wpdb;
$user 				= 	wp_get_current_user();
$c_user_id 			= 	$user->ID;
$profile_picture    =   get_user_meta($c_user_id, 'profile_picture', true);
$profile_info       =   get_user_meta($c_user_id, 'user_profile_info', true);
$parent_chat_id     =  (isset($_GET['chat_id']) && !empty($_GET['chat_id'])) ? intval($_GET['chat_id']) : '';

function get_username_by_id($id){
	$first 	  		=   get_user_meta($id, 'first_name', true) ? get_user_meta($id, 'first_name', true) : '';
	$last 		  	=   get_user_meta($id, 'last_name', true) ? get_user_meta($id, 'last_name', true) : '';
	$uinfo 		  	=   get_userdata($id);
	$userdisp		=   (!empty($first)  || !empty($last)) ? $first.' '.$last : $uinfo->display_name;
	return $userdisp;
}
?>
<div class="chat-system">
    <section class="chat-section">
        <div class="container-fluid">
            <div id="chat-frame">
                <div id="sidepanel">
                    <div id="profile">
                        <div class="wrap">
							<?php 
							if($profile_picture){
								echo '<img src="'.esc_url($profile_picture).'" id="profile-img" class="online" alt="">';
							}else{
								echo '<img src="'.get_avatar_url($c_user_id).'" id="profile-img" class="online" alt="">';
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
                        <ul>
							<?php
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
									$userDisplayname	 =   (!empty($firstname) || !empty($lastname)) ? $firstname.' '.$lastname : $listuserinfo->display_name;
									?>
									<li class="contact active <?php if($parent_chat_id == $u->id) echo 'active-list'; ?>" onclick="showUserChat(<?= $u->id ?>, <?= $c_user_id ?>, this)">
										<div class="wrap">
											<?php 
											if(is_user_online($uid)){
												$ustatus = 'online';
											}else{
												$ustatus = 'offline';
											}
											?>
											<span class="contact-status <?= $ustatus ?>"></span>
											<img src="<?= $uprofile_picture ?>" alt=""/>
											<div class="meta">
												<p class="name"><?= $userDisplayname ?></p>
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
                <div class="content" id="tvadi-user-messages-section">
					<?php
					if(!empty($parent_chat_id)){
						$chatUser =  $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chat_main_tbl` WHERE id='$parent_chat_id' LIMIT 1");
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
													$chatmsg_username 	=  get_username_by_id($msguser).',';
												}
												echo '<li class="'.$status.'">';
												if(!empty($attachments)){
													$attachmentsData = explode(',', $attachments);
													if(!empty($attachmentsData) && is_array(($attachmentsData))){
														foreach($attachmentsData as $adata){
															$url = site_url().'/wp-content/uploads/chat-attachments/'.$adata;
															echo '<span class="attc"><img class="attachments" src="'.$url.'"></span>';
														}
														
													}
												}
												if(!empty($msg->message)){
													echo '<p class="msg-text">'.$msg->message.'</p>';
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
										<input type="file" id="file-input" name="attachments[]" accept="image/*" multiple style="display:none;">
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
								<p class="startchat">Start a chat by clicking on sidebar users.</p>
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
<?php
get_footer();
?>
<script src="<?= get_template_directory_uri() ?>/emojionearea-master/dist/emojionearea.js"></script>
<script>
	jQuery(document).ready(function(){
		trigger_emojis();
		function trigger_emojis(){
			jQuery("#message-text").emojioneArea({
				pickerPosition: "right",
				tonesStyle: "bullet",
				events: {
					keyup: function(editor, event){
						// console.log(editor.html());
						// console.log(this.getText());
					}
				}
			});
		}
     	// jQuery(document).on('click', '.emojionearea-editor', function(){
		// 	jQuery('.emojionearea-button-close').click();
		// });
	});
</script>