<?php
// db.php

$host = 'localhost'; // Endereço do servidor MySQL
$dbname = 'quantumdb'; // Nome do seu banco de dados
$username = 'root'; // Nome de usuário do MySQL
$password = ''; // Senha do usuário

try {
    // Cria uma instância PDO e tenta conectar
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Define o modo de erro para exceção (facilita o tratamento de erros)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se a conexão for bem-sucedida, você pode descomentar a linha abaixo para depuração
    // echo "Conexão com o banco de dados realizada com sucesso!";
    
} catch (PDOException $e) {
    // Se ocorrer um erro, exibe a mensagem
    echo "Erro na conexão: " . $e->getMessage();
    die(); // Interrompe o script caso a conexão falhe
}
?>
