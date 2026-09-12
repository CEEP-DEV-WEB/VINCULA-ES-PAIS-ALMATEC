<?php

session_start();

require_once "../config/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    if (empty($email) || empty($senha)) {

        $mensagem = "Preencha todos os campos.";

    } else {

        $sql = "SELECT id, nome, email, senha, tipo
                FROM usuarios
                WHERE email = ?
                AND tipo = 'responsavel'
                LIMIT 1";

        $stmt = $conexao->prepare($sql);
        $stmt->execute([$email]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $senha === $usuario["senha"]) {

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            $_SESSION["usuario_tipo"] = $usuario["tipo"];

            header("Location: dashboard.php");
            exit;

        } else {

            $mensagem = "E-mail ou senha incorretos.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Responsável | Almatec</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <div class="login-container">

        <h1>Almatec</h1>

        <h2>Área do Responsável</h2>

        <?php if (!empty($mensagem)): ?>

            <p class="mensagem-erro">
                <?= htmlspecialchars($mensagem) ?>
            </p>

        <?php endif; ?>

        <form method="POST">

            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Digite seu e-mail"
                required
            >

            <label for="senha">
                Senha
            </label>

            <input
                type="password"
                id="senha"
                name="senha"
                placeholder="Digite sua senha"
                required
            >

            <button type="submit">
                Entrar
            </button>

        </form>

    </div>

</body>

</html>