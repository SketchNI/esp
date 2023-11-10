<?php

namespace App\Commands\Sites;

use LaravelZero\Framework\Commands\Command;

class TldCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'tld {new-tld? : Set a new TLD.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Set or view the tld.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $esp = sprintf("%s/.esp", getenv('HOME'));
        $tld_file = sprintf("%s/tld", $esp);

        if ($this->argument('new-tld') === null) {
            $tld = file_get_contents($tld_file, $esp);
            $this->info('Current TLD is ".%s"', $tld);

            return;
        }

        $tld = file_put_contents($tld_file, $this->argument('new-tld'), LOCK_EX);

        if ($tld) {
            $this->info('TLD has been updated to ".%s"', $tld);
        }
    }
}
