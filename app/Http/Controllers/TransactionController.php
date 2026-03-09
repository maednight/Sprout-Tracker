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
        return view('public.transaction-create', [
            'transaction' => null,
        ]);
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);

        $transaction->load(['category', 'account']);

        return view('public.transaction-create', [
            'transaction' => $transaction,
        ]);
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

        $validatedData = $this->validateTransactionData($request);

        $normalizedAmount = $this->normalizeAmount($validatedData['amount']);

        if ($normalizedAmount === null) {
            return back()
                ->withErrors([
                    'amount' => 'Please enter a valid amount.',
                ])
                ->withInput();
        }

        $transactionType = $validatedData['transaction_type'];
        $occurredAt = $this->buildOccurredAt($validatedData['transaction_date']);

        $categoryId = $this->resolveCategoryId(
            $user->id,
            $transactionType,
            $validatedData['category'] ?? null
        );

        $accountId = $this->resolveAccountId(
            $user->id,
            $validatedData['account'] ?? null
        );

        Transaction::create([
            'user_id' => $user->id,
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $validatedData['description'] ?? null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction saved successfully.');
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $validatedData = $this->validateTransactionData($request);

        $normalizedAmount = $this->normalizeAmount($validatedData['amount']);

        if ($normalizedAmount === null) {
            return back()
                ->withErrors([
                    'amount' => 'Please enter a valid amount.',
                ])
                ->withInput();
        }

        $transactionType = $validatedData['transaction_type'];
        $occurredAt = $this->buildOccurredAt($validatedData['transaction_date']);

        $categoryId = $this->resolveCategoryId(
            $transaction->user_id,
            $transactionType,
            $validatedData['category'] ?? null
        );

        $accountId = $this->resolveAccountId(
            $transaction->user_id,
            $validatedData['account'] ?? null
        );

        $transaction->update([
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $validatedData['description'] ?? null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $transaction->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction deleted successfully.');
    }

    /* Validate transaction data */
    private function validateTransactionData(Request $request): array
    {
        return $request->validate([
            'transaction_type' => ['required', 'in:income,expense,savings'],
            'transaction_date' => ['required', 'date_format:m/d/Y'],
            'amount' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:80'],
            'account' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /* Normalize amount */
    private function normalizeAmount(string $amount): ?string
    {
        $normalizedAmount = preg_replace('/[^\d.]/', '', $amount);

        if (!$normalizedAmount || !is_numeric($normalizedAmount)) {
            return null;
        }

        return $normalizedAmount;
    }

    /* Build occurred at */
    private function buildOccurredAt(string $transactionDate): Carbon
    {
        $selectedDate = Carbon::createFromFormat('m/d/Y', $transactionDate);
        $currentTime = now();

        return $selectedDate->copy()->setTime(
            $currentTime->hour,
            $currentTime->minute,
            $currentTime->second
        );
    }

    /* Resolve category id */
    private function resolveCategoryId(int $userId, string $transactionType, ?string $categoryName): ?int
    {
        if (!$categoryName || trim($categoryName) === '') {
            return null;
        }

        $category = Category::firstOrCreate([
            'user_id' => $userId,
            'type' => $transactionType,
            'name' => trim($categoryName),
        ]);

        return $category->id;
    }

    /* Resolve account id */
    private function resolveAccountId(int $userId, ?string $accountName): ?int
    {
        if (!$accountName || trim($accountName) === '') {
            return null;
        }

        $account = Account::firstOrCreate([
            'user_id' => $userId,
            'name' => trim($accountName),
        ]);

        return $account->id;
    }

    /* Authorize transaction */
    private function authorizeTransaction(Transaction $transaction): void
    {
        abort_unless(
            auth()->check() && auth()->id() === $transaction->user_id,
            403
        );
    }
}