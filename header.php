<?php
require_once 'conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | CarvoTrack' : 'CarvoTrack - Gestão de Carvoaria'; ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="app-body">
  <header class="app-header">
    <div class="header-container">
      <div class="header-brand">
        <a href="index.php" class="brand-link">
          <span class="brand-icon">🔥</span>
          <span class="brand-name">Carvo<strong>Track</strong></span>
          <span class="badge-tag">PI / PWEB</span>
        </a>
      </div>

      <nav class="app-nav">
        <ul>
          <li><a href="index.php" class="<?php echo ($currentPage ?? '') === 'fornos' ? 'ativo' : ''; ?>">Fornos (CRUD)</a></li>
          <li><a href="forno_criar.php" class="<?php echo ($currentPage ?? '') === 'forno_criar' ? 'ativo' : ''; ?>">+ Cadastrar Forno</a></li>
          <li><a href="novo_processo.php" class="<?php echo ($currentPage ?? '') === 'novo_processo' ? 'ativo' : ''; ?>">⚡ Novo Processo (Transação)</a></li>
          <li><a href="lotes.php" class="<?php echo ($currentPage ?? '') === 'lotes' ? 'ativo' : ''; ?>">Lotes de Madeira</a></li>
        </ul>
      </nav>

      <div class="header-user">
        <div class="user-info">
          <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Operador', ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="user-role"><?php echo htmlspecialchars($_SESSION['usuario_cargo'] ?? 'Operador', ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <a href="logout.php" class="btn btn-sair" title="Encerrar Sessão">Sair 🚪</a>
      </div>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
