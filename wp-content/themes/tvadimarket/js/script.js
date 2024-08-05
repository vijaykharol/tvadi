jQuery(document).ready(function($) {

    // jQuery(document).ready(function(){
    //   jQuery('[data-bs-toggle="popover"]').popover();
    // });

    jQuery(document).on('click', '#unlogged-wishlist-btn', function(){
      jQuery('#logged-suggetion-model').show();
    });

    jQuery(document).on('click', 'div#logged-suggetion-model span.close', function(){
      jQuery('#logged-suggetion-model').hide();
    });

    // WOW ANIMATE
    new WOW().init();

    // Testimonial Slider
    $('#bannerslider').owlCarousel({
        loop: true,
        margin: 0,
        nav: false,
        items: 1,
        slideTransition: 'linear',
        autoplay: true
    });
    
    // PRICE RANGE SLIDER
    document.querySelectorAll('.price-range-container').forEach((container) => {
      const rangeInput = container.querySelectorAll(".range-input input"),
            priceInput = container.querySelectorAll(".price-input input"),
            range = container.querySelector(".slider .progress");
      let priceGap = 100;
  
      priceInput.forEach((input) => {
          input.addEventListener("input", (e) => {
              let minPrice = parseInt(priceInput[0].value),
                  maxPrice = parseInt(priceInput[1].value);
  
              if (maxPrice - minPrice >= priceGap && maxPrice <= rangeInput[1].max) {
                  if (e.target.className.includes("input-min")) {
                      rangeInput[0].value = minPrice;
                      range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
                  } else {
                      rangeInput[1].value = maxPrice;
                      range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
                  }
              }
          });
      });
  
      rangeInput.forEach((input) => {
          input.addEventListener("input", (e) => {
              let minVal = parseInt(rangeInput[0].value),
                  maxVal = parseInt(rangeInput[1].value);
  
              if (maxVal - minVal < priceGap) {
                  if (e.target.className.includes("range-min")) {
                      rangeInput[0].value = maxVal - priceGap;
                  } else {
                      rangeInput[1].value = minVal + priceGap;
                  }
              } else {
                  priceInput[0].value = minVal;
                  priceInput[1].value = maxVal;
                  range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
                  range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
              }
          });
      });
    });

    //RATING RANGE SLIDER
    document.querySelectorAll('.rating-range-container').forEach((container) => {
      const rangeInput = container.querySelectorAll(".range-input input"),
            ratingInput = container.querySelectorAll(".rating-input input"),
            range = container.querySelector(".slider .progress");
      let ratingGap = 0.5;
  
      ratingInput.forEach((input) => {
          input.addEventListener("input", (e) => {
              let minRating = parseFloat(ratingInput[0].value),
                  maxRating = parseFloat(ratingInput[1].value);
  
              if (maxRating - minRating >= ratingGap && maxRating <= parseFloat(rangeInput[1].max)) {
                  if (e.target.className.includes("input-min")) {
                      rangeInput[0].value = minRating;
                      range.style.left = (minRating / parseFloat(rangeInput[0].max)) * 100 + "%";
                  } else {
                      rangeInput[1].value = maxRating;
                      range.style.right = 100 - (maxRating / parseFloat(rangeInput[1].max)) * 100 + "%";
                  }
              }
          });
      });
  
      rangeInput.forEach((input) => {
          input.addEventListener("input", (e) => {
              let minVal = parseFloat(rangeInput[0].value),
                  maxVal = parseFloat(rangeInput[1].value);
  
              if (maxVal - minVal < ratingGap) {
                  if (e.target.className.includes("range-min")) {
                      rangeInput[0].value = maxVal - ratingGap;
                  } else {
                      rangeInput[1].value = minVal + ratingGap;
                  }
              } else {
                  ratingInput[0].value = minVal;
                  ratingInput[1].value = maxVal;
                  range.style.left = (minVal / parseFloat(rangeInput[0].max)) * 100 + "%";
                  range.style.right = 100 - (maxVal / parseFloat(rangeInput[1].max)) * 100 + "%";
              }
          });
      });
    });
    

    // TIME COUNTER
    (function() {
        const second = 1000,
            minute = second * 60,
            hour = minute * 60,
            day = hour * 24;

        //I'm adding this section so I don't have to keep updating this pen every year :-)
        //remove this if you don't need it
        let today = new Date(),
            dd = String(today.getDate()).padStart(2, "0"),
            mm = String(today.getMonth() + 1).padStart(2, "0"),
            yyyy = today.getFullYear(),
            nextYear = yyyy + 1,
            dayMonth = "01/30/",
            birthday = dayMonth + yyyy;

        today = mm + "/" + dd + "/" + yyyy;
        if (today > birthday) {
            birthday = dayMonth + nextYear;
        }
        //end

        const countDown = new Date(birthday).getTime(),
            x = setInterval(function() {

                const now = new Date().getTime(),
                    distance = countDown - now;
                if(jQuery('#days').length){
                  document.getElementById("days").innerText = Math.floor(distance / (day)),
                  document.getElementById("hours").innerText = Math.floor((distance % (day)) / (hour)),
                  document.getElementById("minutes").innerText = Math.floor((distance % (hour)) / (minute)),
                  document.getElementById("seconds").innerText = Math.floor((distance % (minute)) / second);
                }

            }, 0)
    }());

    // LIST GRID OPTION SLEECT
    const listViewButton = document.querySelector('.list-view-button');
    const gridViewButton = document.querySelector('.grid-view-button');
    const list = document.querySelector('.listing-sort');

    listViewButton.onclick = function() {
        list.classList.remove('grid');
        list.classList.add('list');
    }

    gridViewButton.onclick = function() {
        list.classList.remove('list');
        list.classList.add('grid');
    }

});

