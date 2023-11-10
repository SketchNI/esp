<?php

namespace App\Commands;

use App\Services\Shell;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class MakeCertificateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:certificate { domain : The domain name for the certificate }';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new SSL certificate keypair.';

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $esp_data = sprintf("%s/.esp/easyrsa", getenv('HOME'));
        $esp_sh = sprintf("/%s/easyrsa", $esp_data);
        $domain = strip_newlines($this->argument('domain'));

        chdir($esp_data);

        $args = [
            sprintf("--vars=%s/pki/vars", $esp_data),
            '--batch',
            '--days=3650',
            sprintf("--req-cn=%s", $domain)
        ];

        $op = Shell::run(sprintf('EASYRSA_EXTRA_EXTS="subjectAltName=DNS:*.%s" ./easyrsa', $domain),
            [...$args, 'gen-req', $domain, 'nopass']);
        Shell::run('./easyrsa', [...$args, 'sign-req', 'server', $domain]);

        $this->info('Certificates have been created.');
        return File::exists(sprintf("%s/private/%s.key", $esp_data, $domain));
    }
}
