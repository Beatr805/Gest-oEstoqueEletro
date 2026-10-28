<?php

namespace App\Controllers;

use App\Models\Eletronico;
use App\Views\JsonView;
use App\Core\Request;
use PDOException;

class EletronicoController {
    private Eletronico $model;

    public function __construct() {
        $this->model = new Eletronico();
    }

    public function listar(): void {
        $dados = $this->model->listarTodos();
        JsonView::render(["status" => "sucesso", "dados" => $dados], 200);
    }

    public function cadastrar(): void {
        $input = Request::getBody();

        if (empty($input['nome']) || empty($input['numero_lote'])) {
            JsonView::render(["status" => "erro", "mensagem" => "Campos 'nome' e 'numero_lote' são obrigatórios."], 422);
        }

        try {
            $id = $this->model->cadastrarComEstoqueInicial($input);
            JsonView::render(["status" => "sucesso", "mensagem" => "Eletrônico cadastrado com sucesso.", "id_eletronico" => $id], 201);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                JsonView::render(["status" => "erro", "mensagem" => "Número de lote já existente."], 409);
            }
            JsonView::render(["status" => "erro", "mensagem" => "Erro interno ao cadastrar.", "detalhe" => $e->getMessage()], 500);
        }
    }

    public function atualizar(): void {
        $input = Request::getBody();

        if (empty($input['id_eletronico']) || empty($input['nome'])) {
            JsonView::render(["status" => "erro", "mensagem" => "Campos 'id_eletronico' e 'nome' são obrigatórios."], 422);
        }

        $existente = $this->model->buscarPorId((int)$input['id_eletronico']);
        if (!$existente) {
            JsonView::render(["status" => "erro", "mensagem" => "Eletrônico não encontrado."], 404);
        }

        $this->model->atualizar($input);
        JsonView::render(["status" => "sucesso", "mensagem" => "Eletrônico atualizado com sucesso."], 200);
    }

    public function desativar(): void {
        $params = Request::getQueryParams();
        $id = isset($params['id']) ? (int)$params['id'] : null;

        if (!$id) {
            JsonView::render(["status" => "erro", "mensagem" => "Parâmetro 'id' é obrigatório via URL."], 400);
        }

        $desativado = $this->model->desativar($id);

        if ($desativado) {
            JsonView::render(["status" => "sucesso", "mensagem" => "Eletrônico desativado com sucesso."], 200);
        } else {
            JsonView::render(["status" => "erro", "mensagem" => "Eletrônico não encontrado ou já desativado."], 404);
        }
    }
}