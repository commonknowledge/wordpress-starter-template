<?php

// Runs scripts/rename-project.php on a copy of the project's files

function copyProject(): string
{
    $root = dirname(__DIR__, 2);
    $copy = sys_get_temp_dir() . '/rename-project-' . bin2hex(random_bytes(4));

    exec('git -c safe.directory=* -C ' . escapeshellarg($root) . ' ls-files -co --exclude-standard -z', $output);
    foreach (array_filter(explode("\0", implode("\n", $output))) as $file) {
        if (! is_file("$root/$file")) {
            continue;
        }
        is_dir(dirname("$copy/$file")) || mkdir(dirname("$copy/$file"), 0777, true);
        copy("$root/$file", "$copy/$file");
    }
    exec('git -C ' . escapeshellarg($copy) . ' init -q && git -C ' . escapeshellarg($copy) . ' add -A');

    return $copy;
}

function renameProject(string $copy, string $name): array
{
    exec('php ' . escapeshellarg("$copy/scripts/rename-project.php") . ' ' . escapeshellarg($name) . ' 2>&1', $output, $status);

    return [$status, implode("\n", $output)];
}

beforeEach(function () {
    $this->copy = copyProject();
});

afterEach(function () {
    exec('rm -rf ' . escapeshellarg($this->copy));
});

test('renames the theme folder, names and namespace', function () {
    [$status] = renameProject($this->copy, 'tenants-union');

    expect($status)->toBe(0)
        ->and("{$this->copy}/web/app/themes/tenants-union/style.css")->toBeFile()
        ->and("{$this->copy}/web/app/themes/wordpress-starter-template")->not->toBeDirectory()
        ->and(file_get_contents("{$this->copy}/composer.json"))
        ->toContain('"CommonKnowledge\\\\TenantsUnion\\\\": "web/app/themes/tenants-union/src/"')
        ->and(file_get_contents("{$this->copy}/.env.example"))
        ->toContain('LOCAL_DOMAIN=tenants-union.test');
});

test('leaves no trace of the template name outside history and its own repository links', function () {
    renameProject($this->copy, 'tenants-union');

    exec('grep -rnI -e wordpress-starter-template -e WordpressStarterTemplate --exclude-dir=.git '
        . '--exclude=CHANGELOG.md --exclude=rename-project.php --exclude=RenameProjectTest.php '
        . escapeshellarg($this->copy), $leftovers);
    $leftovers = array_filter($leftovers, fn($line) => ! str_contains($line, 'github.com/commonknowledge/wordpress-starter-template'));

    expect($leftovers)->toBe([]);
});

test('rejects a name that is not kebab-case', function (string $name) {
    [$status] = renameProject($this->copy, $name);

    expect($status)->toBe(1)
        ->and("{$this->copy}/web/app/themes/wordpress-starter-template")->toBeDirectory();
})->with(['Tenants Union', 'tenants_union', '-tenants', 'tenants--union', '']);

test('refuses to run twice', function () {
    renameProject($this->copy, 'tenants-union');
    [$status, $output] = renameProject($this->copy, 'another-name');

    expect($status)->toBe(1)
        ->and($output)->toContain('already been renamed');
});
