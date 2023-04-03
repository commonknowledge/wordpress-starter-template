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
