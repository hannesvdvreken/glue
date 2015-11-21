<?php
namespace Madewithlove\Nanoframework\Providers;

class PathsServiceProvider extends AbstractValuesProvider
{
    /**
     * @var string
     */
    protected $key = 'paths';

    /**
     * @return array
     */
    protected function getValues()
    {
        return $this->container->get('config.paths');
    }
}
