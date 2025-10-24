<?php
// debug_path.php
$arquivo = 'uploads/avisos/aviso_68fab9f499eff.jpg';
$baseDir = realpath(__DIR__ . '/../../../');
echo "baseDir: $baseDir\n";
echo "file: $arquivo\n";
$fullPath = realpath($baseDir . '/' . $arquivo);
echo "fullPath: $fullPath\n";
if (!$fullPath) {
    echo "realpath retornou false\n";
} else {
    echo "file_exists: ".(file_exists($fullPath) ? 'sim' : 'nao')."\n";
}
