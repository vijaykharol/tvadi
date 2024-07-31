<?php
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
            $current_user_id                        =   get_current_user_id();
            $post_keywords                          =   get_post_meta( $postid, 'post_keywords', true );
            $post_plateform                         =   get_post_meta( $postid, 'post_plateform', true );
            $post_timeline                          =   get_post_meta( $postid, 'post_timeline', true );
            $post_auction_listing                   =   get_post_meta( $postid, 'post_auction_listing', true );
            $post_auction_length                    =   get_post_meta( $postid, 'post_auction_length', true );
            $post_reserve_amount                    =   get_post_meta( $postid, 'post_reserve_amount', true );
            $price_from                             =   get_post_meta( $postid, 'price_from', true );
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
            $timelinenum                            =   (!empty($post_timeline)) ? (int) $post_timeline : 0;
            $duestring                              =   '+'.$timelinenum.' days';
            $duedate                                =   date('d/m/Y', strtotime((string)$duestring));
            $postimageurl                           =   wp_get_attachment_url(get_post_thumbnail_id($postid), 'full');

            //generate Qr code
            $QRdata                                 =   (!empty($post_qr_code)) ? $post_qr_code : get_the_title();
            ?>
            <section class="make-listing ld-block">
                <div class="container-fluid">
                    <div class="listing-form">
                        <ul class="form-steps mb-40">
                            <li><img src="<?= get_stylesheet_directory_uri() ?>/images/arrow-right-2.png" class="img-fluid" /> Updates</li>
                            <li class="active"><img src="<?= get_stylesheet_directory_uri() ?>/images/arrow-right-2.png" class="img-fluid" /> Details & Requirements</li>
                            <li><img src="<?= get_stylesheet_directory_uri() ?>/images/arrow-right-2.png" class="img-fluid" /> Fulfillment</li>
                        </ul>
                    </div>
                    <div class="side-by-side-data">
                        <div class="left">
                            <div class="semi-columns-content-block">
                                <h4 class="title">Description</h4>
                                <div class="content-inner">
                                    <div class="description">
                                        <?= the_content(); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">Keywords</h4>
                                <div class="content-inner"><?= $post_keywords ?></div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">Media / Market / Category</h4>
                                <div class="content-inner"><?= $post_plateform ?></div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">Time-line</h4>
                                <div class="content-inner"><?= $post_timeline ?></div>
                            </div>
                            <?php 
                            if(!empty($price_from)){
                                ?>
                                <div class="semi-columns-content-block">
                                    <h4 class="title">Budget</h4>
                                    <div class="content-inner">$<?= $price_from ?></div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="semi-columns-content-block">
                                <h4 class="title">Reference Number</h4>
                                <div class="content-inner"><?= $post_phone ?></div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">Phone</h4>
                                <div class="content-inner"><a href="<?= $post_phone ?>"><?= $post_phone ?></a></div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">QR Code</h4>
                                <div class="content-inner"><a href=""><?= $post_qr_code ?></a></div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">URL</h4>
                                <div class="content-inner"><a href=""><?= $post_url ?></a></div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">Additional Media</h4>
                                <div class="content-inner"><a href=""><?= $post_additional_listing_media ?></a></div>
                            </div>
                            <div class="semi-columns-content-block">
                                <h4 class="title">Additional Notes</h4>
                                <div class="content-inner"><?= $post_final_product_proof_of_service ?></div>
                            </div>
                            <?php 
                            if($current_user_id == $post_author_id){
                                ?>
                                <div class="text-end"><a href="/make-listing/?id=<?= $postid ?>" class="btn btn-primary btn-large">Update <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""></a></div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="right basic">
                            <div class="block-inner">
                                <h3>Basics</h3>
                                <div class="discounted">
                                    <h4><?= get_the_title() ?></h4>
                                    <div class="basic-img"><img src="<?= $postimageurl ?>" class="img-fluid" alt="<?= get_the_title() ?>" /></div>
                                    <?php 
                                    // Retrieve the post content
                                    $content            =   get_the_content();
                                    // Strip shortcodes and tags (optional, based on your needs)
                                    $content            =   strip_shortcodes($content);
                                    $content            =   wp_strip_all_tags($content);
                                    // Split the content into an array of words
                                    $words              =   explode(' ', $content);
                                    // Take the first 6 words
                                    $first_six_words    =   array_slice($words, 0, 5);
                                    // Convert the array of words back to a string
                                    $excerpt            =   implode(' ', $first_six_words);
                                    // Append '..' to the excerpt
                                    $excerpt            .=  '..';
                                    ?>
                                    <p><?= $excerpt ?></p>
                                    <ul>
                                        <li class="order-with">Order With:  Venture Ad Agency </li>
                                        <li class="due-date">Due Date: <?= $duedate ?> </li>
                                        <li class="cost">Cost:  $<?= $price_from ?> </li>
                                        <li class="refrence-number">Reference Number:  <?= $post_phone ?></li>
                                        <li class="qr-code mb-0 p-0"><span>Listing QR Code:</span>
                                            <div class="qr-code-block">
                                                <img src="<?= generate_qr_code($QRdata); ?>" class="img-fluid" alt="" />
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <?php 
                            if($current_user_id == $post_author_id){
                                ?>
                                <div class="text-end"><a href="/make-listing/?id=<?= $postid ?>" class="btn btn-primary btn-large">Update <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a></div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </section>
            <!-- LISTING DETAILS END -->

            <!-- FORM MAIN START -->
            <section class="contact-buyer">
                <div class="container-fluid">
                    <div class="side-by-side-data">
                        <div class="left">
                            <form>
                                <div class="form-main">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Contact Buyer:</label>
                                                <div class="field-gap">
                                                    <textarea class="form-control" placeholder="Add message within this oder"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
            <!-- FORM MAIN END -->

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