<?php

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

require_once dirname(__DIR__, 1) . '/src/Config/middlewares.php';
require_once dirname(__DIR__, 1) . '/src/Config/routes.php';




$app->get('/produtos', function ($request, $response, $args) {
    $produtos = Produto::listar();
    return $response->withJson($produtos);
});

$app->run();