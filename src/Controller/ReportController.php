<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Service\CompanyService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ReportController
{
    private ProductService $productService;
    private CompanyService $companyService;
    
    public function __construct()
    {
        $this->productService = new ProductService();
        $this->companyService = new CompanyService();
    }
    
    public function generate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $params = $request->getQueryParams();

        $data = [];
        $data[] = [
            'Id do produto',
            'Nome da Empresa',
            'Nome do Produto',
            'Valor do Produto',
            'Categorias do Produto',
            'Data de Criação',
            'Logs de Alterações'
        ];
        

        $filters = [
            'status' => isset($params['status']) ? $params['status'] : null,
            'category' => isset($params['category']) ? $params['category'] : null,
            'sort_by' => isset($params['sort_by']) ? $params['sort_by'] : 'created_at', // Ordenar por data por padrão
            'order' => isset($params['order']) ? $params['order'] : 'ASC' // Ordem crescente por padrão
        ];

        $stm = $this->productService->getAll($adminUserId, $filters);
        $products = $stm->fetchAll();

        foreach ($products as $i => $product) {
            $stm = $this->companyService->getNameById($product->company_id);
            $companyName = $stm->fetch()->name;

            $stm = $this->productService->getLog($product->id);
            $productLogs = $stm->fetchAll();
            
            $data[$i+1][] = $product->id;
            $data[$i+1][] = $companyName;
            $data[$i+1][] = $product->title;
            $data[$i+1][] = $product->price;
            $data[$i+1][] = $product->category;
            $data[$i+1][] = $product->created_at;
            $data[$i+1][] = $productLogs;
        }
        
        $report = "<table style='font-size: 16px; border-collapse: collapse; width: 100%;'>";
        foreach ($data as $row) {
        $report .= "<tr>";
        foreach ($row as $column) {
            $report .= "<td style='border: 1px solid black; padding: 8px; text-align: center;'>{$column}</td>";
        }
        $report .= "</tr>";
        }
        $report .= "</table>";
        
        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html');
    }
}
