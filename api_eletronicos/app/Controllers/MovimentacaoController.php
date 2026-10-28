<?php

namespace App\Controllers;

use App\Models\Movimentacao;
use App\Views\JsonView;

class MovimentacaoController {
    private Movimentacao $model;

    public function __construct() {
        $this->model = new Movimentacao();
    }

    public function listar(): void {
        $historico = $this->model->listarHistorico();
        JsonView::render(["status" => "sucesso", "dados" => $historico], 200);
    }
}