/* =============================================== */
/* JAVASCRIPT ORGANIZADO PARA A PÁGINA INICIAL */
/* =============================================== */

// === SISTEMA DE ATALHOS ===
// Função para carregar e exibir atalhos na página inicial
function carregarAtalhos() {
  const chaveAtalhos = getChaveAtalhos();
  if (!chaveAtalhos) return;
  
  const atalhosContainer = document.getElementById('opcoes');
  const shortcutButtonsContainer = document.querySelector('.shortcut-buttons');
  
  if (!shortcutButtonsContainer) return;
  
  const atalhosSalvos = JSON.parse(localStorage.getItem(chaveAtalhos)) || [];
  
  // Limpar container de botões
  shortcutButtonsContainer.innerHTML = '';
  
  const totalSlots = 6;
  
  // Criar todos os 6 slots
  for (let i = 0; i < totalSlots; i++) {
    const btn = document.createElement('button');
    
    const atalho = atalhosSalvos[i];
    
    if (atalho && atalho.nome) {
      // Atalho preenchido
      btn.className = 'shortcut-btn filled';
      btn.innerHTML = `
        <img src="${atalho.src}" alt="${atalho.alt}" style="width: 24px; height: 24px;">
      `;
      btn.title = atalho.nome;
      
      // Adicionar evento de clique baseado no tipo de atalho
      switch(atalho.nome) {
        case 'Mini Mapa':
          btn.onclick = () => {
            const mapButton = document.getElementById('mapButton');
            if (mapButton) mapButton.click();
          };
          break;
        case 'Lanchonetes':
          btn.onclick = () => window.location.href = 'lanchonetes.html';
          break;
        case 'Financeiro':
          btn.onclick = () => alert('Módulo Financeiro em desenvolvimento');
          break;
        case 'Preferências':
          btn.onclick = () => alert('Módulo Preferências em desenvolvimento');
          break;
        case 'Desempenho':
          btn.onclick = () => alert('Módulo Desempenho em desenvolvimento');
          break;
        default:
          btn.onclick = () => alert(`Atalho ${atalho.nome} em desenvolvimento`);
      }
    } else {
      // Slot vazio
      btn.className = 'add-shortcut-btn';
      btn.textContent = '+';
      btn.title = 'Adicionar atalho';
      btn.onclick = () => window.location.href = 'atalhos.php';
    }
    
    shortcutButtonsContainer.appendChild(btn);
  }
  
  // Também preencher o container de opções se existir
  if (atalhosContainer) {
    atalhosContainer.innerHTML = '';
    
    atalhosSalvos.forEach((atalho, index) => {
      if (atalho && atalho.nome) {
        const atalhoDiv = document.createElement('div');
        atalhoDiv.className = 'opcao-atalho';
        atalhoDiv.id = `opcao-${index + 1}`;
        atalhoDiv.innerHTML = `
          <img src="${atalho.src}" alt="${atalho.alt}" style="width: 48px; height: 48px; margin-bottom: 8px;">
          <span>${atalho.nome}</span>
        `;
        
        // Adicionar mesmo sistema de clique
        switch(atalho.nome) {
          case 'Mini Mapa':
            atalhoDiv.onclick = () => {
              const mapButton = document.getElementById('mapButton');
              if (mapButton) mapButton.click();
            };
            break;
          case 'Lanchonetes':
            atalhoDiv.onclick = () => window.location.href = 'lanchonetes.html';
            break;
          case 'Financeiro':
            atalhoDiv.onclick = () => alert('Módulo Financeiro em desenvolvimento');
            break;
          case 'Preferências':
            atalhoDiv.onclick = () => alert('Módulo Preferências em desenvolvimento');
            break;
          case 'Desempenho':
            atalhoDiv.onclick = () => alert('Módulo Desempenho em desenvolvimento');
            break;
          default:
            atalhoDiv.onclick = () => alert(`Atalho ${atalho.nome} em desenvolvimento`);
        }
        
        atalhosContainer.appendChild(atalhoDiv);
      }
    });
  }
  
  const atalhosExistentes = atalhosSalvos.filter(a => a && a.nome).length;
  console.log('✅ Atalhos carregados:', atalhosExistentes, 'de', totalSlots);
}

// === CONTROLES ORIGINAIS ===
// Lógica original para o menu de opções dos blocos
const mapButton = document.getElementById("mapButton");
const optionsMenu = document.getElementById("optionsMenu");

if (mapButton && optionsMenu) {
  mapButton.addEventListener("click", () => {
      optionsMenu.classList.toggle("active");
  });
}

