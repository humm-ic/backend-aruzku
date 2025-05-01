<?php

namespace App\Http\Controllers\API;

use App\Models\Expense;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller {
    /**
    * Display a listing of the expenses.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function index( Request $request ) {
        $user = $request->user();
        $startDate = $request->query( 'start_date' );
        $endDate = $request->query( 'end_date' );
        $categoryId = $request->query( 'category_id' );

        $query = $user->expenses()->with( 'category' );

        if ( $startDate ) {
            $query->where( 'date', '>=', $startDate );
        }

        if ( $endDate ) {
            $query->where( 'date', '<=', $endDate );
        }

        if ( $categoryId ) {
            $query->where( 'category_id', $categoryId );
        }

        $expenses = $query->orderBy( 'date', 'desc' )->paginate( 15 );

        return response()->json( [
            'status' => 'success',
            'data' => $expenses,
        ], 200 );
    }

    /**
    * Store a newly created expense in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store( Request $request ) {
        $validator = Validator::make( $request->all(), [
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422 );
        }

        // Cek apakah kategori yang dipilih memiliki tipe 'expense'
        $category = Category::find( $request->category_id );
        if ( $category->type !== 'expense' ) {
            return response()->json( [
                'status' => 'error',
                'message' => 'Invalid category type. Only expense categories are allowed.',
            ], 422 );
        }

        $user = $request->user();

        $expense = $user->expenses()->create( [
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ] );

        // Create notification for the expense
        Notification::create( [
            'user_id' => $user->id,
            'title' => 'Pengeluaran Baru',
            'message' => 'Anda telah menambahkan pengeluaran sebesar Rp ' . number_format( $request->amount, 0, ',', '.' ),
            'type' => 'expense',
        ] );

        return response()->json( [
            'status' => 'success',
            'message' => 'Expense created successfully',
            'data' => [
                'expense' => $expense->load( 'category' ),
            ],
        ], 201 );
    }

    /**
    * Display the specified expense.
    *
    * @param  int  $id
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function show( $id, Request $request ) {
        $user = $request->user();
        $expense = $user->expenses()->with( 'category' )->findOrFail( $id );

        return response()->json( [
            'status' => 'success',
            'data' => [
                'expense' => $expense,
            ],
        ], 200 );
    }

    /**
    * Update the specified expense in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */

    public function update( Request $request, $id ) {
        $validator = Validator::make( $request->all(), [
            'category_id' => 'sometimes|required|exists:categories,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'date' => 'sometimes|required|date',
        ] );

        if ( $validator->fails() ) {
            return response()->json( [
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422 );
        }

        $user = $request->user();
        $expense = $user->expenses()->findOrFail( $id );

        $expense->update( $request->only( [
            'category_id',
            'amount',
            'description',
            'date',
        ] ) );

        return response()->json( [
            'status' => 'success',
            'message' => 'Expense updated successfully',
            'data' => [
                'expense' => $expense->fresh()->load( 'category' ),
            ],
        ], 200 );
    }

    /**
    * Remove the specified expense from storage.
    *
    * @param  int  $id
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function destroy( $id, Request $request ) {
        $user = $request->user();
        $expense = $user->expenses()->findOrFail( $id );

        $expense->delete();

        return response()->json( [
            'status' => 'success',
            'message' => 'Expense deleted successfully',
        ], 200 );
    }
}
