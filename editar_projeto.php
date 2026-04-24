<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['siape'])) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sigipex;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $codigo_projeto = $_POST['codigo_projeto'] ?? '';
    if (!$codigo_projeto) {
        echo json_encode(["error" => "ID do projeto não informado para edição."]);
        exit();
    }

    // Dados Básicos
    $nome_projeto      = $_POST['nome_projeto'] ?? '';
    $resumo_projeto    = $_POST['resumo_projeto'] ?? '';
    $codigo_turma      = $_POST['codigo_turma'] ?? 'TI';
    $github_link       = $_POST['github_link'] ?? null;
    $categoria         = $_POST['categoria'] ?? 'Extensão';
    $orientador        = $_POST['orientador'] ?? '';
    $campus            = $_POST['campus'] ?? '';
    $siape_professor   = $_SESSION['siape'];

    // Processamento de Arrays
    $linguagens = isset($_POST['linguagens']) ? implode(", ", $_POST['linguagens']) : "";
    $coorientadores = isset($_POST['coorientadores']) ? implode(", ", $_POST['coorientadores']) : "";

    // SQL Base
    $sql = "UPDATE projetos SET 
                nome_projeto = ?, coorientador_projeto = ?, resumo_projeto = ?, 
                codigo_turma = ?, github_link = ?, linguagem_projeto = ?, 
                categoria_projeto = ?, orientador_projeto = ?, campus_projeto = ?
            WHERE codigo_projeto = ? AND siape_professor = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nome_projeto, $coorientadores, $resumo_projeto, 
        $codigo_turma, $github_link, $linguagens, $categoria, 
        $orientador, $campus, $codigo_projeto, $siape_professor
    ]);

    // PROCESSAMENTO OPCIONAL DE NOVAS IMAGENS (Se enviadas)
    if (!empty($_FILES['imagem_projeto']['tmp_name'][0])) {
        $imagens = [];
        foreach ($_FILES['imagem_projeto']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['imagem_projeto']['error'][$key] === UPLOAD_ERR_OK) {
                $content = file_get_contents($tmp_name);
                $imagens[] = base64_encode($content);
            }
        }
        $imagens_json = json_encode($imagens);
        $pdo->prepare("UPDATE projetos SET imagem_projeto = ? WHERE codigo_projeto = ?")->execute([$imagens_json, $codigo_projeto]);
    }

    echo json_encode([
        "message" => "Projeto atualizado com sucesso!",
        "redirect" => "painel.html?tab=projetos"
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Erro na edição: " . $e->getMessage()]);
}
?>
