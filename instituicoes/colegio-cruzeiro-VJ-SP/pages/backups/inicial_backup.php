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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página Inicial</title>
  <link rel="stylesheet" href="../css/inicial.css?v=<?php echo time(); ?>_clean_grid">
  <link rel="stylesheet" href="../css/calendario.css?v=<?php echo time(); ?>_force_stretch_<?php echo rand(1000,9999); ?>">
  <style>
  /* FORÇAR EVENTOS ESTICADOS */
  .event-item {
    background: rgba(108, 124, 231, 0.2) !important;
    border-left: 4px solid #00e0ff !important;
    padding: 12px 16px !important;
    margin-bottom: 10px !important;
    border-radius: 0 8px 8px 0 !important;
    min-height: 60px !important;
  }
  .event-title {
    font-size: 0.9rem !important;
    margin-bottom: 6px !important;
    font-weight: 700 !important;
  }
  .event-time {
    font-size: 0.75rem !important;
    margin-top: 4px !important;
    gap: 6px !important;
  }
  .event-date {
    font-size: 0.65rem !important;
    margin-bottom: 6px !important;
  }
  
  /* CENTRALIZAR CALENDÁRIO E ATALHOS */
  .main-grid { 
    display: grid !important; 
    grid-template-columns: auto auto !important; 
    gap: 8px !important; 
    justify-content: center !important;
    width: 100% !important;
    max-width: 1200px !important;
    margin: 0 auto !important;
  }
  .shortcuts-section {
    width: auto !important;
    max-width: 600px !important;
  }
  .calendar-sidebar { 
    width: auto !important;
    min-width: 500px !important; 
    max-width: 500px !important; 
  }
  .calendar-section { 
    max-width: 500px !important; 
    min-width: 500px !important; 
    padding: 15px 18px !important; 
    border: 2px solid rgba(58, 74, 122, 0.8) !important;
  }
  .calendar-day { 
    width: 32px !important; 
    height: 32px !important; 
    font-size: 1rem !important; 
  }
  .calendar-grid { 
    grid-template-columns: repeat(7, 32px) !important; 
    gap: 4px !important; 
  }
  .calendar-nav-btn {
    background: rgba(255, 255, 255, 0.3) !important;
    border: none !important;
    color: white !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    width: 20px !important;
    height: 20px !important;
    border-radius: 50% !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
    backdrop-filter: blur(10px) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
    pointer-events: auto !important;
    z-index: 10 !important;
  }
  .calendar-nav-btn:hover {
    background: rgba(255, 255, 255, 0.5) !important;
    transform: scale(1.1) !important;
  }
  
  /* RESPONSIVO CENTRALIZADO */
  @media (max-width: 1024px) {
    .main-grid {
      grid-template-columns: 1fr !important;
      grid-template-rows: auto auto !important;
      justify-content: center !important;
      justify-items: center !important;
      gap: 10px !important;
    }
    .shortcuts-section,
    .calendar-sidebar {
      max-width: 600px !important;
      width: 100% !important;
    }
  }
  
  /* Botões do menu de perfil do admin */
  .profile-menu-btn {
    width: 100%;
    background: linear-gradient(90deg, #eaf2ff 0%, #dbeaff 100%);
    border: none;
    padding: 11px 16px;
    text-align: left;
    cursor: pointer;
    border-radius: 9px;
    font-weight: 500;
    color: #2e3192;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    margin-bottom: 4px;
    transition: background .18s, color .18s;
    box-shadow: 0 1px 6px rgba(44,92,255,0.07);
  }
  .profile-menu-btn:last-child {
    background: linear-gradient(90deg, #ffeaea 0%, #ffd6d6 100%);
    color: #d32f2f;
  }
  .profile-menu-btn:hover {
    background: #e6efff;
    color: #0057ff;
  }
  .profile-menu-btn:last-child:hover {
    background: #ffd6d6;
    color: #b71c1c;
  }
  .profile-menu-btn svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
  }
  body.dark-mode #profileMenu {
    background: #232a4d !important;
    border-color: #232a4d !important;
    box-shadow: 0 2px 12px rgba(0,224,255,0.08);
  }
  body.dark-mode .profile-menu-btn {
    background: linear-gradient(90deg, #232a4d 0%, #181c2f 100%);
    color: #00e0ff;
    box-shadow: 0 1px 8px rgba(0,224,255,0.07);
  }
  body.dark-mode .profile-menu-btn:last-child {
    background: linear-gradient(90deg, #3a1a1a 0%, #232a4d 100%);
    color: #ffbdbd;
  }
  body.dark-mode .profile-menu-btn:hover {
    background: #181c2f;
    color: #00e0ff;
  }
  body.dark-mode .profile-menu-btn:last-child:hover {
    background: #3a1a1a;
    color: #ff6b6b;
  }
  /* Mini perfil popup modo escuro */
  body.dark-mode #miniPerfilPopup {
    background: #232a4d !important;
    box-shadow: 0 2px 12px rgba(0,224,255,0.08) !important;
  }
  body.dark-mode #miniPerfilPopup span:first-child {
    color: #00e0ff !important;
  }
  body.dark-mode #miniPerfilPopup div[style*="color:#333"] {
    color: #b3d9ff !important;
  }
  body.dark-mode #btnCarteirinha,
  body.dark-mode #btnTrocarTema {
    background: linear-gradient(90deg, #232a4d 0%, #181c2f 100%) !important;
    color: #00e0ff !important;
  }
  body.dark-mode #btnCarteirinha:hover,
  body.dark-mode #btnTrocarTema:hover {
    background: #181c2f !important;
    color: #00e0ff !important;
  }
  body.dark-mode button[name="logout"] {
    background: linear-gradient(90deg, #3a1a1a 0%, #232a4d 100%) !important;
    color: #ffbdbd !important;
  }
  body.dark-mode button[name="logout"]:hover {
    background: #3a1a1a !important;
    color: #ff6b6b !important;
  }
  /* Popup compacto e estilizado para opções Mini Mapa */
  .mini-mapa-opcoes-popup {
    position: absolute;
    left: 50%;
    bottom: 100%;
    transform: translateX(-50%) translateY(-10px) scale(0.97);
    display: flex;
    gap: 6px;
    justify-content: center;
    padding: 4px 8px;
    background: #f7faff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(44,92,255,0.10);
    border: 1px solid #e0eaff;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.18s cubic-bezier(.4,1.4,.6,1), transform 0.18s cubic-bezier(.4,1.4,.6,1);
    z-index: 20;
    min-width: 0;
  }
  .mini-mapa-opcoes-popup.show {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(-50%) translateY(-18px) scale(1);
  }
  .mini-mapa-opcoes-popup button {
    background: #fff;
    border: 1px solid #b6d2ff;
    border-radius: 6px;
    padding: 2px 8px 2px 6px;
    font-size: 12px;
    color: #2e3192;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 3px;
    box-shadow: 0 1px 4px rgba(44,92,255,0.07);
    transition: background 0.13s, color 0.13s, box-shadow 0.13s;
    min-width: 0;
  }
  .mini-mapa-opcoes-popup button:hover {
    background: #eaf2ff;
    color: #0057ff;
    box-shadow: 0 2px 8px rgba(44,92,255,0.13);
  }
  .mini-mapa-opcoes-popup svg {
    width: 13px;
    height: 13px;
    stroke: #5b8cff;
    margin-right: 1px;
  }
  /* Mini mapa popup modo escuro */
  body.dark-mode .mini-mapa-opcoes-popup {
    background: #232a4d !important;
    border-color: #3a4a7a !important;
    box-shadow: 0 2px 8px rgba(0,224,255,0.10) !important;
  }
  body.dark-mode .mini-mapa-opcoes-popup button {
    background: #2e1a47 !important;
    border-color: #3a4a7a !important;
    color: #00e0ff !important;
    box-shadow: 0 1px 4px rgba(0,224,255,0.07) !important;
  }
  body.dark-mode .mini-mapa-opcoes-popup button:hover {
    background: #3a4a7a !important;
    color: #00e0ff !important;
    box-shadow: 0 2px 8px rgba(0,224,255,0.13) !important;
  }
  body.dark-mode .mini-mapa-opcoes-popup svg {
    stroke: #00e0ff !important;
  }
  .apple-transition-overlay {
      position: fixed;
      top: 0; left: 0; width: 100vw; height: 100vh;
      background: linear-gradient(120deg, #5b8cff 0%, #2e3192 100%);
      opacity: 0;
      pointer-events: none;
      z-index: 9999;
      transition: opacity 0.7s cubic-bezier(.4,1.4,.6,1);
    }
    .apple-transition-active {
      opacity: 1;
      pointer-events: auto;
    }
    .shortcuts-card.apple-animate {
      animation: appleFadeOutScale 0.7s cubic-bezier(.4,1.4,.6,1) forwards;
    }
      .shortcuts-card.apple-animate-in {
        animation: appleFadeInScaleUp 0.7s cubic-bezier(.4,1.4,.6,1);
      }
      @keyframes appleFadeInScaleUp {
        0% {
          opacity: 0;
          transform: scale(1.12) translateY(40px);
        }
        40% {
          opacity: 0.7;
          transform: scale(1.04) translateY(16px);
        }
        100% {
          opacity: 1;
          transform: scale(1) translateY(0);
        }
      }
    @keyframes appleFadeInScale {
      0% { opacity: 0; transform: scale(1.12); }
      40% { opacity: 0.7; transform: scale(1.04); }
      100% { opacity: 1; transform: scale(1); }
    }
    .shortcuts-card.hide {
      animation: fadeOutScale 0.5s cubic-bezier(.4,1.4,.6,1) forwards;
    }
    @keyframes fadeOutScale {
      to {
        opacity: 0;
        transform: scale(0.92) translateY(30px);
        filter: blur(2px);
      }
    }

    /* Adicione ao seu CSS */    /* Adicione ao seu CSS */
    .atalho-slot.preenchido {
      transition: background 0.3s, box-shadow 0.3s;
      background: #eaf2ff;
      box-shadow: 0 2px 12px rgba(44,92,255,0.10);
    }
    .atalho-slot.preenchido {
      transition: background 0.3s, box-shadow 0.3s;
      background: #eaf2ff;
      box-shadow: 0 2px 12px rgba(44,92,255,0.10);
    }
    
    /* Menu minimapa */
    #menuMinimapa {
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    
    body.dark-mode #menuMinimapa {
      background: #232a4d !important;
      border-color: #3a4a7a !important;
      color: #f1f1f1 !important;
    }
    
    body.dark-mode #menuMinimapa button {
      background: #2e1a47 !important;
      color: #00e0ff !important;
    }
    
    body.dark-mode #menuMinimapa button:hover {
      background: #3a4a7a !important;
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
  <img src="../assets/imagens/LOGO.png" alt="Logo Quantum" class="logo" id="headerLogo" style="width:32px;height:32px;border-radius:50%;margin-right:10px;vertical-align:middle;box-shadow:0 2px 8px rgba(44,92,255,0.10);">
      <h1>Quantum Edu.</h1>
  <h2 class="welcome-header" id="headerWelcome" style="font-size:16px; font-weight:400; margin:4px 0 0 0; color:#5b8cff; opacity:0; transition:opacity 0.5s;"></h2>
    </div>
    <div class="admin-profile" id="adminProfile" style="position: relative; cursor: pointer;">
      <img src="../assets/imagens/spongebob.png" alt="Admin" class="admin-avatar">
      <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
      <!-- Menu suspenso -->
      <!--
      <div id="profileMenu" style="display:none; position:absolute; right:0; top:110%; background:#fff; border:1px solid #ddd; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); min-width:120px; z-index:100;">
        <button id="toggleThemeBtn" class="profile-menu-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="#5b8cff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="M4.93 19.07l1.41-1.41"/><path d="M17.66 6.34l1.41-1.41"/></svg>
          Alternar modo claro/escuro
        </button>
        <button id="btnSair" class="profile-menu-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="#d32f2f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Sair
        </button>
      </div>
      -->
    </div>
  </header>

  <div class="welcome-card">
    <img src="../assets/imagens/spongebob.png" alt="Avatar Admin" class="welcome-avatar">
    <div>
      <h2 class="welcome-title" id="cardWelcome" style="font-size:1.3rem;font-weight:600;margin:0 0 4px 0;color:#5b8cff;opacity:0;transition:opacity 0.5s;"></h2>
      <p class="welcome-desc">Acesse e gerencie todos os recursos da plataforma Quantum Admin.</p>
    </div>
  </div>

  <aside class="sidebar" id="sidebar">

    <a href="#" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.5V12h-2V9.7l-7 3.11v5.39l-7-3.11V18h2v-3.2l7 3.11 9-4.5V18h2v-6l-11 6-11-6 11-6z"/></svg>
      <span>Acadêmico</span>
    </a>

    <div class="sidebar-item-container">
      <div class="sidebar-item" id="mapButton">
        <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/></svg>
        <span>Mini Mapa</span>
      </div>
      <div class="options" id="optionsMenu">
  <button onclick="window.location.href='tour.html?bloco=A'">Bloco A</button>
  <button onclick="window.location.href='tour.html?bloco=B'">Bloco B</button>
  <button onclick="window.location.href='tour.html?bloco=C'">Bloco C</button>
  <button onclick="window.location.href='tour.html?bloco=D'">Bloco D</button>
  <button onclick="window.location.href='tour.html?bloco=INFANTIL'">Infantil</button>
  <button onclick="window.location.href='tour.html?bloco=Biblioteca'">Biblioteca</button>
      </div>
    </div>

    <a href="lanchonetes.html" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M16 4h-2c-1.1 0-2 .9-2 2v2H6c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-2V4c0-1.1-.9-2-2-2zm-4 0h2v2h-2V4zM6 8h14v9H6V8z"/></svg>
      <span>Lanchonetes</span>
    </a>

    <a href="#" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2-9h4v2h-4v-2zm-2 4h8v2H8v-2z"/></svg>
      <span>Financeiro</span>
    </a>

    <a href="#" class="sidebar-item">
      <svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c.04.32.07.65.07.98s-.03.66-.07.98l2.11 1.65c.19.15.24.42.12.64l-2 3.46c-.12.22-.39.3-.61.22l-2.49-1c-.52.4-1.08.73-1.69.98l-.38 2.65c-.03.24-.24.42-.49.42h-4c-.25 0-.46-.18-.49-.42l-.38-2.65c-.61-.25-1.17-.59-1.69-.98l-2.49 1c-.23.09-.49 0-.61-.22l-2-3.46c-.12-.22-.07-.49.12-.64l2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
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
              <button 
                id="editShortcutsBtn"
                title="Editar atalhos" 
                style="background: none; border: none; cursor: pointer; padding: 4px;"
                aria-label="Editar atalhos"
              >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0057ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 20h9" />
                  <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="shortcut-buttons">
            <button class="add-shortcut-btn" onclick="window.location.href='atalhos.php'">+</button>
            <button class="add-shortcut-btn" onclick="window.location.href='atalhos.php'">+</button>
            <button class="add-shortcut-btn" onclick="window.location.href='atalhos.php'">+</button>
            <button class="add-shortcut-btn" onclick="window.location.href='atalhos.php'">+</button>
          </div>
        </div>

        <div class="opcoes-grid" id="opcoes">
          <!-- Cada opção de atalho deve ter uma classe .opcao-atalho e um id único, ex: id="opcao-1" -->
        </div>
      </div>

      <div class="calendar-sidebar">
        <div class="calendar-section calendar-fade-in">
          <div class="calendar-header">
            <h3 class="calendar-title">
              📅 Calendário
            </h3>
            <div class="calendar-nav">
              <button class="calendar-nav-btn" id="prevMonth" title="Mês anterior">‹</button>
              <div class="calendar-month" id="currentMonth">Carregando...</div>
              <button class="calendar-nav-btn" id="nextMonth" title="Próximo mês">›</button>
            </div>
          </div>
          
          <div class="calendar-container">
            <div class="calendar-widget">
              <div class="calendar-grid" id="calendarGrid">
                <!-- O calendário será gerado aqui pelo JavaScript -->
              </div>
            </div>
            
            <div class="calendar-widget events-widget">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding: 6px 0;">
                <h4 style="margin: 0; color: #e0f4ff; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 6px; text-shadow: 0 2px 4px rgba(0, 224, 255, 0.3);">
                  🎯 Eventos
                </h4>
                <button class="add-event-btn" onclick="addNewEvent()" title="Adicionar novo evento (Ctrl+N)" style="width: 32px; height: 32px; font-size: 20px; font-weight: 800;">
                  <span>+</span>
                </button>
              </div>
              <div class="events-panel" id="eventsPanel">
                <div class="no-events">Carregando eventos...</div>
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
      
      // Perfil
      const adminProfile = document.getElementById('adminProfile');
      const profileMenu = document.getElementById('profileMenu');
      const btnSair = document.getElementById('btnSair');
      if (adminProfile && profileMenu) {
        adminProfile.addEventListener('click', function(e) {
          e.stopPropagation();
          profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function() {
          profileMenu.style.display = 'none';
        });
      }
      // Botão sair do perfil
      if (btnSair) {
        btnSair.onclick = function() {
          document.querySelector('.shortcuts-card').classList.add('apple-animate');
          document.getElementById('appleOverlay').classList.add('apple-transition-active');
          setTimeout(function() {
            window.location.href = '../login.html';
          }, 700);
        };
      }
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
<div id="miniPerfilPopup" style="display:none;position:fixed;top:70px;right:32px;z-index:9999;background:#fff;border-radius:22px;box-shadow:0 2px 12px rgba(44,92,255,0.13);width:340px;max-width:90vw;padding:22px 20px 18px 20px;">
  <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
    <img src="../assets/imagens/spongebob.png" alt="Avatar Admin" style="width:64px;height:64px;border-radius:50%;box-shadow:0 2px 8px rgba(44,92,255,0.10);">
    <div>
      <span style="font-weight:600;font-size:18px;color:#0057ff;"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
      <div style="font-size:14px;color:#333;">RGM: 2012081</div>
      <div style="font-size:14px;color:#333;">Série: 3ºJ</div>
      <div style="font-size:14px;color:#333;">Colégio: COLEGIO CRUZEIRO DO SUL</div>
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
    <button type="submit" name="logout" 
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
        <div class="carteirinha-nome">GUSTAVO MEDEIROS</div>
        <div class="carteirinha-rgm">RGM: 2012081</div>
        <div class="carteirinha-ensino">ENSINO MÉDIO</div>
        <div class="carteirinha-serie">SÉRIE: 3°J</div>
        <div class="carteirinha-validade">VALIDADE: ??????</div>
      </div>
    </div>
  </div>
  <button id="fecharCarteirinha" style="margin-top:18px;background:#0057ff;color:#fff;border:none;border-radius:8px;padding:7px 18px;font-weight:500;cursor:pointer;position:absolute;top:40px;right:calc(50vw - 170px);">Fechar</button>
</div>

<style>
.carteirinha-modelo {
  background: #06398c;
  border-radius: 36px;
  box-shadow: 0 2px 18px rgba(44,92,255,0.18);
  width: 480px;
  max-width: 95vw;
  min-height: 320px;
  padding: 32px 32px 24px 32px;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  position: relative;
  font-family: 'Segoe UI', Arial, sans-serif;
}
.carteirinha-topo {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 18px;
}
.carteirinha-logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.carteirinha-colegio {
  color: #fff;
  line-height: 1.1;
}
.carteirinha-quantum {
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  min-width: 120px;
  height: 90px;
}
.carteirinha-quantum img {
  max-height: 90px;
  width: auto;
  object-fit: contain;
  display: block;
}
.carteirinha-conteudo {
  display: flex;
  align-items: flex-start;
  gap: 28px;
}
.carteirinha-foto {
  background: #d9d9d9;
  border-radius: 8px;
  width: 110px;
  height: 110px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.foto-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
}
.carteirinha-dados {
  color: #fff;
  font-size: 1.13rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 8px;
  margin-top: 8px;
  position: relative;
  min-width: 210px;
}
.carteirinha-nome {
  font-weight: 600;
  font-size: 1.18rem;
  margin-bottom: 2px;
}
.carteirinha-rgm,
.carteirinha-ensino,
.carteirinha-serie,
.carteirinha-validade {
  font-size: 1.03rem;
  margin-bottom: 2px;
  font-weight: 400;
}
.carteirinha-serie {
  margin-top: 4px;
  margin-bottom: 0;
  display: inline-block;
  position: relative;
  z-index: 2;
}
.carteirinha-validade {
  margin-top: 4px;
  font-size: 1.03rem;
  position: relative;
  z-index: 1;
}
@media (max-width: 600px) {
  .carteirinha-modelo {
    width: 98vw;
    padding: 18px 8px 18px 8px;
    border-radius: 18px;
  }
  #fecharCarteirinha {
    right: 12px !important;
    top: 12px !important;
  }
  .carteirinha-conteudo {
    flex-direction: column;
    gap: 12px;
    align-items: center;
  }
  .carteirinha-validade {
    margin-top: 0;
  }
}
</style>

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
// Mini perfil popup funcional
const adminProfile = document.getElementById('adminProfile');
const miniPerfilPopup = document.getElementById('miniPerfilPopup');

// Abre/fecha o mini perfil ao clicar na foto
adminProfile.onclick = function(e) {
  e.stopPropagation();
  miniPerfilPopup.style.display = miniPerfilPopup.style.display === 'none' || miniPerfilPopup.style.display === '' ? 'block' : 'none';
};

// Fecha o mini perfil ao clicar fora dele
document.addEventListener('click', function(e) {
  if (miniPerfilPopup.style.display === 'block') {
    // Só fecha se o clique não for dentro do popup
    if (!miniPerfilPopup.contains(e.target) && e.target !== adminProfile) {
      miniPerfilPopup.style.display = 'none';
    }
  }
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
  miniPerfilPopup.style.display = 'none';
};

// Botão Logout
document.getElementById('btnLogout').onclick = function() {
  window.location.href = '../login.php';
};
</script>
</body>
</html>