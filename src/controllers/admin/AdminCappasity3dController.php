<?php
/**
{LICENSE_PLACEHOLDER}
*/

require dirname(__FILE__) . '/../../vendor/autoload.php';

/**
 * Class AdminCappasity3dController
 */
class AdminCappasity3dController extends ModuleAdminController
{
    /**
     * Request params
     */
    const REQUEST_PARAM_TOKEN = 'token';
    const REQUEST_PARAM_PAGE = 'page';
    const REQUEST_PARAM_QUERY = 'query';
    const REQUEST_PARAM_VERIFY_TOKEN = 'verifyToken';
    const REQUEST_PARAM_CHALLENGE = 'challenge';

    /**
     * @var CappasityClient
     */
    protected $client;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $dbManager = new CappasityManagerDatabase(Db::getInstance(), _DB_PREFIX_, _MYSQL_ENGINE_);

        $this->client = new CappasityClient('{VERSION_PLACEHOLDER}');
        $this->accountManager = new CappasityManagerAccount($this->client, $this->module);
        $this->dbManager = $dbManager;
        $this->fileManager = new CappasityManagerFile($this->client, $dbManager);
        $this->playerManager = new CappasityManagerPlayer($this->module);
        $this->syncManager = new CappasityManagerSync($this->client, $dbManager, $this->module);

        if (Tools::getValue(self::REQUEST_PARAM_TOKEN, null) === null) {
            return $this->handleSync();
        }
    }

    /**
     *
     */
    public function handleSync()
    {
        try {
            error_reporting(0);
            ignore_user_abort(true);
            set_time_limit(0);
        } catch (Exception $e) {
            $this->client->sentry->captureException($e, array(
                'level' => 'error',
                'extra' => array(
                    'code' => 'E_FAILED_SETTINGS'
                )
            ));
        }

        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    echo Tools::safeOutput($this->handleChallenge());
                    break;
                case 'POST':
                    echo Tools::safeOutput($this->handleProducts());
                    break;
            }
        } catch (Exception $e) {
            $this->client->sentry->captureException($e, array(
                'level' => 'error',
                'extra' => array(
                    'code' => 'E_FAILED_SYNC'
                )
            ));
        }

        die();
    }

    /**
     * @return string
     */
    public function handleChallenge()
    {
        $verifyToken =  Tools::getValue(self::REQUEST_PARAM_VERIFY_TOKEN, null);
        $challenge = Tools::getValue(self::REQUEST_PARAM_CHALLENGE, null);

        if ($verifyToken === null || $challenge === null) {
            return '';
        }

        if ($this->syncManager->hasTask($verifyToken)) {
            return $challenge;
        }

        return '';
    }

    /**
     * @return string
     */
    public function handleProducts()
    {
        $input = Tools::file_get_contents('php://input');
        $verifyToken =  Tools::getValue(self::REQUEST_PARAM_VERIFY_TOKEN, null);

        if ($verifyToken === null) {
            return '';
        }

        if ($this->syncManager->hasTask($verifyToken) === false) {
            return '';
        }

        if (array_key_exists('HTTP_CONTENT_ENCODING', $_SERVER) === true
            && $_SERVER['HTTP_CONTENT_ENCODING'] === 'gzip'
        ) {
            $input = gzdecode($input);
        }

        if ($input === false) {
            return '';
        }

        $products = Tools::jsonDecode($input, true);

        if ($products === null) {
            return '';
        }

        $this->syncManager->sync($products);
        $this->syncManager->removeTask($verifyToken);

        return count($products);
    }

    /**
     * @TODO change method to proccesActionName
     * @return string
     */
    public function initContent()
    {
        $token = $this->accountManager->getToken();
        $alias = $this->accountManager->getAlias();

        $page = (int)Tools::getValue(self::REQUEST_PARAM_PAGE, 1);
        $query = Tools::getValue(self::REQUEST_PARAM_QUERY, '');

        try {
            $filesCollection = $this->fileManager->files($token, $alias, $query, $page, 12);
        } catch (Exception $e) {
            return $this->module->displayError(
                $this->module->l('Please renew your account settings')
            );
        }

        $this->context->smarty->assign(
            array(
                'action' => $this->context->link->getAdminLink('AdminCappasity3d', true),
                'files' => CappasityModelFile::getCollection(
                    $filesCollection['data'],
                    $this->playerManager->getSettings()
                ),
                'pagination' => $filesCollection['meta'],
                'alias' => $alias,
                'query' => $query,
            )
        );

        die($this->context->smarty->fetch($this->getTemplatePath() . 'list.tpl'));
    }

    /**
     * @return string
     */
    public function processStatus()
    {
        die((string)$this->dbManager->getSyncTasksCount());
    }
}
