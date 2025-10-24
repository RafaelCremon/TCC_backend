<?php
// download.php?file=uploads/avisos/aviso_xxx.ext
if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('Arquivo não especificado.');
}
$file = ltrim($_GET['file'], '/\\'); // Remove barra inicial se houver
$baseDir = realpath(__DIR__ . '/../../../uploads/avisos');
$fullPath = $baseDir . DIRECTORY_SEPARATOR . basename($file);
if (!file_exists($fullPath)) {
    http_response_code(404);
    exit('Arquivo não encontrado. Caminho: ' . htmlspecialchars($file));
}
$filename = basename($fullPath);
$mime = mime_content_type($fullPath);
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;
