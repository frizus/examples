<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

echo sprintf('<a href="%s" class="button oformit" target="nofollow noopener">Оформить заявку</a>',
    esc_url($args['url']),
);