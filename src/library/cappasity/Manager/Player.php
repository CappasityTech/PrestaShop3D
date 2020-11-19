<?php
/**
{LICENSE_PLACEHOLDER}
*/

/**
 * Class CappasityManagerPlayer
 */
class CappasityManagerPlayer extends CappasityManagerAbstractManager
{
    /**
     *
     */
    const SETTINGS_SUBMIT_KEY = 'submitCappasityPlayerSettings';

    /**
     * @var array
     */
    protected $fieldssets = array(
      array(
          'title' => 'Player options',
          'items' => array('autorun', 'closebutton', 'logo'),
      ),
      array(
          'title' => 'Rotate options',
          'items' => array(
              'autorotate',
              'autorotatetime',
              'autorotatedelay',
              'autorotatedir',
              'hidefullscreen',
              'hideautorotateopt',
              'hidesettingsbtn',
          ),
      ),
      array(
          'title' => 'Zoom options',
          'items' => array('enableimagezoom', 'zoomquality', 'hidezoomopt'),
      ),
      array(
          'title' => 'Window options',
          'items' => array('width', 'height'),
      ),
    );

    /**
     * @var array
     */
    protected $settings = array(
        // core options
        'autorun' => array(
            'type' => 'boolean',
            'default' => 1,
            'paid' => false,
            'description' => 'Auto-start player',
            'name' => 'cappasityPlayerAutorun',
            'enabled' => true,
        ),
        'closebutton' => array(
            'type' => 'boolean',
            'default' => 0,
            'paid' => true,
            'description' => 'Show close button',
            'name' => 'cappasityPlayerClosebutton',
            'enabled' => true,
        ),
        'logo' => array(
            'type' => 'boolean',
            'default' => 1,
            'paid' => true,
            'description' => 'Show logo',
            'name' => 'cappasityPlayerLogo',
            'enabled' => true,
        ),
        // rotate options
        'autorotate' => array(
            'type' => 'boolean',
            'default' => 0,
            'paid' => true,
            'description' => 'Autorotate',
            'name' => 'cappasityPlayerAutoRotate',
            'enabled' => true,
        ),
        'autorotatetime' => array(
            'type' => 'string',
            'default' => 10,
            'paid' => true,
            'description' => 'Autorotate time, seconds',
            'name' => 'cappasityPlayerAutoRotateTime',
            'enabled' => true,
            'validation' => array(
                'method' => 'isFloat',
                'params' => array(
                  'mix' => 2,
                  'max' => 60,
                ),
                'error' => 'Value must be float, min 2, max 60',
            ),
        ),
        'autorotatedelay' => array(
            'type' => 'string',
            'default' => 2,
            'paid' => true,
            'description' => 'Autorotate delay, seconds',
            'name' => 'cappasityPlayerAutoRotateDelay',
            'enabled' => true,
            'validation' => array(
                'method' => 'isFloat',
                'params' => array(
                    'mix' => 1,
                    'max' => 10,
                ),
                'error' => 'Value must be float, min 1, max 10',
            ),
        ),
        'autorotatedir' => array(
            'type' => 'select',
            'values' => array(
                'clockwise' => 1,
                'counter-clockwise' => -1,
            ),
            'default' => 1,
            'paid' => true,
            'description' => 'Autorotate direction',
            'name' => 'cappasityPlayerAutoRotateDirection',
            'enabled' => true,
        ),
        'hidefullscreen' => array(
            'type' => 'boolean',
            'default' => 1,
            'paid' => true,
            'description' => 'Hide fullscreen button',
            'name' => 'cappasityPlayerHideFullScreen',
            'enabled' => true,
        ),
        'hideautorotateopt' => array(
            'type' => 'boolean',
            'default' => 1,
            'paid' => true,
            'description' => 'Hide autorotate button',
            'name' => 'cappasityPlayerHideRotate',
            'enabled' => true,
        ),
        'hidesettingsbtn' => array(
            'type' => 'boolean',
            'default' => 0,
            'paid' => true,
            'description' => 'Hide settings button',
            'name' => 'cappasityPlayerHideSettings',
            'enabled' => true,
        ),
        // zoom options
        'enableimagezoom' => array(
            'type' => 'boolean',
            'default' => 1,
            'paid' => true,
            'description' => 'Enable zoom',
            'name' => 'cappasityPlayerEnableImageZoom',
            'enabled' => true,
        ),
        'zoomquality' => array(
            'type' => 'select',
            'values' => array(
                'SD' => 1,
                'HD' => 2,
            ),
            'default' => 1,
            'paid' => true,
            'description' => 'Zoom quality',
            'name' => 'cappasityPlayerZoomQuality',
            'enabled' => true,
        ),
        'hidezoomopt' => array(
            'type' => 'boolean',
            'default' => 0,
            'paid' => true,
            'description' => 'Hide zoom button',
            'name' => 'cappasityPlayerHideZoomOpt',
            'enabled' => true,
        ),
        // window options
        'width' => array(
            'type' => 'string',
            'default' => '100%',
            'paid' => false,
            'description' => 'Width of embedded window (px or %)',
            'name' => 'cappasityPlayerWidth',
            'enabled' => true,
            'validation' => array(
                'method' => 'isSize',
                'error' => 'Width must be a number of pixels or persents',
            ),
        ),
        'height' => array(
            'type' => 'string',
            'default' => '600px',
            'paid' => false,
            'description' => 'Height of embedded window (px or %)',
            'name' => 'cappasityPlayerHeight',
            'enabled' => true,
            'validation' => array(
                'method' => 'isSize',
                'error' => 'Height must be a number of pixels or persents',
            ),
        ),
    );

