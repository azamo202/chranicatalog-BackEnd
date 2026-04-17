<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'value'];

    // الخاصية الأب (مثل: اللون)
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    // المنتجات التي تمتلك هذه القيمة
    public function products()
    {
        return $this->belongsToMany(Product::class, 'attribute_value_product');
    }
}
