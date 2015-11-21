<?php

namespace Madewithlove\Glue\Traits;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerInterface;
use Madewithlove\Glue\Configuration\Configuration;
use Madewithlove\Glue\Configuration\ConfigurationInterface;

trait Configurable
{
    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @return ContainerInterface
     */
    abstract public function getContainer();

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param ConfigurationInterface $configuration
     */
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        if ($this->configuration instanceof ContainerAwareInterface) {
            $this->configuration->setContainer($this->getContainer());
        }

        $this->configuration->configure();
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// FLUENT SETTERS //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Modify something in the configuration.
     *
     * @param string|array $configuration
     * @param null         $value
     */
    public function configure($configuration, $value = null)
    {
        if ($value && !is_array($configuration)) {
            $configuration = [$configuration => $value];
        }

        $this->configuration = new Configuration(array_merge($this->configuration->toArray(), $configuration));
    }
}
