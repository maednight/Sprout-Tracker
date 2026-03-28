<?php

namespace App\Http\Controllers;

use App\Models\SavingsTransfer;
use App\Services\SavingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsController extends Controller
{
    public function __construct(
        private SavingsService $savingsService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();

        return view('public.savings.savings', [
            'savingsPayload' => $this->savingsService->buildIndexPayload(
                $user->id,
                $request->query('scope'),
                $request->query('anchor')
            ),
        ]);
    }

    public function createTransfer(Request $request): View
    {
        return view('public.savings.savings-transfer-create', $this->savingsService->buildCreateTransferViewData(
            auth()->id(),
            $request->query('date'),
            [
                'transferTypeValue' => old('transfer_type', $request->query('type', 'savings_to_savings')),
                'transferDateValue' => old('transfer_date'),
                'transferAmountValue' => old('amount', ''),
                'transferCategoryValue' => old('source_category_id', ''),
                'transferDestinationCategoryValue' => old('destination_category_id', ''),
                'transferAccountValue' => old('account', ''),
                'transferDescriptionValue' => old('description', ''),
            ]
        ));
    }

    public function transfer(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $this->validateTransferRequest($request);

        $this->savingsService->createTransfer($user->id, $validated, $request);

        return redirect()
            ->route('savings_index')
            ->with(
                'savings_success',
                $validated['transfer_type'] === 'savings_to_savings'
                    ? 'Savings moved successfully.'
                    : 'Savings transferred to income successfully.'
            );
    }

    public function editTransfer(SavingsTransfer $savingsTransfer): View
    {
        $this->authorizeSavingsTransfer($savingsTransfer);

        return view('public.savings.savings-transfer-create', $this->savingsService->buildEditTransferViewData(
            $savingsTransfer,
            [
                'transferTypeValue' => old(
                    'transfer_type',
                    $savingsTransfer->destination_category_id
                        ? 'savings_to_savings'
                        : ($savingsTransfer->account_id ? 'savings_to_income' : 'savings_withdraw')
                ),
                'transferDateValue' => old('transfer_date'),
                'transferAmountValue' => old('amount'),
                'transferCategoryValue' => old('source_category_id'),
                'transferDestinationCategoryValue' => old('destination_category_id'),
                'transferAccountValue' => old('account'),
                'transferDescriptionValue' => old('description'),
            ]
        ));
    }

    public function updateTransfer(Request $request, SavingsTransfer $savingsTransfer): RedirectResponse
    {
        $this->authorizeSavingsTransfer($savingsTransfer);

        $validated = $this->validateTransferRequest($request);
        $user = auth()->user();

        $this->savingsService->updateTransfer($savingsTransfer, $user->id, $validated, $request);

        return redirect()
            ->route('savings_index')
            ->with('savings_success', 'Savings transfer updated successfully.');
    }

    public function destroyTransfer(SavingsTransfer $savingsTransfer): RedirectResponse
    {
        $this->authorizeSavingsTransfer($savingsTransfer);

        $this->savingsService->deleteTransfer($savingsTransfer);

        return redirect()
            ->route('savings_index')
            ->with('savings_success', 'Savings transfer deleted successfully.');
    }

    private function validateTransferRequest(Request $request): array
    {
        return $request->validate([
            'transfer_type' => ['required', 'in:savings_to_savings,savings_to_income,savings_withdraw'],
            'source_category_id' => ['required', 'integer'],
            'destination_category_id' => ['nullable', 'integer'],
            'amount' => ['required', 'string'],
            'transfer_date' => ['required', 'date_format:m/d/Y'],
            'account' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'receipt_photos' => ['nullable', 'array'],
            'receipt_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'receipt_photo_camera' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'existing_receipt_photo_paths' => ['nullable', 'string'],
        ]);
    }

    private function authorizeSavingsTransfer(SavingsTransfer $savingsTransfer): void
    {
        abort_unless(
            auth()->check() && auth()->id() === $savingsTransfer->user_id,
            403
        );
    }
}
