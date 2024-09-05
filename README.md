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

6. You can access the site at [http://localhost:8082](http://localhost:8082). You can install WordPress automatically by running 
```
docker compose run wordpress wp core install --url=http://localhost:8082 --title='WordPress Starter Template' --admin_user=ck_admin --admin_email=hello@commonknowledge.coop
```
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
2. Run `composer bump` to bump minor versions and save results to `composer.json`.
3. Update within `composer.json`. For example the `wordpress` package.
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

1. You can use the same SSH key you generated for both Kinsta and this repository's GitHub Actions workflow.

2. Add the SSH Key to Kinsta

	•	Ensure the public key (id_rsa.pub) has been added to the SSH Access section for the Live environment in the Kinsta dashboard (as shown in the previous steps).

3. Add the SSH Key as a Secret in GitHub

	•	In your repository, go to Settings > Secrets and variables > Actions.

	•	Click New repository secret and create the following secrets:

	•	SSH_PRIVATE_KEY: Paste the contents of your id_rsa (private key).

	•	SSH_USER_HOST: Enter the username and hostname from Kinsta’s SFTP/SSH details

	•	SSH_PORT: Enter the SSH port number from Kinsta’s SFTP/SSH details

