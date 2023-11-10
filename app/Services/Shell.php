<?php

namespace App\Services;

class Shell
{
    public static function run(string $command, array $parameters = [], bool $dry_run = false): array
    {
        $parameters = implode(' ', $parameters);
        if ($dry_run) {
            $output = [$command, $parameters];
            $result_code = 0;
        } else {
            exec(sprintf("%s %s", $command, $parameters), $output, $result_code);
        }

        return compact('output', 'result_code');
    }
}
