<?php
view_once('lib/product_price');
$asset = Bitrix\Main\Page\Asset::getInstance();
$asset->addJs(REL_ASSETS_PATH . 'product-action.js');
$asset->addCss(REL_ASSETS_PATH . 'product-sticker.css');
$asset->addCss(REL_ASSETS_PATH . 'product-action.css');
$asset->addCss(REL_ASSETS_PATH . 'product-rating.css');
