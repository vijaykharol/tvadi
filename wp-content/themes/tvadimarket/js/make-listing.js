/**
 * MAKE LISTING STUFF...
 */
jQuery(document).ready(function(){
    //MAKE LISTING..
    jQuery(document).on('submit', '#make-listing-form', function(e){
        e.preventDefault();
        //validate form
        var check = true;
        //type
        var listing_type = jQuery('#listing-type').find(':selected').val();
        if(listing_type == ''){
            jQuery('.listing-type-error').remove();
            jQuery('#listing-type').css('outline', '2px solid red');
            jQuery('#listing-type').after('<span class="error listing-type-error" style="display:block; color:red;">This field is required.</span>');
            check = false;
            jQuery('#listing-type').focus();
        }else{
            jQuery('.listing-type-error').remove();
            jQuery('#listing-type').css('outline', 'none');
        }

        //title
        var listing_title = jQuery('#listing-title').val();
        if(listing_title == ''){
            jQuery('.listing-title-error').remove();
            jQuery('#listing-title').css('outline', '2px solid red');
            jQuery('#listing-title').after('<span class="error listing-title-error" style="display:block; color:red;">This field is required.</span>');
            check = false;
            jQuery('#listing-title').focus();
        }else{
            jQuery('.listing-title-error').remove();
            jQuery('#listing-title').css('outline', 'none');
        }

        if(check){
            jQuery('#review-make-listing').html('Please wait.. <img src="'+ajax_object.stylesheet_dir+'/images/loader.gif" class="loader">');
            var listingData = new FormData(jQuery('form#make-listing-form')[0]);
            listingData.append('action', 'make_listing');
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   listingData,
                processData     :   false, 
                contentType     :   false,
                success: function(response){
                    let res         =   JSON.parse(response);
                    let status      =   res.status;
                    let message     =   res.message;
                    let url         =   res.url;
                    if(status){
                        //hide/empty error
                        jQuery('.l-error').html('');
                        jQuery('.l-error').hide();

                        //show success
                        jQuery('.l-success').html('');
                        jQuery('.l-success').html(message);
                        jQuery('.l-success').show();

                        setTimeout(() => {
                            window.location.href = url;
                        }, 2000);
                    }else{
                        jQuery('#review-make-listing').html('Review <img src="'+ajax_object.stylesheet_dir+'/images/arrow.svg" class="img-fluid" alt="Make Listing">');
                        //hide/empty error
                        jQuery('.l-success').html('');
                        jQuery('.l-success').hide();
                        //show error
                        jQuery('.l-error').html('');
                        jQuery('.l-error').html(message);
                        jQuery('.l-error').show();
                    }
                }
            });
        }
    });

    //EDIT LISTING..
    jQuery(document).on('click', '#update-make-listing-process', function(e){
        e.preventDefault();
        //validate form
        var check = true;
        //type
        var listing_type = jQuery('#listing-type').find(':selected').val();
        if(listing_type == ''){
            jQuery('.listing-type-error').remove();
            jQuery('#listing-type').css('outline', '2px solid red');
            jQuery('#listing-type').after('<span class="error listing-type-error" style="display:block; color:red;">This field is required.</span>');
            check = false;
            jQuery('#listing-type').focus();
        }else{
            jQuery('.listing-type-error').remove();
            jQuery('#listing-type').css('outline', 'none');
        }

        //title
        var listing_title = jQuery('#listing-title').val();
        if(listing_title == ''){
            jQuery('.listing-title-error').remove();
            jQuery('#listing-title').css('outline', '2px solid red');
            jQuery('#listing-title').after('<span class="error listing-title-error" style="display:block; color:red;">This field is required.</span>');
            check = false;
            jQuery('#listing-title').focus();
        }else{
            jQuery('.listing-title-error').remove();
            jQuery('#listing-title').css('outline', 'none');
        }

        if(check){
            jQuery('#update-make-listing-process').html('Please wait.. <img src="'+ajax_object.stylesheet_dir+'/images/loader.gif" class="loader">');
            var listingData = new FormData(jQuery('form#make-listing-form')[0]);
            listingData.append('action', 'update_listing');
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   listingData,
                processData     :   false, 
                contentType     :   false,
                success: function(response){
                    let res         =   JSON.parse(response);
                    let status      =   res.status;
                    let message     =   res.message;
                    let url         =   res.url;
                    if(status){
                        //hide/empty error
                        jQuery('.l-error').html('');
                        jQuery('.l-error').hide();

                        //show success
                        jQuery('.l-success').html('');
                        jQuery('.l-success').html(message);
                        jQuery('.l-success').show();

                        setTimeout(() => {
                            window.location.href = url;
                        }, 2000);
                    }else{
                        jQuery('#update-make-listing-process').html('Review <img src="'+ajax_object.stylesheet_dir+'/images/arrow.svg" class="img-fluid" alt="Make Listing">');
                        //hide/empty error
                        jQuery('.l-success').html('');
                        jQuery('.l-success').hide();
                        //show error
                        jQuery('.l-error').html('');
                        jQuery('.l-error').html(message);
                        jQuery('.l-error').show();
                    }
                }
            });
        }
    });
});


