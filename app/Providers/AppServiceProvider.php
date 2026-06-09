<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Brand;
use App\Models\HomepageSection;
use App\Models\StoreSetting;
use App\Models\SupportVideo;
use App\Models\SupportDownload;
use App\Models\MaintenanceCenter;
use App\Services\RevalidationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Revalidate on Product changes
        Product::saved(function ($product) {
            RevalidationService::revalidate([
                'products',
                "product-{$product->id}",
                'home-sections',
            ]);
        });
        Product::deleted(function ($product) {
            RevalidationService::revalidate([
                'products',
                "product-{$product->id}",
                'home-sections',
            ]);
        });

        // Revalidate on ProductImage changes (e.g. primary image toggled or images added/deleted)
        ProductImage::saved(function ($image) {
            RevalidationService::revalidate([
                'products',
                "product-{$image->product_id}",
                'home-sections',
            ]);
        });
        ProductImage::deleted(function ($image) {
            RevalidationService::revalidate([
                'products',
                "product-{$image->product_id}",
                'home-sections',
            ]);
        });

        // Revalidate on Category changes
        Category::saved(function ($category) {
            RevalidationService::revalidate([
                'categories',
                'products',
                'home-sections',
            ]);
        });
        Category::deleted(function ($category) {
            RevalidationService::revalidate([
                'categories',
                'products',
                'home-sections',
            ]);
        });

        // Revalidate on Brand changes
        Brand::saved(function ($brand) {
            RevalidationService::revalidate([
                'brands',
                'products',
            ]);
        });
        Brand::deleted(function ($brand) {
            RevalidationService::revalidate([
                'brands',
                'products',
            ]);
        });

        // Revalidate on HomepageSection changes
        HomepageSection::saved(function ($section) {
            RevalidationService::revalidate([
                'home-sections',
            ]);
        });
        HomepageSection::deleted(function ($section) {
            RevalidationService::revalidate([
                'home-sections',
            ]);
        });

        // Revalidate on StoreSetting changes
        StoreSetting::saved(function ($setting) {
            RevalidationService::revalidate([
                'store-settings',
            ]);
        });
        StoreSetting::deleted(function ($setting) {
            RevalidationService::revalidate([
                'store-settings',
            ]);
        });

        // Revalidate on SupportVideo changes
        SupportVideo::saved(function ($video) {
            RevalidationService::revalidate([
                'videos',
            ]);
        });
        SupportVideo::deleted(function ($video) {
            RevalidationService::revalidate([
                'videos',
            ]);
        });

        // Revalidate on SupportDownload changes
        SupportDownload::saved(function ($download) {
            RevalidationService::revalidate([
                'downloads',
            ]);
        });
        SupportDownload::deleted(function ($download) {
            RevalidationService::revalidate([
                'downloads',
            ]);
        });

        // Revalidate on MaintenanceCenter changes
        MaintenanceCenter::saved(function ($center) {
            RevalidationService::revalidate([
                'maintenance-centers',
            ]);
        });
        MaintenanceCenter::deleted(function ($center) {
            RevalidationService::revalidate([
                'maintenance-centers',
            ]);
        });
    }
}
