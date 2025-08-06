<?php
// Iniciar a sessão para verificar se o usuário está logado
session_start();

// Se não estiver logado, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Se o formulário de logout for enviado, destrói a sessão e redireciona para o login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy(); // Destrói todas as variáveis da sessão
    header('Location: login.php'); // Redireciona para a página de login
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página Inicial</title>
     <link rel="stylesheet" href="../css/inicial.css">
</head>
<body>

    <div>
        <a href="tour.html" class="map-link">
            <img src="../assets/imagens/mapa.png" alt="Mapa do Tour Virtual" />
            <span>Clique para abrir o Mini mapa</span>
        </a>

        <!-- Botão de Logout -->
        <form method="POST">
            <button type="submit" name="logout" class="logout-button">Logout</button>
        </form>
    </div>
</body>
</html>
