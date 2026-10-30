<?php
require_once 'conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona para a tela inicial do sistema
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'nao_autenticado') {
    $erro = 'Acesso restrito. Faça login para continuar.';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'logout') {
    $sucesso = 'Sessão encerrada com sucesso.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        try {
            // Verifica se a tabela usuarios está vazia e cria o usuário inicial de demonstração
            $stmtCount = $pdo->query("SELECT COUNT(*) FROM usuarios");
            if ($stmtCount->fetchColumn() == 0) {
                $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
                $stmtSeed = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, ?)");
                $stmtSeed->execute(['Administrador CarvoTrack', 'admin@carvotrack.com', $senhaHash, 'Administrador']);
            }

            // Busca usuário pelo e-mail utilizando prepare e execute com '?' (Boa prática 3)
            $stmt = $pdo->prepare("SELECT id, nome, email, senha, cargo FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Sessão inicializada com sucesso
                $_SESSION['usuario_id']    = $usuario['id'];
                $_SESSION['usuario_nome']  = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_cargo'] = $usuario['cargo'];

                header('Location: index.php');
                exit;
            } else {
                $erro = 'E-mail ou senha incorretos.';
            }
        } catch (PDOException $e) {
            // Boa prática 6: Log detalhado no servidor, aviso genérico para o usuário
            error_log("Erro no login (PDOException): " . $e->getMessage());
            $erro = "Ocorreu um erro interno ao processar a autenticação. Tente novamente mais tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | CarvoTrack</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="auth-brand">
      <div class="brand-badge">🔥 Carvo<span>Track</span></div>
      <h2>Sistema de Gestão & Monitoramento</h2>
      <p class="subtitle">Controle de Fornos e Produção de Carvão Vegetal</p>
    </div>

    <?php if (!empty($erro)): ?>
      <div class="alerta alerta-erro">
        <span>⚠️</span> <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
      <div class="alerta alerta-sucesso">
        <span>✅</span> <?php echo htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="auth-form">
      <div class="campo">
        <label for="email">E-mail de Acesso</label>
        <input type="email" id="email" name="email" placeholder="admin@carvotrack.com" value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@carvotrack.com', ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="••••••••" value="admin123" required>
      </div>

      <button type="submit" class="btn btn-primario btn-bloco">Acessar Sistema</button>
    </form>

    <div class="demo-box">
      <strong>Credenciais de Demonstração:</strong>
      <p>E-mail: <code>admin@carvotrack.com</code> | Senha: <code>admin123</code></p>
      <small>Pedro Silveira • RA: 118771</small>
    </div>
  </div>
</body>
</html>
