<?php
require_once 'conexao.php';
require_once 'protect.php';

$pageTitle = 'Editar Forno';
$currentPage = 'fornos';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?msg=erro');
    exit;
}

$erro = '';

// Carrega os dados atuais do forno utilizando prepare com '?' (Boa prática 3)
try {
    $stmt = $pdo->prepare("SELECT * FROM fornos WHERE id = ?");
    $stmt->execute([$id]);
    $forno = $stmt->fetch();

    if (!$forno) {
        header('Location: index.php?msg=erro');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao carregar forno para edição: " . $e->getMessage());
    header('Location: index.php?msg=erro');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $tipo_material = trim($_POST['tipo_material'] ?? '');
    $capacidade_kg = trim($_POST['capacidade_kg'] ?? '');
    $potencia_kw = trim($_POST['potencia_kw'] ?? '');
    $temperatura_max = trim($_POST['temperatura_max'] ?? '');
    $status = trim($_POST['status'] ?? 'disponivel');
    $observacoes = trim($_POST['observacoes'] ?? '');

    if (empty($codigo) || empty($tipo_material) || empty($capacidade_kg) || empty($potencia_kw) || empty($temperatura_max)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios (*).';
    } elseif (!is_numeric(str_replace(',', '.', $capacidade_kg)) || !is_numeric(str_replace(',', '.', $potencia_kw)) || !is_numeric(str_replace(',', '.', $temperatura_max))) {
        $erro = 'Os campos de capacidade, potência e temperatura devem ser números válidos.';
    } else {
        $cap = (float)str_replace(',', '.', $capacidade_kg);
        $pot = (float)str_replace(',', '.', $potencia_kw);
        $temp = (float)str_replace(',', '.', $temperatura_max);

        try {
            // Boa prática 3: prepare e execute com '?' para todo dado externo
            $sql = "UPDATE fornos 
                    SET codigo = ?, tipo_material = ?, capacidade_kg = ?, potencia_kw = ?, temperatura_max = ?, status = ?, observacoes = ? 
                    WHERE id = ?";
            $stmtUpdate = $pdo->prepare($sql);
            $stmtUpdate->execute([
                $codigo,
                $tipo_material,
                $cap,
                $pot,
                $temp,
                $status,
                $observacoes,
                $id
            ]);

            header('Location: index.php?msg=atualizado');
            exit;
        } catch (PDOException $e) {
            // Boa prática 6: Log no servidor e aviso genérico
            error_log("Erro ao atualizar forno (PDOException): " . $e->getMessage());

            if ($e->getCode() == 23000) {
                $erro = "Já existe outro forno cadastrado com o código ({$codigo}).";
            } else {
                $erro = "Ocorreu um erro interno no servidor ao tentar atualizar o forno. Tente novamente mais tarde.";
            }
        }
    }
} else {
    // Preenche com os dados do banco
    $codigo = $forno['codigo'];
    $tipo_material = $forno['tipo_material'];
    $capacidade_kg = $forno['capacidade_kg'];
    $potencia_kw = $forno['potencia_kw'];
    $temperatura_max = $forno['temperatura_max'];
    $status = $forno['status'];
    $observacoes = $forno['observacoes'];
}

require_once 'header.php';
?>

<div class="page-head">
  <div>
    <h1 class="page-title">Editar Forno #<?php echo htmlspecialchars($forno['codigo'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="page-subtitle">Atualize os parâmetros técnicos e o status operacional da unidade.</p>
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
  <form method="POST" action="forno_editar.php?id=<?php echo (int)$id; ?>" class="crud-form">
    <div class="form-grid">
      <div class="campo">
        <label for="codigo">Código Identificador *</label>
        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="tipo_material">Material do Revestimento *</label>
        <input type="text" id="tipo_material" name="tipo_material" value="<?php echo htmlspecialchars($tipo_material, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="capacidade_kg">Capacidade Máxima (kg) *</label>
        <input type="text" id="capacidade_kg" name="capacidade_kg" value="<?php echo htmlspecialchars($capacidade_kg, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="potencia_kw">Potência Térmica (kW) *</label>
        <input type="text" id="potencia_kw" name="potencia_kw" value="<?php echo htmlspecialchars($potencia_kw, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="temperatura_max">Temperatura Máx. (°C) *</label>
        <input type="text" id="temperatura_max" name="temperatura_max" value="<?php echo htmlspecialchars($temperatura_max, ENT_QUOTES, 'UTF-8'); ?>" required>
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
      <textarea id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($observacoes, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-acoes">
      <button type="submit" class="btn btn-primario">Salvar Alterações</button>
      <a href="index.php" class="btn btn-link">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
