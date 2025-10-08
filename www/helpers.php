<?php
function carregarMaterias($arquivo = 'materias.json') {
    if (!file_exists($arquivo)) file_put_contents($arquivo, json_encode([]));
    return json_decode(file_get_contents($arquivo), true);
}

function salvarMaterias($materias, $arquivo = 'materias.json') {
    file_put_contents($arquivo, json_encode($materias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function buscarMateriaPorId($id, &$materias) {
    foreach ($materias as &$m) if ($m['id'] === $id) return $m;
    return null;
}
?>
