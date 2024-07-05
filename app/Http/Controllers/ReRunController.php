<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ReRunController extends Controller
{
    public function rerun(Message $message) {
        $message->reRun();

        request()->session()->flash('flash.banner', 'ReRunning Back Shortly');

        return back();
    }
}
