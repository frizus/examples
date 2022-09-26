<?php
namespace Frizus\Module\Controller;

use Bitrix\Iblock\SectionElementTable;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;
use Frizus\Module\Controller\Traits\LoadModule;
use Frizus\Module\Controller\Traits\PrepareRequest;
use Frizus\Module\Helper\Arr;
use Frizus\Module\Helper\Cache;
use Frizus\Module\Repository\ProductsRepository;
use Frizus\Module\Repository\RecommendSectionsRepository;
use Frizus\Module\Request\BuyRequest;
use Frizus\Module\Request\CategorySaleLeadersRequest;
use Frizus\Module\Request\CompareRequest;
use Frizus\Module\Request\FavoriteRequest;
use Frizus\Module\Request\RecommendRequest;

class CatalogController extends Base
{
    use LoadModule;
    use PrepareRequest;

    public function beforeActionClosures()
    {
        if ($this->action === 'recommend') {
            return [
                function() {
                    $this->loadModule('iblock');
                    $this->prepareRequest(RecommendRequest::class, 1);
                    $this->loadModule(['catalog', 'sale']);
                },
            ];
        } elseif ($this->action === 'categorySaleLeaders') {
            return [
                function() {
                    $this->loadModule('iblock');
                    $this->prepareRequest(CategorySaleLeadersRequest::class, 1);
                },
            ];
        } elseif ($this->action === 'buy') {
            return [
                function() {
                    $this->loadModule('iblock');
                    $this->prepareRequest(BuyRequest::class);
                    $this->loadModule(['catalog', 'sale']);
                }
            ];
        } elseif ($this->action === 'favorite') {
            return [
                function() {
                    $this->loadModule('iblock');
                    $this->prepareRequest(FavoriteRequest::class);
                    $this->loadModule(['catalog', 'sale']);
                }
            ];
        } elseif ($this->action === 'compare') {
            return [
                function() {
                    $this->loadModule('iblock');
                    $this->prepareRequest(CompareRequest::class);
                    $this->loadModule(['catalog', 'sale']);
                }
            ];
        }
    }

