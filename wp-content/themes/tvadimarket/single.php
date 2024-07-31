<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package tvadimarket
 */

get_header();
?>

	<main id="primary" class="site-main">
		<div class="container-fluid">
			<div class="single-post-main">

				<?php
				while ( have_posts() ) :
					the_post();

					get_template_part( 'template-parts/content', get_post_type() );

					// the_post_navigation(
					// 	array(
					// 		'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'tvadimarket' ) . '</span>',
					// 		'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'tvadimarket' ) . '</span>',
					// 	)
					// );

					//signup button
					$id = get_the_ID();
					if((!is_user_logged_in()) && ($id == 494 || $id == 492)){
						?>
						<div class="sing-singup-section">
							<a href="/register/" class="btn btn-primary" id="single-post-singup">Sign up</a>
						</div>
						<?php
					}

					// If comments are open or we have at least one comment, load up the comment template.
					if( comments_open() || get_comments_number() ) :
						?>
						<div class="comment-s">
							<h3 id="custom-single-comment-toggle" style="cursor: pointer;">Contact Us <span class="custom-toggle-icon"><svg width="30" height="30" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.917 6.24996L22.917 38.7208L11.8899 27.6937C11.6972 27.4967 11.4674 27.3399 11.2137 27.2323C10.96 27.1248 10.6875 27.0686 10.412 27.0671C10.1364 27.0656 9.86332 27.1187 9.60847 27.2235C9.35361 27.3283 9.12206 27.4825 8.92724 27.6774C8.73241 27.8723 8.57817 28.1038 8.47346 28.3587C8.36875 28.6136 8.31564 28.8867 8.3172 29.1622C8.31877 29.4378 8.37498 29.7103 8.48259 29.964C8.59019 30.2176 8.74704 30.4474 8.94407 30.6401L23.5274 45.2234C23.9181 45.614 24.4479 45.8334 25.0003 45.8334C25.5527 45.8334 26.0826 45.614 26.4732 45.2234L41.0566 30.6401C41.2504 30.4468 41.404 30.2171 41.5088 29.9643C41.6136 29.7114 41.6673 29.4403 41.667 29.1666C41.6669 28.7546 41.5447 28.3519 41.3157 28.0094C41.0868 27.6669 40.7615 27.3999 40.3809 27.2423C40.0002 27.0846 39.5814 27.0434 39.1773 27.1237C38.7733 27.2041 38.4021 27.4024 38.1107 27.6937L27.0837 38.7208L27.0837 6.24996C27.0837 5.69742 26.8642 5.16752 26.4735 4.77682C26.0828 4.38612 25.5529 4.16663 25.0003 4.16663C24.4478 4.16663 23.9179 4.38612 23.5272 4.77682C23.1365 5.16752 22.917 5.69742 22.917 6.24996Z" fill="white"/></svg></span></h3>
							<div id="custom-single-comment-section" style="display: none;">
								<?php
								comments_template();
								?>
							</div>
						</div>
						<?php
					endif;

				endwhile; // End of the loop.
				?>
			</div>
		</div>

	</main><!-- #main -->

<?php
// get_sidebar();
get_footer();
