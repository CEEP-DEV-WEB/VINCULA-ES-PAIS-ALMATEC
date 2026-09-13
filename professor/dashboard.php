<?php

session_start();

require_once "../config/conexao.php";

/* Verifica login */
if (
    !isset($_SESSION['usuario_id']) ||
    $_SESSION['usuario_tipo'] !== 'professor'
) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/* Busca o professor */
$sql = "
    SELECT
        p.id AS professor_id,
        u.nome,
        u.email
    FROM professores p
    INNER JOIN usuarios u
        ON u.id = p.usuario_id
    WHERE p.usuario_id = ?
";

$stmt = $conexao->prepare($sql);
$stmt->execute([$usuario_id]);

$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    die("Professor não encontrado.");
}

$nome_professor = $professor['nome'];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Início | Professor | Almatec</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>


<!-- TOPO -->

<header class="topo">

    <div class="topo-conteudo">

        <div class="logo-area">

            <div class="logo-icon">
                A
            </div>

            <div>

                <h1>ALMATEC</h1>

                <span>
                    Educação
                </span>

            </div>

        </div>


        <div class="usuario-area">

            <div class="usuario-info">

                <span class="usuario-label">
                    Professor
                </span>

                <strong>
                    <?= htmlspecialchars($nome_professor) ?>
                </strong>

            </div>


            <div class="usuario-avatar">

                <?= strtoupper(
                    substr($nome_professor, 0, 1)
                ) ?>

            </div>

        </div>

    </div>

</header>


<!-- FAIXA -->

<div class="faixa-institucional">

    <div class="faixa-azul"></div>

    <div class="faixa-vermelha"></div>

    <div class="faixa-branca"></div>

</div>


<!-- MENU -->

<nav class="menu">

    <div class="menu-conteudo">

        <a
            href="dashboard.php"
            class="menu-link ativo"
        >
            Início
        </a>


        <a
            href="turmas.php"
            class="menu-link"
        >
            Minhas turmas
        </a>


        <a
            href="perfil.php"
            class="menu-link"
        >
            Meu perfil
        </a>


        <a
            href="logout.php"
            class="menu-sair"
        >
            Sair
        </a>

    </div>

</nav>


<!-- CONTEÚDO -->

<main class="container">


    <!-- BOAS-VINDAS -->

    <section class="boas-vindas">

        <div>

            <span class="subtitulo">
                ÁREA DO PROFESSOR
            </span>


            <h2>

                Olá,
                <?= htmlspecialchars($nome_professor) ?>!

            </h2>


            <p>

                Bem-vindo ao seu painel de professor.
                Aqui você poderá acompanhar e gerenciar
                suas atividades acadêmicas.

            </p>

        </div>


        <div class="boas-vindas-detalhe">

            <div class="circulo-azul"></div>

            <div class="circulo-vermelho"></div>

        </div>

    </section>


    <!-- ACESSO RÁPIDO -->

    <div class="titulo-secao">

        <div>

            <span>
                PAINEL DO PROFESSOR
            </span>

            <h2>
                Acesso rápido
            </h2>

        </div>

        <p>
            Acesse suas turmas e informações acadêmicas.
        </p>

    </div>


    <section class="cards">


        <a
            href="turmas.php"
            class="card card-resultados"
        >

            <div class="card-topo">

                <div class="icone">
                    🏫
                </div>

                <span class="seta">
                    →
                </span>

            </div>


            <h3>
                Minhas turmas
            </h3>


            <p>
                Acesse suas turmas e disciplinas.
            </p>


            <span class="card-link">
                Ver minhas turmas
            </span>

        </a>


    </section>


    <!-- INFORMAÇÃO -->

    <section
        class="informacao"
        id="informacoes"
    >

        <div class="informacao-icone">
            ℹ
        </div>


        <div>

            <h3>
                Área do professor
            </h3>


            <p>

                Utilize o sistema para gerenciar
                notas, presença e justificativas
                das turmas atribuídas a você.

            </p>

        </div>

    </section>


</main>


<!-- RODAPÉ -->

<footer class="rodape">

    <div class="rodape-conteudo">

        <div>

            <strong>
                ALMATEC
            </strong>

            <p>
                Sistema de acompanhamento escolar
            </p>

        </div>


        <div class="rodape-direita">

            <span>
                Educação • Bahia
            </span>

        </div>

    </div>


    <div class="rodape-linha"></div>


    <p class="copyright">

        Sistema destinado ao acompanhamento
        da comunidade escolar.

    </p>

</footer>


</body>

</html>