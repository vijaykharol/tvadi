<?php 
/*
* Template Name: Profile Page
*/
if(!defined('ABSPATH')){
    exit; 
}

$user_id     = (int) get_query_var('puserid');
if(empty($user_id)){
    header("Location: ".site_url()); 
    exit();
}

$userdata                   =   get_userdata($user_id);
$profile_picture            =   get_user_meta($user_id, 'profile_picture', true);
$profile_cover_picture      =   get_user_meta($user_id, 'profile_cover_picture', true);
$profile_info               =   get_user_meta($user_id, 'user_profile_info', true);
if(!empty($profile_picture)){
    $proImageurl = $profile_picture;
}else{
    $proImageurl = get_avatar_url($user_id);
}
get_header();
?>
<div class="user-profile">
    <!-- PROFILE DATA START -->
	<section class="profile-data">
		<div class="figure-image">
            <?php 
            if(!empty($profile_cover_picture)){
                ?>
                <img src="<?= $profile_cover_picture  ?>" class="img-fluid" alt="Cover Image"/>
                <?php
            }else{
                ?>
                <img src="/wp-content/plugins/helpful-login-register/front-end/assets/img/placeholder.jpg" class="img-fluid" alt="Cover Image"/>
                <?php
            }
            ?>
        </div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-12 col-lg-12">
					<div class="heading-with-image">
						<div class="img">
                            <img src="<?= $proImageurl ?>" class="img-fluid" alt="<?= $userdata->display_name ?>"/>
                        </div>
						<div class="heading-with-button">
							<h2 class="mb-0"><?= $userdata->display_name ?></h2>
							<a href="#" class="btn btn-primary btn-large">Contact <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""></a>
						</div>
					</div>
				</div>
			</div>
			<div class="row justify-content-center">
				<div class="col-12 col-xl-10">
					<div class="content">
						<p><?= $profile_info ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- PROFILE DATA END -->

	<!-- PORTFOLIO START -->
	<section class="portfolio" id="portfolio">
		<div class="container-fluid">
			<div class="global-heading">
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <h2 class="title heading-gap">Portfolio:</h2>
                    </div>
                </div>
            </div>
            <div class="portfolio-wrapper">
            	<div class="post-gap d-grid grid-3">
                    <?php 
                    $arguement = [
                        'post_type'         =>  'playlist',
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  3,
                        'orderby'           =>  'date',
                        'order'             =>  'DESC',    
                    ];
                    $play = get_posts($arguement);
                    if(!empty($play)){
                        foreach($play as $p){
                            $playimg = wp_get_attachment_url(get_post_thumbnail_id($p->ID), 'full');
                            ?>
                            <a href="/channels/?type=p&id=<?= $p->ID ?>" class="post-item hover-image">
                                <h3><?= $p->post_title ?></h3>
                                <div class="post-img"><img src="<?= $playimg ?>" class="img-fluid" alt="<?= $p->post_title ?>"/></div>
                            </a>
                            <?php
                        }
                    }
                    ?>
            	</div>
            	<div class="more mt-3 text-end"><a href="#" class="btn btn-primary btn-large">Contact <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""></a></div>
            </div>
		</div>
	</section>
	<!-- PORTFOLIO END -->

	<!-- MORE LISTING START -->
	<section class="more-listing pt-0 mb-5" id="my-listings">
		<div class="container-fluid">
			<div class="global-heading">
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <h2 class="title heading-gap">My Listings:</h2>
                    </div>
                </div>
            </div>
            <div class="more-listing-wrapper">
            	<div class="post-gap d-grid grid-3">
                    <?php
                    $data = [
                        'post_type'         =>  array('market_listing', 'makers_listing', 'outlet_listing'),
                        'post_status'       =>  'publish',
                        'posts_per_page'    =>  3,
                        'orderby'           =>  'rand',
                        'author'            =>  $user_id,
                    ];
                    $listings = get_posts($data);
                    if(!empty($listings)){
                        foreach($listings as $lis){
                            $imagesurl = wp_get_attachment_url(get_post_thumbnail_id($lis->ID), 'full');
                            ?>
                            <a href="<?= get_permalink($lis->ID) ?>" class="post-item hover-image">
                                <h3><?= $lis->post_title ?></h3>
                                <div class="post-img"><img src="<?= $imagesurl ?>" class="img-fluid" alt="<?= $lis->post_title ?>"/></div>
                            </a>
                            <?php
                        }
                    }
                    ?>
	            </div>
	            <div class="more mt-3 text-end"><a href="/make-listing/" class="btn btn-primary btn-large">Make <img src="<?= get_stylesheet_directory_uri() ?>/images/arrow.svg" class="img-fluid" alt=""></a></div>
            </div>
		</div>
	</section>
	<!-- MORE LISTING END -->
</div>
<?php
get_footer();
?>