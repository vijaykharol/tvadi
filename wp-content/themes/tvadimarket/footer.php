<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package tvadimarket
 */

?>
<!-- FOOTER START -->
<footer class="site-footer">
	<?php 
	if(is_user_logged_in() && !is_page('chat')){
		?>
		<div class="footer-chat-section">
			<!-- Chat Button Icon -->
			<div id="footer-chat-button">
				<button id="open-chat-model-btn"><img src="<?= get_template_directory_uri() ?>/images/chat-svgrepo-com.svg" alt="Chat" class="chaticon-img"/></button>
			</div>
			<!-- Chat Modal -->
			<div id="footer-chat-modal-tab-section">
				<div class="chat-modal-header">
					<span>Chat</span>
					<span id="chat-close-button">&times;</span>
				</div>
				<div class="chat-modal-content">
					<!-- Insert your chat shortcode or HTML here -->
					<div class="footer-chat-inner-tab-content">
						<?= do_shortcode('[tvadi_chat_model]') ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	?>
	<div class="footer-top">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12 col-md-6 col-lg-3">
					<div class="footer-widget">
						<div class="logo-widget">
							<?php 
							$custom_logo_id 	= 	get_theme_mod( 'custom_logo' );
							$logo_url 			= 	wp_get_attachment_image_url( $custom_logo_id, 'full' );
							if(has_custom_logo()){
								echo '<a class="navbar-brand p-0" href="'.esc_url( home_url( '/' ) ).'" rel="home">';
								echo '<img src="'.esc_url( $logo_url ).'" class="img-fluid" alt="'.esc_attr(get_bloginfo('name')).'">';
								echo '</a>';
							}else{
								?>
								<a class="navbar-brand p-0" href="<?= esc_url( home_url( '/' ) ) ?>"><img src="<?= get_template_directory_uri() ?>/images/footer-logo.jpg" class="img-fluid" alt="<?= esc_attr(get_bloginfo('name')) ?>" /></a>
								<?php
							}
							?>
						</div>
						<div class="footer-content">TV Platform & Media Marketplace</div>
					</div>
				</div>
				<div class="col-6 col-sm-4 col-md-6 col-lg-3">
					<div class="footer-widget">
						<h5>Useful Links</h5>
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
				<div class="col-6 col-sm-4 col-md-6 col-lg-3">
					<div class="footer-widget">
						<h5>Featured</h5>
						<?php 
						if(has_nav_menu('footer-featured-links')){
							wp_nav_menu([
								'theme_location' 	=> 	'footer-featured-links',
								'container' 		=> 	'ul',
								'container_class' 	=> 	'our-links',
								'menu_class' 		=> 	'our-links',
								'depth' 			=> 	1, 
							]);
						}
						?>
					</div>
				</div>
				<div class="col-12 col-sm-4 col-md-6 col-lg-3">
					<div class="footer-widget">
						<h5>Social Links</h5>
						<?php 
						if(has_nav_menu('footer-social-links')){
							wp_nav_menu([
								'theme_location' 	=> 	'footer-social-links',
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
		</div>
	</div>
	<div class="footer-bottom">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12 col-md-6 col-lg-6">
					<div class="copyright-text"><a href="<?= home_url() ?>">Tvadi</a> © Copyright 2024</div>
				</div>
				<div class="col-12 col-md-6 col-lg-6">
					<?php 
					if(has_nav_menu('footer-bottom-links')){
						wp_nav_menu([
							'theme_location' 	=> 	'footer-bottom-links',
							'container' 		=> 	'ul',
							'container_class' 	=> 	'our-links-bottom',
							'menu_class' 		=> 	'footer-bottom-end list-inline text-end',
							'depth' 			=> 	1, 
						]);
					}
					?>
				</div>
			</div>
		</div>
	</div>
</footer>
</div><!-- #page -->
<?php wp_footer(); ?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		new WOW().init();
	});
</script>
</body>
</html>
