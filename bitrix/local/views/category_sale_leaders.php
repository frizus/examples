<?php
if (empty($rows)) {
    return;
}

echo '<div class="category-sale-leaders">';
echo '<div class="title">Лидеры продаж</div>';
echo '<div class="items products products-block products-block-light 
    owl-carousel owl-theme owl-bg-nav"
    data-plugin-options=\'' . json_encode(['nav' => true, 'autoplay' => false, 'autoplayTimeout' => 3000, 'smartSpeed' => 1000, 'loop' => count($rows) > 5 ? true : false, 'responsiveClass' => true, 'responsive' => ['0' => ['items' => 2], '600' => ['items' => 2], '768' => ['items' => 3], '1200' => ['items' => 4], '1500' => ['items' => 5],]]) . '\'
>';
foreach ($rows as $row) {
    echo '<div class="item" data-product="' . $row['ID'] . '">';
    echo '<div class="image-wrapper">';
    view('catalog/parts/sticker', ['row' => $row]);
    view('catalog/parts/product_side_panel', ['row' => $row]);
    echo '<a href="' . $row['DETAIL_PAGE_URL'] . '" class="image-link">';
    view('catalog/parts/image', ['row' => $row, 'noWrapper' => true]);
    echo '</a>';
    echo '</div>';
    echo '<div class="body">';
    view('catalog/parts/rating', ['row' => $row]);
    echo '<div class="name-wrapper">';
    echo '<a href="' . $row['DETAIL_PAGE_URL'] . '" class="name-link">';
    view('catalog/parts/name', ['row' => $row, 'noTag' => true]);
    echo '</a>';
    echo '</div>';
    echo '<div class="buy-block">';
    view('catalog/parts/price', ['row' => $row]);
    if (@$row['PROPERTIES']['PRODUCT_PRICE_ALT']['VALUE_XML_ID'] !== 'Y') {
        view('catalog/parts/buy_light', ['row' => $row]);
    }
    echo '</div>';
    echo '</div>';
    if (@$row['PROPERTIES']['PRODUCT_PRICE_ALT']['VALUE_XML_ID'] !== 'Y') {
        echo '<a href="javascript:void(0)" class="buy buy-mobile button button-red">В корзину</a>';
    }
    echo '</div>';
}
echo '</div>';
echo '</div>';
echo "
<script>
(function($, window, document) {
    $(document).ready(function() {
        $('.category-sale-leaders > .items').one('initialized.owl.carousel', function() {
            var \$this = $(this)
            \$this.parent().bitrixProductsBlock()
            $('.item', \$this).bitrixProductAction({
                checkState: true
            })
            $.ripple('.category-sale-leaders .buy', {
                debug: false,
                on: 'mouseenter',
                opacity: 0.4,
                color: '#fff',
                multi: true,
                duration: 0.6,
                easing: 'linear'
            })
        })
    })
})(jQuery, window, document)
</script>
";
