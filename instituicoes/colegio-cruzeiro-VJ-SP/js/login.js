// Garante que o código só execute se o elemento existir
const bolhas = document.getElementById("bolhas");
const bubbleSize = 20;
const maxScale = 1.4;
const gap = bubbleSize * (maxScale - 1); // 8px

function criarBolhas() {
  if (!bolhas) return;
  bolhas.innerHTML = "";

  const width = window.innerWidth;
  const height = window.innerHeight;

  const cols = Math.ceil(width / (bubbleSize + gap));
  const rows = Math.ceil(height / (bubbleSize + gap));
  const total = rows * cols;
  const maxDistance = cols + rows;

  bolhas.style.gridTemplateColumns = `repeat(${cols}, ${bubbleSize}px)`;
  bolhas.style.gridAutoRows = `${bubbleSize}px`;
  bolhas.style.gap = `${gap}px`;

  for (let i = 0; i < total; i++) {
    const li = document.createElement("li");
    const row = Math.floor(i / cols);
    const col = i % cols;
    const delay = (row + col) * 0.05;

    const distance = col + row;
    // Escala base: menor no canto superior esquerdo (distance=0), maior no inferior direito (distance=maxDistance)
    const scaleBase = 0.4 + (distance / maxDistance) * 1.0; // varia de 0.4 a 1.4

    li.style.animationDelay = `${delay}s`;

    bolhas.appendChild(li);
  }
}

if (bolhas) {
  criarBolhas();
  window.addEventListener("resize", criarBolhas);
}

// Função que vai esconder a mensagem de erro ao digitar em qualquer campo
function limparErro() {
  const erroMensagem = document.getElementById("erroMensagem");
  if (erroMensagem) {
    erroMensagem.textContent = "";  // Limpa o texto da mensagem de erro
  }
}

document.addEventListener("DOMContentLoaded", function() {
  const usuario = document.getElementById("usuario");
  const senha = document.getElementById("senha");
  if (usuario) usuario.addEventListener("input", limparErro);
  if (senha) senha.addEventListener("input", limparErro);
});
