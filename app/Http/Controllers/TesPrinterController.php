<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TesPrinterController extends Controller
{
    public function index()
    {
        return view('admin.tes_print');
    }
}
