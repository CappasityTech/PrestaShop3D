<?php
/**
{LICENSE_PLACEHOLDER}
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_8_5($module)
{
    return $module->uninstallOverrides() && $module->installOverrides();
}
