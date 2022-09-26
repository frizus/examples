<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

echo apply_filters(
    'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
    sprintf(
        '<a href="%s" class="%s" %s>%s</a>',
        esc_url( $product->get_permalink() ),
        esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
        isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
        esc_html(apply_filters( 'woocommerce_product_add_to_cart_text', __( 'Read more', 'woocommerce' ), $product ))
    ),
    $product,
    $args
);
