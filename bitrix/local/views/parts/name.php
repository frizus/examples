<?php
$noTag = $noTag ?? null;
if ($noTag !== true) {
    echo  '<div class="name">';
}
echo ((array_key_exists('ELEMENT_PAGE_TITLE', $row['IPROPERTY_VALUES'])) &&
    ($row['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] !== '')) ?
    $row['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] :
    $row['NAME'];
if ($noTag !== true) {
    echo '</div>';
}
