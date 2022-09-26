<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define('THEME_CUSTOMISATIONS_ZAYAVKA_URL', '/zayavka/');
define('THEME_CUSTOMISATION_ZAKAZ_VAR', 'zakaz');

require_once plugin_dir_path(__FILE__) . '../contact-form-7-modules/textarea.php';
require_once plugin_dir_path(__FILE__).'../woocommerce/class-wc-template-loader.php';

/**
 * functions.php
 * Add PHP snippets here
 */
add_action('init', 'theme_customisations_init');

function theme_customisations_init() {
    remove_action( 'storefront_header', 'storefront_header_cart', 60 );
    remove_action( 'storefront_header', 'storefront_site_branding', 20 );
    add_action('storefront_header', 'theme_customisations_site_branding', 20);
    remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    add_action('woocommerce_after_shop_loop_item', 'theme_customisations_loop_add_to_cart', 10);
    //remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    add_action('woocommerce_single_product_summary', 'theme_customisation_single_product_oformit', 30);

    $wcFormHandler = 'WC_Form_Handler';
    remove_action( 'template_redirect', array( $wcFormHandler, 'redirect_reset_password_link' ) );
    remove_action( 'template_redirect', array( $wcFormHandler, 'save_address' ) );
    remove_action( 'template_redirect', array( $wcFormHandler, 'save_account_details' ) );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'checkout_action' ), 20 );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'process_login' ), 20 );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'process_registration' ), 20 );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'process_lost_password' ), 20 );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'process_reset_password' ), 20 );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'cancel_order' ), 20 );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'update_cart_action' ), 20 );
    remove_action( 'wp_loaded', array( $wcFormHandler, 'add_to_cart_action' ), 20 );

    // May need $wp global to access query vars.
    remove_action( 'wp', array( $wcFormHandler, 'pay_action' ), 20 );
    remove_action( 'wp', array( $wcFormHandler, 'add_payment_method_action' ), 20 );
    remove_action( 'wp', array( $wcFormHandler, 'delete_payment_method_action' ), 20 );
    remove_action( 'wp', array( $wcFormHandler, 'set_default_payment_method_action' ), 20 );

    add_action('woocommerce_blocks_product_grid_item_html', 'theme_customisations_product_grid_item_html', 10, 3);

    remove_action('wp_footer', 'enqueue_cf7sr_script');
    add_action('wp_footer', 'theme_customisations_enqueue_cf7sr_script');
}

function theme_customisations_site_branding() {
    echo '<div class="site-branding">' . theme_customisations_site_title_and_logo(false) . '</div>';
}

/**
 * Get the add to cart template for the loop.
 *
 * @param array $args Arguments.
 */
function theme_customisations_loop_add_to_cart($args = array()) {
    global $product;

    if ( $product ) {
        $defaults = array(
            'quantity'   => 1,
            'class'      => implode(
                ' ',
                array_filter(
                    array(
                        'button',
                        'product_type_' . $product->get_type(),
                        '',
                        '',
                    )
                )
            ),
            'attributes' => array(
                'aria-label'       => $product->add_to_cart_description(),
            ),
        );

        $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

        if ( isset( $args['attributes']['aria-label'] ) ) {
            $args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
        }

        wc_get_template( 'loop/add-to-cart.php', $args );
    }
}

function theme_customisation_single_product_oformit() {
    global $product;

    if ($product) {
        $permalink = $product->get_permalink();
        if (function_exists('idn_to_utf8')) {
            $parsedUrl = parse_url($permalink);
            if (array_key_exists('host', $parsedUrl)) {
                $parsedUrl['host'] = idn_to_utf8($parsedUrl['host']);
                $permalink = unparse_url($parsedUrl);
            }
        }
        wc_get_template('single-product/oformit.php', [
            'url' => add_query_arg([THEME_CUSTOMISATION_ZAKAZ_VAR => 'Хочу заказать у вас ' . $product->get_name() . "[newline]" . $permalink], remove_query_arg('text', THEME_CUSTOMISATIONS_ZAYAVKA_URL)),
        ]);
    }
}

