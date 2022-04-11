<?php

namespace DrH\Mpesa\Commands;

use DrH\Mpesa\Repositories\Mpesa;
use Illuminate\Console\Command;

/**
 * Class StkStatus
 * @package DrH\Mpesa\Commands
 */
class StkStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:query_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of all pending transactions';
    /**
     * @var Mpesa
     */
    private $mpesa;

    /**
     * Create a new command instance.
     *
     * @param Mpesa $registerUrl
     */
    public function __construct(Mpesa $registerUrl)
    {
        parent::__construct();
        $this->mpesa = $registerUrl;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        mpesaLogInfo($this->description);

        $results = $this->mpesa->queryStkStatus();

        /** @var array $results */
        if (count($results['successful'])) {
            $this->info("Logging successful queries");

            mpesaLogInfo($results['successful']);
        }

        if (count($results['errors'])) {
            $this->info("Logging failed queries");

            mpesaLogError($results['errors']);
        }

        if (empty($results['successful']) && empty($results['errors'])) {
            $this->comment("Nothing to query... all transactions seem to be ok.");
        }
    }
}
