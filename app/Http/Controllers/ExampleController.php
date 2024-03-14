<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
    public function charts()
    {
        return inertia('Examples/Charts');
    }
}
