<?php
echo '<div class="side-panel">';
if (@$row['PROPERTIES']['PRODUCT_PRICE_ALT']['VALUE_XML_ID'] !== 'Y') {
    echo '<a href="javascript:void(0)" class="side-panel-action add-favorite" title="Отложить">';
    echo '
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="13" viewBox="0 0 16 13">
    <path d="M506.755,141.6l0,0.019s-4.185,3.734-5.556,4.973a0.376,0.376,0,0,1-.076.056,1.838,1.838,0,0,1-1.126.357,1.794,1.794,0,0,1-1.166-.4,0.473,0.473,0,0,1-.1-0.076c-1.427-1.287-5.459-4.878-5.459-4.878l0-.019A4.494,4.494,0,1,1,500,135.7,4.492,4.492,0,1,1,506.755,141.6Zm-3.251-5.61A2.565,2.565,0,0,0,501,138h0a1,1,0,1,1-2,0h0a2.565,2.565,0,0,0-2.506-2,2.5,2.5,0,0,0-1.777,4.264l-0.013.019L500,145.1l5.179-4.749c0.042-.039.086-0.075,0.126-0.117l0.052-.047-0.006-.008A2.494,2.494,0,0,0,503.5,135.993Z" transform="translate(-492 -134)"></path>
</svg>
';
    echo '</a>';
}
echo '<a href="javascript:void(0)" class="side-panel-action add-compare" title="Сравнить">';
echo '
<svg xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 14 13">
    <path d="M595,137a1,1,0,0,1,1,1v8a1,1,0,1,1-2,0v-8A1,1,0,0,1,595,137Zm-4,3a1,1,0,0,1,1,1v5a1,1,0,1,1-2,0v-5A1,1,0,0,1,591,140Zm8-6a1,1,0,0,1,1,1v11a1,1,0,1,1-2,0V135A1,1,0,0,1,599,134Zm4,6h0a1,1,0,0,1,1,1v5a1,1,0,0,1-1,1h0a1,1,0,0,1-1-1v-5A1,1,0,0,1,603,140Z" transform="translate(-590 -134)"></path>
</svg>
';
echo '</a>';
echo '<a href="javascript:void(0)" class="side-panel-action quick-buy" title="Купить в 1 клик"
    onclick="oneClickBuy(\'' . $row['ID'] . '\', \'' . $row['IBLOCK_ID'] . '\', this)"
>';
echo '
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" viewBox="0 0 18 16">
    <path d="M653,148H643a2,2,0,0,1-2-2v-3h2v3h10v-7h-1v2a1,1,0,1,1-2,0v-2H638a1,1,0,1,1,0-2h6v-1a4,4,0,0,1,8,0v1h1a2,2,0,0,1,2,2v7A2,2,0,0,1,653,148Zm-3-12a2,2,0,0,0-4,0v1h4v-1Zm-10,4h5a1,1,0,0,1,0,2h-5A1,1,0,0,1,640,140Z" transform="translate(-637 -132)"></path>
</svg>
';
echo '</a>';
// TODO this is legacy
echo '<button class="side-panel-action quick-look" title="Быстрый просмотр"
    data-event="jqm" 
    data-param-form_id="fast_view" 
    data-param-iblock_id="' . $row['IBLOCK_ID'] . '"
    data-param-id="' . $row['ID'] . '"
    data-param-item_href="' . urlencode($row["DETAIL_PAGE_URL"]) . '"
    data-name="fast_view"
>';
echo '
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" viewBox="0 0 16 12">
    <path d="M549,146a8.546,8.546,0,0,1-8.008-6,8.344,8.344,0,0,1,16.016,0A8.547,8.547,0,0,1,549,146Zm0-2a6.591,6.591,0,0,0,5.967-4,7.022,7.022,0,0,0-1.141-1.76,4.977,4.977,0,0,1-9.652,0,7.053,7.053,0,0,0-1.142,1.76A6.591,6.591,0,0,0,549,144Zm-2.958-7.246c-0.007.084-.042,0.159-0.042,0.246a3,3,0,1,0,6,0c0-.087-0.035-0.162-0.042-0.246A6.179,6.179,0,0,0,546.042,136.753Z" transform="translate(-541 -134)"></path>
</svg>
';
echo '</button>';
echo '</div>';
