// === CALENDÁRIO MODERNO FUNCIONAL - VERSÃO LIMPA ===

class ModernCalendar {
  constructor() {
    this.currentDate = new Date();
    this.selectedDate = null;
    this.events = {};
    this.monthNames = [
      'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
      'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    this.dayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    
    console.log('Calendário criado, inicializando...');
    this.init();
  }

  init() {
    console.log('Inicializando calendário...');
    
    // Aguardar DOM estar pronto
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        this.initializeCalendar();
      });
    } else {
      this.initializeCalendar();
    }
  }

  initializeCalendar() {
    console.log('Inicializando componentes do calendário...');
    try {
      this.loadEvents();
      this.render();
      this.setupEventListeners();
      this.loadSampleEvents();
      console.log('Calendário completamente inicializado');
    } catch (error) {
      console.error('Erro na inicialização:', error);
    }
  }

  setupEventListeners() {
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');

    console.log('Configurando event listeners...', { prevBtn, nextBtn });

    if (prevBtn) {
      prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('Clique no botão anterior');
        this.previousMonth();
      });
    } else {
      console.error('Botão prevMonth não encontrado');
    }
    
    if (nextBtn) {
      nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('Clique no botão próximo');
        this.nextMonth();
      });
    } else {
      console.error('Botão nextMonth não encontrado');
    }
  }

  previousMonth() {
    console.log('Navegando para mês anterior');
    this.currentDate.setMonth(this.currentDate.getMonth() - 1);
    console.log('Nova data:', this.currentDate);
    this.render();
  }

  nextMonth() {
    console.log('Navegando para próximo mês');
    this.currentDate.setMonth(this.currentDate.getMonth() + 1);
    console.log('Nova data:', this.currentDate);
    this.render();
  }

  render() {
    console.log('Renderizando calendário...');
    this.renderHeader();
    this.renderCalendar();
    this.renderEvents();
    console.log('Calendário renderizado');
  }

  renderHeader() {
    const headerElement = document.getElementById('currentMonth');
    if (headerElement) {
      const monthYear = `${this.monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
      headerElement.textContent = monthYear;
      console.log('Cabeçalho atualizado:', monthYear);
    } else {
      console.error('Elemento currentMonth não encontrado');
    }
  }

  renderCalendar() {
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) {
      console.error('Grade do calendário não encontrada');
      return;
    }

    calendarGrid.innerHTML = '';

    // Adicionar cabeçalhos dos dias
    this.dayNames.forEach(day => {
      const dayHeader = document.createElement('div');
      dayHeader.className = 'calendar-day-header';
      dayHeader.textContent = day;
      calendarGrid.appendChild(dayHeader);
    });

    // Obter primeiro dia do mês e quantidade de dias
    const firstDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
    const lastDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    // Adicionar células vazias para dias do mês anterior
    for (let i = 0; i < startingDayOfWeek; i++) {
      const emptyCell = document.createElement('div');
      emptyCell.className = 'calendar-day empty';
      calendarGrid.appendChild(emptyCell);
    }

    // Adicionar dias do mês
    for (let day = 1; day <= daysInMonth; day++) {
      const dayCell = document.createElement('div');
      dayCell.className = 'calendar-day';
      dayCell.textContent = day;

      // Verificar se é hoje
      const today = new Date();
      if (this.currentDate.getFullYear() === today.getFullYear() &&
          this.currentDate.getMonth() === today.getMonth() &&
          day === today.getDate()) {
        dayCell.classList.add('today');
      }

      // Verificar se há eventos neste dia
      const dateKey = this.getDateKey(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
      if (this.events[dateKey] && this.events[dateKey].length > 0) {
        dayCell.classList.add('has-events');
      }

      // Adicionar evento de clique
      dayCell.addEventListener('click', () => {
        document.querySelectorAll('.calendar-day').forEach(cell => cell.classList.remove('selected'));
        dayCell.classList.add('selected');
        this.selectedDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
        this.renderEvents();
      });

      calendarGrid.appendChild(dayCell);
    }
  }

  renderEvents() {
    console.log('🔄 Renderizando TODOS os eventos do mês...');
    const eventsPanel = document.getElementById('eventsPanel');
    
    if (!eventsPanel) {
      console.log('⚠️ Painel de eventos não encontrado - ignorando renderização');
      return;
    }

    try {
      console.log('✅ Painel de eventos encontrado, prosseguindo...');
      
      // Obter todos os eventos do mês atual
      const currentYear = this.currentDate.getFullYear();
      const currentMonth = this.currentDate.getMonth();
      
      let allMonthEvents = [];
      
      // Percorrer todos os dias do mês atual
      const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
      
      for (let day = 1; day <= daysInMonth; day++) {
        const dateKey = this.getDateKey(currentYear, currentMonth, day);
        const dayEvents = this.events[dateKey] || [];
        
        // Adicionar informação da data a cada evento
        dayEvents.forEach(event => {
          allMonthEvents.push({
            ...event,
            date: `${day}/${currentMonth + 1}`,
            fullDate: dateKey
          });
        });
      }
      
      console.log('📅 Total de eventos no mês:', allMonthEvents.length);
      
      // Garantir que eventsPanel ainda existe
      if (!document.getElementById('eventsPanel')) {
        console.error('❌ Painel foi removido durante a execução');
        return;
      }
      
      eventsPanel.innerHTML = '';

      if (allMonthEvents.length === 0) {
        eventsPanel.innerHTML = '<div class="no-events">📅 Nenhum evento neste mês</div>';
      } else {
        // Ordenar eventos por data
        allMonthEvents.sort((a, b) => new Date(a.fullDate) - new Date(b.fullDate));
        
        allMonthEvents.forEach((event, index) => {
          console.log(`🔍 Processando evento ${index + 1}:`, event);
          const eventElement = this.createEventElement(event);
          if (eventElement && document.getElementById('eventsPanel')) {
            eventsPanel.appendChild(eventElement);
          }
        });
      }
      
      console.log('✅ Todos os eventos do mês renderizados com sucesso');
    } catch (error) {
      console.error('❌ Erro ao renderizar eventos:', error);
      const panel = document.getElementById('eventsPanel');
      if (panel) {
        panel.innerHTML = '<div class="no-events">⚠️ Erro ao carregar eventos</div>';
      }
    }
  }

  createEventElement(event) {
    if (!event) {
      console.error('Evento inválido');
      return null;
    }

    try {
      const eventDiv = document.createElement('div');
      eventDiv.className = `event-item ${event.urgent ? 'urgent' : ''}`;

      // Adicionar data se disponível
      if (event.date) {
        const dateDiv = document.createElement('div');
        dateDiv.className = 'event-date';
        dateDiv.textContent = `📅 ${event.date}`;
        eventDiv.appendChild(dateDiv);
      }

      const titleDiv = document.createElement('div');
      titleDiv.className = 'event-title';
      titleDiv.textContent = event.title || 'Sem título';

      const timeDiv = document.createElement('div');
      timeDiv.className = 'event-time';
      timeDiv.textContent = event.time || 'Sem horário';

      eventDiv.appendChild(titleDiv);
      eventDiv.appendChild(timeDiv);

      // Adicionar evento de clique para mais detalhes
      eventDiv.addEventListener('click', () => this.showEventDetails(event));

      return eventDiv;
    } catch (error) {
      console.error('Erro ao criar elemento do evento:', error);
      return null;
    }
  }

  showEventDetails(event) {
    if (!event) return;

    const message = `Evento: ${event.title}\nHorário: ${event.time}\nDescrição: ${event.description || 'Sem descrição'}`;
    alert(message);
  }

  getDateKey(year, month, day) {
    return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  }

  loadEvents() {
    console.log('📚 Carregando eventos do localStorage...');
    try {
      const savedEvents = localStorage.getItem('calendarEvents');
      this.events = savedEvents ? JSON.parse(savedEvents) : {};
      console.log('✅ Eventos carregados do localStorage:', this.events);
    } catch (error) {
      console.error('❌ Erro ao carregar eventos do localStorage:', error);
      this.events = {};
    }
  }

  saveEvents() {
    localStorage.setItem('calendarEvents', JSON.stringify(this.events));
  }

  addEvent(date, event) {
    const dateKey = this.getDateKey(date.getFullYear(), date.getMonth(), date.getDate());
    if (!this.events[dateKey]) {
      this.events[dateKey] = [];
    }
    this.events[dateKey].push(event);
    this.saveEvents();
    this.render();
  }

  loadSampleEvents() {
    // Função desabilitada - não adiciona mais eventos de exemplo
    console.log('📝 loadSampleEvents desabilitada - eventos de exemplo não serão criados');
    return;
    
    // Código comentado para não criar eventos automáticos
    /*
    // Adicionar alguns eventos de exemplo apenas se não houver eventos salvos
    if (Object.keys(this.events).length === 0) {
      const today = new Date();
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);

      this.addEvent(today, {
        title: '📚 Reunião Pedagógica',
        time: '14:00',
        description: 'Reunião mensal com coordenadores',
        urgent: false
      });

      this.addEvent(tomorrow, {
        title: '🎓 Apresentação TCC',
        time: '09:30',
        description: 'Defesa dos trabalhos de conclusão',
        urgent: true
      });

      console.log('Eventos de exemplo carregados');
    }
    */
  }
}

// Função global para adicionar novos eventos
// Função para abrir o modal de adicionar evento
function addNewEvent() {
  const modal = document.getElementById('eventModalOverlay');
  const eventDateInput = document.getElementById('eventDate');
  const eventTimeInput = document.getElementById('eventTime');
  
  // Usar data selecionada ou data atual
  const selectedDate = window.calendar && window.calendar.selectedDate ? 
    window.calendar.selectedDate : new Date();
  
  // Definir data padrão no input
  const dateStr = selectedDate.toISOString().split('T')[0];
  eventDateInput.value = dateStr;
  
  // Definir horário atual
  const now = new Date();
  const timeStr = now.toTimeString().slice(0, 5);
  eventTimeInput.value = timeStr;
  
  // Abrir modal
  modal.classList.add('active');
  
  // Focar no campo de título
  setTimeout(() => {
    document.getElementById('eventTitle').focus();
  }, 300);
}

// Função para fechar o modal
function closeEventModal() {
  const modal = document.getElementById('eventModalOverlay');
  modal.classList.remove('active');
  
  // Limpar formulário
  document.getElementById('eventForm').reset();
}

// Função para criar o evento do formulário
function createEventFromForm(formData) {
  try {
    if (!window.calendar) {
      console.error('❌ Calendário não inicializado');
      alert('Erro: Calendário não está disponível. Recarregue a página.');
      return false;
    }
    
    const title = formData.get('title')?.trim();
    if (!title) {
      alert('⚠️ Por favor, digite um título para o evento.');
      return false;
    }
    
    const eventDate = new Date(formData.get('date') + 'T' + formData.get('time'));
    const eventType = formData.get('type');
    
    // Mapear tipos de evento para emojis
    const typeEmojis = {
      'meeting': '📋',
      'class': '📚',
      'exam': '📝',
      'event': '🎉',
      'holiday': '🏖️',
      'other': '📌'
    };
    
    const newEvent = {
      title: title,
      description: formData.get('description')?.trim() || '',
      time: formData.get('time'),
      type: eventType,
      emoji: typeEmojis[eventType] || '📌',
      created: new Date().toISOString()
    };
    
    window.calendar.addEvent(eventDate, newEvent);
    console.log('✅ Evento criado com sucesso:', newEvent);
    
    // Fechar modal
    closeEventModal();
    
    // Mostrar confirmação
    showEventConfirmation(newEvent);
    
    return true;
  } catch (error) {
    console.error('❌ Erro ao criar evento:', error);
    alert('Erro ao criar evento. Tente novamente.');
    return false;
  }
}

// Função para mostrar confirmação de evento criado
function showEventConfirmation(event) {
  // Criar elemento de notificação
  const notification = document.createElement('div');
  notification.className = 'event-notification';
  notification.innerHTML = `
    <div class="notification-content">
      ${event.emoji} <strong>${event.title}</strong> foi criado!
      <div class="notification-details">📅 ${event.time}</div>
    </div>
  `;
  
  // Adicionar estilos inline
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    z-index: 10000;
    animation: slideInRight 0.3s ease;
    max-width: 300px;
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.2);
  `;
  
  document.body.appendChild(notification);
  
  // Remover após 3 segundos
  setTimeout(() => {
    notification.style.animation = 'slideOutRight 0.3s ease';
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Atalho de teclado Ctrl+N para adicionar evento
document.addEventListener('keydown', function(e) {
  if (e.ctrlKey && e.key === 'n') {
    e.preventDefault();
    addNewEvent();
  }
});

// Event listeners para o modal
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar calendário
  if (typeof window.calendar === 'undefined') {
    console.log('Criando instância do calendário...');
    window.calendar = new ModernCalendar();
  }
  
  // Event listeners do modal
  const modal = document.getElementById('eventModalOverlay');
  const closeBtn = document.getElementById('modalCloseBtn');
  const cancelBtn = document.getElementById('cancelEventBtn');
  const eventForm = document.getElementById('eventForm');
  
  // Fechar modal ao clicar no X
  if (closeBtn) {
    closeBtn.addEventListener('click', closeEventModal);
  }
  
  // Fechar modal ao clicar em Cancelar
  if (cancelBtn) {
    cancelBtn.addEventListener('click', closeEventModal);
  }
  
  // Fechar modal ao clicar no overlay (fundo)
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeEventModal();
      }
    });
  }
  
  // Fechar modal com tecla ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
      closeEventModal();
    }
  });
  
  // Submeter formulário
  if (eventForm) {
    eventForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(eventForm);
      createEventFromForm(formData);
    });
  }
});