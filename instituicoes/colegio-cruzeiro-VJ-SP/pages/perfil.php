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
$stmt2 = $pdo->prepare('SELECT foto_perfil FROM plataforma WHERE usuario_id = :id');
$stmt2->bindParam(':id', $usuario_id);
$stmt2->execute();
$foto_perfil = $stmt2->fetchColumn();
if (!$foto_perfil) {
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
    body {
        background: #fff;
        font-family: 'Segoe UI', Arial, sans-serif;
        transition: background 0.3s, color 0.3s;
    }
    .container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding-top: 40px;
    }
    .perfil-main {
        background: #fff;
        max-width: 900px;
        width: 100%;
        border-radius: 18px;
        box-shadow: 0 4px 24px #5b8cff22;
        padding: 0 32px 32px 32px;
        transition: background 0.3s, color 0.3s;
    }
    .perfil-topo {
        background: linear-gradient(90deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        padding: 22px 32px 22px 28px;
        border-radius: 18px 18px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 12px #5b8cff22;
    }
    .perfil-topo h1 {
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.01em;
        text-shadow: 0 2px 8px #2e319244;
    }
    .perfil-editar-btn, .perfil-voltar-btn {
        background: #fff;
        color: #2e3192;
        border: none;
        border-radius: 10px;
        padding: 8px 22px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.22s, color 0.22s, box-shadow 0.22s, transform 0.18s;
        box-shadow: 0 2px 8px #5b8cff22;
        margin-left: 10px;
    }
    .perfil-editar-btn:hover, .perfil-voltar-btn:hover {
        background: #eaf2ff;
        color: #0057ff;
        box-shadow: 0 4px 16px #5b8cff33;
        transform: scale(1.04);
    }
    .perfil-foto {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-top: -46px;
        margin-bottom: 24px;
    }
    .perfil-foto img {
        width: 104px;
        height: 104px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 12px #5b8cff33;
        border: 5px solid #fff;
    }
    .perfil-foto input[type="file"] { display: none; }
    .perfil-foto label {
        background: linear-gradient(90deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        margin-left: 8px;
        box-shadow: 0 2px 8px #5b8cff22;
        transition: background 0.22s, color 0.22s, box-shadow 0.22s, transform 0.18s;
    }
    .perfil-foto label:hover {
        background: #eaf2ff;
        color: #0057ff;
        box-shadow: 0 4px 16px #5b8cff33;
        transform: scale(1.04);
    }
    .perfil-section { margin-bottom: 32px; }
    .perfil-section h3 {
        color: #2e3192;
        font-size: 1.13rem;
        margin-bottom: 14px;
        margin-top: 0;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    .perfil-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px 24px;
    }
    .perfil-grid input, .perfil-grid select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #e0eaff;
        font-size: 15px;
        background: #f7faff;
        transition: background 0.22s, color 0.22s, border 0.22s;
        box-shadow: 0 2px 8px #5b8cff11;
    }
    .perfil-grid input[disabled] {
        background: #f0f4fa;
        color: #888;
    }
    .perfil-form-salvar {
        display: flex;
        justify-content: flex-end;
    }
    .perfil-form-salvar button {
        background: linear-gradient(90deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 22px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.22s, color 0.22s, box-shadow 0.22s, transform 0.18s;
        box-shadow: 0 2px 8px #5b8cff22;
    }
    .perfil-form-salvar button:hover {
        background: #003fa3;
        color: #fff;
        box-shadow: 0 4px 16px #5b8cff33;
        transform: scale(1.04);
    }
    .msg-sucesso {
        color: #1a7f37;
        margin-bottom: 10px;
        font-weight: 600;
    }
    .msg-erro {
        color: #c00;
        margin-bottom: 10px;
        font-weight: 600;
    }
    @media (max-width: 700px) {
        .perfil-main { padding: 0 8px 24px 8px; }
        .perfil-grid { grid-template-columns: 1fr; }
        .perfil-topo { flex-direction: column; align-items: flex-start; gap: 12px; padding: 18px 12px 18px 12px; }
    }

    /* Modo escuro completo */
    body.dark-mode {
        background: #181c24 !important;
        color: #eaf2ff !important;
    }
    .perfil-main.dark-mode {
        background: #23283a !important;
        color: #eaf2ff !important;
        box-shadow: 0 2px 16px #0008;
    }
    .perfil-topo.dark-mode {
        background: linear-gradient(90deg, #23283a 0%, #181c24 100%) !important;
        color: #eaf2ff !important;
    }
    .perfil-editar-btn.dark-mode, .perfil-voltar-btn.dark-mode {
        background: #23283a !important;
        color: #8bb3ff !important;
        border: 1px solid #3a4060 !important;
    }
    .perfil-editar-btn.dark-mode:hover, .perfil-voltar-btn.dark-mode:hover {
        background: #181c24 !important;
        color: #fff !important;
    }
    .perfil-section h3.dark-mode { color: #8bb3ff !important; }
    .perfil-grid input.dark-mode, .perfil-grid select.dark-mode {
        background: #23283a !important;
        color: #eaf2ff !important;
        border: 1px solid #3a4060 !important;
    }
    .perfil-grid input[disabled].dark-mode { background: #23283a !important; color: #888 !important; }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>

</head>
<body>
        <script>
            // Força o modo escuro ao abrir a página para teste
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('dark-mode');
            });
        </script>
        <div class="container" style="display:flex;justify-content:center;align-items:flex-start;min-height:100vh;padding-top:40px;">
      <div class="perfil-main" id="perfilMain">
        <div class="perfil-topo" id="perfilTopo" style="flex-direction:column;align-items:center;gap:10px;text-align:center;">
            <h1 style="margin-bottom:0;font-size:1.6rem;font-weight:700;letter-spacing:0.01em;">
                <?php echo htmlspecialchars($usuario['nome']); ?>
            </h1>
            <div style="display:flex;gap:10px;justify-content:center;">
                <a href="inicial.php" class="perfil-voltar-btn" id="btnVoltarInicial">Voltar</a>
                <button class="perfil-editar-btn" id="btnEditarPerfil" type="button">Editar</button>
            </div>
        </div>
        <form method="post" class="perfil-form" enctype="multipart/form-data" autocomplete="off">
            <div class="perfil-foto">
                <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil" id="imgPerfil">
                <label for="foto_perfil">Alterar Foto</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" disabled>
            </div>
            <?php if ($sucesso): ?><div class="msg-sucesso"><?php echo $sucesso; ?></div><?php endif; ?>
            <?php if ($erro): ?><div class="msg-erro"><?php echo $erro; ?></div><?php endif; ?>
            <div class="perfil-section">
                <h3>Dados Acadêmicos</h3>
                <div class="perfil-grid">
                    <div>
                        <label>RGM</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['rgm']); ?>" disabled>
                    </div>
                    <div>
                        <label>Série</label>
                        <input type="text" name="serie" id="serie" value="<?php echo htmlspecialchars($usuario['serie']); ?>" disabled>
                    </div>
                </div>
            </div>
            <div class="perfil-section">
                <h3>Contato</h3>
                <div class="perfil-grid">
                    <div>
                        <label>E-mail</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled required>
                    </div>
                    <div>
                        <label>Telefone</label>
                        <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" disabled>
                    </div>
                </div>
            </div>
            <div class="perfil-form-salvar">
                <button type="submit" id="btnSalvarPerfil" style="display:none;">Salvar Alterações</button>
            </div>
        </form>
      </div>
    </div>
    <script>
    // Habilita edição ao clicar em Editar
    document.getElementById('btnEditarPerfil').onclick = function() {
        document.querySelectorAll('.perfil-form input, .perfil-form select').forEach(function(el) {
            if (el.name !== undefined && el.name !== '') el.disabled = false;
        });
        document.getElementById('btnSalvarPerfil').style.display = 'inline-block';
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

    // Aplica/remover classes dark-mode igual ao inicial.php
    function aplicarModoEscuroPerfil() {
        const isDark = document.documentElement.classList.contains('dark-mode');
        const main = document.getElementById('perfilMain');
        const topo = document.getElementById('perfilTopo');
        if (isDark) {
            main.classList.add('dark-mode');
            topo.classList.add('dark-mode');
            document.querySelectorAll('.perfil-section h3').forEach(e=>e.classList.add('dark-mode'));
            document.querySelectorAll('.perfil-grid input, .perfil-grid select').forEach(e=>e.classList.add('dark-mode'));
            document.querySelectorAll('.perfil-editar-btn, .perfil-voltar-btn').forEach(e=>e.classList.add('dark-mode'));
        } else {
            main.classList.remove('dark-mode');
            topo.classList.remove('dark-mode');
            document.querySelectorAll('.perfil-section h3').forEach(e=>e.classList.remove('dark-mode'));
            document.querySelectorAll('.perfil-grid input, .perfil-grid select').forEach(e=>e.classList.remove('dark-mode'));
            document.querySelectorAll('.perfil-editar-btn, .perfil-voltar-btn').forEach(e=>e.classList.remove('dark-mode'));
        }
    }
    // Detecta mudança de tema em tempo real
    const observer = new MutationObserver(aplicarModoEscuroPerfil);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    // Aplica ao carregar
    aplicarModoEscuroPerfil();
    </script>
</body>
</html>
