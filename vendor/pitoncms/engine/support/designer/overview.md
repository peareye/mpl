# Overview

PitonCMS was designed to be _designer friendly_, giving great flexibility to the designer to build creative websites without requiring additional custom backend development.

Page structures, custom data, settings, are all easily extensible by modifying project JSON **Definition** files. These files can be checked into version control and pushed to other environments to promote layout and data changes without having to modify remote databases or push code.

## Site Structure
When you install PitonCMS/Piton from composer, a project directory is created containing this structure.

**`app/`**
For any custom code extensions to core PitonCMS.

**`cache/`**
Holds cached files for Twig, the router, and any other cached content. The `cache/` directory can be safely emptied at any time, and should be emptied in production with each deployment.

**`config/`**
The **config** folder contains important site configuration files that are set as part of the site creation.
* `config/config.local.php` Site settings for the local environment, and overrides default settings. You can copy `vendor/pitoncms/engine/config/config.default.php` to `config/config.local.php` and update any settings you wish to modify.
* `config/dependencies.php.example` Overrides core PitonCMS Dependency Injection Container (DIC). (See PitonCMS for Developers.)
* `config/routes.php.example` Overrides and extends front end routes. (See PitonCMS for Developers.)

**`docker/`** Docker configuration files if using Docker for local development.

**`logs/`** Contains application log files. The `logs/` directory can be safely emptied at any time.

**`public/`** The **public** folder is your web Document Root, and most files are accessible from the web.
* `public/admin/` Links to PitonCMS Administration assets.
* `public/assets/` For frontend public CSS, JS, and static IMG assets.
* `public/media/` Stores uploaded media files.
* `public/.htaccess` Configure custom Apache runtime settings here.
* `public/index.php` Entry point for all PitonCMS web requests. The `.htaccess` file rewrites web requests to use `index.php`.
* `public/install.php` Database installer script. This file self-deletes after first use, and should **NOT** be committed to version control.

**`structure/`** Contains all HTML and JSON Definition files for your website.

**`vendor/`** Contains project dependenices, managed by Composer.

**`.gitignore`** Update to have git ignore any files from version control.

**`composer.json`** Defines project dependencies.

**`composer.lock`** Defines exact state of all dependencies, be sure to commit to your project version control.

**`.htaccess`** The **.htaccess** file at the project root simply denies web access above the public Document Root.

**`docker-compose.yml`** Defines the Docker Compose development image. Also has your development database user and password. If you are not using Docker for development, delete the `docker-compose.yml` and the `docker/` folder from the project.

## JSON Definition Files
You can easily customize many aspects of PitonCMS by creating and editing JSON configuration files in `structure/` directory. Be sure to commit these JSON files to version control. It may also be helpful to use an editor that supports JSON to help with valid JSON.

## Composer
All dependencies are managed by [Composer](https://getcomposer.org/). PitonCMS is also built of separate Composer projects to allow easy upgrades.

Composer requires that you have PHP available from the command line on your local machine to install. However, because Composer checks the current environment to determine dependency version, be sure to run all updates from within your Docker or AMP development server - not your host machine terminal. Your Docker or AMP development environment may be running a different version of PHP, and should be closer to your actual production environment.

## Docker
If you have Docker Desktop running on your development computer, you can use the prebuilt Docker image that comes with PitonCMS. See [PitonCMS Readme](https://github.com/PitonCMS/Piton) to get started with Docker.

## Twig
PitonCMS uses Twig to render HTML templates. You can read more about [Twig](https://twig.symfony.com/).