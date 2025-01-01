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

        $stm = $this->productService->getAll(null, $status, $categoryTitle, 'DESC', $orderBy);
        $products = $stm->fetchAll(\PDO::FETCH_OBJ);

        foreach ($products as $i => $product) {
        
            $stm = $this->companyService->getNameById($product->company_id);
            $companyName = $stm->fetch(\PDO::FETCH_OBJ)->name;
            $stm = $this->productService->getLog($product->product_id);
            $productLogs = $stm->fetchAll(\PDO::FETCH_ASSOC);

            $formattedLogs = [];
            if (!empty($productLogs)) {
                foreach ($productLogs as $log) {
                    $formattedLogs[] = sprintf(
                        "Usuário: %s, Ação: %s, Data: %s",
                        $log['user_name'],
                        $log['change_type'],
                        $log['change_date']
                    );
                }
            } else {
                $formattedLogs[] = "Nenhum log disponível";
            }

            $data[] = [
                $product->product_id,
                $companyName,
                $product->title,
                $product->price,
                $product->category_name,
                $product->created_at,
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

        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html');    }
}