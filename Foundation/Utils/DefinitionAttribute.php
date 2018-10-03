<?php

namespace App\Extend;

trait DefinitionAttribute
{

    /**
     * 返回指定定义的指定映射的值
     *
     * @param $name
     * @param $key
     * @return null
     */

    public static function getDefinitionLabel($name, $key)
    {
        $maps = self::getDefinitionLabels($name);
        return isset($maps[$key]) ? $maps[$key] : null;
    }

    /**
     * 返回指定定义得所有映射
     *
     * @param $name
     * @return array
     */

    public static function getDefinitionLabels($name)
    {
        $name = lcfirst($name) . 'Definition';
        if (method_exists(static::class, $name)) {
            return static::$name();
        }
        if (isset(static::$$name) && is_array(static::$$name)) {
            return static::$$name;
        }
        if (method_exists(self::class, $name)) {
            return static::$name();
        }
        return isset(self::$$name) && is_array(self::$$name) ? self::$$name : [];
    }

    /**
     * 返回指定定义的所有映射的键
     *
     * @param $name
     * @return array
     */

    public static function getDefinitionList($name)
    {
        return array_keys(self::getDefinitionLabels($name));
    }

    /**
     * 截取请求的方法名获得请求的定义名称
     *
     * @param $method
     * @param $prefix
     * @return bool|string
     */

    protected static function getDefinitionName($method, $prefix)
    {
        return substr($method, 3, strpos($method, $prefix) - 3);
    }

    /**
     *
     * @param $method
     * @return bool
     */

    protected static function isDefinitionMethod($method)
    {
        return (starts_with($method, 'get') && ends_with($method, 'Label')) ||
            (starts_with($method, 'get') && ends_with($method, 'List')) ||
            (starts_with($method, 'get') && ends_with($method, 'Labels'));
    }

    /**
     * @param $method
     * @param $parameters
     * @return array|null
     */

    protected static function callDefinitionMethod($method, $parameters)
    {
        if (starts_with($method, 'get') && ends_with($method, 'Label')) {
            return self::getDefinitionLabel(self::getDefinitionName($method, 'Label'), $parameters[0]);
        } else if (starts_with($method, 'get') && ends_with($method, 'List')) {
            return self::getDefinitionList(self::getDefinitionName($method, 'List'));
        } else if (starts_with($method, 'get') && ends_with($method, 'Labels')) {
            return self::getDefinitionLabels(self::getDefinitionName($method, 'Labels'));
        }
    }

    /**
     * @param $method
     * @param $parameters
     * @return array|null
     */

    public function __call($method, $parameters)
    {
        if (self::isDefinitionMethod($method)) {
            return self::callDefinitionMethod($method, $parameters);
        } else if (get_parent_class() && method_exists(get_parent_class(), '__call')) {
            return parent::__call($method, $parameters);
        }
    }

    /**
     * 覆盖Model的__callStatic方法，增加本类的魔术方法
     *
     * @param $method
     * @param $parameters
     * @return array|null
     */

    public static function __callStatic($method, $parameters)
    {
        if ($definition = self::callDefinitionMethod($method, $parameters)) {
            return $definition;
        } else if (get_parent_class() && method_exists(get_parent_class(), '__callStatic')) {
            return parent::__callStatic($method, $parameters);
        }
    }
}