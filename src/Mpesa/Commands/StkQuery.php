<?php

namespace DrH\Mpesa\Commands;

use DrH\Mpesa\Repositories\MpesaRepository;
use Illuminate\Console\Command;

class StkQuery extends Command
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
        mpesaLogInfo($this->description);

        $results = $this->repository->queryStkStatus();

        /** @var array $results */
        if (count($results['successful'])) {
            $this->info("Logging successful queries");

            mpesaLogInfo('-- Successful queries -- ', $results['successful']);
        }

        if (count($results['errors'])) {
            $this->info("Logging failed queries");

            mpesaLogError('-- Failed queries -- ', $results['errors']);
        }

        if (empty($results['successful']) && empty($results['errors'])) {
            $this->comment("Nothing to query... all transactions seem to be ok.");
            mpesaLogInfo('Nothing to query... all transactions seem to be ok.');
        }
    }
}
