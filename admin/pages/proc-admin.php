<?php
require_once '../../includes/db.php'; // Incluindo a conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha']; // A senha digitada pelo usuário

    // Buscando o usuário no banco de dados
    $query = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $pdo->prepare($query);  // Usando o objeto $pdo para preparar a consulta
    $stmt->bindParam(1, $usuario, PDO::PARAM_STR); // Vincula o parâmetro de forma segura
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC); // Obtendo o resultado como um array associativo

    if ($result) {
        // Verificando se a senha fornecida corresponde à senha criptografada no banco de dados
        if (password_verify($senha, $result['senha'])) {
            // Senha correta, redirecionar para a página de administração ou dashboard
            echo "Login bem-sucedido!";
            // Redirecionamento, por exemplo:
            // header('Location: dashboard.php');
        } else {
            // Senha incorreta
            echo "Senha incorreta!";
        }
    } else {
        // Usuário não encontrado
        echo "Usuário não encontrado!";
    }
}
?>
