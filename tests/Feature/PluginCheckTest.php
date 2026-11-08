<?php

use Symfony\Component\Process\Process;

beforeEach(function () {
    $this->bin = sys_get_temp_dir() . '/plugin-check-' . bin2hex(random_bytes(4));
    mkdir($this->bin, 0700);
    file_put_contents($this->bin . '/docker', <<<'SH'
#!/bin/sh
case "$*" in
    *'/bin/wp eval '*)
        if [ "$PLUGIN_CHECK_SCENARIO" = environment-fails ]; then
            echo 'Simulated environment lookup failure' >&2
            exit 1
        fi
        echo development
        ;;
    *'/bin/wp plugin list '*)
        if [ "$PLUGIN_CHECK_SCENARIO" = plugin-list-fails ]; then
            echo 'Simulated plugin list failure' >&2
            exit 1
        fi
        ;;
    *)
        echo "Unexpected Docker command: $*" >&2
        exit 1
        ;;
esac
SH);
    chmod($this->bin . '/docker', 0700);
});

afterEach(function () {
    unlink($this->bin . '/docker');
    rmdir($this->bin);
});

test('the plugin check fails when WP-CLI cannot read its inputs', function (string $scenario, string $message, string $error) {
    $process = new Process(['sh', 'scripts/test-plugins.sh'], dirname(__DIR__, 2), [
        'PATH' => $this->bin . PATH_SEPARATOR . getenv('PATH'),
        // The site's domain, as start.sh sets it in .env
        'LOCAL_DOMAIN' => 'plugin-check.test',
        'PLUGIN_CHECK_SCENARIO' => $scenario,
    ]);
    $process->run();

    expect($process->getExitCode())->toBe(1)
        ->and($process->getOutput())->toContain($message)->not->toContain('All checks passed')
        ->and($process->getErrorOutput())->toContain($error);
})->with([
    'environment lookup fails' => ['environment-fails', 'FAIL  Could not read the WordPress environment', 'Simulated environment lookup failure'],
    'plugin list fails' => ['plugin-list-fails', 'FAIL  Could not list inactive plugins', 'Simulated plugin list failure'],
]);

test('the plugin check accepts a successfully retrieved empty plugin list', function () {
    $process = new Process(['sh', 'scripts/test-plugins.sh'], dirname(__DIR__, 2), [
        'PATH' => $this->bin . PATH_SEPARATOR . getenv('PATH'),
        // The site's domain, as start.sh sets it in .env
        'LOCAL_DOMAIN' => 'plugin-check.test',
        'PLUGIN_CHECK_SCENARIO' => 'no-inactive-plugins',
    ]);
    $process->run();

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toContain('No inactive plugins to test', 'All checks passed')
        ->and($process->getErrorOutput())->toBe('');
});
