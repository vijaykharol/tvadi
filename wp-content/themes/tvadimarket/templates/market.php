<?php
/*
Template Name: Market
*/
if(!defined('ABSPATH')){
    exit; 
}
get_header(); ?>
<!-- MARKET SHOWCASED START -->
<section class="market-showcase">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="left-side position-relative">
                    <div class="image"><img src="<?= get_stylesheet_directory_uri() ?>/images/showcase.png" class="img-fluid" /></div>
                    <div class="content">
                        <h2>Featured Listings</h2>
                        <p>New Listings Showcased</p>
                        <a href="#market-listing-section" id="button-featured-listings-show" class="btn btn-primary btn-large">View <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""></a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="right-side">
                    <div class="row">
                        <div class="col-12 col-lg-12">
                            <div class="auction-counter position-relative" style="background-image: url(<?= get_stylesheet_directory_uri() ?>/images/auction-showcase.png);">
                                <h3>Auction Listings</h3>
                                <!-- <div id="countdown">
                                    <ul>
                                        <li><span id="days"></span>Days</li>
                                        <li><span id="hours"></span>Hr</li>
                                        <li><span id="minutes"></span>Min</li>
                                        <li><span id="seconds"></span>Sc</li>
                                    </ul>
                                </div> -->
                                <a href="#market-listing-section" id="button-auction-listings-show" class="btn btn-primary btn-large">View <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""></a>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="color-box  gradient-blue">
                                <div class="content">
                                    <h3>Maker Listings</h3>
                                    <a href="#market-listing-section" id="button-maker-listings-show" class="btn btn-white">View details</a>
                                </div>
                                <div class="icon"><img src="<?= get_stylesheet_directory_uri() ?>/images/maker-lisitng-icon.png" class="img-fluid" /></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="color-box gradient-yellow">
                                <div class="content">
                                    <h3>Outlet Listings</h3>
                                    <a href="#market-listing-section" id="button-outlet-listings-show" class="btn btn-white">View details</a>
                                </div>
                                <div class="icon"><img src="<?= get_stylesheet_directory_uri() ?>/images/outlet-listing-icon.png" class="img-fluid" /></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- MARKET SHOWCASED END -->

