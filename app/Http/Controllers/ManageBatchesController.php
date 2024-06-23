<?php

namespace App\Http\Controllers;

use App\Http\Resources\BatchResource;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class ManageBatchesController extends Controller
{
    public function index()
    {
        return inertia('Batches/Index', [
            'batches' => BatchResource::collection(DB::table('job_batches')
                ->latest('created_at')
                ->paginate(50)),
        ]);
    }

    public function cancel(string $batchId)
    {
        $batch = Bus::findBatch($batchId);
        $batch->cancel();

        request()->session()->flash('flash.banner', 'Batch Cancelled');

        return back();
    }
}
