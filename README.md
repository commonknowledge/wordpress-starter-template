# WordPress Starter

A starter repository for [WordPress](https://wordpress.org) websites for organisers, campaigns and anyone else who finds it useful.

This folder structure uses the [Bedrock](https://roots.io/bedrock/) pattern, a modern WordPress stack.

## Requirements

- Docker [installed](https://docs.docker.com/install/) and running
- Node 24 (see the theme's `.nvmrc`) to build the theme
- PHP and Composer are optional: without them, the scripts run them in containers

## Run locally

1. [Clone This Repository](https://github.com/commonknowledge/wordpress-starter-template)
2. From the project folder, run:

```
./start.sh
```

That's it. The site is then at `http://<project-name>.test`, and the admin login is `admin` / `admin`.

`start.sh` checks each step below, skips the ones already done, and asks before anything that changes the project or needs `sudo`. Press Enter to accept the default, which is always yes. It is safe to run again at any time, for example after pulling changes or restarting your computer.

1. **Rename the project.** In a new project made from the template, it asks for the project's name in kebab-case: lowercase words joined by hyphens, such as `tenants-union`. It replaces the template's name everywhere, renames the theme folder, and sets the local domain to `tenants-union.test`. The PHP namespace becomes `TenantsUnion`.
2. **Create `.env`** from `.env.example`. Change details [as per the Bedrock documentation](https://roots.io/bedrock/docs/environment-variables/) if needed. `LOCAL_DOMAIN` sets the site's local domain, which Docker Compose, WordPress and the scripts all read.
3. **Start the local proxy**, shared by every project on your machine (see [Local domains](#local-domains)).
4. **Point `*.test` domains at the proxy.** This needs `sudo`, and only happens once per machine.
5. **Install Composer dependencies**, using a container if Composer isn't installed.
6. **Build the theme** with npm (see [Theme](#theme)).
7. **Start the containers.**
8. **Install WordPress**, activate the theme and load the page fixtures in `scripts/fixtures`.

`./start.sh --yes` accepts every default without asking, and `./start.sh --name tenants-union` names the project up front.

### Doing it by hand

Each step is a script or command you can also run yourself:

| Step | Command |
| --- | --- |
| Rename the project | `php scripts/rename-project.php my-project` or `composer rename-project my-project` |
| Create `.env` | `cp .env.example .env` |
| Start the proxy | `scripts/proxy.sh` |
| Point `*.test` at the proxy | `scripts/setup-dns.sh` |
| Install dependencies | `composer install`, or `docker compose run --rm composer install` |
| Build the theme | `npm ci && npm run build` in the theme folder |
| Start the containers | `docker compose up -d` |
| Install WordPress | `scripts/setup.sh "" "Site Title"` |

The proxy must be running before you use `docker compose`, because each project joins its network. `setup.sh` takes the site URL first, which defaults to `LOCAL_DOMAIN` in `.env`.

### Local domains

Sites are served at `http://<project>.test` instead of on a host port, so several projects can run at once without clashing. A shared [Traefik](https://traefik.io/traefik/) proxy in `docker/proxy` listens on port 80 and routes each domain to the project that claims it: `docker-compose.yml` labels the `nginx` container with `LOCAL_DOMAIN` from `.env`. To change a project's domain, change `LOCAL_DOMAIN` and run `docker compose up -d` again. Every project built from this template uses the same proxy.

- `scripts/proxy.sh` starts the proxy, and it restarts with Docker after that. Extra arguments go to `docker compose`, e.g. `scripts/proxy.sh logs -f` or `scripts/proxy.sh down`. Its dashboard is at [http://traefik.test](http://traefik.test).
- `scripts/setup-dns.sh` makes every `*.test` name resolve to `127.0.0.1`, using a small DNS server that runs with the proxy. On macOS it writes `/etc/resolver/test`; on Linux it configures systemd-resolved. It needs `sudo`, and `scripts/setup-dns.sh --remove` undoes it. Without it, add `127.0.0.1 <LOCAL_DOMAIN>` to `/etc/hosts` for each project.
- Port 80 must be free on the host, so stop anything else that uses it, such as a local Apache or Laravel Valet.
- MariaDB is published on a free port picked by Docker; `docker compose port mysql 3306` shows it. Set `DB_HOST_PORT` in `.env` for a fixed port.
- If a browser uses DNS over HTTPS, it may bypass the system resolver for `.test` names. Turn it off, or add the domain to `/etc/hosts`.

### Checking the environment works

Run the smoke test with the site title and URL. Extra `path=text` arguments check a page contains some text:

```
scripts/smoke-test.sh "WordPress Starter Template" "" "/blocks/=wp-block-wordpress-starter-template-example"
```

CI builds the theme, runs `setup.sh` and runs the smoke test against a fresh install.

## Development Documentation

### Theme

The theme in `web/app/themes/wordpress-starter-template` is built with webpack, TypeScript, Sass and PostCSS, and needs Node 24 (see `.nvmrc`). From the theme folder:

- `npm ci` installs dependencies
- `npm run dev` starts the webpack dev server on port 9000, which the theme loads assets from when `WP_ENV` is `development`. It also writes to `build/`, where blocks load their scripts and styles from.
- `npm run build` builds assets to `build/` for other environments
- `npm run typecheck` type-checks with TypeScript 7
- `npm run lint` fixes lint and formatting issues

TypeScript 7 does not yet have a compiler API, which `ts-loader` and `typescript-eslint` need. As recommended in the [TypeScript 7 release notes](https://devblogs.microsoft.com/typescript/announcing-typescript-7-0/#running-side-by-side-with-typescript-6.0), the `typescript` package is aliased to `@typescript/typescript6` for those tools, and TypeScript 7 is installed as `@typescript/native`, which provides `tsc`. Once TypeScript 7.1 ships an API and those tools support it, the alias can be removed.

#### Custom blocks

Each custom block is a folder in `blocks/`. Copy `blocks/example` to start one:

- `block.json` defines the block and points at its built files in `build/blocks/<name>/`
- `index.tsx` is the editor script, written in TypeScript and JSX
- `render.php` renders the block on the server
- `style.scss` styles the block on the front end and in the editor
- `view.ts` (optional) is front-end JavaScript, loaded through `viewScript` in `block.json`

webpack builds and `functions.php` registers every block folder automatically. `@wordpress/*` and React imports use WordPress's own copies at runtime. Add new blocks to `scripts/fixtures/blocks.html` so they show at `/blocks/` and are covered by CI. See `CLAUDE.md` for theme conventions.

### Development Tools

This template comes with some useful development tools for use within WordPress itself.

#### [Create Block Theme](https://wordpress.org/plugins/create-block-theme/)

Crucial. Save off block themes that you are making in the full site editor to the theme on disk. Add fonts direct from the WordPress backend, including Google Fonts.

#### [Pattern Manager](https://wordpress.org/plugins/pattern-manager/)

Create WordPress patterns from within the WordPress backend. They are automatically saved to the theme on disk, with appropriate metadata and can also be modified in this way.

#### [Fakerpress](https://wordpress.org/plugins/fakerpress/)

Generate a lot of dummy content to see how the website looks and feels with full content in it.

#### [Yoast Duplicate Posts](https://wordpress.org/plugins/duplicate-post/)

Again for creating dummy data, this allows quick duplication of posts to fill things out.

### Updating WordPress and other dependencies

1. Run `composer outdated` to verify any dependencies that require updating.
2. Run `composer bump` to bump minor versions and save results to `composer.json`.
3. Update within `composer.json`. For example the `wordpress` package.
4. Run `composer update`.
5. Commit the result.

### WP-CLI

[WP-CLI](https://wp-cli.org/) is installed in the `wordpress` container.

```

docker compose run wordpress wp <command>

```

Note WP-CLI will not work on the host machine, as WordPress configuration refers to databases within the Docker network, not the host machine.

### Adding WordPress Plugins

Run `docker compose run composer require wp-plugin/plugin-name`.

### Further Documentation

Documentation for Bedrock is available at [https://roots.io/bedrock/docs/](https://roots.io/bedrock/docs/).

## Hosting

### Using Kinsta

The following are instructions on how to setup a new site on Kinsta.

You will need to have "Company developer" or above permissions in order to create a site.

These are exhaustive manual instructions, but should not be required after initial setup.

#### Create a New Site on Kinsta

1. Log in to your Kinsta account and create a new site.
2. Select “Don’t install WordPress” and choose the London data center.
3. The provisioning process will take about 10 minutes.

Once the site is created, you will have both Live and Staging environments. Start with the Live environment, and you can later create a Staging environment as needed.

#### Set Up a Single SSH Key for Kinsta and GitHub

You will use the same SSH key to connect to Kinsta and pull from GitHub.
1. Generate an SSH Key Pair (if you don’t already have one)
2. On your local machine, generate a new SSH key pair:

   ```

   ssh-keygen -t rsa -b 4096 -C "your-email@example.com"

   ```

   This will create two files: id_rsa (private key) and id_rsa.pub (public key).

#### Add the Public SSH Key to Kinsta

1. In Kinsta, navigate to Sites > Live Environment > Info.
2. Under SSH Access, click “Add SSH Key” and paste the contents of the id_rsa.pub file.

#### Add the Public SSH Key to GitHub

1. Go to your GitHub profile > Settings > SSH and GPG keys.
2. Click New SSH key, paste the contents of the id_rsa.pub file, and save it.

#### Configure SSH on Your Local Machine

You need to set up SSH forwarding so that you can connect to Kinsta and GitHub using the same key.

1. Open or create the ~/.ssh/config file and add the following block:
   ```
      Host <your_site>_live
      User <Kinsta SSH username from Live Environment>
      Hostname <Kinsta SSH hostname>
      Port <Kinsta SSH port>
      IdentityFile <path_to_your_private_key>
      ForwardAgent yes

   ```

2. Replace the placeholders (<your_site>, <Kinsta SSH username>, <Kinsta SSH hostname>, <Kinsta SSH port>, <path_to_your_private_key>) with the actual details from the SFTP/SSH section of your Live Environment in Kinsta’s dashboard.

3. Start the SSH agent and add your GitHub SSH key:
   ```
   eval "$(ssh-agent -s)"
   ssh-add ~/.ssh/id_rsa
   ```

#### Log into the Kinsta Live Environment

1. SSH into the Live environment with the following command:
`ssh <your_site>_live`

2. Test the GitHub SSH connection by running:
`ssh -T git@github.com`

After accepting the host authenticity, you should see a confirmation message from GitHub.

#### Deploy the WordPress Site from GitHub

1. Remove the Existing public Directory
Once logged into the Live environment, remove the default public directory: `rm -r public`

2. Clone Your WordPress Theme from GitHub
`git clone git@github.com:<your_repo>.git public`

3. Install Dependencies with Composer
Navigate to the public directory and install the dependencies:
`cd public composer install --no-dev`

4. Set Up the .env File
Copy the .env.example file to .env: 
`cp .env.example .env`

   Modify the .env file as per the <a href="https://roots.io/bedrock/docs/environment-variables/">Bedrock documentation</a>. Update the database and other environment details
   You can find database credentials in the Kinsta dashboard under Database Access.
   Set the WP_HOME to http://localhost.


5. Install WordPress

   Run the following command to install WordPress and create the necessary database tables:

   ```
   wp core install --url=<Kinsta Primary Domain URL> --title=<Site Title> --admin_user=<Admin Username> --admin_email=<Admin Email>
   ```

   This command will output the password for the new admin user. Save this password for later.

6. Ask Kinsta support (via Intercom chat) to update the NGINX configuration to point to public/web.

7. Follow Kinsta’s guide to point your domain to the Kinsta environment.

8. Set Up SSL and Force HTTPS

	•	Go to the Tools section in the Kinsta dashboard for the live environment.

	•	Generate a new Let’s Encrypt SSL certificate.

	•	Enable Force HTTPS by selecting “Force all traffic to the primary domain”.

9. In the Kinsta Domains section, make sure your primary domain is selected and marked as primary.
   

### Setting Up Secrets for GitHub Action for Deployment to Kinsta

1. You can use the same SSH key you generated for both Kinsta and this repository's GitHub Actions deploy workflow.

2. Ensure the public key (id_rsa.pub) has been added to the SSH Access section for the Live environment in the Kinsta dashboard (as shown in the previous steps).

3. Add the SSH Key as a Secret in GitHub

	•	In your repository, go to Settings > Secrets and variables > Actions.

	•	Click New repository secret and create the following secrets:

	•	SSH_PRIVATE_KEY: Paste the contents of your id_rsa (private key).

	•	SSH_USER_HOST: Enter the username and hostname from Kinsta’s SFTP/SSH details

	•	SSH_PORT: Enter the SSH port number from Kinsta’s SFTP/SSH details

