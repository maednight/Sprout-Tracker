<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles transaction page and form requests.
 */
class TransactionController extends Controller
{
    /**
     * @var TransactionService Service used to manage transaction workflows.
     */
    private TransactionService $transactionService;

    /**
     * @param TransactionService $transactionService Service used to manage transaction workflows.
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(): View
    {
        $user = auth()->user();

        return view('public.transactions.transactions', [
            'transactionAnalyticsPayload' => $this->transactionService->buildIndexPayload($user->id),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('public.transactions.transaction-create', $user
            ? $this->transactionService->buildCreatePayload($user->id)
            : [
                'transaction' => null,
                'budgetGuardPayload' => ['budgetSnapshots' => [], 'spentByMonthCategory' => []],
            ]);
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);

        return view('public.transactions.transaction-create', $this->transactionService->buildEditPayload($transaction));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()
                ->route('login_view')
                ->withErrors([
                    'email' => 'Please log in again.',
                ]);
        }

        $validatedData = $this->validateTransactionData($request);

        $this->transactionService->createTransaction($user->id, $validatedData, $request);

        return $this->redirectToDashboard($request, $validatedData['transaction_date'] ?? null)
            ->with('success', 'Transaction saved successfully.')
            ->with('success_type', 'added');
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $validatedData = $this->validateTransactionData($request);

        $this->transactionService->updateTransaction($transaction, $validatedData, $request);

        return $this->redirectToDashboard($request, $validatedData['transaction_date'] ?? null)
            ->with('success', 'Transaction updated successfully.')
            ->with('success_type', 'edited');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $this->transactionService->deleteTransaction($transaction);

        return $this->redirectToDashboard(
            $request,
            $transaction->occurred_at?->format('Y-m-d')
        )
            ->with('success', 'Transaction deleted successfully.')
            ->with('success_type', 'deleted');
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

    private function redirectToDashboard(Request $request, ?string $fallbackDate = null): RedirectResponse
    {
        $returnTo = $request->input('return_to');

        if ($this->isSafeReturnPath($returnTo)) {
            return redirect()->to($returnTo);
        }

        $routeParameters = ['period' => 'month'];
        $normalizedDate = $this->normalizeDashboardDate($fallbackDate);

        if ($normalizedDate) {
            $routeParameters['date'] = $normalizedDate;
        }

        return redirect()->route('dashboard', $routeParameters);
    }

    private function normalizeDashboardDate(?string $dateValue): ?string
    {
        if (! is_string($dateValue) || trim($dateValue) === '') {
            return null;
        }

        foreach (['Y-m-d', 'm/d/Y'] as $dateFormat) {
            try {
                return Carbon::createFromFormat($dateFormat, $dateValue)
                    ->startOfDay()
                    ->format('Y-m-d');
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return null;
    }

    private function isSafeReturnPath(mixed $returnTo): bool
    {
        return is_string($returnTo)
            && $returnTo !== ''
            && str_starts_with($returnTo, '/')
            && ! str_starts_with($returnTo, '//');
    }
}
