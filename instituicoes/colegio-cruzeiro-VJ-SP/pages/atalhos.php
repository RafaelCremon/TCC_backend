<?php
session_start();

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
  // Só mostra "Usuários" para classe 1 ou 2
  if ($classe === 1 || $classe === 2) {
    $opcoes[] = [
      'id' => 'usuarios',
      'nome' => 'Usuários',
      'icone' => '<svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.5-1.8 4.5-4.5S14.7 3 12 3 7.5 4.8 7.5 7.5 9.3 12 12 12zm0 2c-3 0-9 1.5-9 4.5V21h18v-2.5c0-3-6-4.5-9-4.5z"/></svg>',
      'link' => '#',
    ];
  }
  $opcoes[] = [
    'id' => 'preferencias',
    'nome' => 'Preferências',
    'icone' => '<svg class="sidebar-icon" viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c.04.32.07.65.07.98s-.03.66-.07.98l2.11 1.65c.19.15.24.42.12.64l-2 3.46c-.12.22-.39.3-.61.22l-2.49-1c-.52.4-1.08.73-1.69.98l-.38 2.65c-.03.24-.24.42-.49.42h-4c-.25 0-.46-.18-.49-.42l-.38-2.65c-.61-.25-1.17-.59-1.69-.98l-2.49 1c-.23-.09-.49 0-.61-.22l-2-3.46c-.12-.22-.07-.49.12-.64l2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>',
    'link' => '#',
  ];
  return $opcoes;
}

