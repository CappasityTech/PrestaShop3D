<?php
/**
{LICENSE_PLACEHOLDER}
*/

/**
 * Class CappasityManagerDatabase
 */
class CappasityManagerDatabase
{
    /**
     *
     */
    const TABLE_CAPPASITY = 'cappasity3d';

    /**
     *
     */
    const TABLE_SYNC_TASKS = 'cappasity3d_sync';

    /**
     * @var Db
     */
    protected $db;

    /**
     * @var string
     */
    protected $engine;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Database constructor.
     * @param Db $db
     * @param string $prefix
     * @param string $engine
     */
    public function __construct(Db $db, $prefix, $engine)
    {
        $this->db = $db;
        $this->engine = pSQL($engine);
        $this->prefix = pSQL($prefix);
    }

    /**
     * @return string
     */
    protected function getCappasityTableName()
    {
        return $this->prefix . self::TABLE_CAPPASITY;
    }

    /**
     * @return string
     */
    protected function getSyncTasksTableName()
    {
        return $this->prefix . self::TABLE_SYNC_TASKS;
    }

    /**
     * @return bool
     */
    public function setUp()
    {
        $sql = array();
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$this->getCappasityTableName()}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` INT UNSIGNED NOT NULL,
                `variant_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `cappasity_id` VARCHAR(1024) NOT NULL,
                `from_hook` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `from_sync` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `from_pick` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                UNIQUE INDEX(`product_id`, `variant_id`),
                PRIMARY KEY(`id`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;";
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$this->getSyncTasksTableName()}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `verification` VARCHAR(1024) NOT NULL,
                `created` DATETIME NOT NULL,
                PRIMARY KEY(`id`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;";

        foreach ($sql as $query) {
            if ($this->db->execute($query) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function cleanUp()
    {
        $sql = array();
        $sql[] = "DROP TABLE IF EXISTS `{$this->getCappasityTableName()}`;";
        $sql[] = "DROP TABLE IF EXISTS `{$this->getSyncTasksTableName()}`;";

        foreach ($sql as $query) {
            if ($this->db->execute($query) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \Exception
     */
    public function getProductsAndVariants($limit = 50, $offset = 0)
    {
        $limit = (int)$limit;
        $offset = (int)$offset;

        $query = "SELECT
                  COALESCE(cappasity_id, NULL) AS cappasity_id,
                  id_product,
                  NULL AS id_product_attribute,
                  reference,
                  upc,
                  ean13
                FROM {$this->prefix}product
                LEFT JOIN {$this->getCappasityTableName()}
                  ON (product_id = id_product AND variant_id = 0)
                WHERE
                  upc > '' OR ean13 > '' OR reference > ''

                UNION ALL

                SELECT
                  COALESCE(cappasity_id, NULL) AS cappasity_id,
                  id_product,
                  id_product_attribute,
                  reference,
                  upc,
                  ean13
                FROM {$this->prefix}product_attribute
                LEFT JOIN {$this->getCappasityTableName()}
                  ON (product_id = id_product AND variant_id = id_product_attribute)
                WHERE
                  upc > '' OR ean13 > '' OR reference > ''

                ORDER BY
                  id_product,
                  id_product_attribute
                LIMIT {$limit}
                OFFSET {$offset}";

        $products = $this->db->ExecuteS($query);

        if (is_array($products) === false) {
            throw new \Exception('Can not get products from database');
        }

        return $products;
    }

    /**
     * @param $verification
     * @return mixed
     */
    public function createSyncTask($verification)
    {
        return $this->db->insert(self::TABLE_SYNC_TASKS, array(
            'verification' => pSQL($verification),
            'created' => date('c'),
        ));
    }

    /**
     * @return mixed
     */
    public function removeSyncTasks()
    {
        return $this->db->delete(self::TABLE_SYNC_TASKS);
    }

    /**
     * @return int
     */
    public function getSyncTasksCount()
    {
        $count = $this->db->getValue(
            "SELECT count(`id`) FROM `{$this->getSyncTasksTableName()}`"
        );

        return (int)$count;
    }

    /**
     * @param $verification
     * @return bool
     */
    public function hasSyncTask($verification)
    {
        $verification = pSQL($verification);

        return $this->db->getRow(
            "SELECT `id` FROM `{$this->getSyncTasksTableName()}` WHERE `verification` = '{$verification}'"
        );
    }

    /**
     * @param $verification
     * @return mixed
     */
    public function removeSyncTask($verification)
    {
        $verification = pSQL($verification);

        return $this->db->delete(self::TABLE_SYNC_TASKS, "verification = '{$verification}'");
    }

    public function getCappasity(array $cond)
    {
        $productId = array_key_exists('productId', $cond) ? (int)$cond['productId'] : null;

        if ($productId === null) {
            throw new Exception('missing product id');
        }

        $query = "SELECT * FROM `{$this->getCappasityTableName()}` WHERE `product_id` = {$productId}";

        if (array_key_exists('variantId', $cond) === true) {
            $variantId = $cond['variantId'] === null ? null : (int)$cond['variantId'];
            $query .= ' AND `variant_id` ' . ($variantId === null ? ' = 0 ' : " = {$variantId}");
        }

        $data = $this->db->ExecuteS($query);

        // @todo add log if data false
        if (count($data) === 0 || $data === false) {
            return array();
        }

        return $data;
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function removeCappasity(array $cond)
    {
        $cond['product_id'] = array_key_exists('product_id', $cond) ? (int)$cond['product_id'] : null;
        $cond['variant_id'] = array_key_exists('variant_id', $cond) ? (int)$cond['variant_id'] : 0;

        if (array_key_exists('cappasity_id', $cond)) {
            $cond['cappasity_id'] = pSQL($cond['cappasity_id']);
        }

        if ($cond['product_id'] === null) {
            throw new Exception('missing product id');
        }

        $query = " `product_id` = {$cond['product_id']} AND `variant_id` = {$cond['variant_id']} ";

        if (array_key_exists('cappasity_id', $cond)) {
            $query .= sprintf(" AND `cappasity_id` = '%s' ", $cond['cappasity_id']);
        }

        return $this->db->delete(self::TABLE_CAPPASITY, $query);
    }

    /**
     * @param integer $productId
     * @param string $cappasityId
     * @return mixed
     */
    public function upsertCappasity(array $cond, array $data)
    {
        // set defaults
        $cond['product_id'] = array_key_exists('product_id', $cond) ? (int)$cond['product_id'] : null;
        $cond['variant_id'] = array_key_exists('variant_id', $cond) ? (int)$cond['variant_id'] : 0;

        $data['cappasity_id'] = array_key_exists('cappasity_id', $data) ? pSQL($data['cappasity_id']) : null;
        $data['from_hook'] = array_key_exists('from_hook', $data) ? (int)$data['from_hook'] : 0;
        $data['from_sync'] = array_key_exists('from_sync', $data) ? (int)$data['from_sync'] : 0;
        $data['from_pick'] = array_key_exists('from_pick', $data) ? (int)$data['from_pick'] : 0;

        // validate
        if ($cond['product_id'] === null) {
            throw new Exception('missing product id');
        }

        if ($data['cappasity_id'] === null) {
            throw new Exception('missing cappasity id');
        }

        if ($data['from_hook'] === 0 && $data['from_sync'] === 0 && $data['from_pick'] === 0) {
            throw new Exception('require `from` attribute');
        }

        // do things
        $sqlCond = " `product_id` = {$cond['product_id']} AND `variant_id` = {$cond['variant_id']} ";

        $exists = $this->db->getRow("SELECT * FROM `{$this->getCappasityTableName()}` WHERE {$sqlCond}");

        if ($exists === false) {
            return $this->db->insert(self::TABLE_CAPPASITY, array_merge($cond, $data));
        }

        return $this->db->update(self::TABLE_CAPPASITY, $data, $sqlCond);
    }
}
