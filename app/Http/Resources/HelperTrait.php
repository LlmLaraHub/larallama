<?php

namespace App\Http\Resources;

trait HelperTrait
{
    public function getRecurring()
    {
        $recurring = $this->recurring;
        if ($recurring) {
            $recurring = str($recurring->name)->headline()->toString();
        }

        return $recurring;
    }

    public function getLastRun()
    {
        $lastRun = $this->last_run;
        if ($lastRun) {
            $lastRun = $lastRun->diffForHumans();
        }

        return $lastRun;
    }
}
