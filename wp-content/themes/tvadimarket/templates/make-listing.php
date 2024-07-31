<?php 
/*
Template Name: Make Listing
*/
if(!defined('ABSPATH')){
    exit; 
}

if(!is_user_logged_in()){
    header("Location: /how-to-make-a-listing/");
    exit();
}
get_header();

//Edit Listing Things...
$listing_id = (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) ? (int) $_REQUEST['id'] : '';
if(!empty($listing_id)){
    $listingData    =   get_post($listing_id);
    $listing_type   =   $listingData->post_type;
    $title          =   $listingData->post_title;
    $description    =   $listingData->post_content;
    $postimageurl   =   wp_get_attachment_url(get_post_thumbnail_id($listing_id), 'full');
    $uploadedimage  =   (!empty($postimageurl)) ?  $postimageurl : '';
    if(!empty($uploadedimage)){
        ?>
        <style>
            .file-wrapper:after{
                background-image: url('<?= $uploadedimage ?>');
            }
        </style>
        <?php
    }
    //Meta Fields..
    $keywords                          =   get_post_meta( $listing_id, 'post_keywords', true );
    $plateform                         =   get_post_meta( $listing_id, 'post_plateform', true );
    $timeline                          =   get_post_meta( $listing_id, 'post_timeline', true );
    $auction_listing                   =   get_post_meta( $listing_id, 'post_auction_listing', true );
    $auction_length                    =   get_post_meta( $listing_id, 'post_auction_length', true );
    $reserve_amount                    =   get_post_meta( $listing_id, 'post_reserve_amount', true );
    $price_from                        =   get_post_meta( $listing_id, 'price_from', true );
    $hourly_onetime                    =   get_post_meta( $listing_id, 'post_hourly_onetime', true );
    $currency                          =   get_post_meta( $listing_id, 'post_currency', true );
    $sku                               =   get_post_meta( $listing_id, 'post_sku', true );
    $phone                             =   get_post_meta( $listing_id, 'post_phone', true );
    $qr_code                           =   get_post_meta( $listing_id, 'post_qr_code', true );
    $url                               =   get_post_meta( $listing_id, 'post_url', true );
    $additional_listing_media          =   get_post_meta( $listing_id, 'post_additional_listing_media', true );
    $location                          =   get_post_meta( $listing_id, 'post_location', true );
    $final_product_proof_of_service    =   get_post_meta( $listing_id, 'post_final_product_proof_of_service', true );
    $playlist_id                       =   get_post_meta( $listing_id, 'post_playlist_id', true );
}
?>
    <!-- MAKE LISTING START -->
    <section class="make-listing">
        <div class="container-fluid">
            <div class="side-by-side-data">
                <div class="left">
                    <form method="POST" class="listing-form" id="make-listing-form">
                        <ul class="form-steps">
                            <li><img src="<?= get_template_directory_uri() ?>/images/arrow-right-2.png" class="img-fluid" /> Listing Type</li>
                            <li><img src="<?= get_template_directory_uri() ?>/images/arrow-right-2.png" class="img-fluid" /> Description</li>
                            <li><img src="<?= get_template_directory_uri() ?>/images/arrow-right-2.png" class="img-fluid" /> Specifications</li>
                            <li><img src="<?= get_template_directory_uri() ?>/images/arrow-right-2.png" class="img-fluid" /> Budget and Timeline</li>
                        </ul>
                        <div class="form-main">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg-6 col-xl-5">
                                    <?php 
                                    if(!empty($listing_id)){
                                        ?>
                                        <input type="hidden" name="listing_id" id="form-listing-id" value='<?= $listing_id ?>'>
                                        <?php
                                    }
                                    ?>
                                    <div class="form-group">
                                        <label>Type of Listing: Maker / Outlet / Other</label>
                                        <div class="field-gap">
                                            <select class="form-control" name="listing_type" id="listing-type">
                                                <option value=''>Choose an option</option>
                                                <option value='makers_listing' <?php if(isset($listing_type) && $listing_type == 'makers_listing') echo 'selected'; ?>>Maker</option>
                                                <option value='outlet_listing' <?php if(isset($listing_type) && $listing_type == 'outlet_listing') echo 'selected'; ?>>Outlet</option>
                                                <option value='market_listing' <?php if(isset($listing_type) && $listing_type == 'market_listing') echo 'selected'; ?>>Market</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Add Image or Tvadi Playlist:</label>
                                        <div class="field-gap">
                                            <select class="form-control" name="playlist_id" id="playlist-id">
                                                <option value=''>Choose an option</option>
                                                <?php 
                                                $args = [
                                                    'post_type'         =>      'playlist',
                                                    'post_status'       =>      'publish',
                                                    'posts_per_page'    =>      -1,
                                                    'orderby'           =>      'date',
                                                    'order'             =>      'DESC',
                                                ];
                                                $playlists = get_posts($args);
                                                if(!empty($playlists)){
                                                    foreach($playlists as $pl){
                                                        ?>
                                                        <option value="<?= $pl->ID ?>" <?php if(isset($playlist_id) && $playlist_id == $pl->ID) echo 'selected'; ?>><?= $pl->post_title ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6 col-xl-6">
                                    <div class="add-media">
                                        <div class="file-wrapper">
                                            <input type="file" name="upload_img" accept="image/*" id="listing-upload-image"/>
                                            <div class="close-btn">×</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-12">
                                    <div class="form-group">
                                        <label>Listing Title:</label>
                                        <div class="field-gap">
                                            <input type="text" name="title" id="listing-title" value="<?php if(isset($title) && !empty($title)) echo $title; ?>" class="form-control" placeholder="Examples: Customized Media Re-brand / Production Services Available / TV Ad Space" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-12">
                                    <div class="form-group">
                                        <label>Description:</label>
                                        <div class="field-gap">
                                            <textarea class="form-control" name="description" placeholder="Enter details and specifics of your listing, what are you offering?"><?php if(isset($description) && !empty($description)) echo $description; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label>Keywords:</label>
                                        <div class="field-gap">
                                            <input type="text" name="keywords" value="<?php if(isset($keywords) && !empty($keywords)) echo $keywords; ?>" class="form-control" placeholder="What form of media?  What platform of media outlet?"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label>Media / Market / Category:</label>
                                        <div class="field-gap">
                                            <select class="form-control" name="plateform" id="listing-platform">
                                                <option value=''>Choose an option</option>
                                                <?php 
                                                if(isset($listing_type) && $listing_type == 'makers_listing'){
                                                    $taxonomy_name  = 'platform';
                                                    $insertedTerm   =   wp_get_post_terms($listing_id, $taxonomy_name);
                                                    $selectedTerm   =   $insertedTerm[0]->term_id;
                                                   
                                                    $args = [
                                                        'taxonomy'      =>  $taxonomy_name,
                                                        'hide_empty'    =>  false,
                                                    ];
                                                    $terms = get_terms($args);
                                                    if(!empty($terms)){
                                                        foreach($terms as $t){
                                                            ?>
                                                            <option value="<?= $t->term_id ?>" <?php if(!empty($selectedTerm) && $selectedTerm == $t->term_id) echo 'selected'; ?>><?= $t->name ?></option>
                                                            <?php
                                                        }
                                                    }
                                                }else if(isset($listing_type) && $listing_type == 'outlet_listing'){
                                                    $taxonomy_name = 'outlet_platform';
                                                    $insertedTerm   = wp_get_post_terms($listing_id, $taxonomy_name);
                                                    $selectedTerm   =   $insertedTerm[0]->term_id;
                                                    $args = [
                                                        'taxonomy'      =>  $taxonomy_name,
                                                        'hide_empty'    =>  false,
                                                    ];
                                                    $terms = get_terms($args);
                                                    if(!empty($terms)){
                                                        foreach($terms as $t){
                                                            ?>
                                                            <option value="<?= $t->term_id ?>" <?php if(!empty($selectedTerm) && $selectedTerm == $t->term_id) echo 'selected'; ?>><?= $t->name ?></option>
                                                            <?php
                                                        }
                                                    }
                                                }else if(isset($listing_type) && $listing_type == 'market_listing'){
                                                    $taxonomy_name  =   'market_platform';
                                                    $insertedTerm   =   wp_get_post_terms($listing_id, $taxonomy_name);
                                                    $selectedTerm   =   $insertedTerm[0]->term_id;
                                                    $args = [
                                                        'taxonomy'      =>  $taxonomy_name,
                                                        'hide_empty'    =>  false,
                                                    ];
                                                    $terms  =   get_terms($args);
                                                    if(!empty($terms)){
                                                        foreach($terms as $t){
                                                            ?>
                                                            <option value="<?= $t->term_id ?>" <?php if(!empty($selectedTerm) && $selectedTerm == $t->term_id) echo 'selected'; ?>><?= $t->name ?></option>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <!-- <input type="text" name="plateform" class="form-control" placeholder="What form of media?  What platform of media outlet?"/> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-12">
                                    <div class="form-group">
                                        <label>Timeline:</label>
                                        <div class="field-gap">
                                            <input type="text" name="timeline" value="<?php if(isset($timeline) && !empty($timeline)) echo $timeline; ?>" class="form-control" placeholder="What are the dates for the project?  Any deadlines?"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label>Auction (optional):</label>
                                        <div class="field-gap">
                                            <select class="form-control" name="auction_listing">
                                                <option value=''>Choose an option</option>
                                                <option value='Yes' <?php if(isset($auction_listing) && $auction_listing == 'Yes') echo 'selected'; ?>>Auction Listing</option>
                                                <option value='No' <?php if(isset($auction_listing) && $auction_listing == 'No') echo 'selected'; ?>>None</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label>Auction Length:</label>
                                        <div class="field-gap">
                                            <select class="form-control" name="auction_length">
                                                <option value=''>Choose an option</option>
                                                <option value='7' <?php if(isset($auction_length) && $auction_length == '7') echo 'selected'; ?>>7 Days</option>
                                                <option value='14' <?php if(isset($auction_length) && $auction_length == '14') echo 'selected'; ?>>14 Days</option>
                                                <option value='21' <?php if(isset($auction_length) && $auction_length == '21') echo 'selected'; ?>>21 Days</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label>Reserve:</label>
                                        <div class="field-gap">
                                            <input type="text" name="reserve_amount" value="<?php if(isset($reserve_amount) && !empty($reserve_amount)) echo $reserve_amount; ?>" class="form-control" placeholder="$ Enter amount" />
                                        </div>
                                    
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label>Budget:</label>
                                        <div class="field-gap">
                                            <input type="text" name="budget" class="form-control" value="<?php if(isset($price_from) && !empty($price_from)) echo $price_from; ?>" placeholder="$ Cost / Per >" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label>Hourly / One Time:</label>
                                        <div class="field-gap">
                                            <select class="form-control" name="hourly_onetime">
                                                <option value=''>Choose an option</option>
                                                <option value='Hourly' <?php if(isset($hourly_onetime) && $hourly_onetime == 'Hourly') echo 'selected'; ?>>Hourly</option>
                                                <option value='One Time' <?php if(isset($hourly_onetime) && $hourly_onetime == 'One Time') echo 'selected'; ?>>One Time</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label>Currency :</label>
                                        <div class="field-gap">
                                            <select class="form-control" name="currency">
                                                <option value=''>Choose an option</option>
                                                <option value='USD' <?php if(isset($currency) && $currency == 'USD') echo 'selected'; ?>>USD</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-12">
                                    <div class="form-group">
                                        <label>Tracking Number / SKU / ISCI</label>
                                        <div class="field-gap">
                                            <input type="text" name="sku" class="form-control" value="<?php if(isset($sku) && !empty($sku)) echo $sku; ?>" placeholder="Enter a custom tracking number otherwise a generated one will be given." />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label>Phone:</label>
                                        <div class="field-gap">
                                            <input type="text" name="phone" class="form-control" value="<?php if(isset($phone) && !empty($phone)) echo $phone; ?>" placeholder="Enter a call to action phone number (optional)." />
                                        </div>
                                    
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label>QR Code:</label>
                                        <div class="field-gap">
                                            <input type="text" name="qr_code" class="form-control" placeholder="QR code automatically generated for ever listing." value="<?php if(isset($qr_code) && !empty($qr_code)) echo $qr_code; ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label>URL:</label>
                                        <div class="field-gap">
                                            <input type="text" name="url" value="<?php if(isset($url) && !empty($url)) echo $url; ?>" class="form-control" placeholder="Enter a call to action URL (optional)." />
                                        </div>
                                    
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <label>Additional Listing Media:</label>
                                        <div class="field-gap">
                                            <input type="text" name="additional_listing_media" value="<?php if(isset($additional_listing_media) && !empty($additional_listing_media)) echo $additional_listing_media; ?>" class="form-control" placeholder="Paste image or video URLs, up to 4 separated with a comma (optional)." />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label>Location:</label>
                                        <div class="field-gap">
                                            <input type="text" name="location" value="<?php if(isset($location) && !empty($location)) echo $location; ?>" class="form-control" placeholder="Add zip codes, place, market (optional for makers)."/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label>Final Product / Proof of Service Rendered:</label>
                                        <div class="field-gap">
                                            <input type="text" name="final_product_proof_of_service" value="<?php if(isset($final_product_proof_of_service) && !empty($final_product_proof_of_service)) echo $final_product_proof_of_service; ?>" class="form-control" placeholder="Resulting media / Proof of delivery"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-12">
                                    <?php 
                                    if(empty($listing_id)){
                                        ?>
                                        <div class="text-end review"><button  class="btn btn-primary btn-large" id="review-make-listing">Review <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="Make Listing" /></button></div>
                                        <?php
                                    }else{
                                        ?>
                                        <div class="text-end review"><button  class="btn btn-primary btn-large" id="update-make-listing-process">Update <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="Make Listing" /></button></div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-12 col-lg-12" id="listing-message">
                                    <div class="l-success" style="color:green; display:none;"></div>
                                    <div class="l-error" style="color:red; display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="right listing-help">
                    <h2 class="heading">Listing Help</h2>
                    <?php 
                    $help_playlist_args = [
                        'post_type'         =>  'playlist',
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  2,
                        'orderby'           =>  'date',
                        'order'             =>  'DESC'
                    ];
                    $helplaylists = get_posts($help_playlist_args);
                    ?>
                    <div class="listing-help-wrapper">
                        <?php 
                        if(!empty($helplaylists)){
                            foreach($helplaylists as $hp){
                                $hpID               =   $hp->ID;
                                $hpimageurl         =   wp_get_attachment_url(get_post_thumbnail_id($hpID));
                                $views              =   (!empty(get_post_meta($hpID, 'views', true))) ? get_post_meta($hpID, 'views', true) : 0;
                                $calculatedViews    =   TvadiFrontEnd::calculatePlaylistviews($views);
                                ?>
                                <div class="listing-help-item hover-image">
                                    <div class="single-img "><a href="/channels/?type=p&id=<?= $hpID ?>"><img src="<?= $hpimageurl ?>" class="img-fluid" alt="<?= ucfirst($hp->post_title) ?>"></a></div>
                                    <h3><a href="/channels/?type=p&id=<?= $hpID ?>"><?= ucfirst($hp->post_title) ?></a></h3>
                                    <div class="box-data">
                                        <?php 
                                        if(is_user_logged_in()){
                                            $current_user_id    =   get_current_user_id();
                                            $likesData          =   get_user_meta($current_user_id, 'user_wishlist_detail', true);
                                            $likesDataArray     =   (!empty($likesData) && is_array($likesData)) ? (array) $likesData : [];
                                            if(!empty($likesDataArray) && in_array($hpID, $likesDataArray)){
                                                ?>
                                                <button type="button" id="logged-wishlist-btn" onclick="tvadiLike(<?= $hpID ?>, this);"class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike" /></button>
                                                <?php
                                            }else{
                                                ?>
                                                <button type="button" id="logged-wishlist-btn" onclick="tvadiLike(<?= $hpID ?>, this);"class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like" /></button>
                                                <?php
                                            }
                                        }else{
                                            ?>
                                            <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like" /></button>
                                            <?php
                                        }
                                        ?>
                                        <button type="button" class="view"><img src="<?= get_template_directory_uri() ?>/images/view.png" class="img-fluid" alt=""></button>
                                        <span class="comments">± <?= $calculatedViews ?></span>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- MAKE LISTING END -->

    <!-- GUIDES TO START -->
    <section class="guides">
        <div class="container-fluid">
            <div class="global-heading">
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <h2 class="title">Guides to: Makers, Outlets, Market, Tools</h2>
                    </div>
                </div>
            </div>
            <div class="guide-wrapper">
                <div class="row">
                    <div class="col-12 col-md-6 col-xl-3">
                        <a href="/makers/" class="guide-block">
                            <div class="block-img"><img src="<?= get_template_directory_uri() ?>/images/guide-1.jpg" class="img-fluid" alt="" /></div>
                            <h4 class="title">Makers Lab ></h4>
                        </a>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <a href="/outlet/" class="guide-block">
                            <div class="block-img"><img src="<?= get_template_directory_uri() ?>/images/guide-2.jpg" class="img-fluid" alt="" /></div>
                            <h4 class="title">Outlet Monetization ></h4>
                        </a>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <a href="/market/" class="guide-block">
                            <div class="block-img"><img src="<?= get_template_directory_uri() ?>/images/guide-3.jpg" class="img-fluid" alt="" /></div>
                            <h4 class="title">Media Market How To ></h4>
                        </a>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3">
                        <a href="/tools/" class="guide-block">
                            <div class="block-img"><img src="<?= get_template_directory_uri() ?>/images/guide-4.jpg" class="img-fluid" alt="" /></div>
                            <h4 class="title">Tools ></h4>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- GUIDES TO END -->
<?php
get_footer();