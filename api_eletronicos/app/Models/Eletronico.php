<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Eletronico {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function listarTodos(): array {
        $sql = "SELECT e.*, COALESCE(es.quantidade_atual, 0) AS saldo_atual
                FROM eletronicos e
                LEFT JOIN estoque es ON e.id_eletronico = es.id_eletronico
                WHERE e.ativo = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array {
        $sql = "SELECT e.*, COALESCE(es.quantidade_atual, 0) AS saldo_atual
                FROM eletronicos e
                LEFT JOIN estoque es ON e.id_eletronico = es.id_eletronico
                WHERE e.id_eletronico = :id AND e.ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function cadastrarComEstoqueInicial(array $dados): int {
        $this->db->beginTransaction();

        try {
            $sqlEletronico = "INSERT INTO eletronicos 
                (nome, descricao, numero_lote, fabricante, categoria, tensao, dimensoes, resolucao, capacidade, conectividade, preco, processador, memoria, camera, sistema_operacional, cor, estoque_minimo) 
                VALUES 
                (:nome, :descricao, :numero_lote, :fabricante, :categoria, :tensao, :dimensoes, :resolucao, :capacidade, :conectividade, :preco, :processador, :memoria, :camera, :sistema_operacional, :cor, :estoque_minimo)";

            $stmt = $this->db->prepare($sqlEletronico);
            $stmt->execute([
                'nome' => $dados['nome'],
                'descricao' => $dados['descricao'] ?? '',
                'numero_lote' => $dados['numero_lote'],
                'fabricante' => $dados['fabricante'] ?? '',
                'categoria' => $dados['categoria'] ?? '',
                'tensao' => $dados['tensao'] ?? null,
                'dimensoes' => $dados['dimensoes'] ?? null,
                'resolucao' => $dados['resolucao'] ?? null,
                'capacidade' => $dados['capacidade'] ?? null,
                'conectividade' => $dados['conectividade'] ?? null,
                'preco' => $dados['preco'] ?? 0.00,
                'processador' => $dados['processador'] ?? null,
                'memoria' => $dados['memoria'] ?? null,
                'camera' => $dados['camera'] ?? null,
                'sistema_operacional' => $dados['sistema_operacional'] ?? null,
                'cor' => $dados['cor'] ?? null,
                'estoque_minimo' => $dados['estoque_minimo'] ?? 5,
            ]);

            $idEletronico = (int)$this->db->lastInsertId();
            $qtdInicial = (int)($dados['estoque_inicial'] ?? 0);

            $sqlEstoque = "INSERT INTO estoque (id_eletronico, quantidade_atual) VALUES (:id_eletronico, :qtd)";
            $stmtEstoque = $this->db->prepare($sqlEstoque);
            $stmtEstoque->execute([
                'id_eletronico' => $idEletronico,
                'qtd' => $qtdInicial
            ]);

            if ($qtdInicial > 0) {
                $sqlMov = "INSERT INTO movimentacoes (id_eletronico, id_usuario, tipo, quantidade, saldo_anterior, saldo_posterior, observacao) 
                           VALUES (:id_e, 1, 'ENTRADA', :qtd, 0, :qtd, 'Carga inicial no cadastro')";
                $stmtMov = $this->db->prepare($sqlMov);
                $stmtMov->execute([
                    'id_e' => $idEletronico,
                    'qtd' => $qtdInicial
                ]);
            }

            $this->db->commit();
            return $idEletronico;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function atualizar(array $dados): bool {
        $sql = "UPDATE eletronicos SET 
                nome = :nome,
                descricao = COALESCE(:descricao, descricao),
                fabricante = COALESCE(:fabricante, fabricante),
                categoria = COALESCE(:categoria, categoria),
                preco = COALESCE(:preco, preco),
                estoque_minimo = COALESCE(:estoque_minimo, estoque_minimo)
                WHERE id_eletronico = :id AND ativo = 1";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $dados['id_eletronico'],
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'fabricante' => $dados['fabricante'] ?? null,
            'categoria' => $dados['categoria'] ?? null,
            'preco' => $dados['preco'] ?? null,
            'estoque_minimo' => $dados['estoque_minimo'] ?? null
        ]);
    }

    public function desativar(int $id): bool {
        $sql = "UPDATE eletronicos SET ativo = 0 WHERE id_eletronico = :id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}