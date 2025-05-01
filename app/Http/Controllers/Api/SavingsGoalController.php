<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SavingsGoalController extends Controller
{
    /**
     * Display a listing of the savings goals.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');
        
        $query = $user->savingsGoals();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $savingsGoals = $query->orderBy('target_date', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'savings_goals' => $savingsGoals,
            ],
        ], 200);
    }

    /**
     * Store a newly created savings goal in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'sometimes|numeric|min:0',
            'target_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        
        $savingsGoal = $user->savingsGoals()->create([
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => $request->current_amount ?? 0,
            'target_date' => $request->target_date,
            'status' => 'in_progress',
        ]);

        // Create notification for the savings goal
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Target Tabungan Baru',
            'message' => 'Anda telah membuat target tabungan baru: ' . $request->name,
            'type' => 'goal',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Savings goal created successfully',
            'data' => [
                'savings_goal' => $savingsGoal,
            ],
        ], 201);
    }

    /**
     * Display the specified savings goal.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        $savingsGoal = $user->savingsGoals()->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'savings_goal' => $savingsGoal,
            ],
        ], 200);
    }

    /**
     * Update the specified savings goal in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'target_amount' => 'sometimes|required|numeric|min:0',
            'current_amount' => 'sometimes|required|numeric|min:0',
            'target_date' => 'sometimes|required|date|after:today',
            'status' => 'sometimes|required|in:in_progress,completed,failed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $savingsGoal = $user->savingsGoals()->findOrFail($id);
        
        $savingsGoal->update($request->only([
            'name',
            'target_amount',
            'current_amount',
            'target_date',
            'status',
        ]));

        // Check if goal is completed
        if ($savingsGoal->current_amount >= $savingsGoal->target_amount && $savingsGoal->status !== 'completed') {
            $savingsGoal->status = 'completed';
            $savingsGoal->save();
            
            // Create notification for completed goal
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Target Tabungan Tercapai',
                'message' => 'Selamat! Anda telah mencapai target tabungan: ' . $savingsGoal->name,
                'type' => 'goal',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Savings goal updated successfully',
            'data' => [
                'savings_goal' => $savingsGoal->fresh(),
            ],
        ], 200);
    }

    /**
     * Remove the specified savings goal from storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $savingsGoal = $user->savingsGoals()->findOrFail($id);
        
        $savingsGoal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Savings goal deleted successfully',
        ], 200);
    }
}
