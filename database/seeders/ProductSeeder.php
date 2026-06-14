<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Setup a category matching the example
        $category = Category::firstOrCreate(
            ['slug' => 'static-cooling'],
            ['name' => ['en' => 'Static Cooling', 'ar' => 'تبريد مباشر', 'ku' => 'دیفرۆست'], 'is_active' => 1]
        );

        // Setup a brand matching the example
        $brand = Brand::firstOrCreate(
            ['name' => 'iLK'],
            ['logo' => 'brands/cBa9v24ll1pki1P7YgOb6N5uXsHKzLApLNWzHgad.webp']
        );

        $colorsEn = ['White', 'Black', 'Silver', 'Grey', 'Red'];
        $colorsAr = ['ابيض', 'اسود', 'فضي', 'رصاصي', 'احمر'];
        $colorsKu = ['سپی', 'ڕەش', 'زیو', 'ڕەساسی', 'سوور'];

        $imagePaths = [
            'products/yqHJNvfZehs0bvhjKkuuvtwmXDP42xWsa8ZOccc0.webp',
            'products/AjygSTbjRAZXcZ4mWfOtDDAqD2NckiJCfXIF2HL9.webp',
            'products/ECdam24MNwO73HOgwXiXvM6JD4euvl5x4eJCqtnw.webp',
            'products/D16K5Dkhg1LeiB6p1AooWvOb3KKmzvnbLh3Y0uRw.webp',
            'products/YOgE1EQQAKrebXPW5AbEN7ukLNZoyj5wUjFpT7Jb.webp',
            'products/yK1jLeKX5iVmzdac5HBEFJdn6tcjOQy7o7xDddE1.webp',
            'products/vSl6jziPeGASCLCuqpsQ4ifln09IjBE8faNa2D2t.webp',
            'products/qIMpss1z1rIGdWnUrN2TlsHi2m4S8Z1JcyHAuqpQ.webp',
            'products/WIFbsEM85ZdpOZz2ICJoscQMitbscLTvIsvz9nWg.webp'
        ];

        for ($i = 0; $i < 50; $i++) {
            $colorIndex = array_rand($colorsEn);
            $capacity = rand(200, 800);
            
            $nameEn = "iLK Refrigerator {$capacity} Liters " . $colorsEn[$colorIndex];
            $nameAr = "ثلاجة iLK سعة {$capacity} لتر " . $colorsAr[$colorIndex];
            $nameKu = "سەلاجەی iLK قەبارە {$capacity} لیتر " . $colorsKu[$colorIndex];

            $slug = Str::slug($nameEn . '-' . Str::random(5));
            $modelNumber = 'iLKR-' . strtoupper(Str::random(2)) . $capacity . 'GL';

            $product = Product::create([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => [
                    'ar' => $nameAr,
                    'en' => $nameEn,
                    'ku' => $nameKu
                ],
                'slug' => $slug,
                'model_number' => $modelNumber,
                'origin_country' => [
                    'ar' => 'تركيا',
                    'en' => 'Turkey',
                    'ku' => 'تورکیا'
                ],
                'description' => [
                    'ar' => "ثلاجة iLK تركية الصنع سعة {$capacity} لتر تبريد مباشر\r\n- تبريد مباشر: للحفاظ على جودة وطراوة الأطعمة بفعالية.\r\n- مروحة هواء لتوزيع التبريد: توزيع مثالي ومتساوٍ للهواء البارد في كافة الأرفف.",
                    'en' => "iLK Refrigerator, Turkish Made, Capacity {$capacity}Liters Direct Cooling\r\n- Direct Cooling: Efficiently preserves food quality and freshness.\r\n- Cooling Distribution Air Fan: Perfect and even distribution of cold air across all shelves.",
                    'ku' => "سەلاجەی تورکی iLK قەبارە {$capacity} لیتر - ساردکردنەوەی ڕاستەوخۆ (دایرێکت کۆڵینگ)\r\n- ساردکردنەوەی ڕاستەوخۆ: بۆ پاراستنی کوالێتی و تازەیی خۆراک بە شێوازێکی کاریگەر."
                ],
                'is_active' => 1,
                'sort_order' => $i + 1,
            ]);

            // Add Images
            shuffle($imagePaths);
            $imagesCount = rand(3, 6);
            for ($j = 0; $j < $imagesCount; $j++) {
                $product->images()->create([
                    'image_path' => $imagePaths[$j],
                    'is_primary' => $j === 0 ? 1 : 0,
                    'sort_order' => $j + 1,
                ]);
            }

            // Add Specifications
            $product->specifications()->createMany([
                [
                    'group_name' => ['ar' => 'السعة', 'en' => 'Capacity', 'ku' => 'قەبارە'],
                    'spec_key' => ['ar' => 'لتر', 'en' => 'Liters', 'ku' => 'لیتر'],
                    'spec_value' => ['ar' => (string)$capacity, 'en' => (string)$capacity, 'ku' => (string)$capacity],
                ],
                [
                    'group_name' => ['ar' => 'الأبعاد', 'en' => 'Dimensions', 'ku' => 'ڕەهەندەکان'],
                    'spec_key' => ['ar' => 'العرض', 'en' => 'Width', 'ku' => 'پانی'],
                    'spec_value' => ['ar' => '70 سم', 'en' => '70 cm', 'ku' => '70 سم'],
                ],
                [
                    'group_name' => ['ar' => 'الأبعاد', 'en' => 'Dimensions', 'ku' => 'ڕەهەندەکان'],
                    'spec_key' => ['ar' => 'الأرتفاع', 'en' => 'Height', 'ku' => 'بەرزی'],
                    'spec_value' => ['ar' => '183 سم', 'en' => '183 cm', 'ku' => '183 سم'],
                ],
                [
                    'group_name' => ['ar' => 'الأبعاد', 'en' => 'Dimensions', 'ku' => 'ڕەهەندەکان'],
                    'spec_key' => ['ar' => 'العمق', 'en' => 'Depth', 'ku' => 'قووڵی'],
                    'spec_value' => ['ar' => '73.5 سم', 'en' => '73.5 cm', 'ku' => '73.5 سم'],
                ],
                [
                    'group_name' => ['ar' => 'حمولة الحاوية', 'en' => 'Container Load', 'ku' => 'بارستایی کۆنتینەر'],
                    'spec_key' => ['ar' => 'قطعة', 'en' => 'Pieces', 'ku' => 'دانە'],
                    'spec_value' => ['ar' => '72', 'en' => '72', 'ku' => '72'],
                ]
            ]);

            // Add Features
            $product->features()->createMany([
                [
                    'feature_text' => ['ar' => 'تبريد مباشر', 'en' => 'Direct Cooling', 'ku' => 'ساردکردنەوەی ڕاستەوخۆ (دایرێکت کۆڵینگ)'],
                    'sort_order' => 1
                ],
                [
                    'feature_text' => ['ar' => 'مكثف خارجي', 'en' => 'External Condenser', 'ku' => 'چڕکەرەوەی دەرەکی (کۆندێنسەری دەرەکی)'],
                    'sort_order' => 2
                ],
                [
                    'feature_text' => ['ar' => 'اضاءة داخلية', 'en' => 'Interior Lighting', 'ku' => 'ڕووناکی ناوخۆیی (گڵۆپی ناوەوە)'],
                    'sort_order' => 3
                ],
                [
                    'feature_text' => ['ar' => 'مجرات قابلة لتعيير الرطوبة', 'en' => 'Humidity-Controlled Crisper Drawers', 'ku' => 'چەکمەجەی کۆنتڕۆڵکردنی شێ (شێ ڕێکخراو)'],
                    'sort_order' => 4
                ]
            ]);
        }
    }
}
