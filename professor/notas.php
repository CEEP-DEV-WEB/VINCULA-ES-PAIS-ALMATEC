<?php

session_start();

require_once "../config/conexao.php";

/* Verifica login */
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "professor") {
    header("Location: login.php");
    exit;
}

/* Verifica se recebeu o ID da vinculação */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Turma ou disciplina não informada.");
}

$ptd_id = (int) $_GET["id"];
$usuario_id = $_SESSION["usuario_id"];


/* ==========================================
   BUSCA O PROFESSOR
========================================== */

$sql = "
    SELECT id
    FROM professores
    WHERE usuario_id = ?
";

$stmt = $conexao->prepare($sql);
$stmt->execute([$usuario_id]);

$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    die("Professor não encontrado.");
}

$professor_id = $professor["id"];


/* ==========================================
   VERIFICA VÍNCULO
========================================== */

$sql = "
    SELECT
        ptd.id,
        ptd.turma_id,
        ptd.disciplina_id,
        t.identificacao AS turma,
        d.nome AS disciplina
    FROM professor_turma_disciplina ptd

    INNER JOIN turmas t
        ON t.id = ptd.turma_id

    INNER JOIN disciplinas d
        ON d.id = ptd.disciplina_id

    WHERE ptd.id = ?
    AND ptd.professor_id = ?
";

$stmt = $conexao->prepare($sql);
$stmt->execute([$ptd_id, $professor_id]);

$vinculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vinculo) {
    die("Você não possui permissão para acessar esta turma.");
}


/* ==========================================
   BUSCA OS ALUNOS
========================================== */

$sql = "
    SELECT
        a.id AS aluno_id,
        u.nome,
        a.matricula,

        r.resultado_id,
        r.nota1,
        r.nota2,
        r.nota3,
        r.nota4

    FROM alunos a

    INNER JOIN usuarios u
        ON u.id = a.usuario_id

    LEFT JOIN (
        SELECT
            id AS resultado_id,
            aluno_id,
            disciplina_id,
            nota1,
            nota2,
            nota3,
            nota4
        FROM resultados
    ) r
        ON r.aluno_id = a.id
        AND r.disciplina_id = ?

    WHERE a.turma_id = ?

    ORDER BY u.nome
";

