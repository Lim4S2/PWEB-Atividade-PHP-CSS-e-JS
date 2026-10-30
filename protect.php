<?php
require_once 'conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado na sessão
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?msg=nao_autenticado');
    exit;
}

// Dados do usuário autenticado disponíveis para as páginas protegidas
$usuarioAtual = [
    'id'    => $_SESSION['usuario_id'],
    'nome'  => $_SESSION['usuario_nome'] ?? 'Operador',
    'email' => $_SESSION['usuario_email'] ?? '',
    'cargo' => $_SESSION['usuario_cargo'] ?? 'Operador'
];
