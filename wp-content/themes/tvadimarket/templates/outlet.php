<?php
/*
Template Name: Outlet
*/
if(!defined('ABSPATH')){
    exit; 
}

get_header(); ?>
<!-- MAIN START -->
<section class="innerpage-banner">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-xl-9">
                <?php 
                $obArgs = [
                    'post_type'         =>  'outlet_listing',
                    'post_status'       =>  'publish',
                    'posts_per_page'    =>  1,
                    'orderby'           =>  'date',
                    'order'             =>  'DESC',
                ];
                $obpost         =   get_posts($obArgs);
                $excludeids     =   [];
                if(!empty($obpost)){
                    foreach($obpost as $obp){
                        $fobid          =   $obp->ID;
                        $excludeids[]   =   $fobid;
                        $fobthumb       =   get_post_thumbnail_id($fobid);
                        $obaUrl         =   wp_get_attachment_url($fobthumb);
                        ?>
                        <div class="inner-banner-content overlay position-relative" style="background-image: url(<?= $obaUrl ?>);">
                            <div class="position-relative">
                                <h2><?= ucfirst($obp->post_title) ?></h2>
                                <div class="p-description"><p><?= substr(strip_tags($obp->post_content), 0, 50).'...' ?></p></div>
                                <a href="<?= get_permalink($fobid) ?>" class="btn btn-primary btn-large">Article <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="<?= ucfirst($obp->post_title) ?>" /></a>
                            </div>
                        </div>
                        <?php      
                    }
                }
                ?>
            </div>
            <div class="col-12 col-xl-3">
                <div class="trending-right-side">
                    <!-- <div class="text-end make"><button class="btn btn-primary btn-large"><a href="/make-listing/">Make <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="Make"></a></button></div> -->
                    <div class="text-end make">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-large dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                Make <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""/>
                            </button>
                            <ul  class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <?php 
                                if(is_user_logged_in()){
                                    ?>
                                    <li><a class="dropdown-item" href="/tools/">Make Playlist</a></li>
                                    <li><a class="dropdown-item" href="/make-listing/">Make Listing</a></li>
                                    <?php
                                }else{
                                    ?>
                                    <li><a class="dropdown-item" href="/how-to-make-a-playlist/">Make Playlist</a></li>
                                    <li><a class="dropdown-item" href="/how-to-make-a-listing/">Make Listing</a></li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                    <div class="trending-block-wrapper">
                        <?php 
                        $outletplaylists_args = [
                            'post_type'         =>      'playlist',
                            'post_status'       =>      'publish',
                            'posts_per_page'    =>      2,
                            'orderby'           =>      'rand',
                        ];
                        $outlet_playlists = get_posts($outletplaylists_args);
                        if(!empty($outlet_playlists)){
                            foreach($outlet_playlists as $op){
                                $opID               =   $op->ID;
                                $opimageurl         =   wp_get_attachment_url(get_post_thumbnail_id($opID));
                                $views              =   (!empty(get_post_meta($opID, 'views', true))) ? get_post_meta($opID, 'views', true) : 0;
                                $calculatedViews    =   TvadiFrontEnd::calculatePlaylistviews($views);
                                ?>
                                <div class="trending-block-item hover-image">
                                    <div class="single-img radius-15 overflow-hidden"><a href="/channels/?type=p&id=<?= $opID ?>"><img src="<?= $opimageurl ?>" class="img-fluid" alt="<?= ucfirst($op->post_title) ?>"></a></div>
                                    <h4 class="title"><a href="/channels/?type=p&id=<?= $opID ?>"><?= ucfirst($op->post_title) ?></a></h4>
                                    <div class="box-data">
                                        <?php 
                                        if(is_user_logged_in()){
                                            $current_user_id    =   get_current_user_id();
                                            $likesData          =   get_user_meta($current_user_id, 'user_wishlist_detail', true);
                                            $likesDataArray     =   (!empty($likesData) && is_array($likesData)) ? (array) $likesData : [];
                                            if(!empty($likesDataArray) && in_array($opID, $likesDataArray)){
                                                ?>
                                                <button type="button" id="logged-wishlist-btn" onclick="tvadiLike(<?= $opID ?>, this);"class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike" /></button>
                                                <?php
                                            }else{
                                                ?>
                                                <button type="button" id="logged-wishlist-btn" onclick="tvadiLike(<?= $opID ?>, this);"class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like" /></button>
                                                <?php
                                            }
                                        }else{
                                            ?>
                                            <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like" /></button>
                                            <?php
                                        }
                                        ?>
                                        <button type="button" class="view"><a href="/channels/?type=p&id=<?= $opID ?>"><img src="<?= get_stylesheet_directory_uri() ?>/images/view.png" class="img-fluid" alt="<?= ucfirst($op->post_title) ?>"></a></button>
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
    </div>
</section>
<!-- MAIN END  -->

<!-- CATEGORIES START -->
<section class="categories-outlet">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-4 col-lg-4">
                <a href="" class="cb-item hover-image">
                    <h4 class="title">Tvadi Media Connect </h4>
                    <div class="cb-item-img"><img src="<?= get_stylesheet_directory_uri() ?>/images/tavadi-media-connect.png" class="img-fluid" alt="" /></div>
                </a>
            </div>
            <div class="col-12 col-md-4 col-lg-4">
                <a href="" class="cb-item hover-image">
                    <h4 class="title">Open Space Estimates</h4>
                    <div class="cb-item-img"><img src="<?= get_stylesheet_directory_uri() ?>/images/open-space-estimates.png" class="img-fluid" alt="" /></div>
                </a>
            </div>
            <div class="col-12 col-md-4 col-lg-4">
                <a href="" class="cb-item hover-image">
                    <h4 class="title">Auction</h4>
                    <div class="cb-item-img"><img src="<?= get_stylesheet_directory_uri() ?>/images/auction.png" class="img-fluid" alt="" /></div>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- CATEGORIES END -->

<!-- OUTLET LISTING START -->
<section class="outlet-listing">
    <div class="container-fluid">
        <div class="filter-topbar">
            <form method="" class="align-items-center">
                <div class="search-form search-large">
                    <input class="form-control" type="search" id="outlet-search-content" placeholder="Newest Outlet Listings" aria-label="Search">
                    <button type="submit" class="btn btn-search" id="outlet-search-btn"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 461.516 461.516" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M185.746 371.332a185.294 185.294 0 0 0 113.866-39.11L422.39 455c9.172 8.858 23.787 8.604 32.645-.568 8.641-8.947 8.641-23.131 0-32.077L332.257 299.577c62.899-80.968 48.252-197.595-32.716-260.494S101.947-9.169 39.048 71.799-9.204 269.394 71.764 332.293a185.64 185.64 0 0 0 113.982 39.039zM87.095 87.059c54.484-54.485 142.82-54.486 197.305-.002s54.486 142.82.002 197.305-142.82 54.486-197.305.002l-.002-.002c-54.484-54.087-54.805-142.101-.718-196.585l.718-.718z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg></button>
                </div>
                <div class="right-side">
                    <div class="list-grid">
                        <button type="button" class="btn list-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/list.png" class="img-fluid" alt="" /></button>
                        <button type="button" class="btn grid-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/dots.png" class="img-fluid" alt="" /></button>
                    </div>
                    <div class="sort-by">
                        <select class="form-control" name="sortby" id="outlets-sortby-filters">
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
                            <input type="radio" name="outlet_listing_type" value="makers_listing" />
                            <span class="title">Makers</span>
                        </label>
                        <label>
                            <input type="radio" name="outlet_listing_type" value="outlet_listing" checked/>
                            <span class="title">Outlets</span>
                        </label>
                        <label>
                            <input type="radio" name="outlet_listing_type" value="market_listing" />
                            <span class="title">Market</span>
                        </label>
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Price Range</h5>
                    <div class="box-inner checkbox-type">
                        <label>
                            <input type="checkbox" name="outlet_price_mode" id="outlet-price-mode" />
                            <span class="title">Filter On</span>
                        </label>
                        <div class="d-flex">
                            <div class="wrapper price-range-container">
                                <div class="price-input" id="outlet-price-filter">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="input-min" id="o-input-min-field" value="0">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="input-max" id="o-input-max-field" value="1000">
                                    </div>
                                </div>
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input" id="outlet-price-range-section">
                                    <input type="range" class="range-min" min="0" max="1000" value="0" step="10" />
                                    <input type="range" class="range-max" min="0" max="1000" value="1000" step="10" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Platform</h5>
                    <?php 
                    $plateform_args = [
                        'taxonomy'      =>  'outlet_platform',
                        'hide_empty'    =>   false,
                        'orderby'       =>  'id',
                        'order'         =>  'DESC', 
                    ];
                    $plateforms = get_terms($plateform_args);
                    if(!empty($plateforms)){
                        ?>
                        <div class="box-inner checkbox-type">
                            <?php
                            foreach($plateforms as $platef){
                                ?>
                                <label>
                                    <input type="checkbox" name="outlet_plateforms[]" value="<?= $platef->term_id ?>"/>
                                    <span class="title"><?= $platef->name ?></span>
                                </label>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="sidebar-box">
                    <h5>Time Preference</h5>
                    <div class="form-group">
                        <input type="date" name="filter" placeholder="Start date" class="form-control" id="outlet-start-date">
                    </div>
                    <div class="form-group">
                        <input type="date" name="filter" placeholder="End date / Open" class="form-control" id="outlet-end-date">
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Average Rating</h5>
                    <div class="box-inner checkbox-type">
                        <label>
                        <input type="checkbox" name="outlet_avgrating_mode" id="outlet-avg-rating-mode" value='1'/>
                            <span class="title">Filter On</span>
                        </label>
                        <div class="d-flex">
                            <div class="wrapper rating-range-container">
                                <div class="price-input rating-input" id="outlet-avg-rating-filter">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="input-min" id="outlet-min-rating" value="0" step="0.1" min="0" max="5">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="input-max" id="outlet-max-rating" value="5" step="0.1" min="0" max="5">
                                    </div>
                                </div>
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input" id="outlet-avg-rating-range-section">
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
            </div>
            <div class="filter-itemshow">
                <div class="listing-sort list" id="outlet-filtered-listing-section">
                    <?php 
                    $arguements = [
                        'post_type'         =>  'outlet_listing',
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  -1,
                        'orderby'           =>  'date',
                        'order'             =>  'DESC'
                    ];
                    $outlets = get_posts($arguements);
                    if(!empty($outlets)){
                        foreach($outlets as $ouT){
                            $ouTID            =   $ouT->ID;
                            $outlet_img_url   =   wp_get_attachment_url(get_post_thumbnail_id($ouTID));
                            $price_from       =   get_post_meta($ouTID, 'price_from', true);
                            $price_from       =   get_post_meta($ouTID, 'price_from', true);
                            $total_ratings    =   get_post_meta($ouTID, 'total_ratings', true);
                            $average_rating   =   get_post_meta($ouTID, 'average_rating', true);
                            ?>
                            <a href="<?= get_permalink($ouTID) ?>" class="listing-item">
                                <div class="item-img"><img src="<?= $outlet_img_url ?>" class="img-fluid" alt="<?= ucfirst($ouT->post_title) ?>" /></div>
                                <div class="item-content">
                                    <h4 class="title"><?= ucfirst($ouT->post_title) ?></h4>
                                    <div class="maK-desc"><p><?= substr(strip_tags($ouT->post_content), 0, 80).'...' ?></p></div>
                                    <div class="inner-content">
                                        <div class="rating-comment">
                                            <span class="rating"><?= $average_rating ?> <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt="<?= ucfirst($ouT->post_title) ?>" /></span>
                                            <span class="comments">(<?= $total_ratings ?>)</span>
                                        </div>
                                        <div class="price">
                                            From $<?= $price_from ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- OUTLET LISTING END -->

<!-- SHOWCASE OUTLETS START -->
<section class="showcase-outlets">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Showcased Outlets</h2>
                </div>
            </div>
        </div>
        <?php 
        $showArgs = [
            'post_type'         =>      'playlist',
            'post_status'       =>      'publish',
            'posts_per_page'    =>      2,
            'orderby'           =>      'rand',
        ];
        $showOposts = get_posts($showArgs);
        if(!empty($showOposts)){
            ?>
            <div class="featured-block d-grid grid-2">
                <?php 
                foreach($showOposts as $sop){
                    $sopID      =   $sop->ID;
                    $sopimgurl  =   wp_get_attachment_url(get_post_thumbnail_id($sopID));
                    ?>
                    <div class="featured-block-item">
                        <div class="featuredimg"><img src="<?= $sopimgurl ?>" class="img-fluid" alt="<?= ucfirst($sop->post_title) ?>"/></div>
                        <div class="featured-content">
                            <span>Featured</span>
                            <h3><a href=""><?= ucfirst($sop->post_title) ?></a></h3>
                            <div class="sop-posts"><p><?= substr(strip_tags($sop->post_content), 0, 90).'...' ?></p></div>
                            <a href="/channels/?type=p&id=<?= $sopID ?>" class="btn btn-primary btn-large">Play <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="<?= ucfirst($sop->post_title) ?>" /></a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
</section>
<!-- SHOWCASE OUTLETS END -->
<?php get_footer(); ?>