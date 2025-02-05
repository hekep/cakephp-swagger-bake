<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Operation\OperationResponse;
use SwaggerBake\Lib\Route\RouteScanner;

class OperationResponseYamlTest extends TestCase
{
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var array
     */
    private $routes;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees');
        });
        $this->router = $router;

        $this->config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-overwrite-model.yml',
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
        ], SWAGGER_BAKE_TEST_APP);

        if (empty($this->routes)) {
            $cakeRoute = new RouteScanner($this->router, $this->config);
            $this->routes = $cakeRoute->getRoutes();
        }
    }

    /**
     * @see https://github.com/cnizzardini/cakephp-swagger-bake/issues/274
     */
    public function test_yaml_schema_overwriting_cakephp_model_schema(): void
    {
        $route = $this->routes['employees:view'];
        $swagger = (new SwaggerFactory($this->config, new RouteScanner($this->router, $this->config)))->create();

        $operationResponse = new OperationResponse(
            $swagger,
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/** @throws Exception */'),
            [],
            $route,
            $swagger->getArray()['components']['schemas']['Employee']
        );

        $schema = $operationResponse
            ->getOperationWithResponses()
            ->getResponseByCode('200')
            ->getContentByMimeType('application/json')
            ->getSchema();

        $this->assertEquals('#/components/schemas/Employee', $schema->getRefEntity());
    }
}