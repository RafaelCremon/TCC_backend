<?php
session_start();
require_once '../../../includes/db.php';
function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
$classe = isset($_SESSION['classe']) ? (int)$_SESSION['classe'] : 0;
$usuario = isset($_SESSION['usuario']) ? esc($_SESSION['usuario']) : 'Anônimo';

// Filtros de busca e tag
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

// Paginação
$avisosPorPagina = 6;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $avisosPorPagina;

// Monta WHERE dinâmico
$where = [];
$params = [];
if ($busca !== '') {
    $where[] = '(LOWER(conteudo) LIKE :busca OR LOWER(tags) LIKE :busca)';
    $params[':busca'] = '%' . mb_strtolower($busca, 'UTF-8') . '%';
}
if ($tag !== '') {
    $where[] = 'FIND_IN_SET(:tag, REPLACE(tags, ", ", ",")) > 0';
    $params[':tag'] = $tag;
}
$whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Conta total filtrado
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM avisos $whereSQL");
foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
$stmtCount->execute();
$totalAvisos = $stmtCount->fetchColumn();
$totalPaginas = max(1, ceil($totalAvisos / $avisosPorPagina));
if (($classe === 1 || $classe === 2) && isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $stmt = $pdo->prepare('SELECT usuario, anexo FROM avisos WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $autor = $row ? $row['usuario'] : null;
    $anexo = $row ? $row['anexo'] : null;
    if ($classe === 1 || $autor === $usuario) {
        $stmt = $pdo->prepare('DELETE FROM avisos WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($anexo) {
            $anexoPath = __DIR__ . '/../../../uploads/avisos/' . basename($anexo);
            if (file_exists($anexoPath)) {
                @unlink($anexoPath);
            }
        }
        header('Location: avisos.php');
        exit;
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
    header('Location: avisos.php');
    exit;
    } else {
    $msg = '<div class="msg-erro">Você não tem permissão para editar este aviso.</div>';
    }
}
if (($classe === 1 || $classe === 2) && isset($_POST['conteudo']) && trim($_POST['conteudo']) !== '' && !isset($_POST['editar_id'])) {
    $conteudo = trim($_POST['conteudo']);
    $tags_str = isset($_POST['tags']) ? trim($_POST['tags']) : '';
    $data_inicial = isset($_POST['data_inicial']) ? $_POST['data_inicial'] : null;
    $data_limite = isset($_POST['data_limite']) ? $_POST['data_limite'] : null;
    $anexo_path = null;
    if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','rar','7z','csv','mp4','mp3'];
    if (in_array($ext, $permitidas)) {
            $dir = 'uploads/avisos/'; // Caminho público
            if (!is_dir(__DIR__ . '/../../../' . $dir)) mkdir(__DIR__ . '/../../../' . $dir, 0777, true);
            $nome_arquivo = uniqid('aviso_') . '.' . $ext;
            $destino = __DIR__ . '/../../../' . $dir . $nome_arquivo;
            if (move_uploaded_file($_FILES['anexo']['tmp_name'], $destino)) {
                $anexo_path = rtrim($dir, '/') . '/' . $nome_arquivo; // Caminho salvo no banco, SEM barra inicial
                $anexo_path = ltrim($anexo_path, '/');
            }
    }
    }
    // Adicionar as colunas data_inicial e data_limite na tabela avisos se ainda não existirem
    try {
    $pdo->query("ALTER TABLE avisos ADD COLUMN data_inicial DATE NULL, ADD COLUMN data_limite DATE NULL");
    } catch (Exception $e) { /* ignora erro se já existe */ }
    $stmt = $pdo->prepare('INSERT INTO avisos (usuario, conteudo, data, anexo, tags, data_inicial, data_limite) VALUES (:usuario, :conteudo, NOW(), :anexo, :tags, :data_inicial, :data_limite)');
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':conteudo', $conteudo);
    $stmt->bindParam(':anexo', $anexo_path);
    $stmt->bindParam(':tags', $tags_str);
    $stmt->bindParam(':data_inicial', $data_inicial);
    $stmt->bindParam(':data_limite', $data_limite);
    $stmt->execute();
    header('Location: avisos.php');
    exit;
}
// ...existing code...
// ...existing code...
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisos - Quantum Edu.</title>
    <link rel="stylesheet" href="../css/inicial.css?v=<?php echo time(); ?>_avisos">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        background: linear-gradient(135deg, #eaf2ff 0%, #f7faff 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', 'SF Pro Display', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        transition: background 0.3s ease;
    }
    .header-avisos {
        background: linear-gradient(135deg, #1f2581 0%, #0d1147 100%);
        border-bottom: 0;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 4px 24px rgba(13, 17, 71, 0.4);
        padding: 0;
        animation: slideDown 0.5s ease-out;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
    }
    .header-title {
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 4px 0;
        letter-spacing: 0.02em;
        text-shadow: 0 4px 12px rgba(46, 49, 146, 0.3);
    }
    .header-desc {
        color: rgba(255, 255, 255, 0.85);
        font-size: 1rem;
        margin: 0;
        font-weight: 400;
        text-shadow: 0 2px 8px rgba(46, 49, 146, 0.2);
    }
    .avisos-main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 24px;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 8px 32px rgba(91, 140, 255, 0.12), 0 2px 8px rgba(91, 140, 255, 0.08);
        margin-top: -32px;
        position: relative;
        z-index: 10;
        animation: slideUp 0.6s ease-out 0.1s both;
    }
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .busca-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 32px;
        background: linear-gradient(135deg, #f7faff 0%, #eaf2ff 100%);
        border-radius: 16px;
        padding: 16px 20px;
        box-shadow: 0 4px 16px rgba(91, 140, 255, 0.08);
        border: 2px solid rgba(91, 140, 255, 0.1);
        transition: all 0.3s ease;
    }
    .busca-bar:hover {
        box-shadow: 0 6px 24px rgba(91, 140, 255, 0.12);
        border-color: rgba(91, 140, 255, 0.2);
    }
    .busca-bar input, .busca-bar select {
        flex: 1;
        padding: 12px 16px;
        border-radius: 12px;
        border: 2px solid #e0eaff;
        font-size: 15px;
        background: #ffffff;
        color: #2e3192;
        transition: all 0.3s ease;
        font-family: inherit;
    }
    .busca-bar input:focus, .busca-bar select:focus {
        border-color: #5b8cff;
        outline: none;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(91, 140, 255, 0.15);
        transform: translateY(-1px);
    }
    .busca-bar button {
        background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(91, 140, 255, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .busca-bar button:hover {
        background: linear-gradient(135deg, #4a7aee 0%, #1f2581 100%);
        box-shadow: 0 6px 20px rgba(91, 140, 255, 0.4);
        transform: translateY(-2px);
    }
    body.dark-mode .busca-bar {
        background: #23263a;
        box-shadow: 0 2px 8px #2e319244;
    }
    body.dark-mode .busca-bar input,
    body.dark-mode .busca-bar select {
        background: #181c2a;
        color: #eaf2ff;
        border: 1.5px solid #5b8cff44;
    }
    body.dark-mode .busca-bar input:focus,
    body.dark-mode .busca-bar select:focus {
        background: #23263a;
        color: #fff;
        border: 1.5px solid #5b8cff;
    }
    body.dark-mode .busca-bar button {
        background: #2e3192;
        color: #fff;
        box-shadow: 0 2px 8px #2e319244;
    }
    body.dark-mode .busca-bar button:hover {
        background: #5b8cff;
        color: #fff;
    }
    .form-aviso {
        margin-bottom: 32px;
    }
    .avisos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
        animation: fadeIn 0.6s ease-out 0.3s both;
    }
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    .aviso-card {
        border: 2px solid #e0eaff;
        border-radius: 18px;
        padding: 24px;
        background: linear-gradient(135deg, #ffffff 0%, #f7faff 100%);
        box-shadow: 0 4px 16px rgba(91, 140, 255, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        min-height: 140px;
        position: relative;
    }
    .aviso-card:hover {
        box-shadow: 0 8px 32px rgba(91, 140, 255, 0.18);
        border-color: #5b8cff;
        transform: translateY(-4px);
    }
    .aviso-autor {
        font-weight: 700;
        color: #2e3192;
        font-size: 1.1rem;
        letter-spacing: 0.02em;
    }
    .aviso-data {
        color: #5b8cff;
        font-size: 13px;
        margin-left: 10px;
        font-weight: 500;
    }
    .aviso-conteudo {
        margin: 16px 0 0 0;
        font-size: 15px;
        color: #2e3192;
        white-space: pre-line;
        flex: 1;
        line-height: 1.6;
        font-weight: 400;
        letter-spacing: 0.01em;
    }
    .aviso-acoes {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #e8ecff;
        display: flex;
        gap: 16px;
    }
    .aviso-acoes a {
        color: #5b8cff;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        padding: 6px 12px;
        border-radius: 8px;
    }
    .aviso-acoes a:last-child {
        color: #ef4444;
    }
    .aviso-acoes a:hover {
        background: #eaf2ff;
        color: #2e3192;
        transform: translateX(2px);
    }
    .aviso-acoes a:last-child:hover {
        background: #fee;
        color: #dc2626;
    }
    .msg-sucesso {
        background: linear-gradient(135deg, #d1f4e0 0%, #e8f9ee 100%);
        color: #0d7936;
        border: 2px solid #4caf50;
        border-radius: 14px;
        padding: 18px 24px;
        margin-bottom: 24px;
        font-weight: 600;
        font-size: 15px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .msg-erro {
        background: linear-gradient(135deg, #ffdddd 0%, #ffeded 100%);
        color: #a50000;
        border: 2px solid #ef4444;
        border-radius: 14px;
        padding: 18px 24px;
        margin-bottom: 24px;
        font-weight: 600;
        font-size: 15px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .form-aviso textarea {
        width: 100%;
        border-radius: 12px;
        border: 2px solid #d0e0ff;
        padding: 16px;
        font-size: 15px;
        margin-bottom: 16px;
        resize: vertical;
        background: linear-gradient(135deg, #ffffff 0%, #f7faff 100%);
        color: #2e3192;
        font-weight: 400;
        line-height: 1.6;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        min-height: 100px;
    }
    .form-aviso button {
        background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 14px 32px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 16px rgba(91, 140, 255, 0.3);
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .form-aviso textarea:focus {
        outline: none;
        border-color: #5b8cff;
        box-shadow: 0 4px 16px rgba(91, 140, 255, 0.15);
        transform: translateY(-2px);
    }
    .form-aviso button:hover {
        background: linear-gradient(135deg, #2e3192 0%, #5b8cff 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(91, 140, 255, 0.4);
    }
    @media (max-width: 700px) {
        .header-content, .avisos-main { padding-left: 6px; padding-right: 6px; }
        .avisos-main { border-radius: 12px; padding-top: 10px; }
        .header-content { padding: 2px 2px 2px 2px; }
        .busca-bar { padding: 6px 4px; }
    }
    /* Tema escuro */
    body.dark-mode { background: #181c2a; }
    body.dark-mode .header-avisos {
        background: linear-gradient(135deg, #0d1147 0%, #1a1f5a 100%);
        border-bottom: 0;
        box-shadow: 0 4px 24px rgba(13, 17, 71, 0.6);
    }
    body.dark-mode .header-title { color: #8ab4ff; text-shadow: 0 2px 8px #2e319244; }
    body.dark-mode .header-desc { color: #eaf2ff; text-shadow: 0 1px 4px #2e319244; }
    body.dark-mode .avisos-main { background: #23263a; box-shadow: 0 4px 32px #2e319244; }
    body.dark-mode .aviso-card { 
        background: linear-gradient(135deg, #23263a 0%, #2e3192 100%); 
        border-color: #5b8cff66; 
        color: #eaf2ff; 
        box-shadow: 0 4px 16px rgba(46, 49, 146, 0.3);
    }
    body.dark-mode .aviso-card:hover {
        box-shadow: 0 8px 32px rgba(91, 140, 255, 0.4);
        border-color: #8ab4ff;
    }
    body.dark-mode .aviso-autor { color: #8ab4ff; }
    body.dark-mode .aviso-data { color: #5b8cff; }
    body.dark-mode .aviso-conteudo { color: #eaf2ff; }
    body.dark-mode .aviso-acoes { border-top-color: #5b8cff22; }
    body.dark-mode .aviso-acoes a { color: #8ab4ff; }
    body.dark-mode .aviso-acoes a:hover { background: #2e3192; color: #eaf2ff; }
    body.dark-mode .aviso-acoes a:last-child { color: #ff6b6b; }
    body.dark-mode .aviso-acoes a:last-child:hover { background: #3a1f1f; color: #ff8787; }
    body.dark-mode .form-aviso textarea { 
        background: linear-gradient(135deg, #181c2a 0%, #23263a 100%); 
        color: #eaf2ff; 
        border-color: #5b8cff44; 
    }
    body.dark-mode .form-aviso textarea:focus {
        border-color: #8ab4ff;
        box-shadow: 0 4px 16px rgba(138, 180, 255, 0.2);
    }
    body.dark-mode .msg-sucesso { 
        background: linear-gradient(135deg, #1a2e1a 0%, #23392a 100%); 
        color: #8ab4ff; 
        border-color: #4caf50; 
    }
    body.dark-mode .msg-erro { 
        background: linear-gradient(135deg, #3a1f1f 0%, #4a2626 100%); 
        color: #ff8787; 
        border-color: #ef4444; 
    }
    </style>
</head>
<body>
    <div class="header-avisos">
        <div class="header-content">
            <div>
                <div class="header-title">Estante de Avisos</div>
                <div class="header-desc">Veja e compartilhe comunicados importantes da instituição.</div>
            </div>
            <a href="inicial.php" style="text-decoration:none;display:flex;align-items:center;gap:10px;font-size:1.25rem;color:#fff;font-weight:700;background:rgba(255,255,255,0.15);padding:10px 20px;border-radius:12px;transition:all 0.3s ease;backdrop-filter:blur(10px);border:2px solid rgba(255,255,255,0.2);" onmouseover="this.style.background='rgba(255,255,255,0.25)';this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.15)';this.style.transform='translateY(0)';this.style.boxShadow='none';">
                <span style="font-size:1.6rem;line-height:1;vertical-align:middle;">&#8592;</span>
                <span style="font-size:1.05rem;letter-spacing:0.02em;">Voltar</span>
            </a>
        </div>
    </div>
    <main class="avisos-main">
        <?php if (isset($msg)) echo $msg; ?>
        <div class="busca-bar">
            <form id="formBuscaTag" method="get" style="display:flex;gap:12px;flex:1;align-items:center;">
                <input type="text" name="busca" id="buscaAviso" placeholder="Buscar aviso..." value="<?php echo esc($busca); ?>" style="flex:1;" />
                <select name="tag" id="buscaTag" style="padding:8px 12px;border-radius:8px;border:1px solid #e0eaff;font-size:1rem;background:#fff;color:#222;">
                    <option value="">Filtrar por tag</option>
                    <?php
                    // Buscar todas as tags únicas dos avisos
                    $tagsUnicas = [];
                    $tagsQuery = $pdo->query('SELECT tags FROM avisos');
                    foreach ($tagsQuery as $rowTag) {
                        $tagsArr = array_filter(array_map('trim', explode(',', $rowTag['tags'])));
                        foreach ($tagsArr as $tagOpt) {
                            if ($tagOpt !== '' && !in_array($tagOpt, $tagsUnicas)) {
                                $tagsUnicas[] = $tagOpt;
                            }
                        }
                    }
                    sort($tagsUnicas, SORT_NATURAL | SORT_FLAG_CASE);
                    foreach ($tagsUnicas as $tagOpt) {
                        $selected = ($tagOpt === $tag) ? 'selected' : '';
                        echo '<option value="'.esc($tagOpt).'" '.($selected ? 'selected' : '').'>'.esc($tagOpt).'</option>';
                    }
                    ?>
                </select>
                <button type="submit" style="background:#5b8cff;color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:1.05rem;font-weight:600;cursor:pointer;box-shadow:0 2px 8px #5b8cff22;">Buscar</button>
                <?php if ($classe === 1 || $classe === 2): ?>
                <button id="abrirPopupAviso" type="button" title="Novo aviso" style="background:#0057ff;color:#fff;border-radius:50%;width:44px;height:44px;border:none;font-size:2rem;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px #5b8cff22;cursor:pointer;margin-left:8px;">+</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Popup de criação de aviso -->
        <div id="popupAvisoOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:10000;background:rgba(44,92,255,0.13);backdrop-filter:blur(2px);align-items:center;justify-content:center;animation:fadeInPopupBg 0.5s;">
            <div id="popupAvisoCard" style="background:#fff;padding:32px 24px 24px 24px;border-radius:22px;box-shadow:0 8px 32px rgba(44,92,255,0.18);min-width:280px;display:flex;flex-direction:column;align-items:center;transform:scale(0.85);opacity:0;animation:popupScaleIn 0.5s cubic-bezier(.4,1.4,.6,1) forwards;">
                <h3 style="margin-bottom:18px;color:#2e3192;font-size:1.35rem;font-weight:700;letter-spacing:0.01em;animation:fadeInPopupTitle 0.7s 0.2s both;">Novo Aviso</h3>
                <form method="post" class="form-aviso" enctype="multipart/form-data" style="width:100%;max-width:420px;">
                    <textarea name="conteudo" rows="2" placeholder="Escreva um novo aviso..." required style="width:100%;margin-bottom:10px;"></textarea>
                    <div style="margin:8px 0 8px 0;display:flex;flex-direction:column;gap:8px;align-items:flex-start;">
                        <label style="font-size:0.98rem;color:#2e3192;font-weight:500;">Tags (separe por vírgula):</label>
                        <input type="text" name="tags" placeholder="Ex: matemática, 3ºJ, importante" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #e0eaff;font-size:1rem;">
                    </div>
                    <div style="margin:8px 0 8px 0;display:flex;gap:12px;align-items:center;">
                        <label style="font-size:0.98rem;color:#2e3192;font-weight:500;">Data inicial:</label>
                        <input type="date" name="data_inicial" style="padding:6px 10px;border-radius:7px;border:1px solid #e0eaff;font-size:1rem;">
                        <label style="font-size:0.98rem;color:#2e3192;font-weight:500;">Data limite:</label>
                        <input type="date" name="data_limite" style="padding:6px 10px;border-radius:7px;border:1px solid #e0eaff;font-size:1rem;">
                    </div>
                    <input type="file" name="anexo" style="margin-bottom:8px;">
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <button type="button" id="fecharPopupAviso" style="background:#eee;color:#2e3192;border:none;padding:10px 28px;border-radius:8px;cursor:pointer;font-size:1rem;font-weight:500;transition:background 0.18s, color 0.18s;">Cancelar</button>
                        <button type="submit" style="background:linear-gradient(135deg,#5b8cff 0%,#2e3192 100%);color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1rem;font-weight:500;cursor:pointer;">Postar</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="avisos-grid" id="avisosGrid">
        <?php
    $sql = "SELECT id, usuario, conteudo, data, anexo, tags, data_limite FROM avisos $whereSQL ORDER BY data DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit', $avisosPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
        $avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $qtdAvisos = count($avisos);
        if ($qtdAvisos === 0) {
            echo '<div class="aviso-card" style="display:flex;align-items:center;justify-content:center;min-height:220px;opacity:0.92;width:100%;grid-column:1/-1;">'
                .'<div style="text-align:center;width:100%;"><img src="../assets/imagens/LOGO.png" alt="Sem avisos" style="width:54px;height:54px;opacity:0.25;margin-bottom:10px;"><div style="font-size:1.18rem;color:#888;font-weight:500;">Nenhum aviso encontrado</div><div style="font-size:0.98rem;color:#bbb;">Quando houver avisos, eles aparecerão aqui.</div></div>'
            .'</div>';
        } else {
            foreach ($avisos as $row) {
                $pode_editar = ($classe === 1 || ($classe === 2 && $row['usuario'] === $usuario));
                $tags = array_filter(array_map('trim', explode(',', $row['tags'] ?? '')));
                echo '<div class="aviso-card" data-conteudo="' . esc(strtolower($row['conteudo'].' '.implode(' ',$tags))) . '">';
                echo '<span class="aviso-autor">' . esc($row['usuario']) . '</span>';
                echo '<span class="aviso-data">(' . esc($row['data']) . ')</span>';
                if (!empty($row['data_limite']) && $row['data_limite'] !== '0000-00-00' && $row['data_limite'] !== '1970-01-01') {
                    $dataLimiteFormatada = date('d/m/Y', strtotime($row['data_limite']));
                    if ($dataLimiteFormatada !== '30/11/-0001' && $dataLimiteFormatada !== '01/01/1970') {
                        echo '<div style="margin:4px 0 0 0;font-size:0.97rem;color:#5b8cff;">Limite: ' . esc($dataLimiteFormatada) . '</div>';
                    }
                }
                if (!empty($tags)) {
                    echo '<div style="margin:6px 0 0 0;">';
                    foreach ($tags as $tag) {
                        echo '<span style="display:inline-block;background:#eaf2ff;color:#2e3192;font-size:0.93rem;padding:2px 10px;border-radius:7px;margin-right:6px;margin-bottom:2px;">#'.esc($tag).'</span>';
                    }
                    echo '</div>';
                }
                if (!empty($row['anexo'])) {
                    $anexosrc = esc($row['anexo']);
                    $nomeArquivo = basename($anexosrc);
                    echo '<div style="margin:10px 0 0 0;"><a href="download.php?file=' . urlencode($anexosrc) . '" style="color:#0057ff;font-weight:500;text-decoration:underline;">📎 Baixar anexo: '.esc($nomeArquivo).'</a></div>';
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
            // Removido preenchimento extra de cards
        }
        ?>
        </div>
        <!-- Paginação -->
        <div style="display:flex;justify-content:center;align-items:center;margin:32px 0 0 0;gap:18px;">
            <?php if ($paginaAtual > 1): ?>
                <a href="avisos.php?pagina=<?php echo $paginaAtual-1; ?>" style="font-size:1.5rem;color:#0057ff;text-decoration:none;padding:6px 14px;border-radius:50%;background:#eaf2ff;">&#8592;</a>
            <?php endif; ?>
            <span style="font-size:1.08rem;color:#2e3192;font-weight:500;">Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?></span>
            <?php if ($paginaAtual < $totalPaginas): ?>
                <a href="avisos.php?pagina=<?php echo $paginaAtual+1; ?>" style="font-size:1.5rem;color:#0057ff;text-decoration:none;padding:6px 14px;border-radius:50%;background:#eaf2ff;">&#8594;</a>
            <?php endif; ?>
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
    // Busca e filtro agora são feitos no backend

    // Popup de criação de aviso
    const abrirPopupAviso = document.getElementById('abrirPopupAviso');
    const popupAvisoOverlay = document.getElementById('popupAvisoOverlay');
    const popupAvisoCard = document.getElementById('popupAvisoCard');
    const fecharPopupAviso = document.getElementById('fecharPopupAviso');
    if (abrirPopupAviso && popupAvisoOverlay && popupAvisoCard && fecharPopupAviso) {
        abrirPopupAviso.onclick = function() {
            popupAvisoOverlay.style.display = 'flex';
            // animação
            setTimeout(() => { popupAvisoCard.style.opacity = 1; popupAvisoCard.style.transform = 'scale(1)'; }, 10);
        };
        fecharPopupAviso.onclick = function() {
            popupAvisoCard.style.opacity = 0;
            popupAvisoCard.style.transform = 'scale(0.85)';
            setTimeout(() => { popupAvisoOverlay.style.display = 'none'; }, 320);
        };
        // Fecha ao clicar fora do card
        popupAvisoOverlay.onclick = function(e) {
            if (e.target === popupAvisoOverlay) fecharPopupAviso.onclick();
        };
    }
    </script>
</body>
</html>