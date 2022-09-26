<?php
$hit = $row['PROPERTIES']['HIT'];
if (!is_array($hit['VALUE_ENUM']) || empty($hit['VALUE_ENUM'])) {
    return;
}

echo '<div class="refactor-stickers">';
foreach ($hit['VALUE_ENUM'] as $i => $value) {
    echo '<div class="sticker sticker-' . strtolower($hit['VALUE_XML_ID'][$i]) . '">';
    echo $value;
    echo '</div>';
}
echo '</div>';
