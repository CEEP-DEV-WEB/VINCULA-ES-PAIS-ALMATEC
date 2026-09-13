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


/* Busca professor */

$sql = "
    SELECT
        p.id AS professor_id,
        u.nome
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


$professor_id = $professor['professor_id'];

$nome_professor = $professor['nome'];


/* Busca turmas e disciplinas */

$sql = "
    SELECT
        ptd.id,
        t.identificacao AS turma,
        d.nome AS disciplina

    FROM professor_turma_disciplina ptd

    INNER JOIN turmas t
        ON t.id = ptd.turma_id

    INNER JOIN disciplinas d
        ON d.id = ptd.disciplina_id

    WHERE ptd.professor_id = ?

    ORDER BY
        t.serie,
        t.identificacao,
        d.nome
";

$stmt = $conexao->prepare($sql);
$stmt->execute([$professor_id]);

$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Minhas Turmas | Professor | Almatec</title>

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

                <h1>
                    ALMATEC
                </h1>

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
            class="menu-link"
        >
            Início
        </a>


        <a
            href="turmas.php"
            class="menu-link ativo"
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


    <div class="titulo-secao">

        <div>

            <span>
                ORGANIZAÇÃO ACADÊMICA
            </span>


            <h2>
                Minhas turmas e disciplinas
            </h2>

        </div>


        <p>

            Gerencie as informações acadêmicas
            das suas turmas.

        </p>

    </div>


    <?php if (count($turmas) > 0): ?>


        <section class="cards">


            <?php foreach ($turmas as $turma): ?>


                <!-- NOTAS -->

                <a
                    href="notas.php?id=<?= $turma['id'] ?>"
                    class="card card-resultados"
                >

                    <div class="card-topo">

                        <div class="icone">
                            📊
                        </div>

                        <span class="seta">
                            →
                        </span>

                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $turma['turma']
                        ) ?>

                    </h3>


                    <p>

                        <strong>
                            Disciplina:
                        </strong>

                        <?= htmlspecialchars(
                            $turma['disciplina']
                        ) ?>

                    </p>


                    <p>
                        Consulte e altere as notas dos alunos.
                    </p>


                    <span class="card-link">
                        Gerenciar notas
                    </span>

                </a>


                <!-- PRESENÇA -->

                <a
                    href="faltas.php?id=<?= $turma['id'] ?>"
                    class="card card-presenca"
                >

                    <div class="card-topo">

                        <div class="icone">
                            📋
                        </div>

                        <span class="seta">
                            →
                        </span>

                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $turma['turma']
                        ) ?>

                    </h3>


                    <p>

                        <strong>
                            Disciplina:
                        </strong>

                        <?= htmlspecialchars(
                            $turma['disciplina']
                        ) ?>

                    </p>


                    <p>
                        Registre e altere a presença dos alunos.
                    </p>


                    <span class="card-link">
                        Gerenciar presença
                    </span>

                </a>


                <!-- JUSTIFICATIVAS -->

                <a
                    href="justificativas.php?id=<?= $turma['id'] ?>"
                    class="card card-rotina"
                >

                    <div class="card-topo">

                        <div class="icone">
                            📄
                        </div>

                        <span class="seta">
                            →
                        </span>

                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $turma['turma']
                        ) ?>

                    </h3>


                    <p>

                        <strong>
                            Disciplina:
                        </strong>

                        <?= htmlspecialchars(
                            $turma['disciplina']
                        ) ?>

                    </p>


                    <p>
                        Analise as justificativas de faltas.
                    </p>


                    <span class="card-link">
                        Ver justificativas
                    </span>

                </a>


            <?php endforeach; ?>


        </section>


    <?php else: ?>


        <section class="sem-aluno">

            <div class="sem-aluno-icone">
                👨‍🏫
            </div>


            <h2>
                Nenhuma turma vinculada
            </h2>


            <p>

                Ainda não existe nenhuma turma
                ou disciplina associada a este professor.

            </p>


            <span>

                Entre em contato com a instituição
                para realizar a vinculação.

            </span>

        </section>


    <?php endif; ?>


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