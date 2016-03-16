<?php

namespace old;

class Parser extends \SimpleXMLElement
{

    /**
     * @param string $name
     * @param null $ns
     * @param bool $is_prefix
     * @return array|null
     */
    public function attr($name = '*', $ns = null, $is_prefix = false)
    {

        $attributes = (array)$this->attributes($ns, $is_prefix);

        if (isset($attributes['@attributes'])) {
            $attributes = $attributes['@attributes'];
        }

        if ($name === '*') {
            return $attributes;
        }

        return isset($attributes[$name]) ? $attributes[$name] : null;

    }

    /**
     * @param \child $name
     * @return self[]
     */
    function __get($name)
    {
        return parent::__get($name);
    }

    /**
     * @param null $filename
     * @return mixed
     */
    public function asXML($filename = null)
    {
        return parent::asXML($filename);
    }

    /**
     * @param null $filename
     * @return mixed
     */
    public function saveXML($filename = null)
    {
        return parent::saveXML($filename);
    }

    /**
     * @param string $path
     * @return self[]
     */
    public function xpath($path)
    {
        return parent::xpath($path);
    }

    /**
     * @param string $prefix
     * @param string $ns
     * @return bool
     */
    public function registerXPathNamespace($prefix, $ns)
    {
        return parent::registerXPathNamespace($prefix, $ns);
    }

    /**
     * @param null $ns
     * @param bool $is_prefix
     * @return self
     */
    public function attributes($ns = null, $is_prefix = false)
    {
        return parent::attributes($ns, $is_prefix);
    }

    /**
     * @param null $ns
     * @param bool $is_prefix
     * @return self
     */
    public function children($ns = null, $is_prefix = false)
    {
        return parent::children($ns, $is_prefix);
    }

    /**
     * @param bool $recursive
     * @return array
     */
    public function getNamespaces($recursive = false)
    {
        return parent::getNamespaces($recursive);
    }

    /**
     * @param bool $recursive
     * @param bool $from_root
     * @return array
     */
    public function getDocNamespaces($recursive = false, $from_root = true)
    {
        return parent::getDocNamespaces($recursive, $from_root);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return parent::getName();
    }

    /**
     * @param string $name
     * @param null $value
     * @param null $namespace
     * @return self
     */
    public function addChild($name, $value = null, $namespace = null)
    {
        return parent::addChild($name, $value, $namespace);
    }

    /**
     * @param string $name
     * @param null|string|null $value
     * @param null|string|null $namespace
     */
    public function addAttribute($name, $value = null, $namespace = null)
    {
        parent::addAttribute($name, $value, $namespace);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }

    /**
     * @return int
     */
    public function count()
    {
        return parent::count();
    }

}