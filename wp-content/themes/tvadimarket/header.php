<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package tvadimarket
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?> <?php bloginfo('name'); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<!-- Sidebar -->
		<div id="tvadi-sidebar"  class="links-sidebar-main">
			<div class="links-sidebar">
				<div class="heading-with-btn">
					<h5 class="mb-0">Useful Links</h5>
					<a href="javascript:void(0)" class="closebtn" id="closeSidebarBtn">&times;</a>
				</div>
				<div class="sidebar-widget">
					<?php 
					if(has_nav_menu('footer-useful-links')){
						wp_nav_menu([
							'theme_location' 	=> 	'footer-useful-links',
							'container' 		=> 	'ul',
							'container_class' 	=> 	'our-links',
							'menu_class' 		=> 	'our-links',
							'depth' 			=> 	1, 
						]);
					}
					?>
				</div>
			</div>
		</div>
		<!-- HEADER START -->
		<header class="site-header">
			<nav class="navbar navbar-expand-xl p-0">
				<div class="container-fluid">
					<?php 
					$custom_logo_id 	= 	get_theme_mod( 'custom_logo' );
					$logo_url 			= 	wp_get_attachment_image_url( $custom_logo_id, 'full' );
					if(has_custom_logo()){
						echo '<a class="navbar-brand p-0" href="'.esc_url( home_url( '/' ) ).'" rel="home">';
						echo '<img src="'.esc_url( $logo_url ).'" class="img-fluid" alt="'.esc_attr(get_bloginfo('name')).'">';
						echo '</a>';
					}else{
						?>
						<a class="navbar-brand p-0" href="<?= esc_url( home_url( '/' ) ) ?>"><img src="<?= get_template_directory_uri() ?>/images/logo.png" class="img-fluid" alt="<?= esc_attr(get_bloginfo('name')) ?>" /></a>
						<?php
					}
					?>
					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<img src="<?= get_template_directory_uri() ?>/images/menu4sqW.png" class="img-fluid" alt="" />
					</button>
					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<?php
							wp_nav_menu(array(
								'theme_location' => 'header-menu',
								'container' => 'div',
								'container_class' => 'navbar_collapse_block',
								'container_id' => 'navbarNav',
								'menu_class' => 'navbar-nav',
								'fallback_cb' => '__return_false',
								'depth' => 2,
								'walker' => new Custom_Nav_Walker(),
								'link_before' => '',
								'link_after' => ''
							));
                		?>
					</div>
					<div class="header-end d-flex align-items-center">
						<form class="search-form">
							<input class="form-control" type="search" placeholder="Search" aria-label="Search">
							<button class="btn btn-search" type="submit">
								<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 461.516 461.516" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M185.746 371.332a185.294 185.294 0 0 0 113.866-39.11L422.39 455c9.172 8.858 23.787 8.604 32.645-.568 8.641-8.947 8.641-23.131 0-32.077L332.257 299.577c62.899-80.968 48.252-197.595-32.716-260.494S101.947-9.169 39.048 71.799-9.204 269.394 71.764 332.293a185.64 185.64 0 0 0 113.982 39.039zM87.095 87.059c54.484-54.485 142.82-54.486 197.305-.002s54.486 142.82.002 197.305-142.82 54.486-197.305.002l-.002-.002c-54.484-54.087-54.805-142.101-.718-196.585l.718-.718z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg>
							</button>
						</form>
						<div class="togglebar-right">
							<button type="button" class="togglebar-btn" id="opensidebarBtn">
								<span class="togglebar"></span>
								<span class="togglebar"></span>
								<span class="togglebar"></span>
							</button>
						</div>
						
						
						<div class="dropdown">
							<a href="javascript:;" class="user-login-btn"  type="button" id="dropdownMenuButton22" data-bs-toggle="dropdown" aria-expanded="false">
								<img src="<?= get_template_directory_uri() ?>/images/user.png" class="img-fluid" />
							</a>
							<ul class="dropdown-menu user-menu" aria-labelledby="dropdownMenuButton22">
								<?php 
								if(!is_user_logged_in()){
									?>
									<li><a class="dropdown-item" href="<?= site_url() ?>/login/">Login</a></li>
									<?php
								}else{
									?>
									<li><a class="dropdown-item" href="/dashboard/?dpage=profile">Profile</a></li>
									<?php 
									$hcurrent_user = get_current_user_id();
									$args_p = [
										'post_type' 		=> 	'playlist',
										'posts_per_page' 	=>	-1,
										'author' 			=> 	$hcurrent_user,
									];
									$userplaylists = get_posts($args_p);
									$numplaylists = (!empty($userplaylists) && is_array($userplaylists)) ? count($userplaylists) : 0;
									if($numplaylists > 0){
										?>
										<li><a class="dropdown-item" href="/profile/<?= $hcurrent_user ?>/#portfolio">Playlists</a></li>
										<?php
									}else{
										?>
										<li><a class="dropdown-item" href="/tools/#playlist-maker">Playlists</a></li>
										<?php
									}

									$args_data_l = [
										'post_type'         =>  array('market_listing', 'makers_listing', 'outlet_listing'),
										'posts_per_page'    =>  -1,
										'author'            =>  $hcurrent_user,
									];
									$useRlistings = get_posts($args_data_l);
									$numuserlists = (!empty($useRlistings) && is_array($useRlistings)) ? count($useRlistings) : 0;
									if($numuserlists > 0){
										?>
										<li><a class="dropdown-item" href="/profile/<?= $hcurrent_user ?>/#my-listings">Listings</a></li>
										<?php
									}else{
										?>
										<li><a class="dropdown-item" href="/make-listing/">Listings</a></li>
										<?php
									}
									?>
									<li><a class="dropdown-item" href="<?= wp_logout_url(home_url()) ?>">Logout</a></li>
									<?php
								}
								?>
							</ul>
						</div>
						<?php 
						/*
						if(!is_user_logged_in()){
							?>
							<a href="<?= site_url() ?>/login/" class="user-login-btn">
								<img src="<?= get_template_directory_uri() ?>/images/user.png" class="img-fluid" />
							</a>
							<?php
						}else{
							?>
							<a href="<?= site_url() ?>/dashboard/" class="user-login-btn">
								<img src="<?= get_template_directory_uri() ?>/images/user.png" class="img-fluid" />
							</a>
							<?php
						}
						*/
						?>
						<a href="/channels/" class="tv-wrapper">
							<img src="<?= get_template_directory_uri() ?>/images/tv.png" class="img-fluid" />
						</a>
					</div>
				</div>
			</nav>
		</header>
		<!-- HEADER END -->

		<!-- Modal Structure -->
        <div class="modal fade" id="wishlistmodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body">
                        <h2>Message</h2>
                        <p>If you want to add in wishlist you have to login.
                            <a href="<?= site_url() ?>/login/">Click Here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
