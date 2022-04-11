<?php

namespace DrH\Mpesa\Commands;

use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Library\C2bRegister;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;
use function config;

class C2bRegisterUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:register_c2b_urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register c2b validation and confirmation URLs';

    /**
     * @var C2bRegister
     */
    private $c2bRegister;

    /**
     * Create a new command instance.
     *
     * @param C2bRegister $c2bRegister
     */
    public function __construct(C2bRegister $c2bRegister)
    {
        parent::__construct();
        $this->c2bRegister = $c2bRegister;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws GuzzleException
     * @throws MpesaException
     */
    #[NoReturn]
    public function handle(): void
    {
        mpesaLogInfo($this->description);

        $response = $this->c2bRegister
            ->shortcode($this->askShortcode())
            ->onConfirmation($this->askConfirmationUrl())
            ->onValidation($this->askValidationUrl())
            ->submit();

        $this->info("Logging response");

        mpesaLogInfo(array($response));
    }

    private function askShortcode(): int
    {
        return $this->ask('What is your shortcode?', config('drh.mpesa.c2b.short_code'));
    }

    private function askConfirmationUrl(): string
    {
        return $this->ask('Confirmation Url', config('drh.mpesa.c2b.confirmation_url'));
    }

    private function askValidationUrl(): string
    {
        return $this->ask('Validation Url', config('drh.mpesa.c2b.validation_url'));
    }
}
