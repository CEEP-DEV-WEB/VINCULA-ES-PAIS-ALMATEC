<?php

session_start();

require_once "../config/conexao.php";


/* ==========================================
   VERIFICA LOGIN
========================================== */

if (
    !isset($_SESSION["usuario_id"]) ||
    $_SESSION["usuario_tipo"] !== "professor"
) {
    header("Location: login.php");
    exit;
}


/* ==========================================
   VERIFICA VÍNCULO
========================================== */

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
   VERIFICA O VÍNCULO
========================================== */

$sql = "
    SELECT
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

$stmt->execute([
    $ptd_id,
    $professor_id
]);

$vinculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vinculo) {
    die("Você não possui permissão para acessar esta turma.");
}


/* ==========================================
   DATA ESCOLHIDA
========================================== */

$data = $_GET["data"] ?? date("Y-m-d");


/* ==========================================
   BUSCA OS ALUNOS
========================================== */

$sql = "
    SELECT
        a.id AS aluno_id,
        u.nome,
        a.matricula,

        p.id AS presenca_id,
        p.presente

    FROM alunos a

    INNER JOIN usuarios u
        ON u.id = a.usuario_id

    LEFT JOIN presencas p
        ON p.aluno_id = a.id
        AND p.disciplina_id = ?
        AND p.data_aula = ?

    WHERE a.turma_id = ?

    ORDER BY u.nome
";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    $vinculo["disciplina_id"],
    $data,
    $vinculo["turma_id"]
]);

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Faltas - ALMATEC</title>


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

            align-items: center;

            justify-content: center;

            font-weight: bold;

            font-size: 18px;
        }


        /* ==========================================
           FAIXA INSTITUCIONAL
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


        .menu-link.ativo {
            color: #1646a0;

            font-weight: bold;

            border-bottom: 3px solid #1646a0;
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
           CARD DA TURMA
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
           CARD DATA
        ========================================== */

        .data-card {
            background: white;

            border-radius: 12px;

            padding: 25px;

            margin-bottom: 25px;

            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }


        .data-card h2 {
            font-size: 19px;

            margin-bottom: 18px;

            color: #222;
        }


        .data-form {
            display: flex;

            align-items: end;

            gap: 15px;

            flex-wrap: wrap;
        }


        .campo-data {
            display: flex;

            flex-direction: column;

            gap: 7px;
        }


        .campo-data label {
            font-size: 13px;

            font-weight: bold;

            color: #555;
        }


        .campo-data input {
            padding: 11px 13px;

            border: 1px solid #d5d8df;

            border-radius: 7px;

            font-size: 14px;

            outline: none;
        }


        .campo-data input:focus {
            border-color: #1646a0;

            box-shadow:
                0 0 0 3px
                rgba(22, 70, 160, 0.10);
        }


        .btn-carregar {
            border: none;

            background: #1646a0;

            color: white;

            padding: 12px 20px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;
        }


        .btn-carregar:hover {
            background: #10377e;
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

            min-width: 700px;
        }


        thead {
            background: #1646a0;

            color: white;
        }


        th {
            padding: 15px 12px;

            font-size: 13px;

            text-align: left;
        }


        th:last-child {
            text-align: center;
        }


        tbody tr {
            border-bottom: 1px solid #eee;
        }


        tbody tr:hover {
            background: #f8f9fc;
        }


        td {
            padding: 15px 12px;

            font-size: 14px;
        }


        td:last-child {
            text-align: center;
        }


        .aluno-nome {
            font-weight: bold;

            color: #333;
        }


        .matricula {
            color: #777;
        }


        /* ==========================================
           OPÇÕES DE PRESENÇA
        ========================================== */

        .presenca-opcoes {
            display: flex;

            justify-content: center;

            align-items: center;

            gap: 20px;
        }


        .opcao {
            display: flex;

            align-items: center;

            gap: 6px;

            cursor: pointer;

            font-size: 13px;
        }


        .opcao input {
            width: 17px;

            height: 17px;

            cursor: pointer;
        }


        /* ==========================================
           BOTÃO SALVAR
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


            .data-form {
                align-items: stretch;

                flex-direction: column;
            }


            .btn-carregar {
                width: 100%;
            }


            .presenca-opcoes {
                flex-direction: column;

                gap: 8px;
            }


            .rodape-conteudo {
                flex-direction: column;

                gap: 10px;
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
                    <?= htmlspecialchars(
                        $_SESSION["usuario_nome"] ?? "Professor"
                    ) ?>
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
            class="menu-link menu-sair"
        >
            Sair
        </a>


    </div>


</nav>



<!-- ==========================================
     CONTEÚDO
========================================== -->

<main class="container">


    <h1 class="titulo-secao">
        Controle de presença
    </h1>


    <p class="subtitulo">
        Registre e acompanhe a presença dos alunos.
    </p>



    <!-- ======================================
         TURMA
    ======================================= -->

    <section class="turma-info">


        <h2>
            <?= htmlspecialchars(
                $vinculo["turma"]
            ) ?>
        </h2>


        <p>

            <strong>
                Disciplina:
            </strong>

            <?= htmlspecialchars(
                $vinculo["disciplina"]
            ) ?>

        </p>


    </section>



    <!-- ======================================
         DATA
    ======================================= -->

    <section class="data-card">


        <h2>
            Selecionar data da aula
        </h2>


        <form
            method="GET"
            class="data-form"
        >


            <input
                type="hidden"
                name="id"
                value="<?= $ptd_id ?>"
            >


            <div class="campo-data">


                <label for="data">
                    Data da aula
                </label>


                <input
                    type="date"
                    id="data"
                    name="data"
                    value="<?= htmlspecialchars($data) ?>"
                    required
                >


            </div>


            <button
                type="submit"
                class="btn-carregar"
            >
                Carregar alunos
            </button>


        </form>


    </section>



    <!-- ======================================
         ALUNOS
    ======================================= -->

    <?php if (count($alunos) > 0): ?>


        <form
            method="POST"
            action="salvar_presenca.php"
        >


            <input
                type="hidden"
                name="ptd_id"
                value="<?= $ptd_id ?>"
            >


            <input
                type="hidden"
                name="data"
                value="<?= htmlspecialchars($data) ?>"
            >



            <div class="tabela-card">


                <div class="tabela-titulo">


                    <h2>
                        Lista de presença
                    </h2>


                    <span class="quantidade">

                        <?= count($alunos) ?>

                        <?= count($alunos) == 1
                            ? "aluno"
                            : "alunos"
                        ?>

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
                                Presença
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


                                    <div class="presenca-opcoes">


                                        <label class="opcao">


                                            <input
                                                type="radio"
                                                name="presenca[<?= $aluno["aluno_id"] ?>]"
                                                value="1"

                                                <?php

                                                if (
                                                    $aluno["presenca_id"] &&
                                                    $aluno["presente"] == 1
                                                ) {

                                                    echo "checked";

                                                }

                                                ?>

                                            >


                                            Presente


                                        </label>



                                        <label class="opcao">


                                            <input
                                                type="radio"
                                                name="presenca[<?= $aluno["aluno_id"] ?>]"
                                                value="0"

                                                <?php

                                                if (
                                                    $aluno["presenca_id"] &&
                                                    $aluno["presente"] == 0
                                                ) {

                                                    echo "checked";

                                                }

                                                ?>

                                            >


                                            Falta


                                        </label>


                                    </div>


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
                        Salvar presença
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