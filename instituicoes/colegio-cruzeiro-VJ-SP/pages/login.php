<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
require_once "../../../includes/db.php";

// Inicializar a variável de erro
if (isset($_SESSION['erro'])) {
    $erro = $_SESSION['erro'];
    unset($_SESSION['erro']); // Limpar a mensagem após a exibição
} else {
    $erro = "";
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Verificar se os campos estão preenchidos
    if (!empty($usuario) && !empty($senha)) {
        // Preparar a consulta SQL
        $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = :usuario");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se o usuário existe e a senha está correta
        if ($admin && password_verify($senha, $admin['senha'])) {
            // Armazenar o ID do usuário na sessão
            $_SESSION['usuario_id'] = $admin['id'];  // Substitua 'id' pelo nome correto da coluna de ID no banco
            $_SESSION['usuario'] = $admin['usuario']; // Armazenar o nome de usuário também, se necessário
            header("Location: inicial.php"); // Redireciona para a página inicial
            exit();
        } else {
            $_SESSION['erro'] = "Usuário ou senha incorretos."; // Armazenar mensagem de erro na sessão
            header("Location: login.php"); // Redireciona para a mesma página
            exit();
        }
    } else {
        $_SESSION['erro'] = "Preencha todos os campos."; // Armazenar mensagem de erro na sessão
        header("Location: login.php"); // Redireciona para a mesma página
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tela de Login</title>
  <link rel="stylesheet" href="../css/login.css" />
  <style>
    /* Fade-in suave para o card de login */
    .card.login-animate-in {
      animation: loginFadeIn 0.7s cubic-bezier(.4,1.4,.6,1);
    }
    @keyframes loginFadeIn {
      0% {
        opacity: 0;
      }
      100% {
        opacity: 1;
      }
    }
  /* Removidas animações Apple-style, mantida apenas a animação de entrada do card */
  </style>
</head>
<body>
  <!-- Card de login -->
<div class="card" id="loginCard">
    <div class="card-head">
  <img src="../assets/imagens/LOGO.png" alt="Logo Quantum" class="logo" style="width:48px;height:48px;border-radius:50%;margin-bottom:10px;box-shadow:0 2px 8px rgba(44,92,255,0.10);">
      <h1>Login</h1>
      <p class="sub">Acesse sua conta</p>
    </div>

<form method="POST">
<div class="campo">
    <label for="usuario">Usuário</label>
    <input type="text" id="usuario" name="usuario" placeholder="Digite seu usuário" value="<?= isset($usuario) ? htmlspecialchars($usuario) : '' ?>" required />
</div>

<div class="campo" style="position: relative; margin-bottom: 15px;">
    <label for="senha" style="display:block; margin-bottom:5px;">Senha</label>
    
    <input type="password" id="senha" name="senha" 
           placeholder="Digite sua senha" required
           style="width:100%; height:40px; padding:8px 35px 8px 8px; box-sizing:border-box;" />
    
    <span class="toggle-senha" id="toggleSenhaBtn" 
      style="position:absolute; right:10px; top: 50px;; bottom:0; display:flex; align-items:center; cursor:pointer;">
    <img id="senhaIcon" src="../assets/imagens/OLHO.jpg" 
         alt="Mostrar/Ocultar Senha" 
         style="width:20px; height:20px; object-fit:contain;">
</span>

    </span>
</div>


            <!-- Mensagem de erro se houver -->
            <?php if (!empty($erro)): ?>
                <div class="mensagem-erro" id="erroMensagem"><?= $erro ?></div>
            <?php endif; ?>

            <button type="submit">Entrar</button>
        </form>
    </div>

    <script src="../js/login.js"></script>
    <script>
    // Animação de entrada do card de login
    window.addEventListener('DOMContentLoaded', function() {
      var card = document.getElementById('loginCard');
      if(card) {
        card.classList.add('login-animate-in');
      }
      // Alternância do ícone de senha
      var senhaInput = document.getElementById('senha');
      var senhaIcon = document.getElementById('senhaIcon');
      var toggleBtn = document.getElementById('toggleSenhaBtn');
      if (senhaInput && senhaIcon && toggleBtn) {
        toggleBtn.addEventListener('click', function() {
          if (senhaInput.type === 'password') {
            senhaInput.type = 'text';
            senhaIcon.src = '../assets/imagens/FECHADO.jpg';
            senhaIcon.alt = 'Senha visível';
          } else {
            senhaInput.type = 'password';
            senhaIcon.src = '../assets/imagens/OLHO.jpg';
            senhaIcon.alt = 'Senha oculta';
          }
        });
      }
    });
  </script>
  <script>
    // Animação de entrada do card de login
    window.addEventListener('DOMContentLoaded', function() {
      var card = document.getElementById('loginCard');
      if(card) {
        card.classList.add('login-animate-in');
        // Não remove a classe, assim só anima uma vez
      }
    });
  </script>
  <link rel="stylesheet" href="inicial.php">
</body>
</html>



