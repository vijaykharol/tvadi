<?php

use Pods\Admin\Config\Pod as Config_Pod;
use Pods\Admin\Config\Group as Config_Group;
use Pods\Admin\Config\Field as Config_Field;
use Pods\Admin\Settings;
use Pods\Tools\Repair;
use Pods\Whatsit\Pod;

/**
 * @package Pods
 */
class PodsAdmin {

	/**
	 * @var PodsAdmin
	 */
	public static $instance = null;

	/**
	 * Singleton handling for a basic pods_admin() request
	 *
	 * @return \PodsAdmin
	 *
	 * @since 2.3.5
	 */
	public static function init() {

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new PodsAdmin();
		}

		return self::$instance;
	}

	/**
	 * Setup and Handle Admin functionality
	 *
	 * @return \PodsAdmin
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct() {

		// Scripts / Stylesheets
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ), 20 );

		// AJAX $_POST fix
		add_action( 'admin_init', array( $this, 'admin_init' ), 9 );

		// Menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );

		// AJAX for Admin
		add_action( 'wp_ajax_pods_admin', array( $this, 'admin_ajax' ) );
		add_action( 'wp_ajax_nopriv_pods_admin', array( $this, 'admin_ajax' ) );

		// Add Media Bar button for Shortcode
		add_action( 'media_buttons', array( $this, 'media_button' ), 12 );

		// Add the Pods capabilities
		add_filter( 'members_get_capabilities', array( $this, 'admin_capabilities' ) );

		add_action( 'admin_head-media-upload-popup', array( $this, 'register_media_assets' ) );

		// Add our debug to Site Info.
		add_filter( 'debug_information', array( $this, 'add_debug_information' ) );

		// Add our status tests.
		add_filter( 'site_status_tests', [ $this, 'site_status_tests' ] );

		$this->rest_admin();

	}

	/**
	 * Init the admin area
	 *
	 * @since 2.0.0
	 */
	public function admin_init() {

		// Fix for plugins that *don't do it right* so we don't cause issues for users
		// @codingStandardsIgnoreLine
		if ( defined( 'DOING_AJAX' ) && ! empty( $_POST ) ) {
			$pods_admin_ajax_actions = array(
				'pods_admin',
				'pods_relationship',
				'pods_upload',
				'pods_admin_components',
			);

			/**
			 * Admin AJAX Callbacks
			 *
			 * @since unknown
			 *
			 * @param array $pods_admin_ajax_actions Array of actions to handle.
			 */
			$pods_admin_ajax_actions = apply_filters( 'pods_admin_ajax_actions', $pods_admin_ajax_actions );

			if ( in_array( pods_v( 'action' ), $pods_admin_ajax_actions, true ) || in_array( pods_v( 'action', 'post' ), $pods_admin_ajax_actions, true ) ) {
				// @codingStandardsIgnoreLine
				foreach ( $_POST as $key => $value ) {
					if ( 'action' === $key || 0 === strpos( $key, '_podsfix_' ) ) {
						continue;
					}

					// @codingStandardsIgnoreLine
					unset( $_POST[ $key ] );

					// @codingStandardsIgnoreLine
					$_POST[ '_podsfix_' . $key ] = $value;
				}
			}
		}//end if
	}

	/**
	 * Attach requirements to admin header
	 *
	 * @since 2.0.0
	 */
	public function admin_head() {
		wp_register_script( 'pods-upgrade', PODS_URL . 'ui/js/jquery.pods.upgrade.js', array(), PODS_VERSION );

		$page = sanitize_text_field( pods_v( 'page' ) );

		if ( empty( $page ) ) {
			return;
		}

		if ( 'pods' !== $page && 0 !== strpos( $page, 'pods-' ) ) {
			return;
		}
		?>
		<script>
			if ( 'undefined' === typeof PODS_URL ) {
				const PODS_URL = '<?php echo esc_js( PODS_URL ); ?>';
			}
		</script>
		<?php

		// To be phased out.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		// To be replaced.
		wp_enqueue_script( 'jquery-qtip2' );
		wp_enqueue_script( 'pods-qtip-init' );

		// To be phased out.
		wp_enqueue_script( 'pods' );

		$action = sanitize_text_field( pods_v( 'action', 'get', 'manage' ) );

		if (
			0 === strpos( $page, 'pods-manage-' )
			|| 0 === strpos( $page, 'pods-add-new-' )
			|| 0 === strpos( $page, 'pods-settings-' )
		) {
			wp_enqueue_script( 'post' );
		} elseif (
			in_array( $page, [ 'pods', 'pods-add-new', 'pods-packages', 'pods-wizard', 'pods-upgrade' ], true )
			|| in_array( $action, [ 'add', 'manage' ], true )
		) {
			wp_enqueue_style( 'pods-wizard' );

			if ( 'pods-upgrade' === $page ) {
				wp_enqueue_script( 'pods-upgrade' );
			}
		}

		wp_enqueue_script( 'pods-dfv' );
		wp_enqueue_style( 'pods-styles' );
	}

	/**
	 * Build the admin menus
	 *
	 * @since 2.0.0
	 */
	public function admin_menu() {

		$advanced_content_types = PodsMeta::$advanced_content_types;
		$taxonomies             = PodsMeta::$taxonomies;
		$settings               = PodsMeta::$settings;

		if ( ! PodsInit::$upgrade_needed || ( pods_is_admin() && 1 === (int) pods_v( 'pods_upgrade_bypass' ) ) ) {
			$submenu_items = array();

			if ( ! empty( $advanced_content_types ) ) {
				$submenu = array();

				$pods_pages = 0;

				foreach ( (array) $advanced_content_types as $pod ) {
					if ( ! $pod instanceof Pod ) {
						continue;
					} elseif ( ! pods_is_admin(
						array(
							'pods',
							'pods_content',
							'pods_add_' . $pod['name'],
							'pods_edit_' . $pod['name'],
							'pods_delete_' . $pod['name'],
						)
					) ) {
						continue;
					}

					$pod_name = $pod['name'];

					$pod = apply_filters( "pods_advanced_content_type_pod_data_{$pod_name}", $pod, $pod['name'] );
					$pod = apply_filters( 'pods_advanced_content_type_pod_data', $pod, $pod['name'] );

					if ( 1 === (int) pods_v( 'show_in_menu', $pod['options'], 0 ) ) {
						$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );
						$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

						$menu_label = pods_v( 'menu_name', $pod['options'], $page_title, true );
						$menu_label = strip_tags( $menu_label );
						$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

						$singular_label = pods_v( 'label_singular', $pod['options'], pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true ), true );
						$plural_label   = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );

						$menu_location        = pods_v( 'menu_location', $pod['options'], 'objects' );
						$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );

						$menu_position = pods_v( 'menu_position', $pod['options'], '', true );
						$menu_icon     = pods_evaluate_tags( pods_v( 'menu_icon', $pod['options'], '', true ), true );

						if ( empty( $menu_position ) ) {
							$menu_position = null;
						}

						$parent_page = null;

						if ( pods_is_admin(
							array(
								'pods',
								'pods_content',
								'pods_edit_' . $pod['name'],
								'pods_delete_' . $pod['name'],
							)
						) ) {
							if ( ! empty( $menu_location_custom ) ) {
								if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
									$submenu_items[ $menu_location_custom ] = array();
								}

								$submenu_items[ $menu_location_custom ][] = array(
									$menu_location_custom,
									$page_title,
									$menu_label,
									'read',
									'pods-manage-' . $pod['name'],
									array( $this, 'admin_content' ),
								);

								continue;
							} else {
								$pods_pages ++;

								$page        = 'pods-manage-' . $pod['name'];
								$parent_page = $page;

								if ( empty( $menu_position ) ) {
									$menu_position = null;
								}
								add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );

								$all_title = $plural_label;
								$all_label = pods_v( 'label_all_items', $pod['options'], __( 'All', 'pods' ) . ' ' . $plural_label );

								if ( pods_v( 'page' ) === $page ) {
									if ( 'edit' === pods_v( 'action', 'get', 'manage' ) ) {
										$all_title = pods_v( 'label_edit_item', $pod['options'], __( 'Edit', 'pods' ) . ' ' . $singular_label );
									} elseif ( 'add' === pods_v( 'action', 'get', 'manage' ) ) {
										$all_title = pods_v( 'label_add_new_item', $pod['options'], sprintf( __( 'Add New %s', 'pods' ), $singular_label ) );
									}
								}

								add_submenu_page(
									$parent_page, $all_title, $all_label, 'read', $page, array(
										$this,
										'admin_content',
									)
								);
							}//end if
						}//end if

						if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod['name'] ) ) ) {
							$page = 'pods-add-new-' . $pod['name'];

							if ( null === $parent_page ) {
								$pods_pages ++;

								$parent_page = $page;

								if ( empty( $menu_position ) ) {
									$menu_position = null;
								}
								add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );
							}

							$add_title = pods_v( 'label_add_new_item', $pod['options'], sprintf( __( 'Add New %s', 'pods' ), $singular_label ) );
							$add_label = pods_v( 'label_add_new', $pod['options'], sprintf( __( 'Add New %s', 'pods' ), $singular_label ) );

							add_submenu_page(
								$parent_page, $add_title, $add_label, 'read', $page, array(
									$this,
									'admin_content',
								)
							);
						}//end if
					} elseif ( 1 === (int) pods_v( 'use_submenu_fallback', $pod['options'], 1 ) ) {
						$submenu[] = $pod;
					}//end if
				}//end foreach

				$submenu = apply_filters( 'pods_admin_menu_secondary_content', $submenu );

				if ( ! empty( $submenu ) && ( ! defined( 'PODS_DISABLE_CONTENT_MENU' ) || ! PODS_DISABLE_CONTENT_MENU ) ) {
					$parent_page = null;

					foreach ( $submenu as $item ) {
						$singular_label = pods_v( 'label_singular', $item['options'], pods_v( 'label', $item, ucwords( str_replace( '_', ' ', $item['name'] ) ), true ), true );
						$plural_label   = pods_v( 'label', $item, ucwords( str_replace( '_', ' ', $item['name'] ) ), true );

						if ( pods_is_admin(
							array(
								'pods',
								'pods_content',
								'pods_edit_' . $item['name'],
								'pods_delete_' . $item['name'],
							)
						) ) {
							$page = 'pods-manage-' . $item['name'];

							if ( null === $parent_page ) {
								$parent_page = $page;

								add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, pods_svg_icon( 'pods' ), '58.5' );
							}

							$all_title = $plural_label;
							$all_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

							if ( pods_v( 'page' ) === $page ) {
								if ( 'edit' === pods_v( 'action', 'get', 'manage' ) ) {
									$all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
								} elseif ( 'add' === pods_v( 'action', 'get', 'manage' ) ) {
									$all_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
								}
							}

							add_submenu_page(
								$parent_page, $all_title, $all_label, 'read', $page, array(
									$this,
									'admin_content',
								)
							);
						} elseif ( current_user_can( 'pods_add_' . $item['name'] ) ) {
							$page = 'pods-add-new-' . $item['name'];

							if ( null === $parent_page ) {
								$parent_page = $page;

								add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, pods_svg_icon( 'pods' ), '58.5' );
							}

							$add_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
							$add_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

							add_submenu_page(
								$parent_page, $add_title, $add_label, 'read', $page, array(
									$this,
									'admin_content',
								)
							);
						}//end if
					}//end foreach
				}//end if
			}//end if

			if ( ! empty( $taxonomies ) ) {
				foreach ( (array) $taxonomies as $pod ) {
					// Default taxonomy capability
					$capability = 'manage_categories';

					if ( ! empty( $pod['options']['capability_type'] ) ) {
						if ( 'custom' === $pod['options']['capability_type'] && ! empty( $pod['options']['capability_type_custom'] ) ) {
							$capability = 'manage_' . (string) $pod['options']['capability_type_custom'] . '_terms';
						}
					}

					// Check capabilities.
					if ( ! pods_is_admin(
						array(
							'pods',
							'pods_content',
							'pods_edit_' . $pod['name'],
							$capability,
						)
					) ) {
						continue;
					}

					// Check UI settings
					if ( 1 !== (int) pods_v( 'show_ui', $pod['options'], 0 ) || 1 !== (int) pods_v( 'show_in_menu', $pod['options'], 0 ) ) {
						continue;
					}

					$menu_location = pods_v( 'menu_location', $pod['options'], 'default' );

					if ( 'default' === $menu_location ) {
						continue;
					}

					$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );
					$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

					$menu_label = pods_v( 'menu_name', $pod['options'], $page_title, true );
					$menu_label = strip_tags( $menu_label );
					$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

					$menu_icon            = pods_evaluate_tags( pods_v( 'menu_icon', $pod['options'], '', true ), true );
					$menu_slug            = 'edit-tags.php?taxonomy=' . $pod['name'];
					$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );

					$menu_position = pods_v( 'menu_position', $pod['options'], '', true );
					if ( empty( $menu_position ) ) {
						$menu_position = null;
					}

					$taxonomy_data = get_taxonomy( $pod['name'] );

					foreach ( (array) $taxonomy_data->object_type as $post_type ) {
						if ( 'post' === $post_type ) {
							remove_submenu_page( 'edit.php', $menu_slug );
						} elseif ( 'attachment' === $post_type ) {
							remove_submenu_page( 'upload.php', $menu_slug . '&amp;post_type=' . $post_type );
						} else {
							remove_submenu_page( 'edit.php?post_type=' . $post_type, $menu_slug . '&amp;post_type=' . $post_type );
						}
					}

					if ( 'settings' === $menu_location ) {
						add_options_page( $page_title, $menu_label, 'read', $menu_slug );
					} elseif ( 'appearances' === $menu_location ) {
						add_theme_page( $page_title, $menu_label, 'read', $menu_slug );
					} elseif ( 'objects' === $menu_location || 'top' === $menu_location ) {
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, '', $menu_icon, $menu_position );
					} elseif ( 'submenu' === $menu_location && ! empty( $menu_location_custom ) ) {
						if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
							$submenu_items[ $menu_location_custom ] = array();
						}

						$submenu_items[ $menu_location_custom ][] = array(
							$menu_location_custom,
							$page_title,
							$menu_label,
							'read',
							$menu_slug,
							'',
						);
					}//end if
				}//end foreach
			}//end if

			if ( ! empty( $settings ) ) {
				foreach ( (array) $settings as $pod ) {
					if ( ! pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod['name'] ) ) ) {
						continue;
					}

					$page_title = pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true );
					$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

					$menu_label = pods_v( 'menu_name', $pod['options'], $page_title, true );
					$menu_label = strip_tags( $menu_label );
					$menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

					$menu_icon = pods_evaluate_tags( pods_v( 'menu_icon', $pod['options'], '', true ), true );

					$menu_position = pods_v( 'menu_position', $pod['options'], '', true );
					if ( empty( $menu_position ) ) {
						$menu_position = null;
					}

					$menu_slug            = 'pods-settings-' . $pod['name'];
					$menu_location        = pods_v( 'menu_location', $pod['options'], 'settings' );
					$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );
					$menu_callback        = array( $this, 'admin_content_settings' );

					if ( 'settings' === $menu_location ) {
						add_options_page( $page_title, $menu_label, 'read', $menu_slug, $menu_callback, $menu_position );
					} elseif ( 'appearances' === $menu_location ) {
						add_theme_page( $page_title, $menu_label, 'read', $menu_slug, $menu_callback, $menu_position );
					} elseif ( 'objects' === $menu_location || 'top' === $menu_location ) {
						add_menu_page( $page_title, $menu_label, 'read', $menu_slug, $menu_callback, $menu_icon, $menu_position );
					} elseif ( 'submenu' === $menu_location && ! empty( $menu_location_custom ) ) {
						if ( ! isset( $submenu_items[ $menu_location_custom ] ) ) {
							$submenu_items[ $menu_location_custom ] = array();
						}

						$submenu_items[ $menu_location_custom ][] = array(
							$menu_location_custom,
							$page_title,
							$menu_label,
							'read',
							$menu_slug,
							$menu_callback,
							$menu_position,
						);
					}//end if
				}//end foreach
			}//end if

			foreach ( $submenu_items as $items ) {
				foreach ( $items as $item ) {
					call_user_func_array( 'add_submenu_page', $item );
				}
			}

			$edit_pods_title = null;

			if ( 'pods' === pods_v( 'page' ) && 'edit' === pods_v( 'action' ) ) {
				$edit_pods_title = __( 'Edit Pod', 'pods' );
			}

			$admin_menus = array(
				'pods'            => array(
					'label'    => __( 'Edit Pods', 'pods' ),
					'title'    => $edit_pods_title,
					'function' => array( $this, 'admin_setup' ),
					'access'   => 'pods',
				),
				'pods-add-new'    => array(
					'label'    => __( 'Add New', 'pods' ),
					'title'    => __( 'Add New Pod', 'pods' ),
					'function' => array( $this, 'admin_setup' ),
					'access'   => 'pods',
				),
				'pods-components' => array(
					'label'    => __( 'Components', 'pods' ),
					'title'    => __( 'Pods Components', 'pods' ),
					'function' => array( $this, 'admin_components' ),
					'access'   => 'pods_components',
				),
				'pods-access-rights-review' => array(
					'label'    => __( 'Review Access Rights', 'pods' ),
					'title'    => __( 'Pods Review Access Rights', 'pods' ),
					'function' => array( $this, 'admin_access_rights_review' ),
					'access'   => 'pods',
				),
				'pods-settings'   => array(
					'label'    => __( 'Settings', 'pods' ),
					'title'    => __( 'Pods Settings', 'pods' ),
					'function' => array( $this, 'admin_settings' ),
					'access'   => 'pods_settings',
				),
				'pods-help'       => array(
					'label'    => __( 'Help', 'pods' ),
					'title'    => __( 'Pods Help', 'pods' ),
					'function' => array( $this, 'admin_help' ),
				),
			);

			add_filter( 'parent_file', array( $this, 'parent_file' ) );
		} else {
			$admin_menus = array(
				'pods-upgrade'  => array(
					'label'    => __( 'Upgrade', 'pods' ),
					'function' => array( $this, 'admin_upgrade' ),
					'access'   => 'manage_options',
				),
				'pods-settings' => array(
					'label'    => __( 'Settings', 'pods' ),
					'title'    => __( 'Pods Settings', 'pods' ),
					'function' => array( $this, 'admin_settings' ),
					'access'   => 'pods_settings',
				),
				'pods-help'     => array(
					'label'    => __( 'Help', 'pods' ),
					'title'    => __( 'Pods Help', 'pods' ),
					'function' => array( $this, 'admin_help' ),
				),
			);

			add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
		}//end if

		/**
		 * Add or change Pods Admin menu items
		 *
		 * @param array $admin_menus The submenu items in Pods Admin menu.
		 *
		 * @since  unknown
		 */
		$admin_menus = apply_filters( 'pods_admin_menu', $admin_menus );

		$parent = false;

		// PODS_LIGHT disables all Pods components so remove the components menu
		if ( pods_light() ) {
			unset( $admin_menus['pods-components'] );
		}

		if ( ! empty( $admin_menus ) && ( ! defined( 'PODS_DISABLE_ADMIN_MENU' ) || ! PODS_DISABLE_ADMIN_MENU ) ) {
			foreach ( $admin_menus as $page => $menu_item ) {
				if ( ! pods_is_admin( pods_v( 'access', $menu_item ) ) ) {
					continue;
				}

				// Don't just show the help page
				if ( false === $parent && 'pods-help' === $page ) {
					continue;
				}

				if ( empty( $menu_item['label'] ) ) {
					$menu_item['label'] = $page;
				}

				if ( empty( $menu_item['title'] ) ) {
					$menu_item['title'] = $menu_item['label'];
				}

				if ( false === $parent ) {
					$parent = $page;

					$menu = __( 'Pods Admin', 'pods' );

					if ( 'pods-upgrade' === $parent ) {
						$menu = __( 'Pods Upgrade', 'pods' );
					}

					add_menu_page( $menu, $menu, 'read', $parent, null, pods_svg_icon( 'pods' ) );
				}

				add_submenu_page( $parent, $menu_item['title'], $menu_item['label'], 'read', $page, $menu_item['function'] );

				if ( 'pods-components' === $page && is_object( PodsInit::$components ) ) {
					PodsInit::$components->menu( $parent );
				}
			}//end foreach
		}//end if
	}

	/**
	 * Set the correct parent_file to highlight the correct top level menu
	 *
	 * @param string $parent_file The parent file.
	 *
	 * @return mixed|string
	 *
	 * @since unknown
	 */
	public function parent_file( $parent_file ) {
		global $current_screen, $submenu_file;

		if ( isset( $current_screen ) && ! empty( $current_screen->taxonomy ) ) {
			$taxonomies = PodsMeta::$taxonomies;
			if ( ! empty( $taxonomies ) ) {
				foreach ( (array) $taxonomies as $pod ) {
					if ( $current_screen->taxonomy !== $pod['name'] ) {
						continue;
					}

					$menu_slug            = 'edit-tags.php?taxonomy=' . $pod['name'];
					$menu_location        = pods_v( 'menu_location', $pod['options'], 'default' );
					$menu_location_custom = pods_v( 'menu_location_custom', $pod['options'], '' );

					if ( 'settings' === $menu_location ) {
						$parent_file = 'options-general.php';
					} elseif ( 'appearances' === $menu_location ) {
						$parent_file = 'themes.php';
					} elseif ( 'objects' === $menu_location ) {
						$parent_file = $menu_slug;
					} elseif ( 'top' === $menu_location ) {
						$parent_file = $menu_slug;
					} elseif ( 'submenu' === $menu_location && ! empty( $menu_location_custom ) ) {
						$parent_file = $menu_location_custom;
					}

					break;
				}//end foreach
			}//end if
		}//end if

		if ( isset( $current_screen ) && ! empty( $current_screen->post_type ) && is_object( PodsInit::$components ) ) {
			$components_menu_items = PodsInit::$components->components_menu_items;

			foreach ( $components_menu_items as $menu_item ) {
				if ( empty( $menu_item['menu_page'] ) || $parent_file !== $menu_item['menu_page'] ) {
					continue;
				}

				$parent_file = 'pods';

				// @codingStandardsIgnoreLine
				$submenu_file = $menu_item['menu_page'];

				break;
			}
		}

		return $parent_file;
	}

	/**
	 * Show upgrade notice.
	 */
	public function upgrade_notice() {

		echo '<div class="error fade"><p>';
		// @codingStandardsIgnoreLine
		echo sprintf( __( '<strong>NOTICE:</strong> Pods %1$s requires your action to complete the upgrade. Please run the <a href="%2$s">Upgrade Wizard</a>.', 'pods' ), esc_html( PODS_VERSION ), esc_url( admin_url( 'admin.php?page=pods-upgrade' ) ) );
		echo '</p></div>';
	}

	/**
	 * Create PodsUI content for the administration pages
	 */
	public function admin_content() {

		// @codingStandardsIgnoreLine
		$pod_name = str_replace( array( 'pods-manage-', 'pods-add-new-' ), '', $_GET['page'] );

		$pod = pods_get_instance( $pod_name, pods_v( 'id', 'get', null, true ) );

		if ( ! $pod->pod_data->has_fields() ) {
			pods_message( __( 'This Pod does not have any fields defined.', 'pods' ), 'error' );
			return;
		}

		// @codingStandardsIgnoreLine
		if ( false !== strpos( $_GET['page'], 'pods-add-new-' ) ) {
			// @codingStandardsIgnoreLine
			$_GET['action'] = pods_v( 'action', 'get', 'add' );
		}

		$pod->ui();
	}

	/**
	 * Create PodsUI content for the settings administration pages
	 */
	public function admin_content_settings() {

		// @codingStandardsIgnoreLine
		$pod_name = str_replace( 'pods-settings-', '', $_GET['page'] );

		$pod = pods_get_instance( $pod_name );

		if ( 'custom' !== pods_v( 'ui_style', $pod->pod_data['options'], 'settings', true ) ) {
			$actions_disabled = array(
				'manage'    => 'manage',
				'add'       => 'add',
				'delete'    => 'delete',
				'duplicate' => 'duplicate',
				'view'      => 'view',
				'export'    => 'export',
			);

			// @codingStandardsIgnoreLine
			$_GET['action'] = 'edit';

			$page_title = pods_v( 'label', $pod->pod_data, ucwords( str_replace( '_', ' ', $pod->pod_data['name'] ) ), true );
			$page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod->pod_data );

			$pod_pod_name = $pod->pod;

			$ui = array(
				'id'               => $pod_pod_name,
				'pod'              => $pod,
				'fields'           => array(
					'edit' => $pod->pod_data->get_fields(),
				),
				'header'           => array(
					'edit' => $page_title,
				),
				'label'            => array(
					'edit' => __( 'Save Changes', 'pods' ),
				),
				'style'            => pods_v( 'ui_style', $pod->pod_data['options'], 'settings', true ),
				'icon'             => pods_evaluate_tags( pods_v( 'menu_icon', $pod->pod_data['options'] ), true ),
				'actions_disabled' => $actions_disabled,
			);

			$ui = apply_filters( "pods_admin_ui_{$pod_pod_name}", apply_filters( 'pods_admin_ui', $ui, $pod->pod, $pod ), $pod->pod, $pod );

			// Force disabled actions, do not pass go, do not collect $two_hundred
			$ui['actions_disabled'] = $actions_disabled;

			pods_ui( $ui );
		} else {
			$pod_pod_name = $pod->pod;
			do_action( 'pods_admin_ui_custom', $pod );
			do_action( "pods_admin_ui_custom_{$pod_pod_name}", $pod );
		}//end if
	}

	/**
	 * Add media button for Pods shortcode
	 *
	 * @param string $context Media button context.
	 */
	public function media_button( $context = null ) {
		if ( ! empty( $_GET['action'] ) && 'elementor' === $_GET['action'] ) {
			return;
		}

		// If shortcodes are disabled don't show the button
		if ( defined( 'PODS_DISABLE_SHORTCODE' ) && PODS_DISABLE_SHORTCODE ) {
			return;
		}

		/**
		 * Filter to remove Pods shortcode button from the post editor.
		 *
		 * @param bool   $show_button Set to false to block the shortcode button from appearing.
		 * @param string $context     Media button context.
		 *
		 * @since 2.3.19
		 */
		if ( ! apply_filters( 'pods_admin_media_button', true, $context ) ) {
			return;
		}

		$current_page = basename( $_SERVER['PHP_SELF'] );
		$current_page = explode( '?', $current_page );
		$current_page = explode( '#', $current_page[0] );
		$current_page = $current_page[0];

		// Only show the button on post type pages
		if ( ! in_array(
			$current_page, array(
				'post-new.php',
				'post.php',
			), true
		) ) {
			return;
		}

		add_action( 'admin_footer', array( $this, 'mce_popup' ) );

		echo '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox button" id="add_pod_button" title="Pods Shortcode"><img style="padding: 0px 6px 0px 0px; margin: -3px 0px 0px;" src="' . esc_url( PODS_URL . 'ui/images/icon16.png' ) . '" alt="' . esc_attr__( 'Pods Shortcode', 'pods' ) . '" />' . esc_html__( 'Pods Shortcode', 'pods' ) . '</a>';
	}

	/**
	 * Enqueue assets for Media Library Popup
	 */
	public function register_media_assets() {

		if ( 'pods_media_attachment' === pods_v( 'inlineId' ) ) {
			wp_enqueue_style( 'pods-styles' );
		}
	}

	/**
	 * Output Pods shortcode popup window
	 */
	public function mce_popup() {

		pods_view( PODS_DIR . 'ui/admin/shortcode.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Handle main Pods Setup area for managing Pods and Fields
	 */
	public function admin_setup() {
		$api = pods_api();

		$pods = $api->load_pods( array( 'fields' => false ) );

		$id     = pods_v( 'id' );
		$action = pods_v( 'action' );
		$view   = pods_v( 'view', 'get', 'all', true );

		// @codingStandardsIgnoreLine
		if ( empty( $pods ) && ! isset( $_GET['action'] ) ) {
			// @codingStandardsIgnoreLine
			$_GET['action'] = 'add';
		}

		if ( 'pods' === $_GET['page'] && empty( $pods ) ) {
			pods_message( __( 'You do not have any Pods set up yet. You can set up your very first Pod below.', 'pods ' ) );
		}

		// @codingStandardsIgnoreLine
		if ( 'pods-add-new' === $_GET['page'] || empty( $pods ) ) {
			// @codingStandardsIgnoreLine
			if ( isset( $_GET['action'] ) && 'add' !== $_GET['action'] ) {
				pods_redirect(
					pods_query_arg(
						array(
							'page'     => 'pods',
							// @codingStandardsIgnoreLine
							'action'   => $_GET['action'],
						)
					)
				);
			} else {
				// @codingStandardsIgnoreLine
				$_GET['action'] = 'add';
			}
			// @codingStandardsIgnoreLine
		} elseif ( isset( $_GET['action'] ) && 'add' === $_GET['action'] ) {
			pods_redirect(
				pods_query_arg(
					array(
						'page'     => 'pods-add-new',
						'action'   => '',
						'id'       => '',
						'do'       => '',
						'_wpnonce' => '',
					)
				)
			);
		}//end if

		$pod_types     = $api->get_pod_types();
		$storage_types = $api->get_storage_types();

		$row = false;

		$pod_types_found = [];
		$sources_found   = [];
		$source_types    = [];

		$include_row_counts         = filter_var( pods_v( 'pods_include_row_counts' ), FILTER_VALIDATE_BOOLEAN );
		$include_row_counts_refresh = filter_var( pods_v( 'pods_include_row_counts_refresh' ), FILTER_VALIDATE_BOOLEAN );

		$fields = [
			'label'       => [
				'label' => __( 'Label', 'pods' ),
			],
			'name'        => [
				'label' => __( 'Name', 'pods' ),
			],
			'type'        => [
				'label' => __( 'Type', 'pods' ),
			],
			'source'      => [
				'label' => __( 'Source', 'pods' ),
				'width' => '10%',
				'type'  => 'raw',
			],
			'storage'     => [
				'label' => __( 'Storage Type', 'pods' ),
				'width' => '10%',
			],
			'group_count' => [
				'label' => __( 'Groups', 'pods' ),
				'width' => '8%',
			],
			'field_count' => [
				'label' => __( 'Fields', 'pods' ),
				'width' => '8%',
			],
		];

		if ( $include_row_counts ) {
			$fields['row_count'] = [
				'label' => __( 'Data Rows', 'pods' ),
				'width' => '8%',
			];

			$fields['row_meta_count'] = [
				'label' => __( 'Meta Rows', 'pods' ),
				'width' => '8%',
			];

			$fields['podsrel_count'] = [
				// translators: "PodsRel" references the name of the Pods relationships table in the database.
				'label' => __( 'PodsRel Rows', 'pods' ),
				'width' => '10%',
			];
		}

		$total_groups       = 0;
		$total_fields       = 0;
		$total_rows         = 0;
		$total_row_meta     = 0;
		$total_podsrel_rows = 0;

		/**
		 * Filters whether to extend internal Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param bool $extend_internal Whether to extend internal Pods.
		 */
		$extend_internal = apply_filters( 'pods_admin_setup_extend_pods_internal', false );

		$pod_list = array();

		$is_tableless = pods_tableless();

		$has_source = false;
		$has_storage_type = false;

		foreach ( $pods as $k => $pod ) {
			$pod_type       = $pod['type'];
			$pod_type_label = null;
			$pod_storage    = $pod['storage'];

			if ( empty( $pod_type ) || ! is_string( $pod_type ) ) {
				$pod_type = 'post_type';
			}

			if ( empty( $pod_storage ) || ! is_string( $pod_storage ) ) {
				$pod_storage = 'meta';
			}

			$show_meta_count = 'meta' === $pod_storage || in_array( $pod['type'], [ 'post_type', 'taxonomy', 'user', 'comment' ], true );

			if ( ! empty( $pod['internal'] ) ) {
				// Don't show internal if we aren't extending them.
				if ( ! $extend_internal ) {
					continue;
				}

				$pod_type    = 'internal';
				$pod_storage = 'meta';
			}

			if ( 'settings' === $pod_type ) {
				$pod_storage = 'option';
			}

			if ( 'meta' !== $pod_storage ) {
				$has_storage_type = true;
			}

			if ( isset( $pod_types[ $pod_type ] ) ) {
				$pod_type_label = $pod_types[ $pod_type ];
			}

			$pod_real_type = $pod_type;

			$storage_type_label = ucwords( $pod_storage );

			if ( isset( $storage_types[ $pod_storage ] ) ) {
				$storage_type_label = $storage_types[ $pod_storage ];
			}

			if ( null !== $pod_type_label ) {
				if ( ! $pod->is_extended() && in_array( $pod_type, array(
						'post_type',
						'taxonomy',
					), true ) ) {
					if ( 'post_type' === $pod_type ) {
						$pod_type = 'cpt';
					} else {
						$pod_type = 'ct';
					}

					if ( isset( $pod_types[ $pod_type ] ) ) {
						$pod_type_label = $pod_types[ $pod_type ];
					}
				}

				if ( ! isset( $pod_types_found[ $pod_type ] ) ) {
					$pod_types_found[ $pod_type ] = 1;
				} else {
					$pod_types_found[ $pod_type ] ++;
				}

				if ( 'all' !== $view && $view !== $pod_type ) {
					continue;
				}

				$pod_real_type = $pod_type;
				$pod_type      = $pod_type_label;
			} elseif ( 'all' !== $view ) {
				continue;
			}//end if

			$group_count = 0;
			$field_count = 0;

			if ( ! pods_is_types_only( false, $pod->get_name() ) ) {
				$group_count = $pod->count_groups();
				$field_count = $pod->count_fields();
			}

			if ( $include_row_counts ) {
				$count_transient_prefix = 'pods_admin_' . $pod['type'] . '_' . $pod['name'] . '_';

				$row_counts = [
					'row_count'      => false,
					'row_meta_count' => false,
					'podsrel_count'  => false,
				];

				if ( ! $include_row_counts_refresh ) {
					$row_counts['row_count']      = pods_transient_get( $count_transient_prefix . 'row_count' );
					$row_counts['row_meta_count'] = pods_transient_get( $count_transient_prefix . 'row_meta_count' );
					$row_counts['podsrel_count']  = pods_transient_get( $count_transient_prefix . 'podsrel_count' );
				}

				if ( ! is_numeric( $row_counts['row_count'] ) ) {
					$row_counts['row_count'] = $pod->count_rows();

					pods_transient_set( $count_transient_prefix . 'row_count', $row_counts['row_count'], HOUR_IN_SECONDS * 3 );
				}

				if ( $show_meta_count && ! is_numeric( $row_counts['row_meta_count'] ) ) {
					$row_counts['row_meta_count'] = $pod->count_row_meta();

					pods_transient_set( $count_transient_prefix . 'row_meta_count', $row_counts['row_meta_count'], HOUR_IN_SECONDS * 3 );
				}

				if ( ! $is_tableless && ! is_numeric( $row_counts['podsrel_count'] ) ) {
					$row_counts['podsrel_count'] = $pod->count_podsrel_rows();

					pods_transient_set( $count_transient_prefix . 'podsrel_count', $row_counts['podsrel_count'], HOUR_IN_SECONDS * 3 );
				}
			}

			$object_storage_type = $pod->get_object_storage_type();
			$source              = $pod->get_object_storage_type_label();

			if ( $source ) {
				$source_types[ $object_storage_type ] = $source;

				if ( ! isset( $sources_found[ $object_storage_type ] ) ) {
					$sources_found[ $object_storage_type ] = 1;
				} else {
					$sources_found[ $object_storage_type ] ++;
				}
			}

			$source = esc_html( $source );

			if ( 'post_type' !== $object_storage_type ) {
				$has_source = true;

				if ( 'file' === $object_storage_type ) {
					$file_source = $pod->get_arg( '_pods_file_source' );

					if ( $file_source ) {
						if ( 0 === strpos( $file_source, ABSPATH ) ) {
							$file_source = str_replace( ABSPATH, '', $file_source );
						}

						ob_start();

						pods_help(
							sprintf(
								'<strong>%s:</strong> %s',
								esc_html__( 'File source', 'pods' ),
								esc_html( $file_source )
							),
							null,
							'.pods-admin-container'
						);

						$source .= ' ' . ob_get_clean();
					}
				} elseif ( 'collection' === $object_storage_type ) {
					$code_source = $pod->get_arg( '_pods_code_source' );

					if ( $code_source ) {
						if ( 0 === strpos( $code_source, ABSPATH ) ) {
							$code_source = str_replace( ABSPATH, '', $code_source );
						}

						ob_start();

						pods_help(
							sprintf(
								'<strong>%s:</strong> %s',
								esc_html__( 'Code source', 'pods' ),
								esc_html( $code_source )
							),
							null,
							'.pods-admin-container'
						);

						$source .= ' ' . ob_get_clean();
					}
				}
			}

			$pod = [
				'id'         => $pod['id'],
				'label'      => $pod['label'],
				'name'       => $pod['name'],
				'object'     => $pod['object'],
				'type'       => $pod_type,
				'real_type'  => $pod_real_type,
				'storage'    => $storage_type_label,
				'source'     => $source,
				'pod_object' => $pod,
			];

			if ( ! pods_is_types_only( false, $pod['name'] ) ) {
				$pod['group_count'] = number_format_i18n( $group_count );
				$pod['field_count'] = number_format_i18n( $field_count );

				$total_groups += $group_count;
				$total_fields += $field_count;
			}

			if ( $include_row_counts ) {
				$pod['row_count'] = number_format_i18n( $row_counts['row_count'] );

				$total_rows += $row_counts['row_count'];

				$pod['row_meta_count'] = 'n/a';
				$pod['podsrel_count']  = 'n/a';

				if ( $show_meta_count ) {
					$pod['row_meta_count'] = number_format_i18n( $row_counts['row_meta_count'] );

					$total_row_meta += $row_counts['row_meta_count'];
				}

				if ( ! $is_tableless ) {
					$pod['podsrel_count'] = number_format_i18n( $row_counts['podsrel_count'] );

					$total_podsrel_rows += $row_counts['podsrel_count'];
				}
			}

			// @codingStandardsIgnoreLine
			if ( 'manage' !== pods_v( 'action' ) ) {
				$found_id   = (int) pods_v( 'id' );
				$found_name = pods_v( 'name' );

				if (
					(
						$found_id
						&& $pod['id'] === $found_id
					)
					|| (
						$found_name
						&& $pod['name'] === $found_name
					)
				) {
					$row = $pod;
				}
			}

			$pod_list[] = $pod;
		}//end foreach

		if ( ! $has_storage_type ) {
			unset( $fields['storage'] );
		}

		if ( ! $has_source ) {
			unset( $fields['source'] );
		}

		if (
			(
				0 === $total_groups
				&& 0 === $total_fields
			)
			|| pods_is_types_only()
		) {
			unset( $fields['group_count'], $fields['field_count'] );
		}

		if ( false === $row && 0 < pods_v( 'id' ) && 'delete' !== pods_v( 'action' ) ) {
			pods_message( 'Pod not found', 'error' );

			// @codingStandardsIgnoreLine
			unset( $_GET['id'], $_GET['action'] );
		}

		$total_pods = count( $pod_list );

		$extra_total_text = '';

		if ( ! pods_is_types_only() ) {
			$extra_total_text .= sprintf(
				', %1$s %2$s, %3$s %4$s',
				number_format_i18n( $total_groups ),
				_n( 'group', 'groups', $total_groups, 'pods' ),
				number_format_i18n( $total_fields ),
				_n( 'field', 'fields', $total_fields, 'pods' )
			);
		}

		if ( $include_row_counts ) {
			$extra_total_text .= sprintf(
			', %1$s %2$s, %3$s %4$s, %5$s %6$s',
				number_format_i18n( $total_rows ),
				_n( 'data row', 'data rows', $total_rows, 'pods' ),
				number_format_i18n( $total_row_meta ),
				_n( 'meta row', 'meta rows', $total_row_meta, 'pods' ),
				number_format_i18n( $total_podsrel_rows ),
				// translators: "podsrel" references the name of the Pods relationships table in the database.
				_n( 'podsrel row', 'podsrel rows', $total_podsrel_rows, 'pods' )
			);
		}

		$pod_list = wp_list_sort( $pod_list, 'label', 'ASC', true );

		$ui = [
			'data'             => $pod_list,
			'row'              => $row,
			'total'            => $total_pods,
			'total_found'      => $total_pods,
			'items'            => 'Pods',
			'item'             => 'Pod',
			'fields'           => [
				'manage' => $fields,
			],
			'sql'              => [
				'field_id'    => 'id',
				'field_index' => 'label',
			],
			'actions_disabled' => [ 'view', 'export', 'delete', 'duplicate' ],
			'actions_custom'   => [
				'add'           => [ $this, 'admin_setup_add' ],
				'edit'          => [
					'callback'          => [ $this, 'admin_setup_edit' ],
					'restrict_callback' => [ $this, 'admin_restrict_non_db_type' ],
				],
				'duplicate_pod' => [
					'label'             => __( 'Duplicate', 'pods' ),
					'callback'          => [ $this, 'admin_setup_duplicate' ],
					'restrict_callback' => [ $this, 'admin_setup_duplicate_restrict' ],
					'nonce'             => true,
				],
				'delete_pod'    => [
					'label'             => __( 'Delete', 'pods' ),
					'confirm'           => __( 'Are you sure you want to delete this Pod?', 'pods' )
											. "\n\n"
											. __( 'All of the content and items will remain in the database.', 'pods' )
											. "\n\n"
											. __( 'You may want to go to Pods Admin > Settings > Cleanup & Reset > "Delete all content for a Pod" first.', 'pods' ),
					'callback'          => [ $this, 'admin_setup_delete' ],
					'restrict_callback' => [ $this, 'admin_restrict_non_db_type' ],
					'nonce'             => true,
					'span_class'        => 'delete',
				],
			],
			'action_links' => [
				'add'           => pods_query_arg( [
					'page'     => 'pods-add-new',
					'action'   => '',
					'id'       => '',
					'do'       => '',
					'_wpnonce' => '',
				] ),
				'duplicate_pod' => pods_query_arg( [
					'action' => 'duplicate_pod',
					'id'     => '{@id}',
					'name'   => '{@name}',
				] ),
			],
			'search'           => false,
			'searchable'       => false,
			'sortable'         => true,
			'pagination'       => false,
			'extra'            => [
				'total' => $extra_total_text,
			],
		];

		if ( 1 < count( $pod_types_found ) ) {
			$ui['views']            = [ 'all' => __( 'All', 'pods' ) . ' (' . $total_pods . ')' ];
			$ui['view']             = $view;
			$ui['heading']          = [ 'views' => __( 'Type', 'pods' ) ];
			$ui['filters_enhanced'] = true;

			foreach ( $pod_types_found as $pod_type => $number_found ) {
				$ui['views'][ $pod_type ] = sprintf(
					'%1$s (%2$s)',
					$pod_types[ $pod_type ],
					number_format_i18n( $number_found )
				);
			}

			if ( $has_source && 1 < count( $sources_found ) ) {
				foreach ( $sources_found as $source_type => $number_found ) {
					$ui['views'][ 'source/' . $source_type ] = sprintf(
						'%1$s: %2$s (%3$s)',
						__( 'Source', 'pods' ),
						$source_types[ $source_type ],
						$number_found
					);
				}
			}
		}

		// Maybe auto-map the slugs to ID when editing.
		if ( 'edit' === $action && $id && ! is_numeric( $id ) ) {
			foreach ( $ui['data'] as $check_pod ) {
				if ( $check_pod['name'] === $id && $check_pod['id'] && is_numeric( $check_pod['id'] ) ) {
					pods_redirect( pods_query_arg( [ 'id' => (int) $check_pod['id'] ] ) );

					break;
				}
			}
		}

		// Add our custom callouts.
		$this->handle_callouts_updates();

		add_filter( 'pods_ui_manage_custom_container_classes', array( $this, 'admin_manage_container_class' ) );

		if ( $this->has_horizontal_callout() ) {
			add_action( 'pods_ui_manage_before_container', [ $this, 'admin_manage_callouts' ] );
		} else {
			add_action( 'pods_ui_manage_after_container', [ $this, 'admin_manage_callouts' ] );
		}

		pods_ui( $ui );
	}

	/**
	 * Handle the Review Access Rights screen.
	 */
	public function admin_access_rights_review() {
		$api = pods_api();

		$pods = $api->load_pods( [ 'fields' => false ] );

		if ( empty( $pods ) ) {
			pods_message( __( 'You do not have any Pods set up yet.', 'pods ' ) );
		}

		$pod_types = $api->get_pod_types();

		$first_pods_version = get_option( 'pods_framework_version_first' );
		$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

		$dynamic_features_allow_options      = pods_access_get_dynamic_features_allow_options();
		$restricted_dynamic_features_options = pods_access_get_restricted_dynamic_features_options();

		$other_view_groups = [
			'public' => [
				'label' => __( 'Content Privacy', 'pods' ),
				'views' => [
					'1' => [
						'label' => __( 'Public', 'pods' ),
						'count' => 0,
					],
					'0' => [
						'label' => __( 'Private', 'pods' ),
						'count' => 0,
					],
				],
			],
			'dynamic_features_allow' => [
				'label' => __( 'Dynamic Features', 'pods' ),
				'views' => [],
			],
			'restricted_dynamic_features' => [
				'label' => __( 'Dynamic Features', 'pods' ),
				'views' => [
					'unrestricted' => [
						'label' => __( 'Unrestricted', 'pods' ),
						'count' => 0,
					],
					'restricted' => [
						'label' => __( 'Restricted', 'pods' ),
						'count' => 0,
					],
				],
			],
		];

		foreach ( $dynamic_features_allow_options as $dynamic_features_allow_option => $dynamic_features_allow_option_label ) {
			$other_view_groups['dynamic_features_allow']['views'][ $dynamic_features_allow_option ] = [
				'label' => trim( str_replace( '🔒', '', $dynamic_features_allow_option_label ) ),
				'count' => 0,
			];
		}

		$other_view_groups['dynamic_features_allow']['views']['inherit']['label'] = __( 'WP Default', 'pods' );

		$row = false;

		$pod_types_found = [];
		$sources_found   = [];
		$source_types    = [];

		$fields = [
			'label'                       => [
				'label' => __( 'Label', 'pods' ),
			],
			'name'                        => [
				'label' => __( 'Name', 'pods' ),
			],
			'type'                        => [
				'label' => __( 'Type', 'pods' ),
			],
			'source'                      => [
				'label' => __( 'Source', 'pods' ),
				'width' => '10%',
				'type'  => 'raw',
			],
			'public'                      => [
				'label' => __( 'Content Privacy', 'pods' ),
				'type'  => 'raw',
			],
			'dynamic_features_allow'      => [
				'label' => __( 'Allow Dynamic Features', 'pods' ),
				'type'  => 'raw',
			],
			'restricted_dynamic_features' => [
				'label' => __( 'Restricted Dynamic Features', 'pods' ),
				'type'  => 'pick',
			],
		];

		/**
		 * Filters whether to extend internal Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param bool $extend_internal Whether to extend internal Pods.
		 */
		$extend_internal = apply_filters( 'pods_admin_setup_extend_pods_internal', false );

		$pod_list = array();

		$has_source = false;

		foreach ( $pods as $k => $pod ) {
			$pod_type       = $pod['type'];
			$pod_type_label = null;

			if ( empty( $pod_type ) || ! is_string( $pod_type ) ) {
				$pod_type = 'post_type';
			}

			if ( ! empty( $pod['internal'] ) ) {
				// Don't show internal if we aren't extending them.
				if ( ! $extend_internal ) {
					continue;
				}

				$pod_type = 'internal';
			}

			if ( isset( $pod_types[ $pod_type ] ) ) {
				$pod_type_label = $pod_types[ $pod_type ];
			}

			$pod_real_type = $pod_type;

			if ( null !== $pod_type_label ) {
				if ( ! $pod->is_extended() && in_array( $pod_type, array(
						'post_type',
						'taxonomy',
					), true ) ) {
					if ( 'post_type' === $pod_type ) {
						$pod_type = 'cpt';
					} else {
						$pod_type = 'ct';
					}

					if ( isset( $pod_types[ $pod_type ] ) ) {
						$pod_type_label = $pod_types[ $pod_type ];
					}
				}

				if ( ! isset( $pod_types_found[ $pod_type ] ) ) {
					$pod_types_found[ $pod_type ] = 1;
				} else {
					$pod_types_found[ $pod_type ] ++;
				}

				$pod_real_type = $pod_type;
				$pod_type      = $pod_type_label;
			}

			$object_storage_type = $pod->get_object_storage_type();
			$source              = $pod->get_object_storage_type_label();

			if ( $source ) {
				$source_types[ $object_storage_type ] = $source;

				if ( ! isset( $sources_found[ $object_storage_type ] ) ) {
					$sources_found[ $object_storage_type ] = 1;
				} else {
					$sources_found[ $object_storage_type ] ++;
				}
			}

			$source = esc_html( $source );

			if ( 'post_type' !== $object_storage_type ) {
				$has_source = true;

				if ( 'file' === $object_storage_type ) {
					$file_source = $pod->get_arg( '_pods_file_source' );

					if ( $file_source ) {
						if ( 0 === strpos( $file_source, ABSPATH ) ) {
							$file_source = str_replace( ABSPATH, '', $file_source );
						}

						$source .= ' ' . pods_help(
							sprintf(
								'<strong>%s:</strong> %s',
								esc_html__( 'File source', 'pods' ),
								esc_html( $file_source )
							),
							null,
							'.pods-admin-container',
							true
						);
					}
				} elseif ( 'collection' === $object_storage_type ) {
					$code_source = $pod->get_arg( '_pods_code_source' );

					if ( $code_source ) {
						if ( 0 === strpos( $code_source, ABSPATH ) ) {
							$code_source = str_replace( ABSPATH, '', $code_source );
						}

						$source .= ' ' . pods_help(
							sprintf(
								'<strong>%s:</strong> %s',
								esc_html__( 'Code source', 'pods' ),
								esc_html( $code_source )
							),
							null,
							'.pods-admin-container',
							true
						);
					}
				}
			}

			$is_public = pods_is_type_public(
				[
					'pod' => $pod,
				]
			);

			$dynamic_features_allow_default = 'inherit';

			if ( 'pod' === $pod->get_type() ) {
				$dynamic_features_allow_default = version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? '1' : '0';
			}

			$dynamic_features_allow = $pod->get_arg( 'dynamic_features_allow', $dynamic_features_allow_default, true );

			if ( isset( $other_view_groups['dynamic_features_allow']['views'][ $dynamic_features_allow ] ) ) {
				$other_view_groups['dynamic_features_allow']['views'][ $dynamic_features_allow ]['count'] ++;
			}

			$dynamic_features_allow_label = isset( $dynamic_features_allow_options[ $dynamic_features_allow ] ) ? $dynamic_features_allow_options[ $dynamic_features_allow ] : $dynamic_features_allow_options[ $dynamic_features_allow_default ];

			if ( $dynamic_features_allow_label === $dynamic_features_allow_options['inherit'] ) {
				$dynamic_features_allow_label .= ' - ' . ( $is_public ? $dynamic_features_allow_options['1'] : $dynamic_features_allow_options['0'] );
			}

			$restrict_dynamic_features = (int) $pod->get_arg( 'restrict_dynamic_features', '1' );

			$pod_row = [
				'id'                          => $pod['id'],
				'label'                       => $pod['label'],
				'name'                        => $pod['name'],
				'object'                      => $pod['object'],
				'type'                        => $pod_type,
				'real_type'                   => $pod_real_type,
				'source'                      => $source,
				'real_source'                 => $object_storage_type,
				'bulk_disabled'               => 'post_type' !== $object_storage_type,
				'pod_object'                  => $pod,
				'public'                      => $is_public ? __( 'Public', 'pods' ) : '🔒 ' . __( 'Private', 'pods' ),
				'real_public'                 => $is_public ? 1 : 0,
				'dynamic_features_allow'      => $dynamic_features_allow_label,
				'real_dynamic_features_allow' => $dynamic_features_allow,
				'restricted_dynamic_features' => $pod->get_arg( 'restricted_dynamic_features' ),
			];

			if ( 0 === $restrict_dynamic_features ) {
				$pod_row['restricted_dynamic_features'] = [];
			}

			if ( ! is_array( $pod_row['restricted_dynamic_features'] ) ) {
				$pod_row['restricted_dynamic_features'] = [
					'display',
					'form',
				];
			}

			if ( $pod->is_extended() ) {
				$extended_help_text = pods_help(
					__( 'This is an extended content type. The Content Privacy cannot be changed by Pods. You can choose to enable Dynamic Features separately anyway if it has "WP Default" used.', 'pods' ),
					null,
					'.pods-admin-container',
					true
				);

				$pod_row['public'] .= $extended_help_text;

				if ( 'inherit' === $dynamic_features_allow ) {
					$pod_row['dynamic_features_allow'] .= $extended_help_text;
				}
			}

			$other_view_groups['public']['views'][ (string) $pod_row['real_public'] ]['count'] ++;

			if ( empty( $pod_row['restricted_dynamic_features'] ) ) {
				$pod_row['restricted_dynamic_features']      = __( 'Unrestricted', 'pods' );
				$pod_row['real_restricted_dynamic_features'] = 'unrestricted';
			} else {
				foreach ( $pod_row['restricted_dynamic_features'] as $fk => $feature ) {
					$pod_row['restricted_dynamic_features'][ $fk ] = pods_v( $feature, $restricted_dynamic_features_options, ucwords( $feature ) );
				}

				$pod_row['real_restricted_dynamic_features'] = 'restricted';
			}

			$other_view_groups['restricted_dynamic_features']['views'][ $pod_row['real_restricted_dynamic_features'] ]['count'] ++;

			// @codingStandardsIgnoreLine
			if ( 'manage' !== pods_v( 'action' ) ) {
				$found_id   = (int) pods_v( 'id' );
				$found_name = pods_v( 'name' );

				if (
					(
						$found_id
						&& $pod_row['id'] === $found_id
					)
					|| (
						$found_name
						&& $pod_row['name'] === $found_name
					)
				) {
					$row = $pod_row;
				}
			}

			$pod_list[] = $pod_row;
		}//end foreach

		if ( ! $has_source ) {
			unset( $fields['source'] );
		}

		if ( false === $row && 0 < pods_v( 'id' ) && 'delete' !== pods_v( 'action' ) ) {
			pods_message( 'Pod not found', 'error' );

			// @codingStandardsIgnoreLine
			unset( $_GET['id'], $_GET['action'] );
		}

		$total_pods = count( $pod_list );

		$total_pods_unfiltered = $total_pods;

		// Handle filtering.
		$view_filters = [
			'type'                        => 'real_type',
			'source'                      => 'real_source',
			'public'                      => 'real_public',
			'dynamic_features_allow'      => 'real_dynamic_features_allow',
			'restricted_dynamic_features' => 'real_restricted_dynamic_features',
		];

		$view                     = pods_v( 'view', 'get', 'all', true );
		$view_filter_info         = explode( '/', $view );
		$view_filter_group_active = isset( $view_filter_info[0] ) ? $view_filter_info[0] : null;
		$view_filter_key_active   = isset( $view_filter_info[1] ) ? $view_filter_info[1] : null;

		foreach ( $view_filters as $view_filter => $real_key ) {
			if ( $view_filter_group_active !== $view_filter ) {
				continue;
			}

			foreach ( $pod_list as $pod_key => $pod ) {
				// Maybe remove the pod from the list.
				if ( (string) $view_filter_key_active !== (string) $pod[ $real_key ] ) {
					unset( $pod_list[ $pod_key ] );
				}
			}
		}

		$pod_list = wp_list_sort( array_values( $pod_list ), 'label' );

		$total_pods = count( $pod_list );

		$ui = [
			'data'             => $pod_list,
			'row'              => $row,
			'total'            => $total_pods,
			'total_found'      => $total_pods,
			'items'            => 'Pods',
			'item'             => 'Pod',
			'header'           => [
				'manage' => '🔒 ' . __( 'Review Access Rights for Pods', 'pods' ),
			],
			'fields'           => [
				'manage' => $fields,
			],
			'sql'              => [
				'field_id'    => 'id',
				'field_index' => 'label',
			],
			'actions_disabled' => [ 'add', 'view', 'export', 'delete', 'duplicate' ],
			'actions_custom'   => [
				'edit'                        => [
					'label'             => __( 'Edit Pod', 'pods' ),
					'restrict_callback' => [ $this, 'admin_restrict_non_db_type' ],
				],
				'make_public'                 => [
					'label'             => __( 'Make public', 'pods' ),
					'callback'          => [ $this, 'admin_access_rights_review_make_public' ],
					'restrict_callback' => [ $this, 'admin_restrict_access_rights_review_make_public' ],
					'nonce'             => true,
				],
				'make_private'                => [
					'label'             => __( 'Make private', 'pods' ),
					'callback'          => [ $this, 'admin_access_rights_review_make_private' ],
					'restrict_callback' => [ $this, 'admin_restrict_access_rights_review_make_private' ],
					'nonce'             => true,
				],
				'wp_default_dynamic_features' => [
					'label'             => __( 'Set Dynamic Features to WP Default', 'pods' ),
					'callback'          => [ $this, 'admin_access_rights_review_wp_default_dynamic_features' ],
					'restrict_callback' => [ $this, 'admin_restrict_access_rights_review_wp_default_dynamic_features' ],
					'nonce'             => true,
				],
				'enable_dynamic_features'     => [
					'label'             => __( 'Enable Dynamic Features', 'pods' ),
					'callback'          => [ $this, 'admin_access_rights_review_enable_dynamic_features' ],
					'restrict_callback' => [ $this, 'admin_restrict_access_rights_review_enable_dynamic_features' ],
					'nonce'             => true,
				],
				'disable_dynamic_features'    => [
					'label'             => __( 'Disable Dynamic Features', 'pods' ),
					'callback'          => [ $this, 'admin_access_rights_review_disable_dynamic_features' ],
					'restrict_callback' => [ $this, 'admin_restrict_access_rights_review_disable_dynamic_features' ],
					'nonce'             => true,
				],
				'restrict_dynamic_features'   => [
					'label'             => __( 'Restrict all dynamic features', 'pods' ),
					'callback'          => [ $this, 'admin_access_rights_review_restrict_dynamic_features' ],
					'restrict_callback' => [ $this, 'admin_restrict_access_rights_review_restrict_dynamic_features' ],
					'nonce'             => true,
				],
				'unrestrict_dynamic_features' => [
					'label'             => __( 'Unrestrict all dynamic features', 'pods' ),
					'callback'          => [ $this, 'admin_access_rights_review_unrestrict_dynamic_features' ],
					'restrict_callback' => [ $this, 'admin_restrict_access_rights_review_unrestrict_dynamic_features' ],
					'nonce'             => true,
				],
			],
			'actions_bulk'     => [
				'make_public'                 => [
					'label'    => __( 'Make public', 'pods' ),
					'callback' => [ $this, 'admin_access_rights_review_make_public_bulk' ],
				],
				'make_private'                => [
					'label'    => __( 'Make private', 'pods' ),
					'callback' => [ $this, 'admin_access_rights_review_make_private_bulk' ],
				],
				'wp_default_dynamic_features' => [
					'label'    => __( 'Set Dynamic Features to WP Default', 'pods' ),
					'callback' => [ $this, 'admin_access_rights_review_wp_default_dynamic_features_bulk' ],
				],
				'enable_dynamic_features'     => [
					'label'    => __( 'Enable Dynamic Features', 'pods' ),
					'callback' => [ $this, 'admin_access_rights_review_enable_dynamic_features_bulk' ],
				],
				'disable_dynamic_features'    => [
					'label'    => __( 'Disable Dynamic Features', 'pods' ),
					'callback' => [ $this, 'admin_access_rights_review_disable_dynamic_features_bulk' ],
				],
				'restrict_dynamic_features'   => [
					'label'    => __( 'Restrict all dynamic features', 'pods' ),
					'callback' => [ $this, 'admin_access_rights_review_restrict_dynamic_features_bulk' ],
				],
				'unrestrict_dynamic_features' => [
					'label'    => __( 'Unrestrict all dynamic features', 'pods' ),
					'callback' => [ $this, 'admin_access_rights_review_unrestrict_dynamic_features_bulk' ],
				],
			],
			'action_links'     => [
				'edit' => pods_query_arg( [
					'page'   => 'pods',
					'action' => 'edit',
					'id'     => '{@id}',
					'name'   => '{@name}',
				] ),
			],
			'search'           => false,
			'searchable'       => false,
			'sortable'         => true,
			'pagination'       => false,
		];

		$ui['views']            = [ 'all' => __( 'All', 'pods' ) . ' (' . $total_pods_unfiltered . ')' ];
		$ui['view']             = $view;
		$ui['heading']          = [ 'views' => __( 'View', 'pods' ) ];
		$ui['filters_enhanced'] = true;

		if ( 1 < count( $pod_types_found ) ) {
			foreach ( $pod_types_found as $pod_type => $number_found ) {
				$ui['views'][ 'type/' . $pod_type ] = sprintf(
					'<strong>%1$s:</strong> %2$s (%3$s)',
					esc_html__( 'Type', 'pods' ),
					esc_html( $pod_types[ $pod_type ] ),
					number_format_i18n( $number_found )
				);
			}
		}

		if ( $has_source && 1 < count( $sources_found ) ) {
			foreach ( $sources_found as $source_type => $number_found ) {
				$ui['views'][ 'source/' . $source_type ] = sprintf(
					'<strong>%1$s:</strong> %2$s (%3$s)',
					esc_html__( 'Source', 'pods' ),
					esc_html( $source_types[ $source_type ] ),
					number_format_i18n( $number_found )
				);
			}
		}

		foreach ( $other_view_groups as $view_group_key => $view_group_info ) {
			if ( empty( $view_group_info['views'] ) ) {
				continue;
			}

			foreach ( $view_group_info['views'] as $view_key => $view_info ) {
				$ui['views'][ $view_group_key . '/' . $view_key ] = sprintf(
					'<strong>%1$s:</strong> %2$s (%3$s)',
					$view_group_info['label'],
					esc_html( $view_info['label'] ),
					number_format_i18n( $view_info['count'] )
				);
			}
		}

		$this->handle_callouts_updates();

		add_action( 'pods_ui_manage_before_filters', static function() {
			$callout_dismiss_link = add_query_arg( [
				'pods_callout_dismiss'       => 'access_rights',
				'pods_callout_dismiss_nonce' => wp_create_nonce( 'pods_callout_dismiss_access_rights' ),
			] );

			pods_message(
				wpautop(
					esc_html__( 'This screen is for reviewing the access rights and settings for your Pods. You can change the access rights for each Pod individually or in bulk.', 'pods' )
					 . "\n\n" . esc_html__( 'Carefully review whether your content types should be public and if dynamic features should be allowed.', 'pods' )
					 . "\n\n" . '<a href="https://docs.pods.io/displaying-pods/access-rights-in-pods/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Read the documentation for more information', 'pods' ) . ' &raquo;</a>'
				),
				'info',
				false,
				false
			);

			$callouts = PodsAdmin::$instance->get_callouts();

			if ( ! empty( $callouts['access_rights'] ) ) {
				pods_message(
					wpautop(
						esc_html__( 'You have not confirmed your Pods access rights below yet.', 'pods' )
						 . "\n\n" . esc_html__( 'Make changes below or confirm them.', 'pods' )
						 . "\n\n" . '<a href="' . esc_url( $callout_dismiss_link ) . '" class="button button-primary">' . esc_html__( 'Yes, I confirm the access rights below are correct for my site', 'pods' ) . '</a>'
					),
					'warning',
					false,
					false
				);
			}

			if ( ! pods_can_use_dynamic_features() ) {
				pods_message(
					wpautop(
						sprintf(
							'
								🔒 %s

								<a href="%s">%s &raquo;</a>
							',
							esc_html__( 'Dynamic Features are currently disabled globally which will automatically override any Dynamic Feature setting below from being referenced.', 'pods' ),
							esc_url( admin_url( 'admin.php?page=pods-settings' ) ),
							esc_html__( 'You can adjust this in your Pods Settings', 'pods' )
						)
					),
					'error',
					false,
					false
				);
			}
		} );

		pods_ui( $ui );
	}

	/**
	 * Get list of callouts to show.
	 *
	 * @since 2.7.17
	 *
	 * @return array List of callouts.
	 */
	public function get_callouts() {
		// Demo mode always bypasses callouts.
		if ( pods_is_demo() ) {
			return [];
		}

		$force_callouts = 1 === (int) pods_v( 'pods_force_callouts' );

		$page = pods_v( 'page' );

		if ( in_array( $page, array( 'pods-settings', 'pods-help' ), true ) ) {
			$force_callouts = true;
		}

		$callouts = get_option( 'pods_callouts' );

		if ( ! $callouts ) {
			$callouts = [
				'friends_2023_docs' => 1,
				'access_rights'     => (
					PodsInit::$version_last
					&& version_compare( PodsInit::$version_last, '3.1.0-a-1', '<' )
				) ? 1 : 0,
			];

			update_option( 'pods_callouts', $callouts );
		}

		// Handle callouts logic.
		$callouts['access_rights'] = ! isset( $callouts['access_rights'] ) || $callouts['access_rights'] ? 1 : 0;
		$callouts['friends_2023_docs'] = ! isset( $callouts['friends_2023_docs'] ) || $callouts['friends_2023_docs'] || $force_callouts ? 1 : 0;

		/**
		 * Allow hooking into whether or not the specific callouts should show.
		 *
		 * @since 2.7.17
		 *
		 * @param array $callouts List of callouts to enable.
		 */
		$callouts = apply_filters( 'pods_admin_callouts', $callouts );

		return $callouts;
	}

	/**
	 * Determine whether there's a horizontal callout.
	 *
	 * @since 3.1.0
	 *
	 * @param array $callouts The list of callouts if available, otherwise it will be fetched.
	 *
	 * @return bool Whether there's a horizontal callout.
	 */
	public function has_horizontal_callout( array $callouts = [] ): bool {
		if ( ! $callouts ) {
			$callouts = $this->get_callouts();
		}

		return ! empty( $callouts['access_rights'] );
	}

	/**
	 * Handle callouts update logic.
	 *
	 * @since 2.7.17
	 */
	public function handle_callouts_updates() {
		$callouts = get_option( 'pods_callouts' );

		if ( ! $callouts ) {
			$callouts = array();
		}

		$callout_dismiss = sanitize_text_field( pods_v( 'pods_callout_dismiss' ) );
		$callout_dismiss_nonce = pods_v( 'pods_callout_dismiss_nonce' );

		// Demo mode will auto-update the option for future loads.
		$is_demo = pods_is_demo();

		// Handle reset dismiss separately.
		if ( 'reset' === $callout_dismiss ) {
			// Reset callouts.
			update_option( 'pods_callouts', [] );

			return;
		}

		// Invalid nonce, cannot do anything with this dismiss request.
		if (
			! $is_demo
			&& (
				! $callout_dismiss_nonce
				|| false === wp_verify_nonce( $callout_dismiss_nonce, 'pods_callout_dismiss_' . $callout_dismiss )
			)
		) {
			return;
		}

		if ( $is_demo ) {
			// Disable Friends of Pods callout on demos.
			$callout_dismiss = 'friends_2023_docs';
		}

		if ( $callout_dismiss ) {
			$this->update_callout( $callout_dismiss, false );
		}
	}

	/**
	 * Handle updating whether a callout should be enabled.
	 *
	 * @since 3.1.0
	 *
	 * @param string $callout The callout to update.
	 * @param bool   $enabled Whether the callout should be enabled.
	 */
	public function update_callout( string $callout, bool $enabled ) {
		$callouts = get_option( 'pods_callouts' );

		if ( ! $callouts ) {
			$callouts = array();
		}

		$callouts[ $callout ] = (int) $enabled;

		update_option( 'pods_callouts', $callouts );
	}

	/**
	 * Add class to container if we have callouts to show.
	 *
	 * @since 2.7.17
	 *
	 * @param array $classes List of classes to use.
	 *
	 * @return array List of classes to use.
	 */
	public function admin_manage_container_class( $classes ) {
		$callouts = $this->get_callouts();

		// Only get enabled callouts.
		$callouts = array_filter( $callouts );

		if ( ! empty( $callouts ) ) {
			if ( $this->has_horizontal_callout( $callouts ) ) {
				$classes[] = 'pods-admin--flex-horizontal';
			} else {
				$classes[] = 'pods-admin--flex';
			}
		}

		return $classes;
	}

	/**
	 * Add callouts to let admins know about certain things.
	 *
	 * @since 2.7.17
	 */
	public function admin_manage_callouts() {
		static $did_callout = false;

		if ( $did_callout ) {
			return;
		}

		$force_callouts = false;

		$page = pods_v( 'page' );

		if ( in_array( $page, array( 'pods-settings', 'pods-help' ), true ) ) {
			$force_callouts = true;
		}

		$callouts = $this->get_callouts();

		if ( ! empty( $callouts['access_rights'] ) ) {
			$did_callout = true;

			pods_view( PODS_DIR . 'ui/admin/callouts/access_rights.php', compact( array_keys( get_defined_vars() ) ) );
		} elseif ( ! empty( $callouts['friends_2023_docs'] ) ) {
			$did_callout = true;

			pods_view( PODS_DIR . 'ui/admin/callouts/friends_2023_docs.php', compact( array_keys( get_defined_vars() ) ) );
		}
	}

	/**
	 * Get the add page of an object
	 *
	 * @param PodsUI $obj PodsUI object.
	 */
	public function admin_setup_add( $obj ) {
		pods_view( PODS_DIR . 'ui/admin/setup-add.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get the edit page of an object
	 *
	 * @param boolean $duplicate Whether the screen is for duplicating.
	 * @param PodsUI  $obj       PodsUI object.
	 */
	public function admin_setup_edit( $duplicate, $obj ) {
		$api = pods_api();

		$pod = $obj->row['pod_object'];

		if ( ! $pod instanceof Pod ) {
			$obj->id = null;
			$obj->row = [];
			$obj->action = 'manage';

			$obj->error( __( 'Invalid Pod configuration detected.', 'pods' ) );
			$obj->manage();

			return null;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() ) {
			$obj->id = null;
			$obj->row = [];
			$obj->action = 'manage';

			// translators: %s: The pod label.
			$obj->error( sprintf( __( 'Unable to edit the "%s" Pod configuration.', 'pods' ), $pod->get_label() ) );
			$obj->manage();

			return null;
		}

		$original_field_count = 0;

		foreach ( $obj->data as $row ) {
			if ( (int) $row['id'] === (int) $obj->id ) {
				if ( ! isset( $row['field_count'] ) ) {
					$row['field_count'] = 0;
				}

				$original_field_count = (int) $row['field_count'];

				break;
			}
		}

		$find_orphan_fields = (
			1 === (int) pods_v( 'pods_debug_find_orphan_fields', 'get', 0 )
			&& pods_is_admin( array( 'pods' ) )
		);

		$migrated = false;

		if ( $find_orphan_fields || 1 !== (int) $pod->get_arg( '_migrated_28' ) ) {
			$pod = $this->maybe_migrate_pod_fields_into_group( $pod );

			$migrated = true;

			// Maybe redirect the page to reload it fresh.
			if ( $find_orphan_fields ) {
				pods_redirect( pods_query_arg( [ 'pods_debug_find_orphan_fields' => null ] ) );
				die();
			}

			// Check again in case the pod migrated wrong.
			if ( ! $pod instanceof Pod ) {
				$obj->id = null;
				$obj->row = [];
				$obj->action = 'manage';

				$obj->error( __( 'Invalid Pod configuration detected.', 'pods' ) );
				$obj->manage();

				return null;
			}
		}

		$current_pod = $pod->export( [
			'include_groups'       => true,
			'include_group_fields' => true,
			'include_fields'       => false,
		] );

		$group_field_count = wp_list_pluck( $current_pod['groups'], 'fields' );
		$group_field_count = array_map( 'count', $group_field_count );
		$group_field_count = array_sum( $group_field_count );

		// Detect if there may be a migration/repair needed.
		if ( $original_field_count !== $group_field_count ) {
			if ( ! $migrated ) {
				$pod = $this->maybe_migrate_pod_fields_into_group( $pod );

				// Check again in case the pod migrated wrong.
				if ( ! $pod instanceof Pod ) {
					$obj->id     = null;
					$obj->row    = [];
					$obj->action = 'manage';

					$obj->error( __( 'Invalid Pod configuration detected.', 'pods' ) );
					$obj->manage();

					return null;
				}

				$current_pod = $pod->export( [
					'include_groups'       => true,
					'include_group_fields' => true,
					'include_fields'       => false,
				] );
			} else {
				pods_message( __( 'You may need to repair this Pod, we detected some fields not assigned to the groups shown in this configuration. You can find the tool at Pods Admin > Settings > Tools.', 'pods' ) );
			}
		}

		$config = [
			'currentPod'     => $current_pod,
			'global'         => $this->get_global_config( $pod ),
			'fieldTypes'     => PodsForm::field_types(),
			'relatedObjects' => $this->get_field_related_objects(),
			'podTypes'       => $api->get_pod_types(),
			'storageTypes'   => $api->get_storage_types(),
			// @todo SKC: Remove these below and replace any references to podsDFVConfig
			'wp_locale'      => $GLOBALS['wp_locale'],
			'userLocale'     => str_replace( '_', '-', get_user_locale() ),
			'currencies'     => PodsField_Currency::data_currencies(),
			'datetime'       => [
				'start_of_week' => (int) get_option( 'start_of_week', 0 ),
				'gmt_offset'    => (int) get_option( 'gmt_offset', 0 ),
			],
		];

		$config['currentPod']['podType'] = [
			'name' => $config['currentPod']['type'],
		];

		$config['currentPod']['storageType'] = [
			'name' => $config['currentPod']['storage'],
		];

		if ( ! empty( $config['currentPod']['internal'] ) ) {
			$config['currentPod']['podType']['name'] = 'internal';
		} elseif ( ! $pod->is_extended() ) {
			if ( 'post_type' === $config['currentPod']['type'] ) {
				$config['currentPod']['podType']['name'] = 'cpt';
			} elseif ( 'taxonomy' === $config['currentPod']['type'] ) {
				$config['currentPod']['podType']['name'] = 'ct';
			}
		}

		$config['currentPod']['podType']['label'] = ucwords( str_replace( '_', ' ', $config['currentPod']['podType']['name'] ) );

		if ( ! empty( $config['podTypes'][ $config['currentPod']['podType']['name'] ] ) ) {
			$config['currentPod']['podType']['label'] = $config['podTypes'][ $config['currentPod']['podType']['name'] ];
		}

		if ( 'settings' === $config['currentPod']['type'] ) {
			$config['currentPod']['storageType']['name'] = 'option';
		}

		$config['currentPod']['storageType']['label'] = ucwords( $config['currentPod']['storageType']['name'] );

		if ( ! empty( $config['storageTypes'][ $config['currentPod']['storageType']['name'] ] ) ) {
			$config['currentPod']['storageType']['label'] = $config['storageTypes'][ $config['currentPod']['storageType']['name'] ];
		}

		/**
		 * Allow filtering the admin config data.
		 *
		 * @since 2.8.0
		 *
		 * @param array  $config The admin config data.
		 * @param Pod    $pod    The pod object.
		 * @param PodsUI $obj    The PodsUI object.
		 */
		$config = apply_filters( 'pods_admin_setup_edit_pod_config', $config, $pod, $obj );

		wp_localize_script( 'pods-dfv', 'podsAdminConfig', $config );

		pods_view( PODS_DIR . 'ui/admin/setup-edit.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get list of field related objects.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of field related objects.
	 */
	protected function get_field_related_objects() {
		$related_object_groups = PodsForm::field_method( 'pick', 'related_objects', true );

		$related_objects = [];

		foreach ( $related_object_groups as $group => $group_objects ) {
			foreach ( $group_objects as $name => $label ) {
				$related_objects[ $name ] = [
					'name'  => $name,
					'label' => $label,
				];
			}
		}

		return $related_objects;
	}

	/**
	 * Maybe migrate pod fields into a group (if they have no group).
	 *
	 * @since 2.8.0
	 *
	 * @param Pod $pod The pod object.
	 *
	 * @return Pod The pod object.
	 */
	public function maybe_migrate_pod_fields_into_group( $pod ) {
		$tool = pods_container( Repair::class );

		$results = $tool->repair_groups_and_fields_for_pod( $pod, 'upgrade' );

		if ( '' !== $results['message_html'] ) {
			if ( 'pods' === pods_v( 'page' ) && 'edit' === pods_v( 'action' ) && 'create' === pods_v( 'do' ) ) {
				// Refresh the page if we just added the Pod.
				pods_redirect();
			} else {
				pods_message( $results['message_html'] );
			}
		}

		return $results['upgraded_pod'];
	}

	/**
	 * Get the global config for Pods admin.
	 *
	 * @param null|\Pods\Whatsit $current_pod
	 *
	 * @return array Global config array.
	 *@since 2.8.0
	 *
	 */
	public function get_global_config( $current_pod = null ) {
		$config_pod   = pods_container( Config_Pod::class );
		$config_group = pods_container( Config_Group::class );
		$config_field = pods_container( Config_Field::class );

		// Pod: Backwards compatible configs and hooks.
		$pod_tabs        = $config_pod->get_tabs( $current_pod);
		$pod_tab_options = $config_pod->get_fields( $current_pod, $pod_tabs );

		$this->backcompat_convert_tabs_to_groups( $pod_tabs, $pod_tab_options, 'pod/_pods_pod' );

		// If not types-only mode, handle groups/fields configs.
		if ( ! pods_is_types_only( false, $current_pod->get_name() ) ) {
			// Group: Backwards compatible methods and hooks.
			$group_tabs        = $config_group->get_tabs( $current_pod);
			$group_tab_options = $config_group->get_fields( $current_pod, $group_tabs );

			$this->backcompat_convert_tabs_to_groups( $group_tabs, $group_tab_options, 'pod/_pods_group' );

			// Field: Backwards compatible methods and hooks.
			$field_tabs        = $config_field->get_tabs( $current_pod);
			$field_tab_options = $config_field->get_fields( $current_pod, $field_tabs );

			$this->backcompat_convert_tabs_to_groups( $field_tabs, $field_tab_options, 'pod/_pods_field' );
		}

		$object_collection = Pods\Whatsit\Store::get_instance();

		/** @var Pods\Whatsit\Storage $storage */
		$storage = $object_collection->get_storage_object( 'collection' );

		// Get objects from storage.
		$pod_object = $storage->get( [
			'object_type'  => 'pod',
			'name'         => '_pods_pod',
			'bypass_cache' => true,
		] );

		$group_object = $storage->get( [
			'object_type'  => 'pod',
			'name'         => '_pods_group',
			'bypass_cache' => true,
		] );

		$field_object = $storage->get( [
			'object_type'  => 'pod',
			'name'         => '_pods_field',
			'bypass_cache' => true,
		] );

		$global_config = [
			'showFields' => ! pods_is_types_only( false, $current_pod->get_name() ),
			'pod'        => $pod_object->export( [
				'include_groups'       => true,
				'include_group_fields' => true,
				'include_fields'       => false,
				'include_field_data'   => true,
				'bypass_cache'         => true,
                'ref_id'               => 'global/' . $pod_object->get_type() . '/' . $pod_object->get_name(),
			] ),
			'group'      => $group_object->export( [
				'include_groups'       => true,
				'include_group_fields' => true,
				'include_fields'       => false,
				'include_field_data'   => true,
				'bypass_cache'         => true,
                'ref_id'               => 'global/' . $pod_object->get_type() . '/' . $pod_object->get_name(),
			] ),
			'field'      => $field_object->export( [
				'include_groups'       => true,
				'include_group_fields' => true,
				'include_fields'       => false,
				'include_field_data'   => true,
				'bypass_cache'         => true,
                'ref_id'               => 'global/' . $pod_object->get_type() . '/' . $pod_object->get_name(),
			] ),
		];

		/**
		 * Allow hooking into the global config setup for a Pod.
		 *
		 * @param array              $global_config The global config object.
		 * @param null|\Pods\Whatsit $current_pod   The Pod object.
		 */
		$global_config = apply_filters( 'pods_admin_setup_global_config', $global_config, $current_pod );

		return $global_config;
	}

	/**
	 * Convert the tabs and their options to groups/fields in the collection storage.
	 *
	 * @since 2.8.0
	 *
	 * @param array  $tabs    List of registered tabs.
	 * @param array  $options List of tab options.
	 * @param string $parent  The parent object to register to.
	 *
	 * @return array Global config array.
	 */
	protected function backcompat_convert_tabs_to_groups( array $tabs, array $options, $parent ) {
		$object_collection = Pods\Whatsit\Store::get_instance();

		/** @var Pods\Whatsit\Storage\Collection $storage */
		$storage = $object_collection->get_storage_object( 'collection' );

		$groups = [];
		$fields = [];

		foreach ( $tabs as $group_name => $group_label ) {
			if ( empty( $options[ $group_name ] ) ) {
				continue;
			}

			if ( is_array( $group_label ) ) {
				$group_args = $group_label;
			} else {
				$group_args = [
					'name'   => $group_name,
					'label'  => $group_label,
				];
			}

			$group_args['parent'] = $parent;

			$group = new \Pods\Whatsit\Group( $group_args );

			$groups[] = $storage->add( $group );

			$group_fields = $options[ $group_name ];

			$sections = false;

			foreach ( $group_fields as $field_name => $field_options ) {
				// Support sections.
				if ( ! isset( $field_options['label'] ) ) {
					$sections = true;
				}

				break;
			}

			$group_sections = $group_fields;

			// Store the same whether it's a section or not.
			if ( ! $sections ) {
				$group_sections = [
					$group_fields,
				];
			}

			foreach ( $group_sections as $section_label => $section_fields ) {
				// Add section field (maybe).
				if ( ! is_int( $section_label ) ) {
					$field_args = [
						'name'   => sanitize_title( $section_label ),
						'label'  => $section_label,
						'type'   => 'heading',
						'parent' => $parent,
						'group'  => 'group/' . $parent . '/' . $group_name,
					];

					$field = new \Pods\Whatsit\Field( $field_args );

					$fields[] = $storage->add( $field );
				}

				if ( ! is_array( $section_fields ) ) {
					continue;
				}

				// Add fields for section.
				foreach ( $section_fields as $field_name => $field_options ) {
					$boolean_group = [];

					// Handle auto-formatting from shorthand.
					if ( ! empty( $field_options['boolean_group'] ) ) {
						$boolean_group = $field_options['boolean_group'];

						foreach ( $boolean_group as $bgf_key => $boolean_group_field ) {
							// Make sure each field has a field name.
							if ( is_string( $bgf_key ) ) {
								$boolean_group[ $bgf_key ]['name'] = $bgf_key;
							}
						}

						$boolean_group = array_values( $boolean_group );

						$field_options['boolean_group'] = $boolean_group;
					}

					if ( empty( $field_options['type'] ) ) {
						continue;
					}

					// Set a unique field name for boolean group headings.
					if ( ! empty( $boolean_group ) ) {
						$field_options['name'] = $field_name . '_' . md5( json_encode( $boolean_group ) );
					}

					$field = $this->backcompat_convert_tabs_to_groups_setup_field( [
						'field_name'    => $field_name,
						'field_options' => $field_options,
						'parent'        => $parent,
						'group_name'    => $group_name,
					] );

					$fields[] = $storage->add( $field );
				}
			}
		}

		return compact( 'groups', 'fields' );
	}

	/**
	 * Setup field for backwards compatibility tabs to groups layer.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args {
	 * 		The field arguments.
	 *
	 *		@type string     $field_name    The field name.
	 *		@type array      $field_options The field options.
	 *		@type string|int $parent        The parent group.
	 *		@type string     $group_name    The group name.
	 * }
	 *
	 * @return \Pods\Whatsit\Field The field object.
	 */
	public function backcompat_convert_tabs_to_groups_setup_field( $args ) {
		$field_name    = $args['field_name'];
		$field_options = $args['field_options'];
		$parent        = $args['parent'];
		$group_name    = $args['group_name'];

		$field_args = $field_options;

		if ( ! isset( $field_args['name'] ) ) {
			$field_args['name'] = $field_name;
		}

		$field_args['parent'] = $parent;
		$field_args['group']  = 'group/' . $parent . '/' . $group_name;

		$dfv_args = (object) [
			'id'              => 0,
			'name'            => $field_args['name'],
			'value'           => '',
			'pod'             => null,
			'type'            => pods_v( 'type', $field_args ),
			'options'         => array_merge( [
				'id' => 0,
			], $field_args ),
			'build_item_data' => true,
		];

		if ( ! empty( $dfv_args->type ) ) {
			$field_args = PodsForm::field_method( $dfv_args->type, 'build_dfv_field_options', $field_args, $dfv_args );
		}

		return new \Pods\Whatsit\Field( $field_args );
	}

	/**
	 * Duplicate a pod
	 *
	 * @param PodsUI $obj PodsUI object.
	 */
	public function admin_setup_duplicate( $obj ) {
		$new_id = pods_api()->duplicate_pod( array( 'name' => $obj->row['name'] ) );

		if ( 0 < $new_id ) {
			pods_redirect(
				pods_query_arg(
					array(
						'action' => 'edit',
						'id'     => $new_id,
						'do'     => 'duplicate',
						'name'   => null,
					)
				)
			);

			return;
		}

		$obj->error( __( 'An error occurred, the Pod was not duplicated.', 'pods' ) );
	}

	/**
	 * Restrict Duplicate action to custom types, not extended
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @since 2.3.10
	 *
	 * @return bool
	 */
	public function admin_setup_duplicate_restrict( $restricted, $restrict, $action, $row, $obj ) {
		if ( in_array(
			$row['real_type'], array(
				'user',
				'media',
				'comment',
			), true
		) ) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Delete a pod
	 *
	 * @param PodsUI     $obj PodsUI object.
	 * @param int|string $id  Item ID.
	 *
	 * @return mixed
	 */
	public function admin_setup_delete( $obj, $id ) {

		$pod = pods_api()->load_pod( array( 'id' => $id ), false );

		if ( empty( $pod ) ) {
			return $obj->error( __( 'Pod not found.', 'pods' ) );
		}

		if ( 'post_type' !== $pod->get_object_storage_type() ) {
			return $obj->error( __( 'Pod cannot be deleted.', 'pods' ) );
		}

		pods_api()->delete_pod( array( 'id' => $id ) );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				unset( $obj->data[ $key ] );
			}
		}

		$obj->total       = count( $obj->data );
		$obj->total_found = count( $obj->data );

		$obj->message( __( 'Pod deleted successfully.', 'pods' ) );

		$obj->manage();
	}

	/**
	 * Handle access rights review bulk action to make public for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param int[]|string[] $ids Item IDs.
	 * @param PodsUI         $obj PodsUI object.
	 *
	 * @return false This always returns false unless there was a real error.
	 */
	public function admin_access_rights_review_make_public_bulk( $ids, $obj ) {
		foreach ( $ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$this->admin_access_rights_review_make_public( $obj, $id, 'bulk' );
		}

		$obj->message( __( 'Selected Pod(s) made public successfully.', 'pods' ) );

		$this->update_callout( 'access_rights', false );

		return false;
	}

	/**
	 * Handle access rights review action to make public for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param PodsUI     $obj  PodsUI object.
	 * @param int|string $id   Item ID.
	 * @param string     $mode Action mode.
	 *
	 * @return mixed
	 */
	public function admin_access_rights_review_make_public( $obj, $id, $mode = 'single' ) {
		$pod = pods_api()->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod not found.', 'pods' ) ) : false;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() || $pod->is_extended() ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod cannot be modified.', 'pods' ) ) : false;
		}

		$params = [
			'id' => $id,
			'public' => 1,
		];

		if ( in_array( $pod->get_type(), [ 'post_type', 'taxonomy' ], true ) ) {
			$params['publicly_queryable'] = 1;
		}

		pods_api()->save_pod( $params );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				$obj->data[ $key ]['public']      = __( 'Public', 'pods' );
				$obj->data[ $key ]['real_public'] = 1;
			}
		}

		if ( 'bulk' !== $mode ) {
			$obj->message( __( 'Pod made public successfully.', 'pods' ) );

			$this->update_callout( 'access_rights', false );

			$obj->manage();
		}
	}

	/**
	 * Handle access rights review bulk action to make private for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param int[]|string[] $ids Item IDs.
	 * @param PodsUI         $obj PodsUI object.
	 *
	 * @return false This always returns false unless there was a real error.
	 */
	public function admin_access_rights_review_make_private_bulk( $ids, $obj ) {
		foreach ( $ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$this->admin_access_rights_review_make_private( $obj, $id, 'bulk' );
		}

		$obj->message( __( 'Selected Pod(s) made private successfully.', 'pods' ) );

		$this->update_callout( 'access_rights', false );

		return false;
	}

	/**
	 * Handle access rights review action to make private for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param PodsUI     $obj  PodsUI object.
	 * @param int|string $id   Item ID.
	 * @param string     $mode Action mode.
	 *
	 * @return mixed
	 */
	public function admin_access_rights_review_make_private( $obj, $id, $mode = 'single' ) {
		$pod = pods_api()->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod not found.', 'pods' ) ) : false;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() || $pod->is_extended() ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod cannot be modified.', 'pods' ) ) : false;
		}

		$params = [
			'id' => $id,
		];

		if ( in_array( $pod->get_type(), [ 'post_type', 'taxonomy' ], true ) ) {
			$params['publicly_queryable'] = 0;
		} else {
			$params['public'] = 0;
		}

		pods_api()->save_pod( $params );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				$obj->data[ $key ]['public']      = '🔒 ' . __( 'Private', 'pods' );
				$obj->data[ $key ]['real_public'] = 0;
			}
		}

		if ( 'bulk' !== $mode ) {
			$obj->message( __( 'Pod made private successfully.', 'pods' ) );

			$this->update_callout( 'access_rights', false );

			$obj->manage();
		}
	}

	/**
	 * Handle access rights review bulk action to restrict dynamic features for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param int[]|string[] $ids Item IDs.
	 * @param PodsUI         $obj PodsUI object.
	 *
	 * @return false This always returns false unless there was a real error.
	 */
	public function admin_access_rights_review_restrict_dynamic_features_bulk( $ids, $obj ) {
		foreach ( $ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$this->admin_access_rights_review_restrict_dynamic_features( $obj, $id, 'bulk' );
		}

		$obj->message( __( 'Selected Pod(s) restricted dynamic features successfully.', 'pods' ) );

		$this->update_callout( 'access_rights', false );

		return false;
	}

	/**
	 * Handle access rights review action to restrict dynamic features for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param PodsUI     $obj  PodsUI object.
	 * @param int|string $id   Item ID.
	 * @param string     $mode Action mode.
	 *
	 * @return mixed
	 */
	public function admin_access_rights_review_restrict_dynamic_features( $obj, $id, $mode = 'single' ) {
		$pod = pods_api()->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod not found.', 'pods' ) ) : false;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod cannot be modified.', 'pods' ) ) : false;
		}

		pods_api()->save_pod( [
			'id'                          => $id,
			'restrict_dynamic_features'   => '1',
			'restricted_dynamic_features' => [
				'display',
				'form',
			],
		] );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				$obj->data[ $key ]['restricted_dynamic_features'] = pods_access_get_restricted_dynamic_features_options();

				$obj->data[ $key ]['real_restricted_dynamic_features'] = 'restricted';
			}
		}

		if ( 'bulk' !== $mode ) {
			$obj->message( __( 'Pod restricted dynamic features successfully.', 'pods' ) );

			$this->update_callout( 'access_rights', false );

			$obj->manage();
		}
	}

	/**
	 * Handle access rights review bulk action to restrict dynamic features for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param int[]|string[] $ids Item IDs.
	 * @param PodsUI         $obj PodsUI object.
	 *
	 * @return false This always returns false unless there was a real error.
	 */
	public function admin_access_rights_review_unrestrict_dynamic_features_bulk( $ids, $obj ) {
		foreach ( $ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$this->admin_access_rights_review_unrestrict_dynamic_features( $obj, $id, 'bulk' );
		}

		$obj->message( __( 'Selected Pod(s) unrestricted dynamic features successfully.', 'pods' ) );

		$this->update_callout( 'access_rights', false );

		return false;
	}

	/**
	 * Handle access rights review action to unrestrict dynamic features for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param PodsUI     $obj  PodsUI object.
	 * @param int|string $id   Item ID.
	 * @param string     $mode Action mode.
	 *
	 * @return mixed
	 */
	public function admin_access_rights_review_unrestrict_dynamic_features( $obj, $id, $mode = 'single' ) {
		$pod = pods_api()->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod not found.', 'pods' ) ) : false;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod cannot be modified.', 'pods' ) ) : false;
		}

		pods_api()->save_pod( [
			'id'                          => $id,
			'restrict_dynamic_features'   => '0',
			'restricted_dynamic_features' => [],
		] );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				$obj->data[ $key ]['restricted_dynamic_features']      = __( 'Unrestricted', 'pods' );
				$obj->data[ $key ]['real_restricted_dynamic_features'] = 'unrestricted';
			}
		}

		if ( 'bulk' !== $mode ) {
			$obj->message( __( 'Pod unrestricted dynamic features successfully.', 'pods' ) );

			$this->update_callout( 'access_rights', false );

			$obj->manage();
		}
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj ) {
		if ( __( 'DB', 'pods' ) !== $row['source'] ) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_access_rights_review_make_public( $restricted, $restrict, $action, $row, $obj ) {
		$restricted = $this->admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj );

		if (
			! $restricted
			&& (
				1 === $row['real_public']
				|| $row['pod_object']->is_extended()
			)
		) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_access_rights_review_make_private( $restricted, $restrict, $action, $row, $obj ) {
		$restricted = $this->admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj );

		if (
			! $restricted
			&& (
				0 === $row['real_public']
				|| $row['pod_object']->is_extended()
			)
		) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_access_rights_review_restrict_dynamic_features( $restricted, $restrict, $action, $row, $obj ) {
		$restricted = $this->admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj );

		if ( ! $restricted && 'restricted' === $row['real_restricted_dynamic_features'] ) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Handle access rights review bulk action to set dynamic features as WP Default for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param int[]|string[] $ids Item IDs.
	 * @param PodsUI         $obj PodsUI object.
	 *
	 * @return false This always returns false unless there was a real error.
	 */
	public function admin_access_rights_review_wp_default_dynamic_features_bulk( $ids, $obj ) {
		foreach ( $ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$this->admin_access_rights_review_wp_default_dynamic_features( $obj, $id, 'bulk' );
		}

		$obj->message( __( 'Selected Pod(s) Dynamic Features set to WP Default successfully.', 'pods' ) );

		$this->update_callout( 'access_rights', false );

		return false;
	}

	/**
	 * Handle access rights review bulk action to set dynamic features as WP Default for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param PodsUI     $obj  PodsUI object.
	 * @param int|string $id   Item ID.
	 * @param string     $mode Action mode.
	 *
	 * @return mixed
	 */
	public function admin_access_rights_review_wp_default_dynamic_features( $obj, $id, $mode = 'single' ) {
		$pod = pods_api()->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod not found.', 'pods' ) ) : false;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() || $pod->is_extended() ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod cannot be modified.', 'pods' ) ) : false;
		}

		$is_public = pods_is_type_public(
			[
				'pod' => $pod,
			]
		);

		$options = pods_access_get_dynamic_features_allow_options();
		$value   = 'inherit';
		$label   = $options[ $value ] . ' - ' . ( $is_public ? $options['1'] : $options['0'] );

		pods_api()->save_pod( [ 'id' => $id, 'dynamic_features_allow' => $value ] );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				$obj->data[ $key ]['dynamic_features_allow']      = $label;
				$obj->data[ $key ]['real_dynamic_features_allow'] = $value;
			}
		}

		if ( 'bulk' !== $mode ) {
			$obj->message( __( 'Pod Dynamic Features set to WP Default successfully.', 'pods' ) );

			$this->update_callout( 'access_rights', false );

			$obj->manage();
		}
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_access_rights_review_wp_default_dynamic_features( $restricted, $restrict, $action, $row, $obj ) {
		$restricted = $this->admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj );

		if ( ! $restricted && 'inherit' === $row['real_dynamic_features_allow'] ) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Handle access rights review bulk action to set dynamic features as enabled for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param int[]|string[] $ids Item IDs.
	 * @param PodsUI         $obj PodsUI object.
	 *
	 * @return false This always returns false unless there was a real error.
	 */
	public function admin_access_rights_review_enable_dynamic_features_bulk( $ids, $obj ) {
		foreach ( $ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$this->admin_access_rights_review_enable_dynamic_features( $obj, $id, 'bulk' );
		}

		$obj->message( __( 'Selected Pod(s) Dynamic Features set to Enabled successfully.', 'pods' ) );

		$this->update_callout( 'access_rights', false );

		return false;
	}

	/**
	 * Handle access rights review bulk action to set dynamic features as enabled for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param PodsUI     $obj  PodsUI object.
	 * @param int|string $id   Item ID.
	 * @param string     $mode Action mode.
	 *
	 * @return mixed
	 */
	public function admin_access_rights_review_enable_dynamic_features( $obj, $id, $mode = 'single' ) {
		$pod = pods_api()->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod not found.', 'pods' ) ) : false;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() || $pod->is_extended() ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod cannot be modified.', 'pods' ) ) : false;
		}

		$options = pods_access_get_dynamic_features_allow_options();
		$value   = '1';
		$label   = $options[ $value ];

		pods_api()->save_pod( [ 'id' => $id, 'dynamic_features_allow' => $value ] );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				$obj->data[ $key ]['dynamic_features_allow']      = $label;
				$obj->data[ $key ]['real_dynamic_features_allow'] = $value;
			}
		}

		if ( 'bulk' !== $mode ) {
			$obj->message( __( 'Pod Dynamic Features set to Enabled successfully.', 'pods' ) );

			$this->update_callout( 'access_rights', false );

			$obj->manage();
		}
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_access_rights_review_enable_dynamic_features( $restricted, $restrict, $action, $row, $obj ) {
		$restricted = $this->admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj );

		if ( ! $restricted && '1' === $row['real_dynamic_features_allow'] ) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Handle access rights review bulk action to set dynamic features as disabled for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param int[]|string[] $ids Item IDs.
	 * @param PodsUI         $obj PodsUI object.
	 *
	 * @return false This always returns false unless there was a real error.
	 */
	public function admin_access_rights_review_disable_dynamic_features_bulk( $ids, $obj ) {
		foreach ( $ids as $id ) {
			if ( ! $id ) {
				continue;
			}

			$this->admin_access_rights_review_disable_dynamic_features( $obj, $id, 'bulk' );
		}

		$obj->message( __( 'Selected Pod(s) Dynamic Features set to Disabled successfully.', 'pods' ) );

		$this->update_callout( 'access_rights', false );

		return false;
	}

	/**
	 * Handle access rights review bulk action to set dynamic features as disabled for a pod.
	 *
	 * @since 3.1.0
	 *
	 * @param PodsUI     $obj  PodsUI object.
	 * @param int|string $id   Item ID.
	 * @param string     $mode Action mode.
	 *
	 * @return mixed
	 */
	public function admin_access_rights_review_disable_dynamic_features( $obj, $id, $mode = 'single' ) {
		$pod = pods_api()->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod not found.', 'pods' ) ) : false;
		}

		if ( 'post_type' !== $pod->get_object_storage_type() || $pod->is_extended() ) {
			return 'bulk' !== $mode ? $obj->error( __( 'Pod cannot be modified.', 'pods' ) ) : false;
		}

		$options = pods_access_get_dynamic_features_allow_options();
		$value   = '0';
		$label   = $options[ $value ];

		pods_api()->save_pod( [ 'id' => $id, 'dynamic_features_allow' => $value ] );

		foreach ( $obj->data as $key => $data_pod ) {
			if ( (int) $id === (int) $data_pod['id'] ) {
				$obj->data[ $key ]['dynamic_features_allow']      = $label;
				$obj->data[ $key ]['real_dynamic_features_allow'] = $value;
			}
		}

		if ( 'bulk' !== $mode ) {
			$obj->message( __( 'Pod Dynamic Features set to Disabled successfully.', 'pods' ) );

			$this->update_callout( 'access_rights', false );

			$obj->manage();
		}
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_access_rights_review_disable_dynamic_features( $restricted, $restrict, $action, $row, $obj ) {
		$restricted = $this->admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj );

		if ( ! $restricted && '0' === $row['real_dynamic_features_allow'] ) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Restrict actions that can't be done for Pods with a non DB source.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $restricted Whether action is restricted.
	 * @param array  $restrict   Restriction array.
	 * @param string $action     Current action.
	 * @param array  $row        Item data row.
	 * @param PodsUI $obj        PodsUI object.
	 *
	 * @return bool Whether the action is restricted.
	 */
	public function admin_restrict_access_rights_review_unrestrict_dynamic_features( $restricted, $restrict, $action, $row, $obj ) {
		$restricted = $this->admin_restrict_non_db_type( $restricted, $restrict, $action, $row, $obj );

		if ( ! $restricted && 'unrestricted' === $row['real_restricted_dynamic_features'] ) {
			$restricted = true;
		}

		return $restricted;
	}

	/**
	 * Get advanced administration view.
	 */
	public function admin_advanced() {

		pods_view( PODS_DIR . 'ui/admin/advanced.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get settings administration view
	 */
	public function admin_settings() {
		// Add our custom callouts.
		$this->handle_callouts_updates();

		/**
		 * Allow hooking into our settings page to set up hooks.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_admin_settings_init' );

		// Add our custom callouts.
		if ( $this->has_horizontal_callout() ) {
			add_action( 'pods_admin_before_settings', [ $this, 'admin_manage_callouts' ] );
		} else {
			add_action( 'pods_admin_after_settings', [ $this, 'admin_manage_callouts' ] );
		}

		pods_view( PODS_DIR . 'ui/admin/settings.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Get components administration UI
	 */
	public function admin_components() {

		if ( ! is_object( PodsInit::$components ) ) {
			return;
		}

		$components = PodsInit::$components->components;

		$view = pods_v( 'view', 'get', 'all', true );

		$recommended = array(
			'advanced-relationships',
			'advanced-content-types',
			'migrate-packages',
			'roles-and-capabilities',
			'pages',
			'table-storage',
			'templates',
		);

		foreach ( $components as $component => &$component_data ) {
			if ( ! in_array(
				$view, array(
					'all',
					'recommended',
					'dev',
				), true
			) && ( ! isset( $component_data['Category'] ) || sanitize_title( $component_data['Category'] ) !== $view ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( 'recommended' === $view && ! in_array( $component_data['ID'], $recommended, true ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( 'dev' === $view && pods_developer() && ! pods_v( 'DeveloperMode', $component_data, false ) ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( pods_v( 'DeveloperMode', $component_data, false ) && ! pods_developer() ) {
				unset( $components[ $component ] );

				continue;
			} elseif ( ! pods_v( 'TablelessMode', $component_data, false ) && pods_tableless() ) {
				unset( $components[ $component ] );

				continue;
			}//end if

			$component_data['Name'] = strip_tags( $component_data['Name'] );

			if ( pods_v( 'DeveloperMode', $component_data, false ) ) {
				$component_data['Name'] .= ' <em style="font-weight: normal; color:#333;">(Developer Preview)</em>';
			}

			$meta = array();

			if ( ! empty( $component_data['Version'] ) ) {
				$meta[] = sprintf( __( 'Version %s', 'pods' ), $component_data['Version'] );
			}

			if ( empty( $component_data['Author'] ) ) {
				$component_data['Author']    = 'Pods Framework Team';
				$component_data['AuthorURI'] = 'https://pods.io/';
			}

			if ( ! empty( $component_data['AuthorURI'] ) ) {
				$component_data['Author'] = '<a href="' . $component_data['AuthorURI'] . '">' . $component_data['Author'] . '</a>';
			}

			$meta[] = sprintf( __( 'by %s', 'pods' ), $component_data['Author'] );

			if ( ! empty( $component_data['URI'] ) ) {
				$meta[] = '<a href="' . $component_data['URI'] . '">' . __( 'Visit component site', 'pods' ) . '</a>';
			}

			if ( pods_is_truthy( pods_v( 'Deprecated', $component_data, false ) ) ) {
				$deprecated = __( 'Deprecated', 'pods' );

				$component_data['Name'] .= sprintf(
					' <em style="font-weight: normal; color:#333;">(%s)</em>',
					$deprecated
				);

				$deprecated_in_version      = pods_v( 'DeprecatedInVersion', $component_data );
				$deprecated_removal_version = pods_v( 'DeprecatedRemovalVersion', $component_data );

				if ( empty( $deprecated_removal_version ) ) {
					// translators: TBD means To Be Determined.
					$deprecated_removal_version = __( 'TBD', 'pods' );
				}

				if ( $deprecated_in_version ) {
					$deprecated = sprintf(
					// translators: %1$s is the version the deprecation starts and %2$s is the version the code will be removed.
						__( 'Deprecated in %1$s, will be removed in %2$s', 'pods' ),
						$deprecated_in_version,
						$deprecated_removal_version
					);
				}

				$meta[] = $deprecated;
			}

			$component_data['Description'] = wpautop( trim( make_clickable( strip_tags( $component_data['Description'], 'em,strong' ) ) ) );

			if ( ! empty( $meta ) ) {
				$description_style = '';

				if ( ! empty( $component_data['Description'] ) ) {
					$description_style = ' style="padding:8px 0 4px;"';
				}

				$component_data['Description'] .= '<div class="pods-component-meta" ' . $description_style . '>' . implode( '&nbsp;&nbsp;|&nbsp;&nbsp;', $meta ) . '</div>';
			}

			$component_data = array(
				'id'          => $component_data['ID'],
				'name'        => $component_data['Name'],
				'category'    => $component_data['Category'],
				'version'     => '',
				'description' => $component_data['Description'],
				'mustuse'     => pods_v( 'MustUse', $component_data, false ),
				'toggle'      => 0,
			);

			if ( ! empty( $component_data['category'] ) ) {
				$category_url = pods_query_arg(
					array(
						'view' => sanitize_title( $component_data['category'] ),
						'pg'   => '',
						// @codingStandardsIgnoreLine
						'page' => $_GET['page'],
					)
				);

				$component_data['category'] = '<a href="' . esc_url( $category_url ) . '">' . $component_data['category'] . '</a>';
			}

			if ( isset( PodsInit::$components->settings['components'][ $component_data['id'] ] ) && 0 !== PodsInit::$components->settings['components'][ $component_data['id'] ] ) {
				$component_data['toggle'] = 1;
			} elseif ( $component_data['mustuse'] ) {
				$component_data['toggle'] = 1;
			}
		}//end foreach

		$ui = array(
			'sql'              => array(
				'field_id' => 'id',
			),
			'data'             => $components,
			'total'            => count( $components ),
			'total_found'      => count( $components ),
			'items'            => __( 'Components', 'pods' ),
			'item'             => __( 'Component', 'pods' ),
			'fields'           => array(
				'manage' => array(
					'name'        => array(
						'label'   => __( 'Name', 'pods' ),
						'width'   => '30%',
						'type'    => 'text',
						'options' => array(
							'text_allow_html' => true,
						),
					),
					'category'    => array(
						'label'   => __( 'Category', 'pods' ),
						'width'   => '10%',
						'type'    => 'text',
						'options' => array(
							'text_allow_html' => true,
						),
					),
					'description' => array(
						'label'   => __( 'Description', 'pods' ),
						'width'   => '60%',
						'type'    => 'text',
						'options' => array(
							'text_allow_html'        => true,
							'text_allowed_html_tags' => 'strong em a ul ol li b i br div',
						),
					),
				),
			),
			'actions_disabled' => array( 'duplicate', 'view', 'export', 'add', 'edit', 'delete' ),
			'actions_custom'   => array(
				'toggle' => array(
					'callback' => array( $this, 'admin_components_toggle' ),
					'nonce'    => true,
				),
			),
			'filters_enhanced' => true,
			'views'            => array(
				'all'         => __( 'All', 'pods' ),
				// 'recommended' => __( 'Recommended', 'pods' ),
				'field-types' => __( 'Field Types', 'pods' ),
				'tools'       => __( 'Tools', 'pods' ),
				'integration' => __( 'Integration', 'pods' ),
				'migration'   => __( 'Migration', 'pods' ),
				'advanced'    => __( 'Advanced', 'pods' ),
			),
			'view'             => $view,
			'heading'          => array(
				'views' => __( 'Category', 'pods' ),
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => false,
			'pagination'       => false,
		);

		if ( pods_developer() ) {
			$ui['views']['dev'] = __( 'Developer Preview', 'pods' );
		}

		// Add our custom callouts.
		$this->handle_callouts_updates();

		add_filter( 'pods_ui_manage_custom_container_classes', array( $this, 'admin_manage_container_class' ) );

		if ( $this->has_horizontal_callout() ) {
			add_action( 'pods_ui_manage_before_container', [ $this, 'admin_manage_callouts' ] );
		} else {
			add_action( 'pods_ui_manage_after_container', [ $this, 'admin_manage_callouts' ] );
		}

		pods_ui( $ui );
	}

	/**
	 * Toggle a component on or off
	 *
	 * @param PodsUI $ui PodsUI object.
	 */
	public function admin_components_toggle( $ui ) {
		$component = pods_v( 'id' );

		if ( ! empty( PodsInit::$components->components[ $component ]['PluginDependency'] ) ) {
			$dependency = explode( '|', PodsInit::$components->components[ $component ]['PluginDependency'] );

			if ( ! pods_is_plugin_active( $dependency[1] ) ) {
				$website = 'http://wordpress.org/extend/plugins/' . dirname( $dependency[1] ) . '/';

				if ( isset( $dependency[2] ) ) {
					$website = $dependency[2];
				}

				if ( ! empty( $website ) ) {
					$website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $website . '" target="_blank" rel="noopener noreferrer">' . $website . '</a>' );
				}

				$message = sprintf( __( 'The %1$s component requires that you have the <strong>%2$s</strong> plugin installed and activated.', 'pods' ), PodsInit::$components->components[ $component ]['Name'], $dependency[0] ) . $website;

				$ui->error( $message );

				$ui->manage();

				return;
			}
		}//end if

		if ( ! empty( PodsInit::$components->components[ $component ]['ThemeDependency'] ) ) {
			$dependency = explode( '|', PodsInit::$components->components[ $component ]['ThemeDependency'] );

			$check = strtolower( $dependency[1] );

			if ( strtolower( get_template() ) !== $check && strtolower( get_stylesheet() ) !== $check ) {
				$website = '';

				if ( isset( $dependency[2] ) ) {
					$website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $dependency[2] . '" target="_blank" rel="noopener noreferrer">' . $dependency[2] . '</a>' );
				}

				$message = sprintf( __( 'The %1$s component requires that you have the <strong>%2$s</strong> theme installed and activated.', 'pods' ), PodsInit::$components->components[ $component ]['Name'], $dependency[0] ) . $website;

				$ui->error( $message );

				$ui->manage();

				return;
			}
		}//end if

		if ( ! empty( PodsInit::$components->components[ $component ]['MustUse'] ) ) {
			$message = sprintf( __( 'The %s component can not be disabled from here. You must deactivate the plugin or theme that added it.', 'pods' ), PodsInit::$components->components[ $component ]['Name'] );

			$ui->error( $message );

			$ui->manage();

			return;
		}

		if ( 1 === (int) pods_v( 'toggled' ) ) {
			$toggle = PodsInit::$components->toggle( $component );

			if ( true === $toggle ) {
				$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component enabled', 'pods' ) );
			} elseif ( false === $toggle ) {
				$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component disabled', 'pods' ) );
			}

			$components = PodsInit::$components->components;

			foreach ( $components as $component => &$component_data ) {
				$toggle = 0;

				if ( isset( PodsInit::$components->settings['components'][ $component_data['ID'] ] ) ) {
					if ( 0 !== PodsInit::$components->settings['components'][ $component_data['ID'] ] ) {
						$toggle = 1;
					}
				}
				if ( true === $component_data['DeveloperMode'] ) {
					if ( ! pods_developer() ) {
						unset( $components[ $component ] );
						continue;
					}
				}

				$component_data = array(
					'id'          => $component_data['ID'],
					'name'        => $component_data['Name'],
					'description' => make_clickable( $component_data['Description'] ),
					'version'     => $component_data['Version'],
					'author'      => $component_data['Author'],
					'toggle'      => $toggle,
				);
			}//end foreach

			$ui->data = $components;

			pods_transient_clear( 'pods_components' );

			$url = pods_query_arg( array( 'toggled' => null ) );

			pods_redirect( $url );
		} elseif ( 1 === (int) pods_v( 'toggle' ) ) {
			$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component enabled', 'pods' ) );
		} else {
			$ui->message( PodsInit::$components->components[ $component ]['Name'] . ' ' . __( 'Component disabled', 'pods' ) );
		}//end if

		$ui->manage();
	}

	/**
	 * Get the admin upgrade page
	 */
	public function admin_upgrade() {

		foreach ( PodsInit::$upgrades as $old_version => $new_version ) {
			if ( version_compare( $old_version, PodsInit::$version_last, '<=' ) && version_compare( PodsInit::$version_last, $new_version, '<' ) ) {
				$new_version = str_replace( '.', '_', $new_version );

				pods_view( PODS_DIR . 'ui/admin/upgrade/upgrade_' . $new_version . '.php', compact( array_keys( get_defined_vars() ) ) );

				break;
			}
		}
	}

	/**
	 * Get the admin help page
	 */
	public function admin_help() {
		// Add our custom callouts.
		$this->handle_callouts_updates();

		if ( $this->has_horizontal_callout() ) {
			add_action( 'pods_admin_before_help', [ $this, 'admin_manage_callouts' ] );
		} else {
			add_action( 'pods_admin_after_help', [ $this, 'admin_manage_callouts' ] );
		}

		pods_view( PODS_DIR . 'ui/admin/help.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Add pods specific capabilities.
	 *
	 * @param array $capabilities List of extra capabilities to add.
	 *
	 * @return array
	 */
	public function admin_capabilities( $capabilities ) {

		$pods = pods_api()->load_pods(
			array(
				'type' => array(
					'settings',
					'post_type',
					'taxonomy',
				),
			)
		);

		$other_pods = pods_api()->load_pods(
			array(
				'type' => array(
					'pod',
					'table',
				),
			)
		);

		$pods = array_merge( $pods, $other_pods );

		$capabilities[] = 'pods';
		$capabilities[] = 'pods_content';
		$capabilities[] = 'pods_settings';
		$capabilities[] = 'pods_components';

		foreach ( $pods as $pod ) {
			if ( 'settings' === $pod['type'] ) {
				$capabilities[] = 'pods_edit_' . $pod['name'];
			} elseif ( 'post_type' === $pod['type'] ) {
				$capability_type = pods_v_sanitized( 'capability_type_custom', $pod['options'], pods_v( 'name', $pod ) );

				if ( 'custom' === pods_v( 'capability_type', $pod['options'] ) && is_string( $capability_type ) && 0 < strlen( $capability_type ) ) {
					$capabilities[] = 'read_' . $capability_type;
					$capabilities[] = 'edit_' . $capability_type;
					$capabilities[] = 'delete_' . $capability_type;

					if ( 1 === (int) pods_v( 'capability_type_extra', $pod['options'], 1 ) ) {
						$capability_type_plural = $capability_type . 's';

						$capabilities[] = 'read_private_' . $capability_type_plural;
						$capabilities[] = 'edit_' . $capability_type_plural;
						$capabilities[] = 'edit_others_' . $capability_type_plural;
						$capabilities[] = 'edit_private_' . $capability_type_plural;
						$capabilities[] = 'edit_published_' . $capability_type_plural;
						$capabilities[] = 'publish_' . $capability_type_plural;
						$capabilities[] = 'delete_' . $capability_type_plural;
						$capabilities[] = 'delete_private_' . $capability_type_plural;
						$capabilities[] = 'delete_published_' . $capability_type_plural;
						$capabilities[] = 'delete_others_' . $capability_type_plural;
					}
				}
			} elseif ( 'taxonomy' === $pod['type'] ) {
				if ( 'custom' === pods_v( 'capability_type', $pod['options'], 'terms' ) ) {
					$capability_type = pods_v_sanitized( 'capability_type_custom', $pod['options'], pods_v( 'name', $pod ) . 's' );

					$capability_type       .= '_term';
					$capability_type_plural = $capability_type . 's';

					// Singular
					$capabilities[] = 'edit_' . $capability_type;
					$capabilities[] = 'delete_' . $capability_type;
					$capabilities[] = 'assign_' . $capability_type;
					// Plural
					$capabilities[] = 'manage_' . $capability_type_plural;
					$capabilities[] = 'edit_' . $capability_type_plural;
					$capabilities[] = 'delete_' . $capability_type_plural;
					$capabilities[] = 'assign_' . $capability_type_plural;
				}
			} else {
				$capabilities[] = 'pods_add_' . $pod['name'];
				$capabilities[] = 'pods_edit_' . $pod['name'];
				$capabilities[] = 'pods_delete_' . $pod['name'];

				if ( $pod instanceof Pod ) {
					$author_field = $pod->get_field( 'author', null, false );
				} else {
					$author_field = pods_v( 'author', $pod['fields'] );
				}

				if ( $author_field && 'pick' === $author_field['type'] && 'user' === $author_field['pick_object'] ) {
					$capabilities[] = 'pods_edit_others_' . $pod['name'];
					$capabilities[] = 'pods_delete_others_' . $pod['name'];
				}

				$actions_enabled = pods_v( 'ui_actions_enabled', $pod['options'] );

				if ( ! empty( $actions_enabled ) ) {
					$actions_enabled = (array) $actions_enabled;
				} else {
					$actions_enabled = array();
				}

				$available_actions = array(
					'add',
					'edit',
					'duplicate',
					'delete',
					'reorder',
					'export',
				);

				if ( ! empty( $actions_enabled ) ) {
					$actions_disabled = array(
						'view' => 'view',
					);

					foreach ( $available_actions as $action ) {
						if ( ! in_array( $action, $actions_enabled, true ) ) {
							$actions_disabled[ $action ] = $action;
						}
					}

					if ( ! in_array( 'export', $actions_disabled, true ) ) {
						$capabilities[] = 'pods_export_' . $pod['name'];
					}

					if ( ! in_array( 'reorder', $actions_disabled, true ) ) {
						$capabilities[] = 'pods_reorder_' . $pod['name'];
					}
				} elseif ( 1 === (int) pods_v( 'ui_export', $pod['options'], 0 ) ) {
					$capabilities[] = 'pods_export_' . $pod['name'];
				}//end if
			}//end if
		}//end foreach

		return $capabilities;
	}

	/**
	 * Handle ajax calls for the administration
	 */
	public function admin_ajax() {

		if ( false === headers_sent() ) {
			pods_session_start();

			header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
		}

		// Sanitize input
		// @codingStandardsIgnoreLine
		$params = pods_unslash( (array) $_POST );

		foreach ( $params as $key => $value ) {
			if ( 'action' === $key ) {
				continue;
			}

			// Fixup $_POST data @codingStandardsIgnoreLine
			$_POST[ str_replace( '_podsfix_', '', $key ) ] = $_POST[ $key ];

			// Fixup $params with unslashed data
			$params[ str_replace( '_podsfix_', '', $key ) ] = $value;

			// Unset the _podsfix_* keys
			unset( $params[ $key ] );
		}

		$params = (object) $params;

		$methods = array(
			'add_pod'            => array( 'priv' => true ),
			'save_pod'           => array( 'priv' => true ),
			'load_sister_fields' => array( 'priv' => true ),
			'process_form'       => array( 'custom_nonce' => true ),
			// priv handled through nonce
			'upgrade'            => array( 'priv' => true ),
			'migrate'            => array( 'priv' => true ),
		);

		/**
		 * AJAX Callbacks in field editor
		 *
		 * @since unknown
		 *
		 * @param array     $methods Callback methods.
		 * @param PodsAdmin $obj     PodsAdmin object.
		 */
		$methods = apply_filters( 'pods_admin_ajax_methods', $methods, $this );

		if ( ! isset( $params->method ) || ! isset( $methods[ $params->method ] ) ) {
			pods_error( __( 'Invalid AJAX request', 'pods' ), $this );
		}

		$defaults = array(
			'priv'         => null,
			'name'         => $params->method,
			'custom_nonce' => null,
		);

		$method = (object) array_merge( $defaults, (array) $methods[ $params->method ] );

		if (
			true !== $method->custom_nonce
			&& (
				! is_user_logged_in()
				|| ! isset( $params->_wpnonce )
				|| false === wp_verify_nonce( $params->_wpnonce, 'pods-' . $params->method )
			)
		) {
			pods_error( __( 'Unauthorized request', 'pods' ), $this );
		}

		// Cleaning up $params
		unset( $params->action );
		unset( $params->method );

		if ( true !== $method->custom_nonce ) {
			unset( $params->_wpnonce );
		}

		// Check permissions (convert to array to support multiple)
		if ( ! empty( $method->priv ) && ! pods_is_admin( array( 'pods' ) ) ) {
			if ( true !== $method->priv && pods_is_admin( $method->priv ) ) {
				// They have access to the custom priv.
			} else {
				// They do not have access.
				pods_error( __( 'Access denied', 'pods' ), $this );
			}
		}

		$params->method = $method->name;

		$method_name = $method->name;

		$params = apply_filters( "pods_api_{$method_name}", $params, $method );

		$api = pods_api();

		$api->display_errors = false;

		if ( 'upgrade' === $method->name ) {
			$output = (string) pods_upgrade( $params->version )->ajax( $params );
		} elseif ( 'migrate' === $method->name ) {
			$output = (string) apply_filters( 'pods_api_migrate_run', $params );
		} else {
			if ( ! method_exists( $api, $method->name ) ) {
				pods_error( __( 'API method does not exist', 'pods' ), $this );
			} elseif ( 'save_pod' === $method->name ) {
				if ( isset( $params->field_data_json ) && is_array( $params->field_data_json ) ) {
					$params->fields = $params->field_data_json;

					unset( $params->field_data_json );

					foreach ( $params->fields as $k => $v ) {
						if ( empty( $v ) ) {
							unset( $params->fields[ $k ] );
						} elseif ( ! is_array( $v ) ) {
							$params->fields[ $k ] = (array) @json_decode( $v, true );
						}
					}
				}
			}

			// Dynamically call the API method
			$params = (array) $params;

			$output = call_user_func( array( $api, $method->name ), $params );
		}//end if

		// Output in json format
		if ( false !== $output ) {

			/**
			 * Pods Admin AJAX request was successful
			 *
			 * @since  2.6.8
			 *
			 * @param array               $params AJAX parameters.
			 * @param array|object|string $output Output for AJAX request.
			 */
			do_action( "pods_admin_ajax_success_{$method->name}", $params, $output );

			if ( is_array( $output ) || is_object( $output ) ) {
				wp_send_json( $output );
			} else {
				// @codingStandardsIgnoreLine
				echo $output;
			}
		} else {
			pods_error( __( 'There was a problem with your request.', 'pods' ) );
		}//end if

		die();
		// KBAI!
	}

	/**
	 * Profiles the Pods configuration
	 *
	 * @param null|string|array $pod             Which Pod(s) to get configuration for. Can be a the name
	 *                                           of one Pod, or an array of names of Pods, or null, which is the
	 *                                           default, to profile all Pods.
	 * @param bool              $full_field_info If true all info about each field is returned. If false,
	 *                                           which is the default only name and type, will be returned.
	 *
	 * @return array
	 *
	 * @since 2.7.0
	 * @deprecated 2.8.0
	 */
	public function configuration( $pod = null, $full_field_info = false ) {
		pods_deprecated( 'PodsAdmin::configuration', '2.8' );

		$api = pods_api();

		if ( null === $pod ) {
			$the_pods = $api->load_pods();
		} elseif ( is_array( $pod ) ) {
			foreach ( $pod as $p ) {
				$the_pods[] = $api->load_pod( $p );
			}
		} else {
			$the_pods[] = $api->load_pod( $pod );
		}

		foreach ( $the_pods as $the_pod ) {
			$configuration[ $the_pod['name'] ] = array(
				'name'    => $the_pod['name'],
				'ID'      => $the_pod['id'],
				'storage' => $the_pod['storage'],
				'fields'  => $the_pod['fields'],
			);
		}

		if ( ! $full_field_info ) {
			foreach ( $the_pods as $the_pod ) {
				$fields = $configuration[ $the_pod['name'] ]['fields'];

				unset( $configuration[ $the_pod['name'] ]['fields'] );

				foreach ( $fields as $field ) {
					$info = array(
						'name' => $field['name'],
						'type' => $field['type'],
					);

					if ( 'pick' === $info['type'] ) {
						$info['pick_object'] = $field['pick_object'];

						if ( isset( $field['pick_val'] ) && '' !== $field['pick_val'] ) {
							$info['pick_val'] = $field['pick_val'];
						}
					}

					if ( is_array( $info ) ) {
						$configuration[ $the_pod['name'] ]['fields'][ $field['name'] ] = $info;
					}

					unset( $info );

				}//end foreach
			}//end foreach
		}//end if

		if ( is_array( $configuration ) ) {
			return $configuration;
		}

	}

	/**
	 * Build UI for extending REST API, if makes sense to do so.
	 *
	 * @since  2.6.0
	 *
	 * @access protected
	 */
	protected function rest_admin() {

		if ( function_exists( 'register_rest_field' ) ) {
			add_filter(
				'pods_admin_setup_edit_field_options', array(
					$this,
					'add_rest_fields_to_field_editor',
				), 12, 2
			);
			add_filter( 'pods_admin_setup_edit_field_tabs', array( $this, 'add_rest_field_tab' ), 12, 2 );
		}

		add_filter( 'pods_admin_setup_edit_tabs', array( $this, 'add_rest_settings_tab' ), 12, 2 );
		add_filter( 'pods_admin_setup_edit_options', array( $this, 'add_rest_settings_tab_fields' ), 12, 2 );

	}

	/**
	 * Check if Pod type <em>could</em> extend core REST API response
	 *
	 * @since  2.5.6
	 *
	 * @access protected
	 *
	 * @param array $pod Pod options.
	 *
	 * @return bool
	 */
	protected function restable_pod( $pod ) {

		$type = $pod['type'];

		$restable_types = array(
			'post_type',
			'user',
			'taxonomy',
			'media',
		);

		return in_array( $type, $restable_types, true );

	}

	/**
	 * Add a rest api tab.
	 *
	 * @since 2.6.0
	 *
	 * @param array $tabs Tab array.
	 * @param array $pod  Pod options.
	 *
	 * @return array
	 */
	public function add_rest_settings_tab( $tabs, $pod ) {
		if ( ! $this->restable_pod( $pod ) ) {
			return $tabs;
		}

		$tabs['rest-api'] = __( 'REST API', 'pods' );

		return $tabs;

	}

	/**
	 * Populate REST API tab.
	 *
	 * @since 0.1.0
	 *
	 * @param array $options Tab options.
	 * @param Pod   $pod     Pod options.
	 *
	 * @return array
	 */
	public function add_rest_settings_tab_fields( $options, $pod ) {
		if ( ! $this->restable_pod( $pod ) ) {
			return $options;
		}

		$options['rest-api'] = [
			'rest_enable' => [
				'label'      => __( 'Enable', 'pods' ),
				'help'       => __( 'Add REST API support for this Pod.', 'pods' ),
				'type'       => 'boolean',
				'default'    => '',
				'dependency' => true,
			],
			'rest_base'   => [
				'label'      => __( 'REST Base (if any)', 'pods' ),
				'help'       => __( 'This will form the url for the route. Default / empty value here will use the pod name.', 'pods' ),
				'type'       => 'text',
				'default'    => '',
				'depends-on' => [ 'rest_enable' => true ],
			],
			'rest_namespace'   => [
				'label'       => __( 'REST API namespace', 'pods' ),
				'help'        => __( 'This will change the namespace URL of the REST API route to a different one from the default one that all normal route endpoints use.', 'pods' ),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => 'wp/v2',
				'depends-on'  => [ 'rest_enable' => true ],
			],
			'read_all'    => [
				'label'      => __( 'Show All Fields (read-only)', 'pods' ),
				'help'       => __( 'Show all fields in REST API. If unchecked fields must be enabled on a field by field basis.', 'pods' ),
				'type'       => 'boolean',
				'default'    => '',
				'depends-on' => [ 'rest_enable' => true ],
				'dependency' => true,
			],
			'read_all_access'   => [
				'label'             => __( 'Read All Access', 'pods' ),
				'help'              => __( 'By default the REST API will allow the fields to be returned for everyone who has access to that endpoint/object. You can also restrict the access of your field based on whether the person is logged in.', 'pods' ),
				'type'              => 'boolean',
				'boolean_yes_label' => __( 'Require being logged in to read all field values via REST', 'pods' ),
				'depends-on'        => [
					'read_all' => true,
				],
			],
			'write_all'   => [
				'label'             => __( 'Allow All Fields To Be Updated', 'pods' ),
				'help'              => __( 'Allow all fields to be updated via the REST API. If unchecked fields must be enabled on a field by field basis.', 'pods' ),
				'type'              => 'boolean',
				'default'           => pods_v( 'name', $pod ),
				'depends-on'        => [ 'rest_enable' => true, 'read_all' => true ],
			],
			/*'write_all_access'   => [
				'label'             => __( 'Write All Access', 'pods' ),
				'help'              => __( 'By default the REST API will allow the fields to be written by everyone who has access to edit that object. You can also restrict the access of your field based on whether the person is logged in.', 'pods' ),
				'type'              => 'boolean',
				'boolean_yes_label' => __( 'Require being logged in to write to all field values via REST', 'pods' ),
				'depends-on'        => [
					'write_all' => true,
				],
			],*/
			'rest_api_field_mode'   => [
				'label'             => __( 'Field Mode', 'pods' ),
				'help'              => __( 'Specify how you would like your values returned in the REST API responses. If you choose to show Both raw and rendered values then an object will be returned for each field that contains the value and rendered properties.', 'pods' ),
				'type'              => 'pick',
				'pick_format_single' => 'radio',
				'default'           => 'value',
				'depends-on'        => [ 'rest_enable' => true ],
				'data'       => [
					'value'            => __( 'Raw values', 'pods' ),
					'render'           => __( 'Rendered values', 'pods' ),
					'value_and_render' => __( 'Both raw and rendered values {value: raw_value, rendered: rendered_value}', 'pods' ),
				],
			],
			'rest_api_field_location'   => [
				'label'              => __( 'Field Location', 'pods' ),
				'help'               => __( 'Specify where you would like your values returned in the REST API responses. To show in the "meta" object of the response, you must have Custom Fields enabled in the Post Type Supports features.', 'pods' ),
				'type'               => 'pick',
				'pick_format_single' => 'radio',
				'default'            => 'object',
				'depends-on'         => [ 'rest_enable' => true ],
				'data'               => [
					'object' => __( 'Show as a custom object field (response.field_name)', 'pods' ),
					'meta'   => __( 'Include in the meta object (response.meta.field_name)', 'pods' ),
				],
			],
		];

		if ( ! $pod->is_extended() ) {
			unset( $options['rest_base'] );
			unset( $options['rest_namespace'] );
		}

		if ( 'post_type' !== $pod->get_type() ) {
			unset( $options['rest_api_field_location'] );
		}

		return $options;
	}

	/**
	 * Add a REST API section to advanced tab of field editor.
	 *
	 * @since 2.5.6
	 *
	 * @param array $options Tab options.
	 * @param array $pod     Pod options.
	 *
	 * @return array
	 */
	public function add_rest_fields_to_field_editor( $options, $pod ) {
		if ( ! $this->restable_pod( $pod ) ) {
			return $options;
		}

		$layout_non_input_field_types = PodsForm::layout_field_types() + PodsForm::non_input_field_types();

		$options['rest'] = [
			'rest_read_write'    => [
				'name'  => 'rest_read_write',
				'type'  => 'heading',
				'label' => __( 'Read/Write', 'pods' ),
			],
			'rest_read'          => [
				'label'       => __( 'Read via REST API', 'pods' ),
				'help'        => __( 'Should this field be readable via the REST API? You must enable REST API support for this Pod.', 'pods' ),
				'type'        => 'boolean',
				'default'     => '',
				'excludes-on' => [
					'type' => $layout_non_input_field_types,
				],
			],
			'rest_read_access'   => [
				'label'             => __( 'Read Access', 'pods' ),
				'help'              => __( 'By default the REST API will allow the fields to be returned for everyone who has access to that endpoint/object. You can also restrict the access of your field based on whether the person is logged in.', 'pods' ),
				'type'              => 'boolean',
				'boolean_yes_label' => __( 'Require being logged in to read this field value via REST', 'pods' ),
				'depends-on'        => [
					'rest_read' => true,
				],
				'excludes-on'       => [
					'type' => $layout_non_input_field_types,
				],
			],
			'rest_write'         => [
				'label'       => __( 'Write via REST API', 'pods' ),
				'help'        => __( 'Should this field be writeable via the REST API? You must enable REST API support for this Pod.', 'pods' ),
				'type'        => 'boolean',
				'default'     => '',
				'excludes-on' => [
					'type' => $layout_non_input_field_types,
				],
			],
			/*'rest_write_access'   => [
				'label'             => __( 'Write Access', 'pods' ),
				'help'              => __( 'By default the REST API will allow the fields to be written by everyone who has access to edit that object. You can also restrict the access of your field based on whether the person is logged in.', 'pods' ),
				'type'              => 'boolean',
				'boolean_yes_label' => __( 'Require being logged in to write to this field value via REST', 'pods' ),
				'depends-on'        => [
					'rest_write' => true,
				],
				'excludes-on'       => [
					'type' => $layout_non_input_field_types,
				],
			],*/
			'rest_field_options' => [
				'name'       => 'rest_field_options',
				'label'      => __( 'Relationship Field Options', 'pods' ),
				'type'       => 'heading',
				'depends-on' => [
					'type' => 'pick',
				],
			],
			'rest_pick_response' => [
				'label'              => __( 'Response Type', 'pods' ),
				'help'               => __( 'This will determine what amount of data for the related items will be returned.', 'pods' ),
				'type'               => 'pick',
				'pick_format_single' => 'dropdown',
				'default'            => 'array',
				'depends-on'         => [
					'type' => 'pick',
				],
				'dependency'         => true,
				'data'               => [
					'array'  => __( 'All fields', 'pods' ),
					'id'     => __( 'ID only', 'pods' ),
					'name'   => __( 'Name only', 'pods' ),
					'custom' => __( 'Custom return (specify field to return)', 'pods' ),
				],
				'excludes-on'        => [
					'type' => $layout_non_input_field_types,
				],
			],
			'rest_pick_depth'    => [
				'label'       => __( 'Depth', 'pods' ),
				'help'        => __( 'How far to traverse relationships in response. 1 will get you all of the fields on the related item. 2 will get you all of those fields plus related items and their fields. The higher the depth, the more data will be returned and the slower performance the REST API calls will be. Updates to this field do NOT take depth into account, so you will always send the ID of the related item when saving.', 'pods' ),
				'type'        => 'number',
				'default'     => '1',
				'depends-on'  => [
					'type'               => 'pick',
					'rest_pick_response' => 'array',
				],
				'excludes-on' => [
					'type' => $layout_non_input_field_types,
				],
			],
			'rest_pick_custom'   => [
				'label'       => __( 'Custom return', 'pods' ),
				'help'        => __( 'Specify the field to use following the established this_field_name.ID traversal pattern. You must include this field name in the selector for this to work properly.', 'pods' ),
				'type'        => 'text',
				'default'     => '',
				'placeholder' => 'this_field_name.ID',
				'depends-on'  => [
					'type'               => 'pick',
					'rest_pick_response' => 'custom',
				],
				'excludes-on' => [
					'type' => $layout_non_input_field_types,
				],
			],
		];

		return $options;
	}

	/**
	 * Add REST field tab
	 *
	 * @since 2.5.6
	 *
	 * @param array $tabs Tab list.
	 * @param array $pod  The ood object.
	 *
	 * @return array
	 */
	public function add_rest_field_tab( $tabs, $pod ) {
		if ( ! $this->restable_pod( $pod ) ) {
			return $tabs;
		}

		$tabs['rest'] = __( 'REST API', 'pods' );

		return $tabs;
	}

	/**
	 * Add Pods-specific debug info to Site Info debug area.
	 *
	 * @since 2.7.13
	 *
	 * @param array $info Debug info.
	 *
	 * @return array Debug info with Pods-specific debug info added.
	 */
	public function add_debug_information( $info ) {
		$auto_start = pods_session_auto_start();

		if ( 'auto' !== $auto_start ) {
			// Turn boolean into 0/1.
			$auto_start = (int) $auto_start;
		}

		// Turn into a string.
		$auto_start = (string) $auto_start;

		$settings = pods_container( Settings::class );

		$settings_fields = $settings->get_setting_fields();

		$settings_values = $settings->get_settings();

		$auto_start = pods_v( $auto_start, $settings_fields['session_auto_start']['data'], __( 'Unknown', 'pods' ) );

		global $wpdb;

		$info['pods'] = [
			'label'       => 'Pods',
			'description' => __( 'Debug information for Pods installations.', 'pods' ),
			'fields'      => [
				'pods-version'                       => [
					'label' => __( 'Pods Version', 'pods' ),
					'value' => PODS_VERSION,
				],
				'pods-first-version'                 => [
					'label' => __( 'Pods Version (First installed)', 'pods' ),
					'value' => get_option( 'pods_framework_version_first', PODS_VERSION ),
				],
				'pods-last-version'                  => [
					'label' => __( 'Pods Version (Last updated from)', 'pods' ),
					'value' => get_option( 'pods_framework_version_last', PODS_VERSION ),
				],
				'pods-server-software'               => [
					'label' => __( 'Server Software', 'pods' ),
					'value' => ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A',
				],
				'pods-user-agent'                    => [
					'label' => __( 'Your User Agent', 'pods' ),
					'value' => ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A',
				],
				'pods-session-save-path'             => [
					'label' => __( 'Session Save Path', 'pods' ),
					'value' => session_save_path(),
				],
				'pods-session-save-path-exists'      => [
					'label' => __( 'Session Save Path Exists', 'pods' ),
					'value' => file_exists( session_save_path() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-session-save-path-writable'    => [
					'label' => __( 'Session Save Path Writeable', 'pods' ),
					'value' => is_writable( session_save_path() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-session-max-lifetime'          => [
					'label' => __( 'Session Max Lifetime', 'pods' ),
					'value' => ini_get( 'session.gc_maxlifetime' ),
				],
				'pods-opcode-cache-apc'              => [
					'label' => __( 'Opcode Cache: Apc', 'pods' ),
					'value' => function_exists( 'apc_cache_info' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-opcode-cache-memcached'        => [
					'label' => __( 'Opcode Cache: Memcached', 'pods' ),
					'value' => class_exists( 'eaccelerator_put' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-opcode-cache-opcache'          => [
					'label' => __( 'Opcode Cache: OPcache', 'pods' ),
					'value' => function_exists( 'opcache_get_status' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-opcode-cache-redis'            => [
					'label' => __( 'Opcode Cache: Redis', 'pods' ),
					'value' => class_exists( 'xcache_set' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-object-cache-apc'              => [
					'label' => __( 'Object Cache: APC', 'pods' ),
					'value' => function_exists( 'apc_cache_info' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-object-cache-apcu'             => [
					'label' => __( 'Object Cache: APCu', 'pods' ),
					'value' => function_exists( 'apcu_cache_info' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-object-cache-memcache'         => [
					'label' => __( 'Object Cache: Memcache', 'pods' ),
					'value' => class_exists( 'Memcache' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-object-cache-memcached'        => [
					'label' => __( 'Object Cache: Memcached', 'pods' ),
					'value' => class_exists( 'Memcached' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-object-cache-redis'            => [
					'label' => __( 'Object Cache: Redis', 'pods' ),
					'value' => class_exists( 'Redis' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-memory-current-usage'          => [
					'label' => __( 'Current Memory Usage', 'pods' ),
					'value' => number_format_i18n( memory_get_usage() / 1024 / 1024, 3 ) . 'M' . ( defined( 'WP_MEMORY_LIMIT' ) ? ' / ' . WP_MEMORY_LIMIT : '' ),
				],
				'pods-memory-current-usage-real'     => [
					'label' => __( 'Current Memory Usage (real)', 'pods' ),
					'value' => number_format_i18n( memory_get_usage( true ) / 1024 / 1024, 3 ) . 'M',
				],
				'pods-network-wide'                  => [
					'label' => __( 'Pods Network-Wide Activated', 'pods' ),
					'value' => is_plugin_active_for_network( basename( PODS_DIR ) . '/init.php' ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-install-location'              => [
					'label' => __( 'Pods Install Location', 'pods' ),
					'value' => str_replace( ABSPATH, '/', PODS_DIR ),
				],
				'pods-developer'                     => [
					'label' => __( 'Pods Developer Activated' ),
					'value' => ( pods_developer() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-tableless-mode'                => [
					'label' => __( 'Pods Tableless Mode Activated', 'pods' ),
					'value' => ( pods_tableless() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-relationship-table-enabled'    => [
					'label' => __( 'Pods Relationship Table Enabled', 'pods' ),
					'value' => ( pods_podsrel_enabled() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-relationship-table-status'     => [
					'label' => __( 'Pods Relationship Table Count' ),
					'value' => ( ! pods_tableless() ? number_format( (float) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}podsrel" ) ) : 'No table' ),
				],
				'pods-light-mode'                    => [
					'label' => __( 'Pods Light Mode Activated', 'pods' ),
					'value' => ( pods_light() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-strict'                        => [
					'label' => __( 'Pods Strict Activated' ),
					'value' => ( pods_strict( false ) ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-allow-deprecated'              => [
					'label' => __( 'Pods Allow Deprecated' ),
					'value' => ( pods_allow_deprecated() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-api-cache'                     => [
					'label' => __( 'Pods API Cache Activated' ),
					'value' => ( pods_api_cache() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-shortcode-allow-evaluate-tags' => [
					'label' => __( 'Pods Shortcode Allow Evaluate Tags' ),
					'value' => ( pods_shortcode_allow_evaluate_tags() ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
				'pods-can-use-sessions'              => [
					'label' => __( 'Pods Can Use Sessions' ),
					'value' => ( pods_can_use_sessions( true ) ) ? __( 'Yes', 'pods' ) : __( 'No', 'pods' ),
				],
			],
		];

		foreach ( $settings_fields as $setting_name => $setting_field ) {
			if ( empty( $setting_field['site_health_include_in_info'] ) ) {
				continue;
			}

			if ( isset( $setting_field['name'] ) ) {
				$setting_name = $setting_field['name'];
			}

			$setting_key = 'pods-settings-' . sanitize_title_with_dashes( $setting_name );

			$value = pods_v( $setting_name, $settings_values );

			$original_value = is_array( $value ) ? implode( ',', $value ) : (string) $value;

			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					$v = (string) $v;

					$has_v = 0 < strlen( $v );

					if ( $has_v && isset( $setting_field['site_health_data'] ) && isset( $setting_field['site_health_data'][ $v ] ) ) {
						$value[ $k ] = $setting_field['site_health_data'][ $v ];
					} elseif ( $has_v && isset( $setting_field['data'] ) && isset( $setting_field['data'][ $v ] ) ) {
						$value[ $k ] = $setting_field['data'][ $v ];
					}
				}

				$value = pods_serial_comma( $value );
			} else {
				$value = (string) $value;

				$has_value = 0 < strlen( $value );

				if ( $has_value && isset( $setting_field['site_health_data'] ) && isset( $setting_field['site_health_data'][ $value ] ) ) {
					$value = $setting_field['site_health_data'][ $value ];
				} elseif ( $has_value && isset( $setting_field['data'] ) && isset( $setting_field['data'][ $value ] ) ) {
					$value = $setting_field['data'][ $value ];
				} elseif ( 'boolean' === $setting_field['data'] || '1' === $value || '0' === $value ) {
					$value = '1' === $value ? __( 'Yes', 'pods' ) : __( 'No', 'pods' );
				}
			}

			if ( 'unknown' === $value || '' === $value ) {
				$value = __( 'Unknown', 'pods' );
			}

			$info['pods']['fields'][ $setting_key ] = [
				'label' => $setting_field['label'],
				'value' => $value . ( $value !== $original_value ? ' [' . $setting_name . '=' . $original_value . ']' : '' ),
			];
		}

		// @todo Later we should add which components are active.

		return $info;
	}

	/**
	 * Add our site status tests.
	 *
	 * @since 2.8.0
	 *
	 * @param array $tests The list of status tests.
	 *
	 * @return array The list of status tests.
	 */
	public function site_status_tests( $tests ) {
		$plugin_search_url = 'plugin-install.php?tab=search&type=term&s=';

		if ( is_multisite() && ! is_network_admin() ) {
			$plugin_search_url = network_admin_url( $plugin_search_url );
		} else {
			$plugin_search_url = self_admin_url( $plugin_search_url );
		}

		if ( ! is_pods_alternative_cache_activated() && ! wp_using_ext_object_cache() ) {
			$tests['direct']['pods_alternative_cache'] = [
				'label' => __( 'Pods Alternative Cache', 'pods' ),
				'test'  => static function () use ( $plugin_search_url ) {
					return [
						'label'       => __( 'The Pods Team recommends you install the Pods Alternative Cache plugin', 'pods' ),
						'status'      => 'recommended',
						'badge'       => [
							'label' => __( 'Performance', 'pods' ),
							'color' => 'blue',
						],
						'description' => sprintf( '<p>%s</p>', __( 'You are not using an external object cache for this site. Pods Alternative Cache is usually useful for Pods installs that use Shared Hosting with limited Object Cache capabilities.', 'pods' ) ),
						'actions'     => sprintf( '<p><a href="%s">%s</a></p>', esc_url( $plugin_search_url . urlencode( 'Pods Alternative Cache' ) ), __( 'Install Pods Alternative Cache', 'pods' ) ),
						'test'        => 'pods_alternative_cache',
					];
				},
			];
		}

		return $tests;
	}

	/**
	 * Check whether the requirements were met and maybe display error messages.
	 *
	 * @since 2.9.0
	 *
	 * @param array $requirements List of requirements.
	 *
	 * @return bool Whether the requirements were met.
	 */
	public function check_requirements( array $requirements ) {
		foreach ( $requirements as $requirement ) {
			// Check if requirement passed.
			if ( $requirement['check'] ) {
				continue;
			}

			// Show admin notice if there's a message to be shown.
			if ( ! empty( $requirement['message'] ) && $this->should_show_notices() ) {
				pods_message( $requirement['message'], 'error' );
			}

			return false;
		}

		return true;
	}

	/**
	 * Check whether we should show notices.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether we should show notices.
	 */
	public function should_show_notices() {
		global $pagenow;

		// We only show notices on admin pages.
		if ( ! is_admin() ) {
			return false;
		}

		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		// We only show on the plugins.php page or on Pods Admin pages.
		if (
			(
				'plugins.php' !== $pagenow
				&& 0 !== strpos( $page, 'pods' )
			)
			|| 0 === strpos( $page, 'pods-manage-' )
		) {
			return false;
		}

		return true;
	}

}
