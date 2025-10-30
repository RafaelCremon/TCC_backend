<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
require_once "includes/db.php";

// Variáveis para mensagens
$mensagem = "";
$tipo_mensagem = ""; // success ou error

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Receber dados do formulário
        $instituicao_id = $_POST['instituicao_id'] ?? null;
        $classe = $_POST['classe'] ?? null;
        $nome = $_POST['nome'] ?? null;
        $rgm = $_POST['rgm'] ?? null;
        $usuario = $_POST['usuario'] ?? null;
        $serie = $_POST['serie'] ?? null;
        $email = $_POST['email'] ?? null;
        $telefone = $_POST['telefone'] ?? null;
        $senha = $_POST['senha'] ?? null;
        $confirmar_senha = $_POST['confirmar_senha'] ?? null;
        $foto_perfil = $_POST['foto_perfil'] ?? null;

        // Validações
        if (empty($instituicao_id) || empty($nome) || empty($rgm) || empty($usuario) || 
            empty($serie) || empty($email) || empty($senha)) {
            throw new Exception("Por favor, preencha todos os campos obrigatórios.");
        }

        if ($senha !== $confirmar_senha) {
            throw new Exception("As senhas não coincidem.");
        }

        if (strlen($senha) < 6) {
            throw new Exception("A senha deve ter no mínimo 6 caracteres.");
        }

        // Verificar se o usuário já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario OR email = :email");
        $stmt->execute(['usuario' => $usuario, 'email' => $email]);
        if ($stmt->fetch()) {
            throw new Exception("Usuário ou e-mail já cadastrado.");
        }

        // Hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Inserir usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (instituicao_id, classe, nome, rgm, usuario, serie, email, telefone, senha) 
            VALUES (:instituicao_id, :classe, :nome, :rgm, :usuario, :serie, :email, :telefone, :senha)
        ");
        
        $stmt->execute([
            'instituicao_id' => $instituicao_id,
            'classe' => $classe,
            'nome' => $nome,
            'rgm' => $rgm,
            'usuario' => $usuario,
            'serie' => $serie,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => $senha_hash
        ]);

        $usuario_id = $pdo->lastInsertId();

        // Criar registro na tabela plataforma
        $stmt = $pdo->prepare("
            INSERT INTO plataforma (usuario_id, foto_perfil, atalhos, conquistas) 
            VALUES (:usuario_id, :foto_perfil, NULL, NULL)
        ");
        
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'foto_perfil' => $foto_perfil
        ]);

        $mensagem = "Usuário criado com sucesso! ID: " . $usuario_id;
        $tipo_mensagem = "success";

        // Limpar campos após sucesso
        $_POST = [];

    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
        $tipo_mensagem = "error";
    }
}

