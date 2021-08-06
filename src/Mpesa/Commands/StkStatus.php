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
        $results = $this->mpesa->queryStkStatus();
//        TODO: Handle this in a better/smoother manner
        dd($results);
    }
}
