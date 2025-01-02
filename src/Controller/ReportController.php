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
        $queryParams = $request->getQueryParams();
        $status = $queryParams['status'] ?? null;
        $categoryTitle = $queryParams['categoryId'] ?? null;
        $orderBy = $queryParams['order_by'] ?? 'created_at';

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

        // Obtendo os produtos
        $products = $this->productService->getAll(null, $status, $categoryTitle, 'DESC', $orderBy);;

        foreach ($products as $product) {
            $companyName = $this->companyService->getNameById($product['company_id'])->fetch(\PDO::FETCH_OBJ)->name;
        
            // Obter logs do produto
            $stm = $this->productService->getLog($product['id']);
            $productLogs = $stm->fetchAll(\PDO::FETCH_ASSOC);
        
            // Verificar se os logs foram retornados
            $formattedLogs = [];
            if (!empty($productLogs)) {
                foreach ($productLogs as $log) {
                    $formattedLogs[] = sprintf(
                        "%s, %s, %s",
                        $log['change_date'],
                        $log['user_name'],
                        $log['change_type']
                    );
                }
            } else {
                $formattedLogs[] = "Nenhum log disponível";
            }
        
            $data[] = [
                $product['id'],
                $companyName,
                $product['title'],
                $product['price'],
                implode(", ", array_column($product['categories'], 'name')),
                $product['created_at'],
                implode("; ", $formattedLogs)
            ];
        }

        $report = "<table style='font-size: 10px; border-collapse: collapse;' border='1'>";
        foreach ($data as $row) {
            $report .= "<tr>";
            foreach ($row as $column) {
                $report .= "<td style='padding: 5px;'>" . htmlspecialchars($column, ENT_NOQUOTES, 'UTF-8') . "</td>";
            }
            $report .= "</tr>";
        }
        $report .= "</table>";

        // Retornar resposta com relatório em HTML
        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html');    }
}