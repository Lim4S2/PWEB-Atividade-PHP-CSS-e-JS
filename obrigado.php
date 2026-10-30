<?php
require_once 'conexao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$estoque = $_SESSION['estoque'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Obrigado pela sua compra!</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-mensagem">
  <div style="max-width: 600px; margin: 4rem auto; text-align: center; padding: 2rem; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
    <h1>Obrigado pela sua compra! 🎉</h1>
    <p>Operação concluída com sucesso no sistema.</p>
    <a class="btn btn-primario" href="index.php" style="margin-top: 1.5rem;">Voltar ao Início</a>
  </div>
</body>
</html>