    public function recommend()
    {
        $categoryId = $this->request->processing('categoryId');
        $keyPart = __CLASS__ . '::' . __FUNCTION__;
        $key = $keyPart . ' ' . $categoryId;
        $ttl = 60 * 60 * 24 * 7;
        $tag = 'iblock_id_' . FRIZUS_CATALOG;
        $html = Cache::remember($key, $ttl, function() use ($categoryId) {
            try {
                $this->loadModule(['iblock', 'catalog', 'sale']);
            } catch (\Throwable $e) {
                Cache::abort();
                throw $e;
            }
            $recommendSections = new RecommendSectionsRepository($categoryId, FRIZUS_CATALOG);
            $productsRepository = new ProductsRepository;
            $excludeElementIds = [];
            $orders = ['RAND' => 'ASC'];

            $rows = [];
            $rowsNeeded = 25;
            $minimalRowsNeeded = 6;
            $firstVariant = true;
            $usedVariant = false;
            $needShuffle = false;
            while (!$recommendSections->isLastVariant()) {
                $sectionIds = $recommendSections->getVariantOfIds();
                $filter = [];
                if (!empty($excludeElementIds)) {
                    $filter['!=ID'] = $excludeElementIds;
                }
                if (!empty($sectionIds)) {
                    $filter['SECTION_ID'] = $sectionIds;
                }

                $newRows = $productsRepository->all($filter, $orders, $rowsNeeded);
                $newRowsCount = count($newRows);
                $haveRows = $newRowsCount > 0;
                if ($haveRows) {
                    $rows += $newRows;
                    if ($usedVariant) {
                        $needShuffle = true;
                    }
                }
                if ($firstVariant && $haveRows &&
                    in_array($recommendSections->lastVariant, [1, 2], true) &&
                    ($newRowsCount < $minimalRowsNeeded)
                ) {
                    $rowsNeeded = $minimalRowsNeeded - $newRowsCount;
                } elseif ($haveRows) {
                    $rowsNeeded -= $newRowsCount;
                    if ($rowsNeeded === 0) {
                        break;
                    }
                }
                if ($firstVariant) {
                    $firstVariant = false;
                }
                if ($haveRows) {
                    if (!$usedVariant) {
                        $usedVariant = true;
                    }
                    $excludeElementIds = array_merge($excludeElementIds, array_keys($newRows));
                }
            }
            unset($productsRepository, $recommendSections);
            if ($needShuffle) {
                $rows = Arr::shuffle($rows);
            }

            view('catalog/recommend', ['rows' => $rows]);
            $html = [
                'top' => view_buffer('recommend top'),
                'bottom' => view_buffer('recommend bottom'),
                'rows' => [],
                'keys' => array_keys($rows),
            ];
            foreach ($rows as $key => $row) {
                $html['rows'][$key] = view_buffer('recommend ' . $key);
            }
            return $html;
        }, $tag, Cache::dir($keyPart));

        if (isset($html['top'])) {
            echo $html['top'];
        }
        $rowsCount = count($html['keys']);
        if ($rowsCount > 0) {
            $displayRows = 5;
            $elementId = $this->request->processing('elementId');
            $elementInRecommended = array_key_exists($elementId, $html['rows']);
            $minimumCount = $elementInRecommended ? ($displayRows + 1) : $displayRows;
            if ($rowsCount > $minimumCount) {
                $remainder = $rowsCount % $displayRows;
                $randMax = intval(round(($rowsCount - $remainder) / $displayRows));
                if ($remainder > 0) {
                    $randMax++;
                }
                $randMax--;
                $batchNum = rand(0, $randMax);
                $start = $batchNum * $displayRows;
                if (($remainder > 0) && ($batchNum === $randMax)) {
                    $end = $start + $remainder;
                } else {
                    $end = $start + $displayRows;
                }
                $rowsNeeded = $displayRows;
            } else {
                $start = 0;
                $end = $rowsCount;
                $rowsNeeded = $displayRows;
            }

            for ($i = $start; $i < $end; $i++) {
                $id = $html['keys'][$i];
                if ($elementInRecommended && ($id === $elementId)) {
                    continue;
                }
                echo $html['rows'][$id];
                $rowsNeeded--;
            }
            if ($rowsNeeded > 0) {
                for ($i = 0; $i < $rowsNeeded; $i++) {
                    $id = $html['keys'][$i];
                    echo $html['rows'][$id];
                }
            }
        }
        if (isset($html['bottom'])) {
            echo $html['bottom'];
        }
    }

    public function categorySaleLeaders()
    {
        $categoryId = $this->request->processing('categoryId');

        $keyPart = __CLASS__ . '::' . __FUNCTION__;
        $key = $keyPart . ' ' . $categoryId;
        $ttl = 60 * 60 * 24 * 7;
        $tag = 'iblock_id_' . FRIZUS_CATALOG;
        Cache::output($key, $ttl, function() use ($categoryId) {
            try {
                $this->loadModule('iblock');
                $this->request->step(2)->validate();
                $this->loadModule(['catalog', 'sale']);
            } catch (\Throwable $e) {
                Cache::abort();
                throw $e;
            }
            $productsRepository = new ProductsRepository;
            $rows = $productsRepository->all(
                [
                    'SECTION_ID' => $categoryId,
                    '=PROPERTY_HIT_VALUE' => 'Хит',
                ],
                ['RAND' => 'ASC'],
                10,
                ['HIT', 'EXTENDED_REVIEWS_COUNT', 'EXTENDED_REVIEWS_RAITING']
            );
            view('catalog/category_sale_leaders', ['rows' => $rows]);
        }, $tag, Cache::dir($keyPart));
    }

    public function buy()
    {
        
    }

    public function favorite()
    {
        
    }

    public function compare()
    {
        
    }
}
