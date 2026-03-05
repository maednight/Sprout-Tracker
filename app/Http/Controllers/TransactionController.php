<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $data = $request->validate([
            'type' => ['required', 'in:income,expense,savings'],
            'date' => ['required', 'date'], // yyyy-mm-dd
            'time' => ['nullable', 'date_format:H:i'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category' => ['nullable', 'string', 'max:80'],
            'account' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $categoryId = null;
        if (!empty($data['category'])) {
            $categoryId = Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => trim($data['category'])]
            )->id;
        }

        $accountId = null;
        if (!empty($data['account'])) {
            $accountId = Account::firstOrCreate(
                ['user_id' => $user->id, 'name' => trim($data['account'])]
            )->id;
        }

        $time = $data['time'] ?? '12:00';
        $occurredAt = $data['date'] . ' ' . $time . ':00';

        $tx = Transaction::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['ok' => true, 'transaction' => $tx], 201);
    }
}