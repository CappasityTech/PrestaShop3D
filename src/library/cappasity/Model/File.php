<?php
/**
{LICENSE_PLACEHOLDER}
*/

/**
 * Class CappasityModelFile
 */
class CappasityModelFile
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var array
     */
    protected $params;

    /**
     * Player constructor.
     * @param $id
     * @param $name
     * @param $alias
     * @param array $params
     */
    public function __construct($id, $name, $alias, array $params)
    {
        $this->id = $id;
        $this->name = $name;
        $this->alias = $alias;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param bool $disableAutoRun
     * @return string
     */
    public function getEmbed($disableAutoRun = false)
    {
        $params = $this->params;

        if ($disableAutoRun) {
            $params['autorun'] = 0;
        }

        $query = http_build_query($params);

        return '<iframe allowfullscreen mozallowfullscreen="true"'
            . '         webkitallowfullscreen="true" '
            . "         width=\"{$this->params['width']}\" "
            . "         height=\"{$this->params['height']}\" "
            . "         frameborder=\"0\" "
            . "         style=\"border:0;\" "
            . "         onmousewheel=\"\" "
            . "         src=\"https://api.cappasity.com/api/player/{$this->id}/embedded?{$query}\" >"
            . " </iframe>";
    }

    /**
     * @param array $data
     * @param array $params
     * @return array
     */
    public static function getCollection(array $data, array $params)
    {
        $collection = array();

        foreach ($data as $modelData) {
            $id = $modelData['id'];
            $name = $modelData['attributes']['name'];
            $alias = array_key_exists('alias', $modelData['attributes']) ? $modelData['attributes']['alias'] : '';

            $collection[] = new CappasityModelFile($id, $name, $alias, $params);
        }

        return $collection;
    }
}
