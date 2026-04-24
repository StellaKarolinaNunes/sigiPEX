<?php
// Inicia o output buffering para evitar problemas com headers ou espaços em branco
ob_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Recebe e limpa os dados enviados
  $nome_professor     = isset($_POST['nome_professor']) ? trim($_POST['nome_professor']) : null;
  $senha              = isset($_POST['senha']) ? $_POST['senha'] : null;
  $telefone           = isset($_POST['telefone']) ? trim($_POST['telefone']) : null;
  $email              = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
  $siape              = filter_input(INPUT_POST, 'siape', FILTER_VALIDATE_INT);
  $coordenacao_curso  = isset($_POST['coordenacao_curso']) ? trim($_POST['coordenacao_curso']) : null;

  // Verifica se todos os campos estão presentes e válidos
  if (!$nome_professor || !$senha || !$telefone || !$email || $siape === false || !$coordenacao_curso) {
    $erro = "Preencha todos os campos corretamente.";
    if (!$email) $erro = "E-mail inválido.";
    if ($siape === false) $erro = "SIAPE inválido (deve conter apenas números).";
    
    echo "<!DOCTYPE html>
              <html lang='pt-BR'>
              <head>
                <meta charset='UTF-8'>
                <title>Erro no Cadastro</title>
              </head>
              <body>
                <script>
                  alert('$erro');
                  window.history.back();
                </script>
              </body>
              </html>";
    exit;
  }

  // Gera o hash da senha para maior segurança
  $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);


  // Configurações de conexão com o banco de dados
  $host    = 'localhost';
  $db      = 'sigipex';    // Altere para o nome do seu banco de dados
  $user    = 'root';      // Altere para o seu usuário do banco
  $pass    = '';        // Altere para a sua senha do banco
  $charset = 'utf8mb4';
  $dsn     = "mysql:host=$host;dbname=$db;charset=$charset";

  try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara a instrução SQL para inserir os dados na tabela professor
    $stmt = $pdo->prepare("INSERT INTO professor (nome_professor, senha, telefone, email, siape, coordenacao_curso)
                               VALUES (:nome_professor, :senha, :telefone, :email, :siape, :coordenacao_curso)");
    $stmt->bindParam(':nome_professor', $nome_professor);
    $stmt->bindParam(':senha', $senha_hashed);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':siape', $siape, PDO::PARAM_INT);
    $stmt->bindParam(':coordenacao_curso', $coordenacao_curso);

    if ($stmt->execute()) {
      // Exibe o alerta e redireciona para a página de login
      echo "<!DOCTYPE html>
                  <html lang='pt-BR'>
                  <head>
                    <meta charset='UTF-8'>
                    <title>Cadastro Realizado</title>
                  </head>
                  <body>
                    <script>
                      alert('Cadastro realizado com sucesso!');
                      window.location.href = 'login_cadastro.html';
                    </script>
                  </body>
                  </html>";
    } else {
      echo "<!DOCTYPE html>
                  <html lang='pt-BR'>
                  <head>
                    <meta charset='UTF-8'>
                    <title>Erro no Cadastro</title>
                  </head>
                  <body>
                    <script>
                      alert('Erro ao cadastrar o professor.');
                      window.history.back();
                    </script>
                  </body>
                  </html>";
    }
  } catch (PDOException $e) {
    if ($e->getCode() == 23000) {
      echo "<!DOCTYPE html>
                  <html lang='pt-BR'>
                  <head>
                    <meta charset='UTF-8'>
                    <title>Erro de Duplicidade</title>
                  </head>
                  <body>
                    <script>
                      alert('Erro: Já existe um professor cadastrado com este e-mail ou siape.');
                      window.history.back();
                    </script>
                  </body>
                  </html>";
    } else {
      echo "<!DOCTYPE html>
                  <html lang='pt-BR'>
                  <head>
                    <meta charset='UTF-8'>
                    <title>Erro no Cadastro</title>
                  </head>
                  <body>
                    <script>
                      alert('Erro: " . $e->getMessage() . "');
                      window.history.back();
                    </script>
                  </body>
                  </html>";
    }
  }
} else {
  echo "<!DOCTYPE html>
          <html lang='pt-BR'>
          <head>
            <meta charset='UTF-8'>
            <title>Método Inválido</title>
          </head>
          <body>
            <script>
              alert('Método de requisição inválido.');
              window.history.back();
            </script>
          </body>
          </html>";
}
ob_end_flush();
