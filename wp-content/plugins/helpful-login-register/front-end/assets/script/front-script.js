jQuery(document).ready(function(){
    //Login Form 
    jQuery(document).on('submit', '#hp-login-form', function(e){
        e.preventDefault();
        let logincheck = true;
        var username = jQuery('#username').val();
        if(username == ''){
            logincheck = false;
            jQuery('#username').css('outline', '1px solid red');
            jQuery('.username-error').remove();
            jQuery('#username').after('<span class="error username-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#username').focus();
        }else{
            jQuery('.username-error').remove();
            jQuery('#username').css('outline', 'none');
        }

        var password = jQuery('#password').val();
        if(password == ''){
            logincheck = false;
            jQuery('#password').css('outline', '1px solid red');
            jQuery('.password-error').remove();
            jQuery('#password').after('<span class="error password-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#password').focus();
        }else{
            jQuery('.password-error').remove();
            jQuery('#password').css('outline', 'none');
        }

        if(logincheck){
            jQuery('#login-btn').html('<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/loader.gif" class="loader">');
            var logindata = new FormData(jQuery('#hp-login-form')[0]);
            logindata.append('action', 'hp_login_process');
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   logindata,
                processData     :   false, 
                contentType     :   false,
                success: function(response){
                    let res         =   JSON.parse(response);
                    let status      =   res.status;
                    let message     =   res.message;
                    if(status){
                        //hide/empty error
                        jQuery('#l-error').html('');
                        jQuery('#l-error').hide();

                        //show success
                        jQuery('#l-success').html('');
                        jQuery('#l-success').html(message);
                        jQuery('#l-success').show();

                        setTimeout(() => {
                            window.location.href="/dashboard/";
                        }, 2000);
                    }else{
                        jQuery('#login-btn').html('Login');
                        //hide/empty error
                        jQuery('#l-success').html('');
                        jQuery('#l-success').hide();

                        //show success
                        jQuery('#l-error').html('');
                        jQuery('#l-error').html(message);
                        jQuery('#l-error').show();

                    }
                }
            });
        }
    });

    //Registration Form
    jQuery(document).on('submit', '#custom-registration-form', function(e){
        e.preventDefault();
        let requiredf = true;
        //js validate
        var firstname = jQuery('#firstname').val();
        if(firstname == ''){
            requiredf = false;
            jQuery('#firstname').css('outline', '2px solid red');
            jQuery('.firstname-error').remove();
            jQuery('#firstname').after('<span class="error firstname-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#firstname').focus();
        }else{
            jQuery('.firstname-error').remove();
            jQuery('#firstname').css('outline', 'none');
        }

        var lastname = jQuery('#lastname').val();
        if(lastname == ''){
            requiredf = false;
            jQuery('#lastname').css('outline', '2px solid red');
            jQuery('.lastname-error').remove();
            jQuery('#lastname').after('<span class="error lastname-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#lastname').focus();
        }else{
            jQuery('.lastname-error').remove();
            jQuery('#lastname').css('outline', 'none');
        }

        // var username = jQuery('#username').val();
        // if(username == ''){
        //     requiredf = false;
        //     jQuery('#username').css('outline', '2px solid red');
        //     jQuery('.username-error').remove();
        //     jQuery('#username').after('<span class="error username-error" style="display:block; color:red;">This field is required.</span>');
        //     jQuery('#username').focus();
        // }else{
        //     jQuery('.username-error').remove();
        //     jQuery('#username').css('outline', 'none');
        // }

        var email = jQuery('#email').val();
        if(email == ''){
            requiredf = false;
            jQuery('#email').css('outline', '2px solid red');
            jQuery('.email-error').remove();
            jQuery('#email').after('<span class="error email-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#email').focus();
        }else{
            jQuery('.email-error').remove();
            jQuery('#email').css('outline', 'none');
        }

        var password = jQuery('#password').val();
        if(password == ''){
            requiredf = false;
            jQuery('#password').css('outline', '2px solid red');
            jQuery('.password-error').remove();
            jQuery('#password').after('<span class="error password-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#password').focus();
        }else{
            jQuery('.password-error').remove();
            jQuery('#password').css('outline', 'none');
        }

        var confirm_password = jQuery('#confirm_password').val();
        if(confirm_password == ''){
            requiredf = false;
            jQuery('#confirm_password').css('outline', '2px solid red');
            jQuery('.confirm_password-error').remove();
            jQuery('#confirm_password').after('<span class="error confirm_password-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#confirm_password').focus();
        }else{
            jQuery('.confirm_password-error').remove();
            jQuery('#confirm_password').css('outline', 'none');
        }

        var checkedRadioLength = jQuery('input[name="user_role"]:checked').length;
        if(checkedRadioLength == 0){
            requiredf = false;
            jQuery('.roles-section-error').remove();
            jQuery('.roles-section').after('<span class="error roles-section-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('.roles-section').focus();
        }else{
            jQuery('.roles-section-error').remove();
        }

        if(requiredf){
            jQuery('#hplr-register-btn').html('<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/loader.gif" class="loader">');
            var logindata = new FormData(jQuery('#custom-registration-form')[0]);
            logindata.append('action', 'hp_register_process');
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   logindata,
                processData     :   false, 
                contentType     :   false,
                success: function(response){
                    let res         =   JSON.parse(response);
                    let status      =   res.status;
                    let message     =   res.message;
                    if(status){
                        //hide/empty error
                        jQuery('#l-error').html('');
                        jQuery('#l-error').hide();

                        //show success
                        jQuery('#l-success').html('');
                        jQuery('#l-success').html(message);
                        jQuery('#l-success').show();
                        jQuery('html, body').animate({scrollTop : 0},800);

                        setTimeout(() => {
                            window.location.href = res.redirecturl;
                        }, 2000);
                    }else{
                        jQuery('#hplr-register-btn').html('Register');
                        //hide/empty error
                        jQuery('#l-success').html('');
                        jQuery('#l-success').hide();

                        //show success
                        jQuery('#l-error').html('');
                        jQuery('#l-error').html(message);
                        jQuery('#l-error').show();

                        jQuery('html, body').animate({scrollTop : 0},800);

                    }
                }
            });
        }
    });

    //Forgot password
    jQuery(document).on('submit', '#forgot_password_form', function(e){
        e.preventDefault();
        let pchecking = true;
        var user_email = jQuery('#user_email').val();
        if(user_email == ''){
            pchecking = false;
            jQuery('#user_email').css('outline', '1px solid red');
            jQuery('.user_email-error').remove();
            jQuery('#user_email').after('<span class="error user_email-error" style="display:block; color:red;">This field is required.</span>');
            jQuery('#user_email').focus();
        }else{
            jQuery('.user_email-error').remove();
            jQuery('#user_email').css('outline', 'none');
        }

        if(pchecking){
            jQuery('#submit-forgotpass').html('<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/loader.gif" class="loader">');
            var forgotdata = new FormData(jQuery('#forgot_password_form')[0]);
            forgotdata.append('action', 'hp_forgotp_process');
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   forgotdata,
                processData     :   false, 
                contentType     :   false,
                success: function(response){
                    let res         =   JSON.parse(response);
                    let status      =   res.status;
                    let message     =   res.message;
                    if(status){
                        //hide/empty error
                        jQuery('#l-error').html('');
                        jQuery('#l-error').hide();

                        //show success
                        jQuery('#l-success').html('');
                        jQuery('#l-success').html(message);
                        jQuery('#l-success').show();
                        jQuery('html, body').animate({scrollTop : 0},800);
                        jQuery('#submit-forgotpass').html('Submit');
                    }else{
                        jQuery('#submit-forgotpass').html('Submit');
                        //hide/empty error
                        jQuery('#l-success').html('');
                        jQuery('#l-success').hide();

                        //show success
                        jQuery('#l-error').html('');
                        jQuery('#l-error').html(message);
                        jQuery('#l-error').show();

                        jQuery('html, body').animate({scrollTop : 0},800);

                    }
                }
            });
        }
    });

    //Dashboard Edit profile
    jQuery(document).on('submit', '#d-edit-profile', function(e){
        e.preventDefault();
        let check = true;
        var firstname = jQuery('#first_name').val();
        if(firstname == ''){
            check = false;
            jQuery('#first_name').css('outline', '2px solid red');
        }else{
            jQuery('#first_name').css('outline', 'none');
        }
        var last_name = jQuery('#last_name').val();
        if(last_name == ''){
            check = false;
            jQuery('#last_name').css('outline', '2px solid red');
        }else{
            jQuery('#last_name').css('outline', 'none');
        }
        var email = jQuery('#email').val();
        if(email == ''){
            check = false;
            jQuery('#email').css('outline', '2px solid red');
        }else{
            jQuery('#email').css('outline', 'none');
        }
        if(check){
            jQuery('#updateprofile').html('<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/loader.gif" class="loader">');
            var forgotdata = new FormData(jQuery('#d-edit-profile')[0]);
            forgotdata.append('action', 'hp_edit_profile');
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   forgotdata,
                processData     :   false, 
                contentType     :   false,
                success: function(response){
                    let res         =   JSON.parse(response);
                    let status      =   res.status;
                    let message     =   res.message;
                    if(status){
                        //hide/empty error
                        jQuery('#l-error').html('');
                        jQuery('#l-error').hide();

                        //show success
                        jQuery('#l-success').html('');
                        jQuery('#l-success').html(message);
                        jQuery('#l-success').show();
                        jQuery('html, body').animate({scrollTop : 0},800);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }else{
                        jQuery('#updateprofile').html('Update Profile');
                        //hide/empty error
                        jQuery('#l-success').html('');
                        jQuery('#l-success').hide();

                        //show success
                        jQuery('#l-error').html('');
                        jQuery('#l-error').html(message);
                        jQuery('#l-error').show();

                        jQuery('html, body').animate({scrollTop : 0},800);

                    }
                }
            });
        }
    });

    //Dashboard change password
    jQuery(document).on('submit', '#d-change-password', function(e){
        e.preventDefault();
        let check_s = true;
        var new_password = jQuery('#new_password').val();
        if(new_password == ''){
            check_s = false;
            jQuery('#new_password').css('outline', '2px solid red');
        }else{
            jQuery('#new_password').css('outline', 'none');
        }
        var confirm_new_password = jQuery('#confirm_new_password').val();
        if(confirm_new_password == ''){
            check_s = false;
            jQuery('#confirm_new_password').css('outline', '2px solid red');
        }else{
            jQuery('#confirm_new_password').css('outline', 'none');
        }
        if(check_s){
            jQuery('#d-change-password-btn').html('<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/loader.gif" class="loader">');
            var forgotdata = new FormData(jQuery('#d-change-password')[0]);
            forgotdata.append('action', 'hp_change_pass');
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   forgotdata,
                processData     :   false, 
                contentType     :   false,
                success: function(response){
                    let res         =   JSON.parse(response);
                    let status      =   res.status;
                    let message     =   res.message;
                    if(status){
                        //hide/empty error
                        jQuery('#l-error').html('');
                        jQuery('#l-error').hide();

                        //show success
                        jQuery('#l-success').html('');
                        jQuery('#l-success').html(message);
                        jQuery('#l-success').show();
                        jQuery('html, body').animate({scrollTop : 0},800);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }else{
                        jQuery('#d-change-password-btn').html('Change Password');
                        //hide/empty error
                        jQuery('#l-success').html('');
                        jQuery('#l-success').hide();

                        //show success
                        jQuery('#l-error').html('');
                        jQuery('#l-error').html(message);
                        jQuery('#l-error').show();

                        jQuery('html, body').animate({scrollTop : 0},800);

                    }
                }
            });
        }
    });
});

