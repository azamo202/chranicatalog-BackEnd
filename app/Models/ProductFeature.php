<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductFeature extends Model
{
    use HasTranslations;

    protected $fillable = ['product_id', 'feature_text', 'sort_order'];
    public $translatable = ['feature_text'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
