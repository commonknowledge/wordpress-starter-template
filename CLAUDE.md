# WordPress starter

Conventions for sites built from this template. See README.md for setup.

## Stack

- Bedrock (Composer-managed WordPress in `web/wp`, config in `config/` and `.env`), run locally with Docker Compose.
- A block (Full Site Editor) theme in `web/app/themes/wordpress-starter-template`: templates, parts and patterns are block markup, and design tokens live in `theme.json`.
- Custom code lives in the theme: post types, taxonomies, fields (Carbon Fields) and blocks.

## Building the theme

- Use core blocks, block styles and patterns where they are enough. Write a custom block only when they aren't.
- **Styling:** `theme.json` first, as far as it sensibly goes. Use `assets/main.css` or a block's own stylesheet only for what `theme.json` can't express. No Tailwind.
- **Custom blocks** live in `blocks/<name>/`; copy `blocks/example` to start one.
  - `block.json` defines the block. Its `editorScript`, `style` and `viewScript` point at `build/blocks/<name>/`.
  - `index.tsx` is the editor script, `render.php` renders on the server, and `style.scss` styles the front end and editor. An optional `view.ts` is front-end JavaScript.
  - webpack and `functions.php` pick up new block folders automatically.
  - Import `@wordpress/*` packages and React normally. The build uses WordPress's own copies, so they are never bundled.
  - Add new blocks to `scripts/fixtures/blocks.html`, so they appear at `/blocks/` and are covered by the CI smoke test.
- TypeScript 7 type-checks (`npm run typecheck`). ts-loader and typescript-eslint use TypeScript 6 through the `typescript` alias, until TypeScript 7 has a compiler API.

## theme.json gotchas

- In `css` properties, nested selectors must start with `&` (`& img`, `&:hover`). Context selectors (`.parent &`) and comma-separated lists get mangled, so write one selector per rule and put context selectors in a stylesheet.
- Block style variation CSS only loads for blocks saved with that style. Markup from a PHP render callback gets none, so style it in the block's own stylesheet.
- Variation `css` is output inside `:where()`, so core block CSS beats it. Put those overrides in a stylesheet.
- Preset slugs must not contain digits: `h1` becomes `--wp--preset--font-size--h-1`. Use `heading-1`.
- Templates need `wp:post-content` with `"align":"full"` for wide-aligned blocks in content to go wide.

## Checking work

- `scripts/setup.sh` installs WordPress, activates the theme and loads the page fixtures. `scripts/smoke-test.sh` checks the site, and CI runs both against a fresh install.
- PHP: `composer lint` (Pint) and `composer test` (Pest). Theme: `npm run build`, `npm run typecheck`, `npm test` (build output) and `npx eslint .` from the theme folder.
- After `setup.sh`, `scripts/test-plugins.sh`, `npm run test:e2e` (Playwright: front end, block editor, Site Editor) and `scripts/check-php-log.sh` check the running site. CI runs them all, so that a Renovate pull request that passes can be merged with confidence. When adding to the theme, keep these tests covering it: they read block folders and `block.json` files, so new blocks are covered once they are in `scripts/fixtures/blocks.html`.

## Finishing work

- Always open a pull request for your branch once the work is pushed, without waiting to be asked. CI only runs on pull requests and `main`, so work without one is unchecked, and in Claude Code cloud sessions a branch with no pull request is incomplete.
- Then watch the pull request (in cloud sessions, subscribe to its activity) and fix CI when it fails, so a person isn't handed a pull request with failing CI. Make at most three attempts: fix, push, wait for CI, and repeat. If CI is still failing after the third, stop and tell a person, in a comment on the pull request, what is failing, what each attempt tried and what you think is wrong. Stop sooner, and say so, if the failure can't be fixed from the branch.