/**
 * PREVIEW UPLOADED IMAGE PROCESS
 */
document.addEventListener('DOMContentLoaded', function(){
    var input = document.getElementById('profile-pic');
    if(jQuery('#profile-pic').length){
        input.addEventListener('change', function(){
            var file = input.files[0];
            if(file){
                // var reader = new FileReader();
                // reader.onload = function(e){
                    jQuery('span#upload-profile-pic-btn').html('<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/loader.gif" style="width:25px; height:25px;">');
                // }
                // reader.readAsDataURL(file);
                //upload profile operation..
                var formData    =   new FormData();
                var fileInput   =   jQuery('#profile-pic')[0];
                formData.append('action', 'upload_profile');
                formData.append('file', fileInput.files[0]);
                jQuery.ajax({
                    url             :   ajax_object.ajax_url,
                    type            :   'POST',
                    data            :   formData,
                    contentType     :   false,
                    processData     :   false,
                    success: function(response){
                        const obj = JSON.parse(response);
                        if(obj.status){
                            if(obj.url){
                                jQuery('span#upload-profile-pic-btn').html('Upload Profile Pic');
                                jQuery('#profile-pic-preview .das-u-profile').attr('src', obj.url);
                            }
                        }else{
                            alert(obj.msg);
                            location.reload();
                        }
                    },
                    error: function(xhr, status, error){
                      console.error('File upload failed', error);
                    }
                });
            } 
        });
    }
});

