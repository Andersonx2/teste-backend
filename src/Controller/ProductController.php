<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Model\Product;
use Contatoseguro\TesteBackend\Service\CategoryService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController
{
    private ProductService $service;
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->service = new ProductService();
        $this->categoryService = new CategoryService();
    }


    

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Obtendo o admin_user_id a partir do cabeçalho da requisição
        $adminUserId = $request->getHeader('admin_user_id')[0];
    
        // Obtendo os parâmetros da consulta (query params)
        $queryParams = $request->getQueryParams();
        $orderBy = $queryParams['order_by'] ?? 'created_at'; // Valor padrão para order_by
        $orderDir = $queryParams['orderDir'] ?? 'DESC'; // Valor padrão para orderDir
    
        // Log dos parâmetros de ordenação
        error_log("Captured order_by: $orderBy, Captured orderDir: $orderDir");
    
        // Parâmetros adicionais
        $status = $queryParams['status'] ?? null;
        $categoryTitle = $queryParams['category'] ?? null;
    
        // Chamando o método getAll da camada de serviço com os parâmetros fornecidos
        $products = $this->service->getAll($adminUserId, $status, $categoryTitle, $orderBy, $orderDir,);
    
        // Verificando se existem produtos retornados
        if (empty($products)) {
            // Caso não haja produtos, retornamos uma resposta vazia
            $response->getBody()->write(json_encode(['message' => 'Nenhum produto encontrado']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    
        // Retornando os resultados no formato JSON
        $response->getBody()->write(json_encode($products));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
    




    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];
    
        $result = $this->service->getOne($args['id']);
    
        $response->getBody()->write(json_encode($result));
    
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }


    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->insertOne($body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function updateOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->deleteOne($args['id'], $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }
}
