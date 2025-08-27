<?php
require_once '../../includes/db.php'; // Incluindo a conexão com o banco de dados

// Buscar instituições
$query = "SELECT * FROM instituicoes";
$stmt = $pdo->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Instituições</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0e1b4d, #2a5cff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            color: #fff;
        }
        .container {
            background: #ffffff15;
            backdrop-filter: blur(10px);
            padding: 25px;
            margin: 40px;
            border-radius: 15px;
            width: 95%;
            max-width: 1200px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            overflow-x: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff10;
            border-radius: 10px;
            overflow: hidden;
        }
        thead {
            background: #ffd700;
            color: #0e1b4d;
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
        }
        tbody tr:nth-child(even) {
            background: rgba(255,255,255,0.05);
        }
        tbody tr:hover {
            background: rgba(255,255,255,0.15);
        }
        a.btn {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .edit {
            background: #28a745;
            color: #fff;
        }
        .edit:hover {
            background: #218838;
        }
        .delete {
            background: #dc3545;
            color: #fff;
        }
        .delete:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Listagem de Instituições</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Endereço</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>CEP</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Subdomínio</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['cnpj']) ?></td>
                        <td><?= htmlspecialchars($row['endereco']) ?></td>
                        <td><?= htmlspecialchars($row['cidade']) ?></td>
                        <td><?= htmlspecialchars($row['estado']) ?></td>
                        <td><?= htmlspecialchars($row['cep']) ?></td>
                        <td><?= htmlspecialchars($row['telefone']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['subdominio']) ?></td>
                        <td>
                            <a href="edit-inst.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn edit">Editar</a>
                            <a href="delete-inst.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn delete" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
