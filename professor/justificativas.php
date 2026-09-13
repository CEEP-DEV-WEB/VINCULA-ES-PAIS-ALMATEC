<?php

session_start();

require_once "../config/conexao.php";

/* Verifica login */
if (
    !isset($_SESSION["usuario_id"]) ||
    $_SESSION["usuario_tipo"] !== "professor"
) {
    header("Location: login.php");
    exit;
}

/* Verifica o vínculo */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Turma ou disciplina não informada.");
}

$ptd_id = (int) $_GET["id"];
$usuario_id = $_SESSION["usuario_id"];

/* Busca o professor */
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

/* Verifica se o professor possui esse vínculo */
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

/* Busca as justificativas */
$sql = "
    SELECT
        j.id,
        j.motivo,
        j.status,
        j.observacao_professor,
        j.data_envio,

        p.data_aula,

        u.nome AS aluno,
        a.matricula

    FROM justificativas j

    INNER JOIN alunos a
        ON a.id = j.aluno_id

    INNER JOIN usuarios u
        ON u.id = a.usuario_id

    INNER JOIN presencas p
        ON p.id = j.presenca_id

    WHERE a.turma_id = ?
    AND p.disciplina_id = ?

    ORDER BY j.status = 'Pendente' DESC, j.data_envio DESC
";

$stmt = $conexao->prepare($sql);
$stmt->execute([
    $vinculo["turma_id"],
    $vinculo["disciplina_id"]
]);

