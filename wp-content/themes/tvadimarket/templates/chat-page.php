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
<link rel="stylesheet" href="<?= get_template_directory_uri() ?>/emojionearea-master/dist/emojionearea.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<link rel="stylesheet" href="<?= get_template_directory_uri() ?>/css/chatstyle.css?<?= time() ?>">
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
														<?php if(!empty($totalunseencounter)){ ?>
															<span class="unseen-new"><?= number_format($totalunseencounter) ?></span> <?php
														}?>
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
										<input type="file" id="file-input" name="attachments[]" accept="image/*" multiple style="display:none;">
										<!-- <button id="attach-file-btn">
											<svg xmlns="http://www.w3.org/2000/svg" class="svg-icon svg-icon--send-img" viewBox="0 0 45.7 45.7">
							                    <path d="M6.6,45.7A6.7,6.7,0,0,1,0,39.1V6.6A6.7,6.7,0,0,1,6.6,0H39.1a6.7,6.7,0,0,1,6.6,6.6V39.1h0a6.7,6.7,0,0,1-6.6,6.6ZM39,4H6.6A2.6,2.6,0,0,0,4,6.6V39.1a2.6,2.6,0,0,0,2.6,2.6H39.1a2.6,2.6,0,0,0,2.6-2.6V6.6A2.7,2.7,0,0,0,39,4Zm4.7,35.1Zm-4.6-.4H6.6a2.1,2.1,0,0,1-1.8-1.1,2,2,0,0,1,.3-2.1l8.1-10.4a1.8,1.8,0,0,1,1.5-.8,2.4,2.4,0,0,1,1.6.7l4.2,5.1,6.6-8.5a1.8,1.8,0,0,1,1.6-.8,1.8,1.8,0,0,1,1.5.8L40.7,35.5a2,2,0,0,1,.1,2.1A1.8,1.8,0,0,1,39.1,38.7Zm-17.2-4H35.1l-6.5-8.6-6.5,8.4C22,34.6,22,34.7,21.9,34.7Zm-11.2,0H19l-4.2-5.1Z" fill="#f68b3c"></path>
							                 </svg>
										</button>
										<button class="submit" id="send-message-btn">
											<svg xmlns="http://www.w3.org/2000/svg" class="svg-icon svg-icon--send" viewBox="0 0 45.6 45.6">
							                    <g>
							                      <path d="M20.7,26.7a1.4,1.4,0,0,1-1.2-.6,1.6,1.6,0,0,1,0-2.4L42.6.5a1.8,1.8,0,0,1,2.5,0,1.8,1.8,0,0,1,0,2.5L21.9,26.1A1.6,1.6,0,0,1,20.7,26.7Z" fill="#d87232"></path>
							                      <path d="M29.1,45.6a1.8,1.8,0,0,1-1.6-1L19.4,26.2,1,18.1a1.9,1.9,0,0,1-1-1.7,1.8,1.8,0,0,1,1.2-1.6L43.3.1a1.7,1.7,0,0,1,1.8.4,1.7,1.7,0,0,1,.4,1.8L30.8,44.4a1.8,1.8,0,0,1-1.6,1.2ZM6.5,16.7l14.9,6.6a2,2,0,0,1,.9.9l6.6,14.9L41,4.6Z" fill="#d87232"></path>
							                    </g>
							                  </svg>
										</button> -->
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