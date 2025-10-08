<?php
include 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materias = carregarMaterias();

    $nova = [
        'id' => uniqid(),
        'nome' => $_POST['nome'],
        'max_faltas' => (int)$_POST['max_faltas'],
        'faltas' => 0
    ];

    $materias[] = $nova;
    salvarMaterias($materias);

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Nova Matéria - Faltômetro</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Nova Matéria</h1>
  <div class="container">
    <form method="POST">
      <label for="nome">Nome da matéria:</label>
      <input type="text" id="nome" name="nome" required>

      <label for="max_faltas">Número máximo de faltas permitidas:</label>
      <input type="number" id="max_faltas" name="max_faltas" min="1" required>

      <input type="submit" value="Salvar">
    </form>
    <br>
    <a href="index.php" class="button">Voltar</a>
  </div>
</body>
</html>
