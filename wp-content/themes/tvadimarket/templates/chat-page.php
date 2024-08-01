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
									$unseenMessages		 =	 $wpdb->get_row("SELECT COUNT(id) as total FROM `".$wpdb->prefix."chats_tbl` WHERE status='0' AND sender='$uid' AND receiver='$c_user_id'");
									$lasmessage		 	 =	 $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."chats_tbl` WHERE (sender='$uid' AND receiver='$c_user_id') OR sender='$c_user_id' AND receiver='$uid' ORDER BY id DESC LIMIT 1");
									$totalunseencounter  =	 $unseenMessages->total;
									$userDisplayname	 =   (!empty($firstname) || !empty($lastname)) ? $firstname.' '.$lastname : $listuserinfo->display_name;
									?>
									<li class="contact active <?php if($parent_chat_id == $u->id) echo 'active-list'; ?>" data-userparent="<?= $parent_chat_id ?>" onclick="showUserChat(<?= $u->id ?>, <?= $c_user_id ?>, this)">
										<div class="wrap" id="info-list-u">
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
												<?php 
												if(!empty($totalunseencounter)){
													?>
													<span class="unseen-new"><?= number_format($totalunseencounter) ?></span>
													<?php
												}
												?>
											</div>
										</div>
										<div class="lastmessage">
											<span class="l-message"><?= $lasmessage->message ?></span>
											<span class="l-datetime"><?= $lasmessage->sent_datetime ?></span>
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
<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
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
					}else{
						console.log(resss.message);
						location.reload();
					}
				}	
			});
  		});
	});
</script>