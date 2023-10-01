<?php

namespace App\Commands\Sites;

use App\Site;
use LaravelZero\Framework\Commands\Command;

use function Termwind\render;
use function Termwind\style;
use function Termwind\terminal;

class SitesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:list';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all registered sites.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sites = Site::all();

        if ($sites->count() === 0) {
            style('error')->apply('text-red-500');
            render('<span><span class="error">ERROR</span> There are no registered sites.</span>');

            return;
        }

        terminal()->clear();
        $this->table(['Name', 'URL', 'Secure', 'Path', 'Created'], $sites->toArray());
    }
}
