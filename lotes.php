<?php
require_once 'conexao.php';
require_once 'protect.php';

$pageTitle = 'Lotes de Madeira';
$currentPage = 'lotes';

$mensagemSucesso = '';
$mensagemErro = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'criado') {
    $mensagemSucesso = 'Lote de madeira cadastrado com sucesso!';
}

$lotes = [];

try {
    $stmt = $pdo->query("SELECT * FROM lotes_madeira ORDER BY id DESC");
    $lotes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao carregar lotes de madeira: " . $e->getMessage());
    $mensagemErro = "Ocorreu um erro interno ao carregar a listagem de lotes.";
}

require_once 'header.php';
?>

<div class="page-head">
  <div>
    <h1 class="page-title">Lotes de Madeira (Biomassa)</h1>
    <p class="page-subtitle">Gestão de matéria-prima para o processo de pirólise e carbonização.</p>
  </div>
  <div>
    <a href="lote_criar.php" class="btn btn-primario">+ Novo Lote</a>
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

<div class="card-container">
  <div class="tabela-responsiva">
    <table class="tabela">
      <thead>
        <tr>
          <th>Código do Lote</th>
          <th>Espécie de Madeira</th>
          <th>Peso (kg)</th>
          <th>Umidade</th>
          <th>Densidade</th>
          <th>Entrada</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($lotes)): ?>
          <tr>
            <td colspan="7" class="tabela-vazia">Nenhum lote registrado. <a href="lote_criar.php">Cadastre um lote</a>.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($lotes as $l): ?>
            <tr>
              <td><strong class="codigo-destaque"><?php echo htmlspecialchars($l['codigo_lote'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
              <td><?php echo htmlspecialchars($l['especie_madeira'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo number_format($l['peso_lote_kg'], 2, ',', '.'); ?> kg</td>
              <td><?php echo number_format($l['umidade_percentual'], 1, ',', '.'); ?>%</td>
              <td><?php echo number_format($l['densidade_kg_m3'], 1, ',', '.'); ?> kg/m³</td>
              <td><?php echo date('d/m/Y', strtotime($l['data_entrada'])); ?></td>
              <td>
                <?php if ($l['status'] === 'disponivel'): ?>
                  <span class="badge badge-verde">Disponível</span>
                <?php elseif ($l['status'] === 'em_uso'): ?>
                  <span class="badge badge-laranja">Em Queima</span>
                <?php else: ?>
                  <span class="badge badge-azul">Concluído</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'footer.php'; ?>