    /**
     * @var string
     */

    protected $aiScriptUrl = 'https://{API_HOST_PLACEHOLDER}/api/player/cappasity-ai';

    /**
     * @var \Cappasity3d
     */
    protected $module;

    /**
     * Player constructor.
     * @param Cappasity3d $module
     */
    public function __construct(Cappasity3d $module)
    {
        $this->module = $module;
    }

    /**
     *
     */
    public function removeSettings()
    {
        foreach ($this->settings as $setting) {
            $this->deleteSetting($setting['name']);
        }
    }

    /**
     *
     */
    public function setDefaultSettings()
    {
        foreach ($this->settings as $setting) {
            $this->setSetting($setting['name'], $setting['default']);
        }
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $settings = array();

        foreach ($this->settings as $key => $setting) {
            $settings[$key] = $this->getSetting($setting['name'], $setting['default']);
        }

        return $settings;
    }

    /**
     * @return string
     */
    public function getAiScriptUrl()
    {
        return $this->aiScriptUrl;
    }

    /**
     * @param array $settings
     */
    public function updateSettings(array $settings = array())
    {
        foreach ($this->getEnabledSettings() as $setting) {
            $name = $setting['name'];

            if (array_key_exists($name, $settings) === false) {
                continue;
            }

            $value = $settings[$name];

            if (array_key_exists('validation', $setting) === true) {
                $validationOptions = $setting['validation'];
                $validationParams = array_key_exists('params', $validationOptions)
                    ? $validationOptions['params']
                    : array();

                if ($this->{$validationOptions['method']}($value, $validationParams) === false) {
                    throw new CappasityManagerPlayerExceptionsValidation(
                        $validationOptions['error']
                    );
                }
            }

            $this->setSetting($name, $value);
        }
    }

    /**
     * @param HelperForm $helper
     * @param boolean $isAccountPaid
     * @return string
     */
    public function renderSettingsForm(HelperForm $helper, $isAccountPaid)
    {
        $settings = $isAccountPaid
            ? $this->getEnabledSettings()
            : array_filter($this->getEnabledSettings(), function ($value) {
                return $value['paid'] !== true;
            });
        $from = array();

        // generate form fieldssets
        foreach ($this->fieldssets as $set) {
            $inputs = array();

            foreach ($set['items'] as $field) {
                if (array_key_exists($field, $settings) === true) {
                    $params = $settings[$field];
                    $inputs[] = $this->getFormField($params);
                    $helper->fields_value[$params['name']] = $this->getSetting($params['name'], $params['default']);
                }
            }

            if (count($inputs) !== 0) {
                $from[] = $this->getFormFieldSet($set, $inputs);
            }
        }

        return $helper->generateForm($from);
    }

    /**
     * @return array
     */
    protected function getFormFieldSet($set, $inputs)
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l($set['title']),
                    'icon' => 'icon-cogs',
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->module->l('Save'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    protected function getFormField($params)
    {
        if ($params['type'] === 'boolean') {
            return array(
                'type' => 'select',
                'label' => $this->module->l($params['description']),
                'name' => $params['name'],
                'required' => true,
                'options' => array(
                    'query' => array(
                        array(
                            'id_option' => 0,
                            'name' => 'no',
                        ),
                        array(
                            'id_option' => 1,
                            'name' => 'yes',
                        )
                    ),
                    'id' => 'id_option',
                    'name' => 'name',
                ),
            );
        }

        if ($params['type'] === 'string') {
            return array(
                'type' => 'text',
                'label' => $this->module->l($params['description']),
                'name' => $params['name'],
                'required' => true,
            );
        }

        if ($params['type'] === 'select') {
            $query = array();

            foreach ($params['values'] as $name => $value) {
                $query[] = array(
                    'id_option' => $value,
                    'name' => $name,
                );
            }

            return array(
                'type' => 'select',
                'label' => $this->module->l($params['description']),
                'name' => $params['name'],
                'required' => true,
                'options' => array(
                    'query' => $query,
                    'id' => 'id_option',
                    'name' => 'name',
                ),
            );
        }

        throw new Exception('Unknown field type');
    }

    /**
     * @return array
     */
    protected function getEnabledSettings()
    {
        return array_filter($this->settings, function ($value) {
            return $value['enabled'] === true;
        });
    }

    /**
     * @return boolean
     */
    protected function isSize($value)
    {
        return preg_match('/^\d+(px|%)$/m', $value) === 1;
    }

    /**
     * @return boolean
     */
    protected function isFloat($value, $options)
    {
        if (is_numeric($value) === false) {
            return false;
        }

        $float = (float)$value;

        if (array_key_exists('min', $options) && $float <= $options['min']) {
            return false;
        }

        if (array_key_exists('max', $options) && $float >= $options['max']) {
            return false;
        }

        return true;
    }
}
