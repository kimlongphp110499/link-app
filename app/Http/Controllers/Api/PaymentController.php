<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TransactionHistory;

class PaymentController extends Controller
{
    /**
     * API nạp tiền cho người dùng.
     */
    public function addPoints(Request $request)
    {
        $auth =  auth()->user();
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'amount' => 'required|integer|min:1', // Kiểm tra số tiền nạp
        ]);

        // Lấy người dùng
        $user = User::findOrFail($auth->id);

        // Cập nhật số điểm (nạp tiền)
        $user->points += $request->amount;
        $user->save();

        // Lưu lịch sử giao dịch
        TransactionHistory::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'transaction_type' => 'Deposit',
        ]);

        // Trả về phản hồi JSON
        return response()->json([
            'message' => 'Money added successfully!',
            'user' => $user,
            'new_points' => $user->points
        ], 200);
    }
}
