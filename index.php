<?php
require_once 'conexao.php';
require_once 'protect.php';

$pageTitle = 'Painel de Fornos';
$currentPage = 'fornos';

$mensagemSucesso = '';
$mensagemErro = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'criado') {
        $mensagemSucesso = 'Forno cadastrado com sucesso!';
    } elseif ($_GET['msg'] === 'atualizado') {
        $mensagemSucesso = 'Dados do forno atualizados com sucesso!';
    } elseif ($_GET['msg'] === 'deletado') {
        $mensagemSucesso = 'Forno removido com sucesso!';
    } elseif ($_GET['msg'] === 'processo_iniciado') {
        $mensagemSucesso = 'Processo de carbonização iniciado com sucesso! (Transação PDO confirmada)';
    } elseif ($_GET['msg'] === 'erro') {
        $mensagemErro = 'Não foi possível completar a operação solicitada.';
    }
}

// Filtro de busca (Read do CRUD)
$busca = trim($_GET['busca'] ?? '');
$filtroStatus = trim($_GET['status'] ?? '');

$fornos = [];
$totalFornos = 0;
$totalDisponiveis = 0;
$totalEmProcesso = 0;
$totalManutencao = 0;

try {
    // Métricas rápidas de status dos fornos
    $stmtMetricas = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'disponivel' THEN 1 ELSE 0 END) as disponiveis,
        SUM(CASE WHEN status = 'em_processo' THEN 1 ELSE 0 END) as em_processo,
        SUM(CASE WHEN status = 'manutencao' THEN 1 ELSE 0 END) as manutencao
    FROM fornos");
    $metricas = $stmtMetricas->fetch();
    if ($metricas) {
        $totalFornos = (int)$metricas['total'];
        $totalDisponiveis = (int)$metricas['disponiveis'];
        $totalEmProcesso = (int)$metricas['em_processo'];
        $totalManutencao = (int)$metricas['manutencao'];
    }

    // Consulta com filtros usando prepare() e execute() com '?' (Boa prática 3)
    $sql = "SELECT * FROM fornos WHERE 1=1";
    $params = [];

    if (!empty($busca)) {
        $sql .= " AND (codigo LIKE ? OR tipo_material LIKE ? OR observacoes LIKE ?)";
        $termo = "%{$busca}%";
        $params[] = $termo;
        $params[] = $termo;
        $params[] = $termo;
    }

    if (!empty($filtroStatus)) {
        $sql .= " AND status = ?";
        $params[] = $filtroStatus;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fornos = $stmt->fetchAll();

} catch (PDOException $e) {
    // Boa prática 6: Log no servidor e aviso genérico
    error_log("Erro ao listar fornos (PDOException): " . $e->getMessage());
    $mensagemErro = "Ocorreu um erro interno ao carregar os dados dos fornos. Tente novamente mais tarde.";
}

require_once 'header.php';
?>

<div class="page-head">
  <div>
    <h1 class="page-title">Fornos de Carbonização</h1>
    <p class="page-subtitle">Gestão e monitoramento dos fornos industriais de queima controlada.</p>
  </div>
  <div class="page-actions">
    <a href="forno_criar.php" class="btn btn-primario">+ Novo Forno</a>
    <a href="novo_processo.php" class="btn btn-secundario">⚡ Iniciar Processo (Transação)</a>
  </div>
</div>

<?php if (!empty($mensagemSucesso)): ?>
  <div class="alerta alerta-sucesso">
    <span>✅</span> <?php echo htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<?php if (!empty($mensagemErro)): ?>
  <div class="alerta alerta-erro">
    <span>⚠️</span> <?php echo htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<!-- Cards de Resumo -->
