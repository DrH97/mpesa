<?php

namespace DrH\Mpesa\Commands;

use DrH\Mpesa\Repositories\MpesaRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StkStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:query_stk_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of all pending stk transactions';

    /**
     * Create a new command instance.
     *
     * @param MpesaRepository $repository
     */
    public function __construct(private MpesaRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Log::info($this->description);

        $results = $this->repository->queryStkStatus();

        /** @var array $results */
        Log::info($results['successful']);
        Log::error($results['errors']);
    }
}
