<?php
namespace Frizus\Module\Cron;

use Bitrix\Iblock\Elements\ElementCatalogTable;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\ORM\ElementV1Table;
use Bitrix\Iblock\ORM\ValueStorage;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Frizus\Module\Helper\SaleLeaderHelper;

class SetBuyAlongBlockCron
{
    public $arguments;

    protected $stats;

    protected $errors;

    public function __construct()
    {
        $options = getopt("", [
            "withRemove::",
            "quiet::",
        ]);

        $this->arguments = [
            'withRemove' => isset($options['withRemove']),
            'quiet' => isset($options['quiet']),
        ];

        if (!Loader::includeModule('sale')) {
            throw new \Exception;
        }
        if (!Loader::includeModule('iblock')) {
            throw new \Exception;
        }
    }

    public function run()
    {
        $this->stats = [
            'updated_bindings' => 0,
            'unchanged' => 0,
            'not_checked_bindings_because_set_manually' => 0,
            'cleanup' => 0,
        ];

        $orderIds = $this->selectOrdersWithMultipleProducts();
        if (empty($orderIds)) {
            return;
        }

        $elementIds = [];
        $result = SaleLeaderHelper::getAllOrdersProducts($orderIds);
        while ($row = $result->fetch()) {
            $elementId = intval($row['PRODUCT_ID']);
            $elementIds[] = $elementId;
        }

        $notCanceledOrderIdsByElementId = [];
        $result = $this->getBasketProducts(false, $orderIds);
        while ($row = $result->fetch()) {
            $elementId = intval($row['PRODUCT_ID']);
            $notCanceledOrderIdsByElementId[$elementId] = $row['ORDER_IDS'];
        }

        $canceledOrderIdsByElementId = [];
        $result = $this->getBasketProducts(true, $orderIds);
        while ($row = $result->fetch()) {
            $elementId = intval($row['PRODUCT_ID']);
            $canceledOrderIdsByElementId[$elementId] = $row['ORDER_IDS'];
        }

        $exclude = null;
        if (!empty($elementIds)) {
            foreach (array_chunk($elementIds, 100) as $elementIdsChunk) {
                /** @var ElementV1Table $elementCatalogTableClass */
                $elementCatalogTableClass = ElementCatalogTable::class;
                $result = $elementCatalogTableClass::getList([
                    'select' => ['ID', 'IBLOCK_ID', 'BUY_ALONG_SET_MANUALLY.VALUE', 'EXPANDABLES.VALUE'],
                    'filter' => [
                        //'=IBLOCK_ID' => FRIZUS_CATALOG,
                        '@ID' => $elementIdsChunk,
                    ]
                ]);
                while ($row = $result->fetchObject()) {
                    $notSetManually = $row['BUY_ALONG_SET_MANUALLY']['VALUE'] !== 'Y';
                    if ($notSetManually) {
                        if (!isset($exclude)) {
                            $exclude = [];
                        }
                        $exclude[] = $row['ID'];

                        $removeBuyAlongProducts = [];
                        if (array_key_exists($row['ID'], $canceledOrderIdsByElementId)) {
                            $result2 = $this->getBuyAlongProducts($row['ID'], $canceledOrderIdsByElementId[$row['ID']]);
                            while ($row2 = $result2->fetch()) {
                                $elementId = intval($row2['PRODUCT_ID']);
                                $removeBuyAlongProducts[$elementId] = $elementId;
                            }
                        }
                        $addBuyAlongProducts = [];
                        if (array_key_exists($row['ID'], $notCanceledOrderIdsByElementId)) {
                            $values = [];
                            $result2 = $this->getBuyAlongProducts($row['ID'], $notCanceledOrderIdsByElementId[$row['ID']]);
                            while ($row2 = $result2->fetch()) {
                                $elementId = intval($row2['PRODUCT_ID']);
                                $addBuyAlongProducts[$elementId] = $elementId;
                            }
                        }
                        foreach ($removeBuyAlongProducts as $key => $value) {
                            if (array_key_exists($key, $addBuyAlongProducts)) {
                                unset($removeBuyAlongProducts[$key]);
                            }
                        }
                        $added = false;
                        $removed = false;
                        $buyAlongProducts = [];
                        if (!empty($addBuyAlongProducts) || !empty($removeBuyAlongProducts)) {
                            foreach ($row['EXPANDABLES'] as $value) {
                                $elementId = $value['VALUE'];
                                if (array_key_exists($elementId, $addBuyAlongProducts)) {
                                    unset($addBuyAlongProducts[$elementId]);
                                    $buyAlongProducts[$elementId] = $elementId;
                                } elseif (array_key_exists($elementId, $removeBuyAlongProducts)) {
                                    if (!$removed) {
                                        $removed = true;
                                    }
                                } else {
                                    $buyAlongProducts[$elementId] = $elementId;
                                }
                            }
                            if (!empty($addBuyAlongProducts)) {
                                foreach ($addBuyAlongProducts as $elementId) {
                                    $buyAlongProducts[$elementId] = $elementId;
                                }
                                $added = true;
                            }
                        }
                        if ($added || $removed) {
                            if (empty($buyAlongProducts)) {
                                $values = false;
                            } else {
                                $values = [];
                                foreach ($buyAlongProducts as $elementId) {
                                    $values[] = [
                                        'VALUE' => (string)$elementId,
                                    ];
                                }
                            }

                            $propertyValues = [
                                'EXPANDABLES' => $values,
                                'BUY_ALONG_UPDATED_AT' => [
                                    'VALUE' => new DateTime(),
                                ],
                            ];
                            \CIBlockElement::SetPropertyValuesEx($row['ID'], $row['IBLOCK_ID'], $propertyValues);
                            $this->stats['updated_bindings']++;
                        } else {
                            $this->stats['unchanged']++;
                        }
                    } else {
                        $this->stats['not_checked_bindings_because_set_manually']++;
                    }
                }
            }
            unset($propertyValues);
        }

        if ($this->arguments['withRemove']) {
            foreach ($this->needClearBuyAlong($exclude) as $row) {
                \CIBlockElement::SetPropertyValuesEx($row['ID'], $row['IBLOCK_ID'], [
                    'EXPANDABLES' => false,
                    'BUY_ALONG_UPDATED_AT' => [
                        'VALUE' => new DateTime(),
                    ]
                ]);
                $this->stats['cleanup']++;
            }
        }

        if (($this->stats['updated_bindings'] > 0) || ($this->stats['cleanup'] > 0)) {
            \CIBlock::clearIblockTagCache(FRIZUS_CATALOG);
        }
    }

