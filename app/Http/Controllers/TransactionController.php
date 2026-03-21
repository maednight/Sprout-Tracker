<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $transactions = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $initialDisplayDate = $transactions->isNotEmpty()
            ? $transactions->first()->occurred_at->format('Y-m-d')
            : now()->format('Y-m-d');

        return view('public.transactions', [
            'transactionAnalyticsPayload' => [
                'transactionGroups' => $this->buildTransactionGroups($transactions),
                'initialDisplayDate' => $initialDisplayDate,
            ],
        ]);
    }

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

        $receiptPhotoPaths = $this->storeReceiptPhotos($request);

        Transaction::create([
            'user_id' => $user->id,
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $validatedData['description'] ?? null,
            'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
            'receipt_photo_paths' => $receiptPhotoPaths,
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

        $receiptPhotoPaths = $this->resolveUpdatedReceiptPhotoPaths($request, $transaction);

        $transaction->update([
            'type' => $transactionType,
            'amount' => $normalizedAmount,
            'category_id' => $categoryId,
            'account_id' => $accountId,
            'occurred_at' => $occurredAt,
            'description' => $validatedData['description'] ?? null,
            'receipt_photo_path' => $receiptPhotoPaths[0] ?? null,
            'receipt_photo_paths' => $receiptPhotoPaths,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        foreach ($this->getTransactionPhotoPaths($transaction) as $photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

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
            'receipt_photos' => ['nullable', 'array'],
            'receipt_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'receipt_photo_camera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_receipt_photo_paths' => ['nullable', 'string'],
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

    /* Get transaction photo paths */
    private function getTransactionPhotoPaths(Transaction $transaction): array
    {
        $photoPaths = is_array($transaction->receipt_photo_paths)
            ? $transaction->receipt_photo_paths
            : [];

        if (empty($photoPaths) && $transaction->receipt_photo_path) {
            $photoPaths = [$transaction->receipt_photo_path];
        }

        return array_values(array_filter($photoPaths));
    }

    /* Decode existing photo paths input */
    private function decodeExistingPhotoPaths(?string $rawValue): array
    {
        if (!$rawValue) {
            return [];
        }

        $decodedValue = json_decode($rawValue, true);

        if (!is_array($decodedValue)) {
            return [];
        }

        return array_values(array_filter($decodedValue));
    }

    /* Store receipt photos */
    private function storeReceiptPhotos(Request $request): array
    {
        $storedPaths = [];

        $galleryFiles = $request->file('receipt_photos', []);

        if (is_array($galleryFiles)) {
            foreach ($galleryFiles as $uploadedFile) {
                if ($uploadedFile) {
                    $storedPaths[] = $uploadedFile->store('transaction-photos', 'public');
                }
            }
        }

        $cameraFile = $request->file('receipt_photo_camera');

        if ($cameraFile) {
            $storedPaths[] = $cameraFile->store('transaction-photos', 'public');
        }

        return array_values(array_filter($storedPaths));
    }

    /* Resolve updated receipt photo paths */
    private function resolveUpdatedReceiptPhotoPaths(Request $request, Transaction $transaction): array
    {
        $currentPhotoPaths = $this->getTransactionPhotoPaths($transaction);
        $keptExistingPhotoPaths = $this->decodeExistingPhotoPaths(
            $request->input('existing_receipt_photo_paths')
        );

        $removedPhotoPaths = array_diff($currentPhotoPaths, $keptExistingPhotoPaths);

        foreach ($removedPhotoPaths as $removedPhotoPath) {
            Storage::disk('public')->delete($removedPhotoPath);
        }

        $newPhotoPaths = $this->storeReceiptPhotos($request);

        return array_values(array_filter([
            ...$keptExistingPhotoPaths,
            ...$newPhotoPaths,
        ]));
    }

    /* Authorize transaction */
    private function authorizeTransaction(Transaction $transaction): void
    {
        abort_unless(
            auth()->check() && auth()->id() === $transaction->user_id,
            403
        );
    }

    private function buildTransactionGroups(Collection $transactions): array
    {
        return $transactions
            ->groupBy(fn (Transaction $transaction) => $transaction->occurred_at->format('Y-m-d'))
            ->map(function (Collection $groupedTransactions, string $dateKey) {
                $groupDate = Carbon::createFromFormat('Y-m-d', $dateKey);

                return [
                    'dateKey' => $dateKey,
                    'dateLabel' => $groupDate->format('D, F d'),
                    'income' => (float) $groupedTransactions->where('type', 'income')->sum('amount'),
                    'expense' => (float) $groupedTransactions->where('type', 'expense')->sum('amount'),
                    'savings' => (float) $groupedTransactions->where('type', 'savings')->sum('amount'),
                    'transactions' => $groupedTransactions
                        ->map(function (Transaction $transaction) {
                            $categoryName = $transaction->category?->name
                                ?? Str::headline($transaction->type);

                            $accountName = $transaction->account?->name ?? '';

                            return [
                                'id' => $transaction->id,
                                'type' => $transaction->type,
                                'category' => $categoryName,
                                'account' => $accountName,
                                'amount' => (float) $transaction->amount,
                                'time' => $transaction->occurred_at->format('g:ia'),
                                'description' => $transaction->description ?? '',
                                'iconPath' => $this->resolveTransactionIconPath($categoryName, $accountName),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function resolveTransactionIconPath(string $categoryName, string $accountName = ''): string
    {
        $normalizedCategory = Str::of($categoryName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', '')
            ->replace('-', '')
            ->replace(' ', '')
            ->value();

        $normalizedAccount = Str::of($accountName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', '')
            ->replace('-', '')
            ->replace(' ', '')
            ->value();

        $defaultCategoryIcons = [
            'salary' => '/projectassets/icons/salary.svg',
            'allowance' => '/projectassets/icons/salary.svg',
            'bonus' => '/projectassets/icons/salary.svg',
            'pettycash' => '/projectassets/icons/salary.svg',
            'shopping' => '/projectassets/icons/shopping.svg',
            'apparel' => '/projectassets/icons/shopping.svg',
            'beauty' => '/projectassets/icons/shopping.svg',
            'gift' => '/projectassets/icons/shopping.svg',
            'transport' => '/projectassets/icons/transport.svg',
            'transportation' => '/projectassets/icons/transport.svg',
            'food' => '/projectassets/icons/food&drinks.svg',
            'fooddrinks' => '/projectassets/icons/food&drinks.svg',
            'foodanddrinks' => '/projectassets/icons/food&drinks.svg',
            'health' => '/projectassets/icons/health.svg',
            'education' => '/projectassets/icons/education.svg',
            'work' => '/projectassets/icons/work.svg',
            'pets' => '/projectassets/icons/pets.svg',
            'emergency' => '/projectassets/icons/savings.svg',
            'retirement' => '/projectassets/icons/savings.svg',
            'travel' => '/projectassets/icons/savings.svg',
            'investment' => '/projectassets/icons/savings.svg',
            'insurance' => '/projectassets/icons/savings.svg',
            'family' => '/projectassets/icons/savings.svg',
            'goal' => '/projectassets/icons/savings.svg',
            'house' => '/projectassets/icons/savings.svg',
            'gadget' => '/projectassets/icons/savings.svg',
            'car' => '/projectassets/icons/savings.svg',
            'others' => '/projectassets/icons/others.svg',
        ];

        if (array_key_exists($normalizedCategory, $defaultCategoryIcons)) {
            return $defaultCategoryIcons[$normalizedCategory];
        }

        if ($normalizedCategory !== '') {
            return '/projectassets/icons/others.svg';
        }

        $defaultAccountIcons = [
            'cash' => '/projectassets/icons/cash.svg',
            'wallet' => '/projectassets/icons/cash.svg',
            'pettycash' => '/projectassets/icons/cash.svg',
            'bank' => '/projectassets/icons/bank.svg',
            'unionbank' => '/projectassets/icons/bank.svg',
            'bpi' => '/projectassets/icons/bank.svg',
            'bdo' => '/projectassets/icons/bank.svg',
            'metrobank' => '/projectassets/icons/bank.svg',
            'landbank' => '/projectassets/icons/bank.svg',
            'card' => '/projectassets/icons/cards.svg',
            'cards' => '/projectassets/icons/cards.svg',
            'creditcard' => '/projectassets/icons/cards.svg',
            'debitcard' => '/projectassets/icons/cards.svg',
        ];

        if (array_key_exists($normalizedAccount, $defaultAccountIcons)) {
            return $defaultAccountIcons[$normalizedAccount];
        }

        return '/projectassets/icons/others.svg';
    }
}
