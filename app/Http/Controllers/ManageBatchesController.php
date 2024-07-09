<?php

namespace App\Http\Controllers;

use App\Http\Resources\BatchResource;
use Illuminate\Support\Facades\Artisan;
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

        Artisan::call('queue:prune-batches');

        request()->session()->flash('flash.banner', 'Batch Cancelled');

        return back();
    }

    public function cancelAll()
    {
        foreach (DB::table('job_batches')
            ->whereNull('finished_at')
            ->latest('created_at')
            ->limit(500)
            ->get() as $batch) {
            $batch = Bus::findBatch($batch->id);
            $batch->cancel();
        }
        Artisan::call('horizon:clear-all-horizon-queues');
        Artisan::call('queue:prune-batches');

        request()->session()->flash('flash.banner', 'Batch Cleared');

        return back();
    }
}
