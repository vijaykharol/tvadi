<?php
/*
Template Name: Checkout
*/
if(!defined('ABSPATH')){
    exit;
}

if(!is_user_logged_in()){
    header("Location: ".site_url()); 
    exit();
}
get_header(); 

require_once get_template_directory() . '/stripe/init.php';

$options            =   get_option('stripe_settings');
$stripe_api_key     =   isset($options['stripe_api_key']) ? esc_attr($options['stripe_api_key']) : '';
$stripe_api_secret  =   isset($options['stripe_api_secret']) ? esc_attr($options['stripe_api_secret']) : '';
$stripe_client_id   =   isset($options['stripe_client_id']) ? esc_attr($options['stripe_client_id']) : '';

\Stripe\Stripe::setApiKey($stripe_api_secret);

$clientID       =   $stripe_client_id;
$current_user   =   wp_get_current_user();
$userid         =   $current_user->ID;
$first_name     =   (!empty(get_user_meta($userid, 'first_name', true)))    ?   get_user_meta($userid, 'first_name', true)  : '';
$last_name      =   (!empty(get_user_meta($userid, 'last_name', true)))     ?   get_user_meta($userid, 'last_name', true)   : '';
$totalamount    =   (isset($_POST['total_amount'])  && !empty(floatval($_POST['total_amount'])))    ?   floatval($_POST['total_amount'])    :   0;
$sellerAmount   =   (isset($_POST['seller_amount']) && !empty(floatval($_POST['seller_amount'])))   ?   floatval($_POST['seller_amount'])   :   0;
$post_author    =   (isset($_POST['post_author'])   && !empty($_POST['post_author'])) ? $_POST['post_author']   :   '';
$listing_id     =   (isset($_POST['product_id'])    && !empty($_POST['product_id'])) ? $_POST['product_id']     :   '';
$listinginfo    =   (!empty($listing_id)) ? get_post($listing_id) : [];
?>
<script src="https://js.stripe.com/v3/"></script>
<style>
    div#card-element {
        background: #fff;
        height: 23px;
    }
    form#payment-form .form-group {
        display: grid;
        margin-bottom: 15px;
    }
    span.total-amount {
        background-color: #f9f9f95c;
        padding: 10px;
        margin-top: 5px;
        border-radius: 10px;
    }
    div#card-errors {
        color: red;
        margin-top: 5px;
    }
    div#card-number-element, div#card-expiry-element, div#card-cvc-element, span.form-field {
        font-weight: 600;
        font-size: 18px;
        border-radius: 10px !important;
        padding: 15px 25px !important;
        border: 1px solid #6c6c6c !important;
        height: 60px;
        outline: 0;
        width: 100%;
        background: #fff;
    }
    span.form-field {
        color: #000;
    }
</style>
<section class="checkout-page">
    <div class="container">
        <div class="checkout-page-block">
            <h3>Checkout</h3>
            <form id="payment-form">
                <?php 
                if(!empty($listinginfo)){
                    ?>
                    <div class="form-group">
                        <label for="">Listing Name</label>
                        <span class="form-field"><?= $listinginfo->post_title ?></span>
                    </div>
                    <?php
                }
                ?>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= $first_name ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= $last_name ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $current_user->user_email ?>" required>
                </div>
                <div class="form-group">
                    <label for="total-amount">Amount (in USD)</label>
                    <span class="total-amount">$<?= number_format($totalamount, 2) ?></span>
                    <input type="hidden" id="total-amount" name="total-amount" value="<?= $totalamount ?>">
                </div>
                <input type="hidden" name="seller-amount" id="seller-amount" value="<?= $sellerAmount ?>">
                <input type="hidden" name="listing-author" id="listing-author" value="<?= $post_author ?>">
                <input type="hidden" name="listing-id" id="listing-id" value="<?= $listing_id ?>">
                <div class="form-group">
                    <label for="card-number-element">Card Number</label>
                    <div id="card-number-element"></div>
                </div>
                <div class="form-group">
                    <label for="card-expiry-element">Expiration Date</label>
                    <div id="card-expiry-element"></div>
                </div>
                <div class="form-group">
                    <label for="card-cvc-element">CVC</label>
                    <div id="card-cvc-element"></div>
                </div>
                <div class="form-group">
                    <div id="card-errors" role="alert"></div>
                </div>
                <!-- <div class="form-group">
                    <label for="card-element">Credit or Debit Card</label>
                    <div id="card-element"></div>
                </div> -->
                <div class="form-group">
                    <div class="sucess" style="display:none; color:green;"></div>
                    <div class="error" style="display:none; color:red;"></div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" id="submit-checkout-payment-btn">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</section>
