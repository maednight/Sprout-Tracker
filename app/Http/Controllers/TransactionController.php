<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function create(): View
    {
        return view('public.transaction-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()
                ->route('login.view')
                ->withErrors([
                    'email' => 'Please log in again.',
                ]);
        }

        $validatedData = $request->validate([
            'transaction_type' => ['required', 'in:income,expense,savings'],
            'transaction_date' => ['required', 'date_format:m/d/Y'],
            'amount' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:80'],
            'account' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $normalizedAmount = preg_replace('/[^\d.]/', '', $validatedData['amount']);

        if (!$normalizedAmount || !is_numeric($normalizedAmount)) {
            return back()
                ->withErrors([
                    'amount' => 'Please enter a valid amount.',
                ])
                ->withInput();
        }

        $transactionType = $validatedData['transaction_type'];

        $parsedDate = Carbon::createFromFormat(
            'm/d/Y',
            $validatedData['transaction_date']
        )->format('Y-m-d');

        $categoryId = null;

        if (!empty($validatedData['category'])) {
            $category = Category::firstOrCreate([
                'user_id' => $user->id,
                'type' => $transactionType,
                'name' => trim($validatedData['category']),
            ]);

            $categoryId = $category->id;
        }

        $accountId = null;

        if (!empty($validatedData['account'])) {
            $account = Account::firstOrCreate([
                'user_id' => $user->id,
                'name' => trim($validatedData['account']),
            ]);

            $accountId = $account->id;
        }

        Transaction::create([
            'user_id' => $user->id,
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $parsedDate . ' 12:00:00',
            'description' => $validatedData['description'] ?? null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction saved successfully.');
    }
}