/**
 * TVADI LIKE 
 */
function tvadiLike(postid, btn){
  if(postid != ''){
    jQuery.ajax({
      url             :   ajax_object.ajax_url,
      type            :   'POST',
      data            :   {
        action : 'process_tvadiLike',
        postid : postid,
      },
      success: function(response){
          let res         =   JSON.parse(response);
          let status      =   res.status;
          let process     =   res.process;
          if(status){
            if(process == 'like'){
              jQuery(btn).html('<img src="/wp-content/themes/tvadimarket/images/Like.png" class="img-fluid" alt="Unlike">');
            }else{
              jQuery(btn).html('<img src="/wp-content/themes/tvadimarket/images/wishlist.png" class="img-fluid" alt="Like">');
            }
          }else{
            alert('Threre has been a problem, please refresh page and try again. Thanks.');
          }
      }
    });
  }
}

jQuery(document).ready(function(){
    jQuery(document).on('click', '#opensidebarBtn', function(){
        jQuery('#tvadi-sidebar').css('transform', 'translateX(0)');
    });

    jQuery(document).on('click', '#closeSidebarBtn', function(){
        jQuery('#tvadi-sidebar').css('transform', 'translateX(100%)');
    });
    jQuery('body,html').click(function(e){
       jQuery('#tvadi-sidebar').css('transform', 'translateX(100%)');
    });
});

/* TVADI TERM LIKE */
function likeTrendingTerm(term_id, btn){
  if(term_id != ''){
    jQuery.ajax({
      url             :   ajax_object.ajax_url,
      type            :   'POST',
      data            :   {
        action : 'tvadi_term_wishlist',
        term_id : term_id,
      },
      success: function(response){
          let res         =   JSON.parse(response);
          let status      =   res.status;
          let process     =   res.process;
          if(status){
            if(process == 'like'){
              jQuery(btn).html('<img src="/wp-content/themes/tvadimarket/images/Like.png" class="img-fluid" alt="Unlike">');
            }else{
              jQuery(btn).html('<img src="/wp-content/themes/tvadimarket/images/wishlist.png" class="img-fluid" alt="Like">');
            }
          }else{
            alert('Threre has been a problem, please refresh page and try again. Thanks.');
          }
      }
    });
  }
}