<?php 
get_footer(); 
?>
<script>
    /*
    jQuery(document).ready(function(){
        var stripe = Stripe('<?= $stripe_api_key ?>');
        var elements = stripe.elements();
        var card = elements.create('card');
        card.mount('#card-element');

        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Create the card number element
        var cardNumber = elements.create('cardNumber', {style: style});
        cardNumber.mount('#card-number-element');

        // Create the card expiry element
        var cardExpiry = elements.create('cardExpiry', {style: style});
        cardExpiry.mount('#card-expiry-element');

        // Create the card CVC element
        var cardCvc = elements.create('cardCvc', {style: style});
        cardCvc.mount('#card-cvc-element');

        card.on('change', function(event){
            var displayError = document.getElementById('card-errors');
            if(event.error){
                displayError.textContent = event.error.message;
            }else{
                displayError.textContent = '';
            }
        });

        jQuery('#payment-form').on('submit', function(event){
            event.preventDefault();

            stripe.createToken(card).then(function(result){
                if(result.error){
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                }else{
                    stripeTokenHandler(result.token);
                }
            });
        });

        function stripeTokenHandler(token){
            jQuery('#submit-checkout-payment-btn').html('<img class="loader" src="<?= get_stylesheet_directory_uri() ?>/images/loader.gif">');
            var formData = {
                action          :   'process_payment',
                stripeToken     :   token.id,
                first_name      :   jQuery('#first_name').val(),
                last_name       :   jQuery('#last_name').val(),
                email           :   jQuery('#email').val(),
                total_amount    :   jQuery('#total-amount').val(),
                seller_amount   :   jQuery('#seller-amount').val(),
                lisitng_author  :   jQuery('#listing-author').val(),
                lisitng_id      :   jQuery('#listing-id').val(),
            };

            jQuery.ajax({
                url         :   '<?= admin_url('admin-ajax.php') ?>',
                method      :   'POST',
                data        :   formData,
                success     :   function(response){
                    const result = JSON.parse(response);
                    if(result.status){
                        jQuery('.error').hide();
                        jQuery('.sucess').html('');
                        jQuery('.sucess').html(result.message);
                        jQuery('.sucess').show();
                    }else{
                        jQuery('.sucess').hide();
                        jQuery('.error').html('');
                        jQuery('.error').html(result.message);
                        jQuery('.error').show();
                    }
                    jQuery('#submit-checkout-payment-btn').html('Submit Payment');
                    // if(response.error){
                    //     jQuery('#card-errors').text(response.error);
                    // }else{
                    //     window.location.href = response.redirect_url;
                    // }
                }
            });
        }
    });
    */
    jQuery(document).ready(function(){
        var stripe = Stripe('<?= $stripe_api_key ?>');
        var elements = stripe.elements();

        var style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Create the card number element
        var cardNumber = elements.create('cardNumber', {style: style});
        cardNumber.mount('#card-number-element');

        // Create the card expiry element
        var cardExpiry = elements.create('cardExpiry', {style: style});
        cardExpiry.mount('#card-expiry-element');

        // Create the card CVC element
        var cardCvc = elements.create('cardCvc', {style: style});
        cardCvc.mount('#card-cvc-element');

        // Handle real-time validation errors from the card element
        cardNumber.on('change', function(event){
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission
        jQuery('#payment-form').on('submit', function(event){
            event.preventDefault();

            stripe.createToken(cardNumber).then(function(result){
                if(result.error){
                    // Inform the user if there was an error
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                }else{
                    // Send the token to your server
                    stripeTokenHandler(result.token);
                }
            });
        });

        function stripeTokenHandler(token){
            jQuery('#submit-checkout-payment-btn').html('<img class="loader" src="<?= get_stylesheet_directory_uri() ?>/images/loader.gif">');
            var formData = {
                action          :   'process_payment',
                stripeToken     :   token.id,
                first_name      :   jQuery('#first_name').val(),
                last_name       :   jQuery('#last_name').val(),
                email           :   jQuery('#email').val(),
                total_amount    :   jQuery('#total-amount').val(),
                seller_amount   :   jQuery('#seller-amount').val(),
                lisitng_author  :   jQuery('#listing-author').val(),
                lisitng_id      :   jQuery('#listing-id').val(),
            };

            jQuery.ajax({
                url         :   '<?= admin_url('admin-ajax.php') ?>',
                method      :   'POST',
                data        :   formData,
                success     :   function(response){
                    const result = JSON.parse(response);
                    if(result.status){
                        jQuery('.error').hide();
                        jQuery('.sucess').html('');
                        jQuery('.sucess').html(result.message);
                        jQuery('.sucess').show();
                    }else{
                        jQuery('.sucess').hide();
                        jQuery('.error').html('');
                        jQuery('.error').html(result.message);
                        jQuery('.error').show();
                    }
                    jQuery('#submit-checkout-payment-btn').html('Submit Payment');
                }
            });
        }
    });
</script>