<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel do Administrador</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #0e1b4d, #2a5cff);
      color: #fff;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      min-height: 100vh;
    }

    h1 {
      margin-top: 40px;
      font-size: 2rem;
      text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
    }

    .container {
      margin-top: 40px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      width: 80%;
      max-width: 900px;
    }

    .card {
      background: #ffffff10;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 15px;
      padding: 30px 20px;
      text-align: center;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
      cursor: pointer;
    }

    .card:hover {
      transform: translateY(-5px);
      background: #ffffff20;
    }

    .card i {
      font-size: 2.5rem;
      margin-bottom: 15px;
      color: #ffd700;
    }

    .card a {
      text-decoration: none;
      color: #fff;
      font-weight: bold;
      font-size: 1.1rem;
      display: block;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <h1>Administrar</h1>

  <div class="container">
    <div class="card">
      <i class="fas fa-user-plus"></i>
      <a href="pages/cad-admin.php">Cadastrar Administrador</a>
    </div>
    <div class="card">
      <i class="fas fa-school"></i>
      <a href="pages/cad-inst.php">Cadastrar Instituição</a>
    </div>
    <div class="card">
      <i class="fas fa-users"></i>
      <a href="pages/list-admin.php">Listar Administradores</a>
    </div>
    <div class="card">
      <i class="fas fa-building"></i>
      <a href="pages/list-inst.php">Listar Instituições</a>
    </div>
  </div>
</body>
</html>
