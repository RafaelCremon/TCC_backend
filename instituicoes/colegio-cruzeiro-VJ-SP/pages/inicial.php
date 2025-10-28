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

// Carrega atalhos do banco se não estiverem na sessão
if (isset($_SESSION['usuario_id']) && !isset($_SESSION['atalhos_usuario'])) {
    require_once '../../../includes/db.php';
    $stmt = $pdo->prepare("SELECT p.atalhos FROM plataforma p WHERE p.usuario_id = :id");
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['atalhos'] !== null) {
        $_SESSION['atalhos_usuario'] = json_decode($row['atalhos'], true);
    } else {
        // Se não existir registro na plataforma, cria um para o usuário
        $insert = $pdo->prepare("INSERT IGNORE INTO plataforma (usuario_id) VALUES (:id)");
        $insert->bindParam(':id', $_SESSION['usuario_id']);
        $insert->execute();
        $_SESSION['atalhos_usuario'] = [];
    }
}

// Buscar dados completos do usuário e da instituição para o miniperfil
$usuarioDados = null;
if (isset($_SESSION['usuario_id'])) {
  require_once '../../../includes/db.php';
  $usuario_id = $_SESSION['usuario_id'];
  $stmt = $pdo->prepare('SELECT u.*, i.nome as nome_instituicao FROM usuarios u JOIN instituicoes i ON u.instituicao_id = i.id WHERE u.id = :id');
  $stmt->bindParam(':id', $usuario_id);
  $stmt->execute();
  $usuarioDados = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para buscar foto de perfil do usuário logado
function getFotoPerfil($pdo, $usuario_id) {
    $stmt = $pdo->prepare('SELECT foto_perfil FROM plataforma WHERE usuario_id = :id');
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    $foto = $stmt->fetchColumn();
    if ($foto && file_exists($foto)) {
        // Corrige caminho relativo para uso no src
        $foto = str_replace('..', '..', $foto);
        return $foto;
    }
    return '../assets/imagens/spongebob.png';
}

// No início do arquivo, após session_start e require db.php:
$fotoPerfilLogado = getFotoPerfil($pdo, $_SESSION['usuario_id']);

// 1. Defina as opções de atalhos disponíveis

function get_opcoes_atalhos_padrao() {
  $classe = isset($_SESSION['classe']) ? (int)$_SESSION['classe'] : 0;
  $opcoes = [
    [
      'id' => 'academico',
      'nome' => 'Avisos',
      'icone' => '<svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M18 16v-5a6 6 0 10-12 0v5a2 2 0 01-2 2h16a2 2 0 01-2-2zm-6 5a2 2 0 002-2h-4a2 2 0 002 2z"/></svg>',
      'link' => 'avisos.php'
    ],
    [
      'id' => 'minimapa',
      'nome' => 'Mini Mapa',
      'icone' => '<svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>',
      'link' => '#',
    ],
    [
      'id' => 'lanchonetes',
      'nome' => 'Lanchonetes',
      'icone' => '<svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M16 4h-2c-1.1 0-2 .9-2 2v2H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-2V4c0-1.1-.9-2-2-2zm-4 0h2v2h-2V4zM6 8h14v9H6V8z"/></svg>',
      'link' => 'lanchonetes.html',
    ],
  ];
  // Atalho "Usuários" (classe 1/2) - aceita id antigo 'financeiro' e novo 'usuarios'
  if ($classe === 1 || $classe === 2) {
    $opcoes[] = [
      'id' => 'usuarios',
      'nome' => 'Usuários',
      'icone' => '<svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5s-3 1.34-3 3 1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05C15.64 13.36 17 14.28 17 15.5V19h7v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
      'link' => '#',
    ];
  }
  // Preferências
  $opcoes[] = [
    'id' => 'preferencias',
    'nome' => 'Preferências',
    'icone' => '<svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c.04.32.07.65.07.98s-.03.66-.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>',
    'link' => '#',
  ];
  return $opcoes;
}

// Corrige atalhos antigos: se houver 'financeiro', troca para 'usuarios' para classe 1/2
$atalhos_usuario = isset($_SESSION['atalhos_usuario']) ? $_SESSION['atalhos_usuario'] : [];
if (isset($_SESSION['classe']) && (int)$_SESSION['classe'] === 1 || (int)$_SESSION['classe'] === 2) {
  foreach ($atalhos_usuario as &$id) {
    if ($id === 'financeiro') {
    }
  }
  unset($id);
  $_SESSION['atalhos_usuario'] = $atalhos_usuario;
}
$opcoes = get_opcoes_atalhos_padrao();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página Inicial</title>
  <link rel="stylesheet" href="../css/inicial.css?v=<?php echo time(); ?>_no_border_fix_<?php echo rand(10000,99999); ?>">
  <link rel="stylesheet" href="../css/calendario.css?v=<?php echo time(); ?>_force_stretch_<?php echo rand(1000,9999); ?>">
  <style>
  /* Animação mini perfil */
  #miniPerfilPopup {
    opacity: 0;
    transform: scale(0.95) translateY(-16px);
    pointer-events: none;
    transition: opacity 0.32s cubic-bezier(.4,1.4,.6,1), transform 0.32s cubic-bezier(.4,1.4,.6,1);
    display: none;
  }
  #miniPerfilPopup.show {
    opacity: 1;
    transform: scale(1) translateY(0);
    pointer-events: auto;
    display: block;
  }
    /* Sidebar hover */
    .sidebar-item {
      transition: background 0.22s, color 0.22s, box-shadow 0.22s, transform 0.18s;
      border-radius: 10px;
    }
    .sidebar-item:hover, .sidebar-item:focus {
      background: linear-gradient(90deg, #eaf2ff 0%, #dbe7ff 100%);
      color: #2e3192;
      box-shadow: 0 2px 12px rgba(44,92,255,0.10);
      transform: scale(1.04);
    }
    .sidebar-item svg {
      transition: filter 0.22s, transform 0.18s;
    }
    .sidebar-item:hover svg, .sidebar-item:focus svg {
      filter: drop-shadow(0 2px 8px #5b8cff33);
      transform: scale(1.12);
    }

    /* Atalhos hover */
    .shortcut-btn, .add-shortcut-btn {
      transition: background 0.22s, color 0.22s, box-shadow 0.22s, transform 0.18s;
      border-radius: 10px;
    }
    .shortcut-btn:hover, .shortcut-btn:focus {
      background: linear-gradient(90deg, #eaf2ff 0%, #dbe7ff 100%);
      color: #2e3192;
      box-shadow: 0 2px 12px rgba(44,92,255,0.10);
      transform: scale(1.06);
    }
    .add-shortcut-btn:hover, .add-shortcut-btn:focus {
      background: #eaf2ff;
      color: #0057ff;
      box-shadow: 0 2px 8px #5b8cff22;
      transform: scale(1.13);
    }

    /* Botões principais */
    button, .btn-create, .btn-cancel, .btn-salvar {
      transition: background 0.22s, color 0.22s, box-shadow 0.22s, transform 0.18s;
    }
    button:hover, button:focus, .btn-create:hover, .btn-cancel:hover, .btn-salvar:hover {
      filter: brightness(1.08);
      box-shadow: 0 2px 12px #5b8cff22;
      transform: scale(1.04);
    }

    /* Cartões e cards */
    .shortcuts-card, .welcome-card {
      transition: box-shadow 0.22s, transform 0.18s;
    }
    .shortcuts-card:hover, .welcome-card:hover {
      box-shadow: 0 4px 24px #5b8cff22;
      transform: scale(1.01);
    }
  </style>
</head>
<body>
  <div class="apple-transition-overlay" id="appleOverlay"></div>
  <div id="welcomeAnimation" style="position:fixed;top:0;left:0;width:100vw;height:100vh;display:flex;align-items:center;justify-content:center;z-index:10000;background:rgba(44,92,255,0.18);backdrop-filter:blur(2px);transition:opacity 0.7s;opacity:1;">
    <h2 id="welcomeText" style="margin:0;font-size:2.6rem;font-weight:600;letter-spacing:0.03em;background:linear-gradient(90deg,#5b8cff 0%,#2e3192 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-fill-color:transparent;opacity:0;white-space:nowrap;overflow:hidden;border-right:2px solid #5b8cff;"></h2>
  </div>
  <header class="top-bar">
    <div class="brand">
      <button id="toggleSidebarBtn" class="menu-button" aria-label="Abrir menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
      </button>
  <img src="..\assets\imagens\LOGO.png" alt="Logo Quantum" class="logo"  style="width:32px;height:32px;border-radius:50%;margin-right:10px;vertical-align:middle;box-shadow:0 2px 8px rgba(44,92,255,0.10);">
      <h1>Quantum Edu.</h1>
  <h2 class="welcome-header" id="headerWelcome" style="font-size:16px; font-weight:400; margin:4px 0 0 0; color:#5b8cff; opacity:0; transition:opacity 0.5s;"></h2>
    </div>
    <div class="admin-profile" id="adminProfile" style="position: relative; cursor: pointer;">
      <img src="<?php echo htmlspecialchars($fotoPerfilLogado); ?>" alt="Foto de Perfil" class="admin-avatar">
      <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
    </div>
  </header>

  <div class="welcome-card">
    <img src="<?php echo htmlspecialchars($fotoPerfilLogado); ?>" alt="Avatar Admin" class="welcome-avatar">
    <div>
      <h2 class="welcome-title" id="cardWelcome" style="font-size:1.3rem;font-weight:600;margin:0 0 4px 0;color:#5b8cff;opacity:0;transition:opacity 0.5s;"></h2>
      <p class="welcome-desc">Acesse e gerencie todos os recursos da plataforma Quantum Admin.</p>
    </div> 
  </div>

  <aside class="sidebar" id="sidebar">

    <a href="avisos.php" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M18 16v-5a6 6 0 10-12 0v5a2 2 0 01-2 2h16a2 2 0 01-2-2zm-6 5a2 2 0 002-2h-4a2 2 0 002 2z"/></svg>
      <span>Avisos</span>
    </a>

    <div class="sidebar-item-container">
      <div class="sidebar-item" id="mapButton">
        <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>
        <span>Mini Mapa</span>
      </div>
    </div>

    <a href="lanchonetes.html" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M16 4h-2c-1.1 0-2 .9-2 2v2H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-2V4c0-1.1-.9-2-2-2zm-4 0h2v2h-2V4zM6 8h14v9H6V8z"/></svg>
      <span>Lanchonetes</span>
    </a>


  <?php if (isset($_SESSION['classe']) && ((int)$_SESSION['classe'] === 1 || (int)$_SESSION['classe'] === 2)): ?>
  <a href="usuarios.php" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5s-3 1.34-3 3 1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05C15.64 13.36 17 14.28 17 15.5V19h7v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
      <span>Usuários</span>
    </a>
    <?php endif; ?>

    <a href="#" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c.04.32.07.65.07.98s-.03.66-.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h-4c-.25 0-.46-.18-.49-.42l-.38-2.65c-.61-.25-1.17-.59-1.69-.98l-2.49 1c-.23.09-.49 0-.61-.22l-2-3.46c-.12-.22-.07-.49.12-.64l2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
      <span>Preferências</span>
    </a>
  </aside>

  <main class="content">
    <div class="main-grid">
      <div class="shortcuts-section">
        <div class="shortcuts-card" id="shortcutsCard">
          <div style="display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0;">Atalhos</h3>
            <div style="display: flex; gap: 8px; align-items: center;">
              <button 
                id="toggleCalendarBtn"
                title="Mostrar/Ocultar Calendário" 
                style="background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%); color: white; border: none; cursor: pointer; padding: 6px 8px; border-radius: 8px; display: none;"
                aria-label="Mostrar/Ocultar Calendário"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
              </button>
              <a 
                id="editShortcutsBtn"
                href="atalhos.php"
                title="Editar atalhos" 
                style="background: none; border: none; cursor: pointer; padding: 4px; display: inline-flex; align-items: center; justify-content: center;"
                aria-label="Editar atalhos"
              >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0057ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 20h9"></path>
                  <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                </svg>
              </a>
            </div>
          </div>
          <div class="shortcut-buttons">
            <?php
    // Mostra os atalhos do usuário
    $totalSlots = 4;
    $count = 0;
    foreach ($atalhos_usuario as $id) {
      foreach ($opcoes as $op) {
        if ($op['id'] === $id) {
          $extra = $op['id'] === 'minimapa' ? 'id="shortcutMinimapa"' : '';
          $link = $op['id'] === 'usuarios' ? 'usuarios.php' : $op['link'];
          echo '<a href="'.$link.'" class="shortcut-btn" '.$extra.'>'.$op['icone'].'<span>'.$op['nome'].'</span></a>';
          $count++;
        }
      }
    }
    // Preenche os slots restantes com "+"
    for ($i = $count; $i < $totalSlots; $i++) {
      echo '<button class="add-shortcut-btn" onclick="window.location.href=\'atalhos.php\'">+</button>';
    }
  ?>
          </div>
        </div>

        <div class="opcoes-grid" id="opcoes">
          <!-- Cada opção de atalho deve ter uma classe .opcao-atalho e um id único, ex: id="opcao-1" -->
        </div>
      </div>

      <div class="calendar-sidebar">
  <div class="calendar-section calendar-fade-in">
          <div class="calendar-container">
            <div style="flex:1; display:flex; flex-direction:column;">
              <div class="calendar-header">
                <h3 class="calendar-title">📅 Calendário</h3>
                <div class="calendar-nav">
                  <button class="calendar-nav-btn" id="prevMonth" title="Mês anterior">‹</button>
                  <div class="calendar-month" id="currentMonth">Carregando...</div>
                  <button class="calendar-nav-btn" id="nextMonth" title="Próximo mês">›</button>
                </div>
              </div>
              <div class="calendar-widget" style="flex:1;">
                <div class="calendar-grid" id="calendarGrid">
                  <!-- O calendário será gerado aqui pelo JavaScript -->
                </div>
              </div>
              <div class="calendar-widget events-widget" style="min-width:320px; max-width:420px; border-radius:0 0 18px 18px; margin-left:0; margin-top:12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding: 6px 0;">
                  <h4 style="margin: 0; color: #6fb4d1ff; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 6px; text-shadow: 0 2px 4px rgba(0, 224, 255, 0.3);">
                    🎯 Eventos
                  </h4>
                  <?php if (isset($_SESSION['classe']) && (int)$_SESSION['classe'] === 1 || (int)$_SESSION['classe'] === 2): ?>
                  <button class="add-event-btn" onclick="addNewEvent()" title="Adicionar novo evento (Ctrl+N)" style="width: 32px; height: 32px; font-size: 20px; font-weight: 800;">
                    <span>+</span>
                  </button>
                  <?php endif; ?>
                </div>
                <div class="events-panel" id="eventsPanel">
                  <div class="no-events">Carregando eventos...</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal para Adicionar Evento -->
  <div class="event-modal-overlay" id="eventModalOverlay">
    <div class="event-modal">
      <div class="event-modal-header">
        <h3>📅 Novo Evento</h3>
        <button class="modal-close-btn" id="modalCloseBtn">&times;</button>
      </div>
      
      <div class="event-modal-body">
        <form id="eventForm" class="event-form">
          <div class="form-group">
            <label for="eventTitle">Título do Evento *</label>
            <input type="text" id="eventTitle" name="title" placeholder="Digite o título do evento..." required>
          </div>
          
          <div class="form-group">
            <label for="eventDescription">Descrição</label>
            <textarea id="eventDescription" name="description" placeholder="Descrição opcional do evento..." rows="3"></textarea>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="eventDate">Data</label>
              <input type="date" id="eventDate" name="date" required>
            </div>
            
            <div class="form-group">
              <label for="eventTime">Horário</label>
              <input type="time" id="eventTime" name="time" value="12:00">
            </div>
          </div>
          
          <div class="form-group">
            <label for="eventType">Tipo de Evento</label>
            <select id="eventType" name="type">
              <option value="meeting">📋 Reunião</option>
              <option value="class">📚 Aula</option>
              <option value="exam">📝 Prova</option>
              <option value="event">🎉 Evento</option>
              <option value="holiday">🏖️ Feriado</option>
              <option value="other" selected>📌 Outro</option>
            </select>
          </div>
        </form>
      </div>
      
      <div class="event-modal-footer">
        <button type="button" class="btn-cancel" id="cancelEventBtn">Cancelar</button>
        <button type="submit" form="eventForm" class="btn-create" id="createEventBtn">✨ Criar Evento</button>
      </div>
    </div>
  </div>

  <div class="overlay" id="overlay"></div>
  
  <script src="../js/inicial.js"></script>
  <script src="../js/atalhos.js"></script>
  <script src="../js/calendario.js?v=<?php echo time(); ?>_all_events_<?php echo rand(1000,9999); ?>"></script>
  <script>
  // Dados da sessão do PHP para JavaScript
  const usuarioLogado = {
    id: <?php echo $_SESSION['usuario_id']; ?>,
    usuario: '<?php echo $_SESSION['usuario']; ?>',
    tipo: '<?php echo $_SESSION['tipo_usuario']; ?>'
  };

  // O calendário agora se inicializa automaticamente
  
  // === FUNCIONALIDADE DELETAR EVENTOS - DEBUG ===
  console.log('🚀 Iniciando script deletar eventos...');
  
  // LIMPAR EVENTOS DE EXEMPLO DO LOCALSTORAGE
  function limparEventosExemplo() {
    try {
      const events = JSON.parse(localStorage.getItem('calendarEvents') || '{}');
      let eventosRemovidos = 0;
      
      for (const dateKey in events) {
        const dayEvents = events[dateKey];
        for (let i = dayEvents.length - 1; i >= 0; i--) {
          const event = dayEvents[i];
          if (event.title === '📚 Reunião Pedagógica' || 
              event.title === '🎓 Apresentação TCC' ||
              event.title === 'Reunião Pedagógica' ||
              event.title === 'Apresentação TCC') {
            dayEvents.splice(i, 1);
            eventosRemovidos++;
          }
        }
        
        // Se não há mais eventos nesta data, remover a chave
        if (dayEvents.length === 0) {
          delete events[dateKey];
        }
      }
      
      if (eventosRemovidos > 0) {
        localStorage.setItem('calendarEvents', JSON.stringify(events));
        console.log(`🧹 ${eventosRemovidos} eventos de exemplo removidos do localStorage`);
      }
    } catch (error) {
      console.error('❌ Erro ao limpar eventos exemplo:', error);
    }
  }
  
  // Executar limpeza
  limparEventosExemplo();
  
  function adicionarBotoesDeletar() {
    console.log('🔍 Verificando calendário...');
    
    if (!window.calendar) {
      console.log('❌ window.calendar não existe');
      return false;
    }
    
    console.log('✅ window.calendar encontrado:', typeof window.calendar);
    
    if (!window.calendar.createEventElement) {
      console.log('❌ createEventElement não existe');
      return false;
    }
    
    console.log('✅ createEventElement encontrado');
    
    // Salvar original
    if (!window.originalCreateEventElement) {
      window.originalCreateEventElement = window.calendar.createEventElement.bind(window.calendar);
      console.log('💾 Função original salva');
    }
    
    // Nova função
    window.calendar.createEventElement = function(event) {
      console.log('🎯 createEventElement chamado para:', event.title);
      
      const eventDiv = document.createElement('div');
      eventDiv.className = 'event-item';
      eventDiv.style.position = 'relative';
      
      // Container flex
      const container = document.createElement('div');
      container.style.cssText = 'display: flex; justify-content: space-between; align-items: center; width: 100%;';
      
      // Lado esquerdo com informações
      const infoDiv = document.createElement('div');
      infoDiv.style.flex = '1';
      
      if (event.date) {
        const dateDiv = document.createElement('div');
        dateDiv.className = 'event-date';
        dateDiv.textContent = '📅 ' + event.date;
        infoDiv.appendChild(dateDiv);
      }
      
      const titleDiv = document.createElement('div');
      titleDiv.className = 'event-title';
      titleDiv.textContent = event.title || 'Sem título';
      infoDiv.appendChild(titleDiv);
      
      const timeDiv = document.createElement('div');
      timeDiv.className = 'event-time';
      timeDiv.textContent = event.time || 'Sem horário';
      infoDiv.appendChild(timeDiv);
      
      // BOTÃO DELETAR - DESIGN MELHORADO
      const deleteBtn = document.createElement('button');
      deleteBtn.innerHTML = '🗑️';
      deleteBtn.title = 'Deletar evento';
      deleteBtn.style.cssText = `
        background: linear-gradient(135deg, rgba(255, 107, 107, 0.9) 0%, rgba(220, 38, 38, 0.9) 100%) !important;
        color: white !important;
        border: none !important;
        padding: 4px 6px !important;
        border-radius: 5px !important;
        cursor: pointer !important;
        font-size: 14px !important;
        margin-left: 6px !important;
        min-width: 26px !important;
        height: 26px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3) !important;
        backdrop-filter: blur(10px) !important;
      `;
      
      deleteBtn.addEventListener('mouseenter', function() {
        this.style.background = 'linear-gradient(135deg, rgba(255, 107, 107, 1) 0%, rgba(220, 38, 38, 1) 100%) !important';
        this.style.transform = 'scale(1.1)';
        this.style.boxShadow = '0 4px 8px rgba(255, 107, 107, 0.5) !important';
      });
      
      deleteBtn.addEventListener('mouseleave', function() {
        this.style.background = 'linear-gradient(135deg, rgba(255, 107, 107, 0.9) 0%, rgba(220, 38, 38, 0.9) 100%) !important';
        this.style.transform = 'scale(1)';
        this.style.boxShadow = '0 2px 4px rgba(255, 107, 107, 0.3) !important';
      });
      
      deleteBtn.onclick = function(e) {
        e.stopPropagation();
        console.log('🗑️ Deletando evento:', event.title);
        
        if (confirm(`🗑️ Deletar evento "${event.title}"?\n\n⚠️ Esta ação não pode ser desfeita.`)) {
          console.log('✅ Confirmou deletar');
          
          for (const dateKey in window.calendar.events) {
            const dayEvents = window.calendar.events[dateKey];
            const idx = dayEvents.findIndex(e => e.title === event.title && e.time === event.time);
            if (idx !== -1) {
              dayEvents.splice(idx, 1);
              if (dayEvents.length === 0) delete window.calendar.events[dateKey];
              window.calendar.saveEvents();
              window.calendar.render();
              
              // Feedback visual
              console.log('✅ Evento deletado com sucesso');
              return;
            }
          }
        }
      };
      
      // Montar
      container.appendChild(infoDiv);
      container.appendChild(deleteBtn);
      eventDiv.appendChild(container);
      
      console.log('✅ Evento criado com botão DELETAR visível');
      return eventDiv;
    };
    
    // Re-renderizar
    window.calendar.render();
    console.log('🔄 Calendário re-renderizado');
    
    return true;
  }
  
  // Múltiplas tentativas
  let tentativa = 0;
  function tentar() {
    tentativa++;
    console.log('🔄 Tentativa', tentativa);
    
    if (adicionarBotoesDeletar()) {
      console.log('🎉 SUCESSO!');
    } else if (tentativa < 20) {
      setTimeout(tentar, 500);
    } else {
      console.log('❌ FALHOU após 20 tentativas');
    }
  }
  
  setTimeout(tentar, 1000);
  </script>
  <script>
  window.addEventListener('DOMContentLoaded', function() {
    // Apple-style card animation
    var card = document.getElementById('shortcutsCard');
    if(card) {
      card.classList.add('apple-animate-in');
      // Não remove a classe, assim só anima uma vez
    }
    // Welcome animation só na primeira vez
    var welcomeDiv = document.getElementById('welcomeAnimation');
    var welcomeText = document.getElementById('welcomeText');
    var cardWelcome = document.getElementById('cardWelcome');
    var text = 'Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!';
    var jaViuBoasVindas = localStorage.getItem('jaViuBoasVindas');
    function initAppFeatures() {
      // Inicializar sistema de atalhos
      inicializarSistemaAtalhos();
      
      // Aguardar calendário estar pronto
      console.log('Inicializando recursos da aplicação...');
      
      // Removido código antigo do menu de perfil para evitar conflito com miniPerfilPopup
    }
    if (welcomeDiv && welcomeText && cardWelcome) {
      if (!jaViuBoasVindas) {
        // Executa animação de boas-vindas
        welcomeText.textContent = '';
        let i = 0;
        let animationDone = false;
        setTimeout(function() {
          welcomeText.style.opacity = '1';
          typeWriter();
        }, 300);
        function typeWriter() {
          if (i <= text.length) {
            welcomeText.textContent = text.slice(0, i);
            i++;
            setTimeout(typeWriter, 60);
          } else {
            setTimeout(moveToCard, 700);
          }
        }
        function moveToCard() {
          animationDone = true;
          welcomeText.style.transition = 'all 0.7s cubic-bezier(.4,1.4,.6,1)';
          welcomeDiv.style.transition = 'opacity 0.7s';
          welcomeText.style.transform = 'translateY(-40px) scale(0.7)';
          welcomeText.style.opacity = '0';
          welcomeDiv.style.opacity = '0';
          setTimeout(function() {
            welcomeDiv.style.display = 'none';
            cardWelcome.textContent = text;
            cardWelcome.style.opacity = '1';
            localStorage.setItem('jaViuBoasVindas', 'true');
            initAppFeatures();
          }, 700);
        }
      } else {
        // Já viu, mostra direto no card
        if (welcomeDiv) welcomeDiv.style.display = 'none';
        cardWelcome.textContent = text;
        cardWelcome.style.opacity = '1';
        initAppFeatures();
      }
    } else if (cardWelcome) {
      // Fallback: sempre mostra o texto no card
      cardWelcome.textContent = text;
      cardWelcome.style.opacity = '1';
      initAppFeatures();
    } else {
      // Se não houver animação, inicializa recursos normalmente
      initAppFeatures();
    }
  });
  </script>
  <!-- Assistente Virtual Pop-up -->
<div id="assistente-container" style="position:fixed;bottom:32px;right:32px;z-index:9999;">
  <button id="abrirAssistente" style="background:#0057ff;color:#fff;border-radius:50%;width:56px;height:56px;border:none;font-size:28px;box-shadow:0 2px 8px rgba(44,92,255,0.10);cursor:pointer;">
    💬
  </button>
  <div id="assistenteChat" style="display:none;flex-direction:column;background:#fff;border-radius:18px;box-shadow:0 2px 12px rgba(44,92,255,0.13);width:340px;max-width:90vw;padding:18px 16px 12px 16px;position:absolute;bottom:70px;right:0;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
      <span style="font-weight:600;color:#0057ff;font-size:17px;">Assistente Virtual</span>
      <button id="fecharAssistente" style="background:none;border:none;font-size:20px;cursor:pointer;color:#0057ff;">×</button>
    </div>
    <div id="assistenteMensagens" style="height:180px;overflow-y:auto;font-size:15px;margin-bottom:8px;background:#f7faff;border-radius:8px;padding:8px;"></div>
    <div style="display:flex;gap:6px;">
      <input id="assistenteInput" type="text" placeholder="Digite sua pergunta..." style="flex:1;padding:7px 10px;border-radius:8px;border:1px solid #e0eaff;font-size:15px;">
      <button id="enviarAssistente" style="background:#0057ff;color:#fff;border:none;border-radius:8px;padding:7px 16px;font-weight:500;cursor:pointer;">Enviar</button>
    </div>
  </div>
</div>
<script>
const btn = document.getElementById('abrirAssistente');
const chat = document.getElementById('assistenteChat');
const fechar = document.getElementById('fecharAssistente');
btn.onclick = () => chat.style.display = 'flex';
fechar.onclick = () => chat.style.display = 'none';

async function obterRespostaIA(pergunta) {
  // Substitua 'SUA_CHAVE_OPENAI' pela sua chave da OpenAI
  const apiKey = 'SUA_CHAVE_OPENAI';
  const endpoint = 'https://api.openai.com/v1/chat/completions';
  const mensagens = [
    { role: "system", content: "Você é uma assistente virtual amigável e útil para um site educacional." },
    { role: "user", content: pergunta }
  ];
  try {
    const resposta = await fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer " + apiKey
      },
      body: JSON.stringify({
        model: "gpt-3.5-turbo",
        messages: mensagens,
        max_tokens: 100
      })
    });
    const dados = await resposta.json();
    return dados.choices && dados.choices[0].message.content ? dados.choices[0].message.content : "Desculpe, não consegui responder agora.";
  } catch (e) {
    return "Desculpe, houve um erro ao tentar responder.";
  }
}

