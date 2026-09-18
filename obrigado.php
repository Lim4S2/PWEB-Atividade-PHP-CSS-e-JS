<?php
session_start();
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
  <div>
    <h1>Obrigado pela sua compra! 🎉</h1>
    <p>Assim que o pagamento for confirmado, por favor envie um e-mail para <strong>contato@codigofonte.tv</strong> com seus dados para envio.</p>
    <p>Unidades restantes: <strong><?php echo $estoque; ?></strong></p>
    <a class="btn" href="index.php">Voltar para a Página Inicial</a>
  </div>
</body>
</html>
