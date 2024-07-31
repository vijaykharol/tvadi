<?php 
/*
Template Name: Home Page
*/
if(!defined('ABSPATH')){
    exit; 
}

get_header();
?>
<!-- BANNER SLIDER WITH PLAYLIST START -->
<section class="banner-slider-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-12 col-xl-9">
                <div class="banner-slider">
                    <div class="owl-carousel owl-theme" id="bannerslider">
                        <?php 
                        $argspSlider = [
                            'post_type'         =>  'playlist',
                            'post_status'       =>  'publish',
                            'posts_per_page'    =>  3,
                            'orderby'           => 'date',
                            'order'             => 'DESC',
                        ];
                        $pSlider = get_posts($argspSlider);
                        if(!empty($pSlider)){
                            foreach($pSlider as $ps){
                                $spID           =   $ps->ID; // Get the current post ID
                                $spthumb        =   get_post_thumbnail_id($spID);
                                $spimgurl       =   wp_get_attachment_image_src($spthumb, 'full')[0];
                                //channel
                                $channelsCat    =   get_the_terms($spID, 'channel');
                                $firstChannel   =   (!empty($channelsCat) && is_array($channelsCat)) ? (array) $channelsCat[0] : '';
                                $channelname    =   $firstChannel['name'];
                               
                                //playlist category
                                // echo 'yes';
                                $playlists_category    =   get_the_terms($spID, 'playlists_category');
                                $fplaylists_category   =   (!empty($playlists_category) && is_array($playlists_category)) ? (array) $playlists_category[0] : '';
                                $playlistcategory           =   (!empty($fplaylists_category) && is_array($fplaylists_category)) ? $fplaylists_category['name'] : '';
                                
                                // $channelname    =   '';
                                // $playlistcategory    =   '';
                                // //getting channel name from the category list
                                // if(!empty($channelsCat)){
                                //     foreach($channelsCat as $ch){
                                //         if($ch->parent != 0){
                                //             $parent_term    =   get_term($ch->parent, 'channel');
                                //             $channelname    =   (isset($parent_term->name) && !empty($parent_term->name)) ? $parent_term->name : '';
                                //             $playlistcategory    =   $ch->name;
                                //             break;
                                //         }
                                //     }
                                // }

                                $pscontent    =   $ps->post_content;
                                $pscontent    =   apply_filters('the_content', $pscontent);
                                $pscontent    =   str_replace(']]>', ']]&gt;', $pscontent);
                                ?>
                                <div class="item">
                                    <div class="banner-wrapper" style="background-image: url(<?= $spimgurl ?>);">
                                        <div class="content">
                                            <h4 class="banner-title wow fadeInUp"><?= $channelname ?>: <?= $playlistcategory ?></h4>
                                            <div class="available-on">
                                                <?php 
                                                $availabeson    =   get_the_terms($spID, 'device');
                                                if(!empty($availabeson)){
                                                    ?>
                                                    <h6 class="mb-0">Available on:</h6>
                                                    <ul class="list-inline">
                                                        <?php
                                                        foreach($availabeson as $av){
                                                            ?>
                                                            <li class="list-inline-item"><a href="#"><?= $av->name ?></a></li>
                                                            <?php
                                                        }
                                                        ?>
                                                    </ul>
                                                    <?php
                                                }   
                                                ?>
                                            </div>
                                            <p><?= substr(strip_tags($pscontent), 0, 60).'...' ?></p>
                                            <a href="<?= site_url() ?>/channels/?type=p&id=<?= $spID ?>" class="btn btn-primary btn-large">Play <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-12 col-xl-3">
                <div class="playlisting">
                    
                    <!-- <div class="text-end make">
                        <a href="/make-listing/" class="btn btn-primary btn-large">Make <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""/></a>
                    </div>  -->
                    <div class="text-end make">
                        <div class="dropdown">
                            <button class="btn btn-primary btn-large dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                Make <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""/>
                            </button>
                            <ul  class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <?php 
                                if(is_user_logged_in()){
                                    ?>
                                    <li><a class="dropdown-item" href="/tools/#playlist-maker">Make Playlist</a></li>
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
                   
                    <div class="playlisting-wrapper">
                        <h5>Now Playing</h5>
                        <?php 
                        $excludeposts = get_posts([
                            'post_type'     =>  'playlist',
                            'post_status'   =>  'publish',
                            'numberposts'   =>  3, 
                            'orderby'       =>  'date',
                            'order'         =>  'DESC',
                            'fields'        =>  'ids'
                        ]);

                        $nowplayingArgs = [
                            'post_type'         =>  'playlist',
                            'post_status'       =>  'publish',
                            'post__not_in'      =>  $excludeposts,
                            'posts_per_page'    =>  4,
                            'orderby'           => 'date',
                            'order'             => 'DESC',
                        ];
                        $nowplaying = get_posts($nowplayingArgs);
                        if(!empty($nowplaying)){
                            foreach($nowplaying as $np){
                                $np_id      =   $np->ID; // Get the current post ID
                                $npthumb    =   get_post_thumbnail_id($np_id);
                                $npimgurl   =   wp_get_attachment_image_src($npthumb, 'full')[0];
                                $npcnt      =   $np->post_content;
                                $npcnt      =   apply_filters('the_content', $npcnt);
                                $npcnt      =   str_replace(']]>', ']]&gt;', $npcnt);
                                $views      =   (!empty(get_post_meta($np_id, 'views', true))) ? get_post_meta($np_id, 'views', true) : 0;
                                $calculatedViews = TvadiFrontEnd::calculatePlaylistviews($views);
                                ?>
                                <div class="playing-box">
                                    <a href="<?= site_url() ?>/channels/?type=p&id=<?= $np_id ?>" class="box-img">
                                        <img src="<?= $npimgurl ?>" class="img-fluid" alt="<?= ucfirst($np->post_title) ?>"/>
                                    </a>
                                    <div class="box-content">
                                        <a href="<?= site_url() ?>/channels/?type=p&id=<?= $np_id ?>">
                                            <div class="title"><?= ucfirst($np->post_title) ?></div>
                                        </a>
                                        <div class="desc"><?= substr(strip_tags($npcnt), 0, 80).'...' ?></div>
                                        <div class="box-data">
                                            <?php 
                                            if(is_user_logged_in()){
                                                $current_user_id    =   get_current_user_id();
                                                $likesData          =   get_user_meta($current_user_id, 'user_wishlist_detail', true);
                                                $likesDataArray     =   (!empty($likesData) && is_array($likesData)) ? (array) $likesData : [];
                                                if(!empty($likesDataArray) && in_array($np_id, $likesDataArray)){
                                                    ?>
                                                    <button type="button" id="logged-wishlist-btn" onclick="tvadiLike(<?= $np_id ?>, this);" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike" /></button>
                                                    <?php
                                                }else{
                                                    ?>
                                                    <button type="button" id="logged-wishlist-btn" onclick="tvadiLike(<?= $np_id ?>, this);" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like" /></button>
                                                    <?php
                                                }
                                            }else{
                                                ?>
                                                <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like" /></button>
                                                <?php
                                            }
                                            ?>
                                            <a href="<?= site_url() ?>/channels/?type=p&id=<?= $np_id ?>"><button type="button" class="view"><img src="<?= get_template_directory_uri() ?>/images/view.png" class="img-fluid" alt="View" /></button></a>
                                            <span class="comments">± <?= $calculatedViews ?></span>
                                        </div>
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
<!-- BANNER SLIDER END -->

<!-- CATEGORIES START -->
<section class="categories wow fadeInRight">
    <div class="container-fluid">
        <div class="category-block d-grid">
            <?php 
            $taxonomy = 'playlists_category';
            //Playlists Cateogries
            $playlists_categories = get_terms(
                [
                    'taxonomy'      =>  'playlists_category',
                    'hide_empty'    =>  false,
                    'number'        =>  6,
                ]
            );
            if(!empty($playlists_categories)){
                foreach($playlists_categories as $pc){
                    // Get term meta value
                    $catimage           =   get_term_meta($pc->term_id, 'thumbnail_image', true);
                    $cattachment_url    =   wp_get_attachment_url($catimage);
                    $term_link          =   get_term_link($pc->term_id, $taxonomy);
                    ?>
                    <a href="<?= site_url() ?>/channels/?type=c&id=<?= $pc->term_id ?>" class="category-block-item">
                        <div class="image"><img src="<?= $cattachment_url ?>" class="img-fluid" alt="<?= ucfirst($pc->name) ?>" /></div>
                        <?php 
                        $catname = ( string ) $pc->name;
                        $checks = "/";
                        if(strpos($catname, $checks) !== false){
                            $explodename = explode('/', $catname);
                            ?>
                            <h4 class="title"><span><?= $explodename[0] ?></span> / <span><?= $explodename[1] ?></span></h4>
                            <?php
                        }else{
                            ?>
                            <h4 class="title"><span><?= ucfirst($pc->name) ?></span></h4>
                            <?php
                        }
                        ?>
                    </a>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>
<!-- CATEGORIES END -->

<!-- TRENDING START -->
<section class="trending">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-6 col-lg-6">
                    <h2 class="title">Trending</h2>
                </div>
                <div class="col-6 col-lg-6">
                    <div class="text-end see-all"><a href="#" class="btn btn-secondary btn-small">See All <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a></div>
                </div>
            </div>
        </div>
        <div class="trending-block-wrapper d-grid grid-3">
            <?php 
            // Get the latest Trending categories
            $trending = get_terms([
                'taxonomy'  =>  'trending',
                'orderby'   =>  'id',
                'order'     =>  'DESC',
                'number'    =>  3
            ]);
            if(!empty($trending) && is_array($trending)){
                foreach($trending as $tt){
                    $countviews = 0;
                    $tTermID    = $tt->term_id;
                    ?>
                    <div href="" class="trending-block-item grid-hover">
                        <?php 
                        $trending_postsArgs = [
                            'post_type'         => 'playlist', 
                            'post_status'       => 'publish', 
                            'posts_per_page'    => 4,
                            'orderby'           => 'date',
                            'order'             => 'DESC',
                            'tax_query'         => [
                                [
                                    'taxonomy'  =>  'trending',
                                    'field'     =>  'term_id',
                                    'terms'     =>  $tTermID,
                                ],
                            ],
                        ];
                        $termPosts = get_posts($trending_postsArgs);

                        if(!empty($termPosts) && is_array($termPosts)){
                            ?>
                            <div class="grid-img d-grid grid-2 radius-15">
                                <?php 
                                foreach($termPosts as $termp){
                                    $plID                   =   $termp->ID;
                                    $thum_id                =   get_post_thumbnail_id($plID);
                                    $trednigPostImageurl    =   wp_get_attachment_image_url($thum_id, 'full');
                                    $postTotalViews         =   (!empty(get_post_meta($plID, 'views', true))) ? get_post_meta($plID, 'views', true) : 0;
                                    $countviews             =   $countviews + $postTotalViews;
                                    ?>
                                    <div class="gs-img"><img src="<?= $trednigPostImageurl ?>" class="img-fluid" alt="<?= $termp->post_title ?>" /></div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <a href="<?= site_url() ?>/channels/?type=c&id=<?= $tTermID ?>"><h4 class="title"><?= ucfirst($tt->name) ?></h4></a>
                        <div class="box-data">
                            <?php 
                            if(is_user_logged_in()){
                                $userId          =   get_current_user_id();
                                $termlikesData       =   get_user_meta($userId, 'user_term_wishlist', true);
                                $termlikesDataArray  =   (!empty($termlikesData) && is_array($termlikesData)) ? (array) $termlikesData : [];
                                if(!empty($termlikesDataArray) && in_array($tTermID, $termlikesDataArray)){
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $tTermID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike"></button>
                                    <?php
                                }else{
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $tTermID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                    <?php
                                }
                            }else{
                                ?>
                                <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                <?php
                            }
                            ?>
                            <button type="button" class="view"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $tTermID ?>"><img src="<?= get_template_directory_uri() ?>/images/view.png" class="img-fluid" alt="<?= ucfirst($tt->name) ?>"></a></button>
                            <span class="comments">± <?= TvadiFrontEnd::calculatePlaylistviews($countviews) ?></span>
                        </div>
                    </div>
                    <?php
                }
            }else{
                echo '<p>Nothing Found.</p>';
            }
            ?>
        </div>
    </div>
</section>
<!-- TRENDING END -->

<!-- FEATURED START -->
<section class="featured-main">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Featured</h2>
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
        <?php 
        $actionTech = $term = get_term_by('slug', 'action-tech', 'featured');
        if(!empty($actionTech)){
            $actiontechID   =   $actionTech->term_id;
            $actiontechArgs =   [
                'post_type'         => 'playlist', 
                'post_status'       => 'publish', 
                'posts_per_page'    => 4,
                'orderby'           => 'date',
                'order'             => 'DESC',
                'tax_query'         => [
                    [
                        'taxonomy'  =>  'featured',
                        'field'     =>  'term_id',
                        'terms'     =>  $actiontechID,
                    ],
                ],
            ];
            $actionTechposts    =   get_posts($actiontechArgs);
            $viewsCounter       =   0;
            if(!empty($actionTechposts)){
                foreach($actionTechposts as $at){
                    $postviews = ( int ) get_post_meta($at->ID, 'views', true);
                    $viewsCounter = (int) $viewsCounter + $postviews;
                }
            }
            ?>
            <div class="featured-add mb-40">
                <div class="content-wrapper">
                    <div class="head-with-desc">
                        <h3><a href="<?= site_url() ?>/channels/?type=c&id=<?= $actionTech->term_id ?>"><?= ucfirst($actionTech->name) ?></a></h3>
                        <p><?= substr(strip_tags($actionTech->description), 0, 50).'...' ?></p>
                    </div>
                    <div class="box-data">
                        <?php 
                        if(is_user_logged_in()){
                            $userId             =   get_current_user_id();
                            $termlikesData      =   get_user_meta($userId, 'user_term_wishlist', true);
                            $termlikesDataArray =   (!empty($termlikesData) && is_array($termlikesData)) ? (array) $termlikesData : [];
                            if(!empty($termlikesDataArray) && in_array($actiontechID, $termlikesDataArray)){
                                ?>
                                <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $actiontechID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike"></button>
                                <?php
                            }else{
                                ?>
                                <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $actiontechID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                <?php
                            }
                        }else{
                            ?>
                            <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                            <?php
                        }
                        ?>
                        <button type="button" class="view"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $actionTech->term_id ?>"><img src="<?= get_template_directory_uri() ?>/images/view.png" class="img-fluid" alt=""></a></button>
                        <span class="comments">± <?= TvadiFrontEnd::calculatePlaylistviews($viewsCounter) ?></span>
                    </div>
                </div>
                <?php 
                if(!empty($actionTechposts)){
                    ?>
                    <div class="img-layout">
                        <div class="image-layout-wrapper">
                            <?php 
                            foreach($actionTechposts as $actp){
                                $actionthumb    =   get_post_thumbnail_id($actp->ID);
                                $actionImgUrl   =   wp_get_attachment_image_url($actionthumb);
                                ?>
                                <a href="<?= site_url() ?>/channels/?type=p&id=<?= $actp->ID ?>" class="add-img"><img src="<?= $actionImgUrl ?>" class="img-fluid" alt="<?= $actp->ID ?>" /></a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
        <?php
        $featured_Args2 = [
            'post_type'         =>  'playlist',
            'post_status'       =>  'publish',
            'posts_per_page'    =>  2,
            'orderby'           =>  'date',
            'order'             =>  'DESC',
            'post__not_in'      =>  $exCludeFeatured,
            'meta_query'        =>  [
                [
                    'key'       =>  'check_featured',   
                    'value'     =>  '1',
                    'compare'   =>  '='
                ],
            ],
        ];
        $faturedPosts2 = get_posts($featured_Args2);
        if(!empty($faturedPosts2)){
            ?>
            <div class="featured-block d-grid grid-2 mb-40">
                <?php 
                foreach($faturedPosts2 as $fp2){
                    $f2thumb        =   get_post_thumbnail_id($fp2->ID);
                    $f2attachment   =   wp_get_attachment_image_url($f2thumb, 'full');
                    $f2eaturedText  =   get_post_meta($fp2->ID, 'featured_text', true);
                    ?>
                    <div class="featured-block-item">
                        <div class="featuredimg"><img src="<?= $f2attachment ?>" class="img-fluid" alt="<?= ucfirst($fp2->post_title) ?>" /></div>
                        <div class="featured-content">
                            <span><?= ucfirst($f2eaturedText) ?></span>
                            <h3><a href="<?= site_url() ?>/channels/?type=p&id=<?= $fp2->ID ?>"><?= ucfirst($fp2->post_title) ?></a></h3>
                            <p><?= substr(strip_tags($fp2->post_content), 0, 90).'...' ?></p>
                            <a href="<?= site_url() ?>/channels/?type=p&id=<?= $fp2->ID ?>" class="btn btn-primary btn-large">Play <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }

        /**
         * Ready, Set, Remix
         */
        $featuredCat2 = $term = get_term_by('slug', 'ready-set-remix', 'featured');
        if(!empty($featuredCat2)){
            $featuredCat2ID   =   $featuredCat2->term_id;
            $featuredCat2Args =   [
                'post_type'         => 'playlist', 
                'post_status'       => 'publish', 
                'posts_per_page'    => 4,
                'orderby'           => 'date',
                'order'             => 'DESC',
                'tax_query'         => [
                    [
                        'taxonomy'  =>  'featured',
                        'field'     =>  'term_id',
                        'terms'     =>  $featuredCat2ID,
                    ],
                ],
            ];
            $featuredCat2posts    =   get_posts($featuredCat2Args);
            // echo '<pre>';
            // print_r($featuredCat2posts);
            // echo '</pre>';
            $viewsCounter2       =   0;
            if(!empty($featuredCat2posts)){
                foreach($featuredCat2posts as $at2){
                    $postviews2    = (int) get_post_meta($at2->ID, 'views', true);
                    $viewsCounter2 = (int) $viewsCounter2 + $postviews2;
                }
            }
            ?>
            <div class="featured-add">
                <div class="content-wrapper">
                    <div class="head-with-desc">
                        <h3><a href="<?= site_url() ?>/channels/?type=c&id=<?= $featuredCat2ID ?>"><?= ucfirst($featuredCat2->name) ?></a></h3>
                        <p><?= substr(strip_tags($featuredCat2->description), 0, 50).'...' ?></p>
                    </div>
                    <div class="box-data">
                        <?php 
                        if(is_user_logged_in()){
                            $userId             =   get_current_user_id();
                            $termlikesData      =   get_user_meta($userId, 'user_term_wishlist', true);
                            $termlikesDataArray =   (!empty($termlikesData) && is_array($termlikesData)) ? (array) $termlikesData : [];
                            if(!empty($termlikesDataArray) && in_array($featuredCat2ID, $termlikesDataArray)){
                                ?>
                                <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $featuredCat2ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike"></button>
                                <?php
                            }else{
                                ?>
                                <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $featuredCat2ID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                <?php
                            }
                        }else{
                            ?>
                            <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                            <?php
                        }
                        ?>
                        <button type="button" class="view"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $featuredCat2ID ?>"><img src="<?= get_template_directory_uri() ?>/images/view.png" class="img-fluid" alt="<?= $featuredCat2->name ?>"></a></button>
                        <span class="comments">± <?= TvadiFrontEnd::calculatePlaylistviews($viewsCounter2) ?></span>
                    </div>
                </div>
                <?php 
                if(!empty($featuredCat2posts)){
                    ?>
                    <div class="img-layout">
                        <div class="image-layout-wrapper">
                            <?php
                            foreach($featuredCat2posts as $fcp2){
                                $fcp2ID     =   $fcp2->ID;
                                $fcp2Thumb  =   get_post_thumbnail_id($fcp2ID);
                                $fcp2ImgUrl =   wp_get_attachment_image_url($fcp2Thumb);
                                ?>
                                <a href="<?= site_url() ?>/channels/?type=p&id=<?= $fcp2ID ?>" class="add-img"><img src="<?= $fcp2ImgUrl ?>" class="img-fluid" alt="<?= $fcp2->post_title ?>"/></a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
        <!-- <div class="featured-add">
            <div class="content-wrapper">
                <div class="head-with-desc">
                    <h3>Ready, Set, Remix</h3>
                    <p>Completely random and entertaining <br>clips from around the world.</p>
                </div>
                <div class="box-data">
                    <button type="button" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt=""></button>
                    <button type="button" class="view"><img src="<?= get_template_directory_uri() ?>/images/view.png" class="img-fluid" alt=""></button>
                    <span class="comments">± 3.1M</span>
                </div>
            </div>
            <div class="img-layout">
                <a href="" class="add-img"><img src="<?= get_template_directory_uri() ?>/images/tech-5.jpg" class="img-fluid" alt="" /></a>
                <a href="" class="add-img"><img src="<?= get_template_directory_uri() ?>/images/tech-6.jpg" class="img-fluid" alt="" /></a>
                <a href="" class="add-img"><img src="<?= get_template_directory_uri() ?>/images/tech-7.jpg" class="img-fluid" alt="" /></a>
                <a href="" class="add-img"><img src="<?= get_template_directory_uri() ?>/images/tech-8.jpg" class="img-fluid" alt="" /></a>
            </div>
        </div> -->
        
    </div>
</section>
<!-- FEATURED END -->

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

<!-- CURRENT LISTING START -->
<section class="current-listing">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Current Listings</h2>
                </div>
            </div>
        </div>
        <div class="listing-wrapper d-grid grid-2">
            <div class="maker-listing">
                <div class="heading-with-button d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Maker Listings</h5>
                    <a href="/makers/" class="btn btn-secondary btn-small">Discover All</a>
                </div>
                <div class="listing-sort shortlist">
                    <?php 
                    $makerArgs = [
                        'post_type'         =>     'makers_listing',
                        'post_status'       =>     'publish',
                        'posts_per_page'    =>     3,
                        'orderby'           =>     'date',
                        'order'             =>     'DESC',
                    ];
                    $latest_makers_listings = get_posts($makerArgs);
                    if(!empty($latest_makers_listings)){
                        foreach($latest_makers_listings as $ml){
                            $makerID        =   $ml->ID; // Get the current post ID
                            $mthumbnail_id  =   get_post_thumbnail_id($makerID);
                            $makerimgUrl    =   wp_get_attachment_image_src($mthumbnail_id, 'full')[0];
                            $price_from     =   (!empty(get_post_meta($makerID, 'price_from', true)))       ?   get_post_meta($makerID, 'price_from', true)         : '0';
                            $total_ratings  =   (!empty(get_post_meta($makerID, 'total_ratings', true)))    ?   get_post_meta($makerID, 'total_ratings', true)      : '0';
                            $average_rating =   (!empty(get_post_meta($makerID, 'average_rating', true)))   ?   get_post_meta($makerID, 'average_rating', true)     : '0';
                            ?>
                            <a href="<?= get_permalink($makerID) ?>" class="listing-item">
                                <div class="item-img"><img src="<?= $makerimgUrl ?>" class="img-fluid" alt="" /></div>
                                <div class="item-content">
                                    <h4 class="title"><?= $ml->post_title ?></h4>
                                    <p><?= substr(strip_tags($ml->post_content), 0, 90).'...' ?></p>
                                    <div class="inner-content">
                                        <div class="rating-comment">
                                            <span class="rating"><?= $average_rating ?> <img src="<?= get_template_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
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
            <div class="outlet-listing">
                <div class="heading-with-button d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Outlet Listings</h5>
                    <a href="/outlet/" class="btn btn-secondary btn-small">Discover All</a>
                </div>
                <div class="listing-sort shortlist">
                    <?php 
                    $outletArgs = [
                        'post_type'         =>     'outlet_listing',
                        'post_status'       =>     'publish',
                        'posts_per_page'    =>     3,
                        'orderby'           =>     'date',
                        'order'             =>     'DESC',
                    ];
                    $outlet_listings = get_posts($outletArgs);
                    if(!empty($outlet_listings)){
                        foreach($outlet_listings as $ol){
                            $outletID        =   $ol->ID; // Get the current post ID
                            $othumbnail_id   =   get_post_thumbnail_id($outletID);
                            $outletimgUrl    =   wp_get_attachment_image_src($othumbnail_id, 'full')[0];
                            $price_from      =   (!empty(get_post_meta($outletID, 'price_from', true)))       ?   get_post_meta($outletID, 'price_from', true)         : '0';
                            $total_ratings   =   (!empty(get_post_meta($outletID, 'total_ratings', true)))    ?   get_post_meta($outletID, 'total_ratings', true)      : '0';
                            $average_rating  =   (!empty(get_post_meta($outletID, 'average_rating', true)))   ?   get_post_meta($outletID, 'average_rating', true)     : '0';
                            ?>
                            <a href="<?= get_permalink($outletID) ?>" class="listing-item">
                                <div class="item-img"><img src="<?= $outletimgUrl ?>" class="img-fluid" alt="" /></div>
                                <div class="item-content">
                                    <h4 class="title"><?= $ol->post_title ?></h4>
                                    <p><?= substr(strip_tags($ol->post_content), 0, 90).'...' ?></p>
                                    <div class="inner-content">
                                        <div class="rating-comment">
                                            <span class="rating"><?= $average_rating ?> <img src="<?= get_template_directory_uri() ?>/images/star.svg" class="img-fluid" alt="" /></span>
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
<!-- CURRENT LISTING END -->

<!-- OUR LATEST ARTICLES START -->
<section class="latest-article">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-sm-6 col-lg-6">
                    <h2 class="title">Our Latest Articles</h2>
                </div>
                <div class="col-12 col-sm-6 col-lg-6">
                    <div class="text-end "><a href="#" class="btn btn-secondary btn-small">More Articles <img src="<?= get_template_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt="" /></a></div>
                </div>
            </div>
        </div>
        <div class="articles-wrapper d-grid grid-3">
            <?php 
            $articlesArgs = [
                'post_type'         =>  'post',
                'post_status'       =>  'publish',
                'posts_per_page'    =>  3,
                'orderby'           => 'date',
                'order'             => 'DESC',
            ];
            $articles = get_posts($articlesArgs); 
            // echo '<pre>'; print_r($articles); echo '</pre>';
            if(!empty($articles)){
                foreach($articles as $as){
                    $articleID        =   $as->ID; // Get the current post ID
                    $articlethumb     =   get_post_thumbnail_id($articleID);
                    $articleimgurl    =   wp_get_attachment_image_src($articlethumb, 'full')[0];
                    $postauthor       =   $as->post_author;
                    $author_data      =   get_userdata($postauthor);
                    $author_username  =   $author_data->display_name;
                    $postDate         =   (isset($as->post_date) && !empty($as->post_date)) ? date('j M', strtotime($as->post_date)) : '';
                    ?>
                    <a href="<?= get_permalink($articleID) ?>" class="article-item">
                        <div class="article-img"><img src="<?= $articleimgurl ?>" class="img-fluid" alt="" /></div>
                        <div class="date"><?= $postDate ?></div>
                        <div class="article-content">
                            <h4 class="title"><?= ucfirst($as->post_title) ?></h4>
                            <div class="posted">Posted by: <?= ucfirst($author_username) ?></div>
                            <div class="share-with-comment">
                                <button type="button" class="message"><img src="<?= get_template_directory_uri() ?>/images/message.png" class="img-fluid" alt="" /></button>
                                <button type="button" class="share"><img src="<?= get_template_directory_uri() ?>/images/share.png" class="img-fluid" alt="" /></button>
                            </div>
                        </div>
                    </a>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>
<!-- OUR LATEST ARTICLES END -->
<?php
get_footer();