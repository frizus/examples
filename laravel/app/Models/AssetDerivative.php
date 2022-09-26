<?php

namespace App\Models;

use App\Casts\Properties;
use App\Rules\AllowExport;
use App\Rules\Categories;
use App\Rules\PropertiesJsonLimitations;

use App\Casts\AsCustomArrayObject;
use App\Casts\StringLinuxLineFeed;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class AssetDerivative
 * @package App\Models
 * @version June 10, 2021, 3:06 pm UTC
 *
 * @property \App\Models\AssetOriginal $assetOriginal
 * @property \App\Models\SyncStatus[] $syncStatuses
 * @property integer $domain
 * @property integer $original_id
 * @property string $item_id
 * @property string $name1
 * @property string $name2
 * @property integer $price
 * @property integer $old_price
 * @property string $properties
 * @property string $description
 * @property boolean $active
 * @property ArrayObject $categories
 * @property boolean $allow_export
 * @property boolean $auto_update_price
 * @property string|\Carbon\Carbon $derivative_created_at
 * @property string|\Carbon\Carbon $derivative_updated_at
 */
class AssetDerivative extends Base
{
    use HasFactory;

    public $table = 'assets_derivative';

    protected $primaryKey = 'id';

    const CREATED_AT = 'derivative_created_at';

    const UPDATED_AT = 'derivative_updated_at';

    public $fillable = [
        'domain',
        'original_id',
        'item_id',
        'name1',
        'name2',
        'price',
        'old_price',
        'properties',
        'description',
        'active',
        'categories',
        'allow_export',
        'auto_update_price',
        'derivative_created_at',
        'derivative_updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'domain' => 'integer',
        'original_id' => 'integer',
        'item_id' => StringLinuxLineFeed::class,
        'name1' => StringLinuxLineFeed::class,
        'name2' => StringLinuxLineFeed::class,
        'price' => 'integer',
        'old_price' => 'integer',
        'properties' => Properties::class,
        'description' => StringLinuxLineFeed::class,
        'active' => 'boolean',
        'auto_update_price' => 'boolean',
        'categories' => AsCustomArrayObject::class,
        'allow_export' => 'boolean',
        'derivative_created_at' => 'datetime',
        'derivative_updated_at' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static function rules($id = null)
    {
        return [
            'domain' => 'required|integer',
            'original_id' => 'required|integer',
            'item_id' => 'unique_with:assets_derivative,domain,item_id' . (isset($id) ? (',ignore:' . $id . ' = id') : ''),
            'name1' => 'required|string|max:2000',
            'name2' => 'required|string|max:2000',
            'price' => 'required|integer',
            'old_price' => 'nullable|integer',
            'properties' => [
                'nullable',
                'array',
                new PropertiesJsonLimitations,
            ],
            'description' => 'nullable|string',
            'categories' => [
                'nullable',
                new Categories
            ],
            'allow_export' => [
                'bail',
                'required',
                'boolean',
                new AllowExport,
            ],
            'auto_update_price' => 'required|boolean',
            'active' => 'nullable|boolean',
            'derivative_created_at' => 'nullable',
            'derivative_updated_at' => 'nullable',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function assetOriginal()
    {
        return $this->belongsTo(\App\Models\AssetOriginal::class, 'original_id');
    }

    public function bitrixIds()
    {
        return $this->hasMany(BitrixIds::class, 'target_domain', 'domain')
            ->where('item_id', $this->item_id);
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'domain', 'domain')
            ->where('item_id', $this->item_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syncStatuses()
    {
        return $this->hasMany(SyncStatus::class, 'target_domain', 'domain')
            ->where('item_id', $this->item_id);
    }
}