<div class="metricas-grid">
  <div class="card-metrica">
    <span class="metrica-icone">🏭</span>
    <div class="metrica-dados">
      <span class="metrica-rotulo">Total de Fornos</span>
      <strong class="metrica-valor"><?php echo $totalFornos; ?></strong>
    </div>
  </div>

  <div class="card-metrica verde">
    <span class="metrica-icone">🟢</span>
    <div class="metrica-dados">
      <span class="metrica-rotulo">Disponíveis</span>
      <strong class="metrica-valor"><?php echo $totalDisponiveis; ?></strong>
    </div>
  </div>

  <div class="card-metrica laranja">
    <span class="metrica-icone">🔥</span>
    <div class="metrica-dados">
      <span class="metrica-rotulo">Em Carbonização</span>
      <strong class="metrica-valor"><?php echo $totalEmProcesso; ?></strong>
    </div>
  </div>

  <div class="card-metrica vermelho">
    <span class="metrica-icone">🛠️</span>
    <div class="metrica-dados">
      <span class="metrica-rotulo">Em Manutenção</span>
      <strong class="metrica-valor"><?php echo $totalManutencao; ?></strong>
    </div>
  </div>
</div>

<!-- Barra de Busca e Filtros -->
<div class="card-container">
  <form method="GET" action="index.php" class="filtro-form">
    <div class="campo campo-busca">
      <input type="text" name="busca" placeholder="Buscar por código ou material..." value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="campo campo-select">
      <select name="status">
        <option value="">Todos os Status</option>
        <option value="disponivel" <?php echo $filtroStatus === 'disponivel' ? 'selected' : ''; ?>>Disponível</option>
        <option value="em_processo" <?php echo $filtroStatus === 'em_processo' ? 'selected' : ''; ?>>Em Processo</option>
        <option value="manutencao" <?php echo $filtroStatus === 'manutencao' ? 'selected' : ''; ?>>Manutenção</option>
      </select>
    </div>

    <button type="submit" class="btn btn-neutro">Filtrar</button>
    <?php if (!empty($busca) || !empty($filtroStatus)): ?>
      <a href="index.php" class="btn btn-link">Limpar</a>
    <?php endif; ?>
  </form>

  <!-- Tabela do CRUD (Read) -->
  <div class="tabela-responsiva">
    <table class="tabela">
      <thead>
        <tr>
          <th>Código</th>
          <th>Material</th>
          <th>Capacidade</th>
          <th>Potência</th>
          <th>Temp. Máx.</th>
          <th>Status</th>
          <th class="acoes-col">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($fornos)): ?>
          <tr>
            <td colspan="7" class="tabela-vazia">Nenhum forno encontrado. <a href="forno_criar.php">Cadastre o primeiro forno</a>.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($fornos as $f): ?>
            <tr>
              <td>
                <strong class="codigo-destaque"><?php echo htmlspecialchars($f['codigo'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php if (!empty($f['observacoes'])): ?>
                  <span class="tabela-obs" title="<?php echo htmlspecialchars($f['observacoes'], ENT_QUOTES, 'UTF-8'); ?>">ℹ️</span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($f['tipo_material'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo number_format($f['capacidade_kg'], 2, ',', '.'); ?> kg</td>
              <td><?php echo number_format($f['potencia_kw'], 2, ',', '.'); ?> kW</td>
              <td><?php echo number_format($f['temperatura_max'], 1, ',', '.'); ?> °C</td>
              <td>
                <?php if ($f['status'] === 'disponivel'): ?>
                  <span class="badge badge-verde">Disponível</span>
                <?php elseif ($f['status'] === 'em_processo'): ?>
                  <span class="badge badge-laranja">Em Processo</span>
                <?php else: ?>
                  <span class="badge badge-vermelho">Manutenção</span>
                <?php endif; ?>
              </td>
              <td class="acoes-col">
                <a href="forno_editar.php?id=<?php echo (int)$f['id']; ?>" class="btn-acao btn-editar" title="Editar">✏️ Editar</a>
                <a href="forno_deletar.php?id=<?php echo (int)$f['id']; ?>" class="btn-acao btn-excluir" title="Excluir">🗑️ Excluir</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'footer.php'; ?>
