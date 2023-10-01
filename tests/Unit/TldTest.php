<?php

test('test that the tld exists', function () {
    expect(file_get_contents('storage/tld'))->toBeString('test');
});


test('test that we can change tld', function() {
    $this
        ->artisan('tld foo')
        ->expectsOutput('INFO TLD has been updated to .foo.')
        ->assertExitCode(0);

    $this->assertCommandCalled('tld foo');

    expect(file_get_contents('storage/tld'))->toBeString('foo');
});

test('test that tld has been updated', function() {
    $this->artisan('tld')
        ->expectsOutput('INFO Current TLD is .foo.')
        ->assertExitCode(0);

    $this->assertCommandCalled('tld');

    expect(file_get_contents('storage/tld'))->toBeString('foo');
});

test('test that we can reset tld', function() {
    $this
        ->artisan('tld test')
        ->expectsOutput('INFO TLD has been updated to .test.')
        ->assertExitCode(0);

    $this->assertCommandCalled('tld test');

    expect(file_get_contents('storage/tld'))->toBeString('test');
});