// Buscar instituições para o select
try {
    $stmt = $pdo->query("SELECT id, nome FROM instituicoes ORDER BY nome");
    $instituicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $instituicoes = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário - Quantum Edu</title>
    <style>
        :root {
            --primary: #5b8cff;
            --primary-dark: #0057ff;
            --secondary: #7049ff;
            --success: #10b981;
            --error: #ef4444;
            --bg: #f7f9fc;
            --card: #ffffff;
            --text: #1b2b66;
            --text-muted: #5b6b8b;
            --border: #e0e6f6;
            --shadow: 0 10px 40px rgba(44, 92, 255, 0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('data:image/svg+xml;utf8,<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="2" cy="2" r="2" fill="%23ffffff" opacity="0.1"/><circle cx="16" cy="16" r="2" fill="%23ffffff" opacity="0.1"/><circle cx="30" cy="30" r="2" fill="%23ffffff" opacity="0.1"/></svg>');
            background-repeat: repeat;
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            background: var(--card);
            border-radius: 24px;
            box-shadow: var(--shadow);
            max-width: 800px;
            width: 100%;
            padding: 40px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(91, 140, 255, 0.3);
        }

        .logo::before {
            content: 'Q';
            font-size: 28px;
            font-weight: 900;
            color: white;
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border: 2px solid var(--success);
        }

        .alert.error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: 2px solid var(--error);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        label .required {
            color: var(--error);
            font-size: 1.1rem;
        }

        input, select, textarea {
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--bg);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(91, 140, 255, 0.1);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%235b8cff" viewBox="0 0 16 16"><path d="M8 11L3 6h10z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            user-select: none;
            color: var(--text-muted);
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 28px;
        }

        button {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 6px 20px rgba(91, 140, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(91, 140, 255, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: var(--bg);
            color: var(--text);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: white;
            border-color: var(--primary);
            color: var(--primary);
        }

        .help-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                padding: 28px 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            h1 {
                font-size: 1.6rem;
            }

            .btn-group {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .container {
                padding: 24px 20px;
                border-radius: 20px;
            }

            h1 {
                font-size: 1.4rem;
            }

            .logo {
                width: 50px;
                height: 50px;
            }

            .logo::before {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"></div>
            <h1>Criar Novo Usuário</h1>
            <p class="subtitle">Sistema de Cadastro - Quantum Education</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert <?= $tipo_mensagem ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-grid">
                <!-- Instituição -->
                <div class="form-group full-width">
                    <label>
                        Instituição <span class="required">*</span>
                    </label>
                    <select name="instituicao_id" required>
                        <option value="">Selecione uma instituição</option>
                        <?php foreach ($instituicoes as $inst): ?>
                            <option value="<?= $inst['id'] ?>" <?= (isset($_POST['instituicao_id']) && $_POST['instituicao_id'] == $inst['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inst['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Nome Completo -->
                <div class="form-group full-width">
                    <label>
                        Nome Completo <span class="required">*</span>
                    </label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" 
                           placeholder="João da Silva" required>
                </div>

                <!-- RGM -->
                <div class="form-group">
                    <label>
                        RGM <span class="required">*</span>
                    </label>
                    <input type="number" name="rgm" value="<?= htmlspecialchars($_POST['rgm'] ?? '') ?>" 
                           placeholder="12345678" required>
                    <small class="help-text">Registro Geral de Matrícula</small>
                </div>

                <!-- Série -->
                <div class="form-group">
                    <label>
                        Série <span class="required">*</span>
                    </label>
                    <input type="text" name="serie" value="<?= htmlspecialchars($_POST['serie'] ?? '') ?>" 
                           placeholder="3A" maxlength="4" required>
                    <small class="help-text">Ex: 1A, 2B, 3C</small>
                </div>

                <!-- Classe/Tipo -->
                <div class="form-group">
                    <label>Classe/Tipo</label>
                    <select name="classe">
                        <option value="">Selecione (opcional)</option>
                        <option value="Aluno" <?= (isset($_POST['classe']) && $_POST['classe'] == 'Aluno') ? 'selected' : '' ?>>Aluno</option>
                        <option value="Professor" <?= (isset($_POST['classe']) && $_POST['classe'] == 'Professor') ? 'selected' : '' ?>>Professor</option>
                        <option value="Coordenador" <?= (isset($_POST['classe']) && $_POST['classe'] == 'Coordenador') ? 'selected' : '' ?>>Coordenador</option>
                        <option value="Administrador" <?= (isset($_POST['classe']) && $_POST['classe'] == 'Administrador') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>

                <!-- Nome de Usuário -->
                <div class="form-group">
                    <label>
                        Nome de Usuário <span class="required">*</span>
                    </label>
                    <input type="text" name="usuario" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" 
                           placeholder="joaosilva" required>
                    <small class="help-text">Usado para login</small>
                </div>

                <!-- E-mail -->
                <div class="form-group full-width">
                    <label>
                        E-mail <span class="required">*</span>
                    </label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           placeholder="joao@exemplo.com" required>
                </div>

                <!-- Telefone -->
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="tel" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" 
                           placeholder="(11) 98765-4321">
                </div>

                <!-- Foto de Perfil (URL) -->
                <div class="form-group">
                    <label>Foto de Perfil (URL)</label>
                    <input type="url" name="foto_perfil" value="<?= htmlspecialchars($_POST['foto_perfil'] ?? '') ?>" 
                           placeholder="https://exemplo.com/foto.jpg">
                </div>

                <!-- Senha -->
                <div class="form-group">
                    <label>
                        Senha <span class="required">*</span>
                    </label>
                    <div class="password-wrapper">
                        <input type="password" name="senha" id="senha" placeholder="••••••••" required minlength="6">
                        <span class="toggle-password" onclick="togglePassword('senha')">👁️</span>
                    </div>
                    <small class="help-text">Mínimo 6 caracteres</small>
                </div>

                <!-- Confirmar Senha -->
                <div class="form-group">
                    <label>
                        Confirmar Senha <span class="required">*</span>
                    </label>
                    <div class="password-wrapper">
                        <input type="password" name="confirmar_senha" id="confirmar_senha" placeholder="••••••••" required minlength="6">
                        <span class="toggle-password" onclick="togglePassword('confirmar_senha')">👁️</span>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <button type="reset" class="btn-secondary">Limpar Formulário</button>
                <button type="submit" class="btn-primary">✓ Criar Usuário</button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const toggle = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                toggle.textContent = '🙈';
            } else {
                input.type = 'password';
                toggle.textContent = '👁️';
            }
        }

        // Validação de senhas em tempo real
        const senha = document.getElementById('senha');
        const confirmarSenha = document.getElementById('confirmar_senha');

        confirmarSenha.addEventListener('input', function() {
            if (senha.value !== confirmarSenha.value) {
                confirmarSenha.setCustomValidity('As senhas não coincidem');
            } else {
                confirmarSenha.setCustomValidity('');
            }
        });

        // Auto-hide mensagem de sucesso
        <?php if ($tipo_mensagem === 'success'): ?>
        setTimeout(() => {
            const alert = document.querySelector('.alert.success');
            if (alert) {
                alert.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
