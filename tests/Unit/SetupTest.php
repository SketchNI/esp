<?php

test('test bad environment', function () {
    $this->artisan('setup -b')
        ->expectsOutput('ERROR ESP is designed to run in a WSL 2 environment. Your environment is Linux.')
        ->assertExitCode(0);

    $this->assertCommandCalled('setup -b');
});

test('test setup command', function () {
    $this->artisan('setup -f')
        ->expectsOutput('INFO Environment is WSL2.')
        ->expectsOutput('INFO Creating directories in /home/sdf/.esp')
        ->expectsOutput('INFO Installing nginx, php8.2 and common PHP extensions.')
        ->expectsOutput('INFO Copying nginx configuration.')
        ->expectsOutput('INFO Copying php-fpm configuration.')
        ->expectsOutput('INFO Restarting services.')
        ->expectsOutput('INFO Downloading EasyRSA.')
        ->expectsOutput('INFO Setting up EasyRSA.')
        ->expectsQuestion('Do you want to continue?', 'Yes')
        ->assertExitCode(0);

    $this->assertCommandCalled('setup -f');
});

test('test setup command with interruption', function () {
    $this->artisan('setup -f')
        ->expectsOutput('INFO Environment is WSL2.')
        ->expectsOutput('INFO Creating directories in /home/sdf/.esp')
        ->expectsOutput('INFO Installing nginx, php8.2 and common PHP extensions.')
        ->expectsOutput('INFO Copying nginx configuration.')
        ->expectsOutput('INFO Copying php-fpm configuration.')
        ->expectsOutput('INFO Restarting services.')
        ->expectsOutput('INFO Downloading EasyRSA.')
        ->expectsOutput('INFO Setting up EasyRSA.')
        ->expectsQuestion('Do you want to continue?', 'No')
        ->assertExitCode(0);

    $this->assertCommandCalled('setup -f');
});
