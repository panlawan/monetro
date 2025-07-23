<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // เช็คว่าผู้ใช้ยืนยันอีเมลแล้วหรือยัง
        if (auth()->user()->hasVerifiedEmail()) {
            // ยืนยันแล้ว - แสดงหน้า dashboard
            return view('dashboard');
        } else {
            // ยังไม่ยืนยัน - ส่งไปหน้าแจ้งเตือน
            return redirect()->route('verification.notice');
        }
    }
}