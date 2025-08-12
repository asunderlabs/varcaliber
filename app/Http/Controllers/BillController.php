<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request, string $localDate = null)
    {
        return redirect()->route('billing.index');
    }
}
