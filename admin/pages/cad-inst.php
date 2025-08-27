<?php
require_once '../../includes/db.php'; // Incluindo a conexão com o banco de dados

// Verificando se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $subdominio = $_POST['subdominio'];

    // Inserção no banco de dados
    $query = "INSERT INTO instituicoes (nome, cnpj, endereco, cidade, estado, cep, telefone, email, subdominio) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $nome, PDO::PARAM_STR);
    $stmt->bindParam(2, $cnpj, PDO::PARAM_STR);
    $stmt->bindParam(3, $endereco, PDO::PARAM_STR);
    $stmt->bindParam(4, $cidade, PDO::PARAM_STR);
    $stmt->bindParam(5, $estado, PDO::PARAM_STR);
    $stmt->bindParam(6, $cep, PDO::PARAM_STR);
    $stmt->bindParam(7, $telefone, PDO::PARAM_STR);
    $stmt->bindParam(8, $email, PDO::PARAM_STR);
    $stmt->bindParam(9, $subdominio, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "<div class='msg success'>Instituição cadastrada com sucesso!</div>";
    } else {
        echo "<div class='msg error'>Erro ao cadastrar instituição: " . $pdo->errorInfo()[2] . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Instituição</title>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.6/dist/inputmask.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0e1b4d, #2a5cff);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #fff;
        }
        .card {
            background: #ffffff15;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
            backdrop-filter: blur(10px);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 8px;
            border: none;
            outline: none;
        }
        input[type="submit"] {
            background: #ffd700;
            color: #0e1b4d;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }
        input[type="submit"]:hover {
            background: #ffcc00;
            transform: translateY(-2px);
        }
        .msg {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .msg.success { background: #28a745; }
        .msg.error { background: #dc3545; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Cadastrar Instituição</h2>
        <form action="cad-inst.php" method="POST">
            <label for="nome">Nome da Instituição:</label>
            <input type="text" name="nome" required>

            <label for="cnpj">CNPJ:</label>
            <input type="text" name="cnpj" id="cnpj" required>

            <label for="endereco">Endereço:</label>
            <input type="text" name="endereco" required>

            <label for="cidade">Cidade:</label>
            <input type="text" name="cidade" required>

            <label for="estado">Estado:</label>
            <input type="text" name="estado" required>

            <label for="cep">CEP:</label>
            <input type="text" name="cep" id="cep" required>

            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" required>

            <label for="email">E-mail:</label>
            <input type="email" name="email" required>

            <label for="subdominio">Subdomínio:</label>
            <input type="text" name="subdominio" required>

            <input type="submit" value="Cadastrar">
        </form>
    </div>

    <script>
        // Máscaras para CNPJ, telefone e CEP
        var cnpjMask = new Inputmask('99.999.999/9999-99');
        cnpjMask.mask(document.querySelector("#cnpj"));

        var telefoneMask = new Inputmask('(99) 99999-9999');
        telefoneMask.mask(document.querySelector("#telefone"));

        var cepMask = new Inputmask('99999-999');
        cepMask.mask(document.querySelector("#cep"));
    </script>
</body>
</html>
