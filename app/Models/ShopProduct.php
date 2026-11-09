<?php

namespace App\Models;

use Database\Factories\ShopProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shop_id
 * @property int $product_id
 * @property string $price
 * @property string|null $name_override
 * @property string|null $description_override
 * @property string|null $thumbnail_override
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $resolved_name
 * @property-read string|null $resolved_description
 * @property-read string|null $resolved_thumbnail
 */
class ShopProduct extends Model
{
    /** @use HasFactory<ShopProductFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'product_id',
        'price',
        'name_override',
        'description_override',
        'thumbnail_override',
        'is_active',
    ];

    protected $appends = [
        'resolved_name',
        'resolved_description',
        'resolved_thumbnail',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function resolvedName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name_override ?: $this->product?->name
        );
    }

    protected function resolvedDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->description_override ?: $this->product?->description
        );
    }

    protected function resolvedThumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->thumbnail_override ?: $this->product?->thumbnail
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
