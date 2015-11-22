<?php

/*
 * This file is part of Glue
 *
 * (c) Madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Madewithlove\Glue\Configuration;

use Franzl\Middleware\Whoops\Middleware as WhoopsMiddleware;
use Madewithlove\Glue\Console\Commands\TinkerCommand;
use Madewithlove\Glue\Console\ConsoleServiceProvider;
use Madewithlove\Glue\Console\PhinxServiceProvider;
use Madewithlove\Glue\Http\Middlewares\LeagueRouteMiddleware;
use Madewithlove\Glue\Http\Providers\Assets\WebpackServiceProvider;
use Madewithlove\Glue\Http\Providers\RelayServiceProvider;
use Madewithlove\Glue\Http\Providers\RequestServiceProvider;
use Madewithlove\Glue\Http\Providers\RoutingServiceProvider;
use Madewithlove\Glue\Http\Providers\TwigServiceProvider;
use Madewithlove\Glue\Http\Providers\UrlGeneratorServiceProvider;
use Madewithlove\Glue\Providers\CommandBusServiceProvider;
use Madewithlove\Glue\Providers\DatabaseServiceProvider;
use Madewithlove\Glue\Providers\DebugbarServiceProvider;
use Madewithlove\Glue\Providers\FilesystemServiceProvider;
use Madewithlove\Glue\Providers\LogsServiceProvider;
use Madewithlove\Glue\Utils;
use Psr7Middlewares\Middleware\DebugBar;
use Psr7Middlewares\Middleware\FormatNegotiator;

class DefaultConfiguration extends AbstractConfiguration
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->configure();

        parent::__construct($attributes);
    }

    /**
     * Configure with the container and environment variables.
     */
    public function configure()
    {
        // Reset debug mode from env variables now that they're available
        $debug = $this->debug !== null
            ? $this->debug
            : !getenv('APP_ENV') || getenv('APP_ENV') === 'local';

        $this->attributes = [
            'debug' => $debug,
            'rootPath' => $this->configureRootPath(),
            'namespace' => $this->configureNamespace(),
            'paths' => $this->configurePaths(),
            'commands' => $this->configureCommands(),
            'providers' => $this->configureProviders(),
            'middlewares' => $this->configureMiddlewares(),
        ];
    }

    /**
     * @return string
     */
    protected function configureRootPath()
    {
        $folder = Utils::find('composer.json', getcwd());
        $folder = str_replace('composer.json', null, $folder);
        $folder = rtrim($folder, DS);

        return $folder;
    }

    /**
     * @return string|void
     */
    protected function configureNamespace()
    {
        $composer = $this->getRootPath().'/composer.json';
        if (!file_exists($composer)) {
            return;
        }

        $composer = file_get_contents($composer);
        $composer = json_decode($composer, true);

        $namespaces = array_get($composer, 'autoload.psr-4', []);
        $namespaces = $namespaces ?: array_get($composer, 'autoload.psr-0', []);
        if (!$namespaces) {
            return;
        }

        return trim(array_keys($namespaces)[0], '\\');
    }

    /**
     * @return array
     */
    public function configurePaths()
    {
        $rootPath = $this->getRootPath();

        return [
            'assets' => $rootPath.'/public/builds',
            'web' => $rootPath.'/public',
            'migrations' => $rootPath.'/resources/migrations',
            'views' => $rootPath.'/resources/views',
            'cache' => $rootPath.'/storage/cache',
            'logs' => $rootPath.'/storage/logs',
        ];
    }

    /**
     * @return string[]
     */
    public function configureCommands()
    {
        return [
            TinkerCommand::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function configureProviders()
    {
        $providers = [
            'commandbus' => CommandBusServiceProvider::class,
            'db' => DatabaseServiceProvider::class,
            'filesystem' => FilesystemServiceProvider::class,
            'logs' => LogsServiceProvider::class,
            'request' => RequestServiceProvider::class,
            'routing' => RoutingServiceProvider::class,
            'view' => TwigServiceProvider::class,
            'pipeline' => RelayServiceProvider::class,
            'url' => UrlGeneratorServiceProvider::class,
            'assets' => WebpackServiceProvider::class,
        ];

        if ($this->isDebug()) {
            $providers += [
                'console' => ConsoleServiceProvider::class,
                'migrations' => PhinxServiceProvider::class,
                'debugbar' => DebugbarServiceProvider::class,
            ];
        }

        return $providers;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureMiddlewares()
    {
        if ($this->isDebug()) {
            return [
                FormatNegotiator::class,
                DebugBar::class,
                WhoopsMiddleware::class,
                LeagueRouteMiddleware::class,
            ];
        }

        return [
            LeagueRouteMiddleware::class,
        ];
    }
}
