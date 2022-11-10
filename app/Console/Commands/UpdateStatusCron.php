<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateStatusCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateStatus:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For Update All Status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        info("Cron Job running at ". now());

        $response = Http::get('http://localhost:8000/api/status');
        return 0;
    }
}