<!-- MARKET LISTING START -->
<section class="market-listing" id="market-listing-section">
    <div class="container-fluid">
        <div class="filter-topbar">
            <form method="" class="align-items-center">
                <div class="search-form search-large">
                    <input class="form-control" type="search" placeholder="Search" aria-label="Search" id="market-search-content"/>
                    <button type="submit" class="btn btn-search" id="market-listings-search-btn">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            version="1.1"
                            xmlns:xlink="http://www.w3.org/1999/xlink"
                            width="512"
                            height="512"
                            x="0"
                            y="0"
                            viewBox="0 0 461.516 461.516"
                            style="enable-background:new 0 0 512 512"
                            xml:space="preserve"
                            class=""
                        >
                            <g>
                                <path
                                    d="M185.746 371.332a185.294 185.294 0 0 0 113.866-39.11L422.39 455c9.172 8.858 23.787 8.604 32.645-.568 8.641-8.947 8.641-23.131 0-32.077L332.257 299.577c62.899-80.968 48.252-197.595-32.716-260.494S101.947-9.169 39.048 71.799-9.204 269.394 71.764 332.293a185.64 185.64 0 0 0 113.982 39.039zM87.095 87.059c54.484-54.485 142.82-54.486 197.305-.002s54.486 142.82.002 197.305-142.82 54.486-197.305.002l-.002-.002c-54.484-54.087-54.805-142.101-.718-196.585l.718-.718z"
                                    fill="#ffffff"
                                    opacity="1"
                                    data-original="#000000"
                                    class=""
                                ></path>
                            </g>
                        </svg>
                    </button>
                </div>
                <div class="right-side">
                    <div class="list-grid">
                        <button type="button" class="btn list-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/list.png" class="img-fluid" alt="" /></button>
                        <button type="button" class="btn grid-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/dots.png" class="img-fluid" alt="" /></button>
                    </div>
                    <div class="sort-by">
                        <select class="form-control" name="sortby" id="market-sortby-filters">
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
        <div class="filter-with-list">
            <div class="filter-sidebar">
                <div class="sidebar-box">
                    <h5>Listing Type</h5>
                    <div class="box-inner checkbox-type">
                        <label>
                            <input type="radio" name="listing_type" value="makers_listing"/>
                            <span class="title">Makers</span>
                        </label>
                        <label>
                            <input type="radio" name="listing_type" value="outlet_listing"/>
                            <span class="title">Outlets</span>
                        </label>
                        <label>
                            <input type="radio" name="listing_type" value="market_listing" checked/>
                            <span class="title">Market</span>
                        </label>
                        <label>
                            <input type="radio" name="listing_type" value="auction_listing"/>
                            <span class="title">Auction</span>
                        </label>
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Price Range</h5>
                    <div class="box-inner checkbox-type">
                        <label>
                            <input type="checkbox" name="price_filter_mode" id="market-price-filter-mode"/>
                            <span class="title">Filter On</span>
                        </label>
                        <div class="d-flex">
                            <div class="wrapper price-range-container">
                                <div class="price-input" id="market-price-filter">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="input-min" id="market-input-min-field" value="0">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="input-max" id="market-input-max-field" value="1000">
                                    </div>
                                </div>
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input" id="market-price-range-section">
                                    <input type="range" class="range-min" min="0" max="1000" value="0" step="10" />
                                    <input type="range" class="range-max" min="0" max="1000" value="1000" step="10" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Platform</h5>
                    <div class="box-inner checkbox-type" id="market-platforms-section">
                        <?php 
                        $plateform_args = [
                            'taxonomy'      =>  'market_platform',
                            'hide_empty'    =>   false,
                            'orderby'       =>  'id',
                            'order'         =>  'DESC', 
                        ];
                        $plateforms = get_terms($plateform_args);
                        if(!empty($plateforms)){
                            foreach($plateforms as $platform){
                                ?>
                                <label>
                                    <input type="checkbox" name="market_platforms[]" value="<?= $platform->term_id ?>"/>
                                    <span class="title"><?= $platform->name ?></span>
                                </label>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Time Preference</h5>
                    <div class="form-group">
                        <input type="date" name="filter" placeholder="Start date" class="form-control" id="market-start-date">
                    </div>
                    <div class="form-group">
                        <input type="date" name="filter" placeholder="End date / Open" class="form-control" id="market-end-date">
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Average Rating</h5>
                    <div class="box-inner checkbox-type">
                        <label>
                            <input type="checkbox" name="average_rating_mode" id="market-average-rating-mode"/>
                            <span class="title">Filter On</span>
                        </label>
                        <div class="d-flex">
                            <div class="wrapper rating-range-container">
                                <div class="price-input rating-input" id="market-avg-rating-filter">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="input-min" id="market-min-rating" value="0" step="0.1" min="0" max="5">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="input-max" id="market-max-rating" value="5" step="0.1" min="0" max="5">
                                    </div>
                                </div>
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input" id="market-avg-rating-range-section">
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
                    <div class="d-flex">
                        <div class="wrapper price-range-container">
                            <div class="price-input" id="market-location-filter">
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
                            <div class="range-input" id="market-location-range-section">
                                <input type="range" class="range-min" min="0" max="1000" value="0" step="10"/>
                                <input type="range" class="range-max" min="0" max="1000" value="1000" step="10"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-itemshow">
                <div class="listing-sort grid" id="market-listings-data-section">
                    <?php
                    $arguements = [
                        'post_type'         =>  'market_listing',
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  -1,
                        'orderby'           =>  'date',
                        'order'             =>  'DESC'
                    ];
                    $market_listing = get_posts($arguements);
                    if(!empty($market_listing)){
                        foreach($market_listing as $market){
                            $mID                =   $market->ID;
                            $m_image_url    =   wp_get_attachment_url(get_post_thumbnail_id($mID));
                            $price_from         =   (!empty(get_post_meta($mID, 'price_from', true)))       ? (int)get_post_meta($mID, 'price_from', true)      :   0;
                            $total_ratings      =   (!empty(get_post_meta($mID, 'total_ratings', true)))    ? (int) get_post_meta($mID, 'total_ratings', true)  :   0;
                            $average_rating     =   (!empty(get_post_meta($mID, 'average_rating', true)))   ? (int) get_post_meta($mID, 'average_rating', true) :   0;
                            ?>
                            <a href="<?= get_permalink($mID) ?>" class="listing-item">
                                <div class="item-img"><img src="<?= $m_image_url ?>" class="img-fluid" alt="<?= $market->post_title ?>"/></div>
                                <div class="item-content">
                                    <h4 class="title"><?= $market->post_title ?></h4>
                                    <div class="description"><p><?= substr(strip_tags($market->post_content), 0, 80).'...' ?></p></div>
                                    <div class="inner-content">
                                        <div class="price">
                                            <?php 
                                            if(!empty($price_from)){
                                                ?>
                                                <span>From $<?= $price_from ?></span>
                                                <?php
                                            }
                                            ?>
                                        </div>
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
                                    </div>
                                </div>
                            </a>
                            <?php
                        }
                    }else{
                        echo '<p class="no-results">Nothing Found.</p>';
                    }
                    ?>
                </div>
                <div class="listing-sort list list-margin auction-list">
                    <div class="global-heading">
                        <h2 class="title">Auction</h2>
                    </div>
                    <?php 
                    $auction_args = [
                        'post_type'         =>  'auction_listing',
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  6,
                        'orderby'           =>  'date',
                        'order'             =>  'DESC'
                    ];
                    $auctions = get_posts($auction_args);
                    if(!empty($auctions)){
                        foreach($auctions as $auc){
                            $auc_id             =   $auc->ID;
                            $auc_image_url      =   wp_get_attachment_url(get_post_thumbnail_id($auc_id));
                            $price_from         =   (!empty(get_post_meta($auc_id, 'price_from', true)))       ? (int)get_post_meta($auc_id, 'price_from', true)      :   0;
                            $total_ratings      =   (!empty(get_post_meta($auc_id, 'total_ratings', true)))    ? (int) get_post_meta($auc_id, 'total_ratings', true)  :   0;
                            $average_rating     =   (!empty(get_post_meta($auc_id, 'average_rating', true)))   ? (int) get_post_meta($auc_id, 'average_rating', true) :   0;
                            ?>
                            <a href="" class="listing-item">
                                <div class="item-img"><img src="<?= $auc_image_url ?>" class="img-fluid" alt="<?= $auc->post_title ?>" /></div>
                                <div class="item-content">
                                    <h4 class="title"><?= $auc->post_title ?></h4>
                                    <div class="description"><p><?= substr(strip_tags($auc->post_content), 0, 100) ?></p></div>
                                    <div class="inner-content">
                                        <div class="price">
                                            <?php 
                                            if(!empty($price_from)){
                                                ?>
                                                <span>From $<?= $price_from ?></span>
                                                <?php
                                            }
                                            ?>
                                        </div>
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
                                    </div>
                                </div>
                            </a>
                            <?php
                        }
                    }else{
                        echo '<p class="nothing-found">Nothing Found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- MARKET LISTING END -->

<!-- MARKET RESOURCES START -->
<section class="market-resources pb-5">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Market Resources</h2>
                </div>
            </div>
        </div>
        <div class="featured-block d-grid grid-2">
            <?php 
            $marketResourecesArgs = [
                'post_type'         =>  'playlist',
                'post_status'       =>  'publish',
                'posts_per_page'    =>  2,
                'orderby'           =>  'date',
                'order'             =>  'DESC'
            ];
            $marketResoureces = get_posts($marketResourecesArgs);
            if(!empty($marketResoureces)){
                foreach($marketResoureces as $marketRes){
                    $marketid     =   $marketRes->ID;
                    $ms_img_url   =   wp_get_attachment_url(get_post_thumbnail_id($marketid));
                    ?>
                    <div class="featured-block-item">
                        <div class="featuredimg"><img src="<?= $ms_img_url ?>" class="img-fluid" alt="<?= ucfirst($marketRes->post_title) ?>" /></div>
                        <div class="featured-content">
                            <span>Market Help</span>
                            <h3><a href="/channels/?type=p&id=<?= $marketid ?>"><?= ucfirst($marketRes->post_title) ?></a></h3>
                            <div class="description"><p><?= substr(strip_tags($marketRes->post_content), 0, 80).'...' ?></p></div>
                            <a href="/channels/?type=p&id=<?= $marketid ?>" class="btn btn-primary btn-large">Play <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>
<!-- SHOWCASE OUTLETS END -->
<?php get_footer(); ?>