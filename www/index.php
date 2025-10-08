<?php
include 'helpers.php';

$materias = carregarMaterias();


if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    foreach ($materias as &$m) {
        if ($m['id'] === $id) {
            if ($_GET['acao'] === 'add') $m['faltas']++;
            if ($_GET['acao'] === 'remove' && $m['faltas'] > 0) $m['faltas']--;
        }
    }
    salvarMaterias($materias);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}


if (isset($_GET['reset'])) {
    salvarMaterias([]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Faltômetro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Faltômetro</h1>

  <div class="container">
    <a href="materia.php" class="button">+ Nova Matéria</a>
    <button onclick="confirmarReset()">Resetar Tudo</button>
  </div>

  <?php if (empty($materias)): ?>
    <div class="container">
      <p>Nenhuma matéria cadastrada ainda :(</p>
    </div>
  <?php else: ?>
    <?php foreach ($materias as $m): 
        $total = $m['max_faltas'];
        $faltas = $m['faltas'];
        $percent = $total > 0 ? min(($faltas / $total) * 100, 120) : 0;

        if ($percent < 40) $cor = "#4CAF50";
        elseif ($percent < 65) $cor = "#FFEB3B";
        elseif ($percent < 80) $cor = "#FF9800";
        else $cor = "#F44336";
    ?>
      <div class="card">
        <h2><?= htmlspecialchars($m['nome']) ?></h2>
        <p>Faltas: <?= $faltas ?> / <?= $total ?></p>

        <div style="background:#eee; border-radius:10px; overflow:hidden; height:20px;">
          <div style="width:<?= $percent ?>%; background:<?= $cor ?>; height:100%;"></div>
        </div>

        <?php if ($percent > 100): ?>
          <p style="color:#d32f2f; font-weight:bold; margin-top:10px;">
            Já prepara o e-mail pra justificar a falta pro professor 😭😭
          </p>
        <?php endif; ?>

        <div style="margin-top:10px;">
  <button class="button" onclick="alterarFalta('add', '<?= $m['id'] ?>', this)">+ Falta</button>
  <button class="button" onclick="alterarFalta('remove', '<?= $m['id'] ?>', this)">- Falta</button>
</div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <script>
    function confirmarReset() {
      if (confirm('Tem certeza que deseja apagar todas as matérias e faltas?')) {
        window.location.href = '?reset=1';
      }
    }
  </script>

  <script>
async function alterarFalta(acao, id, botao) {
  try {
    const response = await fetch(`index.php?acao=${acao}&id=${id}`);
    if (!response.ok) throw new Error('Erro na requisição');

    const data = await response.json();
    if (data.status !== 'ok') throw new Error('Erro ao atualizar JSON');

    // Atualiza a interface sem recarregar
    const card = botao.closest('.card');
    const p = card.querySelector('p');
    const [_, faltas, total] = p.textContent.match(/Faltas: (\d+) \/ (\d+)/);
    let novoValor = parseInt(faltas);
    if (acao === 'add') novoValor++;
    if (acao === 'remove' && novoValor > 0) novoValor--;

    p.textContent = `Faltas: ${novoValor} / ${total}`;

    const barra = card.querySelector('div > div');
    const percent = Math.min((novoValor / total) * 100, 120);
    let cor = '#4CAF50';
    if (percent >= 40 && percent < 65) cor = '#FFEB3B';
    else if (percent >= 65 && percent < 80) cor = '#FF9800';
    else if (percent >= 80) cor = '#F44336';
    barra.style.width = `${percent}%`;
    barra.style.background = cor;

    let aviso = card.querySelector('.aviso-faltas');
    if (percent > 100) {
      if (!aviso) {
        aviso = document.createElement('p');
        aviso.className = 'aviso-faltas';
        aviso.style.color = '#d32f2f';
        aviso.style.fontWeight = 'bold';
        aviso.style.marginTop = '10px';
        aviso.textContent = 'Já prepara o e-mail pra justificar a falta pro professor 😭😭';
        card.appendChild(aviso);
      }
    } else if (aviso) {
      aviso.remove();
    }

  } catch (err) {
    alert('Erro ao atualizar falta: ' + err.message);
  }
}

</script>


</body>
</html>