// --- CÓDIGO PARA CONTROLAR A SIDEBAR ---
// Seleciona os novos elementos do DOM
const toggleButton = document.getElementById('toggleSidebarBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

// Função que abre/fecha a sidebar adicionando/removendo a classe 'visible'
function toggleSidebar() {
  if (sidebar) sidebar.classList.toggle('visible');
  if (overlay) overlay.classList.toggle('visible');
}

// Adiciona o evento de clique ao botão "hambúrguer"
if (toggleButton) {
  toggleButton.addEventListener('click', toggleSidebar);
}

// Adiciona o evento de clique ao overlay para fechar a sidebar quando clicar fora dela
if (overlay) {
  overlay.addEventListener('click', toggleSidebar);
}

// === SISTEMA DE TEMA ===
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
    logo.src = '../assets/logo_quantum_white.png';
  } else {
    logo.src = '../assets/logo_quantum.png';
  }
}

// Carregar tema salvo do localStorage no carregamento da página
window.addEventListener('DOMContentLoaded', function() {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
    trocarLogoTema('dark');
  } else {
    trocarLogoTema('light');
  }
});

/* =============================================== */
/* SCRIPTS MOVIDOS DO INICIAL.PHP */
/* =============================================== */

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

// === ANIMAÇÕES E BOAS-VINDAS ===
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
  var jaViuBoasVindas = localStorage.getItem('jaViuBoasVindas');
  
  function initAppFeatures() {
    // Inicializar sistema de atalhos
    if (typeof inicializarSistemaAtalhos === 'function') {
      inicializarSistemaAtalhos();
    }
    
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
      // Nota: text será definido no PHP através de data-welcome-text
      var text = cardWelcome.getAttribute('data-welcome-text') || 'Bem-vindo!';
      
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
      var text = cardWelcome.getAttribute('data-welcome-text') || 'Bem-vindo!';
      cardWelcome.textContent = text;
      cardWelcome.style.opacity = '1';
      initAppFeatures();
    }
  } else if (cardWelcome) {
    // Fallback: sempre mostra o texto no card
    var text = cardWelcome.getAttribute('data-welcome-text') || 'Bem-vindo!';
    cardWelcome.textContent = text;
    cardWelcome.style.opacity = '1';
    initAppFeatures();
  } else {
    // Se não houver animação, inicializa recursos normalmente
    initAppFeatures();
  }
});

// === ASSISTENTE VIRTUAL ===
function initAssistenteVirtual() {
  const btn = document.getElementById('abrirAssistente');
  const chat = document.getElementById('assistenteChat');
  const fechar = document.getElementById('fecharAssistente');
  
  if (btn && chat && fechar) {
    btn.onclick = () => chat.style.display = 'flex';
    fechar.onclick = () => chat.style.display = 'none';
  }

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

  const enviarBtn = document.getElementById('enviarAssistente');
  if (enviarBtn) {
    enviarBtn.onclick = async function() {
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
  }
}

// === CARTEIRINHA ===
function initCarteirinha() {
  // Botão Carteirinha
  const btnCarteirinha = document.getElementById('btnCarteirinha');
  const carteirinhaPopup = document.getElementById('carteirinhaPopup');
  const fecharCarteirinha = document.getElementById('fecharCarteirinha');

  if (btnCarteirinha && carteirinhaPopup) {
    btnCarteirinha.onclick = function() {
      carteirinhaPopup.style.display = 'flex';
    };
  }

  if (fecharCarteirinha && carteirinhaPopup) {
    // Fechar Carteirinha
    fecharCarteirinha.onclick = function() {
      carteirinhaPopup.style.display = 'none';
    };
    
    // Fechar ao clicar fora
    carteirinhaPopup.onclick = function(e) {
      if (e.target === this) this.style.display = 'none';
    };
  }
}

// === MINI PERFIL E TEMA ===
function initMiniPerfil() {
  // Mini perfil popup funcional
  const adminProfile = document.getElementById('adminProfile');
  const miniPerfilPopup = document.getElementById('miniPerfilPopup');

  if (adminProfile && miniPerfilPopup) {
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
  }

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
  const btnTrocarTema = document.getElementById('btnTrocarTema');
  if (btnTrocarTema) {
    btnTrocarTema.onclick = function() {
      const isDark = document.body.classList.contains('dark-mode');
      const newTheme = !isDark;
      setTheme(newTheme);
      saveTheme(newTheme);
      if (miniPerfilPopup) miniPerfilPopup.style.display = 'none';
    };
  }

  // Botão Logout
  const btnLogout = document.getElementById('btnLogout');
  if (btnLogout) {
    btnLogout.onclick = function() {
      window.location.href = '../login.php';
    };
  }
}

// === INICIALIZAÇÃO COMPLETA ===
// Inicializar todas as funcionalidades quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
  initAssistenteVirtual();
  initCarteirinha();
  initMiniPerfil();
  console.log('✅ Inicial.js organizado carregado com sucesso!');
});