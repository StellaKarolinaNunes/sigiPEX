<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['siape'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada.']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sigipex;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nome               = $_POST['nome'] ?? '';
    $campus             = $_POST['campus'] ?? '';
    $telefone           = $_POST['telefone'] ?? '';
    $email              = $_POST['email'] ?? '';
    $nova_senha         = $_POST['nova_senha'] ?? '';
    $confirma_senha     = $_POST['confirma_nova_senha'] ?? '';
    $siape              = $_SESSION['siape'];

    if (empty($nome)) {
        echo json_encode(['success' => false, 'error' => 'O nome não pode estar vazio.']);
        exit;
    }

    // Inicia a query de atualização
    $sql = "UPDATE professor SET nome_professor = ?, coordenacao_curso = ?, telefone = ?, email = ?";
    $params = [$nome, $campus, $telefone, $email];

    // Lógica de Senha
    if (!empty($nova_senha)) {
        if ($nova_senha !== $confirma_senha) {
            echo json_encode(['success' => false, 'error' => 'As senhas não coincidem.']);
            exit;
        }
        if (strlen($nova_senha) < 8) {
            echo json_encode(['success' => false, 'error' => 'A senha deve ter pelo menos 8 caracteres.']);
            exit;
        }
        
        $hash_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql .= ", senha = ?";
        $params[] = $hash_senha;
    }

    $sql .= " WHERE siape = ?";
    $params[] = $siape;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $_SESSION['nome_professor'] = $nome;

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