$stmt = $conexao->prepare($sql);
$stmt->execute([
    $vinculo["disciplina_id"],
    $vinculo["turma_id"]
]);

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notas - ALMATEC</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            color: #333;
        }


        /* ==========================================
           TOPO
        ========================================== */

        .topo {
            background: white;
            border-bottom: 1px solid #ddd;
        }


        .topo-conteudo {
            max-width: 1200px;
            margin: auto;
            padding: 18px 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }


        .logo-icon {
            width: 45px;
            height: 45px;

            border-radius: 10px;

            background: #1646a0;
            color: white;

            display: flex;
            justify-content: center;
            align-items: center;

            font-weight: bold;
            font-size: 18px;
        }


        .logo-area h1 {
            font-size: 22px;
            color: #1646a0;
        }


        /* ==========================================
           USUÁRIO
        ========================================== */

        .usuario-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }


        .usuario-info {
            text-align: right;
        }


        .usuario-label {
            display: block;
            font-size: 12px;
            color: #777;
            margin-bottom: 3px;
        }


        .usuario-info strong {
            font-size: 15px;
        }


        .usuario-avatar {
            width: 42px;
            height: 42px;

            border-radius: 50%;

            background: #1646a0;
            color: white;

            display: flex;
            justify-content: center;
            align-items: center;

            font-weight: bold;
            font-size: 18px;
        }


        /* ==========================================
           FAIXA
        ========================================== */

        .faixa-institucional {
            display: flex;
            height: 6px;
        }


        .faixa-azul {
            flex: 1;
            background: #1646a0;
        }


        .faixa-vermelha {
            flex: 1;
            background: #d62828;
        }


        .faixa-branca {
            flex: 1;
            background: white;
        }


        /* ==========================================
           MENU
        ========================================== */

        .menu {
            background: white;
            border-bottom: 1px solid #ddd;
        }


        .menu-conteudo {
            max-width: 1200px;
            margin: auto;

            padding: 0 25px;

            display: flex;
            align-items: center;
        }


        .menu-link {
            text-decoration: none;
            color: #555;

            padding: 17px 20px;

            display: block;

            font-size: 14px;
        }


        .menu-link:hover {
            color: #1646a0;
            background: #f5f6f8;
        }


        .menu-sair {
            margin-left: auto;
            color: #d62828;
        }


        /* ==========================================
           CONTAINER
        ========================================== */

        .container {
            max-width: 1200px;
            margin: auto;

            padding: 40px 25px;
        }


        .titulo-secao {
            font-size: 28px;
            color: #222;

            margin-bottom: 8px;
        }


        .subtitulo {
            color: #777;
            margin-bottom: 30px;
        }


        /* ==========================================
           CABEÇALHO DA TURMA
        ========================================== */

        .turma-info {
            background: white;

            border-radius: 12px;

            padding: 25px 30px;

            margin-bottom: 25px;

            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);

            border-left: 5px solid #1646a0;
        }


        .turma-info h2 {
            color: #222;
            margin-bottom: 8px;
            font-size: 22px;
        }


        .turma-info p {
            color: #777;
        }


        .turma-info strong {
            color: #333;
        }


        /* ==========================================
           CARD DA TABELA
        ========================================== */

        .tabela-card {
            background: white;

            border-radius: 12px;

            padding: 25px;

            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);

            overflow-x: auto;
        }


        .tabela-titulo {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 20px;
        }


        .tabela-titulo h2 {
            font-size: 20px;
            color: #222;
        }


        .quantidade {
            background: #eef3ff;

            color: #1646a0;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;
        }


        /* ==========================================
           TABELA
        ========================================== */

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }


        thead {
            background: #1646a0;
            color: white;
        }


        th {
            padding: 15px 12px;

            text-align: center;

            font-size: 13px;
        }


        th:first-child,
        th:nth-child(2) {
            text-align: left;
        }


        tbody tr {
            border-bottom: 1px solid #eee;
        }


        tbody tr:hover {
            background: #f8f9fc;
        }


        td {
            padding: 14px 12px;

            text-align: center;

            font-size: 14px;
        }


        td:first-child,
        td:nth-child(2) {
            text-align: left;
        }


        .aluno-nome {
            font-weight: bold;
            color: #333;
        }


        .matricula {
            color: #777;
        }


        /* ==========================================
           INPUTS DAS NOTAS
        ========================================== */

        .nota-input {
            width: 75px;

            padding: 10px;

            border: 1px solid #d5d8df;

            border-radius: 7px;

            text-align: center;

            font-size: 14px;

            outline: none;

            transition: 0.2s;
        }


        .nota-input:focus {
            border-color: #1646a0;

            box-shadow: 0 0 0 3px rgba(22, 70, 160, 0.10);
        }


        /* Remove setinhas em alguns navegadores */

        .nota-input::-webkit-inner-spin-button,
        .nota-input::-webkit-outer-spin-button {
            opacity: 1;
        }


        /* ==========================================
           BOTÃO
        ========================================== */

        .acoes {
            display: flex;

            justify-content: flex-end;

            margin-top: 25px;
        }


        .btn-salvar {
            border: none;

            background: #1646a0;

            color: white;

            padding: 13px 25px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }


        .btn-salvar:hover {
            background: #10377e;

            transform: translateY(-1px);
        }


        /* ==========================================
           SEM ALUNOS
        ========================================== */

        .sem-alunos {
            background: white;

            padding: 40px;

            border-radius: 12px;

            text-align: center;

            color: #777;

            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }


        .sem-alunos strong {
            display: block;

            color: #333;

            font-size: 18px;

            margin-bottom: 8px;
        }


        /* ==========================================
           RODAPÉ
        ========================================== */

        .rodape {
            margin-top: 30px;

            background: white;

            border-top: 1px solid #ddd;
        }


        .rodape-conteudo {
            max-width: 1200px;

            margin: auto;

            padding: 25px;

            display: flex;

            justify-content: space-between;

            color: #777;

            font-size: 13px;
        }


        /* ==========================================
           RESPONSIVO
        ========================================== */

        @media (max-width: 700px) {

            .usuario-info {
                display: none;
            }


            .menu-conteudo {
                overflow-x: auto;
            }


            .menu-link {
                white-space: nowrap;
            }


            .rodape-conteudo {
                flex-direction: column;

                gap: 10px;
            }


            .tabela-card {
                padding: 15px;
            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     TOPO
========================================== -->

<header class="topo">

    <div class="topo-conteudo">


        <div class="logo-area">

            <div class="logo-icon">
                A
            </div>

            <h1>
                ALMATEC
            </h1>

        </div>


        <div class="usuario-area">

            <div class="usuario-info">

                <span class="usuario-label">
                    Professor
                </span>

                <strong>
                    <?= htmlspecialchars($_SESSION["usuario_nome"] ?? "Professor") ?>
                </strong>

            </div>


            <div class="usuario-avatar">

                <?= strtoupper(
                    substr(
                        $_SESSION["usuario_nome"] ?? "P",
                        0,
                        1
                    )
                ) ?>

            </div>

        </div>


    </div>


    <div class="faixa-institucional">

        <div class="faixa-azul"></div>

        <div class="faixa-vermelha"></div>

        <div class="faixa-branca"></div>

    </div>

</header>



<!-- ==========================================
     MENU
========================================== -->

<nav class="menu">

    <div class="menu-conteudo">


        <a href="dashboard.php" class="menu-link">
            Início
        </a>


        <a href="turmas.php" class="menu-link ativo">
            Minhas turmas
        </a>


        <a href="perfil.php" class="menu-link">
            Meu perfil
        </a>


        <a href="logout.php" class="menu-link menu-sair">
            Sair
        </a>


    </div>

</nav>



<!-- ==========================================
     CONTEÚDO
========================================== -->

<main class="container">


    <h1 class="titulo-secao">
        Notas dos alunos
    </h1>


    <p class="subtitulo">
        Gerencie as notas dos alunos da turma.
    </p>



    <!-- ======================================
         INFORMAÇÕES DA TURMA
    ======================================= -->

    <section class="turma-info">


        <h2>
            <?= htmlspecialchars($vinculo["turma"]) ?>
        </h2>


        <p>

            <strong>
                Disciplina:
            </strong>

            <?= htmlspecialchars($vinculo["disciplina"]) ?>

        </p>


    </section>



    <!-- ======================================
         ALUNOS
    ======================================= -->

    <?php if (count($alunos) > 0): ?>


        <form
            method="POST"
            action="salvar_notas.php"
        >


            <input
                type="hidden"
                name="ptd_id"
                value="<?= $ptd_id ?>"
            >



            <div class="tabela-card">


                <div class="tabela-titulo">


                    <h2>
                        Avaliação dos alunos
                    </h2>


                    <span class="quantidade">

                        <?= count($alunos) ?>

                        <?= count($alunos) == 1 ? "aluno" : "alunos" ?>

                    </span>


                </div>



                <table>


                    <thead>

                        <tr>

                            <th>
                                Aluno
                            </th>

                            <th>
                                Matrícula
                            </th>

                            <th>
                                Nota 1
                            </th>

                            <th>
                                Nota 2
                            </th>

                            <th>
                                Nota 3
                            </th>

                            <th>
                                Nota 4
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach ($alunos as $aluno): ?>


                            <tr>


                                <td>

                                    <span class="aluno-nome">

                                        <?= htmlspecialchars(
                                            $aluno["nome"]
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="matricula">

                                        <?= htmlspecialchars(
                                            $aluno["matricula"]
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <input
                                        class="nota-input"
                                        type="number"
                                        name="notas[<?= $aluno["aluno_id"] ?>][nota1]"
                                        value="<?= htmlspecialchars($aluno["nota1"] ?? "") ?>"
                                        min="0"
                                        max="10"
                                        step="0.01"
                                        required
                                    >

                                </td>


                                <td>

                                    <input
                                        class="nota-input"
                                        type="number"
                                        name="notas[<?= $aluno["aluno_id"] ?>][nota2]"
                                        value="<?= htmlspecialchars($aluno["nota2"] ?? "") ?>"
                                        min="0"
                                        max="10"
                                        step="0.01"
                                        required
                                    >

                                </td>


                                <td>

                                    <input
                                        class="nota-input"
                                        type="number"
                                        name="notas[<?= $aluno["aluno_id"] ?>][nota3]"
                                        value="<?= htmlspecialchars($aluno["nota3"] ?? "") ?>"
                                        min="0"
                                        max="10"
                                        step="0.01"
                                        required
                                    >

                                </td>


                                <td>

                                    <input
                                        class="nota-input"
                                        type="number"
                                        name="notas[<?= $aluno["aluno_id"] ?>][nota4]"
                                        value="<?= htmlspecialchars($aluno["nota4"] ?? "") ?>"
                                        min="0"
                                        max="10"
                                        step="0.01"
                                        required
                                    >

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>



                <div class="acoes">


                    <button
                        type="submit"
                        class="btn-salvar"
                    >
                        Salvar notas
                    </button>


                </div>


            </div>


        </form>


    <?php else: ?>


        <div class="sem-alunos">

            <strong>
                Nenhum aluno cadastrado
            </strong>

            <p>
                Não existem alunos cadastrados nesta turma.
            </p>

        </div>


    <?php endif; ?>


</main>



<!-- ==========================================
     RODAPÉ
========================================== -->

<footer class="rodape">

    <div class="rodape-conteudo">

        <span>
            © <?= date("Y") ?> ALMATEC
        </span>


        <span>
            Sistema de Gestão Escolar
        </span>

    </div>

</footer>


</body>

</html>