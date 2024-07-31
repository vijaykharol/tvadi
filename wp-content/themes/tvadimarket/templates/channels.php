<?php 
/*
Template Name: Channels
*/
if(!defined('ABSPATH')){
    exit; 
}
get_header();
$contentType    =   (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) ? $_REQUEST['type'] : '';
$id             =   (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) ? $_REQUEST['id'] : '';
$videoCode      =   '';
if($contentType == 'p'){
    $playlist = get_post($id);
    if(!empty($playlist)){
        $videoCode = get_post_meta($playlist->ID, 'embeded_code', true);
    }
}else if($contentType == 'c'){
    // Retrieve the term
    $term       =   get_term($id);
    if(!empty($term)){
        $taxonomy   =   $term->taxonomy;
        $termid     =   $term->term_id;
       
       $args = [
            'post_type'         =>  'playlist',
            'post_status'       =>  'publish',
            'posts_per_page'    =>  1,
            'orderby'           =>  'date',
            'order'             =>  'DESC',
            'tax_query' => [
                [
                    'taxonomy'  =>  $taxonomy,
                    'field'     =>  'term_id',
                    'terms'     =>  $termid,
                ],
            ],
        ];

        $lastposts = get_posts($args);
        if(!empty($lastposts)){
            foreach($lastposts as $lp){
                $videoCode = get_post_meta($lp->ID, 'embeded_code', true);
                break;
            }
        }
    }
}else{
    $args = [
        'post_type'         =>  'playlist',
        'post_status'       =>  'publish',
        'posts_per_page'    =>  1,
        'orderby'           =>  'date',
        'order'             =>  'DESC',
    ];
    $lastposts = get_posts($args);
    if(!empty($lastposts)){
        foreach($lastposts as $lp){
            $videoCode = get_post_meta($lp->ID, 'embeded_code', true);
            break;
        }
    }
}
?>
<style>
    .video iframe {
        width: 100%;
        height: 650px;
    }
</style>
<!-- VIDEO PLAYER START -->
<section class="video-player">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="video">
                    <?= $videoCode ?>
                    <!-- <video width="100%" height="100%" autoplay controls loop muted> -->
                    <!-- <source src="https://youtu.be/QjvpjXdgugA" type="video/mp4"> -->
                        <!-- <source src="<?= get_stylesheet_directory_uri() ?>/images/video-dummy.ogg" type="video/ogg"> -->
                    <!-- </video> -->
                </div>
            </div>
        </div>
    </div>
</section>
<!-- VIDEO PLAYER END -->

