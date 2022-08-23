<?php

namespace Neko\LaravelStapler;

use Illuminate\Config\Repository;
use Neko\Stapler\Interfaces\Config;

class IlluminateConfig implements Config
{
    /**
     * Constructor method.
     *
     * @param  Repository  $config  An instance of Laravel's config class.
     * @param  string  $packageName  The name of the package this driver is being used with.
     * @param  string  $separator  The separator between package name and item.
     */
    public function __construct(protected Repository $config, protected $packageName = null, protected $separator = '.')
    {
    }

    /**
     * Retrieve a configuration value.
     *
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        $item = $this->getItemPath($name);

        return $this->config->get($item);
    }

    /**
     * Set a configuration value.
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function set($name, $value)
    {
        $item = $this->getItemPath($name);

        $this->config->set($item, $value);
    }

    /**
     * Return the path to an item so that it can be loaded via config.
     * We need to append the package name to the item separated
     * with '::' for L4 and '.' for L5.
     *
     * @param  string  $item
     *
     * @return string
     */
    protected function getItemPath($item)
    {
        return $this->packageName.$this->separator.$item;
    }
}
