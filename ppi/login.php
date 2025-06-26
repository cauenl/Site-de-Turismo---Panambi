<?php

if (isset($_POST['submit'])) {
  // print_r('Nome: ' . $_POST['nome']);
  // print_r('<br>');
  // print_r('Email: ' . $_POST['email']);
  // print_r('<br>');
  // print_r('Telefone: ' . $_POST['telefone']);
  // print_r('<br>');
  // print_r('Senha: ' . $_POST['senha']);
  // print_r('<br>');
  // print_r('Confirma Senha: ' . $_POST['confirma_senha']);

  include_once('./php/config.php');

  $nome = $_POST['nome'] ?? '';
  $email = $_POST['email'];
  $sql = "SELECT id FROM usuario WHERE email = ?";
  $stmt = $conexao->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    echo "E-mail já cadastrado!";
    exit;
  }
  $stmt->close();
  $telefone = $_POST['telefone'] ?? '';
  $senha = $_POST['senha'];
  $confirma_senha = $_POST['confirma_senha'];

  if ($senha != $confirma_senha) {
    $erro = "As senhas devem ser iguais!";
  }

  $result = mysqli_query($conexao, "INSERT INTO usuario (nome, email, telefone, senha) VALUES ('$nome', '$email', '$telefone', '$senha')");
}

if (isset($_POST['submitLogin'])) {
  include_once('./php/config.php');

  $email = $_POST['email'];
  $senha = $_POST['senha'];

  // Verifica se o usuário existe e a senha está correta
  $sql = "SELECT id, nome FROM usuario WHERE email = ? AND senha = ?";
  $stmt = $conexao->prepare($sql);
  $stmt->bind_param("ss", $email, $senha);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    session_start();
    $usuario = $result->fetch_assoc();
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nome'] = $usuario['nome'];

    // Verifica se é admin
    require_once './php/config.php';
    require_once './php/admin_functions.php';
    if (isAdmin($conexao, $usuario['id'])) {
        header('Location: admin.php');
    } else {
        header('Location: index.php');
    }
    exit;
  } else {
    $erro = "Email ou senha incorretos!";
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal de Turismo - Login</title>
  <link rel="stylesheet" href="./css/login.css"> <!-- Ajuste o caminho do CSS -->
  <script src="https://kit.fontawesome.com/cf6fa412bd.js" crossorigin="anonymous"></script>
</head>

<body>
  <!-- Logo -->
  <div class="img">
    <img src="./img/escudo2.png" alt="Logo">
  </div> <!-- Ajuste o caminho da imagem -->

  <!-- Container do formulário -->
  <div class="container">
    <div class="buttonsForm">
      <div class="btnColor"></div>
      <button id="btnSignin">Login</button>
      <button id="btnSignup">Cadastre-se</button>
    </div>

    <!-- Formulário de Login -->
    <form id="signin" method="post" action="login.php">
      <label>
        <input type="email" name="email" placeholder="E-mail" required autocomplete="email">
        <i class="fas fa-envelope iEmail"></i>
      </label>

      <label>
        <input type="password" name="senha" placeholder="Senha" required autocomplete="current-password">
        <i class="fas fa-lock iPassword"></i>
      </label>

      <div class="divCheck">
        <input type="checkbox" name="lembrar">
        <span>Lembrar senha</span>
      </div>

      <button type="submit" name="submitLogin">Login</button>

      <?php if (!empty($erro)) {
        echo "<p class='erro'>$erro</p>";
      } ?>
    </form>


    <!-- Formulário de Cadastro (opcional, mantive igual ao original) -->
    <form id="signup" method="POST" action="login.php">
      <label>
        <input type="text" name="nome" placeholder="Nome" required autocomplete="name">
        <i class="fas fa-user iName"></i>
      </label>

      <label>
        <input type="email" name="email" placeholder="E-mail" required autocomplete="email">
        <i class="fas fa-envelope iEmail"></i>
      </label>

      <label>
        <input type="tel" name="telefone" placeholder="Telefone" required autocomplete="tel">
        <i class="fas fa-phone iPhone"></i>
      </label>

      <label>
        <input type="password" name="senha" placeholder="Senha" required autocomplete="new-password">
        <i class="fas fa-lock iPassword"></i>
      </label>

      <label>
        <input type="password" name="confirma_senha" placeholder="Repita sua senha" required
          autocomplete="new-password">
        <i class="fas fa-lock iPassword2"></i>
      </label>

      <div class="divCheck">
        <input type="checkbox" name="termos" required>
        <span>Li e aceito os termos de serviço</span>
      </div>

      <button type="submit" name="submit">Cadastre-se</button>
    </form>

  </div>

  <!-- Script JS (ajuste o caminho se necessário) -->
  <script src="./js/main.js"></script>
</body>

</html>