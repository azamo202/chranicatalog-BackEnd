<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductSpecification extends Model
{
    use HasTranslations;

    protected $fillable = ['product_id', 'group_name', 'spec_key', 'spec_value'];
    public $translatable = ['group_name', 'spec_key', 'spec_value'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
