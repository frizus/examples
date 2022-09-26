<?php
namespace App\TargetDomains\Libraries\Image;

use App\Models\Image;

class ImagesFill
{
    public static function fill($domain, $item_id, $urls)
    {
        if (!is_array($urls) || empty($urls)) {
            Image::where('domain', '=', $domain)
                ->where('item_id', '=', $item_id)
                ->delete();
            return true;
        }

        $images = Image::where('domain', '=', $domain)
            ->where('item_id', '=', $item_id)
            ->get()
            ->keyBy('source');

        $deleteKeys = $images->keys()->flip()->toArray();

        $i = 0;
        foreach ($urls as $url) {
            $firstImage = $i == 0;
            if (!$images->has($url)) {
                $image = new Image([
                    'domain' => $domain,
                    'item_id' => $item_id,
                    'source' => $url,
                    'first_image' => $firstImage,
                    'uploaded' => false,
                ]);
                if (!$image->save()) {
                    return false;
                }
            } else {
                $image = $images->get($url);
                if ($firstImage != $image->first_image) {
                    $image->first_image = $firstImage;
                    if (!$image->save()) {
                        return false;
                    }
                }
            }

            unset($deleteKeys[$url]);
            $i++;
        }

        if (!empty($deleteKeys)) {
            $ids = [];
            foreach (array_keys($deleteKeys) as $deleteKey) {
                $ids[] = $images->get($deleteKey)->id;
            }

            Image::whereIn('id', $ids)->delete();
        }

        return true;
    }
}
