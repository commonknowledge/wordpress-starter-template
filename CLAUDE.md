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

- `./start.sh` brings the site up from any state, skipping steps already done; `./start.sh --yes` runs it without questions. `scripts/setup.sh` installs WordPress, activates the theme and loads the page fixtures. `scripts/smoke-test.sh` checks the site, and CI runs both, through `start.sh`, against a fresh install.
- PHP: `composer lint` (Pint) and `composer test` (Pest). Theme: `npm run build`, `npm run typecheck` and `npx eslint .` from the theme folder.
