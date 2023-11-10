<?php

namespace App\Commands\Sites;

use App\Site;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\table;

class ListCommand extends Command
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
        $sites = Site::all(['name', 'url', 'secure', 'path', 'created_at']);

        if ($sites->count() === 0) {
            $this->error('There are no registered sites.');
            return;
        }

        table(['Name', 'URL', 'Secure', 'Path', 'Created'], $sites->toArray());

    }
}
