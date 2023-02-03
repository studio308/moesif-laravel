<?php namespace JonnyPickett\MoesifLaravel\Tests;

use Orchestra\Testbench\TestCase;

/**
*
*/
abstract class MoesifLaravelTestCase extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->app['path.base'] = __DIR__ . '/../../../../src';

        $this->modifyConfiguration($this->app);
    }

    /**
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'JonnyPickett\MoesifLaravel\ServiceProvider'
        ];
    }

    /**
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [];
    }

    /**
     * Perform user specific configuration.
     */
    protected function modifyConfiguration($app)
    {
    }

    /**
     * Prepare routes.
     */
    protected function defineRoutes($router)
    {
        $router->get('test', [function () {
            return json_encode(['message' => 'testing']);
        }])->middleware('moesif');
    }
}
