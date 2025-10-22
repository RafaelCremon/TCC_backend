<?php
require_once '../../includes/db.php'; // Incluindo a conexão com o banco de dados

// Buscar as instituições cadastradas para o select
$query_inst = "SELECT id, nome FROM instituicoes";
$result_inst = $pdo->query($query_inst); // Usando $pdo ao invés de $conn

// Verificando se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $instituicao_id = $_POST['instituicao_id'];
    $nome = $_POST['nome'];
    $usuario = $_POST['usuario'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    // Criptografando a senha usando password_hash
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Inserção no banco de dados
    $query = "INSERT INTO usuarios (instituicao_id, nome, usuario, email, telefone, senha) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $instituicao_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $nome, PDO::PARAM_STR);
    $stmt->bindParam(3, $usuario, PDO::PARAM_STR);
    $stmt->bindParam(4, $email, PDO::PARAM_STR);
    $stmt->bindParam(5, $telefone, PDO::PARAM_STR);
    $stmt->bindParam(6, $senha, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "<div class='msg success'>Usuário cadastrado com sucesso!</div>";
    } else {
        echo "<div class='msg error'>Erro ao cadastrar usuário: " . $pdo->errorInfo()[2] . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário</title>
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
            max-width: 450px;
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
        select, input {
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
        <h2>Cadastrar Administrador</h2>
        <form action="cad-admin.php" method="POST">
            <label for="instituicao_id">Instituição:</label>
            <select name="instituicao_id" required>
                <option value="">Selecione a instituição</option>
                <?php while ($row = $result_inst->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nome'] ?></option>
                <?php endwhile; ?>
            </select>

            <label for="nome">Nome:</label>
            <input type="text" name="nome" required>

            <label for="usuario">Usuário:</label>
            <input type="text" name="usuario" required>

            <label for="email">E-mail:</label>
            <input type="email" name="email" required>

            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" required>

            <input type="submit" value="Cadastrar">
        </form>
    </div>

    <script>
        // Máscara para telefone
        var telefoneMask = new Inputmask('(99) 99999-9999');
        telefoneMask.mask(document.querySelector("#telefone"));
    </script>
</body>
</html>
