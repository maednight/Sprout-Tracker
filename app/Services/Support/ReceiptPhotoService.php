<?php

namespace App\Services\Support;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptPhotoService
{
    public function getTransactionPhotoPaths(Transaction $transaction): array
    {
        $photoPaths = is_array($transaction->receipt_photo_paths)
            ? $transaction->receipt_photo_paths
            : [];

        if (empty($photoPaths) && $transaction->receipt_photo_path) {
            $photoPaths = [$transaction->receipt_photo_path];
        }

        return array_values(array_filter($photoPaths));
    }

    public function decodeExistingPhotoPaths(?string $rawValue): array
    {
        if (! $rawValue) {
            return [];
        }

        $decodedValue = json_decode($rawValue, true);

        if (! is_array($decodedValue)) {
            return [];
        }

        return array_values(array_filter($decodedValue));
    }

    public function storeReceiptPhotos(Request $request): array
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

    public function resolveUpdatedReceiptPhotoPaths(Request $request, ?Transaction $transaction = null): array
    {
        $currentPhotoPaths = $transaction ? $this->getTransactionPhotoPaths($transaction) : [];
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

    public function deleteTransactionPhotos(Transaction $transaction): void
    {
        foreach ($this->getTransactionPhotoPaths($transaction) as $photoPath) {
            Storage::disk('public')->delete($photoPath);
        }
    }

    public function resolveReceiptPhotoUrls(Transaction $transaction): array
    {
        return collect($this->getTransactionPhotoPaths($transaction))
            ->map(function (string $photoPath) {
                if (
                    Str::startsWith($photoPath, ['http://', 'https://', '/storage/', 'storage/'])
                ) {
                    return Str::startsWith($photoPath, 'storage/')
                        ? asset($photoPath)
                        : $photoPath;
                }

                return Storage::url($photoPath);
            })
            ->values()
            ->all();
    }
}
