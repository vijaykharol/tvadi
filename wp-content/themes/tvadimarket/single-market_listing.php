<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package tvadimarket
*/

get_header();
$stylesheet_directory = get_stylesheet_directory();
require_once get_stylesheet_directory() . '/php-qrcode/vendor/autoload.php';
// Import the necessary classes
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Function to generate and display a QR code
 */
function generate_qr_code($data){
    // Set QR code options
    $options = new QROptions([
        'version'    => 5,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => QRCode::ECC_L,
        'scale'      => 5,
    ]);
    
    // Create a new QR code instance
    $qrcode = new QRCode($options);
    
    // Generate the QR code as a string
    $imageString = $qrcode->render($data);

    // Output the QR code image
    header('Content-Type: image/png');
    echo $imageString;
}

if(have_posts()) :
    while(have_posts()) : the_post(); 
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <!-- LISTING DETAILS START -->
            <?php 
            $postid                                 =   get_the_ID();
            $post_author_id                         =   get_post_field( 'post_author', $postid );
            $author_data                            =   get_userdata($post_author_id);
            $current_user_id                        =   get_current_user_id();
            $post_keywords                          =   get_post_meta( $postid, 'post_keywords', true );
            $post_plateform                         =   get_post_meta( $postid, 'post_plateform', true );
            $post_timeline                          =   get_post_meta( $postid, 'post_timeline', true );
            $post_auction_listing                   =   get_post_meta( $postid, 'post_auction_listing', true );
            $post_auction_length                    =   get_post_meta( $postid, 'post_auction_length', true );
            $post_reserve_amount                    =   get_post_meta( $postid, 'post_reserve_amount', true );
            $price_from                             =   (int)get_post_meta( $postid, 'price_from', true );
            $post_hourly_onetime                    =   get_post_meta( $postid, 'post_hourly_onetime', true );
            $post_currency                          =   get_post_meta( $postid, 'post_currency', true );
            $post_sku                               =   get_post_meta( $postid, 'post_sku', true );
            $post_phone                             =   get_post_meta( $postid, 'post_phone', true );
            $post_qr_code                           =   get_post_meta( $postid, 'post_qr_code', true );
            $post_url                               =   get_post_meta( $postid, 'post_url', true );
            $post_additional_listing_media          =   get_post_meta( $postid, 'post_additional_listing_media', true );
            $post_location                          =   get_post_meta( $postid, 'post_location', true );
            $post_final_product_proof_of_service    =   get_post_meta( $postid, 'post_final_product_proof_of_service', true );
            $post_playlist_id                       =   get_post_meta( $postid, 'post_playlist_id', true );
            $total_ratings                          =   get_post_meta( $postid, 'total_ratings', true );
            $average_rating                         =   get_post_meta( $postid, 'average_rating', true );
            $subtitle                               =   get_post_meta( $postid, 'subtitle', true );
            $timelinenum                            =   (!empty($post_timeline)) ? (int) $post_timeline : 0;
            $duestring                              =   '+'.$timelinenum.' days';
            $duedate                                =   date('d/m/Y', strtotime((string)$duestring));
            $postimageurl                           =   wp_get_attachment_url(get_post_thumbnail_id($postid), 'full');

            //generate Qr code
            $QRdata                                 =   (!empty($post_qr_code)) ? $post_qr_code : get_the_title();
            ?>
            <!-- OUTLET LISITNG START -->
            <section class="listing-details pb-0">
                <div class="container-fluid">
                    <div class="data-wrapper">
                        <div class="data-wrapper-left">
                            <div class="data-wrapper-left-inner">
                                <div class="image"><img src="<?= $postimageurl ?>" class="img-fluid" alt="" /></div>
                                <div class="content">
                                    <div class="content-inner">
                                        <h2 class="title"><?= get_the_title() ?></h2>
                                        <?php
                                        if(!empty($subtitle)){
                                            ?>
                                            <span class="subtitle"><?= $subtitle ?></span>
                                            <?php
                                        }
                                        ?>
                                        <div class="rating-comment">
                                            <?php 
                                            if(!empty($average_rating)){
                                                ?>
                                                <span class="rating"><?= $average_rating ?> <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
                                                <?php
                                            }
                                            if(!empty($total_ratings)){
                                                ?>
                                                <span class="comments">(<?= $total_ratings ?>)</span>
                                                <?php
                                            } 
                                            ?>
                                        </div>
                                        <div class="profileinfo">
                                            <?php 
                                            $profile_picture    =   get_user_meta($post_author_id, 'profile_picture', true);
                                            ?>
                                            <span class="img">
                                            <?php 
                                            if($profile_picture){
                                                echo '<img src="'.esc_url($profile_picture).'" class="img-fluid" alt="Profile Picture">';
                                            }else{
                                                echo '<img src="'.get_avatar_url($post_author_id).'" class="img-fluid" alt="Profile Picture">';
                                            }
                                            ?>
                                            </span>
                                            <span class="from"><?= ucfirst($author_data->display_name) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="basic-content">
                                <h3>Basics:</h3>
                                <div class="description-listing">
                                    <?= get_the_content() ?>
                                </div>
                            </div>
                        </div>
                        <div class="data-wrapper-right">
                            <form>
                                <div class="price">From $20</div>
                                <div class="form-group">
                                    <label>Type:</label>
                                    <select class="form-control rounded-type">
                                        <option>Project</option>
                                        <option>Hourly</option>
                                    </select>
                                </div>
                                <!-- <div class="form-group">
                                    <label>Method:</label>
                                    <select class="form-control rounded-type">
                                        <option>Project</option>
                                        <option>Hourly</option>
                                    </select>
                                </div> -->
                                <p>Disclaimer:  All buys are subject to sellers approval and verification.  Please check with the seller to verify cost and work time-frame.  </p>
                                <div class="contact-btn"><a href="#" class="btn btn-secondary btn-large">Contact <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a></div>
                                <div class="order-btn"><a href="#" class="btn btn-primary btn-large">Order <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a></div>
                                <div class="form-list">
                                    <?php 
                                    if(is_user_logged_in()){
                                        $likesData      =   get_user_meta($current_user_id, 'user_wishlist_detail', true);
                                        $likesDataArray =   (!empty($likesData) && is_array($likesData)) ? (array) $likesData : [];
                                        if(!empty($likesDataArray) && in_array($postid, $likesDataArray)){
                                            ?>
                                            <a onclick="singletvadiLike(<?= $postid ?>, this);" id="logged-wishlist-btn" class="add-wishlist"><img src="<?= get_stylesheet_directory_uri() ?>/images/Like.png" class="img-fluid" alt=""/> Remove to Wish-list</a>
                                            <?php
                                        }else{
                                            ?>
                                            <a onclick="singletvadiLike(<?= $postid ?>, this);" id="logged-wishlist-btn" class="add-wishlist"><img src="<?= get_stylesheet_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt=""/> Add to Wish-list</a>
                                            <?php
                                        }
                                    }else{
                                        ?>
                                        <a class="add-wishlist" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal"><img src="<?= get_stylesheet_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt=""/> Add to Wish-list</a>
                                        <?php
                                    }
                                    ?>
                                    <a href="#" class="share"><img src="<?= get_stylesheet_directory_uri() ?>/images/share-square.png" class="img-fluid" alt="" /> Share</a>
                                    <a href="#" class="report-d"><img src="<?= get_stylesheet_directory_uri() ?>/images/report.png" class="img-fluid" alt="" /> Report</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="about-seller">
                        <div class="seller-inner">
                            <div class="global-heading">
                                <div class="row">
                                    <div class="col-12 col-lg-12">
                                        <h2 class="title">About Seller:</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="seller-info">
                                <div class="profil-pic">
                                    <?php 
                                    if($profile_picture){
                                        echo '<img src="'.esc_url($profile_picture).'" class="img-fluid" alt="Profile Picture">';
                                    }else{
                                        echo '<img src="'.get_avatar_url($post_author_id).'" class="img-fluid" alt="Profile Picture">';
                                    }
                                    ?>
                                </div>
                                <div class="content">
                                    <span class="from"><?= ucfirst($author_data->display_name) ?></span>
                                    <div class="rating-contact">
                                        <div class="rating-comment">
                                            <span class="rating">5 <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
                                            <span class="comments">(288)</span>
                                        </div>
                                        <div class="contact-btn mb-0"><a href="#" class="btn btn-secondary btn-large">Contact </a></div>
                                    </div>
                                </div>
                            </div>
                            <div class="about-content">
                                <?php 
                                $profile_info       =   get_user_meta($author_data->ID, 'user_profile_info', true);
                                echo $profile_info;
                                ?>
                            </div>
                        </div>
                        <div class="member-with-lang">
                            <?php 
                            $registration_date  = $author_data->user_registered;
                            // Extract the year from the registration date
                            $joining_year       = date('Y', strtotime($registration_date));
                            ?>
                            <div class="member">Member Since: <span><?= $joining_year ?></span></div>
                            <div class="member">Languages: English, French</div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- OUTLET LISITNG END -->

            <!-- SIMILAR LISTING START -->
            <section class="similar-listing bg-dark">
                <div class="container-fluid">
                    <div class="global-heading">
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <h2 class="title heading-gap">Similar  Listings:</h2>
                            </div>
                        </div>
                    </div>
                    <div class="similar-listing-wrapper">
                        <div class="post-gap d-grid grid-3">
                            <?php 
                             $arguements1 = [
                                'post_type'         =>  'market_listing',
                                'post_status'       =>  'publish',
                                'posts_per_page'    =>  3,
                                'author'            =>  $post_author_id,
                                'orderby'           =>  'date',
                                'order'             =>  'DESC',
                            ];
                            $listings1       =  get_posts($arguements1);
                            $listingidArray   =  [];
                            if(!empty($listings1)){
                                foreach($listings1 as $lis1){
                                    $listingidArray[]    =   $lis1->ID;
                                    $listimg            =   wp_get_attachment_url(get_post_thumbnail_id($lis1->ID), 'full');
                                    ?>
                                    <a href="<?= get_permalink($lis1->ID) ?>" class="post-item hover-image">
                                        <h3><?= ucfirst($lis1->post_title) ?></h3>
                                        <div class="post-img"><img src="<?= $listimg ?>" class="img-fluid" alt="<?= ucfirst($lis1->post_title) ?>" /></div>
                                    </a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <div class="more mt-3 text-end"><a href="/outlet/" class="btn btn-secondary btn-small">Discover All</a></div>
                    </div>
                </div>
            </section>
            <!-- SIMILAR LISTING END -->

            <!-- OUTLET AUCTION START -->
            <section class="outlet-auction">
                <div class="container-fluid">
                    <div class="global-heading">
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <h2 class="title heading-gap">Outlet Auctions:</h2>
                            </div>
                        </div>
                    </div>
                    <div class="outlet-auction-wrapper">
                        <div class="post-gap d-grid grid-3">
                            <?php 
                            $arguements2 = [
                                'post_type'         =>  'market_listing',
                                'post_status'       =>  'publish',
                                'posts_per_page'    =>  3,
                                'orderby'           =>  'date',
                                'order'             =>  'DESC',
                                'post__not_in'      =>  $listingidArray,
                            ];
                            $listings2 = get_posts($arguements2);
                            if(!empty($listings2)){
                                foreach($listings2 as $lis2){
                                    $listingidArray[]    =   $lis2->ID;
                                    $listimg2             =   wp_get_attachment_url(get_post_thumbnail_id($lis2->ID), 'full');
                                    ?>
                                    <a href="<?= get_permalink($lis2->ID) ?>" class="post-item hover-image">
                                        <h3><?= ucfirst($lis2->post_title) ?></h3>
                                        <div class="post-img"><img src="<?= $listimg2 ?>" class="img-fluid" alt="<?= ucfirst($lis2->post_title) ?>" /></div>
                                    </a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <div class="more mt-3 text-end"><a href="/outlet/" class="btn btn-secondary btn-small">Discover All</a></div>
                    </div>
                </div>
            </section>
            <!-- OUTLET AUCTION END -->

            <!-- VIEWING HISTORY START -->
            <section class="viewing-history bg-dark">
                <div class="container-fluid">
                    <div class="global-heading">
                        <div class="row">
                            <div class="col-12 col-lg-12">
                                <h2 class="title heading-gap">Viewing History:</h2>
                            </div>
                        </div>
                    </div>
                    <div class="more-listing-wrapper">
                        <div class="post-gap d-grid grid-3">
                            <?php 
                            $arguements3 = [
                                'post_type'         =>  'market_listing',
                                'post_status'       =>  'publish',
                                'posts_per_page'    =>  3,
                                'orderby'           =>  'date',
                                'order'             =>  'DESC',
                                'post__not_in'      =>  $listingidArray,
                            ];
                            $listings3 = get_posts($arguements3);
                            if(!empty($listings3)){
                                foreach($listings3 as $lis3){
                                    $listingidArray[]  =   $lis3->ID;
                                    $listimg3          =   wp_get_attachment_url(get_post_thumbnail_id($lis3->ID), 'full');
                                    ?>
                                    <a href="<?= get_permalink($lis3->ID) ?>" class="post-item hover-image">
                                        <h3><?= ucfirst($lis2->post_title) ?></h3>
                                        <div class="post-img"><img src="<?= $listimg3 ?>" class="img-fluid" alt="<?= ucfirst($lis2->post_title) ?>" /></div>
                                    </a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <div class="more mt-3 text-end"><a href="/outlet/" class="btn btn-secondary btn-small">Discover All</a></div>
                    </div>
                </div>
            </section>
            <!-- VIEWING HISTORY END -->

        </article><!-- #post-<?php the_ID(); ?> -->
        <?php
        // If comments are open or we have at least one comment, load up the comment template.
        // if ( comments_open() || get_comments_number() ) :
        //     comments_template();
        // endif;
    endwhile;
else :
    get_template_part( 'template-parts/content', 'none' );
endif;
get_footer();