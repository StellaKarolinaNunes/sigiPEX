<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['siape'])) {
    echo json_encode(['logado' => false]);
    exit;
}

try {
    require_once 'db_config.php';
    $stmt = $pdo->prepare("SELECT nome_professor, siape, coordenacao_curso as campus, telefone, email FROM professor WHERE siape = ?");
    $stmt->execute([$_SESSION['siape']]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prof) {
        echo json_encode([
            'logado' => true,
            'nome' => $prof['nome_professor'],
            'siape' => $prof['siape'],
            'campus' => $prof['campus'],
            'telefone' => $prof['telefone'],
            'email' => $prof['email']
        ]);
    } else {
        echo json_encode(['logado' => false, 'error' => 'Professor não encontrado no banco.']);
    }
} catch (Exception $e) {
    echo json_encode(['logado' => false, 'error' => $e->getMessage()]);
}
?>