//Market Filter Events
jQuery(document).ready(function(){
    //search submit
    jQuery(document).on('click', '#market-listings-search-btn', function(e){
        e.preventDefault();
        filterMarketListings();
    });

    //price filter -- MIN and MAX change
    jQuery(document).on('input', '#market-price-range-section input[type="range"]', function(){
        filterMarketListings();
    });

    jQuery(document).on('input', '#market-price-filter input[type="number"]', function(){
        filterMarketListings();
    });

    //avg rating
    jQuery(document).on('input', '#market-avg-rating-range-section input[type="range"]', function(){
        filterMarketListings();
    });

    jQuery(document).on('input', '#market-avg-rating-filter input[type="number"]', function(){
        filterMarketListings();
    });

    jQuery(document).on('click', '#market-average-rating-mode', function(){
        filterMarketListings();
    });
    
    //listing type
    jQuery(document).on('click', 'input[name="listing_type"]', function(){
        filterMarketListings();
    });
    
    //price mode
    jQuery(document).on('click', 'input#market-price-filter-mode', function(){
      filterMarketListings();
    });
  
    //date filter
    jQuery(document).on('change', '#market-start-date', function(){
      filterMarketListings();
    });
    jQuery(document).on('change', '#market-end-date', function(){
      filterMarketListings();
    });
  
    //plateforms
    jQuery(document).on('change', 'input[name="market_platforms[]"]', function(){
        filterMarketListings();
    });

    //Sortby Filter
    jQuery(document).on('change', '#market-sortby-filters', function(){
        filterMarketListings();
    });
    // Disable and readonly based on auction listing select box 
    jQuery("select[name='auction_listing']").change(function() {
        var selectedOption = jQuery(this).val();        
        if (selectedOption === "No") {            
            jQuery("select[name='auction_length']").prop("disabled", true);
            jQuery("input[name='reserve_amount']").prop("readonly", true);
        } else {            
            jQuery("select[name='auction_length']").prop("disabled", false);
            jQuery("input[name='reserve_amount']").prop("readonly", false);
        }
    });
});
  
//Filter Outlet
function filterMarketListings(){
    //search
    var search =  jQuery('#market-search-content').val();
    //listing type
    var listing_type = jQuery('input[name="listing_type"]:checked').val();

    //price
    if(jQuery('input#market-price-filter-mode').is(':checked')){
        var price_filter_mode = 'On';
    }else{
        var price_filter_mode = '';
    }
    var min_price   =   jQuery('#market-input-min-field').val();
    var max_price   =   jQuery('#market-input-max-field').val();

    //rating
    if(jQuery('#market-average-rating-mode').is(':checked')){
        var rating_filter_mode = 'On';
    }else{
        var rating_filter_mode = '';
    }
    var min_rating   =   jQuery('#market-min-rating').val();
    var max_rating   =   jQuery('#market-max-rating').val();

    //date
    var start_date  =   jQuery('#market-start-date').val();
    var end_date    =   jQuery('#market-end-date').val();

    //platforms
    var plateforms = [];
    jQuery('input[name="market_platforms[]"]:checked').each(function(){
        plateforms.push(jQuery(this).val());
    });

    //sortby
    var sortby      =   jQuery('#market-sortby-filters').find(':selected').val();

    //AJAX REQUEST
    jQuery.ajax({
        url             :   ajax_object.ajax_url,
        type            :   'POST',
        data            :   {
            action          :   'filter_market_listing',
            search          :   search,
            listing_type    :   listing_type,
            price_f_m       :   price_filter_mode,
            min_price       :   min_price,
            max_price       :   max_price,
            start_date      :   start_date,
            end_date        :   end_date,
            plateforms      :   plateforms,
            sortby          :   sortby,
            rating_filter_mode : rating_filter_mode,
            min_rating      :   min_rating,
            max_rating      :   max_rating,
        },
        success: function(response){
            jQuery('#market-listings-data-section').html('');
            jQuery('#market-listings-data-section').html(response);
        },
        error: function(reserr){
        console.log(reserr);
        }
    });
}

/**
 * PREVIEW UPLOADED IMAGE PROCESS
 */
document.addEventListener('DOMContentLoaded', function(){
    var input = document.getElementById('listing-upload-image');
    
    input.addEventListener('change', function(){
        var file = input.files[0];
        if(file){
            var reader = new FileReader();
            reader.onload = function(e){
                var css = '.file-wrapper:after { background-image: url(' + e.target.result + ') !important; }';
                jQuery('#dynamic-styles').remove();
                jQuery('<style id="dynamic-styles">' + css + '</style>').appendTo('head');
            }
            reader.readAsDataURL(file);
        } 
    });
});

/**
 * Make Listing Platforms
 */