document.getElementById('enviarAssistente').onclick = async function() {
  const input = document.getElementById('assistenteInput');
  const mensagens = document.getElementById('assistenteMensagens');
  const pergunta = input.value.trim();
  if (!pergunta) return;
  mensagens.innerHTML += `<div style="margin-bottom:6px;"><b>Você:</b> ${pergunta}</div>`;
  input.value = '';
  mensagens.innerHTML += `<div style="margin-bottom:6px;color:#888;">Assistente digitando...</div>`;
  mensagens.scrollTop = mensagens.scrollHeight;
  const resposta = await obterRespostaIA(pergunta);
  mensagens.innerHTML = mensagens.innerHTML.replace('Assistente digitando...', `<b>Assistente:</b> ${resposta}`);
  mensagens.scrollTop = mensagens.scrollHeight;
};
</script>
<!-- Adicione este bloco logo após o header -->
<div id="miniPerfilPopup" style="position:fixed;top:70px;right:32px;z-index:20000;background:#fff;border-radius:22px;box-shadow:0 2px 12px rgba(44,92,255,0.13);width:340px;max-width:90vw;padding:22px 20px 18px 20px;">
  <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
    <img src="<?php echo htmlspecialchars($fotoPerfilLogado); ?>" alt="Avatar Admin" style="width:64px;height:64px;border-radius:50%;box-shadow:0 2px 8px rgba(44,92,255,0.10);">
    <div>
      <span style="font-weight:600;font-size:18px;color:#0057ff;display:flex;align-items:center;gap:8px;position:relative;">
        <?php echo $usuarioDados ? htmlspecialchars($usuarioDados['usuario']) : 'Usuário'; ?>
        <a href="perfil.php" title="Editar perfil" style="position:absolute;right:-38px;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;background:#eaf2ff;border-radius:50%;text-decoration:none;box-shadow:0 2px 8px #0057ff22;transition:background 0.2s;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0057ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
        </a>
      </span>
      <?php if ($usuarioDados && !empty($usuarioDados['nome'])): ?>
        <div style="font-size:14px;color:#333;">Nome: <?php echo htmlspecialchars($usuarioDados['nome']); ?></div>
      <?php endif; ?>
      <?php if ($usuarioDados && !empty($usuarioDados['rgm'])): ?>
        <div style="font-size:14px;color:#333;">RGM: <?php echo htmlspecialchars($usuarioDados['rgm']); ?></div>
      <?php endif; ?>
      <?php if ($usuarioDados && !empty($usuarioDados['serie'])): ?>
        <div style="font-size:14px;color:#333;">Série: <?php echo htmlspecialchars($usuarioDados['serie']); ?></div>
      <?php endif; ?>
      <?php if ($usuarioDados && !empty($usuarioDados['nome_instituicao'])): ?>
        <div style="font-size:14px;color:#333;">Colégio: <?php echo htmlspecialchars($usuarioDados['nome_instituicao']); ?></div>
      <?php endif; ?>
    </div>
  </div>
  <div style="display:flex;gap:10px;justify-content:flex-end;">
    <button id="btnCarteirinha" style="background:#eaf2ff;border:none;border-radius:8px;padding:7px 16px;font-weight:500;color:#0057ff;cursor:pointer;display:flex;align-items:center;gap:7px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0057ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="6" width="18" height="12" rx="3"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      Carteirinha
    </button>
    <button id="btnTrocarTema" style="background:#eaf2ff;border:none;border-radius:8px;padding:7px 16px;font-weight:500;color:#0057ff;cursor:pointer;display:flex;align-items:center;gap:7px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0057ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 2v2"/>
        <path d="M12 20v2"/>
        <path d="M4.93 4.93l1.41 1.41"/>
        <path d="M17.66 17.66l1.41 1.41"/>
        <path d="M2 12h2"/>
        <path d="M20 12h2"/>
        <path d="M4.93 19.07l1.41-1.41"/>
        <path d="M17.66 6.34l1.41-1.41"/>
      </svg>
      Trocar tema
    </button>
  <form method="POST">
  <button id="btnLogout" type="submit" name="logout" 
    style="background:#ffd6d6;
         border:none;
         border-radius:8px;
         padding:7px 16px;
         font-weight:500;
         color:#d32f2f;
         cursor:pointer;">
    Sair
  </button>
  </form>
  </div>
