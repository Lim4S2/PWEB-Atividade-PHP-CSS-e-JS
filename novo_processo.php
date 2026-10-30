<?php
require_once 'conexao.php';
require_once 'protect.php';

$pageTitle = 'Novo Processo de Carbonização';
$currentPage = 'novo_processo';

$erro = '';
$sucesso = '';

// Carrega fornos e lotes de madeira disponíveis para o processo
$fornosDisponiveis = [];
$lotesDisponiveis = [];

try {
    $stmtFornos = $pdo->query("SELECT id, codigo, tipo_material, capacidade_kg, temperatura_max FROM fornos WHERE status = 'disponivel' ORDER BY codigo ASC");
    $fornosDisponiveis = $stmtFornos->fetchAll();

    $stmtLotes = $pdo->query("SELECT id, codigo_lote, especie_madeira, peso_lote_kg, umidade_percentual FROM lotes_madeira WHERE status = 'disponivel' ORDER BY codigo_lote ASC");
    $lotesDisponiveis = $stmtLotes->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar opções para novo processo: " . $e->getMessage());
    $erro = "Erro ao carregar os dados de fornos e lotes disponíveis.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forno_id = filter_input(INPUT_POST, 'forno_id', FILTER_VALIDATE_INT);
    $lote_id = filter_input(INPUT_POST, 'lote_id', FILTER_VALIDATE_INT);
    $temperatura_alvo = trim($_POST['temperatura_alvo'] ?? '');

    if (!$forno_id || !$lote_id || empty($temperatura_alvo)) {
        $erro = 'Por favor, selecione um forno disponível, um lote de madeira e defina a temperatura alvo.';
    } elseif (!is_numeric(str_replace(',', '.', $temperatura_alvo))) {
        $erro = 'A temperatura alvo deve ser um valor numérico válido.';
    } else {
        $tempAlvo = (float)str_replace(',', '.', $temperatura_alvo);

        // =====================================================================
        // BOA PRÁTICA 5: TRANSAÇÃO QUANDO A ESCRITA ENVOLVE VÁRIAS TABELAS
        // Tabelas envolvidas:
        // 1. processos_carbonizacao (INSERT)
        // 2. fornos (UPDATE status -> 'em_processo')
        // 3. lotes_madeira (UPDATE status -> 'em_uso')
        // 4. historico_acoes (INSERT auditoria)
        // =====================================================================
        try {
            $pdo->beginTransaction();

            // 1. Inserir o processo de carbonização
            $sqlProcesso = "INSERT INTO processos_carbonizacao (forno_id, lote_id, usuario_id, data_inicio, temperatura_alvo, status) 
                            VALUES (?, ?, ?, NOW(), ?, 'ativo')";
            $stmt1 = $pdo->prepare($sqlProcesso);
            $stmt1->execute([$forno_id, $lote_id, $_SESSION['usuario_id'], $tempAlvo]);
            $processoId = $pdo->lastInsertId();

            // 2. Atualizar status do forno para 'em_processo'
            $sqlForno = "UPDATE fornos SET status = 'em_processo' WHERE id = ?";
            $stmt2 = $pdo->prepare($sqlForno);
            $stmt2->execute([$forno_id]);

            // 3. Atualizar status do lote de madeira para 'em_uso'
            $sqlLote = "UPDATE lotes_madeira SET status = 'em_uso' WHERE id = ?";
            $stmt3 = $pdo->prepare($sqlLote);
            $stmt3->execute([$lote_id]);

            // 4. Registrar auditoria no histórico
            $sqlLog = "INSERT INTO historico_acoes (usuario_id, acao, detalhes) VALUES (?, ?, ?)";
            $stmt4 = $pdo->prepare($sqlLog);
            $detalhesLog = "Iniciado processo #{$processoId}: Forno ID {$forno_id}, Lote ID {$lote_id}, Temp Alvo: {$tempAlvo}°C";
            $stmt4->execute([$_SESSION['usuario_id'], 'INICIAR_CARBONIZACAO', $detalhesLog]);

            // Se todas as 4 operações executaram com sucesso, confirma no banco
            $pdo->commit();

            header('Location: index.php?msg=processo_iniciado');
            exit;

        } catch (PDOException $e) {
            // Em caso de qualquer erro em qualquer uma das tabelas, desfaz tudo
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Boa prática 6: Log no servidor e aviso genérico
            error_log("Falha na transação PDO de carbonização: " . $e->getMessage());
            $erro = "Ocorreu um erro interno ao iniciar o processo. A transação foi cancelada e nenhuma alteração foi gravada.";
        }
    }
}

