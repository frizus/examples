<?php
view_once('lib/products');
$asset = Bitrix\Main\Page\Asset::getInstance();
$asset->addCss(REL_ASSETS_PATH . 'products-block.css');
$asset->addCss(REL_ASSETS_PATH . 'product-buy-light.css');
$asset->addJs(REL_ASSETS_PATH . 'products-block.js');