</div>

<!-- Pop-up da carteirinha com alinhamento ajustado -->
<div id="carteirinhaPopup" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:10000;background:rgba(0,0,0,0.18);align-items:center;justify-content:center;">
  <div class="carteirinha-modelo">
    <div class="carteirinha-topo">
      <div class="carteirinha-logo">
        <svg width="40" height="40" viewBox="0 0 60 60">
          <polygon points="30,5 35,25 55,25 38,35 45,55 30,43 15,55 22,35 5,25 25,25" fill="#FFB800"/>
        </svg>
        <div class="carteirinha-colegio">
          <span style="font-size:1.05rem;">Colégio</span><br>
          <span style="font-size:1.35rem;font-weight:600;">Cruzeiro do Sul</span>
        </div>
      </div>
      <div class="carteirinha-quantum">
        <img src="../assets/imagens/LOGO.png" alt="Quantum Education" style="height:90px;width:auto;object-fit:contain;display:block;margin-top:2px;">
      </div>
    </div>
    <div class="carteirinha-conteudo">
      <div class="carteirinha-foto">
        <div class="foto-placeholder">
          <svg width="80" height="80" viewBox="0 0 80 80">
            <circle cx="40" cy="32" r="20" fill="#aaa"/>
            <rect x="18" y="54" width="44" height="20" rx="10" fill="#aaa"/>
          </svg>
        </div>
      </div>
      <div class="carteirinha-dados">
        <?php if ($usuarioDados && !empty($usuarioDados['nome'])): ?>
          <div class="carteirinha-nome" style="display:flex;align-items:center;gap:8px;">
            <?php echo htmlspecialchars($usuarioDados['nome']); ?>
            <a href="perfil.php" title="Editar perfil" style="margin-left:4px;display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#eaf2ff;border-radius:50%;text-decoration:none;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0057ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
            </a>
          </div>
        <?php endif; ?>
        <?php if ($usuarioDados && !empty($usuarioDados['rgm'])): ?>
          <div class="carteirinha-rgm">RGM: <?php echo htmlspecialchars($usuarioDados['rgm']); ?></div>
        <?php endif; ?>
        <?php if ($usuarioDados && !empty($usuarioDados['serie'])): ?>
          <div class="carteirinha-serie">SÉRIE: <?php echo htmlspecialchars($usuarioDados['serie']); ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <button id="fecharCarteirinha" style="margin-top:18px;background:#0057ff;color:#fff;border:none;border-radius:8px;padding:7px 18px;font-weight:500;cursor:pointer;position:absolute;top:40px;right:calc(50vw - 170px);">Fechar</button>
