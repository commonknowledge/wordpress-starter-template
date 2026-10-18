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

## Local environment

Engineers start every project with `./start.sh`, so a broken setup stops everyone's work. Keep it working, and keep CI testing it.

- `start.sh` orchestrates; each step is its own script and must keep working on its own: `scripts/rename-project.php`, `scripts/proxy.sh`, `scripts/setup-dns.sh`, `scripts/setup.sh`. `scripts/uninstall.sh` reverses them. Orchestration is POSIX shell, and must work with both GNU and BSD (macOS) tools; text processing that must behave identically everywhere, like the rename, is PHP.
- `start.sh` checks every step and skips what is done, so it is safe to run at any time. It must never overwrite content: it only runs `setup.sh` when WordPress isn't installed. Its questions default to yes; `uninstall.sh` deletes data, so its question defaults to no.
- `LOCAL_DOMAIN` in `.env` is the single source of the site's domain. Docker Compose, `WP_HOME`, the scripts (through `scripts/env.sh`) and CI all read it, and an exported `LOCAL_DOMAIN` wins over `.env` everywhere, as in Compose.
- One Traefik proxy and one CoreDNS server (`docker/proxy`) serve every project on the machine. Projects join the external `local-proxy` network, so the proxy must run before `docker compose`.
- Everything that names the template (`wordpress-starter-template`, `WordpressStarterTemplate`) is renamed by `rename-project.php`, so new names need no extra handling. `tests/Feature/RenameProjectTest.php` checks nothing is missed.

`.github/workflows/push.yml` tests this setup on every pull request, on `main` and nightly, so upstream image changes are caught too. Its jobs, one concern each:

- `test`: the template comes up with `./start.sh --yes` and passes the smoke, theme asset and `setup.sh` tests; re-running `start.sh` skips every step and keeps an edited page.
- `prompts`: `start.sh` rejects a name that isn't kebab-case, and `uninstall.sh` deletes nothing on its default answer.
- `projects`: three fresh `git clone`s come up side by side through `start.sh`, one answering its questions, with real `*.test` DNS and no `/etc/hosts` entries. Each serves its own marked theme and has its own database. Other lookups survive the DNS server stopping. Uninstalling removes one project and leaves the others; `--all` keeps the proxy while it's in use, and the last one leaves no containers, volumes, networks, images or DNS setup behind.
- `macos`: the `.test` resolver is added and removed, and `.env` parsing, `start.sh --help` and the rename work with BSD tools. There's no Docker on GitHub's macOS runners.
- `shellcheck`: every shell script.

When you change a setup script, update the test for the behaviour you changed, and add a test for any new step. Check a new test fails when the behaviour it guards is broken.

## Checking work

- `./start.sh --yes` brings the site up without questions, and `scripts/smoke-test.sh` checks it. Shell scripts: `shellcheck -s sh start.sh scripts/*.sh`.
- PHP: `composer lint` (Pint) and `composer test` (Pest). Theme: `npm run build`, `npm run typecheck` and `npx eslint .` from the theme folder.
