<?php
namespace App\TargetDomains;

use App\Misc\Helpers\Str;
use App\Models\AssetOriginal;
use App\TargetDomains\Libraries\Clean;
use App\TargetDomains\Libraries\Request;
use App\TargetDomains\Libraries\Yml;
use Illuminate\Http\Client\Response;
use SimpleXMLElement;

// устаревший код
class TargetDomain extends Base
{
    const HOST = 'https://---.ru';

    const NAME = '---';

    const NO_DETAILS_SCRAPE = true;

    /**
     *
     */
    const TARGET_URLS = [
        'https://---.ru/export/---.xml'
    ];

    /**
     * @var SimpleXMLElement
     */
    public static $xml;

    protected static $isSimpleUrl;

    static function loadDocument(Response $response)
    {
        if (static::$isSimpleUrl) {
            return false;
        } else {
            self::$xml = simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_COMPACT | LIBXML_PARSEHUGE);
        }

        return self::$xml !== false;
    }

    static function load($url)
    {
        if (static::$isSimpleUrl) {
            $options = [
                'stream' => true,
            ];
        } else {
            $options = [
                'timeout' => 600,
                'read_timeout' => 600,
                'stream' => true,
            ];
        }

        return Request::load($url, $options);
    }

    static function loadAndValidate($url, $skipIncorrectPageNumberCheck = false)
    {
        return parent::loadAndValidate($url, true);
    }

    static function isUrlLastPaginationPage()
    {
        return true;
    }

    static function preparePaginationUrl($id, $pageNumber)
    {
        return static::TARGET_URLS[$id];
    }

    static function prepareSimpleUrl($url)
    {
        static::$isSimpleUrl = true;
        return $url;
    }

    static function runPriceScrape($domain)
    {
        $categories = static::buildCategories();
        $items = [];
        $i = 0;
        $l = count(self::$xml->shop->offers->offer);
        foreach(self::$xml->shop->offers->offer as $offer)
        {
            $id = (string)$offer->attributes()->id;
            if ($id === '') {
                continue;
            }

            $available = (string)$offer->attributes()->available == 'true' ? true : null;

            $url = (string)$offer->url;
            $matches = [];
            if (preg_match('#^' . preg_quote(static::HOST, '#') . '(?<urlWithoutHost>\/.+)$#', $url, $matches)) {
                $url = Yml::unescape($matches['urlWithoutHost']);
            } else {
                $url = null;
            }

            $attributes = [];
            $attributes['domain'] = (string)$domain;
            $attributes['item_id'] = $id;
            $attributes['name1'] = Str::convertEmptyStringToNull(Clean::string(Yml::unescape((string)$offer->name)));
            $attributes['name2'] = Str::convertEmptyStringToNull(Clean::string(Yml::unescape((string)$offer->name)));
            $attributes['url'] = $url;
            $attributes['crude_price'] = $offer->price . ' ' . $offer->currencyId;
            $attributes['crude_old_price'] = isset($offer->oldprice) ? ($offer->oldprice . ' ' . $offer->currencyId) : null;
            $attributes['price'] = static::parsePrice((string)$offer->price);
            $attributes['old_price'] = isset($offer->oldprice) ? static::parsePrice((string)$offer->oldprice) : null;
            $attributes['description'] = Str::convertEmptyStringToNull(Clean::html(Yml::unescape((string)$offer->description)));
            $attributes['properties'] = [];
            if (isset($offer->categoryId)) {
                $attributes['properties']['breadcrumbs'] = Str::convertEmptyStringToNull(static::makeBreadcrumbs($categories, (string)$offer->categoryId));
            } else {
                $attributes['properties']['breadcrumbs'] = null;
            }

            $attributes['properties']['available'] = $available;
            if (isset($offer->picture)) {
                foreach ($offer->picture as $picture) {
                    $url = (string)$picture;
                    $matches = [];
                    if (!preg_match('#^' . preg_quote(static::HOST, '#') . '(?<urlWithoutHost>\/.+)$#', $url, $matches)) {
                        continue;
                    }
                    $attributes['properties']['pictures'][] = Yml::unescape($url);
                }

            }
            if (isset($offer->param)) {
                foreach ($offer->param as $param) {
                    if (isset($param->attributes()->name)) {
                        $key = Clean::property(Clean::string(Yml::unescape((string)$param->attributes()->name)));
                        $value = Clean::string(Yml::unescape((string)$param));
                        if ($value === 'Array') {
                            continue;
                        }
                        $attributes['properties']['attributes'][$key] = $value;
                    } else {
                        echo 'id: ' . $id . ', name: ' . $attributes['name2'] . ' param value: ' . $param . "\n";
                    }
                }
            }
            $attributes['active'] = true;
            $items[$id] = $attributes;
            $i++;
            if (($i % 20 == 0) || ($i == $l)) {
                static::assetsOriginalXmlSave($domain, $items);
                $items = [];
            }
        }
    }

    public static function buildCategories() {
        if (!isset(self::$xml->shop->categories)) {
            return [];
        }

        $categories = [];
        foreach (self::$xml->shop->categories->category as $category) {
            $id = (string)$category->attributes()->id;
            if ($id == '') {
                continue;
            }

            $parentId = isset($category->attributes()->parentId) ? (string)$category->attributes()->parentId : null;

            $categories[$id] = [
                'name' => Yml::unescape((string)$category),
                'parentId' => $parentId,
            ];
        }

        return $categories;
    }

    public static function makeBreadcrumbs($categories, $categoryId)
    {
        static $breadcrumbs;

        if (!isset($breadcrumbs[$categoryId])) {
            $breadcrumbs[$categoryId] = '';
            $category = ['parentId' => $categoryId];

            do {
                if (!isset($categories[$category['parentId']])) {
                    $addon = Yml::unescape($category['parentId']);
                    $category = ['parentId' => null];
                } else {
                    $category = $categories[$category['parentId']];
                    $addon = Yml::unescape($category['name']);
                }

                if ($breadcrumbs[$categoryId] !== '') {
                    $breadcrumbs[$categoryId] = ' / ' . $breadcrumbs[$categoryId];
                }
                $breadcrumbs[$categoryId] = $addon . $breadcrumbs[$categoryId];
            } while (isset($category['parentId']));
        }

        return $breadcrumbs[$categoryId];
    }

    static function assetsOriginalXmlSave($domain, $items)
    {
        $dbItems = AssetOriginal::where('domain', '=', $domain)
            ->whereIn('item_id', array_keys($items))
            ->with(['assetDerivative'])
            ->get()
            ->keyBy('item_id');

        $updatePrices = true;
        static::resetSavingVars();
        $now = now();
        foreach ($items as $id => $attributes) {
            if (isset($dbItems[$id])) {
                $item = $dbItems[$id];
                $attributes['price_updated_at'] = $now;
                $attributes['details_updated_at'] = $attributes['price_updated_at'];
            } else {
                $attributes['created_at'] = $now;
                $attributes['discovered_at'] = $attributes['created_at'];
                $attributes['price_updated_at'] = $attributes['created_at'];
                $attributes['details_updated_at'] = $attributes['created_at'];
                $item = new AssetOriginal();
            }
            $item->fill($attributes);
            $attributes += array_diff_key($item->attributesToArray(), $attributes);
            $attributes['moderate'] = static::needToModerate($item, $updatePrices);
            $item->moderate = $attributes['moderate'];

            if (static::validationPasses($item, $attributes)) {
                static::trySave($item, $attributes, $updatePrices);
            }
            unset($items[$id]);
        }

        if (static::haveSaveErrors()) {
            static::throwException();
        }
    }
}
