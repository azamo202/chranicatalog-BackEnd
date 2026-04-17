<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations; // تفعيل الحزمة
    protected $fillable = ['parent_id', 'name', 'slug', 'image', 'is_active'];

    public $translatable = ['name'];
    // القسم الأب
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // الأقسام الفرعية
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // المنتجات التابعة لهذا القسم
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
