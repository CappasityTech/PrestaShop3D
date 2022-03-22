<?php
/**
{LICENSE_PLACEHOLDER}
*/

/**
 * Class CappasityModelAccount
 */
class CappasityModelAccount
{
    /**
     * @var string
     */
    protected $plan;

    /**
     * @var string
     */
    protected $alias;

    /**
     * Account constructor.
     * @param $plan
     * @param $alias
     */
    public function __construct($plan, $alias)
    {
        $this->alias = $alias;
        $this->plan = $plan;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return $this->plan === 'free';
    }
}
