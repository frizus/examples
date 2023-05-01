<?php
// ...

if ($itemsCnt) {
    view_once('lib/category_sale_leaders');
    echo '<div class="js-load-block loader_circle" data-file="/catalog/category_sale_leaders.php?categoryId=' . $arSection['ID'] . '">';
    echo '<div class="stub"></div>';
    echo '</div>';
}
?>
