<?php

use Frizus\Module\Helper\Price;

$havePrice = (@$row['PROPERTIES']['PRODUCT_PRICE_ALT']['VALUE_XML_ID'] === 'Y') ||
    ($row['PRICE'] !== false);

if (!$havePrice) {
    return;
}

//echo '<pre>',var_dump($row['PRICE']['RESULT_PRICE'],true),'</pre>';

echo '<div class="price-block">';
if (@$row['PROPERTIES']['PRODUCT_PRICE_ALT']['VALUE_XML_ID'] === 'Y') {
    echo '<span class="estimated">Расчетная цена</span>';
} else {
    $haveDiscount = $row['PRICE']['RESULT_PRICE']['BASE_PRICE'] > $row['PRICE']['RESULT_PRICE']['DISCOUNT_PRICE'];
    $price = $row['PRICE']['RESULT_PRICE']['DISCOUNT_PRICE'];
    $oldPrice = $haveDiscount ? $row['PRICE']['RESULT_PRICE']['BASE_PRICE'] : null;
    echo '<div class="price-value-container">';
    echo '<span class="value">';
    if (@$row['PROPERTIES']['PRICE_FROM']['VALUE'] === 'Y') {
        echo '<span class="from">от</span>&nbsp;';
    }
    echo Price::format($price);
    echo '</span>';
    if ($haveDiscount) {
        echo '<span class="old-value">' . Price::format($oldPrice) . '</span>';
    }
    echo '</div>';
    if ($haveDiscount) {
        if ($row['PRICE']['RESULT_PRICE']['PERCENT'] > 0) {
            $percent = $row['PRICE']['RESULT_PRICE']['PERCENT'];
        } else {
            $percent = $row['PRICE']['RESULT_PRICE']['DISCOUNT'] / $row['PRICE']['RESULT_PRICE']['BASE_PRICE'];
            $percent = round($percent * 100);
        }

        echo '<div class="price-discount-container">';
        echo '<div class="percent">';
        echo '-' . $percent . '%';
        echo '</div>';
        echo '<div class="save">';
        echo 'Экономия ' . Price::format($row['PRICE']['RESULT_PRICE']['DISCOUNT']);
        echo '</div>';
        echo '</div>';
    }
}
echo '</div>';
