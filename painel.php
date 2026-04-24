<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['siape'])) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sigipex;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $siape = $_SESSION['siape'];
    $is_admin = (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 1);

    // 1. BUSCA DADOS DO USUÁRIO LOGADO
    $stmt = $pdo->prepare("SELECT nome_professor, siape, telefone, email, coordenacao_curso as campus FROM professor WHERE siape = ? LIMIT 1");
    $stmt->execute([$siape]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prof) {
        echo json_encode(["error" => "Perfil não encontrado"]);
        exit();
    }

    // 2. BUSCA PROJETOS (Logica de Admin ou Professor)
    if ($is_admin) {
        // Master vê TUDO
        $stmt = $pdo->prepare("SELECT * FROM projetos ORDER BY codigo_projeto DESC");
        $stmt->execute();
    } else {
        // Professor vê apenas o seu
        $stmt = $pdo->prepare("SELECT * FROM projetos WHERE siape_professor = ? ORDER BY codigo_projeto DESC");
        $stmt->execute([$siape]);
    }
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. BUSCA TODOS OS USUÁRIOS (Somente se for Admin)
    $usuarios = [];
    if ($is_admin) {
        $stmt = $pdo->prepare("SELECT nome_professor, siape, coordenacao_curso as campus, email FROM professor");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "nome_professor" => $prof['nome_professor'],
        "siape"          => $prof['siape'],
        "telefone"       => $prof['telefone'] ?? '',
        "email"          => $prof['email'] ?? '',
        "campus"         => $prof['campus'] ?? 'IFPA',
        "is_admin"       => $is_admin,
        "projetos"       => $projetos,
        "usuarios"       => $usuarios
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => "Erro interno: " . $e->getMessage()]);
}
?>
