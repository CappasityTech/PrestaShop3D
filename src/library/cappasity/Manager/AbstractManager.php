<?php
/**
{LICENSE_PLACEHOLDER}
*/

/**
 * Class CappasityManagerAbstractManager
 */
class CappasityManagerAbstractManager
{
    /**
     * @var array
     */
    protected $settings = array();

    /**
     *
     */
    public function removeSettings()
    {
        if (count($this->settings) === 0) {
            return;
        }

        foreach ($this->settings as $setting) {
            Configuration::deleteByName($setting);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setSetting($key, $value)
    {
        Configuration::updateValue($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSetting($key, $default = null)
    {
        if (Configuration::hasKey($key) === true) {
            return Configuration::get($key);
        }

        return $default;
    }

    /**
     * @param string $key
     */
    protected function deleteSetting($key)
    {
        Configuration::deleteByName($key);
    }
}
