<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'model_number',
        'origin_country',
        'description',
        'is_active',
        'price'
    ];

    public $translatable = ['name', 'description' , 'origin_country'];

    // القسم
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // العلامة التجارية
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // معرض الصور
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // المواصفات الفنية المجمعة
    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }

    // المميزات النقطية
    public function features()
    {
        return $this->hasMany(ProductFeature::class);
    }

    // قيم الخصائص (نظام الفلترة) Many-to-Many
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_value_product');
    }

    // أقسام الصفحة الرئيسية
    public function homepageSections()
    {
        return $this->belongsToMany(HomepageSection::class, 'homepage_section_product');
    }
}
