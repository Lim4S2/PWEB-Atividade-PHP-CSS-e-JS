<?php
require_once 'conexao.php';
require_once 'protect.php';

$pageTitle = 'Novo Lote de Madeira';
$currentPage = 'lotes';

$erro = '';
$codigo_lote = '';
$especie_madeira = '';
$peso_lote_kg = '';
$umidade_percentual = '';
$densidade_kg_m3 = '';
$data_entrada = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_lote = trim($_POST['codigo_lote'] ?? '');
    $especie_madeira = trim($_POST['especie_madeira'] ?? '');
    $peso_lote_kg = trim($_POST['peso_lote_kg'] ?? '');
    $umidade_percentual = trim($_POST['umidade_percentual'] ?? '');
    $densidade_kg_m3 = trim($_POST['densidade_kg_m3'] ?? '');
    $data_entrada = trim($_POST['data_entrada'] ?? date('Y-m-d'));

    if (empty($codigo_lote) || empty($especie_madeira) || empty($peso_lote_kg) || empty($umidade_percentual) || empty($densidade_kg_m3) || empty($data_entrada)) {
        $erro = 'Preencha todos os campos obrigatórios (*).';
    } elseif (!is_numeric(str_replace(',', '.', $peso_lote_kg)) || !is_numeric(str_replace(',', '.', $umidade_percentual)) || !is_numeric(str_replace(',', '.', $densidade_kg_m3))) {
        $erro = 'Os valores de peso, umidade e densidade devem ser números válidos.';
    } else {
        $peso = (float)str_replace(',', '.', $peso_lote_kg);
        $umid = (float)str_replace(',', '.', $umidade_percentual);
        $dens = (float)str_replace(',', '.', $densidade_kg_m3);

        try {
            // Boa prática 3: prepare e execute com '?' para todo dado externo
            $sql = "INSERT INTO lotes_madeira (codigo_lote, especie_madeira, peso_lote_kg, umidade_percentual, densidade_kg_m3, status, data_entrada) 
                    VALUES (?, ?, ?, ?, ?, 'disponivel', ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $codigo_lote,
                $especie_madeira,
                $peso,
                $umid,
                $dens,
                $data_entrada
            ]);

            header('Location: lotes.php?msg=criado');
            exit;
        } catch (PDOException $e) {
            // Boa prática 6: Log no servidor e aviso genérico
            error_log("Erro ao salvar lote de madeira (PDOException): " . $e->getMessage());

            if ($e->getCode() == 23000) {
                $erro = "Já existe um lote cadastrado com o código ({$codigo_lote}).";
            } else {
                $erro = "Ocorreu um erro interno no servidor ao tentar registrar o lote de madeira.";
            }
        }
    }
}

require_once 'header.php';
?>

<div class="page-head">
  <div>
    <h1 class="page-title">Cadastrar Lote de Madeira</h1>
    <p class="page-subtitle">Registro de biomassa florestal para controle de estoque e queima.</p>
  </div>
  <div>
    <a href="lotes.php" class="btn btn-neutro">⬅ Voltar aos Lotes</a>
  </div>
</div>

<?php if (!empty($erro)): ?>
  <div class="alerta alerta-erro">
    <span>⚠️</span> <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<div class="card-container form-card">
  <form method="POST" action="lote_criar.php" class="crud-form">
    <div class="form-grid">
      <div class="campo">
        <label for="codigo_lote">Código do Lote *</label>
        <input type="text" id="codigo_lote" name="codigo_lote" placeholder="Ex: LOT-EUC-2026-04" value="<?php echo htmlspecialchars($codigo_lote, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="especie_madeira">Espécie da Madeira *</label>
        <input type="text" id="especie_madeira" name="especie_madeira" placeholder="Ex: Eucalipto Saligna, Eucalipto Grandis, Acácia..." value="<?php echo htmlspecialchars($especie_madeira, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="peso_lote_kg">Peso Total do Lote (kg) *</label>
        <input type="text" id="peso_lote_kg" name="peso_lote_kg" placeholder="Ex: 3500.00" value="<?php echo htmlspecialchars($peso_lote_kg, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="umidade_percentual">Teor de Umidade (%) *</label>
        <input type="text" id="umidade_percentual" name="umidade_percentual" placeholder="Ex: 15.50" value="<?php echo htmlspecialchars($umidade_percentual, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="densidade_kg_m3">Densidade Básica (kg/m³) *</label>
        <input type="text" id="densidade_kg_m3" name="densidade_kg_m3" placeholder="Ex: 650.00" value="<?php echo htmlspecialchars($densidade_kg_m3, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>

      <div class="campo">
        <label for="data_entrada">Data de Entrada no Pátio *</label>
        <input type="date" id="data_entrada" name="data_entrada" value="<?php echo htmlspecialchars($data_entrada, ENT_QUOTES, 'UTF-8'); ?>" required>
      </div>
    </div>

    <div class="form-acoes">
      <button type="submit" class="btn btn-primario">Salvar Lote de Madeira</button>
      <a href="lotes.php" class="btn btn-link">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once 'footer.php'; ?>
