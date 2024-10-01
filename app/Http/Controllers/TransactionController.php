<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)->get();
        return $this->apiResponse($transactions, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'group_id' => 'nullable|uuid',
            'category_id' => 'nullable|uuid',
            'amount' => 'required|numeric',
            'type' => 'required|string',
            'name' => 'required|string',
            'date' => 'required|date'
        ]);

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'group_id' => $data['group_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'],
            'name' => $data['name'],
            'date' => $data['date']
        ]);

        return $this->apiResponse($transaction, 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id == $request->user()->id) {
            return $this->apiResponse($transaction, 200);
        }
        return $this->apiResponse(403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {

        $data = $request->validate([
            'group_id' => 'nullable|uuid',
            'category_id' => 'nullable|uuid',
            'amount' => 'nullable|numeric',
            'type' => 'nullable|string',
            'name' => 'nullable|string',
        ]);

        if ($transaction->user_id == $request->user()->id) {
            $transaction->update($data);
            return $this->apiResponse('Transaction updated successfully', 204);
        }
        return $this->apiResponse(403);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction, Request $request)
    {
        if ($transaction->user_id == $request->user()->id) {
            $transaction->delete();
            return $this->apiResponse('Transaction deleted successfully', 204);
        }
        return $this->apiResponse(403);
    }

    /**
     * Get transaction stats.
     */
    public function stats(Request $request)
    {
        $userId = $request->user()->id;
        $now = Carbon::now();

        // Prepare the start and end dates for the current month and the last six months
        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        $monthlyTotals = [];
        $categoryChangeAnalysis = [];

        // Fetch all transactions for the user for the last 6 months.
        $allTransactions = Transaction::where('user_id', $userId)
            ->whereBetween('date', [$now->copy()->subMonths(5)->startOfMonth(), $currentMonthEnd])
            ->orderBy('date', 'desc')
            ->get();

        // Separate transactions into current month
        $currentMonthTransactions = $allTransactions->whereBetween('date', [$currentMonthStart, $currentMonthEnd]);

        // Process the monthly totals for the last six months
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();

            // Filter transactions for the current month
            $transactionsThisMonth = $allTransactions->whereBetween('date', [$monthStart, $monthEnd]);

            // Group transactions by 'type'
            $totalsByType = $transactionsThisMonth->groupBy('type')->map(function ($transactions, $type) {
                return $transactions->sum('amount');
            });

            // Get totals for each type
            $income = $totalsByType->get('income', 0);
            $expense = $totalsByType->get('expense', 0);
            $investment = $totalsByType->get('investment', 0);
            $savings = $totalsByType->get('savings', 0);

            // Calculate total for the month
            $total = $income + $expense + $investment + $savings;

            // Add to the monthly totals array
            $monthlyTotals[] = [
                'name' => $monthStart->format('M'),
                'total' => $total,
                'income' => $income,
                'expense' => $expense,
                'investment' => $investment,
                'savings' => $savings,
            ];
        }

        // Calculate category totals for the current month
        $categoryTotalsCurrentMonth = $currentMonthTransactions->groupBy('type')->map(function ($transactions, $category) {
            return $transactions->sum('amount');
        });

        // Calculate category totals for the previous month
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $categoryTotalsPreviousMonth = $allTransactions->whereBetween('date', [$previousMonthStart, $previousMonthEnd])
            ->groupBy('type')->map(function ($transactions, $category) {
                return $transactions->sum('amount');
            });

        // Analyze changes in category totals
        $allCategories = ['income', 'expense', 'investment', 'savings'];
        foreach ($allCategories as $category) {
            $currentTotal = $categoryTotalsCurrentMonth->get($category, 0);
            $previousTotal = $categoryTotalsPreviousMonth->get($category, 0);

            // Calculate change percentage
            if ($currentTotal > 0) {
                if ($previousTotal > 0) {
                    $change = (($currentTotal - $previousTotal) / $previousTotal) * 100;
                } else {
                    $change = 100; // If previous total was 0
                }
            } else {
                if ($previousTotal > 0) {
                    $change = -100; // If current total is 0 but previous total was greater
                } else {
                    $change = 0; // No transactions in both months
                }
            }

            // Format change to a string with a % sign
            $changeFormatted = number_format($change, 2) . '%';

            // Store the results
            $categoryChangeAnalysis[$category] = [
                'total' => $currentTotal,
                'change' => $changeFormatted,
            ];
        }

        // Last 10 transactions for the current month
        $last10Transactions = $currentMonthTransactions->take(10);

        // Return the stats as a response
        return $this->apiResponse([
            'monthly_totals' => $monthlyTotals,
            'category_totals_current_month' => $categoryChangeAnalysis,
            'last_10_transactions' => $last10Transactions,
        ], 200);
    }
}