//Maker filter Events
jQuery(document).ready(function(){
  //search submit
  jQuery(document).on('click', '#search-maker-listing-btn', function(e){
      e.preventDefault();
      filterMakers();
  });

  //price filter -- MIN and MAX change
  jQuery(document).on('input', '#maker-price-range-section input[type="range"]', function(){
    filterMakers();
  });

  jQuery(document).on('input', '#makers-price-filter input[type="number"]', function(){
    filterMakers();
  });

  //avg rating
  jQuery(document).on('input', '#maker-avg-rating-range input[type="range"]', function(){
    filterMakers();
  });

  jQuery(document).on('input', '#makers-avg-rating-filter input[type="number"]', function(){
    filterMakers();
  });

  jQuery(document).on('click', '#avg-rating-filter-mode', function(){
    filterMakers();
  });

   //listing type
  jQuery(document).on('click', 'input[name="maker_listing_type"]', function(){
    filterMakers();
  });
  
  jQuery(document).on('click', '#price-filter-mode', function(){
    filterMakers();
  });

  //date filter
  jQuery(document).on('change', '#filter-start-date', function(){
    filterMakers();
  });
  jQuery(document).on('change', '#filter-end-date', function(){
    filterMakers();
  });

  //plateforms
  jQuery(document).on('change', 'input[name="plateforms[]"]', function(){
    filterMakers();
  });

  //Sortby Filter
  jQuery(document).on('change', '#makers-sortby-filters', function(){
    filterMakers();
  });
});

//Filter Makers
function filterMakers(){
  //search
  var search      =   jQuery('#maker-search-content').val();

  //listing type
  var listing_type = jQuery('input[name="maker_listing_type"]:checked').val();

  //price
  if(jQuery('#price-filter-mode').is(':checked')){
    var price_filter_mode = 'On';
  }else{
    var price_filter_mode = '';
  }
  var min_price   =   jQuery('#input-min-field').val();
  var max_price   =   jQuery('#input-max-field').val();

  //rating
  if(jQuery('#avg-rating-filter-mode').is(':checked')){
    var rating_filter_mode = 'On';
  }else{
    var rating_filter_mode = '';
  }
  var min_rating   =   jQuery('#maker-min-rating').val();
  var max_rating   =   jQuery('#maker-max-rating').val();

  //date
  var start_date  =   jQuery('#filter-start-date').val();
  var end_date    =   jQuery('#filter-end-date').val();

  //plateforms
  var plateforms  =   [];
  jQuery('input[name="plateforms[]"]:checked').each(function(){
    plateforms.push(jQuery(this).val());
  });
  
  //sortby
  var sortby      =   jQuery('#makers-sortby-filters').find(':selected').val();

  //AJAX REQUEST
  jQuery.ajax({
    url             :   ajax_object.ajax_url,
    type            :   'POST',
    data            :   {
      action        :   'filter_makers',
      search        :   search,
      listing_type  :   listing_type,
      price_f_m     :   price_filter_mode,
      min_price     :   min_price,
      max_price     :   max_price,
      start_date    :   start_date,
      end_date      :   end_date,
      plateforms    :   plateforms,
      sortby        :   sortby,
      rating_filter_mode : rating_filter_mode,
      min_rating    :   min_rating,
      max_rating    :   max_rating,
    },
    success: function(response){
      jQuery('#makers-filtered-listing-section').html('');
      jQuery('#makers-filtered-listing-section').html(response);
    },
    error: function(reserr){
      console.log(reserr);
    }
  });
}


//Outlet Filter Events
jQuery(document).ready(function(){
  //search submit
  jQuery(document).on('click', '#outlet-search-btn', function(e){
      e.preventDefault();
      filterOutlet();
  });

  //price filter -- MIN and MAX change
  jQuery(document).on('input', '#outlet-price-range-section input[type="range"]', function(){
    filterOutlet();
  });

  jQuery(document).on('input', '#outlet-price-filter input[type="number"]', function(){
    filterOutlet();
  });

  //avg rating
  jQuery(document).on('input', '#outlet-avg-rating-range-section input[type="range"]', function(){
    filterOutlet();
  });

  jQuery(document).on('input', '#outlet-avg-rating-filter input[type="number"]', function(){
    filterOutlet();
  });

  jQuery(document).on('click', '#outlet-avg-rating-mode', function(){
    filterOutlet();
  });

  //listing type
  jQuery(document).on('click', 'input[name="outlet_listing_type"]', function(){
    filterOutlet();
  });
  
  jQuery(document).on('click', '#outlet-price-mode', function(){
    filterOutlet();
  });

  //date filter
  jQuery(document).on('change', '#outlet-start-date', function(){
    filterOutlet();
  });
  jQuery(document).on('change', '#outlet-end-date', function(){
    filterOutlet();
  });

  //plateforms
  jQuery(document).on('change', 'input[name="outlet_plateforms[]"]', function(){
    filterOutlet();
  });

  //Sortby Filter
  jQuery(document).on('change', '#outlets-sortby-filters', function(){
    filterOutlet();
  });

});