$opcoes = get_opcoes_atalhos_padrao();
$atalhos_usuario = isset($_SESSION['atalhos_usuario']) ? $_SESSION['atalhos_usuario'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atalhos'])) {
  $selecionados = json_decode($_POST['atalhos'], true);
  // Garante no máximo 4 e sem duplicados
  $selecionados = array_slice(array_unique($selecionados), 0, 4);
  $_SESSION['atalhos_usuario'] = $selecionados;
  $atalhos_usuario = $selecionados;

  if (isset($_SESSION['usuario_id'])) {
    require_once '../../../includes/db.php'; // ajuste o caminho se necessário
    $atalhos_json = json_encode($selecionados);
    $stmt = $pdo->prepare("UPDATE usuarios SET atalhos = :atalhos WHERE id = :id");
    $stmt->bindParam(':atalhos', $atalhos_json);
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();
  }
  // Redireciona para a tela inicial após salvar
  header('Location: inicial.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Escolher Atalhos</title>
  <link rel="stylesheet" href="../css/inicial.css?v=<?php echo time(); ?>">
  <style>
    .btn-voltar-inicial {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: linear-gradient(90deg, #eaf2ff 0%, #dbe7ff 100%);
      color: #2e3192;
      border: none;
      border-radius: 10px;
      padding: 10px 22px;
      font-size: 1.08rem;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(44,92,255,0.10);
      margin-bottom: 22px;
      margin-top: 18px;
      transition: background 0.22s, color 0.22s, box-shadow 0.22s, transform 0.18s;
    }
    .btn-voltar-inicial:hover, .btn-voltar-inicial:focus {
      background: linear-gradient(90deg, #dbe7ff 0%, #eaf2ff 100%);
      color: #0057ff;
      box-shadow: 0 4px 16px #5b8cff22;
      transform: scale(1.06);
    }
    .btn-voltar-inicial svg {
      width: 22px;
      height: 22px;
      vertical-align: middle;
      transition: filter 0.22s, transform 0.18s;
    }
    .btn-voltar-inicial:hover svg, .btn-voltar-inicial:focus svg {
      filter: drop-shadow(0 2px 8px #5b8cff33);
      transform: scale(1.13);
    }
  </style>
  <style>
    body {
      background: var(--bg-main, #f7faff);
      color: var(--text-main, #222);
      transition: background 0.3s, color 0.3s;
    }
    .atalhos-container {
      max-width: 420px;
      margin: 24px auto;
      background: var(--bg-card, #f7faff);
      border-radius: 16px;
      padding: 18px 12px 18px 12px;
      box-shadow: 0 2px 18px rgba(44,92,255,0.12);
      text-align: center;
      transition: background 0.3s;
    }
    .atalhos-slots {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-bottom: 18px;
      min-height: 90px;
    }
    .slot {
      width: 80px;
      height: 80px;
      border: 2px dashed var(--border-slot, #b2c6e6);
      border-radius: 12px;
      background: var(--bg-slot, #fff);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 2px;
      position: relative;
      transition: border 0.2s, background 0.3s;
      box-shadow: 0 2px 8px rgba(44,92,255,0.06);
    }
    .slot.dragover {
      border: 2.5px solid #5b8cff;
      background: #eaf2ff;
    }
    .slot .atalho-card {
      position: static;
      margin: 0;
      cursor: grab;
    }
    .atalhos-list {
      display: flex;
      flex-wrap: wrap;
      gap: 22px;
      justify-content: center;
      margin-bottom: 24px;
    }
    .atalho-card {
      background: var(--bg-slot, #fff);
      border: 2px solid #c7d6ff;
      border-radius: 10px;
      padding: 10px 4px 6px 4px;
      width: 80px;
      min-height: 60px;
      cursor: grab;
      transition: border 0.2s, box-shadow 0.2s, background 0.3s;
      box-shadow: 0 2px 10px rgba(44,92,255,0.08);
      display: flex;
      flex-direction: column;
      align-items: center;
      user-select: none;
      position: relative;
      margin-bottom: 0;
    }
    .atalho-card.selected {
      border: 2.5px solid #5b8cff;
      box-shadow: 0 4px 18px rgba(44,92,255,0.15);
      background: linear-gradient(120deg, #eaf2ff 0%, #f7faff 100%);
    }
  .atalho-card svg { width: 28px; height: 28px; margin-bottom: 6px;}
  .atalho-card .nome { font-size: 13px; font-weight: 600; color: #2e3192;}
    .btn-salvar {
      background: linear-gradient(135deg, #5b8cff 0%, #2e3192 100%);
      color: #fff; border: none; border-radius: 10px;
      padding: 12px 34px; font-size: 17px; font-weight: 600; cursor: pointer;
      margin-top: 14px;
      box-shadow: 0 2px 8px rgba(44,92,255,0.10);
      transition: background 0.3s;
    }
    .btn-salvar:hover {
      filter: brightness(1.08);
    }
    .msg { color: #008a00; font-weight: 500; margin-bottom: 18px;}
    .limite { color: #d90000; font-size: 15px; margin-bottom: 12px;}
    .slot-label {
      position: absolute;
      top: 6px;
      left: 12px;
      font-size: 13px;
      color: #5b8cff;
      font-weight: 600;
      opacity: 0.7;
      letter-spacing: 0.02em;
    }
    /* Dark mode */
    body.dark {
      --bg-main: #181c2a;
      --bg-card: #232846;
      --bg-slot: #232846;
      --text-main: #eaf2ff;
      --border-slot: #3b4a7a;
    }
    body.dark .atalho-card .nome,
    body.dark .slot-label { color: #8bb6ff; }
    body.dark .atalho-card { border-color: #3b4a7a; }
    body.dark .atalho-card.selected { background: linear-gradient(120deg, #2e3192 0%, #232846 100%);}
    body.dark .slot { border-color: #3b4a7a; background: #232846; }
    body.dark .atalhos-container { background: #232846; }
    body.dark .btn-salvar { background: linear-gradient(135deg, #5b8cff 0%, #232846 100%);}
  </style>
</head>
<body>
  <form class="atalhos-container" method="post" onsubmit="return salvarAtalhos()">
  <!-- Botão de voltar simplificado -->
          <button onclick="window.location.href='inicial.php'" class="voltar-btn" style="margin-bottom: 18px; margin-top: 0; background: #e7eefe; color: #2e3192; border: none; border-radius: 8px; padding: 6px 14px; box-shadow: 0 2px 8px rgba(44,92,255,0.08); cursor: pointer; font-size: 22px; font-weight: bold; transition: background 0.2s, color 0.2s; display: flex; align-items: center; justify-content: center;">&lt;</button>
    <h2>Arraste até 4 atalhos para os slots</h2>
    <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>
    <div class="atalhos-slots" id="slots">
      <?php
      // Preenche os slots com os atalhos do usuário, se houver
      for ($i = 0; $i < 4; $i++) {
        $id = isset($atalhos_usuario[$i]) ? $atalhos_usuario[$i] : '';
        echo '<div class="slot" data-slot="'.$i.'" ondragover="dragOver(event)" ondrop="dropAtalho(event,'.$i.')" ondragleave="dragLeave(event)">';
        echo '<span class="slot-label">Slot '.($i+1).'</span>';
        if ($id) {
          foreach ($opcoes as $op) {
            if ($op['id'] === $id) {
              echo '<div class="atalho-card selected" draggable="true" data-id="'.$op['id'].'" ondragstart="dragStart(event)">'.$op['icone'].'<div class="nome">'.$op['nome'].'</div></div>';
            }
          }
        }
        echo '</div>';
      }
      ?>
    </div>
    <div style="margin: 22px 0 10px 0; font-weight:500; color:#2e3192;">Arraste os atalhos abaixo para os slots acima:</div>
    <div class="atalhos-list" id="atalhosList">
      <?php foreach ($opcoes as $op): ?>
        <?php if (!in_array($op['id'], $atalhos_usuario)): ?>
          <div class="atalho-card" draggable="true" data-id="<?php echo $op['id']; ?>" ondragstart="dragStart(event)">
            <?php echo $op['icone']; ?>
            <div class="nome"><?php echo $op['nome']; ?></div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <input type="hidden" name="atalhos" id="atalhosInput" value="">
    <button class="btn-salvar" type="submit">Salvar</button>
    <div style="margin-top:18px;">
      
    </div>
  </form>
  <script>
    // Tema: acompanha o inicial.php (usa localStorage 'theme')
    function aplicarTema() {
      const tema = localStorage.getItem('theme');
      if (tema === 'dark') document.body.classList.add('dark');
      else document.body.classList.remove('dark');
    }
    aplicarTema();
    window.addEventListener('storage', aplicarTema);


    let draggedId = null;
    let draggedFromSlot = null;
    let draggedFromHTML = null;
    let dropSuccess = false;

    function dragStart(e) {
      draggedId = e.target.getAttribute('data-id');
      dropSuccess = false;
      // Descobre de qual slot veio
      let parentSlot = e.target.closest('.slot');
      if (parentSlot) {
        draggedFromSlot = parentSlot;
        draggedFromHTML = parentSlot.innerHTML;
      } else {
        draggedFromSlot = null;
        draggedFromHTML = null;
      }
      e.dataTransfer.effectAllowed = "move";
    }
    function dragOver(e) {
      e.preventDefault();
      e.currentTarget.classList.add('dragover');
    }
    function dragLeave(e) {
      e.currentTarget.classList.remove('dragover');
    }
    function dropAtalho(e, slotIdx) {
      e.preventDefault();
      e.currentTarget.classList.remove('dragover');
      if (!draggedId) return;
      dropSuccess = true;

      // Remove o atalho deste slot se já houver
      let slots = document.querySelectorAll('.slot');
      slots.forEach((slot, idx) => {
        let card = slot.querySelector('.atalho-card');
        if (card && card.getAttribute('data-id') === draggedId) {
          slot.innerHTML = '<span class="slot-label">Slot ' + (idx+1) + '</span>';
        }
      });

      // Remove o atalho da lista de baixo se estiver lá
      let cards = document.querySelectorAll('.atalhos-list .atalho-card');
      cards.forEach(card => {
        if (card.getAttribute('data-id') === draggedId) {
          card.parentNode.removeChild(card);
        }
      });

      // Adiciona o atalho ao slot
      let opcoes = <?php echo json_encode($opcoes); ?>;
      let op = opcoes.find(o => o.id === draggedId);
      if (op) {
        e.currentTarget.innerHTML = '<span class="slot-label">Slot ' + (slotIdx+1) + '</span>' +
          '<div class="atalho-card selected" draggable="true" data-id="'+op.id+'" ondragstart="dragStart(event)">' +
          op.icone + '<div class="nome">'+op.nome+'</div></div>';
      }
      draggedId = null;
      draggedFromSlot = null;
      draggedFromHTML = null;
    }

    // Se soltar fora de qualquer slot, remove do slot e volta para a lista
    document.addEventListener('dragend', function(e) {
      if (draggedId && draggedFromSlot && !dropSuccess) {
        // Remove o atalho do slot
        draggedFromSlot.innerHTML = '<span class="slot-label">' + draggedFromSlot.querySelector('.slot-label').textContent + '</span>';
        // Adiciona de volta na lista de atalhos disponíveis
        let opcoes = <?php echo json_encode($opcoes); ?>;
        let op = opcoes.find(o => o.id === draggedId);
        if (op) {
          let atalhosList = document.getElementById('atalhosList');
          let div = document.createElement('div');
          div.className = 'atalho-card';
          div.setAttribute('draggable', 'true');
          div.setAttribute('data-id', op.id);
          div.setAttribute('ondragstart', 'dragStart(event)');
          div.innerHTML = op.icone + '<div class="nome">' + op.nome + '</div>';
          atalhosList.appendChild(div);
        }
      }
      draggedId = null;
      draggedFromSlot = null;
      draggedFromHTML = null;
      dropSuccess = false;
    });

    function salvarAtalhos() {
      // Pega os atalhos dos slots na ordem
      let slots = document.querySelectorAll('.slot');
      let atalhos = [];
      slots.forEach(slot => {
        let card = slot.querySelector('.atalho-card');
        if (card) atalhos.push(card.getAttribute('data-id'));
      });
      document.getElementById('atalhosInput').value = JSON.stringify(atalhos);
      return true;
    }
  </script>
</body>
</html>