function unparse_url($parts) {
    return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
        ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
        (isset($parts['user']) ? "{$parts['user']}" : '') .
        (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
        (isset($parts['user']) ? '@' : '') .
        (isset($parts['host']) ? "{$parts['host']}" : '') .
        (isset($parts['port']) ? ":{$parts['port']}" : '') .
        (isset($parts['path']) ? "{$parts['path']}" : '') .
        (isset($parts['query']) ? "?{$parts['query']}" : '') .
        (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
}

function theme_customisations_product_grid_item_html($args, $data, $product) {
    $data->button = '<div class="wp-block-button wc-block-grid__product-add-to-cart">' .
        sprintf(
            '<a href="%s" %s>%s</a>',
            esc_url( $data->permalink ),
            wc_implode_html_attributes( [
                'class' => 'wp-block-button__link',
            ] ),
            esc_html(apply_filters( 'woocommerce_product_add_to_cart_text', __( 'Read more', 'woocommerce' ), $product ))
        ) .
        '</div>';
    $data->price = '<span class="woocommerce-Price-amount amount">Цена по запросу</span>';

    return "<li class=\"wc-block-grid__product\">
        <a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
            {$data->image}
            {$data->title}
        </a>
        {$data->badge}
        {$data->price}
        {$data->rating}
        {$data->button}
    </li>";
}

add_filter( 'theme_page_templates', 'theme_customisations_add_page_template_to_dropdown' );

function theme_customisations_add_page_template_to_dropdown($templates) {
    $templates['theme-customisations/template-popup.php'] = 'Пустой при аякс-запросе';
    return $templates;
}

add_filter( 'page_template', 'theme_customisations_page_template' );

function theme_customisations_page_template( $page_template ){
    if ( get_page_template_slug() == 'theme-customisations/template-popup.php' ) {
        $page_template = plugin_dir_path(__FILE__) . '/templates/template-popup.php';
    }
    return $page_template;
}

add_action('wp_print_scripts','theme_customisations_dequeue_scripts');
function theme_customisations_dequeue_scripts() {
    wp_dequeue_script('wc-cart-fragments');
    wp_dequeue_script('storefront-header-cart');
}

/**
 * Replaces query version in registered scripts or styles with file modified time
 * @see https://wordpress.stackexchange.com/a/294553
 *
 * @param $src
 *
 * @return string
 */
function add_modified_time( $src ) {
    $path = wp_parse_url( $src, PHP_URL_PATH );

    if ((strpos($src, wp_styles()->base_url) === 0) && ($modified_time = @filemtime( untrailingslashit( ABSPATH ) . $path))) {
        $src = add_query_arg( 'ver', $modified_time, remove_query_arg( 'ver', $src ) );
    }

    return $src;
}

add_filter( 'style_loader_src', 'add_modified_time', 99999999, 1 );
add_filter( 'script_loader_src', 'add_modified_time', 99999999, 1 );

function theme_customisations_enqueue_cf7sr_script() {
    ob_start();
    enqueue_cf7sr_script();
    $contents = ob_get_clean();
    if ($contents != "") {
        $cf7sr_script_url = 'https://www.google.com/recaptcha/api.js?onload=cf7srLoadCallback&render=explicit';
        $esc_url_cf7sr_script_url = esc_url($cf7sr_script_url);
        $cf7sr_script_url_hl = $cf7sr_script_url . '&hl=ru';
        $esc_url_cf7sr_script_url_hl = esc_url($cf7sr_script_url_hl);
        echo str_replace($esc_url_cf7sr_script_url, $esc_url_cf7sr_script_url_hl, $contents);
    }
}

function theme_customisations_site_title_and_logo( $echo = true ) {
    $html = '';

    if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
        $logo = get_custom_logo();
        $html .= is_home() ? '<div class="logo">' . $logo . '</div>' : $logo;
    }

    $tag = is_home() ? 'h1' : 'div';

    $html .= '<div class="site-title-and-description">';
    $html .= '<' . esc_attr( $tag ) . ' class="beta site-title"><a href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a></' . esc_attr( $tag ) . '>';

    if ( '' !== get_bloginfo( 'description' ) ) {
        $html .= '<p class="site-description">' . esc_html( get_bloginfo( 'description', 'display' ) ) . '</p>';
    }
    $html .= '</div>';

    if ( ! $echo ) {
        return $html;
    }

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function theme_customisations_template_redirect()
{
    if ( is_product_category('Услуги') ) {
        wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
        exit();
    }
    if ( is_singular( 'product' ) ) {
        $object       = get_queried_object();
        $terms = wc_get_product_terms( $object->ID, 'product_cat');
        foreach ($terms as $term) {
            if ($term->name == 'Услуги') {
                wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
                exit();
            }
        }
    }
}

add_action( 'template_redirect', 'theme_customisations_template_redirect' );

function woocommerce_template_single_price() {
    wc_get_template( 'single-product/price.php' );
}

function woocommerce_template_loop_price() {
    wc_get_template( 'loop/price.php' );
}

add_action( 'robots_txt', 'theme_customisations_robots_txt_append', -1 );

function theme_customisations_robots_txt_append( $output ){

    $str = '
	Disallow: /author/             # Архив автора.
	Disallow: */embed              # Все встраивания.
	Disallow: */page/              # Все виды пагинации.
	Disallow: */xmlrpc.php         # Файл WordPress API
	Disallow: *utm*=               # Ссылки с utm-метками
	Disallow: *openstat=           # Ссылки с метками openstat
	Disallow: */?' . THEME_CUSTOMISATION_ZAKAZ_VAR . '=
	';

    $str = trim( $str );
    $str = preg_replace( '/^[\t ]+(?!#)/mU', '', $str );
    $output .= "$str\n";

    return $output;
}