<?php

namespace App\Http\Controllers\API;

use App\Models\Income;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IncomeController extends Controller {
    /**
    * Display a listing of the incomes.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function index( Request $request ) {
        $user = $request->user();
        $startDate = $request->query( 'start_date' );
        $endDate = $request->query( 'end_date' );
        $categoryId = $request->query( 'category_id' );

        $query = $user->incomes()->with( 'category' );

        if ( $startDate ) {
            $query->where( 'date', '>=', $startDate );
        }

        if ( $endDate ) {
            $query->where( 'date', '<=', $endDate );
        }

        if ( $categoryId ) {
            $query->where( 'category_id', $categoryId );
        }

        $incomes = $query->orderBy( 'date', 'desc' )->paginate( 15 );

        return response()->json( [
            'status' => 'success',
            'data' => $incomes,
        ], 200 );
    }

    /**
    * Store a newly created income in storage.
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

        // Cek apakah kategori yang dipilih memiliki tipe 'income'
        $category = Category::find( $request->category_id );
        if ( $category->type !== 'income' ) {
            return response()->json( [
                'status' => 'error',
                'message' => 'Invalid category type. Only income categories are allowed.',
            ], 422 );
        }

        $user = $request->user();

        $income = $user->incomes()->create( [
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ] );

        // Create notification for the income
        Notification::create( [
            'user_id' => $user->id,
            'title' => 'Pemasukan Baru',
            'message' => 'Anda telah menambahkan pemasukan sebesar Rp ' . number_format( $request->amount, 0, ',', '.' ),
            'type' => 'income',
        ] );

        return response()->json( [
            'status' => 'success',
            'message' => 'Income created successfully',
            'data' => [
                'income' => $income->load( 'category' ),
            ],
        ], 201 );
    }

    /**
    * Display the specified income.
    *
    * @param  int  $id
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function show( $id, Request $request ) {
        $user = $request->user();
        $income = $user->incomes()->with( 'category' )->findOrFail( $id );

        return response()->json( [
            'status' => 'success',
            'data' => [
                'income' => $income,
            ],
        ], 200 );
    }

    /**
    * Update the specified income in storage.
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
        $income = $user->incomes()->findOrFail( $id );

        $income->update( $request->only( [
            'category_id',
            'amount',
            'description',
            'date',
        ] ) );

        return response()->json( [
            'status' => 'success',
            'message' => 'Income updated successfully',
            'data' => [
                'income' => $income->fresh()->load( 'category' ),
            ],
        ], 200 );
    }

    /**
    * Remove the specified income from storage.
    *
    * @param  int  $id
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function destroy( $id, Request $request ) {
        $user = $request->user();
        $income = $user->incomes()->findOrFail( $id );

        $income->delete();

        return response()->json( [
            'status' => 'success',
            'message' => 'Income deleted successfully',
        ], 200 );
    }
}
