<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of all transactions (expenses and incomes).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $type = $request->query('type'); // 'expense', 'income', or null for all
        
        // Get expenses
        $expensesQuery = $user->expenses()
            ->select(
                'expenses.id',
                'expenses.amount',
                'expenses.description',
                'expenses.date',
                'categories.name as category_name',
                DB::raw("'expense' as type")
            )
            ->join('categories', 'expenses.category_id', '=', 'categories.id');
        
        if ($startDate) {
            $expensesQuery->where('expenses.date', '>=', $startDate);
        }
        
        if ($endDate) {
            $expensesQuery->where('expenses.date', '<=', $endDate);
        }
        
        // Get incomes
        $incomesQuery = $user->incomes()
            ->select(
                'incomes.id',
                'incomes.amount',
                'incomes.description',
                'incomes.date',
                'categories.name as category_name',
                DB::raw("'income' as type")
            )
            ->join('categories', 'incomes.category_id', '=', 'categories.id');
        
        if ($startDate) {
            $incomesQuery->where('incomes.date', '>=', $startDate);
        }
        
        if ($endDate) {
            $incomesQuery->where('incomes.date', '<=', $endDate);
        }
        
        // Combine and paginate results
        if ($type === 'expense') {
            $transactions = $expensesQuery->orderBy('date', 'desc')->paginate(15);
        } elseif ($type === 'income') {
            $transactions = $incomesQuery->orderBy('date', 'desc')->paginate(15);
        } else {
            $transactions = $expensesQuery->union($incomesQuery)->orderBy('date', 'desc')->paginate(15);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ], 200);
    }
}
