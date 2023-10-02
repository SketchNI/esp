<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PharData;
use function Termwind\render;
use function Termwind\style;

class SetupCommand extends Command
{
    protected $signature = 'setup';

    protected $description = 'Sets up ESP';

    public function handle(): void
    {
        style('info')->apply('text-blue-500');
        style('error')->apply('text-red-500');
        style('success')->apply('text-green-500');

        if (!$this->detectOperatingSystemIsWsl2()) {
            render(sprintf(
                '<span><span class="error">ERROR</span> ESP is designed to run in a WSL 2 environment. Your environment is %s.</span>',
                PHP_OS
            ));
            return;
        }

        $__env_home = getenv('HOME');
        $__data_path = sprintf("%s/.esp", $__env_home);
        $__name = posix_getpwuid(posix_geteuid())['name'];

        $this->createDirectories($__data_path);
        $this->installDependencies();
        $this->setupNginx($__name, $__data_path);
        $this->setupPhp($__name, $__data_path);
        $this->restartServices();
        $this->setupEasyRsa($__data_path);

        chdir(posix_getcwd());
    }

    private function detectOperatingSystemIsWsl2(): bool
    {
        return preg_match('/WSL2/i', php_uname('r'));
    }

    /**
     * @param  string  $__data_path
     *
     * @return void
     */
    private function createDirectories(string $__data_path): void
    {
        render(sprintf(
                '<span><span class="info">INFO</span> Creating directories in %s</span>', $__data_path)
        );
        File::ensureDirectoryExists(sprintf('%s', $__data_path));
        File::ensureDirectoryExists(sprintf('%s/Nginx', $__data_path));
        File::ensureDirectoryExists(sprintf('%s/Logs', $__data_path));
    }

    /**
     * @return void
     */
    private function installDependencies(): void
    {
        render('<span><span class="info">INFO</span> Installing nginx, php8.2 and common PHP extensions.</span>');
        shell_exec("sudo apt-get install nginx-full php8.2-ast php8.2-bcmath php8.2-bz2 php8.2-cli php8.2-common php8.2-curl php8.2-decimal php8.2-dev php8.2-ds php8.2-enchant php8.2-excimer php8.2-fpm php8.2-gd php8.2-gmp php8.2-gnupg php8.2-grpc php8.2-http php8.2-imap php8.2-interbase php8.2-intl php8.2-lz4 php8.2-maxminddb php8.2-mbstring php8.2-msgpack php8.2-oauth php8.2-pcov php8.2-pgsql php8.2-protobuf php8.2-pspell php8.2-raphf php8.2-readline php8.2-redis php8.2-sqlite3 php8.2-ssh2 php8.2-tidy php8.2-xdebug php8.2-xhprof php8.2-xml php8.2-xmlrpc php8.2-xsl php8.2-yaml php8.2-zmq php8.2-zstd php8.2 -y");
    }

    /**
     * @param  mixed  $__name
     * @param  string  $__data_path
     *
     * @return void
     */
    private function setupNginx(mixed $__name, string $__data_path): void
    {
        render('<span><span class="info">INFO</span> Copying nginx configuration.</span>');
        File::copy('storage/stubs/nginx.conf.stub', 'storage/tmp/nginx');
        File::replaceInFile('ESP_USER', $__name, 'storage/tmp/nginx');
        File::replaceInFile('ESP_DATA_PATH', $__data_path, 'storage/tmp/nginx');
        shell_exec('sudo mv storage/tmp/nginx /etc/nginx/nginx.conf');
    }

    /**
     * @param  mixed  $__name
     * @param  string  $__data_path
     *
     * @return void
     */
    private function setupPhp(mixed $__name, string $__data_path): void
    {
        render('<span><span class="info">INFO</span> Copying php-fpm configuration.</span>');
        File::copy('storage/stubs/php8.2-pool.conf.stub', 'storage/tmp/php');
        File::replaceInFile('ESP_USER', $__name, 'storage/tmp/php');
        File::replaceInFile('ESP_DATA_PATH', $__data_path, 'storage/tmp/php');
        shell_exec('sudo mv storage/tmp/php /etc/php/8.2/fpm/pool.d/www.conf');
    }

    /**
     * @return void
     */
    private function restartServices(): void
    {
        render('<span><span class="info">INFO</span> Restarting services.</span>');
        shell_exec('sudo systemctl restart nginx.service php8.2-fpm.service');
    }

    /**
     * @param  string  $__data_path
     *
     * @return void
     */
    private function setupEasyRsa(string $__data_path): void
    {
        if (!File::isDirectory(sprintf("%s/easyrsa", $__data_path))) {
            render('<span><span class="info">INFO</span> Downloading EasyRSA.</span>');
            chdir($dir = posix_getcwd());
            file_put_contents(
                'EasyRSA-3.1.6.tgz',
                file_get_contents('https://github.com/OpenVPN/easy-rsa/releases/download/v3.1.6/EasyRSA-3.1.6.tgz')
            );

            $phar = new PharData('EasyRSA-3.1.6.tgz');
            $phar->decompress();
            $phar->extractTo($__data_path.'/');
            File::move(sprintf("%s/EasyRSA-3.1.6", $__data_path), sprintf("%s/easyrsa", $__data_path));
            File::delete(['EasyRSA-3.1.6.tar', 'EasyRSA-3.1.6.tgz']);

            render('<span><span class="info">INFO</span> Setting up EasyRSA.</span>');
            chdir($__data_path.'/easyrsa');
            shell_exec('./easyrsa init-pki');

            chdir($dir);
            $contents = file_get_contents('storage/stubs/pki-vars.stub');
            File::append($__data_path.'/easyrsa/pki/vars', $contents);

            $this->choice(sprintf(
                '  Before continuing, you can edit the file at %s to generate your CA cert.'.PHP_EOL.
                '   Sane defaults have been provided and you can safely ignore this step.',
                $__data_path.'/easyrsa/pki/vars'
            ), ['Continue']);

            chdir($__data_path.'/easyrsa');
            shell_exec('./easyrsa --req-cn="ESP by Sketch" --batch build-ca nopass');
        } else {
            render('<span><span class="info">INFO</span> EasyRSA already installed. Skipping.</span>');
        }
    }
}