//Filter Outlet
function filterOutlet(){
  //search
  var search =  jQuery('#outlet-search-content').val();

  //listing type
  var listing_type = jQuery('input[name="outlet_listing_type"]:checked').val();

  //price
  if(jQuery('#outlet-price-mode').is(':checked')){
    var price_filter_mode = 'On';
  }else{
    var price_filter_mode = '';
  }
  var min_price   =   jQuery('#o-input-min-field').val();
  var max_price   =   jQuery('#o-input-max-field').val();

  //rating
  if(jQuery('#outlet-avg-rating-mode').is(':checked')){
    var rating_filter_mode = 'On';
  }else{
    var rating_filter_mode = '';
  }
  var min_rating   =   jQuery('#outlet-min-rating').val();
  var max_rating   =   jQuery('#outlet-max-rating').val();

  //date
  var start_date  =   jQuery('#outlet-start-date').val();
  var end_date    =   jQuery('#outlet-end-date').val();

  //plateforms
  var plateforms = [];
  jQuery('input[name="outlet_plateforms[]"]:checked').each(function(){
    plateforms.push(jQuery(this).val());
  });
  
  //sortby
  var sortby      =   jQuery('#outlets-sortby-filters').find(':selected').val();

  //AJAX REQUEST
  jQuery.ajax({
    url             :   ajax_object.ajax_url,
    type            :   'POST',
    data            :   {
      action        :   'filter_outlet',
      search        :   search,
      listing_type  :   listing_type,
      price_f_m     :   price_filter_mode,
      min_price     :   min_price,
      max_price     :   max_price,
      start_date    :   start_date,
      end_date      :   end_date,
      plateforms    :   plateforms,
      sortby        :   sortby,
      rating_filter_mode : rating_filter_mode,
      min_rating    :   min_rating,
      max_rating    :   max_rating,
    },
    success: function(response){
      jQuery('#outlet-filtered-listing-section').html('');
      jQuery('#outlet-filtered-listing-section').html(response);
    },
    error: function(reserr){
      console.log(reserr);
    }
  });
}

/**
 * TVADI LIKE 
 */
function singletvadiLike(postid, btn){
  if(postid != ''){
    jQuery.ajax({
      url             :   ajax_object.ajax_url,
      type            :   'POST',
      data            :   {
        action : 'process_tvadiLike',
        postid : postid,
      },
      success: function(response){
          let res         =   JSON.parse(response);
          let status      =   res.status;
          let process     =   res.process;
          if(status){
            if(process == 'like'){
              jQuery(btn).html('<img src="/wp-content/themes/tvadimarket/images/Like.png" class="img-fluid" alt="Unlike"> Remove to Wish-list');
            }else{
              jQuery(btn).html('<img src="/wp-content/themes/tvadimarket/images/wishlist.png" class="img-fluid" alt="Like"> Add to Wish-list');
            }
          }else{
            alert('Threre has been a problem, please refresh page and try again. Thanks.');
          }
      }
    });
  }
}

