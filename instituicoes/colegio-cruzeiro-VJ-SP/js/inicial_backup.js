// Lógica original para o menu de opções dos blocos
const mapButton = document.getElementById("mapButton");
const optionsMenu = document.getElementById("optionsMenu");

mapButton.addEventListener("click", () => {
    optionsMenu.classList.toggle("active");
});


// --- NOVO CÓDIGO PARA CONTROLAR A SIDEBAR ---

// Seleciona os novos elementos do DOM
const toggleButton = document.getElementById('toggleSidebarBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

// Função que abre/fecha a sidebar adicionando/removendo a classe 'visible'
function toggleSidebar() {
  sidebar.classList.toggle('visible');
  overlay.classList.toggle('visible');
}

// Adiciona o evento de clique ao botão "hambúrguer"
toggleButton.addEventListener('click', toggleSidebar);

// Adiciona o evento de clique ao overlay para fechar a sidebar quando clicar fora dela
overlay.addEventListener('click', toggleSidebar);

// Alternância de tema claro/escuro - apenas se o botão existir
const toggleThemeBtn = document.getElementById('toggleThemeBtn');
if (toggleThemeBtn) {
  toggleThemeBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    document.body.classList.toggle('dark-mode');
    // Salva preferência no localStorage
    if(document.body.classList.contains('dark-mode')) {
      localStorage.setItem('theme', 'dark');
      trocarLogoTema('dark');
    } else {
      localStorage.setItem('theme', 'light');
      trocarLogoTema('light');
    }
  });
}

// Função para trocar a logo conforme tema
function trocarLogoTema(theme) {
  var logo = document.getElementById('headerLogo');
  if (!logo) return;
  if (theme === 'dark') {
    logo.src = '../assets/imagens/INVERSO.png';
  } else {
    logo.src = '../assets/imagens/LOGO.png';
  }
}

// Aplica o tema salvo ao carregar - comentado pois agora é gerenciado pelo PHP
// if(localStorage.getItem('theme') === 'dark') {
//   document.body.classList.add('dark-mode');
//   trocarLogoTema('dark');
// } else {
//   trocarLogoTema('light');
// }

// Função de atalhos para ser chamada pelo HTML
window.carregarAtalhos = function() {
  // Verifica se as informações do usuário estão disponíveis
  if (typeof usuarioLogado === 'undefined') {
    console.error('Informações do usuário não disponíveis');
    return;
  }
  
  const chaveAtalhos = `atalhosSelecionados_usuario_${usuarioLogado.id}`;
  const atalhos = JSON.parse(localStorage.getItem(chaveAtalhos)) || [];
  const container = document.querySelector('.shortcut-buttons');
  if (!container) return;
  
  container.innerHTML = '';
  atalhos.slice(0, 4).forEach(atalho => {
    if (atalho && atalho.nome && atalho.src) {
      const btn = document.createElement('button');
      btn.className = 'add-shortcut-btn atalho-preenchido';
      btn.title = atalho.nome;
      btn.style.display = 'flex';
      btn.style.flexDirection = 'column';
      btn.style.alignItems = 'center';
      btn.style.justifyContent = 'center';
      btn.innerHTML = `
        <img src="${atalho.src}" alt="${atalho.alt || ''}" style="width:28px;height:28px;margin-bottom:2px;">
        <span style="font-size:11px;color:#222;">${atalho.nome}</span>
      `;
      if (atalho.nome === "Mini Mapa") {
        btn.onclick = function(e) {
          e.stopPropagation();
          mostrarMenuMinimapa(e);
        };
      } else if (atalho.nome === "Lanchonetes") {
        btn.onclick = () => window.location.href = 'lanchonetes.html';
      }
      container.appendChild(btn);
    } else {
      const btn = document.createElement('button');
      btn.className = 'add-shortcut-btn';
      btn.textContent = '+';
      btn.onclick = () => window.location.href = 'atalhos.php';
      container.appendChild(btn);
    }
  });
  for (let i = atalhos.length; i < 4; i++) {
    const btn = document.createElement('button');
    btn.className = 'add-shortcut-btn';
    btn.textContent = '+';
    btn.onclick = () => window.location.href = 'atalhos.php';
    container.appendChild(btn);
  }
}

// Função para mostrar menu do minimapa
function mostrarMenuMinimapa(event) {
  // Remove menu existente se houver
  const menuExistente = document.getElementById('menuMinimapa');
  if (menuExistente) {
    menuExistente.remove();
  }

  // Verifica se está no modo escuro
  const isDarkMode = document.body.classList.contains('dark-mode');

  // Cria o menu
  const menu = document.createElement('div');
  menu.id = 'menuMinimapa';
  menu.style.cssText = `
    position: fixed;
    background: ${isDarkMode ? '#232a4d' : 'white'};
    border: 1px solid ${isDarkMode ? '#3a4a7a' : '#ddd'};
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,${isDarkMode ? '0.3' : '0.15'});
    z-index: 10000;
    padding: 8px;
    min-width: 150px;
    color: ${isDarkMode ? '#f1f1f1' : '#333'};
  `;

  // Adiciona os botões
  const opcoes = [
    { nome: 'Bloco A', url: 'tour.html?bloco=A' },
    { nome: 'Bloco B', url: 'tour.html?bloco=B' },
    { nome: 'Bloco C', url: 'tour.html?bloco=C' },
    { nome: 'Bloco D', url: 'tour.html?bloco=D' },
    { nome: 'Infantil', url: 'tour.html?bloco=INFANTIL' },
    { nome: 'Biblioteca', url: 'tour.html?bloco=Biblioteca' }
  ];

  opcoes.forEach(opcao => {
    const btn = document.createElement('button');
    btn.textContent = opcao.nome;
    btn.style.cssText = `
      display: block;
      width: 100%;
      padding: 8px 12px;
      margin: 2px 0;
      border: none;
      background: ${isDarkMode ? '#2e1a47' : '#f5f5f5'};
      color: ${isDarkMode ? '#00e0ff' : '#333'};
      border-radius: 4px;
      cursor: pointer;
      text-align: left;
      transition: background 0.2s;
    `;
    btn.onclick = () => {
      window.location.href = opcao.url;
    };
    btn.onmouseover = () => {
      btn.style.background = isDarkMode ? '#3a4a7a' : '#e0e0e0';
    };
    btn.onmouseout = () => {
      btn.style.background = isDarkMode ? '#2e1a47' : '#f5f5f5';
    };
    menu.appendChild(btn);
  });

  // Posiciona o menu próximo ao botão clicado
  const rect = event.target.closest('button').getBoundingClientRect();
  menu.style.left = rect.left + 'px';
  menu.style.top = (rect.top - 10) + 'px';

  // Adiciona ao body
  document.body.appendChild(menu);

  // Ajusta posição se estiver fora da tela
  setTimeout(() => {
    const menuRect = menu.getBoundingClientRect();
    if (menuRect.top < 0) {
      menu.style.top = (rect.bottom + 10) + 'px';
    }
    if (menuRect.right > window.innerWidth) {
      menu.style.left = (rect.right - menuRect.width) + 'px';
    }
  }, 10);

  // Fecha o menu ao clicar fora
  setTimeout(() => {
    document.addEventListener('click', function fecharMenu() {
      menu.remove();
      document.removeEventListener('click', fecharMenu);
    });
  }, 100);
}