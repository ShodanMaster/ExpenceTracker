<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReccuringTransactionController extends Controller
{
    public function index(){
        return view('reccuringTrancasction');
    }
}
