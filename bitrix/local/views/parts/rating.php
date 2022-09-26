<?php

$reviewsCount = (int)@$row['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE'];
$rating = (int)@$row['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'];
$title = $reviewsCount > 0 ? sprintf('Рейтинг %s из 5', $reviewsCount) : 'Нет оценок';

echo '<div class="refactor-rating" title="' . $title . '">';
for ($i = 1; $i <= 5; $i++) {
    echo '<div class="rating-point';
    if ($rating >= $i) {
        echo ' rating-point-set';
    }
    echo '">';
    echo '
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="13" viewBox="0 0 15 13">
        <rect width="15" height="13"></rect>
        <path d="M1333.37,457.5l-4.21,2.408,0.11,0.346,2.07,4.745h-0.72l-4.12-3-4.09,3h-0.75l2.04-4.707,0.12-.395-4.19-2.4V457h5.12l1.53-5h0.38l1.57,5h5.14v0.5Z" transform="translate(-1319 -452)"></path>
    </svg>
    ';
    echo '</div>';
}
if ($reviewsCount > 0) {
    echo '<div class="rating-count">' . $reviewsCount . '</div>';
}
echo '</div>';
