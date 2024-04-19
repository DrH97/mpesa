<?php

namespace DrH\Mpesa\Commands;

use DrH\Mpesa\Repositories\MpesaRepository;
use Illuminate\Console\Command;

class B2bTransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:b2b_transaction_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of all pending b2b transactions';

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
    public function handle(): void
    {
        mpesaLogInfo($this->description);

        $results = $this->repository->queryB2bStatus();

        if (count($results)) {
            $this->info("Logging status queries");
            mpesaLogInfo('', $results);
        } else {
            $this->comment("Nothing to query... all transactions seem to be ok.");
        }
    }
}
