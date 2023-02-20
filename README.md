# WordPress Starter

A starter repository for [WordPress](https://wordpress.org) websites for organisers, campaigns and anyone else who finds it useful.

This folder structure uses the [Bedrock](https://roots.io/bedrock/) pattern, a modern WordPress stack.

## Requirements

- Docker [installed](https://docs.docker.com/install/)
- Node.js - LTS version, 14.17.1. If using NVM, you can use `nvm use` in this directory.
- PHP and Composer installed locally. If not, you can use them in containers provided, prefixing all Composer commands below with `docker-compose run composer <command>`

## Run locally

1. [Generate a repository](https://github.com/commonknowledge/groundwork-starter-template/generate) from this template
2. If you have PHP and Composer installed locally you can run from this directory `composer install`. Otherwise run `composer install` via Docker with `docker-compose run composer install`.
3. Copy `.env.example` to `.env`, running `cp .env.example .env`. The example file contains variables required for this Docker Compose setup but modify details appropriately [as per the Bedrock documentation](https://roots.io/bedrock/docs/environment-variables/) as required.
4. Start up all containers in the background with:

```
docker-compose up -d
```

6. You can access the site at [http://localhost:8082](http://localhost:8082). You can view logs by running `docker-compose logs`.

# Full development documentation

## MySQL

MySQL is provided by MariaDB.

MySQL data is persisted between Docker Compose ups and downs. To start again, you can run `composer run-script clear-database`.

## WP-CLI

[WP-CLI](https://wp-cli.org/) is installed in the `wordpress` container.

```

docker-compose run wordpress wp --allow-root <command>

```

Note WP-CLI will not work on the host machine, as WordPress configuration refers to databases within the Docker network, not the host machine.

## Updating Wordpress

Update `composer.json` with the version you want for `roots/wordpress` and then run:

```

docker-compose run composer update roots/wordpress

```

## Adding WordPress Plugins

Run `docker-compose run composer require wpackagist-plugin/plugin-name`.

## Further Documentation

Documentation for Bedrock is available at [https://roots.io/bedrock/docs/](https://roots.io/bedrock/docs/).

## Hosting

### Using Kinsta

The following are instructions on how to setup a new site on Kinsta.

You will need to have "Company developer" or above permissions in order to create a site.

These are exhaustive manual instructions, but should not be required after initial setup.

1. Create [a new site on Kinsta](https://kinsta.com/knowledgebase/new-site/). You want to select "Don't install WordPress" and choose the London data centre. It will provision after around ten minutes.
2. On Kinsta we have a Live environment and a staging environment. From Sites on the side bar, enter your site, click on "Change environment", then Staging environment and then "Create a staging environment". This will also take a few minutes to complete.
3. For the next steps, you will need to [add your SSH key to Kinsta](https://kinsta.com/feature-updates/add-ssh-keys/). This is to allow you to log into Kinsta over SSH.
4. You will also need to [add a SSH key to GitHub](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-github-account). This is so you can check out the theme from GitHub over SSH when logged into Kinsta.
5. Kinsta needs to be set up to forward you GitHub SSH key to it when you connect to it over SSH and checkout the theme with Git. You can look up the [precise SSH details on Kinsta under the "SFTP/SSH" in the staging and live environments in their admin panel.](https://my.kinsta.com/sites/details/2bb09ffb-dd14-473a-9412-137d70754eb7/live?idCompany=c67ca3d2-36ad-4de5-89c3-0c9ff8dc2481) You can then add them to a `~/.ssh/config` block to look something like this. Note the IP address of live and staging only differs with regard to port.
   ```
    host <your site>_staging
        User <see "SFTP/SSH" details for Staging Environment>
        Hostname <see "SFTP/SSH" details for Staging Environment>
        Port <see "SFTP/SSH" details for Staging Environment>
        IdentityFile <Location on your local machine of the SSH key>
        ForwardAgent yes
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
9. Remove the `public` directory with `rm -r public`. Clone the code into the `public/` directiory: `git clone git@gitlab.com:commonknowledge/nurses-united/website.git public`.
10. `cd public; composer install`.
11 Return to the `public` directory, `cd ~/public`. If this is the first time, copy `.env.example` to `.env` with `cp .env.example .env` and modify details appropriately [as per the Bedrock documentation](https://roots.io/bedrock/docs/environment-variables/). You can use Vim on the server. The details of the database are on the Kinsta admin panel under the site itself then "Database Access". The `WP_HOME` can be `http://localhost`.
12. You need to create database tables for WordPress. Run `wp core install --url=<URL from Primary domain in Kinsta admin panel> --title=<site name> --admin_user=<desired username> --admin_email=<desired password>`. This will output the password for the user you have just created to the terminal. Save it for when you need to login.
13. Ask Kinsta to update NGINX to point at `public/web` on Intercom chat inside the Kinsta control panel. Note, not `public/current/web`, which is the directory if you are deploy Bedrock with [Trellis](https://github.com/roots/trellis). This installation does not use Trellis.
14. You can now point the domains to this installation of WordPress following [Kinsta's instructions](https://kinsta.com/knowledgebase/add-domain/).
15. Head to Tools for the site in the staging environment. Use "SSL certificate" to generate a new Let's Encrypt SSL certificate and wait for this to complete.
16. Still on the tools page, setup Force HTTPS by clicking on Modify and dollowing your nose, selecting "Force all traffic to the primary domain" along the way. Edit the `.env` file created in step 12, to have `WP_HOME` include `https` not `http`.
17. Finally on the tools page upgrade to PHP 7.4 by changing the "PHP Engine" dropdown.
18. For staging the cache should be turned off and WordPress debugging should be turned on.
19. In Domains, change the DNS pointed domain to your primary one selecting "Make primary"
20. WordPress should now work at the site URL. You can login to the administration dashboard with the password you just created.
21. Repeat steps 7 through 21, but on the production environment.
