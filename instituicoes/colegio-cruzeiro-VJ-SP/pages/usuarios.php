<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !in_array((int)$_SESSION['classe'], [1,2])) {
    header('Location: login.php');
    exit;
}
require_once '../../../includes/db.php';

// Função para buscar todos os usuários (exceto classe 1)
function getUsuarios($pdo, $instituicao_id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE instituicao_id = :instituicao_id AND (classe = '2' OR classe = '3')");
    $stmt->execute(['instituicao_id' => $instituicao_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para criar usuário (classe 2 ou 3)
function criarUsuario($pdo, $dados) {
    $stmt = $pdo->prepare("INSERT INTO usuarios (instituicao_id, classe, nome, usuario, email, telefone, senha) VALUES (:instituicao_id, :classe, :nome, :usuario, :email, :telefone, :senha)");
    $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
    $stmt->execute($dados);
}

// Função para editar usuário
function editarUsuario($pdo, $dados) {
    $sql = "UPDATE usuarios SET classe = :classe, nome = :nome, usuario = :usuario, email = :email, telefone = :telefone";
    $params = [
        'classe' => $dados['classe'],
        'nome' => $dados['nome'],
        'usuario' => $dados['usuario'],
        'email' => $dados['email'],
        'telefone' => $dados['telefone'],
        'id' => $dados['id']
    ];
    if (!empty($dados['senha'])) {
        $sql .= ", senha = :senha";
        $params['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
    }
    $sql .= " WHERE id = :id AND (classe = '2' OR classe = '3')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

// Função para buscar usuário por id
function getUsuarioById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id AND (classe = '2' OR classe = '3')");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para excluir usuário (classe 2 ou 3)
function excluirUsuario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND (classe = '2' OR classe = '3')");
    $stmt->execute(['id' => $id]);
}

// Processamento de formulário
$erro = '';
$sucesso = '';
if (isset($_GET['ok'])) {
    if ($_GET['ok'] === 'criado') {
        $sucesso = 'Usuário criado com sucesso!';
    } elseif ($_GET['ok'] === 'editado') {
        $sucesso = 'Usuário editado com sucesso!';
    } elseif ($_GET['ok'] === 'excluido') {
        $sucesso = 'Usuário excluído com sucesso!';
    }
    // Limpa o parâmetro da URL após exibir a mensagem, usando JS para não recarregar
    echo "<script>if (window.history.replaceState) { window.history.replaceState(null, '', 'usuarios.php'); }</script>";
}
$usuarioEdit = null;

// Buscar id da instituição do usuário logado se não estiver na sessão
if (isset($_SESSION['instituicao_id'])) {
    $instituicao_id = $_SESSION['instituicao_id'];
} else {
    // Busca no banco
    $stmt = $pdo->prepare("SELECT instituicao_id FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $instituicao_id = $row ? $row['instituicao_id'] : null;
    $_SESSION['instituicao_id'] = $instituicao_id;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['criar'])) {
        // Criação
        $dados = [
            'instituicao_id' => $instituicao_id,
            'classe' => $_POST['classe'],
            'nome' => $_POST['nome'],
            'usuario' => $_POST['usuario'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'],
            'senha' => $_POST['senha']
        ];
        if (!in_array($dados['classe'], ['2','3'])) {
            $erro = 'Só é permitido criar usuários de classe 2 ou 3.';
        } else {
            criarUsuario($pdo, $dados);
            header('Location: usuarios.php?ok=criado');
            exit;
        }
    } elseif (isset($_POST['editar'])) {
        // Edição
        $dados = [
            'id' => $_POST['id'],
            'classe' => $_POST['classe'],
            'nome' => $_POST['nome'],
            'usuario' => $_POST['usuario'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'],
            'senha' => $_POST['senha']
        ];
        if (!in_array($dados['classe'], ['2','3'])) {
            $erro = 'Só é permitido editar para classe 2 ou 3.';
        } else {
            editarUsuario($pdo, $dados);
            header('Location: usuarios.php?ok=editado');
            exit;
        }
    } elseif (isset($_POST['excluir'])) {
        excluirUsuario($pdo, $_POST['id']);
        header('Location: usuarios.php?ok=excluido');
        exit;
    } elseif (isset($_POST['carregar_edicao'])) {
        $usuarioEdit = getUsuarioById($pdo, $_POST['id']);
    }
}

$usuarios = getUsuarios($pdo, $instituicao_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="../css/inicial.css?v=<?php echo time(); ?>_usuarios_theme">
    <style>
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 32px;
        }
        h2 {
            color: var(--brand);
        }
        form { margin-bottom: 32px; }
        label { display: block; margin-top: 12px; color: var(--ink); }
        input, select {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: 1.5px solid var(--ring);
            margin-top: 4px;
            background: var(--card);
            color: var(--ink);
            font-size: 1rem;
        }
        input:focus, select:focus {
            outline: 2px solid var(--brand);
            border-color: var(--brand);
        }
        button {
            background: linear-gradient(90deg, var(--brand) 0%, var(--brand-2) 100%);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            margin-top: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s, color 0.2s;
        }
        button:hover {
            filter: brightness(1.08);
        }
        .msg { margin: 12px 0; padding: 10px; border-radius: 6px; }
        .erro { background: #ffe0e0; color: #b30000; }
        .sucesso { background: #e0ffe0; color: #008c2e; }
        /* Grid de cards de usuários */
        .usuarios-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 28px;
            margin-top: 24px;
        }
        .usuario-card {
            background: var(--card);
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(44,92,255,0.10);
            padding: 24px 22px 18px 22px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            min-width: 0;
            transition: box-shadow 0.18s, transform 0.13s;
            position: relative;
        }
        .usuario-card:hover {
            box-shadow: 0 6px 32px rgba(44,92,255,0.18);
            transform: translateY(-2px) scale(1.02);
        }
        .usuario-card-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 10px;
        }
        .usuario-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #eaf2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(44,92,255,0.08);
        }
        .usuario-card-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .usuario-nome {
            font-size: 1.18em;
            font-weight: 700;
            color: var(--brand);
        }
        .usuario-usuario {
            font-size: 1em;
            color: var(--muted);
            margin-bottom: 2px;
        }
        .usuario-card-body {
            margin-bottom: 10px;
            color: var(--ink);
            font-size: 1em;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .usuario-card-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
            justify-content: flex-end;
        }
        .badge-classe {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.98em;
            font-weight: 600;
            color: #fff;
            margin-top: 4px;
        }
        .badge-prof { background: linear-gradient(90deg,#5b8cff 0%,#2e3192 100%); }
        .badge-aluno { background: linear-gradient(90deg,#00e0ff 0%,#2e3192 100%); }
        .card-btn {
            min-width: 80px;
            padding: 10px 14px;
            font-size: 1em;
            margin: 2px 0;
        }
        @media (max-width: 800px) {
            .container { padding: 10px; }
            table, th, td { font-size: 0.95em; }
            .usuarios-cards-grid { grid-template-columns: 1fr; }
            .usuario-card { padding: 16px 8px; }
        }
        body.dark-mode .usuario-card {
            background: #232a4d;
            color: #f1f1f1;
            box-shadow: 0 2px 16px rgba(0,224,255,0.10);
        }
        body.dark-mode .usuario-nome { color: #00e0ff; }
        body.dark-mode .usuario-usuario { color: #8fa2d9; }
        body.dark-mode .usuario-card-body { color: #f1f1f1; }
        body.dark-mode .usuario-avatar { background: #2e1a47; }
    </style>
    <script>
    // Dark mode automático se já estiver ativado no inicial
    if (localStorage.getItem('theme') === 'dark' || document.body.classList.contains('dark-mode')) {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
    }
    </script>
</head>
<body>
<div class="container">
    <button onclick="window.location='inicial.php'" class="big-action-btn" style="margin-bottom:22px;">
        <svg style="width:26px;height:26px;vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
        <span style="font-size:1.15em; font-weight:600; margin-left:6px;">Voltar</span>
    </button>
    <div style="display:flex;gap:18px;margin-bottom:32px;justify-content:center;">
        <button id="tab-cadastro" type="button" class="big-action-btn" style="background:var(--brand);color:#fff;">
            <svg style="width:22px;height:22px;margin-right:7px;vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2H7v-2h2V7h2v4h2v2h-2v2zm-1-14a8 8 0 1 1 0 16 8 8 0 0 1 0-16z"/></svg>
            Cadastro/Edição
        </button>
        <button id="tab-lista" type="button" class="big-action-btn" style="background:var(--brand-2);color:#fff;">
            <svg style="width:22px;height:22px;margin-right:7px;vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 8h14v-2H7v2zm0-4h14v-2H7v2zm0-6v2h14V7H7z"/></svg>
            Visualizar Usuários
        </button>
    </div>
    <div id="cadastro-section" style="display:block;">
        <h2><?php echo $usuarioEdit ? 'Editar Usuário' : 'Criar Novo Usuário'; ?></h2>
        <?php if ($erro): ?><div class="msg erro"><?php echo $erro; ?></div><?php endif; ?>
        <?php if ($sucesso): ?><div class="msg sucesso"><?php echo $sucesso; ?></div><?php endif; ?>
        <form method="post">
            <?php if ($usuarioEdit): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuarioEdit['id']); ?>">
            <?php endif; ?>
            <label>Nome
                <input type="text" name="nome" required value="<?php echo $usuarioEdit ? htmlspecialchars($usuarioEdit['nome']) : ''; ?>">
            </label>
            <label>Usuário
                <input type="text" name="usuario" required value="<?php echo $usuarioEdit ? htmlspecialchars($usuarioEdit['usuario']) : ''; ?>">
            </label>
            <label>Email
                <input type="email" name="email" required value="<?php echo $usuarioEdit ? htmlspecialchars($usuarioEdit['email']) : ''; ?>">
            </label>
            <label>Telefone
                <input type="text" name="telefone" value="<?php echo $usuarioEdit ? htmlspecialchars($usuarioEdit['telefone']) : ''; ?>">
            </label>
            <label>Classe
                <select name="classe" required>
                    <option value="2" <?php if ($usuarioEdit && $usuarioEdit['classe'] == '2') echo 'selected'; ?>>2 (Professor)</option>
                    <option value="3" <?php if ($usuarioEdit && $usuarioEdit['classe'] == '3') echo 'selected'; ?>>3 (Aluno)</option>
                </select>
            </label>
            <label>Senha <?php if ($usuarioEdit) echo '(deixe em branco para não alterar)'; ?>
                <input type="password" name="senha" <?php if (!$usuarioEdit) echo 'required'; ?> autocomplete="new-password">
            </label>
            <button type="submit" name="<?php echo $usuarioEdit ? 'editar' : 'criar'; ?>" class="big-action-btn" style="background:var(--brand-2);color:#fff;min-width:180px;">
                <svg style="width:22px;height:22px;margin-right:7px;vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M5 13l4 4L19 7"/></svg>
                <?php echo $usuarioEdit ? 'Salvar Alterações' : 'Criar Usuário'; ?>
            </button>
            <?php if ($usuarioEdit): ?>
                <button type="button" onclick="window.location='usuarios.php'" class="big-action-btn" style="background:#e53e3e;color:#fff;min-width:120px;">
                    <svg style="width:20px;height:20px;margin-right:7px;vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    Cancelar
                </button>
            <?php endif; ?>
        </form>
    </div>
    <div id="lista-section" style="display:none;">
        <h2>Usuários Cadastrados</h2>
        <div class="usuarios-cards-grid">
        <?php foreach ($usuarios as $u): ?>
            <div class="usuario-card">
                <div class="usuario-card-header">
                    <div class="usuario-avatar">
                        <svg viewBox="0 0 48 48" width="48" height="48"><circle cx="24" cy="24" r="22" fill="#eaf2ff"/><ellipse cx="24" cy="20" rx="10" ry="10" fill="#b5c6e0"/><ellipse cx="24" cy="38" rx="16" ry="8" fill="#dbeaff"/></svg>
                    </div>
                    <div class="usuario-card-info">
                        <div class="usuario-nome"><?php echo htmlspecialchars($u['nome']); ?></div>
                        <div class="usuario-usuario">@<?php echo htmlspecialchars($u['usuario']); ?></div>
                        <span class="badge-classe badge-<?php echo $u['classe'] == '2' ? 'prof' : 'aluno'; ?>"><?php echo $u['classe'] == '2' ? 'Professor' : 'Aluno'; ?></span>
                    </div>
                </div>
                <div class="usuario-card-body">
                    <div class="usuario-email"><b>Email:</b> <?php echo htmlspecialchars($u['email']); ?></div>
                    <div class="usuario-tel"><b>Telefone:</b> <?php echo htmlspecialchars($u['telefone']); ?></div>
                </div>
                <div class="usuario-card-actions">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($u['id']); ?>">
                        <button type="submit" name="carregar_edicao" class="big-action-btn card-btn" title="Editar">
                            <svg style="width:18px;height:18px;margin-right:5px;vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M3 17.25V21h3.75l11.06-11.06-3.75-3.75L3 17.25zm14.71-9.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            Editar
                        </button>
                    </form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($u['id']); ?>">
                        <button type="submit" name="excluir" class="big-action-btn card-btn" style="background:#e53e3e;color:#fff;" title="Excluir">
                            <svg style="width:18px;height:18px;margin-right:5px;vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
// Alternância de abas
const tabCadastro = document.getElementById('tab-cadastro');
const tabLista = document.getElementById('tab-lista');
const cadastroSection = document.getElementById('cadastro-section');
const listaSection = document.getElementById('lista-section');
tabCadastro.onclick = function() {
    cadastroSection.style.display = 'block';
    listaSection.style.display = 'none';
    tabCadastro.style.background = 'var(--brand)';
    tabLista.style.background = 'var(--brand-2)';
};
tabLista.onclick = function() {
    cadastroSection.style.display = 'none';
    listaSection.style.display = 'block';
    tabCadastro.style.background = 'var(--brand-2)';
    tabLista.style.background = 'var(--brand)';
};
// Tema escuro automático
if (localStorage.getItem('theme') === 'dark' || document.body.classList.contains('dark-mode')) {
    document.documentElement.classList.add('dark-mode');
    document.body.classList.add('dark-mode');
}
</script>
</body>
</html>
