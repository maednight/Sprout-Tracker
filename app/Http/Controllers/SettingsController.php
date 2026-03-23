<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user()->fresh();

        $transactions = $user->transactions()
            ->orderBy('occurred_at', 'desc')
            ->get();

        $transactionDays = $transactions
            ->filter(fn ($transaction) => $transaction->occurred_at !== null)
            ->toBase()
            ->map(fn ($transaction) => $transaction->occurred_at->copy()->startOfDay()->toDateString())
            ->unique()
            ->values();

        $stats = [
            'current_streak' => $this->calculateCurrentStreak($transactionDays),
            'total_days' => $transactionDays->count(),
            'transactions' => $transactions->count(),
        ];

        return view('settings', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function updateName(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->name = $validated['name'];
        $user->save();

        $freshUser = $user->fresh();
        Auth::login($freshUser);
        $request->session()->regenerate();

        return redirect()
            ->route('settings.index')
            ->with('settings_success', 'Name updated successfully.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $photoPath = $validated['profile_photo']->store('profile-photos', 'public');

        $user->profile_photo_path = $photoPath;
        $user->save();

        $freshUser = $user->fresh();
        Auth::login($freshUser);
        $request->session()->regenerate();

        return redirect()
            ->route('settings.index')
            ->with('settings_success', 'Profile photo updated successfully.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'current_password_for_email' => ['required'],
        ], [
            'current_password_for_email.required' => 'Please enter your current password to change your email.',
        ]);

        if (! Hash::check($validated['current_password_for_email'], $user->password)) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'current_password_for_email' => 'Current password is incorrect.',
                ])
                ->withInput();
        }

        $user->email = $validated['email'];
        $user->save();

        $freshUser = $user->fresh();
        Auth::login($freshUser);
        $request->session()->regenerate();

        return redirect()
            ->route('settings.index')
            ->with('settings_success', 'Email updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required' => 'Please enter a new password.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'current_password' => 'Current password is incorrect.',
                ])
                ->withInput();
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        $freshUser = $user->fresh();
        Auth::login($freshUser);
        $request->session()->regenerate();

        return redirect()
            ->route('settings.index')
            ->with('settings_success', 'Password updated successfully.');
    }

    private function calculateCurrentStreak(Collection $transactionDays): int
    {
        if ($transactionDays->isEmpty()) {
            return 0;
        }

        $daysLookup = $transactionDays->flip();

        $today = Carbon::today();
        $currentDate = $today->copy();

        if (! $daysLookup->has($today->toDateString())) {
            $yesterday = $today->copy()->subDay();

            if (! $daysLookup->has($yesterday->toDateString())) {
                return 0;
            }

            $currentDate = $yesterday;
        }

        $streak = 0;

        while ($daysLookup->has($currentDate->toDateString())) {
            $streak++;
            $currentDate->subDay();
        }

        return $streak;
    }
}
