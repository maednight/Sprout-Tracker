<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('public.dashboard'); // Vue mounts here
    }

    public function data(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $month = $request->query('month'); // YYYY-MM
        $base = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();
        $start = $base->copy()->startOfMonth();
        $end = $base->copy()->endOfMonth();

        $tx = Transaction::query()
            ->with(['category:id,name', 'account:id,name'])
            ->where('user_id', $user->id)
            ->whereBetween('occurred_at', [$start, $end])
            ->orderBy('occurred_at', 'desc')
            ->get();

        // Totals per type
        $income = (float) $tx->where('type', 'income')->sum('amount');
        $expense = (float) $tx->where('type', 'expense')->sum('amount');
        $savings = (float) $tx->where('type', 'savings')->sum('amount');
        $balance = $income - $expense - $savings;

        // Totals per day for calendar
        $byDay = [];
        foreach ($tx as $t) {
            $d = $t->occurred_at->format('Y-m-d');
            $byDay[$d] ??= ['income' => 0, 'expense' => 0, 'savings' => 0];
            $byDay[$d][$t->type] += (float) $t->amount;
        }

        return response()->json([
            'month' => $start->format('Y-m'),
            'summary' => [
                'income' => $income,
                'expense' => $expense,
                'savings' => $savings,
                'balance' => $balance,
            ],
            'calendar' => $byDay,
            'recent' => $tx->take(20)->values(),
        ]);
    }
}