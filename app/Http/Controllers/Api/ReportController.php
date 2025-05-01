<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get monthly financial report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monthlyReport(Request $request)
    {
        $user = $request->user();
        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('m'));
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Get total income
        $totalIncome = $user->incomes()
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        
        // Get total expense
        $totalExpense = $user->expenses()
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        
        // Get income by category
        $incomeByCategory = $user->incomes()
            ->select('categories.name', DB::raw('SUM(incomes.amount) as total'))
            ->join('categories', 'incomes.category_id', '=', 'categories.id')
            ->whereBetween('incomes.date', [$startDate, $endDate])
            ->groupBy('categories.name')
            ->get();
        
        // Get expense by category
        $expenseByCategory = $user->expenses()
            ->select('categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->whereBetween('expenses.date', [$startDate, $endDate])
            ->groupBy('categories.name')
            ->get();
        
        // Get daily expense
        $dailyExpense = $user->expenses()
            ->select(DB::raw('DATE(date) as day'), DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('day')
            ->get();
        
        // Get daily income
        $dailyIncome = $user->incomes()
            ->select(DB::raw('DATE(date) as day'), DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('day')
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => [
                    'year' => (int) $year,
                    'month' => (int) $month,
                    'month_name' => $startDate->format('F'),
                ],
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'balance' => $totalIncome - $totalExpense,
                ],
                'income_by_category' => $incomeByCategory,
                'expense_by_category' => $expenseByCategory,
                'daily_expense' => $dailyExpense,
                'daily_income' => $dailyIncome,
            ],
        ], 200);
    }

    /**
     * Generate PDF report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(Request $request)
    {
        $user = $request->user();
        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('m'));
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Get incomes
        $incomes = $user->incomes()
            ->with('category')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
        
        // Get expenses
        $expenses = $user->expenses()
            ->with('category')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
        
        // Calculate totals
        $totalIncome = $incomes->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $balance = $totalIncome - $totalExpense;
        
        // Group by category
        $incomeByCategory = $incomes->groupBy('category.name')
            ->map(function ($items) {
                return $items->sum('amount');
            });
        
        $expenseByCategory = $expenses->groupBy('category.name')
            ->map(function ($items) {
                return $items->sum('amount');
            });
        
        $data = [
            'user' => $user,
            'period' => $startDate->format('F Y'),
            'incomes' => $incomes,
            'expenses' => $expenses,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'generatedAt' => now()->format('d F Y H:i:s'),
        ];
        
        $pdf = PDF::loadView('reports.monthly', $data);
        
        return $pdf->download('laporan-keuangan-' . $startDate->format('F-Y') . '.pdf');
    }

    /**
     * Get financial analysis data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function analysis(Request $request)
    {
        $user = $request->user();
        $period = $request->query('period', 'month'); // month, year, all
        
        $now = Carbon::now();
        
        if ($period === 'month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($period === 'year') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        } else {
            $startDate = Carbon::createFromTimestamp(0);
            $endDate = $now->copy()->endOfDay();
        }
        
        // Get expense by category
        $expenseByCategory = $user->expenses()
            ->select('categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->whereBetween('expenses.date', [$startDate, $endDate])
            ->groupBy('categories.name')
            ->get();
        
        // Get income by category
        $incomeByCategory = $user->incomes()
            ->select('categories.name', DB::raw('SUM(incomes.amount) as total'))
            ->join('categories', 'incomes.category_id', '=', 'categories.id')
            ->whereBetween('incomes.date', [$startDate, $endDate])
            ->groupBy('categories.name')
            ->get();
        
        // Get monthly expense trend
        $monthlyExpenseTrend = $user->expenses()
            ->select(DB::raw('YEAR(date) as year'), DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'total' => $item->total,
                ];
            });
        
        // Get monthly income trend
        $monthlyIncomeTrend = $user->incomes()
            ->select(DB::raw('YEAR(date) as year'), DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'total' => $item->total,
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => $period,
                'expense_by_category' => $expenseByCategory,
                'income_by_category' => $incomeByCategory,
                'monthly_expense_trend' => $monthlyExpenseTrend,
                'monthly_income_trend' => $monthlyIncomeTrend,
            ],
        ], 200);
    }
}
