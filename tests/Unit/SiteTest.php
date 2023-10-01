<?php

test('test that there are no sites', function () {
    $this
        ->artisan('site:list')
        ->expectsOutput('ERROR There are no registered sites.')
        ->assertExitCode(0);

    $this->assertCommandCalled('site:list');
});
