<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'];

    if (!$email || !$senha) {
        echo "<script>
                alert('Preencha os campos corretamente.');
                window.location.href = 'login_cadastro.html';
              </script>";
        exit;
    }

    // Configurações de conexão com o banco de dados
    $host = 'localhost';
    $db = 'sigipex';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Busca o professor com o e-mail informado
        $stmt = $pdo->prepare("SELECT * FROM professor WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $professor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($professor && password_verify($senha, $professor['senha'])) {
            // Login realizado com sucesso
            $_SESSION['siape'] = $professor['siape'];
            $_SESSION['nome_professor'] = $professor['nome_professor'];
            $_SESSION['nivel_acesso'] = ($email === 'ADM@gmail.com') ? 1 : 0;
            header("Location: index.php");
            exit();
        } else {
            echo "<script>
                    alert('E-mail ou senha incorretos!');
                    window.location.href = 'login_cadastro.html';
                  </script>";
        }

    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
} else {
    echo "<script>
            alert('Método inválido.');
            window.location.href = 'login_cadastro.html';
          </script>";
}
?>