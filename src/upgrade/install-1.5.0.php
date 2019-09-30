<?php
/**
{LICENSE_PLACEHOLDER}
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_5_0($module)
{
    $table = _DB_PREFIX_ . CappasityManagerDatabase::TABLE_CAPPASITY;
    $alter = "ALTER TABLE `{$table}` "
      . " ADD `variant_id` INT UNSIGNED NOT NULL DEFAULT 0, "
      . " ADD `from_hook` TINYINT UNSIGNED NOT NULL DEFAULT 0, "
      . " ADD `from_sync` TINYINT UNSIGNED NOT NULL DEFAULT 0, "
      . " ADD `from_pick` TINYINT UNSIGNED NOT NULL DEFAULT 0, "
      . " ADD UNIQUE INDEX(`product_id`, `variant_id`) ";

    $module->uninstallOverrides();
    $module->installOverrides();

    return Db::getInstance()->execute($alter);
}
