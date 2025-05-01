<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        
        $query = Category::query();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $categories = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'categories' => $categories,
            ],
        ], 200);
    }
}
