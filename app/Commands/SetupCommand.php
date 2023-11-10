<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\Shell;

class SetupCommand extends Command
{
    protected $signature = 'setup { --f|force : Forcibly set up ESP, overwriting all previous configuration }
                                   { --b|bad : Force bad environment. Mainly used for testing. }';

    protected $description = 'Sets up ESP';

    public function handle(): void
    {
        if (!$this->detectOperatingSystemIsWsl2($this->option('bad'))) {
            $this->error('ESP is designed to run in a WSL2 environment.');
            return;
        } else {
            $this->info('Environment is WSL2');
        }

        $__env_home = getenv('HOME');
        $__data_path = sprintf("%s/.esp", $__env_home);
        $__name = posix_getpwuid(posix_geteuid())['name'];

        $this->createDirectories($__data_path);
        $this->installDependencies();
        $this->setupNginx($__name, $__data_path);
        $this->setupPhp($__name, $__data_path);
        $this->restartServices();
        $this->setupEasyRsa($__data_path, $this->option('force'));
        file_put_contents($__data_path.'/tld', 'test');
    }

    private function detectOperatingSystemIsWsl2(bool $bad): bool
    {
        return preg_match(
            '/WSL2/i',
            $bad ? '5.15.90.1' : php_uname('r')
        );
    }

    /**
     * @param  string  $__data_path
     *
     * @return void
     */
    private function createDirectories(string $__data_path): void
    {
        $this->info(sprintf('Creating directories in %s', $__data_path));
        File::ensureDirectoryExists(sprintf('%s', $__data_path));
        File::ensureDirectoryExists(sprintf('%s/Nginx', $__data_path));
        File::ensureDirectoryExists(sprintf('%s/Logs', $__data_path));
        File::ensureDirectoryExists(sprintf("%s/tmp", $__data_path));
        File::put(sprintf("%s/esp.sqlite", $__data_path), '');
    }

    /**
     * @return void
     */
    private function installDependencies(): void
    {
        $this->info('Installing nginx, php8.2 and common PHP extensions');
        Shell::run('sudo apt-get install', [
            'nginx-full', 'php8.2-ast', 'php8.2-bcmath', 'php8.2-bz2', 'php8.2-cli', 'php8.2-common', 'php8.2-curl',
            'php8.2-decimal', 'php8.2-dev', 'php8.2-ds', 'php8.2-enchant', 'php8.2-excimer', 'php8.2-fpm', 'php8.2-gd',
            'php8.2-gmp', 'php8.2-gnupg', 'php8.2-grpc', 'php8.2-http', 'php8.2-imap', 'php8.2-interbase',
            'php8.2-intl', 'php8.2-lz4', 'php8.2-maxminddb', 'php8.2-mbstring', 'php8.2-msgpack', 'php8.2-oauth',
            'php8.2-pcov', 'php8.2-pgsql', 'php8.2-protobuf', 'php8.2-pspell', 'php8.2-raphf', 'php8.2-readline',
            'php8.2-redis', 'php8.2-sqlite3', 'php8.2-ssh2', 'php8.2-tidy', 'php8.2-xdebug', 'php8.2-xhprof',
            'php8.2-xml', 'php8.2-xmlrpc', 'php8.2-xsl', 'php8.2-yaml', 'php8.2-zmq', 'php8.2-zstd', 'php8.2', '-y'
        ]);
    }

    /**
     * @param  mixed  $__name
     * @param  string  $__data_path
     *
     * @return void
     */
    private function setupNginx(mixed $__name, string $__data_path): void
    {
        $this->info('Copying nginx configuration');
        file_put_contents($__data_path . '/tmp/nginx', $this->nginxConfStub());
        File::replaceInFile('ESP_USER', $__name, $__data_path . '/tmp/nginx');
        File::replaceInFile('ESP_DATA_PATH', $__data_path, $__data_path . '/tmp/nginx');
        Shell::run('sudo openssl', ['dhparam', '-out', '/etc/nginx/dhparam.pem', 2048]);
        Shell::run('sudo mv', ['storage/tmp/nginx', '/etc/nginx/nginx.conf']);
    }

    /**
     * @param  mixed  $__name
     * @param  string  $__data_path
     *
     * @return void
     */
    private function setupPhp(mixed $__name, string $__data_path): void
    {
        $this->info('Copying php-fpm configuration');
        file_put_contents($__data_path . '/tmp/php', $this->php82PoolConfStub());
        File::replaceInFile('ESP_USER', $__name, $__data_path . '/tmp/php');
        File::replaceInFile('ESP_DATA_PATH', $__data_path, $__data_path . '/tmp/php');
        Shell::run('sudo mv', [$__data_path . '/tmp/php', '/etc/php/8.2/fpm/pool.d/www.conf']);
    }

