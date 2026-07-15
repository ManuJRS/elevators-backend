<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

if ( ! function_exists( 'twentytwentyfive_menus_setup' ) ) :
	/**
	 * Enables classic navigation menus and registers menu locations.
	 *
	 * Block themes hide Appearance > Menus by default; this restores the
	 * classic Menus screen and exposes locations for assignment.
	 *
	 * @return void
	 */
	function twentytwentyfive_menus_setup() {
		add_theme_support( 'menus' );

		register_nav_menus(
			array(
				'primary' => __( 'Menú principal', 'twentytwentyfive' ),
				'footer'  => __( 'Menú de pie de página', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_menus_setup' );

if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues the theme stylesheet on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$src    = 'style' . $suffix . '.css';

		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( $src ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
		wp_style_add_data(
			'twentytwentyfive-style',
			'path',
			get_parent_theme_file_path( $src )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

add_action( 'wp_head', 'redireccionar_titulo_navbar_a_vue' );
function redireccionar_titulo_navbar_a_vue() {
	if ( ! is_cart() && ! is_checkout() ) {
		return;
	}

	$frontend_url = redireccionar_titulo_navbar_a_vue_build_url();
	?>
	<script>
	document.addEventListener( 'DOMContentLoaded', function () {
		var redirectUrl = <?php echo wp_json_encode( $frontend_url ); ?>;
		var selectors = [
			'.site-branding a',
			'.custom-logo-link',
			'.navbar-brand',
			'.site-title a',
			'.wp-block-site-title a',
			'.wp-block-site-logo a',
		].join( ', ' );

		document.querySelectorAll( selectors ).forEach( function ( element ) {
			element.setAttribute( 'href', redirectUrl );
		} );
	} );
	</script>
	<?php
}

function redireccionar_titulo_navbar_a_vue_build_url() {
	$frontend_base = 'http://localhost:5173/';
	$params        = array();

	if ( is_cart() || is_checkout() ) {
		$params['from_checkout'] = 'true';
	}

	if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
		$cart_items = array();

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product_id = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;
			$quantity   = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 0;

			if ( $product_id > 0 && $quantity > 0 ) {
				$cart_items[] = $product_id . ':' . $quantity;
			}
		}

		if ( ! empty( $cart_items ) ) {
			$params['carrito'] = implode( ',', $cart_items );
		}
	}

	return ! empty( $params ) ? add_query_arg( $params, $frontend_base ) : $frontend_base;
}

/**
 * -----------------------------------------------------------------------------
 * Icono SVG del Footer (Media Uploader → URL)
 * -----------------------------------------------------------------------------
 * Selector de medios en Apariencia > Menús. Guarda la URL del archivo en
 * post meta y la expone en WPGraphQL como `footerSvg` solo para subítems
 * de la ubicación FOOTER.
 */

if ( ! defined( 'TT5_MENU_ITEM_FOOTER_SVG_META' ) ) {
	define( 'TT5_MENU_ITEM_FOOTER_SVG_META', '_menu_item_footer_svg' );
}

if ( ! defined( 'TT5_MENU_ITEM_IS_MAP_META' ) ) {
	define( 'TT5_MENU_ITEM_IS_MAP_META', '_menu_item_is_map' );
}

if ( ! defined( 'TT5_FOOTER_MENU_LOCATION' ) ) {
	define( 'TT5_FOOTER_MENU_LOCATION', 'footer' );
}

/**
 * Encola wp_enqueue_media() solo en la pantalla de menús.
 *
 * @param string $hook_suffix Hook del admin actual.
 * @return void
 */
function tt5_enqueue_menu_svg_media_scripts( $hook_suffix ) {
	if ( 'nav-menus.php' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'tt5_enqueue_menu_svg_media_scripts' );

/**
 * Sanitiza la URL del icono SVG (solo URLs http/https locales o absolutas válidas).
 *
 * @param string $url URL del archivo seleccionado en la Biblioteca de Medios.
 * @return string URL limpia o cadena vacía.
 */
function tt5_sanitize_footer_svg_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	$sanitized = esc_url_raw( $url );

	if ( '' === $sanitized ) {
		return '';
	}

	// Preferir SVG; acepta también imágenes servidas desde uploads.
	$path = (string) wp_parse_url( $sanitized, PHP_URL_PATH );
	$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	if ( $ext && ! in_array( $ext, array( 'svg', 'svgz', 'png', 'jpg', 'jpeg', 'webp', 'gif' ), true ) ) {
		return '';
	}

	return $sanitized;
}

/**
 * Campo de Media Uploader en cada ítem de menú.
 *
 * @param int    $item_id Menu item ID.
 * @param object $item    Menu item data object.
 * @param int    $depth   Depth of menu item.
 * @param array  $args    Menu item args.
 * @param int    $id      Nav menu ID.
 * @return void
 */
function tt5_menu_item_footer_svg_field( $item_id, $item, $depth, $args, $id ) {
	unset( $item, $depth, $args, $id );

	$current = get_post_meta( $item_id, TT5_MENU_ITEM_FOOTER_SVG_META, true );
	$current = is_string( $current ) ? $current : '';
	$field   = 'menu-item-footer-svg[' . (int) $item_id . ']';
	$has_url = '' !== $current;
	?>
	<div class="field-footer-svg description description-wide tt5-footer-svg-field">
		<label>
			<?php esc_html_e( 'Icono SVG del Footer', 'twentytwentyfive' ); ?>
		</label>

		<input
			type="hidden"
			class="tt5-footer-svg-url"
			id="edit-menu-item-footer-svg-<?php echo esc_attr( (string) $item_id ); ?>"
			name="<?php echo esc_attr( $field ); ?>"
			value="<?php echo esc_attr( $current ); ?>"
		/>

		<div class="tt5-footer-svg-actions" style="display:flex;gap:8px;align-items:center;margin:6px 0;">
			<button type="button" class="button tt5-footer-svg-select">
				<?php esc_html_e( 'Seleccionar SVG', 'twentytwentyfive' ); ?>
			</button>
			<button
				type="button"
				class="button tt5-footer-svg-remove"
				style="<?php echo $has_url ? '' : 'display:none;'; ?>"
			>
				<?php esc_html_e( 'Eliminar', 'twentytwentyfive' ); ?>
			</button>
		</div>

		<div
			class="tt5-footer-svg-preview"
			style="display:<?php echo $has_url ? 'block' : 'none'; ?>;margin-top:8px;"
		>
			<?php if ( $has_url ) : ?>
				<img
					src="<?php echo esc_url( $current ); ?>"
					alt=""
					style="max-width:48px;max-height:48px;height:auto;width:auto;display:block;"
				/>
			<?php endif; ?>
		</div>

		<span class="description">
			<?php esc_html_e( 'Selecciona un SVG desde la Biblioteca de Medios. Solo se expone en GraphQL para subelementos (hijos) del menú FOOTER.', 'twentytwentyfive' ); ?>
		</span>
	</div>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'tt5_menu_item_footer_svg_field', 10, 5 );

/**
 * Checkbox: renderizar el subelemento como mapa de Google Maps.
 *
 * @param int    $item_id Menu item ID.
 * @param object $item    Menu item data object.
 * @param int    $depth   Depth of menu item.
 * @param array  $args    Menu item args.
 * @param int    $id      Nav menu ID.
 * @return void
 */
function tt5_menu_item_is_map_field( $item_id, $item, $depth, $args, $id ) {
	unset( $item, $depth, $args, $id );

	$checked = (string) get_post_meta( $item_id, TT5_MENU_ITEM_IS_MAP_META, true );
	$field   = 'menu-item-is-map[' . (int) $item_id . ']';
	?>
	<p class="field-is-map description description-wide tt5-is-map-field">
		<label for="edit-menu-item-is-map-<?php echo esc_attr( (string) $item_id ); ?>">
			<input
				type="checkbox"
				id="edit-menu-item-is-map-<?php echo esc_attr( (string) $item_id ); ?>"
				class="tt5-is-map-checkbox"
				name="<?php echo esc_attr( $field ); ?>"
				value="1"
				<?php checked( $checked, '1' ); ?>
			/>
			<?php esc_html_e( '¿Renderizar como Mapa de Google Maps? (Oculta el texto del enlace)', 'twentytwentyfive' ); ?>
		</label>
		<br />
		<span class="description">
			<?php esc_html_e( 'En GraphQL solo retorna true para subelementos (hijos) del menú FOOTER. Usa la URL del enlace como src/embed del mapa.', 'twentytwentyfive' ); ?>
		</span>
	</p>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'tt5_menu_item_is_map_field', 11, 5 );

/**
 * Guarda la URL del SVG al actualizar un ítem de menú.
 *
 * @param int   $menu_id         ID del menú.
 * @param int   $menu_item_db_id ID del ítem de menú (post).
 * @param array $args            Argumentos del ítem.
 * @return void
 */
function tt5_save_menu_item_footer_svg( $menu_id, $menu_item_db_id, $args ) {
	unset( $menu_id, $args );

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$posted = isset( $_POST['menu-item-footer-svg'] ) && is_array( $_POST['menu-item-footer-svg'] )
		? wp_unslash( $_POST['menu-item-footer-svg'] )
		: array();

	if ( ! isset( $posted[ $menu_item_db_id ] ) ) {
		return;
	}

	$sanitized = tt5_sanitize_footer_svg_url( $posted[ $menu_item_db_id ] );

	if ( '' === $sanitized ) {
		delete_post_meta( $menu_item_db_id, TT5_MENU_ITEM_FOOTER_SVG_META );
		return;
	}

	update_post_meta( $menu_item_db_id, TT5_MENU_ITEM_FOOTER_SVG_META, $sanitized );
}
add_action( 'wp_update_nav_menu_item', 'tt5_save_menu_item_footer_svg', 10, 3 );

/**
 * Guarda el flag booleano is_map al actualizar un ítem de menú.
 * Los checkboxes no se envían si están desmarcados: en ese caso se limpia el meta.
 *
 * @param int   $menu_id         ID del menú.
 * @param int   $menu_item_db_id ID del ítem de menú (post).
 * @param array $args            Argumentos del ítem.
 * @return void
 */
function tt5_save_menu_item_is_map( $menu_id, $menu_item_db_id, $args ) {
	unset( $menu_id, $args );

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$posted = isset( $_POST['menu-item-is-map'] ) && is_array( $_POST['menu-item-is-map'] )
		? wp_unslash( $_POST['menu-item-is-map'] )
		: array();

	$is_map = isset( $posted[ $menu_item_db_id ] ) && '1' === (string) $posted[ $menu_item_db_id ];

	if ( $is_map ) {
		update_post_meta( $menu_item_db_id, TT5_MENU_ITEM_IS_MAP_META, '1' );
		return;
	}

	delete_post_meta( $menu_item_db_id, TT5_MENU_ITEM_IS_MAP_META );
}
add_action( 'wp_update_nav_menu_item', 'tt5_save_menu_item_is_map', 10, 3 );

/**
 * JS del Media Uploader con delegación de eventos (ítems clonados / drag & drop).
 *
 * @return void
 */
function tt5_print_menu_svg_media_script() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'nav-menus' !== $screen->id ) {
		return;
	}
	?>
	<script>
	(function ($) {
		'use strict';

		var mediaFrame = null;

		function getFieldWrap($el) {
			return $el.closest('.tt5-footer-svg-field');
		}

		function setPreview($wrap, url) {
			var $input = $wrap.find('.tt5-footer-svg-url');
			var $preview = $wrap.find('.tt5-footer-svg-preview');
			var $remove = $wrap.find('.tt5-footer-svg-remove');

			$input.val(url || '');

			if (url) {
				$preview
					.html(
						'<img src="' +
							String(url).replace(/"/g, '&quot;') +
							'" alt="" style="max-width:48px;max-height:48px;height:auto;width:auto;display:block;" />'
					)
					.show();
				$remove.show();
			} else {
				$preview.empty().hide();
				$remove.hide();
			}
		}

		$(document).on('click', '.tt5-footer-svg-select', function (event) {
			event.preventDefault();

			var $wrap = getFieldWrap($(this));

			if (typeof wp === 'undefined' || !wp.media) {
				return;
			}

			if (mediaFrame) {
				mediaFrame.off('select');
			}

			mediaFrame = wp.media({
				title: <?php echo wp_json_encode( __( 'Seleccionar icono SVG', 'twentytwentyfive' ) ); ?>,
				button: {
					text: <?php echo wp_json_encode( __( 'Usar este archivo', 'twentytwentyfive' ) ); ?>
				},
				library: {
					type: ['image', 'image/svg+xml']
				},
				multiple: false
			});

			mediaFrame.on('select', function () {
				var attachment = mediaFrame.state().get('selection').first().toJSON();
				var url = (attachment && attachment.url) ? attachment.url : '';
				setPreview($wrap, url);
			});

			mediaFrame.open();
		});

		$(document).on('click', '.tt5-footer-svg-remove', function (event) {
			event.preventDefault();
			setPreview(getFieldWrap($(this)), '');
		});
	})(jQuery);
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts', 'tt5_print_menu_svg_media_script' );

/**
 * Indica si el ítem es hijo y pertenece a la location footer.
 *
 * @param object $menu_item Modelo MenuItem de WPGraphQL.
 * @return bool
 */
function tt5_menu_item_is_footer_child( $menu_item ) {
	$parent_db_id = isset( $menu_item->parentDatabaseId ) ? (int) $menu_item->parentDatabaseId : 0;

	if ( $parent_db_id <= 0 ) {
		return false;
	}

	$locations = ( isset( $menu_item->locations ) && is_array( $menu_item->locations ) )
		? $menu_item->locations
		: array();

	return in_array( TT5_FOOTER_MENU_LOCATION, $locations, true );
}

/**
 * Registra footerSvg en MenuItem (URL del SVG para hijos del FOOTER).
 *
 * @return void
 */
function tt5_register_menu_item_footer_svg_graphql_field() {
	if ( ! function_exists( 'register_graphql_field' ) ) {
		return;
	}

	register_graphql_field(
		'MenuItem',
		'footerSvg',
		array(
			'type'        => 'String',
			'description' => __(
				'URL del icono SVG del ítem. Solo se resuelve para subelementos del menú asignado a FOOTER.',
				'twentytwentyfive'
			),
			'resolve'     => static function ( $menu_item ) {
				if ( ! tt5_menu_item_is_footer_child( $menu_item ) ) {
					return null;
				}

				$database_id = isset( $menu_item->databaseId ) ? (int) $menu_item->databaseId : 0;
				if ( $database_id <= 0 ) {
					return null;
				}

				$raw = get_post_meta( $database_id, TT5_MENU_ITEM_FOOTER_SVG_META, true );
				$url = is_string( $raw ) ? tt5_sanitize_footer_svg_url( $raw ) : '';

				return '' !== $url ? $url : null;
			},
		)
	);
}
add_action( 'graphql_register_types', 'tt5_register_menu_item_footer_svg_graphql_field' );

/**
 * Registra isMap en MenuItem (Boolean para hijos del FOOTER).
 *
 * @return void
 */
function tt5_register_menu_item_is_map_graphql_field() {
	if ( ! function_exists( 'register_graphql_field' ) ) {
		return;
	}

	register_graphql_field(
		'MenuItem',
		'isMap',
		array(
			'type'        => 'Boolean',
			'description' => __(
				'Indica si el subelemento del FOOTER debe renderizarse como mapa de Google Maps (oculta el texto del enlace).',
				'twentytwentyfive'
			),
			'resolve'     => static function ( $menu_item ) {
				if ( ! tt5_menu_item_is_footer_child( $menu_item ) ) {
					return false;
				}

				$database_id = isset( $menu_item->databaseId ) ? (int) $menu_item->databaseId : 0;
				if ( $database_id <= 0 ) {
					return false;
				}

				$raw = get_post_meta( $database_id, TT5_MENU_ITEM_IS_MAP_META, true );

				return '1' === (string) $raw || true === $raw || 1 === $raw;
			},
		)
	);
}
add_action( 'graphql_register_types', 'tt5_register_menu_item_is_map_graphql_field' );

