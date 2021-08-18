<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationFromRouteFactory;

class OperationFromRouteFactoryTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private Router $router;

    private array $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->config = [
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/x-www-form-urlencoded'],
            'responseContentTypes' => ['application/json'],
            'namespaces' => [
                'controllers' => ['\SwaggerBakeTest\App\\'],
                'entities' => ['\SwaggerBakeTest\App\\'],
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ];
    }

    /**
     * Tests:
     * - OperationFromRouteFactory::create()
     * - OpenApiOperation attribute tagNames
     */
    public function test_create(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['index']
            ]);
        });

        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $swagger = (new SwaggerFactory($config))->create();
        $cakeRoute = new RouteScanner($router, $config);

        $routes = $cakeRoute->getRoutes();

        $operation = (new OperationFromRouteFactory($swagger))->create(
            $routes['employees:index'],
            'GET',
            null
        );

        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals('GET', $operation->getHttpMethod());
        $this->assertEquals('employees:index:get', $operation->getOperationId());
        $this->assertEquals('CustomTag', $operation->getTags()[1]);
    }

    public function test_operation_is_put(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['update']
            ]);
        });

        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $swagger = (new SwaggerFactory($config))->create();
        $cakeRoute = new RouteScanner($router, $config);

        $routes = $cakeRoute->getRoutes();
        $operation = (new OperationFromRouteFactory($swagger))->create(
            $routes['employees:edit'],
            'PUT',
            null
        );

        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals('PUT', $operation->getHttpMethod());
        $this->assertEquals('employees:edit:put', $operation->getOperationId());
    }
}