    /**
     * @see https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3030#:~:text=%D0%92%20%D1%84%D0%B8%D0%BB%D1%8C%D1%82%D1%80%D0%B5%20%D0%B2%20%D0%BA%D0%B0%D1%87%D0%B5%D1%81%D1%82%D0%B2%D0%B5%20%D0%B8%D0%BC%D0%B5%D0%BD
     */
    public static function selectOrdersWithMultipleProducts()
    {
        $filter = new ConditionTree();
        $filter->whereNotNull('ORDER.ID')
            ->where('MODULE', 'catalog')
            ->where('PRODUCT.IBLOCK.IBLOCK_ID', FRIZUS_CATALOG)
            ->where(new ExpressionField('MULTIPLE_PRODUCTS', 'COUNT(%s) > 1', ['PRODUCT_ID']));
//        \Bitrix\Main\Application::getConnection()->startTracker(false);
        $result = Basket::getList([
            'select' => ['ORDER_ID'],
            'filter' => $filter,
            'group' => ['ORDER_ID'],
        ]);
//        \Bitrix\Main\Application::getConnection()->stopTracker();
//        echo $result->getTrackerQuery()->getSql();die();
        $orderIds = [];
        while ($row = $result->fetch()) {
            $orderIds[] = intval($row['ORDER_ID']);
        }
        return $orderIds;
    }

    protected function needClearBuyAlong($exclude)
    {
        $lastId = 0;

        $filter = [
            '!=BUY_ALONG_SET_MANUALLY.VALUE' => 'Y',
            '!==EXPANDABLES.VALUE' => null,
        ];
        if (isset($exclude)) {
            $filter['!@ID'] = $exclude;
        }

        /** @var ElementV1Table $elementCatalogTableClass */
        $elementCatalogTableClass = ElementCatalogTable::class;
        while (true) {
            $filter['>ID'] = $lastId;
//            \Bitrix\Main\Application::getConnection()->startTracker(false);
            $result = $elementCatalogTableClass::getList([
                'select' => ['ID'],
                'filter' => $filter,
                'limit' => 100,
                'group' => ['ID'],
                'order' => ['ID' => 'ASC'],
            ]);
//            \Bitrix\Main\Application::getConnection()->stopTracker();
//            echo $result->getTrackerQuery()->getSql();die();
            $rows = $result->fetchAll();
            if (empty($rows)) {
                return;
            }
            foreach ($rows as $row) {
                yield $row;
            }
            $lastId = $row['ID'];
        }
    }

    protected function getBuyAlongProducts($productId, $orderIds)
    {
//        \Bitrix\Main\Application::getConnection()->startTracker(false);
        $result = Basket::getList([
            'select' => ['PRODUCT_ID'],
            'filter' => [
                '@ORDER_ID' => explode(',', $orderIds),
                '!=PRODUCT_ID' => $productId,
                '=MODULE' => 'catalog',
                '=PRODUCT.IBLOCK.IBLOCK_ID' => FRIZUS_CATALOG,
            ],
            'group' => ['PRODUCT_ID']
        ]);
//        \Bitrix\Main\Application::getConnection()->stopTracker();
//        echo $result->getTrackerQuery()->getSql();die();
        return $result;
    }

    protected function getBasketProducts($canceled, $orderIds)
    {
//        \Bitrix\Main\Application::getConnection()->startTracker(false);
        $filter = [
            '!==ORDER.ID' => null,
            '=MODULE' => 'catalog',
            '=PRODUCT.IBLOCK.IBLOCK_ID' => FRIZUS_CATALOG,
            '@ORDER_ID' => $orderIds,
        ];
        if ($canceled) {
            $filter['=ORDER.CANCELED'] = true;
        } else {
            $filter['!=ORDER.CANCELED'] = true;
        }
        $result = Basket::getList([
            'select' => [
                'PRODUCT_ID',
                'ORDER_IDS' => new ExpressionField('ORDER_IDS', 'GROUP_CONCAT(%s SEPARATOR ",")', ['ORDER_ID'])
            ],
            'filter' => $filter,
            'group' => ['PRODUCT_ID']
        ]);
//        \Bitrix\Main\Application::getConnection()->stopTracker();
//        echo $result->getTrackerQuery()->getSql();die();
        return $result;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function outErrors()
    {
        foreach ($this->errors as $error) {
            echo $error . "\n";
        }
    }

    public function outStats()
    {
        foreach ($this->stats as $key => $value) {
            echo ucfirst(str_replace('_', ' ', $key)) . ': ' . $value . "\n";
        }
    }
}
