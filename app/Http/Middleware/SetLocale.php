<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. التحقق مما إذا كان الطلب يحتوي على هيدر Accept-Language
        if ($request->hasHeader('Accept-Language')) {
            
            // 2. جلب قيمة اللغة من الهيدر (مثل: ar, en, ku)
            $locale = $request->header('Accept-Language');
            
            // 3. تحديد اللغات المسموحة في نظامك (اختياري للأمان)
            $supportedLocales = ['ar', 'en', 'ku'];

            // 4. إذا كانت اللغة مدعومة، قم بتغيير لغة النظام الحالية
            if (in_array($locale, $supportedLocales)) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}