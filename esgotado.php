<?php
session_start();
$estoque = $_SESSION['estoque'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produto Esgotado</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-mensagem">
  <div>
    <h1>Produto Esgotado ❌</h1>
    <p>Não há mais unidades disponíveis no estoque.</p>
    <p>Estoque atual: <strong><?php echo $estoque; ?></strong></p>
    <a class="btn" href="index.php">Voltar para a Página Inicial</a>
  </div>
</body>
</html>
