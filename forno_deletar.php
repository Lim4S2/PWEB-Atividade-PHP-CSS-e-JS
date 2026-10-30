<?php
require_once 'conexao.php';
require_once 'protect.php';

$pageTitle = 'Excluir Forno';
$currentPage = 'fornos';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?msg=erro');
    exit;
}

$erro = '';

// Busca informações do forno com prepare e execute com '?' (Boa prática 3)
try {
    $stmt = $pdo->prepare("SELECT * FROM fornos WHERE id = ?");
    $stmt->execute([$id]);
    $forno = $stmt->fetch();

    if (!$forno) {
        header('Location: index.php?msg=erro');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar forno para exclusão: " . $e->getMessage());
    header('Location: index.php?msg=erro');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_exclusao'])) {
    try {
        // Verifica se existem processos vinculados a este forno
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM processos_carbonizacao WHERE forno_id = ?");
        $stmtCheck->execute([$id]);
        $temProcessos = $stmtCheck->fetchColumn() > 0;

        if ($temProcessos) {
            $erro = "Este forno não pode ser excluído pois possui histórico de processos de carbonização registrados no sistema.";
        } else {
            // Executa a exclusão com prepare() e execute() com '?' (Boa prática 3)
            $stmtDel = $pdo->prepare("DELETE FROM fornos WHERE id = ?");
            $stmtDel->execute([$id]);

            header('Location: index.php?msg=deletado');
            exit;
        }
    } catch (PDOException $e) {
        // Boa prática 6: Log no servidor e aviso genérico
        error_log("Erro ao excluir forno (PDOException): " . $e->getMessage());
        $erro = "Ocorreu um erro interno ao tentar remover o forno. Tente novamente mais tarde.";
    }
}

require_once 'header.php';
?>

<div class="page-head">
  <div>
    <h1 class="page-title">Excluir Forno</h1>
    <p class="page-subtitle">Confirmação de exclusão permanente do cadastro.</p>
  </div>
  <div>
    <a href="index.php" class="btn btn-neutro">⬅ Voltar</a>
  </div>
</div>

<?php if (!empty($erro)): ?>
  <div class="alerta alerta-erro">
    <span>⚠️</span> <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<div class="card-container form-card">
  <div class="confirmacao-box">
    <div class="confirmacao-icone">⚠️</div>
    <h2>Tem certeza que deseja excluir este forno?</h2>
    <p>Esta operação removerá permanentemente o cadastro da unidade do banco de dados.</p>

    <div class="detalhes-exclusao">
      <p><strong>Código:</strong> <?php echo htmlspecialchars($forno['codigo'], ENT_QUOTES, 'UTF-8'); ?></p>
      <p><strong>Material:</strong> <?php echo htmlspecialchars($forno['tipo_material'], ENT_QUOTES, 'UTF-8'); ?></p>
      <p><strong>Capacidade:</strong> <?php echo number_format($forno['capacidade_kg'], 2, ',', '.'); ?> kg</p>
      <p><strong>Status Atual:</strong> <?php echo htmlspecialchars($forno['status'], ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <form method="POST" action="forno_deletar.php?id=<?php echo (int)$id; ?>" class="form-acoes">
      <input type="hidden" name="confirmar_exclusao" value="1">
      <button type="submit" class="btn btn-perigo">Sim, Excluir Forno</button>
      <a href="index.php" class="btn btn-link">Cancelar e Voltar</a>
    </form>
  </div>
</div>

<?php require_once 'footer.php'; ?>
