<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sigipex;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Adiciona a coluna nivel_acesso (0 = Professor, 1 = Admin)
    $pdo->exec("ALTER TABLE professor ADD COLUMN IF NOT EXISTS nivel_acesso INT DEFAULT 0");
    $pdo->exec("ALTER TABLE professores ADD COLUMN IF NOT EXISTS nivel_acesso INT DEFAULT 0"); // Garante em ambas

    // 2. Promove o ADM@gmail.com (se existir)
    $stmt = $pdo->prepare("UPDATE professor SET nivel_acesso = 1 WHERE email = ?");
    $stmt->execute(['ADM@gmail.com']);
    
    $stmt2 = $pdo->prepare("UPDATE professores SET nivel_acesso = 1 WHERE email_professor = ?");
    $stmt2->execute(['ADM@gmail.com']);

    echo "✅ Sistema de Níveis de Acesso ativado!";
} catch (Exception $e) {
    echo "❌ Erro ao ativar níveis: " . $e->getMessage();
}
?>
