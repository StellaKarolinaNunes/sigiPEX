<?php
session_start();
if (!isset($_SESSION['siape'])) {
    echo json_encode(["error" => "Usuário não autenticado."]);
    exit();
}

$codigo_projeto = $_GET['codigo_projeto'] ?? '';
if (!$codigo_projeto) {
    echo json_encode(["error" => "Código do projeto não informado."]);
    exit();
}

require_once 'db_config.php';

$stmt = $pdo->prepare("SELECT * FROM projetos WHERE codigo_projeto = ? AND siape_professor = ?");
$stmt->execute([$codigo_projeto, $_SESSION['siape']]);
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
    echo json_encode(["error" => "Projeto não encontrado ou acesso negado."]);
    exit();
}

echo json_encode($projeto);
?>
