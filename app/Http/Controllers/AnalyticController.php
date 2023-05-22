<?php

namespace App\Http\Controllers;

use App\Models\AnalyticMenus;
use Illuminate\Http\Request;

class AnalyticController extends Controller
{
    public function getMenus(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['items' => AnalyticMenus::all()->toArray()]);
    }
}
