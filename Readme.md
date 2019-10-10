# Overview
3D module for PrestaShop - www.prestashop.com   
Powered by Cappasity REST API

(c) 2014-2019 Cappasity Inc.  
License https://cappasity.com/eula_modules/

This source code is available for educational purposes only.

# Documentation

[https://cappasitytech.github.io/PrestaShop3D/](https://cappasitytech.github.io/PrestaShop3D/)

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
