<?php

namespace Cargo;

/**
 * Class Config
 * @package Cargo
 */
class Config
{
    const PARAM_KEY_SEPARATOR = '.';

    protected $params = [];

    /**
     * @param string $key
     * @param mixed  $default
     * @return string|array|int|bool
     */
    public function getParam($key, $default = null)
    {
        $keyParts = explode(self::PARAM_KEY_SEPARATOR, $key);
        $param = $this->params;
        $currentPropertyPath = [];
        foreach ($keyParts as $keyPart) {
            if (!isset($param[$keyPart])) {
                return $default;
            }
            $currentPropertyPath[] = $keyPart;
            $param = &$param[$keyPart];
        }

        return $param;
    }

    /**
     * @param array  $config
     * @param string $prefix
     * @return array
     */
    public function addParamsFromArray(array $config, $prefix = '')
    {
        foreach ($config as $name => $configItem) {
            $paramName = $prefix.self::PARAM_KEY_SEPARATOR.$name;
            if (is_array($configItem)) {
                $this->params = array_merge($this->params, $this->addParamsFromArray($configItem, $paramName));
            } else {
                $this->params[$paramName] = $configItem;
            }
        }
    }
}
