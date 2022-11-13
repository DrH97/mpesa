<?php

namespace DrH\Mpesa\Commands;

use DrH\Mpesa\Repositories\MpesaRepository;
use Illuminate\Console\Command;

class TransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:transaction_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of all pending bulk transactions';

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

        $results = $this->repository->queryBulkStatus();

        /** @var array $results */
        if (count($results['status'])) {
            $this->info("Logging status queries");
            mpesaLogInfo('', $results['status']);
        } else {
            $this->comment("Nothing to query... all transactions seem to be ok.");
        }
    }
}
