<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class CNABItau400Controller extends Controller
{
    public function index()
    {
        return view('cnab-itau.index');
    }
}
