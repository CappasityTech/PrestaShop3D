# Overview
3D module for PrestaShop - www.prestashop.com   
Powered by Cappasity 

(c) 2014-2022 Cappasity Inc.  
License https://cappasity.com/eula_modules/

This source code is available for educational purposes only.

# Documentation

- [Prestashop configuration](prestashop-config.md)
- [Troubleshooting](troubleshooting.md)

# Development

## Overrides

If you edit files for overrides don't forget to add a migration with the following lines:

```php
<?php
/**
{LICENSE_PLACEHOLDER}
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_X_X_X($module)
{
    return $module->uninstallOverrides() && $module->installOverrides();
}
```

## Build module

```sh
# 0.0.0 - version of module
./scripts/prepare.sh 0.0.0
```

## Setup local PrestaShop server

```sh
./scripts/development-server.sh -v 1.7.1.2
```

Option | Default | Description
--- | --- | ---
`-v` | `1.6.1.18` | Version of PrestaShop from docker hub

## Go to the admin panel
By, default, you can find admin panel at [http://localhost:8080/dev-admin](http://localhost:8080/dev-admin)
Go to `Menu > Modules > Modules & Services` to upload the module build.

In case of alternative configuration:
* Explore `docker-compose.yml` for the environment variables for `presta`
* If it's {PS_INSTALL_AUTO} value is not 1, then go visit http://{PS_DOMAIN}/{PS_FOLDER_INSTALL} to install prestashop to the server 
* Go to http://{PS_DOMAIN}/{PS_FOLDER_ADMIN} to the admin panel
* Go to http://{PS_DOMAIN} for the storefront