/**
 * UPLOAD COVER IMAGE
 */
jQuery(document).ready(function(){
    jQuery(document).on('change', 'input#profile-cover-pic', function(){
        var fileInput = jQuery('input#profile-cover-pic')[0];
        if(fileInput.files.length > 0){
            jQuery('span#upload-profile-cover-img-btn').html('<img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/loader.gif" style="width:25px; height:25px;">');
            var formData = new FormData();
            formData.append('action', 'upload_cover');
            formData.append('cover', fileInput.files[0]);
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   formData,
                contentType     :   false,
                processData     :   false,
                success         :   function(response){
                    const obj = JSON.parse(response);
                    if(obj.status){
                        if(obj.url){
                            jQuery('span#upload-profile-cover-img-btn').html('Upload cover image');
                            jQuery('div#profile-cover-preview img.das-u-profile-cover').attr('src', obj.url);
                        }
                    }else{
                        alert(obj.msg);
                        location.reload();
                    }
                },
                error           :   function(xhr, status, error){
                    console.error(error);
                }
            });
        }else{
            alert('No file selected!');
        }
    });
});

jQuery(document).ready(function(){
    jQuery(document).on('click', 'button#copy-profile-url-button', function(){
        var copyText = jQuery('a#profile-url').attr('href');
        var tempInput = jQuery('<input>'); 
        jQuery('body').append(tempInput); 
        tempInput.val(copyText).select();
        try {
            document.execCommand('copy');
            console.log('URL copied to clipboard');
        } catch (err) {
            console.log('Failed to copy URL');
        }
        tempInput.remove(); 
        jQuery('#copyMessage').fadeIn(500).delay(2000).fadeOut(500); 
    });
});

