<?php

namespace App\Http\Controllers;
use OpenApi\Attributes as OA;

/**
 * CONFIGURAÇÃO GLOBAL OBRIGATÓRIA DA DOCUMENTAÇÃO API
 */
#[OA\Info(
    title: "API de Sistema Hotelaria - Desafio Foco",
    version: "1.0.0",
    description: "Documentação interativa das rotas de gerenciamento de hotéis, quartos e reservas do sistema hoteleiro."
)]
#[OA\Server(
    url: "http://localhost/api",
    description: "Servidor Local Docker Sail"
)]
abstract class Controller
{
    // 
}