</div>

<script>
// Botão Carteirinha
document.getElementById('btnCarteirinha').onclick = function() {
  document.getElementById('carteirinhaPopup').style.display = 'flex';
};
// Fechar Carteirinha
document.getElementById('fecharCarteirinha').onclick = function() {
  document.getElementById('carteirinhaPopup').style.display = 'none';
};
// Fechar ao clicar fora
document.getElementById('carteirinhaPopup').onclick = function(e) {
  if (e.target === this) this.style.display = 'none';
};
</script>
<script>
// Mini perfil popup com animação e logs de depuração
document.addEventListener('DOMContentLoaded', function() {
  var adminProfile = document.getElementById('adminProfile');
  var miniPerfilPopup = document.getElementById('miniPerfilPopup');
  if (!adminProfile) {
    alert('Erro: avatar do perfil não encontrado!');
    console.error('[MiniPerfil] adminProfile não encontrado');
    return;
  }
  if (!miniPerfilPopup) {
    alert('Erro: popup do mini perfil não encontrado!');
    console.error('[MiniPerfil] miniPerfilPopup não encontrado');
    return;
  }
  function abrirMiniPerfilAnimado() {
    console.log('[MiniPerfil] Abrindo popup...');
    miniPerfilPopup.style.display = 'block';
    miniPerfilPopup.style.zIndex = '20000';
    // Força reflow para garantir a transição
    void miniPerfilPopup.offsetWidth;
    miniPerfilPopup.classList.add('show');
    // Fallback: se não aparecer, força visibilidade
    setTimeout(function() {
      if (getComputedStyle(miniPerfilPopup).opacity === '0') {
        miniPerfilPopup.style.opacity = '1';
        miniPerfilPopup.style.pointerEvents = 'auto';
        miniPerfilPopup.style.display = 'block';
        console.log('[MiniPerfil] Fallback: forçando visibilidade!');
      }
    }, 350);
  }
  function fecharMiniPerfilAnimado() {
    console.log('[MiniPerfil] Fechando popup...');
    miniPerfilPopup.classList.remove('show');
    // Após a transição, esconde o popup
    setTimeout(function() {
      if (!miniPerfilPopup.classList.contains('show')) {
        miniPerfilPopup.style.display = 'none';
      }
    }, 320);
  }
  adminProfile.onclick = function(e) {
    e.stopPropagation();
    console.log('[MiniPerfil] Clique no avatar!');
    if (!miniPerfilPopup.classList.contains('show')) {
      abrirMiniPerfilAnimado();
    } else {
      fecharMiniPerfilAnimado();
    }
  };
  document.addEventListener('click', function(e) {
    if (miniPerfilPopup.classList.contains('show')) {
      if (!miniPerfilPopup.contains(e.target) && e.target !== adminProfile) {
        fecharMiniPerfilAnimado();
      }
    }
  });
});

