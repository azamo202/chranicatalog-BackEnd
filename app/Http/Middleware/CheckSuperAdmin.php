<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // نتحقق إذا كان المستخدم المسجل دخوله هو super_admin
        if ($request->user() && $request->user()->role === 'super_admin') {
            return $next($request); // تفضل بالدخول
        }

        // إذا لم يكن super_admin، نرفض الطلب
        return response()->json([
            'status' => false,
            'message' => 'غير مصرح لك بإجراء هذه العملية. هذه الصلاحية مخصصة للمدير العام فقط.'
        ], 403);
    }
}