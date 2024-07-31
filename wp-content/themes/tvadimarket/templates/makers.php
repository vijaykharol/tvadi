<?php 
/*
Template Name: Makers
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
            <div class="col-12 col-xl-9">
                <?php 
                $mbArgs = [
                    'post_type'         =>  'makers_listing',
                    'post_status'       =>  'publish',
                    'posts_per_page'    =>  1,
                    'orderby'           =>  'date',
                    'order'             =>  'DESC',
                ];
                $mbpost         =   get_posts($mbArgs);
                $excludeids     =   [];
                if(!empty($mbpost)){
                    foreach($mbpost as $mbp){
                        $fmbid          =   $mbp->ID;
                        $excludeids[]   =   $fmbid;
                        $fmbthumb       =   get_post_thumbnail_id($fmbid);
                        $mbaUrl         =   wp_get_attachment_url($fmbthumb);
                        ?>
                        <div class="inner-banner-content" style="background-image: url(<?= $mbaUrl ?>);">
                            <h2><?= ucfirst($mbp->post_title) ?></h2>
                            <div class="p-description"><p><?= substr(strip_tags($mbp->post_content), 0, 50).'...' ?></p></div>
                            <a href="<?= get_permalink($fmbid) ?>" class="btn btn-primary btn-large">Visit <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a>
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

<!-- FEATURED START -->
<section class="featured-main">
    <div class="container-fluid">
        <div class="featured-block d-grid grid-2">
            <?php 
            $makersArgs = [
                'post_type'         =>  'makers_listing',
                'post_status'       =>  'publish',
                'posts_per_page'    =>  2,
                'orderby'           =>  'date',
                'order'             =>  'DESC',
                'post__not_in'      =>  $excludeids,
            ];
            $fMakers = get_posts($makersArgs);
            if(!empty($fMakers)){
                foreach($fMakers as $fm){
                    $fmid       =   $fm->ID;
                    $fmthumb    =   get_post_thumbnail_id($fmid);
                    $aUrl       =   wp_get_attachment_url($fmthumb);
                    ?>
                    <div class="featured-block-item">
                        <div class="featuredimg"><img src="<?= $aUrl ?>" class="img-fluid" alt="<?= ucfirst($fm->post_title) ?>"/></div>
                        <div class="featured-content">
                            <span>Explore</span>
                            <h3><a href="<?= get_permalink($fmid) ?>"><?= ucfirst($fm->post_title) ?></a></h3>
                            <div class="p-description"><p><?= substr(strip_tags($fm->post_content), 0, 90).'...' ?></p></div>
                            <a href="<?= get_permalink($fmid) ?>" class="btn btn-primary btn-large">Visit <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a>
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

<!-- MAKERS CHANNELS START -->
<section class="channels-main makers-channel">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Maker Channels</h2>
                </div>
            </div>
        </div>
        <div class="channels-wrapper">
            <?php 
            /**
             * Daily Top Viral Clips
             */
            $f1 = get_term_by('slug', 'daily-top-viral-clips', 'featured');
            if(!empty($f1)){
                $f1ID   =   $f1->term_id;
                $f1Args =   [
                    'post_type'         => 'playlist', 
                    'post_status'       => 'publish', 
                    'posts_per_page'    => 4,
                    'orderby'           => 'date',
                    'order'             => 'DESC',
                    'tax_query'         => [
                        [
                            'taxonomy'  =>  'featured',
                            'field'     =>  'term_id',
                            'terms'     =>  $f1ID,
                        ],
                    ],
                ];
                $f1posts    =   get_posts($f1Args);

                //views
                $viewsCounter       =   0;
                if(!empty($f1posts)){
                    foreach($f1posts as $f1p){
                        $postviews    = (int) get_post_meta($f1p->ID, 'views', true);
                        $viewsCounter = (int) $viewsCounter + $postviews;
                    }
                }
                ?>
                <div class="featured-add">
                    <div class="content-wrapper">
                        <div class="head-with-desc">
                            <h3><a href="<?= site_url() ?>/channels/?type=c&id=<?= $f1ID ?>"><?= ucfirst($f1->name) ?></a></h3>
                            <p><?= $f1->description ?></p>
                        </div>
                        <div class="box-data">
                            <?php 
                            if(is_user_logged_in()){
                                $userId             =   get_current_user_id();
                                $termlikesData      =   get_user_meta($userId, 'user_term_wishlist', true);
                                $termlikesDataArray =   (!empty($termlikesData) && is_array($termlikesData)) ? (array) $termlikesData : [];
                                if(!empty($termlikesDataArray) && in_array($f1ID, $termlikesDataArray)){
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $f1ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike"></button>
                                    <?php
                                }else{
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $f1ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                    <?php
                                }
                            }else{
                                ?>
                                <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                <?php
                            }
                            ?>
                            <button type="button" class="view"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $f1ID ?>"><img src="<?= get_stylesheet_directory_uri() ?>/images/view.png" class="img-fluid" alt="" /></a></button>
                            <span class="comments">± <?= TvadiFrontEnd::calculatePlaylistviews($viewsCounter) ?></span>
                        </div>
                    </div>
                    <div class="img-layout">
                        <?php 
                        if(!empty($f1posts)){
                            ?>
                            <div class="image-layout-wrapper">
                                <?php 
                                foreach($f1posts as $f1po){
                                    $f1pid      =   $f1po->ID;
                                    $f1pthumb   =   get_post_thumbnail_id($f1pid);
                                    $f1pimgurl  =   wp_get_attachment_image_url($f1pthumb);
                                    ?>
                                    <a href="<?= site_url() ?>/channels/?type=p&id=<?= $f1pid ?>" class="add-img"><img src="<?= $f1pimgurl ?>" class="img-fluid" alt="<?= $f1po->ID ?>" /></a>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!--  24/7 Music Videos -->
            <?php
            /**
             * 24/7 Music Videos
             */
            $f2 = get_term_by('slug', '24-7-music-videos', 'featured');
            if(!empty($f2)){
                $f2ID   =   $f2->term_id;
                $f2Args =   [
                    'post_type'         => 'playlist', 
                    'post_status'       => 'publish', 
                    'posts_per_page'    => 4,
                    'orderby'           => 'date',
                    'order'             => 'DESC',
                    'tax_query'         => [
                        [
                            'taxonomy'  =>  'featured',
                            'field'     =>  'term_id',
                            'terms'     =>  $f2ID,
                        ],
                    ],
                ];
                $f2posts    =   get_posts($f2Args);

                //views
                $viewsCounter       =   0;
                if(!empty($f2posts)){
                    foreach($f2posts as $f2p){
                        $postviews    = (int) get_post_meta($f2p->ID, 'views', true);
                        $viewsCounter = (int) $viewsCounter + $postviews;
                    }
                }
                ?>
                <div class="featured-add">
                    <div class="content-wrapper">
                        <div class="head-with-desc">
                            <h3><a href="<?= site_url() ?>/channels/?type=c&id=<?= $f2ID ?>"><?= ucfirst($f2->name) ?></a></h3>
                            <p><?= $f2->description ?></p>
                        </div>
                        <div class="box-data">
                            <?php 
                            if(is_user_logged_in()){
                                $userId             =   get_current_user_id();
                                $termlikesData      =   get_user_meta($userId, 'user_term_wishlist', true);
                                $termlikesDataArray =   (!empty($termlikesData) && is_array($termlikesData)) ? (array) $termlikesData : [];
                                if(!empty($termlikesDataArray) && in_array($f2ID, $termlikesDataArray)){
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $f2ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike"></button>
                                    <?php
                                }else{
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $f2ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                    <?php
                                }
                            }else{
                                ?>
                                <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                <?php
                            }
                            ?>
                            <button type="button" class="view"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $f2ID ?>"><img src="<?= get_stylesheet_directory_uri() ?>/images/view.png" class="img-fluid" alt="" /></a></button>
                            <span class="comments">± <?= TvadiFrontEnd::calculatePlaylistviews($viewsCounter) ?></span>
                        </div>
                    </div>
                    <div class="img-layout">
                        <?php 
                        if(!empty($f2posts)){
                            ?>
                            <div class="image-layout-wrapper">
                                <?php 
                                foreach($f2posts as $f2po){
                                    $f2pid      =   $f2po->ID;
                                    $f2pthumb   =   get_post_thumbnail_id($f2pid);
                                    $f2pimgurl  =   wp_get_attachment_image_url($f2pthumb);
                                    ?>
                                    <a href="<?= site_url() ?>/channels/?type=p&id=<?= $f2pid ?>" class="add-img"><img src="<?= $f2pimgurl ?>" class="img-fluid" alt="<?= $f2po->ID ?>" /></a>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!--  -->
            <?php
            /**
             * Todays Trending
             */
            $f3 = get_term_by('slug', 'todays-trending', 'featured');
            if(!empty($f3)){
                $f3ID   =   $f3->term_id;
                $f3Args =   [
                    'post_type'         => 'playlist', 
                    'post_status'       => 'publish', 
                    'posts_per_page'    => 4,
                    'orderby'           => 'date',
                    'order'             => 'DESC',
                    'tax_query'         => [
                        [
                            'taxonomy'  =>  'featured',
                            'field'     =>  'term_id',
                            'terms'     =>  $f3ID,
                        ],
                    ],
                ];
                $f3posts    =   get_posts($f3Args);

                //views
                $viewsCounter       =   0;
                if(!empty($f3posts)){
                    foreach($f3posts as $f3p){
                        $postviews    = (int) get_post_meta($f3p->ID, 'views', true);
                        $viewsCounter = (int) $viewsCounter + $postviews;
                    }
                }
                ?>
                <div class="featured-add">
                    <div class="content-wrapper">
                        <div class="head-with-desc">
                            <h3><a href="<?= site_url() ?>/channels/?type=c&id=<?= $f3ID ?>"><?= ucfirst($f3->name) ?></a></h3>
                            <p><?= $f3->description ?></p>
                        </div>
                        <div class="box-data">
                            <?php 
                            if(is_user_logged_in()){
                                $userId             =   get_current_user_id();
                                $termlikesData      =   get_user_meta($userId, 'user_term_wishlist', true);
                                $termlikesDataArray =   (!empty($termlikesData) && is_array($termlikesData)) ? (array) $termlikesData : [];
                                if(!empty($termlikesDataArray) && in_array($f3ID, $termlikesDataArray)){
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $f3ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike"></button>
                                    <?php
                                }else{
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $f3ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                    <?php
                                }
                            }else{
                                ?>
                                <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                <?php
                            }
                            ?>
                            <button type="button" class="view"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $f3ID ?>"><img src="<?= get_stylesheet_directory_uri() ?>/images/view.png" class="img-fluid" alt="" /></a></button>
                            <span class="comments">± <?= TvadiFrontEnd::calculatePlaylistviews($viewsCounter) ?></span>
                        </div>
                    </div>
                    <div class="img-layout">
                        <?php 
                        if(!empty($f3posts)){
                            ?>
                            <div class="image-layout-wrapper">
                                <?php 
                                foreach($f3posts as $f3po){
                                    $f3pid      =   $f3po->ID;
                                    $f3pthumb   =   get_post_thumbnail_id($f3pid);
                                    $f3pimgurl  =   wp_get_attachment_image_url($f3pthumb);
                                    ?>
                                    <a href="<?= site_url() ?>/channels/?type=p&id=<?= $f3pid ?>" class="add-img"><img src="<?= $f3pimgurl ?>" class="img-fluid" alt="<?= $f3po->ID ?>" /></a>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!--  -->
            <div class="text-end discoverall"><a href="#" class="btn btn-secondary btn-small">Discover All</a></div>
        </div>
    </div>
</section>
<!-- MAKERS CHANNELS END -->

<!-- MAKER LISTING START -->
<section class="maker-listing">
    <div class="container-fluid">
        <div class="filter-topbar">
            <form method="POST" class="align-items-center" id="maker-filter-form">
                <div class="search-form search-large">
                    <input class="form-control" type="search" name="search" id="maker-search-content" placeholder="Newest Maker Listings" aria-label="Search">
                    <button type="submit" class="btn btn-search" id="search-maker-listing-btn"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 461.516 461.516" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M185.746 371.332a185.294 185.294 0 0 0 113.866-39.11L422.39 455c9.172 8.858 23.787 8.604 32.645-.568 8.641-8.947 8.641-23.131 0-32.077L332.257 299.577c62.899-80.968 48.252-197.595-32.716-260.494S101.947-9.169 39.048 71.799-9.204 269.394 71.764 332.293a185.64 185.64 0 0 0 113.982 39.039zM87.095 87.059c54.484-54.485 142.82-54.486 197.305-.002s54.486 142.82.002 197.305-142.82 54.486-197.305.002l-.002-.002c-54.484-54.087-54.805-142.101-.718-196.585l.718-.718z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg></button>
                </div>
                <div class="right-side">
                    <div class="list-grid">
                        <button type="button" class="btn list-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/list.png" class="img-fluid" alt="" /></button>
                        <button type="button" class="btn grid-view-button"><img src="<?= get_stylesheet_directory_uri() ?>/images/dots.png" class="img-fluid" alt="" /></button>
                    </div>
                    <div class="sort-by">
                        <select class="form-control" name="sortby" id="makers-sortby-filters">
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
                            <input type="radio" name="maker_listing_type" value="makers_listing" checked/>
                            <span class="title">Makers</span>
                        </label>
                        <label>
                            <input type="radio" name="maker_listing_type" value="outlet_listing"/>
                            <span class="title">Outlets</span>
                        </label>
                        <label>
                            <input type="radio" name="maker_listing_type" value="market_listing"/>
                            <span class="title">Market</span>
                        </label>
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Price Range</h5>
                    <div class="box-inner checkbox-type">
                        <label>
                            <input type="checkbox" name="price_filter_status" id="price-filter-mode" value="1"/>
                            <span class="title">Filter On</span>
                        </label>
                        <div class="d-flex">
                            <div class="wrapper price-range-container">
                                <div class="price-input" id="makers-price-filter">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="input-min" id="input-min-field" value="0" name="price_min">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="input-max" id="input-max-field" value="1000" name="price_max">
                                    </div>
                                </div>
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input" id="maker-price-range-section">
                                    <input type="range" class="range-min" min="0" max="1000" value="0" step="10" />
                                    <input type="range" class="range-max" min="0" max="1000" value="1000" step="10" />
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
                                    <input type="checkbox" name="plateforms[]" value="<?= $p->term_id ?>"/>
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
                        <input type="date" name="filter" placeholder="Start date" id="filter-start-date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="date" name="filter" placeholder="End date / Open" id="filter-end-date" name="end_date" class="form-control">
                    </div>
                </div>
                <div class="sidebar-box">
                    <h5>Average Rating</h5>
                    <div class="box-inner checkbox-type">
                        <label>
                        <input type="checkbox" name="avgrating_filter_status" id="avg-rating-filter-mode" value="1"/>
                            <span class="title">Filter On</span>
                        </label>
                        <div class="d-flex">
                            <div class="wrapper rating-range-container">
                                <div class="price-input rating-input" id="makers-avg-rating-filter">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="input-min" id="maker-min-rating" value="0" step="0.1" min="0" max="5">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="input-max" id="maker-max-rating" value="5" step="0.1" min="0" max="5">
                                    </div>
                                </div>
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input" id="maker-avg-rating-range">
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
                <div class="listing-sort grid" id="makers-filtered-listing-section">
                    <?php 
                    $arguements = [
                        'post_type'         =>  'makers_listing',
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
                                        <div class="rating-comment">
                                            <span class="rating"><?= $average_rating ?> <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
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
                    }else{
                        '<p class="notfound-makers">Nothing Found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- MAKER LISTING END -->
<?php get_footer(); ?>