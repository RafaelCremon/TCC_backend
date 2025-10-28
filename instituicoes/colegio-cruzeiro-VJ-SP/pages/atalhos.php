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
  $selecionados = array_slice(array_unique($selecionados), 0, 4);
  $_SESSION['atalhos_usuario'] = $selecionados;
  $atalhos_usuario = $selecionados;

  if (isset($_SESSION['usuario_id'])) {
    require_once '../../../includes/db.php';
    $atalhos_json = json_encode($selecionados);
    $insert = $pdo->prepare("INSERT IGNORE INTO plataforma (usuario_id) VALUES (:id)");
    $insert->bindParam(':id', $_SESSION['usuario_id']);
    $insert->execute();
    $stmt = $pdo->prepare("UPDATE plataforma SET atalhos = :atalhos WHERE usuario_id = :id");
    $stmt->bindParam(':atalhos', $atalhos_json);
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();
  }
  header('Location: inicial.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personalizar Atalhos</title>
  <link rel="stylesheet" href="../css/inicial.css?v=<?php echo time(); ?>">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      padding: 20px;
      transition: background 0.3s ease;
    }

    body.dark {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
      animation: fadeInDown 0.6s ease;
    }

    .voltar-btn {
      position: absolute;
      top: 30px;
      left: 30px;
      width: 48px;
      height: 48px;
      background: rgba(255, 255, 255, 0.95);
      border: none;
      border-radius: 50%;
      font-size: 24px;
      font-weight: bold;
      color: #667eea;
      cursor: pointer;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(10px);
    }

    .voltar-btn:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
      background: white;
    }

    body.dark .voltar-btn {
      background: rgba(30, 30, 50, 0.95);
      color: #8b9cff;
    }

    h1 {
      font-size: 2.5rem;
      color: white;
      margin-bottom: 10px;
      text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
    }

    .subtitle {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.1rem;
      font-weight: 400;
    }

    .slots-section {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 24px;
      padding: 40px;
      margin-bottom: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(10px);
      animation: fadeInUp 0.6s ease;
    }

    body.dark .slots-section {
      background: rgba(30, 30, 50, 0.95);
    }

    .section-title {
      font-size: 1.3rem;
      color: #667eea;
      margin-bottom: 25px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    body.dark .section-title {
      color: #8b9cff;
    }

    .section-title::before {
      content: '';
      width: 4px;
      height: 24px;
      background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
      border-radius: 2px;
    }

    .atalhos-slots {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }

    .slot {
      aspect-ratio: 1;
      border: 3px dashed #d0d7ff;
      border-radius: 20px;
      background: linear-gradient(135deg, #f8f9ff 0%, #f0f3ff 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
      transition: all 0.3s ease;
      overflow: hidden;
    }

    body.dark .slot {
      background: linear-gradient(135deg, #1e1e32 0%, #252540 100%);
      border-color: #3a3a5a;
    }

    .slot::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .slot:hover::before {
      opacity: 1;
    }

    .slot.dragover {
      border-color: #667eea;
      border-style: solid;
      background: linear-gradient(135deg, #eef1ff 0%, #e8ecff 100%);
      transform: scale(1.02);
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
    }

    body.dark .slot.dragover {
      background: linear-gradient(135deg, #2a2a4a 0%, #32325a 100%);
      border-color: #8b9cff;
    }

    .slot-label {
      position: absolute;
      top: 12px;
      left: 12px;
      font-size: 0.75rem;
      color: #667eea;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      opacity: 0.7;
    }

    body.dark .slot-label {
      color: #8b9cff;
    }

    .slot-empty-icon {
      font-size: 2.5rem;
      opacity: 0.2;
    }

    .atalho-card {
      background: white;
      border: 2px solid #e0e6ff;
      border-radius: 16px;
      padding: 20px;
      cursor: grab;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      user-select: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      width: 100%;
      height: 100%;
    }

    body.dark .atalho-card {
      background: #2a2a4a;
      border-color: #3a3a5a;
    }

    .atalho-card:active {
      cursor: grabbing;
    }

    .atalho-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(102, 126, 234, 0.2);
      border-color: #667eea;
    }

    .atalho-card.selected {
      border-color: #667eea;
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25);
      background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
    }

    body.dark .atalho-card.selected {
      background: linear-gradient(135deg, #2a2a4a 0%, #32325a 100%);
      border-color: #8b9cff;
    }

    .atalho-card svg {
      width: 40px;
      height: 40px;
      fill: #667eea;
      transition: transform 0.3s ease;
    }

    body.dark .atalho-card svg {
      fill: #8b9cff;
    }

    .atalho-card:hover svg {
      transform: scale(1.1);
    }

    .atalho-card .nome {
      font-size: 0.95rem;
      font-weight: 600;
      color: #2d3748;
      text-align: center;
    }

    body.dark .atalho-card .nome {
      color: #e0e6ff;
    }

    .atalhos-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 20px;
      padding: 20px 0;
    }

    .disponivel-card {
      aspect-ratio: 1;
    }

    .btn-salvar {
      width: 100%;
      max-width: 300px;
      margin: 30px auto 0;
      display: block;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 16px;
      padding: 18px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn-salvar:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
    }

    .btn-salvar:active {
      transform: translateY(0);
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      .container {
        padding: 10px;
      }

      h1 {
        font-size: 1.8rem;
      }

      .slots-section {
        padding: 25px 20px;
      }

      .atalhos-slots {
        grid-template-columns: repeat(2, 1fr);
      }

      .atalhos-list {
        grid-template-columns: repeat(2, 1fr);
      }

      .voltar-btn {
        top: 15px;
        left: 15px;
        width: 42px;
        height: 42px;
      }
    }
  </style>
</head>
<body>
  <button onclick="window.location.href='inicial.php'" class="voltar-btn" type="button">←</button>
  
  <div class="container">
    <div class="header">
      <h1>✨ Personalize seus Atalhos</h1>
      <p class="subtitle">Arraste e solte até 4 atalhos nos slots abaixo</p>
    </div>

    <form method="post" onsubmit="return salvarAtalhos()">
      <div class="slots-section">
        <div class="section-title">🎯 Seus Atalhos</div>
        <div class="atalhos-slots" id="slots">
          <?php
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
            } else {
              echo '<div class="slot-empty-icon">+</div>';
            }
            echo '</div>';
          }
          ?>
        </div>

        <div class="section-title" style="margin-top: 40px;">📱 Atalhos Disponíveis</div>
        <div class="atalhos-list" id="atalhosList">
          <?php foreach ($opcoes as $op): ?>
            <?php if (!in_array($op['id'], $atalhos_usuario)): ?>
              <div class="atalho-card disponivel-card" draggable="true" data-id="<?php echo $op['id']; ?>" ondragstart="dragStart(event)">
                <?php echo $op['icone']; ?>
                <div class="nome"><?php echo $op['nome']; ?></div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <input type="hidden" name="atalhos" id="atalhosInput" value="">
        <button class="btn-salvar" type="submit">Salvar Atalhos</button>
      </div>
    </form>
  </div>

  <script>
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
      let parentSlot = e.target.closest('.slot');
      if (parentSlot) {
        draggedFromSlot = parentSlot;
        draggedFromHTML = parentSlot.innerHTML;
      } else {
        draggedFromSlot = null;
        draggedFromHTML = null;
      }
      e.dataTransfer.effectAllowed = "move";
      e.target.style.opacity = '0.5';
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

      let slots = document.querySelectorAll('.slot');
      slots.forEach((slot, idx) => {
        let card = slot.querySelector('.atalho-card');
        if (card && card.getAttribute('data-id') === draggedId) {
          slot.innerHTML = '<span class="slot-label">Slot ' + (idx+1) + '</span><div class="slot-empty-icon">+</div>';
        }
      });

      let cards = document.querySelectorAll('.atalhos-list .atalho-card');
      cards.forEach(card => {
        if (card.getAttribute('data-id') === draggedId) {
          card.parentNode.removeChild(card);
        }
      });

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

    document.addEventListener('dragend', function(e) {
      e.target.style.opacity = '1';
      if (draggedId && draggedFromSlot && !dropSuccess) {
        draggedFromSlot.innerHTML = '<span class="slot-label">' + draggedFromSlot.querySelector('.slot-label').textContent + '</span><div class="slot-empty-icon">+</div>';
        let opcoes = <?php echo json_encode($opcoes); ?>;
        let op = opcoes.find(o => o.id === draggedId);
        if (op) {
          let atalhosList = document.getElementById('atalhosList');
          let div = document.createElement('div');
          div.className = 'atalho-card disponivel-card';
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