require_once 'header.php';
?>

<div class="page-head">
  <div>
    <h1 class="page-title">⚡ Iniciar Processo de Carbonização</h1>
    <p class="page-subtitle">Demonstração da <strong>Boa Prática 5</strong>: Transação PDO com escrita atômica em 4 tabelas.</p>
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
  <div class="transacao-banner">
    <div class="banner-badge">Transação PDO Ativa</div>
    <p>Esta operação grava o processo, altera o status do forno para <em>Em Processo</em>, bloqueia o lote de madeira como <em>Em Uso</em> e registra o log de auditoria de forma atômica (<code>beginTransaction</code> / <code>commit</code> / <code>rollBack</code>).</p>
  </div>

  <form method="POST" action="novo_processo.php" class="crud-form">
    <div class="form-grid">
      <div class="campo">
        <label for="forno_id">Selecione o Forno Disponível *</label>
        <select id="forno_id" name="forno_id" required>
          <option value="">-- Escolha um Forno --</option>
          <?php foreach ($fornosDisponiveis as $f): ?>
            <option value="<?php echo (int)$f['id']; ?>">
              <?php echo htmlspecialchars($f['codigo'] . ' - ' . $f['tipo_material'] . ' (Cap: ' . number_format($f['capacidade_kg'], 0, '', '.') . 'kg)', ENT_QUOTES, 'UTF-8'); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($fornosDisponiveis)): ?>
          <small class="campo-alerta">Nenhum forno disponível no momento. <a href="forno_criar.php">Cadastre um novo</a>.</small>
        <?php endif; ?>
      </div>

      <div class="campo">
        <label for="lote_id">Selecione o Lote de Madeira *</label>
        <select id="lote_id" name="lote_id" required>
          <option value="">-- Escolha um Lote --</option>
          <?php foreach ($lotesDisponiveis as $l): ?>
            <option value="<?php echo (int)$l['id']; ?>">
              <?php echo htmlspecialchars($l['codigo_lote'] . ' - ' . $l['especie_madeira'] . ' (' . number_format($l['peso_lote_kg'], 0, '', '.') . 'kg, ' . $l['umidade_percentual'] . '% umid.)', ENT_QUOTES, 'UTF-8'); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($lotesDisponiveis)): ?>
          <small class="campo-alerta">Nenhum lote disponível no estoque. <a href="lote_criar.php">Cadastre um lote</a>.</small>
        <?php endif; ?>
      </div>

      <div class="campo campo-cheio">
        <label for="temperatura_alvo">Temperatura Alvo de Carbonização (°C) *</label>
        <input type="text" id="temperatura_alvo" name="temperatura_alvo" placeholder="Ex: 550.00" value="550.00" required>
        <small class="campo-dica">Temperatura programada para as válvulas e sensores manterem durante a pirólise da madeira.</small>
      </div>
    </div>

    <div class="form-acoes">
      <button type="submit" class="btn btn-primario" <?php echo (empty($fornosDisponiveis) || empty($lotesDisponiveis)) ? 'disabled' : ''; ?>>
        🚀 Executar Transação e Iniciar Processo
      </button>
      <a href="index.php" class="btn btn-link">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
