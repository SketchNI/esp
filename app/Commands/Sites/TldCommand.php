<?php

namespace App\Commands\Sites;

use LaravelZero\Framework\Commands\Command;

use function Termwind\render;
use function Termwind\style;

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
        style('info')->apply('text-blue-500');
        style('error')->apply('text-red-500');
        style('success')->apply('text-green-500');

        if ($this->argument('new-tld') === null) {
            $tld = file_get_contents(storage_path('tld'));
            render(sprintf(
                '<span><span class="info">INFO</span> Current TLD is <span class="success">.%s</span>.</span>',
                $tld
            ));

            return;
        }

        $tld = file_put_contents(
            storage_path('tld'),
            $this->argument('new-tld'),
            LOCK_EX
        );

        if ($tld) {
            render(sprintf(
                '<span><span class="info">INFO</span> TLD has been updated to <span class="success">.%s</span>.</span>',
                $this->argument('new-tld')));
        }
    }
}
