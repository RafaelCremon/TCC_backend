<?php
// Incluir o arquivo de conexão com o banco de dados
require_once "../../../includes/db.php";  // Ajuste o caminho conforme necessário

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Preparar a consulta SQL
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar se o usuário existe e a senha está correta
    if ($admin && password_verify($senha, $admin['senha'])) {
        $_SESSION['usuario_id'] = $admin['id'];
        $_SESSION['usuario'] = $admin['usuario'];
        $_SESSION['classe'] = $admin['classe'];
        $_SESSION['foto_perfil'] = $admin['foto_perfil'];
        header("Location: inicial.php");
        exit();
    } else {
        $_SESSION['erro'] = "Usuário ou senha incorretos."; // Armazenar mensagem de erro na sessão
        header("Location: login.php"); // Redirecionar para a página de login
        exit();
    }
}
?>
