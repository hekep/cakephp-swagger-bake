<?php


namespace SwaggerBake\Test\TestCase\Lib;


use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;

class SwaggerTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.DepartmentEmployees',
        'plugin.SwaggerBake.Departments',
        'plugin.SwaggerBake.Employees',
    ];

    private Router $router;

    private array $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'map' => [
                    'customGet' => [
                        'action' => 'customGet',
                        'method' => 'GET',
                        'path' => 'custom-get'
                    ],
                    'customPost' => [
                        'action' => 'customPost',
                        'method' => 'POST',
                        'path' => 'custom-post'
                    ]
                ]
            ]);
            $builder->resources('Departments', function (RouteBuilder $routes) {
                $routes->resources('DepartmentEmployees');
            });
        });
        $this->router = $router;

        $this->config = [
            'prefix' => '/',
            'yml' => '/config/swagger-with-existing.yml',
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

        AnnotationLoader::load();
    }

    public function test_get_mixing_static_yaml_and_dynamic_openapi(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($this->router, $config);

        $openApi = (new Swagger(new ModelScanner($cakeRoute, $config)))->getArray();

        $this->assertArrayHasKey('/departments', $openApi['paths']);
        $this->assertArrayHasKey('/pets', $openApi['paths']);
        $this->assertArrayHasKey('Pets', $openApi['components']['schemas']);
    }

    public function test_get_array_from_bare_bones(): void
    {
        $vars = $this->config;
        $vars['yml'] = '/config/swagger-bare-bones.yml';
        $config = new Configuration($vars, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($this->router, $config);

        $swagger = new Swagger(new ModelScanner($cakeRoute, $config));
        $arr = json_decode($swagger->toString(), true);

        $this->assertArrayHasKey('/departments', $arr['paths']);
        $this->assertArrayHasKey('Department', $arr['components']['schemas']);
    }

    public function test_custom_json_options(): void
    {
        $vars = $this->config;
        $vars['jsonOptions'] = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $config = new Configuration($vars, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($this->router, $config);

        $swagger = new Swagger(new ModelScanner($cakeRoute, $config));
        $jsonString = $swagger->toString();

        $this->assertStringNotContainsString('"\/departments"', $jsonString);
        $this->assertStringContainsString('"/departments"', $jsonString);
        $this->assertStringNotContainsString("\n", $jsonString);

        $arr = json_decode($swagger->toString(), true);

        $this->assertArrayHasKey('/pets', $arr['paths']);
        $this->assertArrayHasKey('Pets', $arr['components']['schemas']);
    }
}