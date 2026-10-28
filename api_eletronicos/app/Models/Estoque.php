<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Estoque {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function movimentar(int $idEletronico, string $tipo, int $quantidade, ?string $observacao = null): array {
        $this->db->beginTransaction();

        try {
            $sqlSelect = "SELECT quantidade_atual FROM estoque WHERE id_eletronico = :id FOR UPDATE";
            $stmtSelect = $this->db->prepare($sqlSelect);
            $stmtSelect->execute(['id' => $idEletronico]);
            $estoque = $stmtSelect->fetch();

            if (!$estoque) {
                $this->db->rollBack();
                return ['sucesso' => false, 'status' => 404, 'mensagem' => 'Eletrônico não encontrado no estoque.'];
            }

            $saldoAnterior = (int)$estoque['quantidade_atual'];
            $saldoPosterior = 0;

            if ($tipo === 'ENTRADA') {
                $saldoPosterior = $saldoAnterior + $quantidade;
            } else if ($tipo === 'SAIDA') {
                if ($quantidade > $saldoAnterior) {
                    $this->db->rollBack();
                    return ['sucesso' => false, 'status' => 409, 'mensagem' => 'Saldo insuficiente em estoque.'];
                }
                $saldoPosterior = $saldoAnterior - $quantidade;
            } else {
                $this->db->rollBack();
                return ['sucesso' => false, 'status' => 400, 'mensagem' => 'Tipo inválido. Use ENTRADA ou SAIDA.'];
            }

            $sqlUpdate = "UPDATE estoque SET quantidade_atual = :novo_saldo WHERE id_eletronico = :id";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'novo_saldo' => $saldoPosterior,
                'id' => $idEletronico
            ]);

            $sqlHist = "INSERT INTO movimentacoes (id_eletronico, id_usuario, tipo, quantidade, saldo_anterior, saldo_posterior, observacao) 
                        VALUES (:id_e, 1, :tipo, :qtd, :anterior, :posterior, :obs)";
            $stmtHist = $this->db->prepare($sqlHist);
            $stmtHist->execute([
                'id_e' => $idEletronico,
                'tipo' => $tipo,
                'qtd' => $quantidade,
                'anterior' => $saldoAnterior,
                'posterior' => $saldoPosterior,
                'obs' => $observacao ?? "Movimentação executada via API ({$tipo})"
            ]);

            $this->db->commit();
            return [
                'sucesso' => true,
                'status' => 200,
                'dados' => [
                    'id_eletronico' => $idEletronico,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_atual' => $saldoPosterior
                ]
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}