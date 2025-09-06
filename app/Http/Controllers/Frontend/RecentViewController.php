<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecentViewController extends Controller
{
    public function add(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
