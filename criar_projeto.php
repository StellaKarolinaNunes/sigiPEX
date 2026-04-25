<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['siape'])) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

try {
    require_once 'db_config.php';

    // Dados Básicos
    $nome_projeto      = $_POST['nome_projeto'] ?? '';
    
    // VERIFICAÇÃO DE DUPLICIDADE
    $check = $pdo->prepare("SELECT COUNT(*) FROM projetos WHERE nome_projeto = ? AND siape_professor = ?");
    $check->execute([$nome_projeto, $_SESSION['siape']]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(["error" => "Você já possui um projeto cadastrado com este nome."]);
        exit();
    }

    $resumo_projeto    = $_POST['resumo_projeto'] ?? '';
    $codigo_turma      = $_POST['codigo_turma'] ?? 'TI'; // Valor padrão
    $github_link       = $_POST['github_link'] ?? null;
    $categoria         = $_POST['categoria'] ?? 'Extensão';
    $orientador        = $_POST['orientador'] ?? '';
    $campus            = $_POST['campus'] ?? '';
    $situacao          = $_POST['situacao'] ?? 'Em andamento';
    $siape_professor   = $_SESSION['siape'];

    // Processamento de Arrays (Linguagens e Coorientadores)
    $linguagens = isset($_POST['linguagens']) ? implode(", ", $_POST['linguagens']) : "";
    $coorientadores = isset($_POST['coorientadores']) ? implode(", ", $_POST['coorientadores']) : "";

    // Dados de Alunos (Opcionais na interface atual)
    $nome_aluno = isset($_POST['alunos_nomes']) ? implode(", ", $_POST['alunos_nomes']) : "";
    $email_aluno = isset($_POST['alunos_emails']) ? implode(", ", $_POST['alunos_emails']) : "";

    // PROCESSAMENTO DE IMAGENS
    $imagens = [];
    if (isset($_FILES['imagem_projeto'])) {
        foreach ($_FILES['imagem_projeto']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['imagem_projeto']['error'][$key] === UPLOAD_ERR_OK) {
                $content = file_get_contents($tmp_name);
                $imagens[] = base64_encode($content);
            }
        }
    }
    $imagens_json = json_encode($imagens);

    // PROCESSAMENTO DE ARQUIVOS (PDF)
    $arquivos = [];
    if (isset($_FILES['arquivo_projeto'])) {
        foreach ($_FILES['arquivo_projeto']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['arquivo_projeto']['error'][$key] === UPLOAD_ERR_OK) {
                $content = file_get_contents($tmp_name);
                $arquivos[] = [
                    "name" => $_FILES['arquivo_projeto']['name'][$key],
                    "data" => base64_encode($content)
                ];
            }
        }
    }
    $arquivos_json = json_encode($arquivos);

    // INSERÇÃO NO BANCO DE DADOS
    $sql = "INSERT INTO projetos (
                nome_projeto, coorientador_projeto, nome_aluno, email_aluno, 
                resumo_projeto, codigo_turma, imagem_projeto, arquivo_projeto, 
                siape_professor, github_link, linguagem_projeto, categoria_projeto, 
                orientador_projeto, campus_projeto, situacao_projeto
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nome_projeto, $coorientadores, $nome_aluno, $email_aluno, 
        $resumo_projeto, $codigo_turma, $imagens_json, $arquivos_json, 
        $siape_professor, $github_link, $linguagens, $categoria, 
        $orientador, $campus, $situacao
    ]);

    echo json_encode([
        "message" => "Projeto e todos os campos salvos com sucesso!",
        "redirect" => "painel.html?tab=projetos"
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Erro no banco: " . $e->getMessage()]);
}
?>