jQuery(document).ready(function($) {
  jQuery("#custom-single-comment-toggle").click(function() {
      jQuery("#custom-single-comment-section").toggle();
      if(jQuery('#custom-single-comment-section').is(':visible')){
        jQuery('#custom-single-comment-toggle span.custom-toggle-icon').html('<svg width="30" height="30" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M27.083 43.75L27.083 11.2792L38.1101 22.3063C38.3028 22.5033 38.5326 22.6601 38.7863 22.7677C39.04 22.8752 39.3125 22.9314 39.588 22.9329C39.8636 22.9344 40.1367 22.8813 40.3915 22.7765C40.6464 22.6717 40.8779 22.5175 41.0728 22.3226C41.2676 22.1277 41.4218 21.8962 41.5265 21.6413C41.6313 21.3864 41.6844 21.1133 41.6828 20.8378C41.6812 20.5622 41.625 20.2897 41.5174 20.036C41.4098 19.7824 41.253 19.5526 41.0559 19.3599L26.4726 4.7766C26.0819 4.38604 25.5521 4.16663 24.9997 4.16663C24.4473 4.16663 23.9174 4.38604 23.5268 4.7766L8.94343 19.3599C8.74964 19.5532 8.59596 19.7829 8.4912 20.0357C8.38645 20.2886 8.33269 20.5597 8.33301 20.8334C8.3331 21.2454 8.45533 21.6481 8.68426 21.9906C8.91318 22.3331 9.23852 22.6001 9.61915 22.7577C9.99977 22.9154 10.4186 22.9566 10.8227 22.8763C11.2267 22.7959 11.5979 22.5976 11.8893 22.3063L22.9163 11.2792L22.9163 43.75C22.9163 44.3026 23.1358 44.8325 23.5265 45.2232C23.9172 45.6139 24.4471 45.8334 24.9997 45.8334C25.5522 45.8334 26.0821 45.6139 26.4728 45.2232C26.8635 44.8325 27.083 44.3026 27.083 43.75Z" fill="white"/></svg>');
      }else{
        jQuery('#custom-single-comment-toggle span.custom-toggle-icon').html('<svg width="30" height="30" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.917 6.24996L22.917 38.7208L11.8899 27.6937C11.6972 27.4967 11.4674 27.3399 11.2137 27.2323C10.96 27.1248 10.6875 27.0686 10.412 27.0671C10.1364 27.0656 9.86332 27.1187 9.60847 27.2235C9.35361 27.3283 9.12206 27.4825 8.92724 27.6774C8.73241 27.8723 8.57817 28.1038 8.47346 28.3587C8.36875 28.6136 8.31564 28.8867 8.3172 29.1622C8.31877 29.4378 8.37498 29.7103 8.48259 29.964C8.59019 30.2176 8.74704 30.4474 8.94407 30.6401L23.5274 45.2234C23.9181 45.614 24.4479 45.8334 25.0003 45.8334C25.5527 45.8334 26.0826 45.614 26.4732 45.2234L41.0566 30.6401C41.2504 30.4468 41.404 30.2171 41.5088 29.9643C41.6136 29.7114 41.6673 29.4403 41.667 29.1666C41.6669 28.7546 41.5447 28.3519 41.3157 28.0094C41.0868 27.6669 40.7615 27.3999 40.3809 27.2423C40.0002 27.0846 39.5814 27.0434 39.1773 27.1237C38.7733 27.2041 38.4021 27.4024 38.1107 27.6937L27.0837 38.7208L27.0837 6.24996C27.0837 5.69742 26.8642 5.16752 26.4735 4.77682C26.0828 4.38612 25.5529 4.16663 25.0003 4.16663C24.4478 4.16663 23.9179 4.38612 23.5272 4.77682C23.1365 5.16752 22.917 5.69742 22.917 6.24996Z" fill="white"/></svg>');
      }
  });
});

//Market Page Action
jQuery(document).ready(function(){
  //Makers..
  jQuery(document).on('click', 'a#button-maker-listings-show', function(){
    jQuery('.box-inner.checkbox-type input[value="makers_listing"]').click();
  });

  //Outlets..
  jQuery(document).on('click', 'a#button-outlet-listings-show', function(){
    jQuery('.box-inner.checkbox-type input[value="outlet_listing"]').click();
  });

  //Auction..
  jQuery(document).on('click', 'a#button-auction-listings-show', function(){
    jQuery('.box-inner.checkbox-type input[value="auction_listing"]').click();
  });

  //Featured Listings..
  jQuery(document).on('click', 'a#button-featured-listings-show', function(){
    jQuery.ajax({
      url             :   ajax_object.ajax_url,
      type            :   'POST',
      data            :   {
        action        :   'featured_listings_process',
      },
      success: function(response){
        jQuery('#market-listings-data-section').html('');
        jQuery('#market-listings-data-section').html(response);
      },
      error: function(reserr){
        console.log(reserr);
      }
    });
  });
});

/**
 * ADVANCE SEARCH PAGE FILTER.................................................................................................................
 */
