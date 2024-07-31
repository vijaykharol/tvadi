<?php
/**
 * tvadimarket functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package tvadimarket
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function tvadimarket_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on tvadimarket, use a find and replace
		* to change 'tvadimarket' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'tvadimarket', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'tvadimarket' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'tvadimarket_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'tvadimarket_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function tvadimarket_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'tvadimarket_content_width', 640 );
}
add_action( 'after_setup_theme', 'tvadimarket_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function tvadimarket_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'tvadimarket' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'tvadimarket' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'tvadimarket_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function tvadimarket_scripts() {
	wp_enqueue_style( 'tvadimarket-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'tvadimarket-style', 'rtl', 'replace' );

	wp_enqueue_script( 'tvadimarket-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'tvadimarket_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load class Custom_Nav_Walker
 */
require get_template_directory() . '/inc/header-nav-walker.php';

/**
 * lOAD TVADI CLASSES
 */
require get_template_directory() . '/inc/classes/class.tvadi-frontend.php';
require get_template_directory() . '/inc/classes/class.tvadi-make-listing.php';
require get_template_directory() . '/inc/classes/class.tvadi-operations.php';
require get_template_directory() . '/inc/classes/class.chat-process-handler.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * ENQUEUE FILES
 */

function tvadi_enqueue_scripts_styles() {
    // Enqueue Theme stylesheets
    wp_enqueue_style('tvadi-bootstrap-min-css', get_template_directory_uri() . '/css/bootstrap.min.css', array(), time(), 'all');

	wp_enqueue_style('tvadi-main-css', get_template_directory_uri() . '/css/main.css', array(), time(), 'all');

	wp_enqueue_style('tvadi-owl-carousel-min', get_template_directory_uri() . '/css/owl.carousel.min.css', array(), time(), 'all');

	wp_enqueue_style('tvadi-owl-theme-default-min', get_template_directory_uri() . '/css/owl.theme.default.min.css', array(), time(), 'all');

	wp_enqueue_style('tvadi-animate-min', get_template_directory_uri() . '/css/animate.min.css', array(), time(), 'all');

	wp_enqueue_style('tvadi-responsive', get_template_directory_uri() . '/css/responsive.css', array(), time(), 'all');

	// Enqueue Theme scripts
    wp_enqueue_script('jquery');

    // Enqueue jQuery UI
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-slider'); // Enqueue the slider component

	wp_enqueue_script('tvadi-popper-min', get_template_directory_uri() . '/js/popper.min.js', array('jquery'), time(), true);

	wp_enqueue_script('tvadi-bootstrap-min-js', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), time(), true);

	wp_enqueue_script('tvadi-owl-carousel-min-js', get_template_directory_uri() . '/js/owl.carousel.min.js', array('jquery'), time(), true);

	wp_enqueue_script('tvadi-wow-min-js', get_template_directory_uri() . '/js/wow.min.js', array('jquery'), time(), true);

	wp_enqueue_script('tvadi-script-js', get_template_directory_uri() . '/js/script.js', array('jquery'), time(), true);
	// Pass AJAX URL to script
	wp_localize_script('tvadi-script-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

	wp_enqueue_script('tvadi-make-listing-js', get_template_directory_uri() . '/js/make-listing.js', array('jquery'), time(), true);
	// Pass AJAX URL to script
	wp_localize_script('tvadi-make-listing-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'stylesheet_dir' => get_stylesheet_directory_uri()));

}
add_action('wp_enqueue_scripts', 'tvadi_enqueue_scripts_styles');


// add_action('init', 'check_user_activity');

function check_user_activity(){


    if(is_user_logged_in()){
        $user = wp_get_current_user();

		// if (!in_array('administrator', $user->roles)) {
			$last_activity = get_user_meta($user->ID, 'last_activity_time', true);

			if($last_activity && is_numeric($last_activity)){ 
				$timeout_minutes = 30; 
				$inactive_timeout = $timeout_minutes * 60; 
				$current_time = current_time('timestamp');								
				if(is_numeric($last_activity)){
					$time_since_last_activity = $current_time - $last_activity;
				}else{            
					$time_since_last_activity = PHP_INT_MAX; // Set a large value to force logout
				}
				if($time_since_last_activity > $inactive_timeout){
					wp_logout();
				}
			}else{            
				wp_logout();
			}
		// }
    }
}


add_action('wp_login', 'update_user_last_activity', 10, 2);
function update_user_last_activity($user_login, $user){
    if(!empty($user)){		           
        update_user_meta($user->ID, 'last_activity_time', current_time('timestamp'));
		update_user_meta($user->ID, 'is_online', '1');		
    }
}

// Update user status on logout
function update_user_status_on_logout(){
    $user_id = get_current_user_id();
    if($user_id){
        update_user_meta($user_id, 'is_online', '0');
    }
}
add_action('clear_auth_cookie', 'update_user_status_on_logout');

/**
 * MAIN HEADER MENU
 */
function tvadi_theme_child_menus(){
    register_nav_menus(array(
        'header-menu' => __('Header Menu', 'tvadimarket')
    ));
}
add_action('init', 'tvadi_theme_child_menus');

/**
 * Footer Social links
 */
function tvadi_f_social_links_menus_cb(){
    register_nav_menus(array(
        'footer-social-links' => __('Footer Social Links', 'tvadimarket')
    ));
}
add_action('init', 'tvadi_f_social_links_menus_cb');

/**
 * FOOTER FEATURED MENUS
 */
function tvadi_f_featured_links_menus_cb(){
    register_nav_menus(array(
        'footer-featured-links' => __('Footer Featured Links', 'tvadimarket')
    ));
}
add_action('init', 'tvadi_f_featured_links_menus_cb');

/**
 * FOOTER Useful Links
 */
function tvadi_f_useful_links_menus_cb(){
    register_nav_menus(array(
        'footer-useful-links' => __('Footer Useful Links', 'tvadimarket')
    ));
}
add_action('init', 'tvadi_f_useful_links_menus_cb');

/**
 * Footer Bottom Links
 */
function tvadi_f_bottom_links_menus_cb(){
    register_nav_menus(array(
        'footer-bottom-links' => __('Footer Bottom Links', 'tvadimarket')
    ));
}
add_action('init', 'tvadi_f_bottom_links_menus_cb');

// Check if a user is online by user ID
function is_user_online($user_id){
    $is_online = get_user_meta($user_id, 'is_online', true);
    return $is_online === '1';
}