<!-- CHANNELS START -->
<section class="channels-main">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Channels</h2>
                </div>
            </div>
        </div>
        <?php 
        $taxonomy = 'playlists_category';
        //Playlists Cateogries
        $playlists_categories = get_terms(
            [
                'taxonomy'      =>  'playlists_category',
                'hide_empty'    =>  false,
            ]
        );
        if(!empty($playlists_categories)){
            ?>
            <div class="channels-wrapper">
                <?php 
                foreach($playlists_categories as $pc){
                    $pcID = $pc->term_id;
                    $pcPostsArg =   [
                        'post_type'         => 'playlist', 
                        'post_status'       => 'publish', 
                        'posts_per_page'    => 4,
                        'orderby'           => 'date',
                        'order'             => 'DESC',
                        'tax_query'         => [
                            [
                                'taxonomy'  =>  'playlists_category',
                                'field'     =>  'term_id',
                                'terms'     =>  $pcID,
                            ],
                        ],
                    ];
                    $postdata = get_posts($pcPostsArg);
                    ?>
                    <div class="featured-add">
                        <div class="content-wrapper">
                            <div class="head-with-desc">
                                <h3><?= $pc->name ?></h3>
                                <p><?= $pc->description ?></p>
                            </div>
                            <span class="rating ms-auto">5 <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt=""></span>
                        </div>
                        <?php 
                        if(!empty($postdata)){
                            ?>
                            <div class="img-layout">
                                <div class="image-layout-wrapper">
                                    <?php 
                                        foreach($postdata as $pd){
                                            $postid         =   $pd->ID;
                                            $postthumb      =   get_post_thumbnail_id($postid);
                                            $postimgurl     =   wp_get_attachment_url($postthumb);
                                            ?>
                                            <a href="<?= site_url() ?>/channels/?type=p&id=<?= $postid ?>" class="add-img"><img src="<?= $postimgurl ?>" class="img-fluid" alt="<?= $pd->post_title ?>" /></a>
                                            <?php
                                        }
                                        ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <!--  -->
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
</section>
<!-- CHANNELS END -->

<!-- USER PLAYLIST START -->
<section class="user-playlist">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">User Playlists</h2>
                </div>
            </div>
        </div>
        <?php
        //Playlists Cateogries
        $userplaylists = get_terms(
            [
                'taxonomy'      =>  'user_playlist',
                'orderby'       =>  'id',
                'order'         =>  'DESC',
                'number'        =>  6, 
                'hide_empty'    =>  false,
            ]
        );
        if(!empty($userplaylists)){
            ?>
            <div class="trending-block-wrapper d-grid grid-3">
                <?php 
                foreach($userplaylists as $up){
                    $upID           =   $up->term_id;
                    $upPostsArg     =   [
                        'post_type'         => 'playlist', 
                        'post_status'       => 'publish', 
                        'posts_per_page'    => 4,
                        'orderby'           => 'date',
                        'order'             => 'DESC',
                        'tax_query'         => [
                            [
                                'taxonomy'  =>  'user_playlist',
                                'field'     =>  'term_id',
                                'terms'     =>  $upID,
                            ],
                        ],
                    ];
                    $uppostdata     =   get_posts($upPostsArg);
                    $viewsCounter   =   0;
                    ?>
                    <div class="trending-block-item grid-hover">
                        <?php 
                        if(!empty($uppostdata)){
                            ?>
                            <div class="grid-img d-grid grid-2">
                                <?php 
                                foreach($uppostdata as $upp){
                                    $upostid            =   $upp->ID;
                                    $postviews         =   (int) get_post_meta($upp->ID, 'views', true);
                                    $viewsCounter      =   (int) $viewsCounter + $postviews;
                                    $upthumb            =   get_post_thumbnail_id($upostid);
                                    $upimgurl           =   wp_get_attachment_url($upthumb);
                                    ?>
                                    <a href="<?= site_url() ?>/channels/?type=p&id=<?= $upostid ?>">
                                        <div class="gs-img"><img src="<?= $upimgurl ?>" class="img-fluid" alt="<?= $upp->title ?>" /></div>
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <h4 class="title"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $upID ?>"><?= ucfirst($up->name) ?></a></h4>
                        <div class="box-data">
                            <?php 
                            if(is_user_logged_in()){
                                $userId             =   get_current_user_id();
                                $termlikesData      =   get_user_meta($userId, 'user_term_wishlist', true);
                                $termlikesDataArray =   (!empty($termlikesData) && is_array($termlikesData)) ? (array) $termlikesData : [];
                                if(!empty($termlikesDataArray) && in_array($upID, $termlikesDataArray)){
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $upID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/Like.png" class="img-fluid" alt="Unlike"></button>
                                    <?php
                                }else{
                                    ?>
                                    <button type="button" class="wishlist" onClick="likeTrendingTerm(<?= $upID ?>, this)"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                    <?php
                                }
                            }else{
                                ?>
                                <button type="button" id="unlogged-wishlist-btn" data-bs-toggle="modal" data-bs-target="#wishlistmodal" class="wishlist"><img src="<?= get_template_directory_uri() ?>/images/wishlist.png" class="img-fluid" alt="Like"></button>
                                <?php
                            }
                            ?>
                            <button type="button" class="view"><a href="<?= site_url() ?>/channels/?type=c&id=<?= $upID ?>"><img src="<?= get_stylesheet_directory_uri() ?>/images/view.png" class="img-fluid" alt="<?= ucfirst($up->name) ?>"></a></button>
                            <span class="comments">± <?= TvadiFrontEnd::calculatePlaylistviews($viewsCounter) ?></span>
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
<!-- USER PLAYLIST END -->

<!-- Tvadi on Mobile & TV Apps Start -->
<section class="mobile-tv-apps">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Tvadi on Mobile & TV Apps</h2>
                </div>
            </div>
        </div>
        <div class="apps">
            <div class="row justify-content-center">
                <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                    <a href="" class="apps-img text-end googlplay"><img src="<?= get_stylesheet_directory_uri() ?>/images/google-play.png" class="img-fluid" alt="" /></a>
                </div>
                <div class="col-6 col-sm-6 col-md-6 col-lg-6">
                    <a href="" class="apps-img appstore"><img src="<?= get_stylesheet_directory_uri() ?>/images/app-strore.png" class="img-fluid" alt="" /></a>
                </div>
                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                    <a href="" class="apps-img"><img src="<?= get_stylesheet_directory_uri() ?>/images/firetv.png" class="img-fluid" alt="" /></a>
                </div>
                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                    <a href="" class="apps-img text-center"><img src="<?= get_stylesheet_directory_uri() ?>/images/roku.png" class="img-fluid" alt="" /></a>
                </div>
                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                    <a href="" class="apps-img text-end"><img src="<?= get_stylesheet_directory_uri() ?>/images/apple-tv.png" class="img-fluid" alt="" /></a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Tvadi on Mobile & TV Apps End -->
<?php
get_footer();