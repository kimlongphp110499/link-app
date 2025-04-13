<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $checkSchedule = Schedule::count();

        return view('admin.dashboard', compact('checkSchedule'));
    }

    // Hiển thị trang đăng nhập
    public function showLoginForm()
    {
        return view('admin.login');
    }

    // Xử lý đăng nhập
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
    
        // Attempt to log in the user
        $credentials = $request->only('email', 'password');
        if (Auth::guard('admin')->attempt($credentials, $request->remember)) {
            // If successful, redirect to admin dashboard
            return redirect()->intended('admin/dashboard');
        }
    
        // If login fails, redirect back with error message
        return redirect()->back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }
    

    // Đăng xuất
    public function logout()
    {
        Auth::logout();
        return redirect('/admin/login');
    }

    // Hiển thị danh sách Admin
    public function index()
    {
        $admins = Admin::paginate(20);
        return view('admin.index', compact('admins'));
    }

    // Hiển thị form tạo mới admin
    public function create()
    {
        return view('admin.create');
    }

    // Lưu thông tin admin mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.index');
    }

    // Hiển thị form chỉnh sửa admin
    public function edit(Admin $admin)
    {
        return view('admin.edit', compact('admin'));
    }

    // Cập nhật thông tin admin
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $admin->password,
        ]);

        return redirect()->route('admin.index');
    }

    // Xóa admin
    public function destroy(Admin $admin)
    {
        $admin->delete();
        return redirect()->route('admin.index');
    }

    public function show(Request $request)
    {
    }
}
