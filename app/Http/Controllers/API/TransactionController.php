<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $status = $request->input('status');

        $transaction = Transaction::with('items')->where('user_id', Auth::id());

        if ($id) {
            $transaction = $transaction->find($id);

            if ($transaction) {
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );
            }
        }

        if ($status) {
            $transaction->where('status', $status);
        }

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
    }

    public function checkout(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'exists:products,id',
                'items.*.quantity' => 'required|numeric',
                'total_price' => 'required',
                'shipping_price' => 'required',
                'status' => 'required|in:PENDING,SUCCESS,FAILED,CANCELLED,SHIPPING,SHIPPED',
                'address' => 'required'
            ]);

            $validated['user_id'] = Auth::id();

            $transaction = Transaction::create($validated);

            foreach ($validated['items'] as $product) {
                TransactionItem::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product['id'],
                    'transaction_id' => $transaction->id,
                    'quantity' => $product['quantity'],
                ]);
            }

            return ResponseFormatter::success(
                $transaction->load('items'),
                'Transaksi berhasil'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went wrong',
                    'error' => $error,
                ],
                'Data transaksi gagal',
                400
            );
        }
    }
}
