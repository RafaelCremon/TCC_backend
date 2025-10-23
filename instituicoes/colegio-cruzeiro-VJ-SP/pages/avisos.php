<?php
session_start();
require_once '../../../includes/db.php';
function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
$classe = isset($_SESSION['classe']) ? (int)$_SESSION['classe'] : 0;
$usuario = isset($_SESSION['usuario']) ? esc($_SESSION['usuario']) : 'Anônimo';
if (($classe === 1 || $classe === 2) && isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $stmt = $pdo->prepare('SELECT usuario FROM avisos WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $autor = $stmt->fetchColumn();
    if ($classe === 1 || $autor === $usuario) {
        $stmt = $pdo->prepare('DELETE FROM avisos WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $msg = '<div class="msg-sucesso">Aviso excluído com sucesso!</div>';
    } else {
        $msg = '<div class="msg-erro">Você não tem permissão para excluir este aviso.</div>';
    }
}
if (($classe === 1 || $classe === 2) && isset($_POST['editar_id']) && isset($_POST['novo_conteudo'])) {
    $id = (int)$_POST['editar_id'];
    $novo_conteudo = trim($_POST['novo_conteudo']);
    $stmt = $pdo->prepare('SELECT usuario FROM avisos WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $autor = $stmt->fetchColumn();
    if ($classe === 1 || $autor === $usuario) {
        $stmt = $pdo->prepare('UPDATE avisos SET conteudo = :conteudo WHERE id = :id');
        $stmt->bindParam(':conteudo', $novo_conteudo);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $msg = '<div class="msg-sucesso">Aviso editado com sucesso!</div>';
    } else {
        $msg = '<div class="msg-erro">Você não tem permissão para editar este aviso.</div>';
    }
}
if (($classe === 1 || $classe === 2) && isset($_POST['conteudo']) && trim($_POST['conteudo']) !== '' && !isset($_POST['editar_id'])) {
    $conteudo = trim($_POST['conteudo']);
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
    $tags_str = implode(',', $tags);
    $img_path = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $permitidas)) {
            $dir = '../uploads/avisos/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $nome_arquivo = uniqid('aviso_') . '.' . $ext;
            $destino = $dir . $nome_arquivo;
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                $img_path = $destino;
            }
        }
    }
    $stmt = $pdo->prepare('INSERT INTO avisos (usuario, conteudo, data, imagem, tags) VALUES (:usuario, :conteudo, NOW(), :imagem, :tags)');
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':conteudo', $conteudo);
    $stmt->bindParam(':imagem', $img_path);
    $stmt->bindParam(':tags', $tags_str);
    $stmt->execute();
    $msg = '<div class="msg-sucesso">Aviso postado com sucesso!</div>';
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisos - Quantum Edu.</title>
    <link rel="stylesheet" href="../css/inicial.css?v=<?php echo time(); ?>_avisos">
    <style>
    body { background: #f7faff; }
    .header-avisos {
        background: #fff;
        border-bottom: 1px solid #e0eaff;
        position: sticky;
        top: 0;
        z-index: 10;
        padding: 0 0 0 0;
    }
    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px 12px 24px;
    }
    .header-title {
        font-size: 2.1rem;
        font-weight: 700;
        color: #2e3192;
        margin: 0;
    }
    .header-desc {
        color: #5b8cff;
        font-size: 1.13rem;
        margin-top: 2px;
        font-weight: 400;
    }
    .avisos-main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 32px 16px 0 16px;
    }
    .busca-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }
    .busca-bar input {
        flex: 1;
        padding: 10px 16px;
        border-radius: 8px;
        border: 1px solid #e0eaff;
        font-size: 1.08rem;
        background: #fff;
        color: #222;
        transition: border 0.18s;
    }
    .busca-bar input:focus {
        border: 1.5px solid #5b8cff;
        outline: none;
    }
    .form-aviso {
        margin-bottom: 28px;
    }
    .avisos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
    }
    .aviso-card {
        border: 1px solid #e0eaff;
        border-radius: 14px;
        padding: 18px 20px 14px 20px;
        background: #fff;
        box-shadow: 0 2px 8px #5b8cff11;
        transition: box-shadow 0.18s, transform 0.18s;
        display: flex;
        flex-direction: column;
        min-height: 120px;
    }
    .aviso-card:hover {
        box-shadow: 0 4px 16px #5b8cff22;
        transform: scale(1.01);
    }
    .aviso-autor {
        font-weight: 600;
        color: #0057ff;
        font-size: 1.01rem;
    }
    .aviso-data {
        color: #888;
        font-size: 12px;
        margin-left: 8px;
    }
    .aviso-conteudo {
        margin: 10px 0 0 0;
        font-size: 1.13rem;
        color: #222;
        white-space: pre-line;
        flex: 1;
    }
    .aviso-acoes {
        margin-top: 10px;
    }
    .aviso-acoes a {
        color: #0057ff;
        margin-right: 16px;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.18s;
    }
    .aviso-acoes a:last-child {
        color: #d32f2f;
        margin-right: 0;
    }
    .aviso-acoes a:hover {
        text-decoration: underline;
        color: #2e3192;
    }
    .msg-sucesso {
        background: #eaf2ff;
        color: #0057ff;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 18px;
        font-weight: 500;
        box-shadow: 0 2px 8px #5b8cff11;
    }
    .msg-erro {
        background: #ffd6d6;
        color: #d32f2f;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 18px;
        font-weight: 500;
        box-shadow: 0 2px 8px #d32f2f11;
    }
    .form-aviso textarea {
        width: 100%;
        border-radius: 8px;
        border: 1px solid #e0eaff;
        padding: 10px;
        font-size: 1.08rem;
        margin-bottom: 8px;
        resize: vertical;
        background: #f7faff;
        color: #222;
    }
    .form-aviso button {
        background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 8px 22px;
        font-size: 1.08rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.18s, transform 0.18s;
        box-shadow: 0 2px 8px #5b8cff22;
    }
    .form-aviso button:hover {
        background: linear-gradient(135deg, #2e3192 0%, #5b8cff 100%);
        transform: scale(1.04);
    }
    @media (max-width: 700px) {
        .header-content, .avisos-main { padding-left: 8px; padding-right: 8px; }
    }
    /* Tema escuro */
    body.dark-mode { background: #181c2a; }
    body.dark-mode .header-avisos { background: #181c2a; border-bottom: 1px solid #23263a; }
    body.dark-mode .header-title { color: #8ab4ff; }
    body.dark-mode .header-desc { color: #eaf2ff; }
    body.dark-mode .avisos-main { background: #181c2a; }
    body.dark-mode .aviso-card { background: #23263a; border-color: #23263a; color: #eaf2ff; }
    body.dark-mode .aviso-conteudo { color: #eaf2ff; }
    body.dark-mode .form-aviso textarea { background: #23263a; color: #eaf2ff; border-color: #23263a; }
    body.dark-mode .msg-sucesso { background: #23263a; color: #8ab4ff; }
    body.dark-mode .msg-erro { background: #2e3192; color: #ffd6d6; }
    </style>
</head>
<body>
    <div class="header-avisos">
        <div class="header-content">
            <div>
                <div class="header-title">Estante de Avisos</div>
                <div class="header-desc">Veja e compartilhe comunicados importantes da instituição.</div>
            </div>
            <a href="inicial.php" style="text-decoration:none;"><img src="../assets/imagens/LOGO.png" alt="Logo Quantum" style="width:38px;height:38px;border-radius:50%;box-shadow:0 2px 8px #5b8cff22;"></a>
        </div>
    </div>
    <main class="avisos-main">
        <?php if (isset($msg)) echo $msg; ?>
        <div class="busca-bar">
            <input type="text" id="buscaAviso" placeholder="Buscar aviso..." oninput="filtrarAvisos()">
            <?php if ($classe === 1 || $classe === 2): ?>
            <form method="post" class="form-aviso" style="margin:0;flex:1;max-width:420px;" enctype="multipart/form-data">
                <textarea name="conteudo" rows="2" placeholder="Escreva um novo aviso..." required></textarea>
                <div style="margin:8px 0 8px 0;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                    <label style="font-size:0.98rem;color:#2e3192;font-weight:500;">Tags:</label>
                    <?php
                    $tags_padrao = ['matemática','português','geral','materias técnicas','3ºs anos','3ºJ'];
                    foreach ($tags_padrao as $tag) {
                        echo '<label style="font-size:0.97rem;"><input type="checkbox" name="tags[]" value="'.esc($tag).'"> '.esc($tag).'</label>';
                    }
                    ?>
                </div>
                <input type="file" name="imagem" accept="image/*" style="margin-bottom:8px;">
                <button type="submit">Postar</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="avisos-grid" id="avisosGrid">
        <?php
        $stmt = $pdo->query('SELECT id, usuario, conteudo, data, imagem, tags FROM avisos ORDER BY data DESC');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pode_editar = ($classe === 1 || ($classe === 2 && $row['usuario'] === $usuario));
            $tags = array_filter(array_map('trim', explode(',', $row['tags'] ?? '')));
            echo '<div class="aviso-card" data-conteudo="' . esc(strtolower($row['conteudo'].' '.implode(' ',$tags))) . '">';
            echo '<span class="aviso-autor">' . esc($row['usuario']) . '</span>';
            echo '<span class="aviso-data">(' . esc($row['data']) . ')</span>';
            if (!empty($tags)) {
                echo '<div style="margin:6px 0 0 0;">';
                foreach ($tags as $tag) {
                    echo '<span style="display:inline-block;background:#eaf2ff;color:#2e3192;font-size:0.93rem;padding:2px 10px;border-radius:7px;margin-right:6px;margin-bottom:2px;">#'.esc($tag).'</span>';
                }
                echo '</div>';
            }
            if (!empty($row['imagem'])) {
                $imgsrc = esc($row['imagem']);
                if (strpos($imgsrc, '../') === 0) $imgsrc = substr($imgsrc, 2); // Corrige caminho relativo
                echo '<div style="margin:10px 0 0 0;"><img src="'.esc($imgsrc).'" alt="Imagem do aviso" style="max-width:100%;max-height:180px;border-radius:10px;box-shadow:0 2px 8px #5b8cff22;"></div>';
            }
            if ($pode_editar && isset($_GET['editar']) && $_GET['editar'] == $row['id']) {
                echo '<form method="post" class="form-aviso" style="margin-top:10px;">';
                echo '<textarea name="novo_conteudo" rows="3" required>' . esc($row['conteudo']) . '</textarea><br>';
                echo '<input type="hidden" name="editar_id" value="' . $row['id'] . '">';
                echo '<button type="submit">Salvar</button> ';
                echo '<a href="avisos.php" style="color:#d32f2f;">Cancelar</a>';
                echo '</form>';
            } else {
                echo '<div class="aviso-conteudo">' . nl2br(esc($row['conteudo'])) . '</div>';
                if ($pode_editar) {
                    echo '<div class="aviso-acoes">';
                    echo '<a href="avisos.php?editar=' . $row['id'] . '">Editar</a>';
                    echo '<a href="avisos.php?excluir=' . $row['id'] . '" onclick="return confirm(\'Tem certeza que deseja excluir?\');">Excluir</a>';
                    echo '</div>';
                }
            }
            echo '</div>';
        }
        ?>
        </div>
    </main>
    <script>
    function setTheme(isDark) {
        if (isDark) document.body.classList.add('dark-mode');
        else document.body.classList.remove('dark-mode');
    }
    function loadSavedTheme() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') setTheme(true);
        else setTheme(false);
    }
    loadSavedTheme();
    // Filtro de busca local
    function filtrarAvisos() {
        const termo = document.getElementById('buscaAviso').value.toLowerCase();
        document.querySelectorAll('.aviso-card').forEach(card => {
            const conteudo = card.getAttribute('data-conteudo');
            card.style.display = conteudo.includes(termo) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
