<?php

namespace App\Services\Support;

use Illuminate\Support\Str;

/**
 * Resolves transaction labels, colors, and icons for presentation.
 */
class TransactionPresentationService
{
    public function defaultCategoryCatalog(): array
    {
        return [
            [
                'key' => 'transportation',
                'name' => 'Transportation',
                'color' => '#EB5757',
                'iconPath' => '/projectassets/icons/transport.svg',
            ],
            [
                'key' => 'food',
                'name' => 'Food',
                'color' => '#F2994A',
                'iconPath' => '/projectassets/icons/food&drinks.svg',
            ],
            [
                'key' => 'household',
                'name' => 'Household',
                'color' => '#9B51E0',
                'iconPath' => '/projectassets/icons/homebills.svg',
            ],
            [
                'key' => 'beauty',
                'name' => 'Beauty',
                'color' => '#FF6FAE',
                'iconPath' => '/projectassets/icons/selfcare.svg',
            ],
            [
                'key' => 'health',
                'name' => 'Health',
                'color' => '#E74C3C',
                'iconPath' => '/projectassets/icons/health.svg',
            ],
            [
                'key' => 'others',
                'name' => 'Others',
                'color' => '#F2C94C',
                'iconPath' => '/projectassets/icons/others.svg',
            ],
        ];
    }

    public function resolveCategoryKey(string $categoryName, string $transactionType = 'expense'): string
    {
        if ($transactionType !== 'expense') {
            return Str::of($categoryName)
                ->lower()
                ->trim()
                ->replace('&', 'and')
                ->replace('/', ' ')
                ->replace('-', ' ')
                ->squish()
                ->replace(' ', '_')
                ->value();
        }

        $normalizedName = Str::of($categoryName)
            ->lower()
            ->trim()
            ->replace('&', 'and')
            ->replace('/', ' ')
            ->replace('-', ' ')
            ->squish()
            ->value();

        return match ($normalizedName) {
            'beauty', 'self care', 'selfcare' => 'beauty',
            'transport', 'transportation', 'fare', 'gas', 'car' => 'transportation',
            'food', 'food and drinks', 'food drinks', 'groceries', 'dining' => 'food',
            'household', 'home bills', 'homebills', 'utilities', 'rent' => 'household',
            'health', 'medical', 'medicine' => 'health',
            'shopping', 'apparel', 'gift', 'education', 'school', 'pets', 'pet care', 'others', 'other', '' => 'others',
            default => 'others',
        };
    }

    public function resolveCategoryColor(string $categoryKey): string
    {
        return collect($this->defaultCategoryCatalog())
            ->firstWhere('key', $categoryKey)['color'] ?? '#7d8597';
    }

    public function resolveTransactionIconPath(
        string $categoryName,
        string $accountName = '',
        string $transactionType = 'expense'
    ): string {
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
            'shopping' => '/projectassets/icons/others.svg',
            'apparel' => '/projectassets/icons/others.svg',
            'beauty' => '/projectassets/icons/selfcare.svg',
            'gift' => '/projectassets/icons/others.svg',
            'transport' => '/projectassets/icons/transport.svg',
            'transportation' => '/projectassets/icons/transport.svg',
            'food' => '/projectassets/icons/food&drinks.svg',
            'fooddrinks' => '/projectassets/icons/food&drinks.svg',
            'foodanddrinks' => '/projectassets/icons/food&drinks.svg',
            'health' => '/projectassets/icons/health.svg',
            'education' => '/projectassets/icons/others.svg',
            'work' => '/projectassets/icons/others.svg',
            'pets' => '/projectassets/icons/others.svg',
            'household' => '/projectassets/icons/homebills.svg',
            'homebills' => '/projectassets/icons/homebills.svg',
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

    public function resolveTransactionIconColor(string $transactionType): string
    {
        return match ($transactionType) {
            'income' => 'green',
            'savings' => 'blue',
            default => 'coral',
        };
    }
}
