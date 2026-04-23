<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * جلب جميع المدراء
     */
    public function index()
    {
        $admins = Admin::all();
        return response()->json([
            'status' => true,
            'data' => $admins
        ], 200);
    }

    /**
     * تسجيل الدخول (Login)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.'
            ], 401);
        }

        $token = $admin->createToken('AdminAuthToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data' => [
                'admin' => $admin, 
                'token' => $token
            ]
        ], 200);
    }

    /**
     * جلب بيانات المدير الحالي (Profile)
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
        ], 200);
    }

    /**
     * عرض بيانات مدير محدد
     */
    public function show($id)
    {
        $admin = Admin::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $admin
        ], 200);
    }

    /**
     * إنشاء مدير جديد (مخصصة للـ Super Admin)
     */
    public function store(Request $request)
    {
        // 1. التحقق من المدخلات
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email', // يمنع تكرار الإيميل
            'password' => 'required|string|min:6', // يجب ألا تقل عن 6 أحرف
            'role' => 'required|in:admin,super_admin', // يجب أن تكون إحدى هاتين القيمتين
        ]);

        // 2. إنشاء الحساب مع تشفير كلمة المرور
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // تشفير ضروري جداً
            'role' => $request->role,
        ]);

        // 3. إرجاع النتيجة
        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء حساب المدير بنجاح.',
            'data' => $admin
        ], 201);
    }

    /**
     * تحديث بيانات مدير (Update)
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id, // استثناء المدير الحالي من قاعدة المنع
            'password' => 'nullable|string|min:6', // جعل كلمة المرور اختيارية عند التعديل
            'role' => 'required|in:admin,super_admin',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // تحديث كلمة المرور فقط في حال تم إرسالها في الطلب
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث بيانات المدير بنجاح.',
            'data' => $admin
        ], 200);
    }

    /**
     * حذف مدير (Destroy)
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف المدير بنجاح.'
        ], 200);
    }

    /**
     * تسجيل الخروج (Logout)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الخروج بنجاح.'
        ], 200);
    }
}