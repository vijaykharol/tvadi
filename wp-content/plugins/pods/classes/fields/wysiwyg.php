<?php

/**
 * @package Pods\Fields
 */
class PodsField_WYSIWYG extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Paragraph';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'wysiwyg';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'WYSIWYG (Visual Editor)';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Paragraph', 'pods' );
		static::$label = __( 'WYSIWYG (Visual Editor)', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {
		$options = [
			static::$type . '_editor'            => [
				'label'                 => __( 'Editor', 'pods' ),
				'default'               => 'tinymce',
				'type'                  => 'pick',
				'data'                  => apply_filters(
					'pods_form_ui_field_wysiwyg_editors', [
						'tinymce'  => __( 'TinyMCE (WP Default, cannot be used with repeatable fields)', 'pods' ),
						'quill'    => __( 'Quill Editor (Limited line break functionality)', 'pods' ),
						'cleditor' => __( 'CLEditor (No longer available, the fallback uses Quill Editor)', 'pods' ),
					]
				),
				'pick_format_single'    => 'dropdown',
				'pick_show_select_text' => 0,
				'dependency'            => true,
			],
			'editor_options'                     => [
				'label'         => __( 'Editor Options', 'pods' ),
				'type'          => 'boolean_group',
				'depends-on'    => [ static::$type . '_editor' => 'tinymce' ],
				'boolean_group' => [
					static::$type . '_media_buttons' => [
						'label'   => __( 'Enable Media Buttons', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					],
				],
			],
			static::$type . '_editor_height'     => [
				'label'           => __( 'Editor Height', 'pods' ),
				'help'            => __( 'Height in pixels', 'pods' ),
				'default'         => '',
				'type'            => 'number',
				'depends-on'      => [ static::$type . '_editor' => 'tinymce' ],
				'number_decimals' => 0,
			],
			'output_options'                     => [
				'label'         => __( 'Output Options', 'pods' ),
				'type'          => 'boolean_group',
				'boolean_group' => [
					static::$type . '_trim'            => [
						'label'      => __( 'Trim extra whitespace before/after contents', 'pods' ),
						'default'    => 1,
						'type'       => 'boolean',
					],
					static::$type . '_trim_lines'        => [
						'label'   => __( 'Trim whitespace at the end of lines', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					static::$type . '_trim_p_brs'        => [
						'label'   => __( 'Remove blank lines including empty "p" tags and "br" tags', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					static::$type . '_trim_extra_lines'  => [
						'label'   => __( 'Remove extra blank lines (when there are 3+ blank lines, replace with a maximum of 2)', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					static::$type . '_sanitize_html'    => [
						'label'      => __( 'Sanitize HTML', 'pods' ),
						'default'    => 1,
						'help'       => __( 'This sanitizes things like script tags and other content not normally allowed in WordPress content. Disable this only if you trust users who will have access to enter content into this field.', 'pods' ),
						'type'       => 'boolean',
						'dependency' => true,
					],
					static::$type . '_oembed'          => [
						'label'   => __( 'Enable oEmbed', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
						'help'    => [
							__( 'Embed videos, images, tweets, and other content.', 'pods' ),
							'https://wordpress.org/support/article/embeds/',
						],
					],
					static::$type . '_wptexturize'     => [
						'label'   => __( 'Enable wptexturize', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => [
							__( 'Transforms less-beautiful text characters into stylized equivalents.', 'pods' ),
							'https://developer.wordpress.org/reference/functions/wptexturize/',
						],
					],
					static::$type . '_convert_chars'   => [
						'label'   => __( 'Enable convert_chars', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => [
							__( 'Converts text into valid XHTML and Unicode', 'pods' ),
							'https://developer.wordpress.org/reference/functions/convert_chars/',
						],
					],
					static::$type . '_wpautop'         => [
						'label'   => __( 'Enable wpautop', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => [
							__( 'Changes double line-breaks in the text into HTML paragraphs', 'pods' ),
							'https://developer.wordpress.org/reference/functions/wpautop/',
						],
					],
					static::$type . '_allow_shortcode' => [
						'label'      => __( 'Allow Shortcodes', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => [
							__( 'Embed [shortcodes] that help transform your static content into dynamic content.', 'pods' ),
							'https://codex.wordpress.org/Shortcode_API',
						],
					],
				],
			],
			static::$type . '_allowed_html_tags' => [
				'label'   => __( 'Allowed HTML Tags', 'pods' ),
				'default' => '',
				'type'    => 'text',
				'help'    => __( 'Format: strong em a ul ol li b i', 'pods' ),
			],
		];

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = 'LONGTEXT';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		$value = $this->strip_html( $value, $options );

		/**
		 * Allow filtering of the display value for the WYSIWYG field type before it's processed.
		 *
		 * NOTE: HTML has already been stripped at this point.
		 *
		 * @since 3.1.0
		 *
		 * @param mixed|null      $value   Current value.
		 * @param string          $type    Field type.
		 * @param string|null     $name    Field name.
		 * @param array|null      $options Field options.
		 * @param array|null      $pod     Pod information.
		 * @param int|string|null $id      Current item ID.
		 */
		$value = apply_filters( 'pods_form_ui_field_wysiwyg_display_value_pre_process', $value, static::$type, $name, $options, $pod, $id );

		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		if ( 1 === (int) pods_v( static::$type . '_oembed', $options, 0 ) ) {
			$post_temp = false;

			// Workaround for WP_Embed since it needs a $post to work from
			if ( 'post_type' === pods_v( 'type', $pod ) && 0 < $id && ( ! isset( $GLOBALS['post'] ) || empty( $GLOBALS['post'] ) ) ) {
				$post_temp = true;

				// @codingStandardsIgnoreLine
				$GLOBALS['post'] = get_post( $id );
			}

			/**
			 * @var $embed WP_Embed
			 */
			$embed = $GLOBALS['wp_embed'];
			$value = $embed->run_shortcode( $value );
			$value = $embed->autoembed( $value );

			// Cleanup after ourselves
			if ( $post_temp ) {
				// @codingStandardsIgnoreLine
				$GLOBALS['post'] = null;
			}
		}//end if

		if ( 1 === (int) pods_v( static::$type . '_wptexturize', $options, 1 ) ) {
			$value = wptexturize( $value );
		}

		if ( 1 === (int) pods_v( static::$type . '_convert_chars', $options, 1 ) ) {
			$value = convert_chars( $value );
		}

		if ( 1 === (int) pods_v( static::$type . '_wpautop', $options, 1 ) ) {
			$value = wpautop( $value );
		}

		if ( 1 === (int) pods_v( static::$type . '_allow_shortcode', $options, 0 ) ) {
			if ( 1 === (int) pods_v( static::$type . '_wpautop', $options, 1 ) ) {
				$value = shortcode_unautop( $value );
			}

			$value = do_shortcode( $value );
		}

		/**
		 * Allow filtering of the display value for the WYSIWYG field type.
		 *
		 * @since 3.1.0
		 *
		 * @param mixed|null      $value   Current value.
		 * @param string          $type    Field type.
		 * @param string|null     $name    Field name.
		 * @param array|null      $options Field options.
		 * @param array|null      $pod     Pod information.
		 * @param int|string|null $id      Current item ID.
		 */
		return apply_filters( 'pods_form_ui_field_wysiwyg_display_value', $value, static::$type, $name, $options, $pod, $id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;
		$form_field_type = PodsForm::$field_type;

		// Force TinyMCE repeatable fields to not be repeatable because it lacks compatibility.
		if ( 1 === (int) pods_v( 'repeatable', $options ) && 'tinymce' === pods_v( static::$type . '_editor', $options, 'tinymce', true ) ) {
			$options['repeatable'] = 0;
		}

		$value = $this->maybe_sanitize_output( $value, $options );
		$value = $this->normalize_value_for_input( $value, $options, "\n" );

		// Normalize the line breaks for React.
		if ( null !== $value ) {
			$value = str_replace( "\r\n", "\n", $value );
			$value = str_replace( "\r", "\n", $value );
		}

		if ( isset( $options['name'] ) && ! pods_permission( $options ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;

				$field_type = 'textarea';
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;

			$field_type = 'textarea';
		} elseif ( 'tinymce' === pods_v( static::$type . '_editor', $options ) ) {
			// TinyMCE does not support repeatable.
			if ( is_array( $value ) ) {
				$value = implode( "\n", $value );
			}

			$field_type = 'tinymce';

			// Enforce boolean.
			$options[ static::$type . '_media_buttons' ]  = filter_var( pods_v( static::$type . '_media_buttons', $options, true ), FILTER_VALIDATE_BOOLEAN );

			// Set up default editor.
			// @todo Support this properly in React, which will be a challenge.
			$options[ static::$type . '_default_editor' ] = pods_v( static::$type . '_default_editor', $options, wp_default_editor(), true );

			if ( in_array( $options[ static::$type . '_default_editor' ], [ 'html', 'tinymce' ], true ) ) {
				$options[ static::$type . '_default_editor' ] = 'tinymce';
			}

			if (
				(
					function_exists( 'did_filter' )
					&& ! did_filter( 'tiny_mce_before_init' )
				)
				|| ! did_action( 'enqueue_block_editor_assets' )
			) {
				wp_tinymce_inline_scripts();
			}

			wp_enqueue_editor();

			$settings = [];
			$settings['textarea_name'] = $name;
			$settings['media_buttons'] = false;

			if ( ! ( defined( 'PODS_DISABLE_FILE_UPLOAD' ) && true === PODS_DISABLE_FILE_UPLOAD ) && ! ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && true === PODS_UPLOAD_REQUIRE_LOGIN && ! is_user_logged_in() ) && ! ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && ! is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && ( ! is_user_logged_in() || ! current_user_can( PODS_UPLOAD_REQUIRE_LOGIN ) ) ) ) {
				$settings['media_buttons'] = $options[ static::$type . '_media_buttons' ];
			} else {
				$options[ static::$type . '_media_buttons' ] = false;
			}

			$editor_height = (int) pods_v( static::$type . '_editor_height', $options, false );

			if ( $editor_height ) {
				$settings['editor_height'] = $editor_height;
			}

			if ( ! empty( $options[ static::$type . '_tinymce_settings' ] ) ) {
				$settings = array_merge( $settings, $options[ static::$type . '_tinymce_settings' ] );
			}

			// WP will handle the scripting needed, but we won't output the textarea here.
			ob_start();
			wp_editor( $value, '_pods_dfv_' . $name, $settings );
			$unused_output = ob_get_clean();

			// Workaround because the above already outputs the style we need.
			$styles = wp_styles();

			$found_editor_buttons = array_search( 'editor-buttons', $styles->done, true );

			if ( false !== $found_editor_buttons ) {
				unset( $styles->done[ $found_editor_buttons ] );
			}

			$found_dashicons = array_search( 'dashicons', $styles->done, true );

			if ( false !== $found_dashicons ) {
				unset( $styles->done[ $found_dashicons ] );
			}

			wp_print_styles( 'editor-buttons' );
			wp_print_styles( 'dashicons' );
		} elseif ( 'quill' === pods_v( static::$type . '_editor', $options ) ) {
			$field_type = 'quill';
		} elseif ( 'cleditor' === pods_v( static::$type . '_editor', $options ) ) {
			$field_type = 'quill';
		} else {
			// Support custom WYSIWYG integration
			$editor_type = pods_v( static::$type . '_editor', $options );
			do_action( "pods_form_ui_field_wysiwyg_{$editor_type}", $name, $value, $options, $pod, $id );
			do_action( 'pods_form_ui_field_wysiwyg', pods_v( static::$type . '_editor', $options ), $name, $value, $options, $pod, $id );

			return;
		}//end if

		if ( ! empty( $options['disable_dfv'] ) ) {
			return pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
		}

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		$length = (int) pods_v( static::$type . '_max_length', $options, 0 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		$value = wp_trim_words( $value );

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function strip_html( $value, $options = null ) {

		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		// Allow HTML tags.
		$options[ static::$type . '_allow_html' ] = 1;

		return parent::strip_html( $value, $options );
	}
}
