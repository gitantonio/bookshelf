<?php

namespace App\Listeners;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;

class HealthCheck
{
    public function handle(DiagnosingHealth $event): void
    {
        DB::select('SELECT 1');
    }
}
