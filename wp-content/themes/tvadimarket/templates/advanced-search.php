<?php
/*
 * Template Name: Advanced Search 
 */

 if(!defined('ABSPATH')){
    exit; 
}

get_header();
?>
<!-- MAIN START -->
<section class="innerpage-banner">
    <div class="container-fluid">
        <div class="row">
            <h1>Advanced Search</h1>
            <div class="col-12 col-xl-9">
                <?php 
                echo do_shortcode('[wpgmza id="1"]');
                ?>
            </div>
            <div class="col-12 col-xl-3">
                <div class="trending-right-side" id="adv-search-trending-right">
                    <div class="side-top box">
                        <h4 class="title">Listing Info</h4>
                        <div class="title" class="text-end"><button class="btn btn-primary btn-large"><a href="/market/">More <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="Make"></a></button></div>
                    </div>
                    <div class="trending-block-wrapper">
                        <?php 
                        $outletplaylists_args = [
                            'post_type'         =>      ['makers_listing'],
                            'post_status'       =>      'publish',
                            'posts_per_page'    =>      1,
                            'orderby'           =>      'rand',
                        ];
                        $outlet_playlists = get_posts($outletplaylists_args);
                        if(!empty($outlet_playlists)){
                            foreach($outlet_playlists as $op){
                                $opID               =   $op->ID;
                                $opimageurl         =   wp_get_attachment_url(get_post_thumbnail_id($opID));
                                $price_from         =   get_post_meta($opID, 'price_from', true);
                                $total_ratings      =   get_post_meta($opID, 'total_ratings', true);
                                $average_rating     =   get_post_meta($opID, 'average_rating', true);
                                $post_location      =   strip_tags(get_post_meta($opID, 'post_location', true));
                                $platforms          =   wp_get_post_terms($opID, 'platform');
                                ?>
                                <div class="trending-block-item hover-image box">
                                    <div class="single-img radius-15 overflow-hidden " id="adv-side-single-img">
                                        <div class="imgblock"><a href="/channels/?type=p&id=<?= $opID ?>"><img src="<?= $opimageurl ?>" class="img-fluid" alt="<?= ucfirst($op->post_title) ?>"></a></div>
                                        <div class="priceblock">
                                            <div class="inner-content">
                                                <div class="price">
                                                    From $<?= $price_from ?>
                                                </div>
                                                <div class="rating-comment">
                                                    <span class="rating"><?= $average_rating ?> <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
                                                    <span class="comments">(<?= $total_ratings ?>)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box">
                                        <h4 class="title"><a href="/channels/?type=p&id=<?= $opID ?>"><?= ucfirst($op->post_title) ?></a></h4>
                                    </div>
                                    <div class="box-data box"><?= substr(strip_tags($op->post_content), 0, 80).'..' ?></div>
                                    <?php 
                                    if(!empty($post_location)){
                                        ?>
                                        <div class="box">
                                            <span>Location</span>
                                            <span><?= $post_location ?></span>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- MAIN END  -->
 
<!-- ADVANCE SEARCH SECTION -->
<section class="maker-listing" id="advance-search-section">
    <div class="container-fluid">
        <div class="filter-topbar">
            <form method="POST" class="align-items-center" id="maker-filter-form">
                <div class="search-form search-large">
                    <input class="form-control" type="search" name="search" id="adv-search-content" placeholder="Advanced Search" aria-label="Search">
                    <button type="submit" class="btn btn-search" id="search-adv-listing-btn"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 461.516 461.516" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M185.746 371.332a185.294 185.294 0 0 0 113.866-39.11L422.39 455c9.172 8.858 23.787 8.604 32.645-.568 8.641-8.947 8.641-23.131 0-32.077L332.257 299.577c62.899-80.968 48.252-197.595-32.716-260.494S101.947-9.169 39.048 71.799-9.204 269.394 71.764 332.293a185.64 185.64 0 0 0 113.982 39.039zM87.095 87.059c54.484-54.485 142.82-54.486 197.305-.002s54.486 142.82.002 197.305-142.82 54.486-197.305.002l-.002-.002c-54.484-54.087-54.805-142.101-.718-196.585l.718-.718z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg></button>
                </div>
                <div class="right-side">
                    <div class="list-grid">
                        <button type="button" class="btn list-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/list.png" class="img-fluid" alt="" /></button>
                        <button type="button" class="btn grid-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/dots.png" class="img-fluid" alt="" /></button>
                    </div>
                    <div class="sort-by">
                        <select class="form-control" name="sortby" id="adv-sortby-filters">
                            <option value="" selected="selected">Default sorting</option>
                            <option value="popularity">Sort by popularity</option>
                            <option value="date_desc">Sort by latest</option>
                            <option value="date_asc">Sort by oldest</option>
                            <option value="price_asc">Sort by price: low to high</option>
                            <option value="price_desc">Sort by price: high to low</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="adv-content-section">
            <div class="adv-main-loader" style="display:none; text-align:center;">
                <img src="<?= get_stylesheet_directory_uri() ?>/images/content-loader.gif" alt="Loader" width="100" height="100" class="img-loader">
            </div>
            <div class="filter-with-list">
                <div class="filter-sidebar">
                    <div class="sidebar-box">
                        <h5>Listing Type</h5>
                        <div class="box-inner checkbox-type">
                            <label>
                                <input type="radio" name="adv_search_listing_type" value="makers_listing"/>
                                <span class="title">Makers</span>
                            </label>
                            <label>
                                <input type="radio" name="adv_search_listing_type" value="outlet_listing"/>
                                <span class="title">Outlets</span>
                            </label>
                            <label>
                                <input type="radio" name="adv_search_listing_type" value="market_listing"/>
                                <span class="title">Market</span>
                            </label>
                            <label>
                                <input type="radio" name="adv_search_listing_type" value="auction_listing"/>
                                <span class="title">Auction</span>
                            </label>
                        </div>
                    </div>
                    <div class="sidebar-box">
                        <h5>Price Range</h5>
                        <div class="box-inner checkbox-type">
                            <label>
                                <input type="checkbox" name="price_filter_status" id="adv-price-filter-mode" value="1"/>
                                <span class="title">Filter On</span>
                            </label>
                            <div class="d-flex">
                                <div class="wrapper price-range-container">
                                    <div class="price-input" id="adv-price-filter">
                                        <div class="field">
                                            <span>Min</span>
                                            <input type="number" class="input-min" id="adv-input-min-field" value="0" name="price_min">
                                        </div>
                                        <div class="separator">-</div>
                                        <div class="field">
                                            <span>Max</span>
                                            <input type="number" class="input-max" id="adv-input-max-field" value="1000" name="price_max">
                                        </div>
                                    </div>
                                    <div class="slider">
                                        <div class="progress"></div>
                                    </div>
                                    <div class="range-input" id="adv-price-range-section">
                                        <input type="range" class="range-min" min="0" max="1000" value="0" step="10"/>
                                        <input type="range" class="range-max" min="0" max="1000" value="1000" step="10"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $plateform_args = [
                        'taxonomy'      =>  'platform',
                        'hide_empty'    =>   false,
                        'orderby'       =>  'id',
                        'order'         =>  'DESC', 
                    ];
                    $plateforms = get_terms($plateform_args);
                    if(!empty($plateforms)){
                        ?>
                        <div class="sidebar-box">
                            <h5>Platform</h5>
                            <div class="box-inner checkbox-type">
                                <?php 
                                foreach($plateforms as $p){
                                    ?>
                                    <label>
                                        <input type="checkbox" name="adv_plateforms[]" value="<?= $p->term_id ?>"/>
                                        <span class="title"><?= $p->name ?></span>
                                    </label>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="sidebar-box">
                        <h5>Time Preference</h5>
                        <div class="form-group">
                            <input type="date" name="filter" placeholder="Start date" id="adv-filter-start-date" name="start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <input type="date" name="filter" placeholder="End date / Open" id="adv-filter-end-date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="sidebar-box">
                        <h5>Average Rating</h5>
                        <div class="box-inner checkbox-type">
                            <label>
                            <input type="checkbox" name="avgrating_filter_status" id="adv-avg-rating-filter-mode" value="1"/>
                                <span class="title">Filter On</span>
                            </label>
                            <div class="d-flex">
                                <div class="wrapper rating-range-container">
                                    <div class="price-input rating-input" id="adv-avg-rating-filter">
                                        <div class="field">
                                            <span>Min</span>
                                            <input type="number" class="input-min" id="adv-min-rating" value="0" step="0.1" min="0" max="5">
                                        </div>
                                        <div class="separator">-</div>
                                        <div class="field">
                                            <span>Max</span>
                                            <input type="number" class="input-max" id="adv-max-rating" value="5" step="0.1" min="0" max="5">
                                        </div>
                                    </div>
                                    <div class="slider">
                                        <div class="progress"></div>
                                    </div>
                                    <div class="range-input" id="adv-avg-rating-range">
                                        <input type="range" class="range-min" min="0" max="5" value="0" step="0.1">
                                        <input type="range" class="range-max" min="0" max="5" value="5" step="0.1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-inner checkbox-type">
                            <label>
                                <input type="checkbox" name="" />
                                <span class="title">Auction</span>
                            </label>
                            <label>
                                <input type="checkbox" name="" />
                                <span class="title">Top Tier</span>
                            </label>
                            <label>
                                <input type="checkbox" name="" />
                                <span class="title">New</span>
                            </label>
                        </div>
                    </div>
                    <div class="sidebar-box">
                        <h5>Location</h5>
                        <div class="form-group">
                            <input type="text" name="filter" placeholder="Enter Zip or Area" class="form-control">
                        </div>
                        <div class="box-inner">
                            <div class="d-flex">
                                <div class="wrapper price-range-container">
                                    <div class="price-input" id="makers-location-filter">
                                        <div class="field">
                                            <span>Min</span>
                                            <input type="number" class="input-min" value="0" name="price_min">
                                        </div>
                                        <div class="separator">-</div>
                                        <div class="field">
                                            <span>Max</span>
                                            <input type="number" class="input-max" value="1000" name="price_max">
                                        </div>
                                    </div>
                                    <div class="slider">
                                        <div class="progress"></div>
                                    </div>
                                    <div class="range-input">
                                        <input type="range" class="range-min" min="0" max="1000" value="0" step="10" />
                                        <input type="range" class="range-max" min="0" max="1000" value="1000" step="10" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="filter-itemshow">
                    <div class="listing-sort list" id="makers-filtered-listing-section">
                        <?php 
                        $arguements = [
                            'post_type'         =>  ['makers_listing', 'market_listing', 'auction_listing', 'outlet_listing'],
                            'post_status'       =>  'publish',
                            'posts_per_page'    =>  -1,
                            'orderby'           =>  'date',
                            'order'             =>  'DESC'
                        ];
                        $maKers = get_posts($arguements);
                        if(!empty($maKers)){
                            foreach($maKers as $maK){
                                $mkID               =   $maK->ID;
                                $maker_image_url    =   wp_get_attachment_url(get_post_thumbnail_id($mkID));
                                $price_from         =   get_post_meta($mkID, 'price_from', true);
                                $price_from         =   get_post_meta($mkID, 'price_from', true);
                                $total_ratings      =   get_post_meta($mkID, 'total_ratings', true);
                                $average_rating     =   get_post_meta($mkID, 'average_rating', true);
                                ?>
                                <a href="<?= get_permalink($mkID) ?>" class="listing-item">
                                    <div class="item-img"><img src="<?= $maker_image_url ?>" class="img-fluid" alt="<?= ucfirst($maK->post_title) ?>"/></div>
                                    <div class="item-content">
                                        <h4 class="title"><?= ucfirst($maK->post_title) ?></h4>
                                        <div class="maK-desc"><p><?= substr(strip_tags($maK->post_content), 0, 80).'..' ?></p></div>
                                        <div class="inner-content">
                                            <div class="price">
                                                From $<?= $price_from ?>
                                            </div>
                                            <div class="rating-comment">
                                                <span class="rating"><?= $average_rating ?> <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
                                                <span class="comments">(<?= $total_ratings ?>)</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <?php
                            }
                        }else{
                            '<p class="notfound-makers">Nothing Found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ADVANCE SEARCH SECTION -->
 
<!-- FEATURED START -->
<section class="featured-main">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Showcased Outlets</h2>
                </div>
            </div>
        </div>
        <div class="featured-block d-grid grid-2 mb-40">
            <?php 
            $featured_Args = [
                'post_type'      => 'playlist',
                'post_status'    => 'publish',
                'posts_per_page' => 2,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_query'     => [
                    [
                        'key'       =>  'check_featured',   
                        'value'     =>  '1',
                        'compare'   =>  '='
                    ],
                ],
            ];
            $latestFeatured     =   get_posts($featured_Args);
            $exCludeFeatured    =   [];
            if(!empty($latestFeatured) && is_array($latestFeatured)){
                foreach($latestFeatured as $lf){
                    $exCludeFeatured[] = $lf->ID;
                    $featuredText   =   get_post_meta($lf->ID, 'featured_text', true);
                    $thumb          =   get_post_thumbnail_id($lf->ID);
                    $attachmenturl  =   wp_get_attachment_image_url($thumb, 'full');
                    ?>
                    <div class="featured-block-item">
                        <div class="featuredimg"><img src="<?= $attachmenturl ?>" class="img-fluid" alt="<?= ucfirst($lf->post_title) ?>" /></div>
                        <div class="featured-content">
                            <span><?= ucfirst($featuredText) ?></span>
                            <h3><a href="<?= site_url() ?>/channels/?type=p&id=<?= $lf->ID ?>"><?= ucfirst($lf->post_title) ?></a></h3>
                            <p><?= substr(strip_tags($lf->post_content), 0, 90).'...' ?></p>
                            <a href="<?= site_url() ?>/channels/?type=p&id=<?= $lf->ID ?>" class="btn btn-primary btn-large">Play <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>
<!-- FEATURED END -->
<?php get_footer(); ?>