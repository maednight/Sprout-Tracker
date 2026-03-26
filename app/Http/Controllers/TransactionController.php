<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();

        return view('public.transactions', [
            'transactionAnalyticsPayload' => $this->transactionService->buildIndexPayload($user->id),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('public.transaction-create', $user
            ? $this->transactionService->buildCreatePayload($user->id)
            : [
                'transaction' => null,
                'budgetGuardPayload' => ['budgetSnapshots' => [], 'spentByMonthCategory' => []],
            ]);
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);

        return view('public.transaction-create', $this->transactionService->buildEditPayload($transaction));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()
                ->route('login.view')
                ->withErrors([
                    'email' => 'Please log in again.',
                ]);
        }

        $validatedData = $this->validateTransactionData($request);

        $this->transactionService->createTransaction($user->id, $validatedData, $request);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction saved successfully.');
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $validatedData = $this->validateTransactionData($request);

        $this->transactionService->updateTransaction($transaction, $validatedData, $request);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $this->transactionService->deleteTransaction($transaction);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction deleted successfully.');
    }

    private function validateTransactionData(Request $request): array
    {
        return $request->validate([
            'transaction_type' => ['required', 'in:income,expense,savings'],
            'transaction_date' => ['required', 'date_format:m/d/Y'],
            'amount' => ['required', 'string'],
            'category' => ['required', 'string', 'max:80'],
            'account' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'receipt_photos' => ['nullable', 'array'],
            'receipt_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'receipt_photo_camera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_receipt_photo_paths' => ['nullable', 'string'],
        ]);
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        abort_unless(
            auth()->check() && auth()->id() === $transaction->user_id,
            403
        );
    }
}
