// splash.js
// Data de lançamento (ano, mês-1, dia, hora, min, seg)
const launchDate = new Date(2025, 9, 31, 0, 0, 0); // 31 de outubro de 2025

const progressBar = document.getElementById('progress');
const percentText = document.getElementById('percent');


// Calcula a porcentagem real baseada no tempo decorrido
const startDate = new Date(2025, 9, 1, 0, 0, 0); // Data de início: 1º de outubro de 2025

function animateProgress() {
    const now = new Date();
    const total = launchDate - startDate;
    const elapsed = now - startDate;
    let percent = Math.floor((elapsed / total) * 100);
    if (percent < 0) percent = 0;
    if (percent > 100) percent = 100;
    progressBar.style.width = percent + '%';
    percentText.textContent = percent + '%';
    if (percent >= 100) {
        setTimeout(() => {
            document.querySelector('.splash-bg').style.display = 'none';
        }, 1000);
        return;
    }
    setTimeout(animateProgress, 1200);
}

animateProgress();
