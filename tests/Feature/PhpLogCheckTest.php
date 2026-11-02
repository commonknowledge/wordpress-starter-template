<?php

use Symfony\Component\Process\Process;

beforeEach(function () {
    $this->bin = sys_get_temp_dir() . '/php-log-check-' . bin2hex(random_bytes(4));
    mkdir($this->bin, 0700);
    file_put_contents($this->bin . '/debug.log', '');
    file_put_contents($this->bin . '/docker', <<<'SH'
#!/bin/sh
if [ "$*" != 'compose logs --no-color wordpress' ]; then
    echo "Unexpected Docker command: $*" >&2
    exit 2
fi
printf '%s' "$PHP_LOG_DOCKER_OUTPUT"
if [ "$PHP_LOG_DOCKER_STATUS" -ne 0 ]; then
    echo 'Simulated Docker log read failure' >&2
fi
exit "$PHP_LOG_DOCKER_STATUS"
SH);
    chmod($this->bin . '/docker', 0700);
});

afterEach(function () {
    unlink($this->bin . '/docker');
    unlink($this->bin . '/debug.log');
    rmdir($this->bin);
});

test('the PHP log check distinguishes clean logs, PHP errors and failed log reads', function (string $container, int $dockerStatus, string $debugLog, int $expectedStatus, string $message) {
    file_put_contents($this->bin . '/debug.log', $debugLog);
    $process = new Process(['sh', 'scripts/check-php-log.sh', $this->bin . '/debug.log'], dirname(__DIR__, 2), [
        'PATH' => $this->bin . PATH_SEPARATOR . getenv('PATH'),
        'PHP_LOG_DOCKER_OUTPUT' => $container,
        'PHP_LOG_DOCKER_STATUS' => (string) $dockerStatus,
    ]);
    $process->run();

    expect($process->getExitCode())->toBe($expectedStatus)
        ->and($process->getOutput())->toContain($message);

    if ($expectedStatus !== 0) {
        expect($process->getOutput())->not->toContain('PASS  PHP logged no errors');
    }
    if ($dockerStatus !== 0) {
        expect($process->getErrorOutput())->toContain('Simulated Docker log read failure');
    } else {
        expect($process->getErrorOutput())->toBe('');
    }
})->with([
    'failed log read' => ['', 1, '', 1, 'FAIL  Could not read the wordpress container logs'],
    'failed log read with partial output' => ['wordpress | ready to handle connections', 1, '', 1, 'FAIL  Could not read the wordpress container logs'],
    'empty logs' => ['', 0, '', 0, 'PASS  PHP logged no errors'],
    'normal FPM output' => ['wordpress | NOTICE: ready to handle connections', 0, '', 0, 'PASS  PHP logged no errors'],
    'container warning' => ['wordpress | PHP Warning: simulated container warning', 0, '', 1, 'PHP Warning: simulated container warning'],
    'WordPress warning' => ['', 0, 'PHP Warning: simulated WordPress warning', 1, 'PHP Warning: simulated WordPress warning'],
]);
