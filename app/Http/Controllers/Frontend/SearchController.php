<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function autocomplete(Request $request)
    {
        $q = $request->query('q');
        $results = Product::where('name', 'like', "%{$q}%")->limit(10)->get();
        return response()->json($results);
    }
}