$justificativas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Justificativas - ALMATEC</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fa;
            color: #1f2937;
        }

        /* ==============================
           TOPO
        ============================== */

        .topo {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .topo-conteudo {
            max-width: 1200px;
            margin: auto;
            padding: 18px 30px;

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

            background: #1769e0;
            color: white;

            border-radius: 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 21px;
            font-weight: bold;
        }

        .logo-texto {
            font-size: 22px;
            font-weight: bold;
            color: #1769e0;
        }

        .usuario-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .usuario-info {
            text-align: right;
        }

        .usuario-label {
            font-size: 12px;
            color: #6b7280;
        }

        .usuario-nome {
            font-size: 15px;
            font-weight: bold;
        }

        .usuario-avatar {
            width: 42px;
            height: 42px;

            border-radius: 50%;

            background: #e9f1ff;
            color: #1769e0;

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: bold;
        }

        /* ==============================
           FAIXA INSTITUCIONAL
        ============================== */

        .faixa-institucional {
            width: 100%;
        }

        .faixa-azul {
            height: 6px;
            background: #1769e0;
        }

        .faixa-vermelha {
            height: 6px;
            background: #e63946;
        }

        .faixa-branca {
            height: 5px;
            background: #ffffff;
        }

        /* ==============================
           MENU
        ============================== */

        .menu {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .menu-conteudo {
            max-width: 1200px;
            margin: auto;

            padding: 0 30px;

            display: flex;
            align-items: center;
            gap: 8px;
        }

        .menu-link {
            text-decoration: none;
            color: #4b5563;

            padding: 16px 18px;

            font-size: 14px;
            font-weight: 600;

            border-bottom: 3px solid transparent;

            transition: 0.2s;
        }

        .menu-link:hover {
            color: #1769e0;
            background: #f8fafc;
        }

        .menu-link.ativo {
            color: #1769e0;
            border-bottom-color: #1769e0;
        }

        .menu-sair {
            margin-left: auto;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 7px;

            color: #dc2626;
            background: #fff1f2;

            font-size: 14px;
            font-weight: bold;
        }

        /* ==============================
           CONTAINER
        ============================== */

        .container {
            max-width: 1200px;
            margin: 0 auto;

            padding: 40px 30px 60px;
        }

        /* ==============================
           CABEÇALHO DA PÁGINA
        ============================== */

        .cabecalho-pagina {
            margin-bottom: 30px;
        }

        .cabecalho-pagina h1 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #111827;
        }

        .subtitulo {
            color: #6b7280;
            font-size: 15px;
        }

        /* ==============================
           CARD DA TURMA
        ============================== */

        .card-turma {
            background: #ffffff;

            border: 1px solid #e5e7eb;
            border-radius: 12px;

            padding: 24px;

            margin-bottom: 25px;

            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
        }

        .card-turma h2 {
            color: #1769e0;
            font-size: 21px;
            margin-bottom: 8px;
        }

        .card-turma p {
            color: #6b7280;
            font-size: 14px;
        }

        .card-turma strong {
            color: #374151;
        }

        /* ==============================
           TÍTULO
        ============================== */

        .titulo-secao {
            font-size: 21px;
            margin: 30px 0 18px;

            color: #111827;
        }

        /* ==============================
           CARDS JUSTIFICATIVAS
        ============================== */

        .cards-justificativas {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card-justificativa {
            background: #ffffff;

            border: 1px solid #e5e7eb;
            border-radius: 12px;

            padding: 25px;

            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
        }

        .card-topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            gap: 20px;

            margin-bottom: 20px;
        }

        .aluno-info h3 {
            font-size: 19px;
            color: #111827;

            margin-bottom: 6px;
        }

        .matricula {
            color: #6b7280;
            font-size: 13px;
        }

        /* ==============================
           STATUS
        ============================== */

        .status {
            padding: 7px 13px;

            border-radius: 20px;

            font-size: 12px;
            font-weight: bold;

            white-space: nowrap;
        }

        .status-pendente {
            background: #fff7ed;
            color: #c2410c;
        }

        .status-aceita {
            background: #ecfdf5;
            color: #047857;
        }

        .status-recusada {
            background: #fef2f2;
            color: #b91c1c;
        }

        /* ==============================
           INFORMAÇÕES
        ============================== */

        .info-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 15px;

            margin-bottom: 20px;
        }

        .info-item {
            background: #f8fafc;

            border-radius: 8px;

            padding: 13px 15px;
        }

        .info-item strong {
            display: block;

            font-size: 12px;

            color: #6b7280;

            margin-bottom: 5px;
        }

        .info-item span {
            font-size: 14px;
            color: #374151;
        }

        /* ==============================
           MOTIVO
        ============================== */

        .motivo {
            background: #f8fafc;

            border-left: 4px solid #1769e0;

            padding: 16px;

            border-radius: 6px;

            margin-bottom: 20px;
        }

        .motivo strong {
            display: block;

            margin-bottom: 8px;

            font-size: 13px;

            color: #374151;
        }

        .motivo p {
            font-size: 14px;
            line-height: 1.6;

            color: #4b5563;
        }

        /* ==============================
           FORMULÁRIO
        ============================== */

        .analise {
            border-top: 1px solid #e5e7eb;

            padding-top: 20px;
        }

        .analise label {
            display: block;

            font-size: 13px;
            font-weight: bold;

            margin-bottom: 8px;

            color: #374151;
        }

        .analise textarea {
            width: 100%;

            min-height: 100px;

            resize: vertical;

            padding: 12px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;

            outline: none;

            margin-bottom: 14px;
        }

        .analise textarea:focus {
            border-color: #1769e0;

            box-shadow: 0 0 0 3px rgba(23, 105, 224, 0.1);
        }

        .botoes {
            display: flex;
            gap: 10px;
        }

        .btn {
            border: none;

            padding: 11px 20px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }

        .btn-aceitar {
            background: #1769e0;
            color: white;
        }

        .btn-aceitar:hover {
            background: #1258be;
        }

        .btn-recusar {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-recusar:hover {
            background: #fecaca;
        }

        /* ==============================
           OBSERVAÇÃO
        ============================== */

        .observacao {
            background: #f0f7ff;

            border-left: 4px solid #1769e0;

            padding: 15px;

            border-radius: 6px;

            font-size: 14px;

            line-height: 1.6;

            color: #374151;
        }

        .observacao strong {
            display: block;
            margin-bottom: 5px;
        }

        /* ==============================
           SEM JUSTIFICATIVAS
        ============================== */

        .sem-justificativas {
            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 12px;

            padding: 45px 20px;

            text-align: center;

            color: #6b7280;
        }

        .sem-justificativas .icone {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .sem-justificativas h3 {
            color: #374151;
            margin-bottom: 7px;
        }

        /* ==============================
           RODAPÉ
        ============================== */

        .rodape {
            background: #ffffff;

            border-top: 1px solid #e5e7eb;
        }

        .rodape-conteudo {
            max-width: 1200px;

            margin: auto;

            padding: 20px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            color: #6b7280;

            font-size: 13px;
        }

        /* ==============================
           RESPONSIVO
        ============================== */

        @media (max-width: 700px) {

            .topo-conteudo {
                padding: 15px 18px;
            }

            .menu-conteudo {
                padding: 0 18px;

                overflow-x: auto;
            }

            .container {
                padding: 30px 18px 50px;
            }

            .usuario-info {
                display: none;
            }

            .card-topo {
                flex-direction: column;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .botoes {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .rodape-conteudo {
                padding: 18px;
                text-align: center;
            }
        }

    </style>

</head>


<body>

<!-- ==============================
     TOPO
============================== -->

<header class="topo">

    <div class="topo-conteudo">

        <div class="logo-area">

            <div class="logo-icon">
                A
            </div>

            <div class="logo-texto">
                ALMATEC
            </div>

        </div>


        <div class="usuario-area">

            <div class="usuario-info">

                <div class="usuario-label">
                    Professor
                </div>

                <div class="usuario-nome">
                    <?= htmlspecialchars($_SESSION["usuario_nome"] ?? "Professor") ?>
                </div>

            </div>

            <div class="usuario-avatar">
                P
            </div>

        </div>

    </div>

</header>


<!-- ==============================
     FAIXA
============================== -->

<div class="faixa-institucional">

    <div class="faixa-azul"></div>

    <div class="faixa-vermelha"></div>

    <div class="faixa-branca"></div>

</div>


<!-- ==============================
     MENU
============================== -->

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

        <a href="logout.php" class="menu-sair">
            Sair
        </a>

    </div>

</nav>


<!-- ==============================
     CONTEÚDO
============================== -->

<main class="container">

    <div class="cabecalho-pagina">

        <h1>
            Justificativas de faltas
        </h1>

        <p class="subtitulo">
            Consulte e analise as justificativas enviadas pelos alunos.
        </p>

    </div>


    <!-- TURMA -->

    <section class="card-turma">

        <h2>
            <?= htmlspecialchars($vinculo["turma"]) ?>
        </h2>

        <p>

            <strong>Disciplina:</strong>

            <?= htmlspecialchars($vinculo["disciplina"]) ?>

        </p>

    </section>


    <h2 class="titulo-secao">
        Justificativas recebidas
    </h2>


    <?php if (count($justificativas) > 0): ?>

        <div class="cards-justificativas">

            <?php foreach ($justificativas as $justificativa): ?>

                <article class="card-justificativa">

                    <div class="card-topo">

                        <div class="aluno-info">

                            <h3>
                                <?= htmlspecialchars($justificativa["aluno"]) ?>
                            </h3>

                            <span class="matricula">

                                Matrícula:
                                <?= htmlspecialchars($justificativa["matricula"]) ?>

                            </span>

                        </div>


                        <?php

                        $classeStatus = "status-pendente";

                        if ($justificativa["status"] === "Aceita") {
                            $classeStatus = "status-aceita";
                        }

                        if ($justificativa["status"] === "Recusada") {
                            $classeStatus = "status-recusada";
                        }

                        ?>

                        <span class="status <?= $classeStatus ?>">

                            <?= htmlspecialchars($justificativa["status"]) ?>

                        </span>

                    </div>


                    <div class="info-grid">

                        <div class="info-item">

                            <strong>
                                Data da falta
                            </strong>

                            <span>

                                <?= date(
                                    "d/m/Y",
                                    strtotime($justificativa["data_aula"])
                                ) ?>

                            </span>

                        </div>


                        <div class="info-item">

                            <strong>
                                Data de envio
                            </strong>

                            <span>

                                <?= date(
                                    "d/m/Y H:i",
                                    strtotime($justificativa["data_envio"])
                                ) ?>

                            </span>

                        </div>

                    </div>


                    <div class="motivo">

                        <strong>
                            Motivo apresentado
                        </strong>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $justificativa["motivo"]
                                )
                            ) ?>

                        </p>

                    </div>


                    <?php if ($justificativa["status"] === "Pendente"): ?>

                        <div class="analise">

                            <form
                                method="POST"
                                action="analisar_justificativa.php"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $justificativa["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="ptd_id"
                                    value="<?= $ptd_id ?>"
                                >


                                <label
                                    for="observacao_<?= $justificativa["id"] ?>"
                                >
                                    Observação do professor
                                </label>


                                <textarea
                                    id="observacao_<?= $justificativa["id"] ?>"
                                    name="observacao"
                                    placeholder="Digite uma observação sobre a análise..."
                                ></textarea>


                                <div class="botoes">

                                    <button
                                        type="submit"
                                        name="acao"
                                        value="Aceita"
                                        class="btn btn-aceitar"
                                    >
                                        Aceitar justificativa
                                    </button>


                                    <button
                                        type="submit"
                                        name="acao"
                                        value="Recusada"
                                        class="btn btn-recusar"
                                    >
                                        Recusar justificativa
                                    </button>

                                </div>

                            </form>

                        </div>


                    <?php elseif (
                        !empty($justificativa["observacao_professor"])
                    ): ?>

                        <div class="observacao">

                            <strong>
                                Observação do professor
                            </strong>

                            <?= nl2br(
                                htmlspecialchars(
                                    $justificativa["observacao_professor"]
                                )
                            ) ?>

                        </div>

                    <?php endif; ?>

                </article>

            <?php endforeach; ?>

        </div>


    <?php else: ?>

        <div class="sem-justificativas">

            <div class="icone">
                ✓
            </div>

            <h3>
                Nenhuma justificativa encontrada
            </h3>

            <p>
                Não existem justificativas registradas para esta turma e disciplina.
            </p>

        </div>

    <?php endif; ?>

</main>


<!-- ==============================
     RODAPÉ
============================== -->

<footer class="rodape">

    <div class="rodape-conteudo">

        <span>
            © <?= date("Y") ?> ALMATEC
        </span>

        <span>
            Sistema de gestão escolar
        </span>

    </div>

</footer>

</body>

</html>