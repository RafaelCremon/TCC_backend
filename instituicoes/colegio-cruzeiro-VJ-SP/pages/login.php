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
            $_SESSION['tipo_usuario'] = $admin['tipo'];
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

    /* Modo escuro */
    body.dark-mode {
      background:
        url('../assets/imagens/FUNDO.png') center center repeat,
        linear-gradient(135deg, #181c2f 0%, #232a45 100%);
      color: #f3f3f3;
    }
    body.dark-mode .card {
      background: #232a45;
      color: #f3f3f3;
      box-shadow: 0 4px 24px rgba(0,0,0,0.25);
    }
    body.dark-mode .card-head h1,
    body.dark-mode .card-head p.sub {
      color: #f3f3f3;
    }
    body.dark-mode .campo label {
      color: #bfc8e2;
    }
    body.dark-mode .campo input {
      background: #181c2f;
      color: #f3f3f3;
      border: 1px solid #3a4266;
    }
    body.dark-mode .campo input::placeholder {
      color: #8a94b8;
    }
    body.dark-mode .mensagem-erro {
      color: #ff6b6b;
    }
    body.dark-mode .logo {
      /* Removido filter: invert(1); */
      background: url('../assets/imagens/FUNDO.png') center center no-repeat;
      background-size: cover;
    }
    body:not(.dark-mode) .logo {
      filter: none;
      background: #fff;
    }
    /* Botão de alternância de tema */
    .theme-toggle-btn {
      position: fixed;
      right: 32px;
      bottom: 32px;
      z-index: 100;
      background: #fff;
      border: none;
      border-radius: 50%;
      width: 48px;
      height: 48px;
      box-shadow: 0 2px 8px rgba(44,92,255,0.10);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background 0.3s;
      padding: 0;
    }
    .theme-toggle-btn img {
      width: 28px;
      height: 28px;
      pointer-events: none;
    }
    .theme-toggle-btn:hover {
      background: #e3e7fa;
    }
    body.dark-mode .theme-toggle-btn {
      background: #232a45;
    }
    body.dark-mode .theme-toggle-btn:hover {
      background: #181c2f;
    }
    body.dark-mode #senhaIcon,
    body.dark-mode #themeIcon {
      filter: invert(1) drop-shadow(0 1px 2px rgba(44,92,255,0.10));
    }
    body:not(.dark-mode) #senhaIcon,
    body:not(.dark-mode) #themeIcon {
      filter: drop-shadow(0 1px 2px rgba(44,92,255,0.10));
    }
  </style>
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
        logoImg.src = '../assets/imagens/LOGOTEMAESCURO.png'; // Logo para tema escuro
      } else {
        document.body.classList.remove('dark-mode');
        themeIcon.src = '../assets/imagens/moon.png'; // Ícone de lua no modo claro
        themeIcon.alt = 'Modo escuro';
        logoImg.src = '../assets/imagens/LOGOSEMFUNDO.png'; // Logo para tema claro
      }
      updateSenhaIcon();
    }
    // Inicialmente modo claro
    setTheme(false);
    themeBtn.addEventListener('click', function() {
      const isDark = document.body.classList.contains('dark-mode');
      setTheme(!isDark);
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