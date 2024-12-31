<?php
namespace Contatoseguro\TesteBackend\Service;

class UserService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db; 
    }

    // Método para buscar o nome do usuário pelo ID
    public function getUserNameById($userId)
    {
        $stm = $this->db->prepare("SELECT name FROM admin_user WHERE id = :id");
        $stm->bindParam(':id', $userId);
        $stm->execute();
        $user = $stm->fetch(\PDO::FETCH_OBJ);

        return $user ? $user->name : 'Desconhecido'; // Retorna o nome ou 'Desconhecido'
    }
}
