<?php
/*
Template Name: Tools
*/
if(!defined('ABSPATH')){
    exit; 
}
if(!is_user_logged_in()){
    header("Location: /how-to-make-a-playlist/");
    exit();
}
get_header(); ?>
<!-- CATEGORIES START -->
<section class="categories-outlet tools">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-4 col-lg-4">
                <a href="" class="cb-item hover-image">
                    <h4 class="title">Get Started</h4>
                    <div class="cb-item-img"><img src="<?= get_stylesheet_directory_uri() ?>/images/tavadi-media-connect.png" class="img-fluid" alt="" /></div>
                </a>
            </div>
            <div class="col-12 col-md-4 col-lg-4">
                <a href="" class="cb-item hover-image">
                    <h4 class="title">Resources</h4>
                    <div class="cb-item-img"><img src="<?= get_stylesheet_directory_uri() ?>/images/open-space-estimates.png" class="img-fluid" alt="" /></div>
                </a>
            </div>
            <div class="col-12 col-md-4 col-lg-4">
                <a href="" class="cb-item hover-image">
                    <h4 class="title">Forum</h4>
                    <div class="cb-item-img"><img src="<?= get_stylesheet_directory_uri() ?>/images/auction.png" class="img-fluid" alt="" /></div>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- CATEGORIES END -->

<!-- PLAYLIST MAKER START -->
<section class="playlist-maker" id="playlist-maker">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Playlist Maker</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="playlist-maker-block"><img src="<?= get_stylesheet_directory_uri() ?>/images/coming-soon.jpg" class="img-fluid" alt="" /></div>
            </div>
        </div>
    </div>
</section>
<!-- PLAYLIST MAKER END -->

<!-- RESOURCES START -->
<section class="resources">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Resources</h2>
                </div>
            </div>
        </div>
        <div class="channels-wrapper">
            <?php 
            $ai_media_tools = get_term( 85, 'featured' );
            if(!empty($ai_media_tools)){
                $ai_id = $ai_media_tools->term_id;
                ?>
                <div class="featured-add">
                    <div class="content-wrapper">
                        <div class="head-with-desc">
                            <h3><a href="/channels/?type=c&id=<?= $ai_id ?>"></a><?= ucfirst($ai_media_tools->name) ?></h3>
                            <p><?= ucfirst($ai_media_tools->description) ?></p>
                        </div>
                        <span class="rating ms-auto">5 <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt=""></span>
                    </div>
                    <?php 
                    $args = [
                        'post_type'         =>  'playlist',
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  4,
                        'orderby'           =>  'date',
                        'order'             =>  'DESC',
                        'tax_query'         => [
                            [
                                'taxonomy' => 'featured',
                                'field'    => 'term_id',
                                'terms'    => $ai_id,
                            ],
                        ],
                    ];
                    $aiPosts = get_posts($args);
                    ?>
                    <div class="img-layout d-grid grid-4">
                        <?php 
                        if(!empty($aiPosts)){
                            foreach($aiPosts as $aip){
                                $ai_featuredImg = wp_get_attachment_url(get_post_thumbnail_id($aip->ID), 'full');
                                ?>
                                <a href="/channels/?type=p&id=<?= $aip->ID ?>" class="add-img"><img src="<?= $ai_featuredImg ?>" class="img-fluid" alt="<?= $aip->post_title ?>" /></a>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!--  -->
            <?php
            $outletEveryWhere = get_term( 86, 'featured' );
            if(!empty($outletEveryWhere)){
                $oterm_id = $outletEveryWhere->term_id;
                ?>
                <div class="featured-add">
                    <div class="content-wrapper">
                        <div class="head-with-desc">
                            <h3><?= ucfirst($outletEveryWhere->name) ?></h3>
                            <p><?= $outletEveryWhere->description ?></p>
                        </div>
                        <span class="rating ms-auto">5 <img src="<?= get_stylesheet_directory_uri() ?>/images/star.svg" class="img-fluid" alt=""></span>
                    </div>
                    <?php 
                    $args2 = [
                        'post_type'         =>  'playlist',
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  4,
                        'orderby'           =>  'date',
                        'order'             =>  'DESC',
                        'tax_query' => [
                            [
                                'taxonomy' => 'featured',
                                'field'    => 'term_id',
                                'terms'    => $oterm_id,
                            ],
                        ],
                    ];
                    $oposts = get_posts($args2);
                    ?>
                    <div class="img-layout d-grid grid-4">
                        <?php 
                        if(!empty($oposts)){
                            foreach($oposts as $op){
                                $featuredImg = wp_get_attachment_url(get_post_thumbnail_id($op->ID), 'full');
                                ?>
                                <a href="/channels/?type=p&id=<?= $op->ID ?>" class="add-img"><img src="<?= $featuredImg ?>" class="img-fluid" alt="<?= $op->post_title ?>" /></a>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <!--  -->
        </div>
    </div>
</section>
<!-- RESOURCES END -->

<!-- FORUM START -->
<section class="forum">
    <div class="container-fluid">
        <div class="global-heading">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <h2 class="title">Forum</h2>
                </div>
            </div>
        </div>
        <div class="forum-image" id="forum-section">
            <?= do_shortcode('[wpforo]'); ?>
        </div>
    </div>
</section>
<!-- FORUM END -->
<?php get_footer(); ?>