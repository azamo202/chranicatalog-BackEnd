<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SiteContactController extends Controller
{
    /**
     * إرسال رسالة من نموذج الاتصال إلى البريد الإلكتروني الخاص بالشركة
     */
    public function send(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|min:10',
        ]);

        $data = $request->only('name', 'email', 'message');

        try {
            // 2. إرسال الإيميل
            Mail::raw("رسالة جديدة من الموقع:\n\nالاسم: {$data['name']}\nالبريد الإلكتروني: {$data['email']}\n\nالرسالة:\n{$data['message']}", function ($message) use ($data) {
                $message->to('chrani.company@gmail.com')
                        ->subject('رسالة اتصال جديدة: ' . $data['name']);
            });

            return response()->json([
                'status' => true,
                'message' => 'تم إرسال رسالتك بنجاح. سنقوم بالرد عليك قريباً.'
            ]);
        } catch (\Exception $e) {
            // في حال فشل الإرسال (مثلاً عدم إعداد SMTP)
            return response()->json([
                'status' => false,
                'message' => 'عذراً، حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
}
