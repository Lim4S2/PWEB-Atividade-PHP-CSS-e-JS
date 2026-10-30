<?php
require_once 'conexao.php';
require_once 'protect.php';

$pageTitle = 'Novo Forno';
$currentPage = 'forno_criar';

$erro = '';
$codigo = '';
$tipo_material = '';
$capacidade_kg = '';
$potencia_kw = '';
$temperatura_max = '';
$status = 'disponivel';
$observacoes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $tipo_material = trim($_POST['tipo_material'] ?? '');
    $capacidade_kg = trim($_POST['capacidade_kg'] ?? '');
    $potencia_kw = trim($_POST['potencia_kw'] ?? '');
    $temperatura_max = trim($_POST['temperatura_max'] ?? '');
    $status = trim($_POST['status'] ?? 'disponivel');
    $observacoes = trim($_POST['observacoes'] ?? '');

    // Validação básica
    if (empty($codigo) || empty($tipo_material) || empty($capacidade_kg) || empty($potencia_kw) || empty($temperatura_max)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios (*).';
    } elseif (!is_numeric(str_replace(',', '.', $capacidade_kg)) || !is_numeric(str_replace(',', '.', $potencia_kw)) || !is_numeric(str_replace(',', '.', $temperatura_max))) {
        $erro = 'Os campos de capacidade, potência e temperatura devem ser numéricos válidos.';
    } else {
        $cap = (float)str_replace(',', '.', $capacidade_kg);
        $pot = (float)str_replace(',', '.', $potencia_kw);
        $temp = (float)str_replace(',', '.', $temperatura_max);

        try {
            // Boa prática 3: prepare e execute com '?' para todo dado externo
            $sql = "INSERT INTO fornos (codigo, tipo_material, capacidade_kg, potencia_kw, temperatura_max, status, observacoes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $codigo,
                $tipo_material,
                $cap,
                $pot,
                $temp,
                $status,
                $observacoes
            ]);

            header('Location: index.php?msg=criado');
            exit;
        } catch (PDOException $e) {
            // Boa prática 6: Log detalhado no servidor e aviso genérico ao usuário
            error_log("Erro ao cadastrar forno (PDOException): " . $e->getMessage());

            if ($e->getCode() == 23000) { // Violação de constraint (UNIQUE no código do forno)
                $erro = "Já existe um forno cadastrado com este código ({$codigo}). Escolha outro código identificador.";
            } else {
                $erro = "Ocorreu um erro interno no servidor ao tentar salvar o forno. Tente novamente mais tarde.";
            }
        }
    }
}

require_once 'header.php';
?>

<div class="page-head">
  <div>
    <h1 class="page-title">Cadastrar Novo Forno</h1>
    <p class="page-subtitle">Insira as informações técnicas para integrar uma nova unidade de carbonização ao sistema.</p>
  </div>
  <div>
    <a href="index.php" class="btn btn-neutro">⬅ Voltar para a Lista</a>
  </div>
</div>

<?php if (!empty($erro)): ?>
  <div class="alerta alerta-erro">
    <span>⚠️</span> <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<div class="card-container form-card">
  <form method="POST" action="forno_criar.php" class="crud-form">
    <div class="form-grid">
      <div class="campo">
        <label for="codigo">Código Identificador *</label>
        <input type="text" id="codigo" name="codigo" placeholder="Ex: FRN-04" value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>" required>
        <small class="campo-dica">Código único para identificação no pátio industrial.</small>
      </div>

      <div class="campo">
        <label for="tipo_material">Material do Revestimento *</label>
        <input type="text" id="tipo_material" name="tipo_material" placeholder="Ex: Alvenaria Refratária, Aço Carbono..." value="<?php echo htmlspecialchars($tipo_material, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="capacidade_kg">Capacidade Máxima (kg) *</label>
        <input type="text" id="capacidade_kg" name="capacidade_kg" placeholder="Ex: 4000.00" value="<?php echo htmlspecialchars($capacidade_kg, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="potencia_kw">Potência Térmica (kW) *</label>
        <input type="text" id="potencia_kw" name="potencia_kw" placeholder="Ex: 50.00" value="<?php echo htmlspecialchars($potencia_kw, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="temperatura_max">Temperatura Máx. (°C) *</label>
        <input type="text" id="temperatura_max" name="temperatura_max" placeholder="Ex: 700.00" value="<?php echo htmlspecialchars($temperatura_max, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="status">Status Operacional *</label>
        <select id="status" name="status" required>
          <option value="disponivel" <?php echo $status === 'disponivel' ? 'selected' : ''; ?>>Disponível para Queima</option>
          <option value="em_processo" <?php echo $status === 'em_processo' ? 'selected' : ''; ?>>Em Processo de Carbonização</option>
          <option value="manutencao" <?php echo $status === 'manutencao' ? 'selected' : ''; ?>>Em Manutenção Técnica</option>
        </select>
      </div>
    </div>

    <div class="campo campo-cheio">
      <label for="observacoes">Observações Técnicas</label>
      <textarea id="observacoes" name="observacoes" rows="3" placeholder="Detalhes dos termopares, válvulas de alívio ou histórico de isolamento..."><?php echo htmlspecialchars($observacoes, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-acoes">
      <button type="submit" class="btn btn-primario">Salvar Forno</button>
      <a href="index.php" class="btn btn-link">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
