<?php

namespace App\Commands\Sites;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class CreateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:create';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new site.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $config = $this->getConfigForNewSite();

        if (!$config['ssl']) {
            $this->prepareNginxConfigurationWithoutSSL($config['domain']);
        } else {
            $this->prepareNginxConfigurationWithSSL($config['domain']);
        }
    }

    private function getConfigForNewSite(): array
    {
        $framework_array = ['Laravel'];
        $db_array = ['MariaDB', 'PostgreSQL', 'MySQL', 'SQLite'];

        $tld = file_get_contents('storage/tld');

        $config['framework'] = $this->choice('Which Framework would you like to use?', $framework_array);
        $config['database'] = $this->choice('Which database would you like to use?', $db_array);
        $config['ssl'] = $this->choice('Should this site use SSL?', ['No', 'Yes']);
        $config['name'] = $this->ask('What would you like to call this project?');
        $config['site'] = mb_strtolower($config['name']);
        $config['domain'] = sprintf('%s.%s', $config['site'], $tld);
        $config['url'] = sprintf('%s://%s', $config['ssl'] ? 'https' : 'http', $config['domain']);

        if (config('app.env') !== 'production') {
            $this->table(array_keys($config), [array_values($config)], 'box-double');
        }

        return $config;
    }

    /**
     * @param $domain
     *
     * @return void
     */
    public function prepareNginxConfigurationWithoutSSL($domain): void
    {
        $__name = posix_getpwuid(posix_geteuid())['name'];

        File::copy('storage/stubs/nginx-non-ssl.conf.stub', 'storage/tmp/nginx');
        File::replaceInFile('ESP_SERVER_NAME', $domain, 'storage/tmp/nginx');
        File::replaceInFile('ESP_DIR', '', 'storage/tmp/nginx');
        File::replaceInFile('ESP_USER', $__name, 'storage/tmp/nginx');
    }

    /**
     * @param $domain
     *
     * @return void
     */
    public function prepareNginxConfigurationWithSSL($domain): void
    {
        $__name = posix_getpwuid(posix_geteuid())['name'];

        File::copy('storage/stubs/nginx-ssl.conf.stub', 'storage/tmp/nginx');
        File::replaceInFile('ESP_SERVER_NAME', $domain, 'storage/tmp/nginx');
        File::replaceInFile('ESP_DIR', '', 'storage/tmp/nginx');
        File::replaceInFile('ESP_SSL_CERT', '', 'storage/tmp/nginx');
        File::replaceInFile('ESP_SSL_KEY', '', 'storage/tmp/nginx');
        File::replaceInFile('ESP_USER', $__name, 'storage/tmp/nginx');
    }
}
