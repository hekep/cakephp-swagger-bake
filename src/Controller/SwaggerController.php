<?php
declare(strict_types=1);

namespace SwaggerBake\Controller;

use Cake\Event\Event;

class SwaggerController extends AppController
{
    public function initialize(): void
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->loadComponent('SwaggerBake.SwaggerUi');
    }

    /**
     * Controller action for displaying built-in Swagger UI
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $config = $this->SwaggerUi->getSwaggerBakeConfiguration();
        $title = $config->getTitleFromYml();
        $url = $config->getWebPath();
        $this->set(compact('title','url'));
        $doctype = $this->request->getQuery('doctype');
        $this->viewBuilder()->setLayout($config->getLayout($doctype));
        return $this->render($config->getView($doctype));
    }
}
