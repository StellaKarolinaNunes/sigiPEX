<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//inclua os arquivos manualmente
require 'sigiPEX/PHPMailer/src/PHPMailer.php';
require 'sigiPEX/PHPMailer/src/SMTP.php';
require 'sigiPEX/PHPMailer/src/Exception.php';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        echo "<script>
                alert('Por favor, insira um e-mail válido.');
                window.location.href = 'recuperar_senha.html';
              </script>";
        exit;
    }

    // Conexão com o banco de dados
    $host    = 'localhost';
    $db      = 'sigipex';    // Substitua pelo nome do seu banco
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8mb4';
    $dsn     = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica se o e-mail existe na tabela professor
        $stmt = $pdo->prepare("SELECT siape FROM professor WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Criando um token seguro
            $token = bin2hex(random_bytes(16));
            $siape = $result['siape'];

            // Insere o token no banco
            $stmtInsert = $pdo->prepare("INSERT INTO redefinicao_senha (siape, token) VALUES (:siape, :token)");
            $stmtInsert->bindParam(':siape', $siape, PDO::PARAM_INT);
            $stmtInsert->bindParam(':token', $token);
            $stmtInsert->execute();

            // Criando o link de recuperação
            $reset_link = "http://seusite.com/recuperar_senha_form.php?token=$token";

            // Envio do e-mail usando PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configuração do servidor SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Exemplo: smtp.gmail.com
                $mail->SMTPAuth   = true;
                $mail->Username   = 'viniciusjhonefer@gmail.com';
                $mail->Password   = 'xme2@04NN';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Ou PHPMailer::ENCRYPTION_SMTPS
                $mail->Port       = 587; // Padrão TLS

                // Configuração do e-mail
                $mail->setFrom('viniciusjhonefer@gmail.com', 'Jhonefer Vinicius');
                $mail->addAddress($email);
                $mail->Subject = 'Recuperação de Senha';
                $mail->Body    = "Olá, \n\nClique no link abaixo para redefinir sua senha:\n\n$reset_link\n\nSe você não solicitou a redefinição, ignore este e-mail.";

                // Enviar e-mail
                $mail->send();

                echo "<script>
                        alert('Um e-mail de recuperação foi enviado para seu endereço.');
                        window.location.href = 'login.html';
                      </script>";
            } catch (Exception $e) {
                echo "<script>
                        alert('Erro ao enviar e-mail: " . $mail->ErrorInfo . "');
                        window.location.href = 'recuperar_senha.html';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('E-mail não encontrado.');
                    window.location.href = 'recuperar_senha.html';
                  </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
                alert('Erro: " . $e->getMessage() . "');
                window.location.href = 'recuperar_senha.html';
              </script>";
    }
} else {
    echo "<script>
            alert('Método inválido.');
            window.location.href = 'recuperar_senha.html';
          </script>";
}
?>