jQuery(document).ready(function(){
  //search submit
  jQuery(document).on('click', '#search-adv-listing-btn', function(e){
      e.preventDefault();
      advSearch();
  });

  //price filter -- MIN and MAX change
  jQuery(document).on('input', '#adv-price-range-section input[type="range"]', function(){
    advSearch();
  });

  jQuery(document).on('input', '#adv-price-filter input[type="number"]', function(){
    advSearch();
  });

  //avg rating
  jQuery(document).on('input', '#adv-avg-rating-range input[type="range"]', function(){
    advSearch();
  });

  jQuery(document).on('input', '#adv-avg-rating-filter input[type="number"]', function(){
    advSearch();
  });

  jQuery(document).on('click', '#adv-avg-rating-filter-mode', function(){
    advSearch();
  });

   //listing type
  jQuery(document).on('click', 'input[name="adv_search_listing_type"]', function(){
    advSearch();
  });
  
  jQuery(document).on('click', '#adv-price-filter-mode', function(){
    advSearch();
  });

  //date filter
  jQuery(document).on('change', '#adv-filter-start-date', function(){
    advSearch();
  });
  jQuery(document).on('change', '#adv-filter-end-date', function(){
    advSearch();
  });

  //plateforms
  jQuery(document).on('change', 'input[name="adv_plateforms[]"]', function(){
    advSearch();
  });

  //Sortby Filter
  jQuery(document).on('change', '#adv-sortby-filters', function(){
    advSearch();
  });
});

//Filter Makers
function advSearch(){
  //search
  var search      =   jQuery('#adv-search-content').val();

  //listing type
  var listing_type = jQuery('input[name="adv_search_listing_type"]:checked').val();

  //price
  if(jQuery('#adv-price-filter-mode').is(':checked')){
    var price_filter_mode = 'On';
  }else{
    var price_filter_mode = '';
  }
  var min_price   =   jQuery('#adv-input-min-field').val();
  var max_price   =   jQuery('#adv-input-max-field').val();

  //rating
  if(jQuery('#adv-avg-rating-filter-mode').is(':checked')){
    var rating_filter_mode = 'On';
  }else{
    var rating_filter_mode = '';
  }
  var min_rating   =   jQuery('#adv-min-rating').val();
  var max_rating   =   jQuery('#adv-max-rating').val();

  //date
  var start_date  =   jQuery('#adv-filter-start-date').val();
  var end_date    =   jQuery('#adv-filter-end-date').val();

  //plateforms
  var plateforms  =   [];
  jQuery('input[name="adv_plateforms[]"]:checked').each(function(){
    plateforms.push(jQuery(this).val());
  });
  
  //sortby
  var sortby      =   jQuery('#adv-sortby-filters').find(':selected').val();
  jQuery('.adv-main-loader').show();
  //AJAX REQUEST
  jQuery.ajax({
    url             :   ajax_object.ajax_url,
    type            :   'POST',
    data            :   {
      action        :   'adv_search',
      search        :   search,
      listing_type  :   listing_type,
      price_f_m     :   price_filter_mode,
      min_price     :   min_price,
      max_price     :   max_price,
      start_date    :   start_date,
      end_date      :   end_date,
      plateforms    :   plateforms,
      sortby        :   sortby,
      rating_filter_mode : rating_filter_mode,
      min_rating    :   min_rating,
      max_rating    :   max_rating,
    },
    success: function(response){
      jQuery('#makers-filtered-listing-section').html('');
      jQuery('#makers-filtered-listing-section').html(response);
      jQuery('.adv-main-loader').hide();
    },
    error: function(reserr){
      jQuery('.adv-main-loader').hide();
      console.log(reserr);
    }
  });
}

//Add Contact
function addContact(post_author_id, current_user_id, post_id, btn){
  if(post_author_id != '' || current_user_id != '' || post_id != ''){
    jQuery(btn).html('Please wait...');
    jQuery.ajax({
      url             :   ajax_object.ajax_url,
      type            :   'POST',
      data            :   {
        action        :   'add_contact_process',
        post_author   :   post_author_id,
        user_id       :   current_user_id,
        post_id       :   post_id,
      },
      success: function(response){
          const ress = JSON.parse(response);
          if(ress.status){
            if(ress.chat_url){
              window.location.href = ress.chat_url;
            }
          }else{
            alert(ress.message);
            location.reload();
          }
          jQuery(btn).html('Contact <img src="/wp-content/themes/tvadimarket/images/arrow.svg" class="img-fluid" alt="">');
      },
      error: function(reserr){
        console.log(reserr);
        jQuery(btn).html('Contact <img src="/wp-content/themes/tvadimarket/images/arrow.svg" class="img-fluid" alt="">');
      }
    });
  }
}