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
        
        $stm = $this->productService->getAll($adminUserId);
        $products = $stm->fetchAll();
       
        foreach ($products as $i => $product) {
            $companyName = '';
            if (isset($product->company_id)) {
                $stm = $this->companyService->getNameById($product->company_id);
                $company = $stm->fetch();
                if ($company) {
                    $companyName = $company->name; 
                }
            }
        
            $productLogs = [];
            if (isset($product->product_id)) {
                $stm = $this->productService->getLog($product->product_id);
                $productLogs = $stm->fetchAll(\PDO::FETCH_OBJ); 
            }
        
            $data[$i+1][] = $product->product_id;  
            $data[$i+1][] = $companyName;
            $data[$i+1][] = $product->title;
            $data[$i+1][] = $product->price;
            $data[$i+1][] = $product->category_name;  
            $data[$i+1][] = $product->created_at;
            $data[$i+1][] = json_encode($productLogs); 
         
        }
        
       

        // Gerar o HTML da tabela
        $report = "<table style='font-size: 10px;'>";
        foreach ($data as $row) {
            $report .= "<tr>";
            foreach ($row as $column) {
                $report .= "<td>{$column}</td>";
            }
            $report .= "</tr>";
        }
        $report .= "</table>";
        
        // Retorna a resposta com o relatório em HTML
        $response->getBody()->write($report);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html'); 
}
} 