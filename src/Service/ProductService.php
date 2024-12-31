<?php
namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class ProductService
{
    private \PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = DB::connect(); 
    }


    public function getAll($adminUserId, $status = null, $categoryTitle = null, $orderDir = 'DESC', $orderBy = 'created_at')
    {
        $receivedOrderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'Não definido';    
        $query = "
            SELECT 
                p.id AS product_id, 
                p.title, 
                p.company_id,
                p.price, 
                p.active, 
                p.created_at, 
                c.id AS category_id, 
                c.title AS category_name
            FROM 
                product p
            JOIN 
                product_category pc ON p.id = pc.product_id
            JOIN 
                category c ON pc.cat_id = c.id
        ";    
    
        $conditions = [];
        $parameters = [];
    
        if ($status !== null) {
            $conditions[] = "p.active = :status";
            $parameters[':status'] = $status;
        }
    
        if ($categoryTitle !== null) {
            $conditions[] = "c.title = :categoryTitle";
            $parameters[':categoryTitle'] = $categoryTitle;
        }
    
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $allowedOrderColumns = ['created_at', 'price', 'title'];      
        if (!in_array($orderBy, $allowedOrderColumns)) {
            $orderBy = 'created_at';  
        }

        $orderDir = strtoupper($orderDir);
        if (!in_array($orderDir, ['DESC', 'ASC'], true)) {
            $orderDir = $receivedOrderDir === 'DESC' ? 'DESC' : 'ASC'; 
        }

        $query .= " ORDER BY " . $orderBy . " " . $orderDir;    
        $stm = $this->pdo->prepare($query);
    
        foreach ($parameters as $param => $value) {
            $stm->bindValue($param, $value);
        }

        $stm->execute();
        return $stm;
    }
    
          
  public function getOne($id)
    {
        $stm = $this->pdo->prepare("
            SELECT 
                p.id AS product_id, 
                p.title, 
                p.price, 
                p.active, 
                p.created_at, 
                c.id AS category_id, 
                c.title AS category_name
            FROM 
                product p
            JOIN 
                product_category pc ON p.id = pc.product_id
            JOIN 
                category c ON pc.cat_id = c.id
            WHERE 
                p.id = :id
        ");   
        
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);       
        $stm->execute();

        $results = $stm->fetchAll(\PDO::FETCH_ASSOC);
    
        if (empty($results)) {
            return null;
        }
    
        $result = [
            'id' => $results[0]['product_id'],
            'title' => $results[0]['title'],
            'price' => $results[0]['price'],
            'active' => $results[0]['active'],
            'created_at' => $results[0]['created_at'],
            'categories' => []
        ];

        foreach ($results as $row) {
            $result['categories'][] = [
                'name' => $row['category_name']
            ];
        }
    
        return $result;
    }
    

    public function insertOne($body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            INSERT INTO product (
                company_id,
                title,
                price,
                active
            ) VALUES (
                :company_id,
                :title,
                :price,
                :active
            )
        ");
        
        $stm->bindParam(':company_id', $body['company_id'], \PDO::PARAM_INT);
        $stm->bindParam(':title', $body['title'], \PDO::PARAM_STR);
        $stm->bindParam(':price', $body['price'], \PDO::PARAM_STR);
        $stm->bindParam(':active', $body['active'], \PDO::PARAM_INT);

        if (!$stm->execute()) {
            return false;
        }

        $productId = $this->pdo->lastInsertId();

        $stm = $this->pdo->prepare("
            INSERT INTO product_category (
                product_id,
                cat_id
            ) VALUES (
                :product_id,
                :category_id
            );
        ");
        
        $stm->bindParam(':product_id', $productId, \PDO::PARAM_INT);
        $stm->bindParam(':category_id', $body['category_id'], \PDO::PARAM_INT);

        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                :product_id,
                :admin_user_id,
                'create'
            )
        ");

        $stm->bindParam(':product_id', $productId, \PDO::PARAM_INT);
        $stm->bindParam(':admin_user_id', $adminUserId, \PDO::PARAM_INT);

        return $stm->execute();
    }

    public function updateOne($id, $body, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            UPDATE product
            SET company_id = :company_id,
                title = :title,
                price = :price,
                active = :active
            WHERE id = :id
        ");
        
        $stm->bindParam(':company_id', $body['company_id'], \PDO::PARAM_INT);
        $stm->bindParam(':title', $body['title'], \PDO::PARAM_STR);
        $stm->bindParam(':price', $body['price'], \PDO::PARAM_STR);
        $stm->bindParam(':active', $body['active'], \PDO::PARAM_INT);
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);

        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            UPDATE product_category
            SET cat_id = :category_id
            WHERE product_id = :id
        ");
        
        $stm->bindParam(':category_id', $body['category_id'], \PDO::PARAM_INT);
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);

        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                :id,
                :admin_user_id,
                'update'
            )
        ");
        
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);
        $stm->bindParam(':admin_user_id', $adminUserId, \PDO::PARAM_INT);

        return $stm->execute();
    }

    public function deleteOne($id, $adminUserId)
    {
        $stm = $this->pdo->prepare("
            DELETE FROM product_category WHERE product_id = :id
        ");
        
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);
        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("DELETE FROM product WHERE id = :id");
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);
        if (!$stm->execute()) {
            return false;
        }

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                :id,
                :admin_user_id,
                'delete'
            )
        ");
        
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);
        $stm->bindParam(':admin_user_id', $adminUserId, \PDO::PARAM_INT);

        return $stm->execute();
    }

    public function getLog($id)
    {
        $stm = $this->pdo->prepare("
            SELECT *
            FROM product_log
            WHERE product_id = :id
        ");
        
        $stm->bindParam(':id', $id, \PDO::PARAM_INT);
        $stm->execute();

        return $stm;
    }


    
}

