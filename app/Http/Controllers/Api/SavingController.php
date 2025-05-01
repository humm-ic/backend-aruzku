<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Saving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SavingController extends Controller
{
    /**
     * Display a listing of the savings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        
        $query = $user->savings();
        
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        
        $savings = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $savings,
        ], 200);
    }

    /**
     * Store a newly created saving in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        
        $saving = $user->savings()->create([
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        // Create notification for the saving
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Tabungan Baru',
            'message' => 'Anda telah menambahkan tabungan sebesar Rp ' . number_format($request->amount, 0, ',', '.'),
            'type' => 'savings',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Saving created successfully',
            'data' => [
                'saving' => $saving,
            ],
        ], 201);
    }

    /**
     * Display the specified saving.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        $saving = $user->savings()->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'saving' => $saving,
            ],
        ], 200);
    }

    /**
     * Update the specified saving in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'date' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $saving = $user->savings()->findOrFail($id);
        
        $saving->update($request->only([
            'amount',
            'description',
            'date',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Saving updated successfully',
            'data' => [
                'saving' => $saving->fresh(),
            ],
        ], 200);
    }

    /**
     * Remove the specified saving from storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $saving = $user->savings()->findOrFail($id);
        
        $saving->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Saving deleted successfully',
        ], 200);
    }
}
