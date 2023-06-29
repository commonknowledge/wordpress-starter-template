# WordPress Starter

A starter repository for [WordPress](https://wordpress.org) websites for organisers, campaigns and anyone else who finds it useful.

This folder structure uses the [Bedrock](https://roots.io/bedrock/) pattern, a modern WordPress stack.

## Requirements

- Docker [installed](https://docs.docker.com/install/)
- PHP and Composer installed locally. If not, you can use them in containers provided, prefixing all Composer commands below with `docker compose run composer <command>`

## Run locally

1. [Generate a repository](https://github.com/commonknowledge/groundwork-starter-template/generate) from this template
2. If you have PHP and Composer installed locally you can run from this directory `composer install`. Otherwise run `composer install` via Docker with `docker compose run composer install`.
3. Copy `.env.example` to `.env`, running `cp .env.example .env`. The example file contains variables required for this Docker Compose setup but modify details appropriately [as per the Bedrock documentation](https://roots.io/bedrock/docs/environment-variables/) as required.
4. Start up all containers with:

```
docker compose up
```

6. You can access the site at [http://localhost:8082](http://localhost:8082). You can install WordPress automatically by running `docker compose run wordpress wp --allow-root core install --url=http://localhost:8082 --title='WordPress Starter Template' --admin_user=ck_admin --admin_email=hello@commonknowledge.coop`.

## Development Documentation

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
2. Run `composer bump` to bump minor versions.
3. Update withing `composer.json`. For example the `wordpress` package.
4. Run `composer update`.
5. Commit the result.

### WP-CLI

[WP-CLI](https://wp-cli.org/) is installed in the `wordpress` container.

```

docker compose run wordpress wp --allow-root <command>

```

Note WP-CLI will not work on the host machine, as WordPress configuration refers to databases within the Docker network, not the host machine.

### Adding WordPress Plugins

Run `docker compose run composer require wpackagist-plugin/plugin-name`.

### Further Documentation

Documentation for Bedrock is available at [https://roots.io/bedrock/docs/](https://roots.io/bedrock/docs/).

## Hosting

### Using Kinsta

The following are instructions on how to setup a new site on Kinsta.

You will need to have "Company developer" or above permissions in order to create a site.

These are exhaustive manual instructions, but should not be required after initial setup.

1. Create [a new site on Kinsta](https://kinsta.com/knowledgebase/new-site/). You want to select "Don't install WordPress" and choose the London data centre. It will provision after around ten minutes.
2. On Kinsta we have a Live environment and a staging environment. Start with the Live environment for a new build. Then after, you can create a staging environment as needed.
3. For the next steps, you will need to [add your SSH key to Kinsta](https://kinsta.com/feature-updates/add-ssh-keys/). This is to allow you to log into Kinsta over SSH.
4. You will also need to [add a SSH key to GitHub](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-github-account). This is so you can check out the theme from GitHub over SSH when logged into Kinsta.
5. Kinsta needs to be set up to forward you GitHub SSH key to it when you connect to it over SSH and checkout the theme with Git. You can look up the precise SSH details on Kinsta under the "SFTP/SSH" in the live environment section of the admin panel. You can then add them to a `~/.ssh/config` block to look something like this.
   ```
    host <your site>_live
        User <see "SFTP/SSH" details for Live Environment>
        Hostname <see "SFTP/SSH" details for Live Environment>
        Port <see "SFTP/SSH" details for Live Environment>
        IdentityFile <Location on your local machine of the SSH key>
        ForwardAgent yes
   ```
6. Add you GitHub SSH key to `ssh-agent` [following these instructions](https://help.github.com/en/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent#adding-your-ssh-key-to-the-ssh-agent). The command will be something like `ssh-add ~/.ssh/gitlab_key_rsa`
7. We are going to begin with the staging environment. SSH onto Kinsta staging environment using this command `ssh nurses_united_staging`.
8. Test GitHub works by running the command `ssh -T git@github.com`. After accepting the authenticity of the host you should see a friendly message from GitHub.
9. Remove the `public` directory with `rm -r public`. Clone the code into the `public/` directiory: `git clone git@github.com:commonknowledge/pluto-press.git public`.
10. `cd public && composer install`.
11 Return to the `public` directory, `cd ~/public`. If this is the first time, copy `.env.example` to `.env` with `cp .env.example .env` and modify details appropriately [as per the Bedrock documentation](https://roots.io/bedrock/docs/environment-variables/). You can use Vim on the server. The details of the database are on the Kinsta admin panel under the site itself then "Database Access". The `WP_HOME` can be `http://localhost`.
12. You need to create database tables for WordPress. Run `wp core install --url=<URL from Primary domain in Kinsta admin panel> --title=<site name> --admin_user=<desired username> --admin_email=<desired password>`. This will output the password for the user you have just created to the terminal. Save it for when you need to login.
13. Ask Kinsta to update NGINX to point at `public/web` on Intercom chat inside the Kinsta control panel. Note, not `public/current/web`, which is the directory if you are deploy Bedrock with [Trellis](https://github.com/roots/trellis). This installation does not use Trellis.
14. You can now point the domains to this installation of WordPress following [Kinsta's instructions](https://kinsta.com/knowledgebase/add-domain/).
15. Head to Tools for the site in the live environment. Use "SSL certificate" to generate a new Let's Encrypt SSL certificate and wait for this to complete.
16. Still on the tools page, setup Force HTTPS by clicking on Modify and dollowing your nose, selecting "Force all traffic to the primary domain" along the way. Edit the `.env` file created in step 12, to have `WP_HOME` include `https` not `http`.
17. In Domains, change the DNS pointed domain to your primary one selecting "Make primary".
18. WordPress should now work at the site URL. You can login to the administration dashboard with the password you just created.
19. Repeat steps 7 through 20, but creating a development environment.