jQuery(document).ready(function(){
    jQuery(document).on('change', '#listing-type', function(){
        var listing_type = jQuery('#listing-type').find(':selected').val();
         //AJAX REQUEST
        jQuery.ajax({
            url             :   ajax_object.ajax_url,
            type            :   'POST',
            data            :   {
                action          :   'platform_process',
                listing_type    :   listing_type,
            },
            success: function(response){
                jQuery('#listing-platform').html('');
                jQuery('#listing-platform').html(response);
            },
            error: function(reserr){
                console.log(reserr);
            }
        });
    });
});

/**
 * CHAT PROCESS.. Code Start From Here..........................................................
 */
jQuery(document).ready(function(){
    scrollToBottom();
    //send message process
    jQuery(document).on('click', '#send-message-btn', function(){
        var receiver  =     jQuery('#receiver-id').val();
        var sender    =     jQuery('#sender-id').val();
        var message   =     jQuery('#message-text').val();
        var parent    =     jQuery('#parent-id').val();
        var files     =     jQuery('#file-input')[0].files;
        if(message == '' && files.length <= 0){
            jQuery('button#send-message-btn i').css('color', 'red');
            return false;
        }
        if(!validateMessage(message)){
            jQuery('button#send-message-btn i').css('color', 'red');
            return false;
        }
        if((receiver != '') && (sender != '') && (parent != '')){
            jQuery('#message-text').text('');
            jQuery('.emojionearea-editor').html('');
            jQuery('#image-preview').empty();
            jQuery('#send-message-btn').html('<img src="'+ajax_object.stylesheet_dir+'/images/loader.gif" class="chat-loader" height="25" width="25">');
            var formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('receiver', receiver);
            formData.append('sender', sender);
            formData.append('message', message);
            formData.append('parent', parent);
            for(var i = 0; i < files.length; i++) {
                formData.append('attachments[]', files[i]);
            }
            jQuery.ajax({
                url             :   ajax_object.ajax_url,
                type            :   'POST',
                data            :   formData,
                processData     :   false,
                contentType     :   false,
                success: function(response){
                    const resss = JSON.parse(response);
                    if(resss.status){
                        if(resss.html != ''){
                            jQuery('#message-content-section').html('');
                            jQuery('#message-content-section').html(resss.html);
                            scrollToBottom();
                        }
                    }else{
                        alert(resss.message);
                        location.reload();
                    }
                    jQuery('#send-message-btn').html('<i class="fa fa-paper-plane" aria-hidden="true"></i>');
                },
                error: function(reserr){
                    console.log(reserr);
                }
            });
        }else{
            alert('Something went wrong please try again. Thanks!');
            location.reload();
        }
    });
});

//Show particular user chats..
function showUserChat(parent_id, c_user_id, btn){
    if(parent_id != ''){
        jQuery('li.contact').removeClass('active-list');
        jQuery(btn).addClass('active-list');
        jQuery.ajax({
            url             :   ajax_object.ajax_url,
            type            :   'POST',
            data            :   {
                action      :   'show_user_chats',
                parent_id   :   parent_id,
                c_user_id   :   c_user_id,
            },
            success: function(response){
                const resss = JSON.parse(response);
                if(resss.status){
                    if(resss.html != ''){
                        jQuery('#tvadi-user-messages-section').html('');
                        jQuery('#tvadi-user-messages-section').html(resss.html);
                        jQuery('#tvadi-user-messages-section').attr('data-parent', parent_id);
                        emojiTrigger();
                        scrollToBottom();
                    }
                }else{
                    alert(resss.message);
                    location.reload();
                }
            },
            error: function(reserr){
                console.log(reserr);
            }
        });
    }else{
        alert('Something went wrong please try again. Thanks!');
        location.reload();
    }
}

function emojiTrigger(){
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
jQuery(document).ready(function(){
    jQuery(document).on('click', '#attach-file-btn', function(e){
        e.preventDefault();
        jQuery('#file-input').click();
    });

    jQuery(document).on('change', '#file-input', function(e){
        var files = e.target.files;
        jQuery('#image-preview').empty();
        if(files){
            Array.from(files).forEach(file => {
                if (file.type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = jQuery('<img />', {
                            'src': e.target.result,
                            'class': 'preview-image'
                        });
                        jQuery('#image-preview').append(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
});

//message scroll to bottm
function scrollToBottom(){
    if(jQuery('.submessages').length > 0){
        const submessages = jQuery('.submessages');
        submessages.scrollTop(submessages[0].scrollHeight);
    }
}

// Validation email and phone number function
function validateMessage(message){
    const emailRegex = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,7}\b/;
    const phoneRegex = /\b\d{10}\b/;

    if(emailRegex.test(message)){
        // alert("Email addresses are not allowed in the message.");
        return false;
    }

    if(phoneRegex.test(message)){
        // alert("Phone numbers are not allowed in the message.");
        return false;
    }
    return true;
}