    /**
     * @return void
     */
    private function restartServices(): void
    {
        $this->info('Restarting services');
        Shell::run('sudo systemctl restart', ['nginx.service', 'php8.2-fpm.service']);
    }

    /**
     * @param  string  $__data_path
     * @param  bool  $force
     *
     * @return void
     */
    private function setupEasyRsa(string $__data_path, bool $force): void
    {
        $__easy_dir = sprintf("%s/easyrsa", $__data_path);
        $__easy_sh = sprintf('./%s/easyrsa', $__easy_dir);
        $esp = posix_getcwd();

        if (File::isDirectory($__easy_dir) && $force) {
            File::delete(['EasyRSA-3.1.6.tar', 'EasyRSA-3.1.6.tgz']);
            File::deleteDirectory($__data_path.'/easyrsa');
        }

        $this->info('Downloading EasyRSA');
        file_put_contents(
            $esp.'/EasyRSA-3.1.6.tgz',
            file_get_contents('https://github.com/OpenVPN/easy-rsa/releases/download/v3.1.6/EasyRSA-3.1.6.tgz')
        );

        $this->info('Extracting EasyRSA');
        Shell::run('tar', ['-zxf', 'EasyRSA-3.1.6.tgz', '-C', $__data_path]);
        File::move(sprintf("%s/EasyRSA-3.1.6", $__data_path), $__easy_dir);
        File::delete(['EasyRSA-3.1.6.tar', 'EasyRSA-3.1.6.tgz']);

        $this->info('Setting up EasyRSA.');
        Shell::run($__easy_sh, ['init-pki']);

        File::append($__easy_dir.'/pki/vars', $this->pkiVarsStub());

        $this->newline();
        $this->info(sprintf(
            '  Before continuing, you can edit the file at %s to generate your CA cert.'.PHP_EOL.
            '  Sane defaults have been provided and you can safely ignore this step.',
            $__easy_dir.'/pki/vars'
        ));

        $question = $this->choice('Do you want to continue?', ['Yes', 'No']);

        $this->newline();
        if ($question === 'No') {
            $this->error(sprintf('Aborting installation and removing %s.', $__easy_dir));
            File::deleteDirectory($__easy_dir);
        } else {
            $this->info('Generating EasyRSA CA Certificate');
            Shell::run($__easy_sh,
                ['--req-cn="ESP by Sketch"', '--batch', 'build-ca', 'nopass', '>>', '/dev/null', '2&>1']);
        }
    }

    private function nginxConfStub(): string
    {
        return <<<'NGINXCONFIG'
user ESP_USER;
worker_processes auto;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
    worker_connections 768;
    # multi_accept on;
}

http {
    sendfile on;
    tcp_nopush on;
    types_hash_max_size 2048;
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3; # Dropping SSLv3, ref: POODLE
    ssl_prefer_server_ciphers on;
    access_log ESP_DATA_PATH/Logs/access.log;
    error_log ESP_DATA_PATH/Logs/error.log;

    ##
    # Gzip Settings
    ##

    gzip on;

    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
    include ESP_DATA_PATH/Nginx/*;
}
NGINXCONFIG;
    }

    private function php82PoolConfStub(): string
    {
        return <<<'PHPPOOLCONFIG'
[www]
user = ESP_USER
group = ESP_USER

listen = /home/ESP_USER/.php8.2-fpm.sock

listen.owner = ESP_USER
listen.group = ESP_USER

pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
access.log = ESP_DATA_PATH/Logs/php.access.log
access.format = "%R - %u %t \"%m %r%Q%q\" %s %f %{milli}d %{kilo}M %C%%"

php_admin_value[error_log] = ESP_DATA_PATH/Logs/php.error.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 32M
PHPPOOLCONFIG;
    }

    private function pkiVarsStub(): string
    {
        return <<<'PKIVARS'
set_var EASYRSA_REQ_COUNTRY	    "UK"
set_var EASYRSA_REQ_PROVINCE	"Northern Ireland"
set_var EASYRSA_REQ_CITY	    "Belfast"
set_var EASYRSA_REQ_ORG	        "Sketch Media"
set_var EASYRSA_REQ_EMAIL	    "some.email@example.com"
set_var EASYRSA_REQ_OU		    "Environment Setup Program"
set_var EASYRSA_REQ_CN          "ESP by SketchNI"
PKIVARS;
    }
}