// Função para definir o tema
function setTheme(isDark) {
  if (isDark) {
    document.body.classList.add('dark-mode');
  } else {
    document.body.classList.remove('dark-mode');
  }
  // Logo permanece sempre a mesma, independente do tema
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

// Carregar tema salvo ao inicializar a página
loadSavedTheme();

// Botão Trocar tema
document.getElementById('btnTrocarTema').onclick = function() {
  const isDark = document.body.classList.contains('dark-mode');
  const newTheme = !isDark;
  setTheme(newTheme);
  saveTheme(newTheme);
  fecharMiniPerfilAnimado();
};
</script>


  <!-- Popup de seleção de bloco -->

  <div id="popupBlocos" class="popup-blocos-overlay">
    <div class="popup-blocos-card">
      <h3>Escolha um bloco:</h3>
      <div class="popup-blocos-btns">
        <button onclick="window.location.href='tour.html?bloco=A'" class="btn-bloco">Bloco A</button>
        <button onclick="window.location.href='tour.html?bloco=B'" class="btn-bloco">Bloco B</button>
        <button onclick="window.location.href='tour.html?bloco=C'" class="btn-bloco">Bloco C</button>
        <button onclick="window.location.href='tour.html?bloco=D'" class="btn-bloco">Bloco D</button>
        <button onclick="window.location.href='tour.html?bloco=INFANTIL'" class="btn-bloco">Infantil</button>
        <button onclick="window.location.href='tour.html?bloco=Biblioteca'" class="btn-bloco">Biblioteca</button>
      </div>
      <button id="btnCancelarPopupBlocos" class="btn-cancelar-popup">Cancelar</button>
    </div>
  </div>
  <style>
  .popup-blocos-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    z-index: 10000;
    background: rgba(44,92,255,0.13);
    backdrop-filter: blur(2px);
    align-items: center;
    justify-content: center;
    animation: fadeInPopupBg 0.5s;
  }
  .popup-blocos-overlay.show {
    display: flex;
    animation: fadeInPopupBg 0.5s;
  }
  @keyframes fadeInPopupBg {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  .popup-blocos-card {
    background: #fff;
    padding: 32px 24px 24px 24px;
    border-radius: 22px;
    box-shadow: 0 8px 32px rgba(44,92,255,0.18);
    min-width: 280px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transform: scale(0.85);
    opacity: 0;
    animation: popupScaleIn 0.5s cubic-bezier(.4,1.4,.6,1) forwards;
  }
  @keyframes popupScaleIn {
    from { transform: scale(0.85); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
  }
  .popup-blocos-card h3 {
    margin-bottom: 18px;
    color: #2e3192;
    font-size: 1.35rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    animation: fadeInPopupTitle 0.7s 0.2s both;
  }
  @keyframes fadeInPopupTitle {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .popup-blocos-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    justify-content: center;
    margin-bottom: 18px;
    animation: fadeInPopupBtns 0.7s 0.3s both;
  }
  @keyframes fadeInPopupBtns {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .btn-bloco {
    background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px 22px;
    font-size: 1.08rem;
    font-weight: 500;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(44,92,255,0.10);
    transition: background 0.2s, transform 0.18s, box-shadow 0.18s;
    outline: none;
  }
  .btn-bloco:hover, .btn-bloco:focus {
    background: linear-gradient(135deg, #2e3192 0%, #5b8cff 100%);
    transform: scale(1.08);
    box-shadow: 0 4px 16px rgba(44,92,255,0.18);
  }
  .btn-cancelar-popup {
    margin-top: 18px;
    background: #eee;
    color: #2e3192;
    border: none;
    padding: 10px 28px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: background 0.18s, color 0.18s;
  }
  .btn-cancelar-popup:hover {
    background: #dbe7ff;
    color: #0057ff;
  }

  /* Tema escuro */
  body.dark-mode .popup-blocos-overlay {
    background: rgba(20,24,40,0.82);
  }
  body.dark-mode .popup-blocos-card {
    background: #181c2a;
    box-shadow: 0 8px 32px rgba(20,24,40,0.38);
  }
  body.dark-mode .popup-blocos-card h3 {
    color: #8ab4ff;
  }
  body.dark-mode .btn-bloco {
    background: linear-gradient(135deg, #2e3192 0%, #5b8cff 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(20,24,40,0.18);
  }
  body.dark-mode .btn-bloco:hover, body.dark-mode .btn-bloco:focus {
    background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
    color: #fff;
    box-shadow: 0 4px 16px rgba(20,24,40,0.28);
  }
  body.dark-mode .btn-cancelar-popup {
    background: #23263a;
    color: #8ab4ff;
  }
  body.dark-mode .btn-cancelar-popup:hover {
    background: #2e3192;
    color: #fff;
  }
  </style>

  <script src="../js/inicial.js"></script>
  <script src="../js/atalhos.js"></script>
  <script src="../js/calendario.js?v=<?php echo time(); ?>_all_events_<?php echo rand(1000,9999); ?>"></script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    function abrirPopupBlocos(e) {
      e.preventDefault();
      const popup = document.getElementById('popupBlocos');
      popup.classList.add('show');
    }

    // Sidebar
    const mapBtn = document.getElementById('mapButton');
    if (mapBtn) mapBtn.addEventListener('click', abrirPopupBlocos);

    // Atalhos rápidos
    const shortcutMinimapa = document.getElementById('shortcutMinimapa');
    if (shortcutMinimapa) shortcutMinimapa.addEventListener('click', abrirPopupBlocos);

    // Fecha popup ao clicar fora
    document.getElementById('popupBlocos').addEventListener('click', function(e) {
      if (e.target === this) this.classList.remove('show');
    });
    // Botão cancelar
    document.getElementById('btnCancelarPopupBlocos').onclick = function() {
      document.getElementById('popupBlocos').classList.remove('show');
    };
  });
  </script>

</body>
</html>

