<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Movimentacao {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function listarHistorico(): array {
        $sql = "SELECT m.id_movimentacao, m.id_eletronico, e.nome AS eletronico, u.nome AS usuario, 
                       m.tipo, m.quantidade, m.saldo_anterior, m.saldo_posterior, m.observacao, m.data_movimentacao
                FROM movimentacoes m
                JOIN eletronicos e ON m.id_eletronico = e.id_eletronico
                JOIN usuarios u ON m.id_usuario = u.id_usuario
                ORDER BY m.data_movimentacao DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}