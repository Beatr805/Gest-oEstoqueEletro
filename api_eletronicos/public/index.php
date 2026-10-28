<?php

// Habilitar exibição de erros do PHP para diagnóstico imediato
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Importação segura dos arquivos necessários
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Request.php';
require_once __DIR__ . '/../app/Views/JsonView.php';
require_once __DIR__ . '/../app/Models/Eletronico.php';
require_once __DIR__ . '/../app/Models/Estoque.php';
require_once __DIR__ . '/../app/Models/Movimentacao.php';
require_once __DIR__ . '/../app/Controllers/EletronicoController.php';
require_once __DIR__ . '/../app/Controllers/EstoqueController.php';
require_once __DIR__ . '/../app/Controllers/MovimentacaoController.php';

use App\Core\Request;
use App\Controllers\EletronicoController;
use App\Controllers\EstoqueController;
use App\Controllers\MovimentacaoController;
use App\Views\JsonView;

// Headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = Request::getMethod();
$uri = Request::getUri();

// Roteamento para as 6 rotas obrigatórias
switch (true) {
    case ($method === 'GET' && $uri === '/eletronico'):
        (new EletronicoController())->listar();
        break;

    case ($method === 'POST' && $uri === '/eletronico'):
        (new EletronicoController())->cadastrar();
        break;

    case ($method === 'PUT' && $uri === '/eletronico'):
        (new EletronicoController())->atualizar();
        break;

    case ($method === 'DELETE' && $uri === '/eletronico'):
        (new EletronicoController())->desativar();
        break;

    case ($method === 'PUT' && $uri === '/estoque'):
        (new EstoqueController())->movimentar();
        break;

    case ($method === 'GET' && $uri === '/movimentacoes'):
        (new MovimentacaoController())->listar();
        break;

    default:
        JsonView::render(["status" => "erro", "mensagem" => "Rota não encontrada (404).", "uri_recebida" => $uri], 404);
        break;
}