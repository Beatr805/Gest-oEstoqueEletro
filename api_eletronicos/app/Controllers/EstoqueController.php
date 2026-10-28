<?php

namespace App\Controllers;

use App\Models\Estoque;
use App\Views\JsonView;
use App\Core\Request;

class EstoqueController {
    private Estoque $model;

    public function __construct() {
        $this->model = new Estoque();
    }

    public function movimentar(): void {
        $input = Request::getBody();

        if (empty($input['id_eletronico']) || empty($input['tipo']) || !isset($input['quantidade'])) {
            JsonView::render(["status" => "erro", "mensagem" => "Campos 'id_eletronico', 'tipo' e 'quantidade' são obrigatórios."], 422);
        }

        $idEletronico = (int)$input['id_eletronico'];
        $tipo = strtoupper(trim($input['tipo']));
        $quantidade = (int)$input['quantidade'];
        $observacao = $input['observacao'] ?? null;

        if ($quantidade <= 0) {
            JsonView::render(["status" => "erro", "mensagem" => "A quantidade deve ser maior que zero."], 422);
        }

        $resultado = $this->model->movimentar($idEletronico, $tipo, $quantidade, $observacao);

        if ($resultado['sucesso']) {
            JsonView::render(["status" => "sucesso", "mensagem" => "Movimentação realizada com sucesso.", "dados" => $resultado['dados']], $resultado['status']);
        } else {
            JsonView::render(["status" => "erro", "mensagem" => $resultado['mensagem']], $resultado['status']);
        }
    }
}