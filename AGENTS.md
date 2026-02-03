# AGENTS.md

## Commands

### Build & Development

- **Start development server**: `cd web/app/themes/wordpress-starter-template && npm run dev`
  - Runs webpack dev server on port 9000 with hot module replacement
- **Build for production**: `cd web/app/themes/wordpress-starter-template && npm run build`
  - Creates optimized bundle in `build/` directory
- **Start WordPress locally**: `docker compose up`
  - Site accessible at http://localhost:8082

### Lint & Format

- **Lint & fix JS/TS**: `cd web/app/themes/wordpress-starter-template && npm run lint`
  - Runs ESLint with --fix and Prettier --write
- **Lint PHP**: `composer test` (runs phpcs)
- **Check specific PHP file**: `vendor/bin/phpcs path/to/file.php`
- **Format specific JS file**: `cd web/app/themes/wordpress-starter-template && npx prettier --write path/to/file.js`

### Tests

- **Run PHP code standards test**: `composer test`
  - Uses PHP_CodeSniffer with PSR-2 rules
- **Lint specific file**: `vendor/bin/phpcs web/app/themes/wordpress-starter-template/src/Blocks.php`

### Docker Commands

- **Install dependencies**: `docker compose run composer install`
- **Run WP-CLI**: `docker compose run wordpress wp --allow-root <command>`
- **Install WordPress**: `docker compose run wordpress wp core install --url=http://localhost:8082 --title='Site Title' --admin_user=admin --admin_email=email@example.com`

## Code Style Guidelines

### PHP

- **Standard**: PSR-2 (checked via phpcs)
- **PHP Version**: 8.3+
- **Namespaces**: Use `CommonKnowledge\WordpressStarterTemplate\`
- **Class naming**: PascalCase (e.g., `Blocks`, `Taxonomy`)
- **Method naming**: camelCase (e.g., `register()`, `registerExampleBlock()`)
- **Array syntax**: Use short array syntax `[]` instead of `array()`
- **String quotes**: Use double quotes for strings with interpolation, single quotes otherwise
- **Indentation**: 4 spaces
- **Files**: Located in `web/app/themes/wordpress-starter-template/src/`

**Example:**
```php
<?php

namespace CommonKnowledge\WordpressStarterTemplate;

class Blocks
{
    public static function register()
    {
        self::registerExampleBlock();
    }

    public static function registerExampleBlock()
    {
        $labels = [
            'name' => 'Examples',
        ];
    }
}
```

### JavaScript/TypeScript

- **Language**: TypeScript with strict mode enabled
- **Target**: ES2017
- **Module system**: ES Modules (import/export)
- **JSX**: React JSX transform
- **Semicolons**: Required
- **Quotes**: Double quotes for strings
- **Indentation**: 2 spaces
- **Max line length**: Follow Prettier defaults

### Imports

- **Path alias**: `@/` maps to `assets/` directory
- **Import order**: Use ESLint import/order plugin (alphabetical, case-insensitive)
- **Sort imports**: Built-in imports and symbols sorted alphabetically

**Example:**
```typescript
import "@/main.css";
import { a, b, c } from "module";
import React from "react";
```

### CSS/Styling

- **Framework**: Tailwind CSS v4
- **PostCSS**: Used for processing
- **File location**: `web/app/themes/wordpress-starter-template/assets/`
- **CSS imports**: Import in entry point (main.tsx)

### Error Handling

- **PHP**: Use WordPress error handling patterns
  - REST API: Return `WP_REST_Response` or `WP_Error`
  - Check permissions with `permission_callback`
- **TypeScript**: Use strict type checking, avoid `any`

### Naming Conventions

- **PHP classes**: PascalCase (e.g., `API`, `Taxonomy`)
- **PHP files**: Match class name (e.g., `Blocks.php`)
- **JS/TS files**: camelCase or PascalCase for components
- **Theme slug**: `wordpress-starter-template` (kebab-case)
- **Constants**: SCREAMING_SNAKE_CASE
- **WordPress actions/filters**: snake_case with underscores

### Git & Pre-commit

- **Pre-commit hooks**: Configured via husky and lint-staged
- **PHP**: Runs phpcs pre-commit hook
- **JS**: Runs `eslint --fix` and `prettier -w` on staged files
- **Install hooks**: `composer run install-hooks`

## Project Structure

```
web/app/themes/wordpress-starter-template/
├── assets/           # JS/TS/CSS source files
│   ├── main.tsx     # Entry point
│   └── main.css     # Styles with Tailwind
├── build/           # Compiled output (gitignored)
├── src/             # PHP classes
│   ├── API.php
│   ├── Blocks.php
│   └── Taxonomy.php
├── functions.php    # Theme setup and hooks
├── package.json    # NPM dependencies and scripts
└── composer.json   # PHP dependencies (root level)
```

## WordPress Development

- **Framework**: Bedrock (modern WordPress stack)
- **Custom fields**: Carbon Fields
- **Block editor**: Gutenberg supported
- **REST API**: Custom endpoints in API.php
- **Theme location**: `web/app/themes/wordpress-starter-template/`
- **Plugins**: Managed via Composer (wpackagist)

## Important Notes

- Never commit the `build/` directory or `node_modules/`
- PHP_CodeSniffer ignores `web/wp` and `vendor/` directories
- Webpack dev server runs on port 9000, site on 8082
- Use `WP_ENV` environment variable (development/production)
