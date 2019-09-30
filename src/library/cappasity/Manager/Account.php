<?php
/**
{LICENSE_PLACEHOLDER}
*/

/**
 * Class CappasityManagerAccount
 */
class CappasityManagerAccount extends CappasityManagerAbstractManager
{
    /**
     *
     */
    const SETTINGS_SUBMIT_KEY = 'submitCappasityAccountSettings';

    /**
     * Account alias
     */
    const SETTING_ALIAS = 'cappasityAccountAlias';

    /**
     *
     */
    const SETTING_STATUS_CACHED_AT = 'cappasityAccountStatusCachedAt';

    /**
     * Account token
     */
    const SETTING_TOKEN = 'cappasityAccountToken';

    /**
     * Account plan
     */
    const SETTING_IS_PAID = 'cappasityAccountIsPaid';

    /**
     * @var array
     */
    protected $settings = array(
        self::SETTING_ALIAS,
        self::SETTING_STATUS_CACHED_AT,
        self::SETTING_TOKEN,
        self::SETTING_IS_PAID,
    );

    /**
     * @var CappasityClient
     */
    protected $client;

    /**
     * @var \Cappasity3d
     */
    protected $module;

    /**
     * Account constructor.
     * @param CappasityClient $client
     * @param Cappasity3d $module
     */
    public function __construct(CappasityClient $client, Cappasity3d $module)
    {
        $this->client = $client;
        $this->module = $module;
    }

    /**
     * @param $token
     * @param CappasityModelAccount $account
     */
    public function updateSettings($token, CappasityModelAccount $account)
    {
        $this->setSetting(self::SETTING_TOKEN, $token);
        $this->setSetting(self::SETTING_ALIAS, $account->getAlias());
        $this->setSetting(self::SETTING_IS_PAID, $account->isFree() !== true);
        $this->setSetting(self::SETTING_STATUS_CACHED_AT, time());
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getSetting(self::SETTING_TOKEN);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->getSetting(self::SETTING_ALIAS);
    }

    /**
     * @return bool
     */
    public function isAccountPaid()
    {
        $cachedAt = (int)$this->getSetting(self::SETTING_STATUS_CACHED_AT);

        // 3 hours cache
        if ((time() - $cachedAt) > (60 * 60 * 3)) {
            $token = $this->getSetting(self::SETTING_TOKEN);

            try {
                $account = $this->info($token);
                $this->updateSettings($token, $account);
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $this->getSetting(self::SETTING_IS_PAID);
    }

    /**
     * @param $token
     * @return CappasityModelAccount
     */
    public function info($token)
    {
        $response = $this->client->get('users/me', array(), $token);

        $alias = $response['data']['attributes']['alias'];
        $plan = $response['data']['attributes']['plan'];

        // provide authentication context to the user
        $this->client->sentry->user_context(array(
            'id' => $alias,
            'plan' => $plan
        ));

        $account = new CappasityModelAccount($plan, $alias);

        return $account;
    }

    /**
     * @param HelperForm $helper
     * @return string
     */
    public function renderSettingsForm(HelperForm $helper)
    {
        $alias = $this->getAlias();
        $form = array();

        $form['legend'] = array(
            'title' => $this->module->l('Account settings'),
            'icon' => 'icon-cogs',
        );

        if ($alias) {
            $form['description'] = $this->module->l('Logged as') . " `{$alias}`";
        } else {
            $form['description'] = $this->module->l('You can generate your API token on your account/security page.')
                . ' '
                . $this->module->l('No account?')
                . ' '
                . '<a target="_blank" href="https://3d.cappasity.com/register?aff=prestashop">'
                . $this->module->l('Sign up for free!')
                . '</a>';
        }

        $form['input'] = array(
            array(
                'type' => 'password',
                'required' => true,
                'name' => self::SETTING_TOKEN,
                'desc' => $this->module->l('Enter your Cappasity account token'),
                'label' => $this->module->l('Token'),
            ),
        );

        $form['submit'] = array(
            'title' => $this->module->l('Save'),
        );

        return $helper->generateForm(array(array('form' => $form)));
    }
}
