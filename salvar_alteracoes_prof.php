<?php
session_start();
if (!isset($_SESSION['siape'])) {
    echo json_encode(["error" => "Usuário não autenticado."]);
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=sigipex;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Captura os dados enviados
$nome     = $_POST['nome'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$email    = $_POST['email'] ?? '';
$senha    = $_POST['senha'] ?? '';

// Verifica se o novo e-mail já existe para outro usuário
if (!empty($email)) {
    $stmt = $pdo->prepare("SELECT email FROM professor WHERE email = ? AND siape != ?");
    $stmt->execute([$email, $_SESSION['siape']]);
    if ($stmt->fetch()) {
        echo json_encode(["error" => "E-mail já cadastrado."]);
        exit();
    }
}

// Atualiza os dados (incluindo senha se informada)
if (!empty($senha)) {
    $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE professor SET nome_professor = ?, telefone = ?, email = ?, senha = ? WHERE siape = ?");
    $stmt->execute([$nome, $telefone, $email, $hashedSenha, $_SESSION['siape']]);
} else {
    $stmt = $pdo->prepare("UPDATE professor SET nome_professor = ?, telefone = ?, email = ? WHERE siape = ?");
    $stmt->execute([$nome, $telefone, $email, $_SESSION['siape']]);
}

// Se necessário, atualize a sessão com o novo e-mail (a autenticação permanece pelo siape)
$_SESSION['email'] = $email; // Opcional, se você usar o e-mail em outras partes

echo json_encode(["message" => "Dados atualizados com sucesso!"]);
?>
