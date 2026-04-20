<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. تهيئة الأقسام (Categories)
        // ربط الاسم العربي بـ slug إنجليزي لتجنب مشاكل الروابط
        $categoriesData = [
            'الثلاجات' => 'refrigerators',
            'الأفران' => 'ovens',
            'الشفاطات' => 'hoods'
        ];

        $categories = [];
        foreach ($categoriesData as $catName => $slug) {
            $categories[$catName] = Category::firstOrCreate(
                ['slug' => $slug], // البحث عن طريق السلاج لتجنب التكرار
                ['name' => $catName] // إنشاء الاسم في حال عدم الوجود
            );
        }

        // 2. تهيئة العلامات التجارية (Brands)
        // الاعتماد على الاسم فقط بما أن جدول brands لا يحتوي على حقل slug
        $brandsData = ['ALPA', 'ALFA', 'MOLINEX'];
        $brands = [];
        foreach ($brandsData as $brandName) {
            $brands[$brandName] = Brand::firstOrCreate(
                ['name' => $brandName], // البحث عن طريق الاسم لمنع التكرار
                ['logo' => null]        // إنشاء الحقول المتبقية في حال عدم الوجود
            );
        }

        // 3. قوالب بيانات المنتجات باللغات الثلاث
        $productTemplates = [
            'الثلاجات' => [
                ['en' => 'Side by Side Refrigerator 600L', 'ar' => 'ثلاجة بابين جنباً إلى جنب 600 لتر', 'ku' => 'ساردکەرەوەی دوو دەرگا 600 لیتر'],
                ['en' => 'Top Mount Refrigerator 450L', 'ar' => 'ثلاجة بفريزر علوي 450 لتر', 'ku' => 'ساردکەرەوەی بەشی سەرەوە 450 لیتر'],
                ['en' => 'Bottom Freezer Refrigerator 320L', 'ar' => 'ثلاجة بفريزر سفلي 320 لتر', 'ku' => 'ساردکەرەوەی بەشی خوارەوە 320 لیتر'],
                ['en' => 'Mini Bar Refrigerator 90L', 'ar' => 'ثلاجة ميني بار 90 لتر', 'ku' => 'ساردکەرەوەی مینی بار 90 لیتر'],
                ['en' => 'French Door Refrigerator 700L', 'ar' => 'ثلاجة باب فرنسي 700 لتر', 'ku' => 'ساردکەرەوەی دەرگای فەرەنسی 700 لیتر'],
            ],
            'الأفران' => [
                ['en' => 'Freestanding Gas Oven 5 Burners', 'ar' => 'فرن غاز قائم بذاته 5 شعلات', 'ku' => 'فڕنی غازی سەربەخۆ 5 چاو'],
                ['en' => 'Built-in Electric Oven 60cm', 'ar' => 'فرن كهربائي مدمج 60 سم', 'ku' => 'فڕنی کارەبایی ناوەکی 60 سم'],
                ['en' => 'Gas Oven with Grill 90cm', 'ar' => 'فرن غاز مع شواية 90 سم', 'ku' => 'فڕنی غاز لەگەڵ برژێنەر 90 سم'],
                ['en' => 'Multifunction Electric Oven 70L', 'ar' => 'فرن كهربائي متعدد الوظائف 70 لتر', 'ku' => 'فڕنی کارەبایی فرە کردار 70 لیتر'],
                ['en' => 'Compact Microwave Oven 45cm', 'ar' => 'فرن ميكروويف مدمج 45 سم', 'ku' => 'فڕنی مایکرۆوەیڤی بچووک 45 سم'],
            ],
            'الشفاطات' => [
                ['en' => 'Wall Mount Chimney Hood 90cm', 'ar' => 'شفاط مطبخ جداري 90 سم', 'ku' => 'هەواکێشی چێشتخانەی دیوار 90 سم'],
                ['en' => 'Built-in Cooker Hood 60cm', 'ar' => 'شفاط مدمج 60 سم', 'ku' => 'هەواکێشی ناوەکی 60 سم'],
                ['en' => 'Island Cooker Hood 90cm', 'ar' => 'شفاط جزيرة معلق 90 سم', 'ku' => 'هەواکێشی دوورگەیی 90 سم'],
                ['en' => 'Telescopic Range Hood 60cm', 'ar' => 'شفاط تلسكوبي قابل للسحب 60 سم', 'ku' => 'هەواکێشی تەلەسکۆپی 60 سم'],
                ['en' => 'Decorative Glass Hood 90cm', 'ar' => 'شفاط ديكوري بزجاج مقوى 90 سم', 'ku' => 'هەواکێشی شوشەی دیکۆری 90 سم'],
            ]
        ];

        $origins = ['Turkey', 'Italy', 'Germany', 'China', 'Egypt'];

        $descriptions = [
            'en' => 'High quality product with excellent performance, modern design, and energy efficiency to meet all your daily needs.',
            'ar' => 'منتج عالي الجودة بأداء ممتاز وتصميم عصري، موفر للطاقة لتلبية كافة احتياجاتك اليومية بكفاءة عالية.',
            'ku' => 'بەرهەمێکی کوالێتی بەرزە بە کارکردنێکی نایاب و دیزاینێکی مۆدێرن، وزە دەپارێزێت بۆ پڕکردنەوەی هەموو پێداویستییە ڕۆژانەکانت.'
        ];

        // تعطيل قيود المفاتيح الأجنبية لتجنب الأخطاء أثناء الإدراج إن أردت عمل Truncate مستقبلاً
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 4. توليد 60 منتجاً (20 لكل قسم)
        foreach ($productTemplates as $categoryName => $templates) {
            $categoryId = $categories[$categoryName]->id;

            for ($i = 0; $i < 20; $i++) {
                // اختيار قالب عشوائي من القسم الحالي
                $template = $templates[array_rand($templates)];

                // توليد بيانات فريدة للمنتج
                $modelNumber = strtoupper(Str::random(3)) . '-' . rand(1000, 9999);
                $brand = $brands[$brandsData[array_rand($brandsData)]];
                $origin = $origins[array_rand($origins)];
                $price = rand(150, 1500) + (rand(0, 99) / 100); // توليد سعر عشري منطقي مثل 450.99

                // السلاج يجب أن يكون فريداً لتجنب مشاكل Unique Constraint
                $slug = Str::slug($template['en'] . '-' . $modelNumber);

                Product::create([
                    'category_id' => $categoryId,
                    'brand_id' => $brand->id,
                    'name' => [
                        'en' => $template['en'],
                        'ar' => $template['ar'],
                        'ku' => $template['ku'],
                    ],
                    'slug' => $slug,
                    'model_number' => $modelNumber,
                    'origin_country' => $origin,
                    'description' => $descriptions, // تم توحيد الوصف ليكون احترافي ومناسب للجميع
                    'price' => $price,
                    'is_active' => true,
                ]);
            }
        }

        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
