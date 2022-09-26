<?php
$alt = (
    array_key_exists('ELEMENT_PREVIEW_PICTURE_FILE_ALT', $row['IPROPERTY_VALUES']) &&
    ($row['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_ALT'] !== '')
) ? $row['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_ALT'] : $row['NAME'];
$title = (
    array_key_exists('ELEMENT_PREVIEW_PICTURE_FILE_TITLE', $row['IPROPERTY_VALUES']) &&
    ($row['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] !== '')
) ? $row['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] : $row['NAME'];
$alt = htmlspecialcharsbx($alt, ENT_COMPAT, false);
$title = htmlspecialcharsbx($title, ENT_COMPAT, false);

$noWrapper = $noWrapper ?? null;
if ($noWrapper !== true) {
    echo '<div class="image">';
}
if (is_array($row['PREVIEW_PICTURE'])) {
    echo '<img 
        src="' . (SITE_TEMPLATE_PATH.'/images/loaders/double_ring.svg') . '"
        data-src="' . $row['PREVIEW_PICTURE']['SRC'] . '"
        alt="' . $alt . '" title="' . $title . '"
        class="lazy" loading="lazy">';
} elseif (is_array($row['DETAIL_PICTURE'])) {
    echo '<img 
        src="' . (SITE_TEMPLATE_PATH.'/images/loaders/double_ring.svg') . '" 
        data-src="' . $row['DETAIL_PICTURE']['SRC'] . '"
        alt="' . $alt . '" title="' . $title . '" 
        class="lazy" loading="lazy">';
} else {
    echo '<img 
        src="' . (SITE_TEMPLATE_PATH . '/images/svg/noimage_product.svg') . '"
        alt="' . $alt . '" title="' . $title . '"
        loading="lazy">';
}
if ($noWrapper !== true) {
    echo '</div>';
}
