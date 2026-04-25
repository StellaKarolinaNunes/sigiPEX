<?php
/**
 * db_config.php
 * Configuração centralizada de conexão com o banco de dados.
 */

$host = 'localhost';
$dbname = 'sigipex';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Em produção, nunca mostre o erro detalhado diretamente.
    // die("Erro crítico de conexão.");
    throw new Exception("Erro de conexão: " . $e->getMessage());
}
?>
