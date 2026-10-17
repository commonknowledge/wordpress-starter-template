<?php

/**
 * Renames a new project made from this template. Replaces the template's name
 * in every tracked file (and .env, if there is one) and renames the theme
 * folder. Run it once, before the first `docker compose up`, so containers,
 * the database volume and the local domain all take the new name.
 *
 * Usage: php scripts/rename-project.php <project-name>
 *   e.g. php scripts/rename-project.php tenants-union
 *
 * The name must be kebab-case: lowercase letters, digits and single hyphens.
 * It becomes e.g. tenants-union (folders, containers, block namespace and
 * http://tenants-union.test) and TenantsUnion (the PHP namespace).
 */
const TEMPLATE_NAME = 'wordpress-starter-template';
const TEMPLATE_CLASS_NAME = 'WordpressStarterTemplate';

// History and the licence keep the template's name, and so does this script
const SKIP_FILES = [
    'CHANGELOG.md',
    'LICENSE.md',
    'scripts/rename-project.php',
    'tests/Feature/RenameProjectTest.php',
];

function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$name = $argv[1] ?? '';
if (! preg_match('/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/', $name)) {
    fail('Usage: php scripts/rename-project.php <project-name>, in kebab-case, e.g. tenants-union');
}
if ($name === TEMPLATE_NAME) {
    fail('Choose a name other than the template\'s own');
}
$className = implode('', array_map('ucfirst', explode('-', $name)));

$root = dirname(__DIR__);
$themes = $root . '/web/app/themes';
if (! is_dir($themes . '/' . TEMPLATE_NAME)) {
    fail('This project has already been renamed: there is no theme folder called ' . TEMPLATE_NAME);
}
if (file_exists($themes . '/' . $name)) {
    fail("A theme folder called $name already exists");
}

// safe.directory lets this run in a container whose user doesn't own the files
exec('git -c safe.directory=* -C ' . escapeshellarg($root) . ' ls-files -z 2>&1', $output, $status);
if ($status !== 0) {
    fail('Could not list the project\'s files with git: ' . implode(PHP_EOL, $output));
}
$files = array_filter(explode("\0", implode("\n", $output)));
if (file_exists($root . '/.env')) {
    $files[] = '.env';
}

$changed = 0;
foreach ($files as $file) {
    $path = $root . '/' . $file;
    if (in_array($file, SKIP_FILES, true) || is_link($path) || ! is_file($path)) {
        continue;
    }
    $contents = file_get_contents($path);
    if (str_contains($contents, "\0")) {
        continue;
    }
    // Links to the template's own repository keep pointing at it
    $renamed = preg_replace(
        '#(?<!github\.com/commonknowledge/)' . TEMPLATE_NAME . '#',
        $name,
        str_replace(TEMPLATE_CLASS_NAME, $className, $contents),
    );
    if ($renamed !== $contents) {
        file_put_contents($path, $renamed);
        $changed++;
    }
}

rename($themes . '/' . TEMPLATE_NAME, $themes . '/' . $name);

echo "Renamed the project to $name in $changed files, and moved the theme to web/app/themes/$name." . PHP_EOL;
echo 'Next:' . PHP_EOL;
if (! file_exists($root . '/.env')) {
    echo '  cp .env.example .env' . PHP_EOL;
}
echo '  composer dump-autoload, or composer install if you haven\'t yet' . PHP_EOL;
echo "  npm run build in web/app/themes/$name, if you have built the theme already" . PHP_EOL;
echo "The site will be at http://$name.test" . PHP_EOL;
