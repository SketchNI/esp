<?php

namespace App\Commands\Sites;

use App\Site;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class RemoveCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:remove { name : The name of site to remove. }';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove a site from ESP, including its nginx configuration and SSL certificates.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $site = Site::whereName($this->argument('name'))->first();

        $_esp_dir = sprintf("%s/.esp", getenv('HOME'));
        $_easyrsa = sprintf("%s/easyrsa", $_esp_dir);
        $tld = strip_newlines(file_get_contents(sprintf('%s/tld', $_esp_dir)));

        $cert_file = sprintf("%s/pki/issued/%s.%s.crt", $_easyrsa, strip_newlines($site->name), $tld);
        $key_file = sprintf("%s/pki/private/%s.%s.pem", $_easyrsa, strip_newlines($site->name), $tld);
        $nginx_file = sprintf("%s/Nginx/%s.conf", $_esp_dir, strip_newlines($site->name));


        File::delete([$cert_file, $key_file, $nginx_file]);
        $site->delete();
        $this->info('Site has been removed.');
    }
}
