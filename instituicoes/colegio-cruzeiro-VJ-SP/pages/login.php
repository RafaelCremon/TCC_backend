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
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se o usuário existe e a senha está correta
        if ($admin && password_verify($senha, $admin['senha'])) {
            // Armazenar o ID do usuário na sessão
            $_SESSION['usuario_id'] = $admin['id'];  // Substitua 'id' pelo nome correto da coluna de ID no banco
            $_SESSION['usuario'] = $admin['usuario']; // Armazenar o nome de usuário também, se necessário
            $_SESSION['classe'] = $admin['classe']; // Nova sessão para classe
            $_SESSION['foto_perfil'] = $admin['foto_perfil']; // Foto do perfil
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
  <link rel="stylesheet" href="../css/login.css">
</head>
<body>
  <!-- Card de login -->
  <div class="card" id="loginCard" style="padding:32px 32px 24px 32px; margin-top:40px;">
    <div class="card-head">
      <img src="../assets/imagens/LOGOSEMFUNDO.png"
           alt="Logo Quantum Education"
           class="logo"
           id="logoImg"
           style="width:180px;height:auto;margin-bottom:10px;box-shadow:0 2px 8px rgba(44,92,255,0.10);border-radius:12px;padding:12px;">
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
      
    <button class="theme-toggle-btn" id="themeToggleBtn" type="button" aria-label="Alternar tema">
    <img id="themeIcon" src="../assets/imagens/moon.png" alt="Modo escuro">
  </button>

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
       // Alternância de tema claro/escuro e troca da logo
    const themeBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const logoImg = document.getElementById('logoImg');
    
    function setTheme(dark) {
      if (dark) {
        document.body.classList.add('dark-mode');
        themeIcon.src = '../assets/imagens/sun.png'; // Ícone de sol no modo escuro
        themeIcon.alt = 'Modo claro';
        logoImg.src = '../assets/imagens/FUNDOAZUL.png'; // Logo branca no modo escuro
      } else {
        document.body.classList.remove('dark-mode');
        themeIcon.src = '../assets/imagens/moon.png'; // Ícone de lua no modo claro
        themeIcon.alt = 'Modo escuro';
        logoImg.src = '../assets/imagens/LOGOSEMFUNDO.png'; // Logo para tema claro
      }
      updateSenhaIcon();
    }
    
    // Carregar tema salvo do localStorage
    function loadSavedTheme() {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'dark') {
        setTheme(true);
      } else {
        setTheme(false);
      }
    }
    
    // Salvar tema no localStorage
    function saveTheme(isDark) {
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }
    
    // Carregar tema salvo ao inicializar
    loadSavedTheme();
    
    themeBtn.addEventListener('click', function() {
      const isDark = document.body.classList.contains('dark-mode');
      const newTheme = !isDark;
      setTheme(newTheme);
      saveTheme(newTheme);
    });

    // Função para atualizar o ícone do olho conforme o tema e estado da senha
    function updateSenhaIcon() {
      var senhaInput = document.getElementById('senha');
      var senhaIcon = document.getElementById('senhaIcon');
      if (!senhaInput || !senhaIcon) return;
      if (senhaInput.type === 'password') {
        if (document.body.classList.contains('dark-mode')) {
          senhaIcon.src = '../assets/imagens/OLHOESCURO.png';
          senhaIcon.style.filter = 'invert(1)';
        } else {
          senhaIcon.src = '../assets/imagens/OLHO.jpg';
          senhaIcon.style.filter = '';
        }
        senhaIcon.alt = 'Senha oculta';
      } else {
        if (document.body.classList.contains('dark-mode')) {
          senhaIcon.src = '../assets/imagens/FECHADOESCURO.png';
          senhaIcon.style.filter = 'invert(1)';
        } else {
          senhaIcon.src = '../assets/imagens/FECHADO.jpg';
          senhaIcon.style.filter = '';
        }
        senhaIcon.alt = 'Senha visível';
      }
    }
  </script>
  <link rel="stylesheet" href="inicial.php">
</body>
</html>