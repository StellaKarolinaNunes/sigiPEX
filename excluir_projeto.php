<?php
session_start();
if (!isset($_SESSION['siape'])) {
    echo json_encode(["error" => "Usuário não autenticado."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$codigo_projeto = $data['codigo_projeto'] ?? '';
if (!$codigo_projeto) {
    echo json_encode(["error" => "Código do projeto não informado."]);
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=sigipex;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Verifica se o projeto pertence ao professor logado
$stmt = $pdo->prepare("SELECT siape_professor FROM projetos WHERE codigo_projeto = ?");
$stmt->execute([$codigo_projeto]);
$projeto = $stmt->fetch();
if (!$projeto || $projeto['siape_professor'] != $_SESSION['siape']) {
    echo json_encode(["error" => "Projeto não encontrado ou acesso negado."]);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM projetos WHERE codigo_projeto = ?");
    $stmt->execute([$codigo_projeto]);
    echo json_encode(["message" => "Projeto excluído com sucesso!"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao excluir projeto: " . $e->getMessage()]);
}
?>
