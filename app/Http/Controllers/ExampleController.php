<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    
    public function charts() {
        return inertia("Examples/Charts");
    }
}
