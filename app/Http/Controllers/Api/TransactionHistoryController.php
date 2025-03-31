<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionHistory;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    /**
     * Lấy lịch sử giao dịch nạp tiền của người dùng
     */
    public function getHistory($userId)
    {
        // Lấy lịch sử giao dịch của người dùng từ bảng transaction_histories
        $transactions = TransactionHistory::where('user_id', $userId)
                                          ->orderBy('created_at', 'desc')
                                          ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'message' => 'No transactions found for this user.'
            ], 404);
        }

        return response()->json([
            'message' => 'Transaction history fetched successfully!',
            'transactions' => $transactions
        ], 200);
    }
}

