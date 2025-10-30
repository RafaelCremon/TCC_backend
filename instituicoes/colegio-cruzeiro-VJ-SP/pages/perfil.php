<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../../../includes/db.php';
$usuario_id = $_SESSION['usuario_id'];
$erro = '';
$sucesso = '';
// Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $serie = trim($_POST['serie'] ?? '');
    // Foto de perfil
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $permitidas)) {
            $destino = '../../../includes/perfis/perfil_' . $usuario_id . '_' . time() . '.' . $ext;
            if (!is_dir('../../../includes/perfis')) mkdir('../../../includes/perfis', 0777, true);
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino)) {
                $foto_perfil = $destino;
                // Atualiza na tabela plataforma
                $stmt = $pdo->prepare('UPDATE plataforma SET foto_perfil = :foto WHERE usuario_id = :id');
                $stmt->bindParam(':foto', $foto_perfil);
                $stmt->bindParam(':id', $usuario_id);
                $stmt->execute();
            } else {
                $erro = 'Erro ao salvar a foto.';
            }
        } else {
            $erro = 'Formato de imagem não permitido.';
        }
    }
    if ($email && !$erro) {
        $stmt = $pdo->prepare('UPDATE usuarios SET email = :email, telefone = :telefone, serie = :serie WHERE id = :id');
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':serie', $serie);
        $stmt->bindParam(':id', $usuario_id);
        if ($stmt->execute()) {
            $sucesso = 'Perfil atualizado com sucesso!';
        } else {
            $erro = 'Erro ao atualizar perfil.';
        }
    } elseif (!$erro) {
        $erro = 'E-mail é obrigatório.';
    }
}
// Buscar dados atuais
$stmt = $pdo->prepare('SELECT nome, email, telefone, serie, rgm FROM usuarios WHERE id = :id');
$stmt->bindParam(':id', $usuario_id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar foto de perfil
$stmt2 = $pdo->prepare('SELECT foto_perfil FROM plataforma WHERE usuario_id = :id');
$stmt2->bindParam(':id', $usuario_id);
$stmt2->execute();
$foto_perfil_db = $stmt2->fetchColumn();

// Verificar se existe e é válida
if ($foto_perfil_db && file_exists($foto_perfil_db)) {
    $foto_perfil = $foto_perfil_db;
} else {
    $foto_perfil = '../assets/imagens/spongebob.png';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script>
    // Aplica o tema salvo do localStorage ANTES do DOMContentLoaded para evitar flash de tema errado
    (function() {
        var savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-mode');
        } else {
            document.documentElement.classList.remove('dark-mode');
        }
    })();
    </script>

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        background: linear-gradient(135deg, #f5f7ff 0%, #e8ecff 100%);
        font-family: 'Segoe UI', 'SF Pro Display', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        transition: background 0.3s ease, color 0.3s ease;
        min-height: 100vh;
    }
    .container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 60px 20px 40px 20px;
    }
    .perfil-main {
        background: #ffffff;
        max-width: 920px;
        width: 100%;
        border-radius: 24px;
        box-shadow: 0 8px 32px rgba(91, 140, 255, 0.12), 0 2px 8px rgba(91, 140, 255, 0.08);
        overflow: hidden;
        transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
        animation: slideUp 0.5s ease-out;
    }
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .perfil-main:hover {
        box-shadow: 0 12px 48px rgba(91, 140, 255, 0.18), 0 4px 16px rgba(91, 140, 255, 0.12);
        transform: translateY(-2px);
    }
    .perfil-topo {
        background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        padding: 48px 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .perfil-topo::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
    .perfil-topo h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.02em;
        text-shadow: 0 4px 12px rgba(46, 49, 146, 0.3);
        z-index: 1;
        position: relative;
    }
    .perfil-topo-btns {
        display: flex;
        gap: 12px;
        z-index: 1;
        position: relative;
    }
    .perfil-editar-btn, .perfil-voltar-btn {
        background: rgba(255, 255, 255, 0.95);
        color: #2e3192;
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 10px 24px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        display: inline-block;
    }
    .perfil-editar-btn:hover, .perfil-voltar-btn:hover {
        background: #ffffff;
        color: #0057ff;
        border-color: #0057ff;
        box-shadow: 0 6px 20px rgba(91, 140, 255, 0.3);
        transform: translateY(-2px) scale(1.02);
    }
    .perfil-content {
        padding: 40px;
    }
    .perfil-foto {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        margin-top: -70px;
        margin-bottom: 40px;
        animation: fadeIn 0.6s ease-out 0.2s both;
    }
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .perfil-foto-wrapper {
        position: relative;
    }
    .perfil-foto img {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 8px 24px rgba(91, 140, 255, 0.25);
        border: 6px solid #fff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .perfil-foto img:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 32px rgba(91, 140, 255, 0.35);
    }
    .perfil-foto input[type="file"] { display: none; }
    .perfil-foto label {
        background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        border-radius: 12px;
        padding: 10px 24px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(91, 140, 255, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: none; /* Oculto por padrão */
        align-items: center;
        gap: 8px;
    }
    .perfil-foto label.edicao-ativa {
        display: inline-flex; /* Mostra apenas em modo de edição */
    }
    .perfil-foto label:hover {
        background: linear-gradient(135deg, #4a7aee 0%, #1f2581 100%);
        box-shadow: 0 6px 20px rgba(91, 140, 255, 0.4);
        transform: translateY(-2px);
    }
    .perfil-foto label::before {
        content: '📷';
        font-size: 18px;
    }
    .perfil-section { 
        margin-bottom: 36px;
        animation: fadeIn 0.6s ease-out 0.3s both;
    }
    .perfil-section h3 {
        color: #2e3192;
        font-size: 1.25rem;
        margin-bottom: 20px;
        margin-top: 0;
        font-weight: 700;
        letter-spacing: 0.02em;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e8ecff;
    }
    .perfil-section h3::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
        border-radius: 2px;
    }
    .perfil-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    .perfil-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .perfil-field label {
        font-size: 13px;
        font-weight: 600;
        color: #5b8cff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .perfil-grid input, .perfil-grid select {
        width: 100%;
        padding: 14px 16px;
        border-radius: 12px;
        border: 2px solid #e0eaff;
        font-size: 15px;
        background: #f7faff;
        transition: all 0.3s ease;
        font-family: inherit;
        color: #2e3192;
    }
    .perfil-grid input:focus, .perfil-grid select:focus {
        outline: none;
        border-color: #5b8cff;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(91, 140, 255, 0.15);
        transform: translateY(-1px);
    }
    .perfil-grid input[disabled] {
        background: #f0f4fa;
        color: #7e8ba3;
        cursor: not-allowed;
        border-color: #e8ecff;
    }
    .perfil-form-salvar {
        display: flex;
        justify-content: flex-end;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 2px solid #e8ecff;
    }
    .perfil-form-salvar button {
        background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 14px 32px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(91, 140, 255, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .perfil-form-salvar button:hover {
        background: linear-gradient(135deg, #4a7aee 0%, #1f2581 100%);
        box-shadow: 0 6px 20px rgba(91, 140, 255, 0.4);
        transform: translateY(-2px);
    }
    .perfil-form-salvar button::after {
        content: '💾';
        font-size: 18px;
    }
    .msg-sucesso, .msg-erro {
        padding: 14px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.4s ease-out;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .msg-sucesso {
        background: linear-gradient(135deg, #d4f4dd 0%, #a3e6b7 100%);
        color: #1a7f37;
        border: 2px solid #34d058;
    }
    .msg-sucesso::before {
        content: '✅';
        font-size: 20px;
    }
    .msg-erro {
        background: linear-gradient(135deg, #ffe0e0 0%, #ffb3b3 100%);
        color: #c00;
        border: 2px solid #ff4444;
    }
    .msg-erro::before {
        content: '❌';
        font-size: 20px;
    }
    
    @media (max-width: 768px) {
        .perfil-main { border-radius: 16px; }
        .perfil-content { padding: 24px 20px; }
        .perfil-grid { grid-template-columns: 1fr; gap: 20px; }
        .perfil-topo { padding: 32px 20px; }
        .perfil-topo h1 { font-size: 1.6rem; }
        .perfil-foto img { width: 120px; height: 120px; }
    }

    /* Modo Escuro Aprimorado */
    body.dark-mode {
        background: linear-gradient(135deg, #0f1318 0%, #181c24 100%) !important;
    }
    .perfil-main.dark-mode {
        background: #1a1f2e !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6), 0 2px 8px rgba(0, 0, 0, 0.4) !important;
    }
    .perfil-main.dark-mode:hover {
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.7), 0 4px 16px rgba(0, 0, 0, 0.5) !important;
    }
    .perfil-topo.dark-mode {
        background: linear-gradient(135deg, #2a3142 0%, #1a1f2e 100%) !important;
    }
    .perfil-topo.dark-mode h1 {
        color: #e8ecff !important;
    }
    .perfil-editar-btn.dark-mode, .perfil-voltar-btn.dark-mode {
        background: rgba(42, 49, 66, 0.95) !important;
        color: #8ab4ff !important;
        border: 2px solid #3a4060 !important;
    }
    .perfil-editar-btn.dark-mode:hover, .perfil-voltar-btn.dark-mode:hover {
        background: #2a3142 !important;
        color: #b3d1ff !important;
        border-color: #5b8cff !important;
    }
    .perfil-foto.dark-mode img {
        border-color: #1a1f2e !important;
    }
    .perfil-section.dark-mode h3 {
        color: #8ab4ff !important;
        border-bottom-color: #2a3142 !important;
    }
    .perfil-section.dark-mode h3::before {
        background: linear-gradient(135deg, #8ab4ff 0%, #5b8cff 100%) !important;
    }
    .perfil-field.dark-mode label {
        color: #8ab4ff !important;
    }
    .perfil-grid.dark-mode input, .perfil-grid.dark-mode select {
        background: #23283a !important;
        color: #e8ecff !important;
        border: 2px solid #3a4060 !important;
    }
    .perfil-grid.dark-mode input:focus, .perfil-grid.dark-mode select:focus {
        border-color: #5b8cff !important;
        background: #2a3142 !important;
        box-shadow: 0 4px 12px rgba(91, 140, 255, 0.25) !important;
    }
    .perfil-grid.dark-mode input[disabled] {
        background: #1a1f2e !important;
        color: #6b7280 !important;
        border-color: #2a3142 !important;
    }
    .perfil-form-salvar.dark-mode {
        border-top-color: #2a3142 !important;
    }
    .msg-sucesso.dark-mode {
        background: linear-gradient(135deg, #1a3d2a 0%, #0f2419 100%) !important;
        color: #4ade80 !important;
        border-color: #22c55e !important;
    }
    .msg-erro.dark-mode {
        background: linear-gradient(135deg, #4a2c2c 0%, #2e1919 100%) !important;
        color: #ff6b6b !important;
        border-color: #ef4444 !important;
    }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>

</head>
<body>
    <div class="container">
      <div class="perfil-main" id="perfilMain">
        <div class="perfil-topo" id="perfilTopo">
            <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
            <div class="perfil-topo-btns">
                <a href="inicial.php" class="perfil-voltar-btn" id="btnVoltarInicial">← Voltar</a>
                <button class="perfil-editar-btn" id="btnEditarPerfil" type="button">✏️ Editar</button>
            </div>
        </div>
        <div class="perfil-content">
            <form method="post" class="perfil-form" enctype="multipart/form-data" autocomplete="off">
                <div class="perfil-foto" id="perfilFoto">
                    <div class="perfil-foto-wrapper">
                        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil" id="imgPerfil">
                    </div>
                    <label for="foto_perfil" id="labelAlterarFoto">Alterar Foto</label>
                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" disabled>
                </div>
                <?php if ($sucesso): ?><div class="msg-sucesso" id="msgSucesso"><?php echo $sucesso; ?></div><?php endif; ?>
                <?php if ($erro): ?><div class="msg-erro" id="msgErro"><?php echo $erro; ?></div><?php endif; ?>
                <div class="perfil-section" id="perfilSection1">
                    <h3>📚 Dados Acadêmicos</h3>
                    <div class="perfil-grid" id="perfilGrid1">
                        <div class="perfil-field" id="perfilField1">
                            <label>RGM</label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['rgm']); ?>" disabled>
                        </div>
                        <div class="perfil-field" id="perfilField2">
                            <label>Série</label>
                            <input type="text" name="serie" id="serie" value="<?php echo htmlspecialchars($usuario['serie']); ?>" disabled>
                        </div>
                    </div>
                </div>
                <div class="perfil-section" id="perfilSection2">
                    <h3>📧 Contato</h3>
                    <div class="perfil-grid" id="perfilGrid2">
                        <div class="perfil-field" id="perfilField3">
                            <label>E-mail</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled required>
                        </div>
                        <div class="perfil-field" id="perfilField4">
                            <label>Telefone</label>
                            <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" disabled>
                        </div>
                    </div>
                </div>
                <div class="perfil-form-salvar" id="perfilFormSalvar">
                    <button type="submit" id="btnSalvarPerfil" style="display:none;">Salvar Alterações</button>
                </div>
            </form>
        </div>
      </div>
    </div>
    <script>
    // Habilita edição ao clicar em Editar
    document.getElementById('btnEditarPerfil').onclick = function() {
        document.querySelectorAll('.perfil-form input, .perfil-form select').forEach(function(el) {
            if (el.name !== undefined && el.name !== '') el.disabled = false;
        });
        document.getElementById('btnSalvarPerfil').style.display = 'inline-flex';
        
        // Mostrar botão de alterar foto
        const labelFoto = document.getElementById('labelAlterarFoto');
        if (labelFoto) {
            labelFoto.classList.add('edicao-ativa');
        }
        
        this.style.display = 'none';
    };
    // Preview da foto
    document.getElementById('foto_perfil').onchange = function(e) {
        if (e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('imgPerfil').src = ev.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    };

    // Aplica/remover classes dark-mode de forma completa
    function aplicarModoEscuroPerfil() {
        const isDark = document.documentElement.classList.contains('dark-mode');
        
        // Aplica ao body
        if (isDark) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        
        // Lista de elementos para aplicar dark-mode
        const elementos = [
            '#perfilMain', '#perfilTopo', '#perfilFoto',
            '#perfilSection1', '#perfilSection2',
            '#perfilGrid1', '#perfilGrid2',
            '#perfilField1', '#perfilField2', '#perfilField3', '#perfilField4',
            '#perfilFormSalvar', '#msgSucesso', '#msgErro',
            '#btnVoltarInicial', '#btnEditarPerfil'
        ];
        
        elementos.forEach(selector => {
            const el = document.querySelector(selector);
            if (el) {
                if (isDark) {
                    el.classList.add('dark-mode');
                } else {
                    el.classList.remove('dark-mode');
                }
            }
        });
        
        // Aplica a todos os h3, inputs e labels
        document.querySelectorAll('.perfil-section h3').forEach(e => {
            isDark ? e.classList.add('dark-mode') : e.classList.remove('dark-mode');
        });
        document.querySelectorAll('.perfil-grid input, .perfil-grid select').forEach(e => {
            isDark ? e.classList.add('dark-mode') : e.classList.remove('dark-mode');
        });
        document.querySelectorAll('.perfil-field label').forEach(e => {
            isDark ? e.classList.add('dark-mode') : e.classList.remove('dark-mode');
        });
        document.querySelectorAll('.perfil-field').forEach(e => {
            isDark ? e.classList.add('dark-mode') : e.classList.remove('dark-mode');
        });
        document.querySelectorAll('.perfil-grid').forEach(e => {
            isDark ? e.classList.add('dark-mode') : e.classList.remove('dark-mode');
        });
    }
    
    // Detecta mudança de tema em tempo real
    const observer = new MutationObserver(aplicarModoEscuroPerfil);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    
    // Aplica ao carregar
    document.addEventListener('DOMContentLoaded', aplicarModoEscuroPerfil);
    aplicarModoEscuroPerfil();
    </script>
</body>
</html>
