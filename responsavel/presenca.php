<?php

session_start();

require_once "../config/conexao.php";

// ================================
// VERIFICA LOGIN
// ================================

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// ================================
// BUSCA RESPONSÁVEL
// ================================

$sql = "SELECT id
        FROM responsaveis
        WHERE usuario_id = ?";

$stmt = $conexao->prepare($sql);
$stmt->execute([$usuario_id]);

$responsavel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$responsavel) {
    die("Responsável não encontrado.");
}

$responsavel_id = $responsavel['id'];

// ================================
// IDENTIFICA O ALUNO
// ================================

$aluno_id = $_GET['aluno'] ?? null;

if (!$aluno_id) {

    $sql = "SELECT aluno_id
            FROM responsavel_aluno
            WHERE responsavel_id = ?
            LIMIT 1";

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$responsavel_id]);

    $vinculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vinculo) {
        die("Nenhum aluno vinculado.");
    }

    $aluno_id = $vinculo['aluno_id'];
}

// ================================
// CONFIRMA VÍNCULO DO ALUNO
// ================================

$sql = "SELECT
            a.id,
            a.matricula,
            u.nome
        FROM responsavel_aluno ra

        INNER JOIN alunos a
            ON ra.aluno_id = a.id

        INNER JOIN usuarios u
            ON a.usuario_id = u.id

        WHERE ra.responsavel_id = ?
        AND a.id = ?";

$stmt = $conexao->prepare($sql);
$stmt->execute([
    $responsavel_id,
    $aluno_id
]);

$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Aluno não encontrado ou não está vinculado.");
}

// ================================
// BUSCA PRESENÇAS
// ================================

$sql = "SELECT
            p.data_aula,
            p.presente,
            d.nome AS disciplina

        FROM presencas p

        INNER JOIN disciplinas d
            ON p.disciplina_id = d.id

        WHERE p.aluno_id = ?

        ORDER BY p.data_aula DESC";

$stmt = $conexao->prepare($sql);
$stmt->execute([$aluno_id]);

$presencas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================================
// CALCULA ESTATÍSTICAS
// ================================

$total_aulas = count($presencas);

$total_presencas = 0;
$total_faltas = 0;

foreach ($presencas as $presenca) {

    if ($presenca['presente']) {
        $total_presencas++;
    } else {
        $total_faltas++;
    }
}

if ($total_aulas > 0) {

    $percentual_presenca =
        ($total_presencas / $total_aulas) * 100;

} else {

    $percentual_presenca = 0;
}

// Situação
if ($percentual_presenca >= 75) {

    $situacao = "Frequência adequada";
    $classe_situacao = "situacao-boa";

} else {

    $situacao = "Atenção à frequência";
    $classe_situacao = "situacao-alerta";
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Presença | Almatec</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        /* =========================
           CABEÇALHO
        ========================= */

        .pagina-cabecalho {

            background: white;

            border-radius: 16px;

            padding: 30px;

            margin-bottom: 25px;

            box-shadow: 0 8px 30px rgba(0,0,0,.07);

            border-left: 6px solid #d71920;

        }

        .pagina-cabecalho span {

            color: #d71920;

            font-size: 11px;

            font-weight: bold;

            letter-spacing: 1.5px;

        }

        .pagina-cabecalho h2 {

            color: #003b70;

            margin-top: 5px;

            font-size: 28px;

        }

        .pagina-cabecalho p {

            color: #687385;

            margin-top: 5px;

        }

        /* =========================
           RESUMO
        ========================= */

        .resumo-presenca {

            display: grid;

            grid-template-columns:
            repeat(3, 1fr);

            gap: 18px;

            margin-bottom: 25px;

        }

        .presenca-card {

            background: white;

            border-radius: 16px;

            padding: 25px;

            border: 1px solid #e9edf2;

            box-shadow:
            0 4px 15px rgba(0,0,0,.04);

        }

        .presenca-card span {

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1px;

            color: #687385;

        }

        .presenca-card strong {

            display: block;

            font-size: 30px;

            color: #003b70;

            margin-top: 5px;

        }

        .numero-presenca {

            color: #198754 !important;

        }

        .numero-falta {

            color: #d71920 !important;

        }

        /* =========================
           BARRA DE FREQUÊNCIA
        ========================= */

        .frequencia-box {

            background: white;

            border-radius: 16px;

            padding: 25px;

            margin-bottom: 25px;

            border: 1px solid #e9edf2;

            box-shadow:
            0 8px 30px rgba(0,0,0,.07);

        }

        .frequencia-topo {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 12px;

        }

        .frequencia-topo h3 {

            color: #003b70;

            font-size: 16px;

        }

        .porcentagem {

            font-size: 20px;

            font-weight: bold;

            color: #003b70;

        }

        .barra {

            width: 100%;

            height: 13px;

            background: #e9edf2;

            border-radius: 20px;

            overflow: hidden;

        }

        .barra-progresso {

            height: 100%;

            background: #003b70;

            border-radius: 20px;

        }

        .situacao {

            display: inline-block;

            margin-top: 15px;

            padding: 8px 14px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }

        .situacao-boa {

            background: #e8f7ee;

            color: #198754;

        }

        .situacao-alerta {

            background: #fff0f1;

            color: #d71920;

        }

        /* =========================
           TABELA
        ========================= */

        .tabela-container {

            background: white;

            border-radius: 16px;

            overflow: hidden;

            border: 1px solid #e9edf2;

            box-shadow:
            0 8px 30px rgba(0,0,0,.07);

        }

        .tabela-titulo {

            padding: 22px 25px;

            border-bottom:
            1px solid #e9edf2;

        }

        .tabela-titulo h3 {

            color: #003b70;

        }

        .tabela-titulo p {

            color: #687385;

            font-size: 13px;

            margin-top: 3px;

        }

        .tabela-scroll {

            overflow-x: auto;

        }

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 600px;

        }

        th {

            background: #003b70;

            color: white;

            padding: 15px;

            font-size: 12px;

            text-align: left;

        }

        td {

            padding: 16px 15px;

            border-bottom:
            1px solid #e9edf2;

            color: #344054;

            font-size: 14px;

        }

        tr:hover td {

            background: #f8fafc;

        }

        .data {

            color: #003b70;

            font-weight: bold;

        }

        .presente {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            background: #e8f7ee;

            color: #198754;

            font-size: 11px;

            font-weight: bold;

        }

        .falta {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            background: #fff0f1;

            color: #d71920;

            font-size: 11px;

            font-weight: bold;

        }

        .voltar {

            display: inline-block;

            margin-bottom: 20px;

            color: #003b70;

            font-size: 13px;

            font-weight: bold;

        }

        .voltar:hover {

            color: #d71920;

        }

        /* =========================
           RESPONSIVO
        ========================= */

        @media (max-width: 800px) {

            .resumo-presenca {

                grid-template-columns: 1fr;

            }

            .pagina-cabecalho h2 {

                font-size: 23px;

            }

        }

    </style>

</head>

<body>

<!-- =========================
     TOPO
========================= -->

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

            <div class="usuario-avatar">

                <?= strtoupper(
                    substr(
                        $_SESSION['usuario_nome'],
                        0,
                        1
                    )
                ) ?>

            </div>

        </div>

    </div>

</header>

<!-- FAIXA BAHIA -->

<div class="faixa-institucional">

    <div class="faixa-azul"></div>

    <div class="faixa-vermelha"></div>

    <div class="faixa-branca"></div>

</div>

<!-- =========================
     MENU
========================= -->

<nav class="menu">

    <div class="menu-conteudo">

        <a href="dashboard.php"
           class="menu-link">

            Início

        </a>

        <a href="resultados.php?aluno=<?= $aluno_id ?>"
           class="menu-link">

            Resultados

        </a>

        <a href="rotina.php?aluno=<?= $aluno_id ?>"
           class="menu-link">

            Rotina

        </a>

        <a href="presenca.php?aluno=<?= $aluno_id ?>"
           class="menu-link ativo">

            Presença

        </a>

        <a href="perfil.php"
           class="menu-link">

            Meu perfil

        </a>

        <a href="logout.php"
           class="menu-sair">

            Sair

        </a>

    </div>

</nav>

<!-- =========================
     CONTEÚDO
========================= -->

<main class="container">

    <a href="dashboard.php"
       class="voltar">

        ← Voltar para o início

    </a>

    <!-- CABEÇALHO -->

    <section class="pagina-cabecalho">

        <span>
            FREQUÊNCIA ESCOLAR
        </span>

        <h2>

            Presença de
            <?= htmlspecialchars(
                $aluno['nome']
            ) ?>

        </h2>

        <p>

            Matrícula:

            <strong>

                <?= htmlspecialchars(
                    $aluno['matricula']
                ) ?>

            </strong>

        </p>

    </section>

    <!-- RESUMO -->

    <section class="resumo-presenca">

        <div class="presenca-card">

            <span>
                Total de aulas
            </span>

            <strong>
                <?= $total_aulas ?>
            </strong>

        </div>

        <div class="presenca-card">

            <span>
                Presenças
            </span>

            <strong class="numero-presenca">

                <?= $total_presencas ?>

            </strong>

        </div>

        <div class="presenca-card">

            <span>
                Faltas
            </span>

            <strong class="numero-falta">

                <?= $total_faltas ?>

            </strong>

        </div>

    </section>

    <!-- FREQUÊNCIA -->

    <section class="frequencia-box">

        <div class="frequencia-topo">

            <h3>
                Frequência geral
            </h3>

            <span class="porcentagem">

                <?= number_format(
                    $percentual_presenca,
                    1,
                    ',',
                    '.'
                ) ?>%

            </span>

        </div>

        <div class="barra">

            <div
                class="barra-progresso"
                style="width:
                <?= $percentual_presenca ?>%;">
            </div>

        </div>

        <span class="situacao <?= $classe_situacao ?>">

            <?= $situacao ?>

        </span>

    </section>

    <!-- TABELA -->

    <section class="tabela-container">

        <div class="tabela-titulo">

            <h3>
                Histórico de frequência
            </h3>

            <p>
                Registro das aulas e presença do aluno.
            </p>

        </div>

        <div class="tabela-scroll">

            <table>

                <thead>

                    <tr>

                        <th>
                            Data
                        </th>

                        <th>
                            Disciplina
                        </th>

                        <th>
                            Situação
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (count($presencas) > 0): ?>

                        <?php foreach (
                            $presencas
                            as $presenca
                        ): ?>

                            <tr>

                                <td class="data">

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $presenca['data_aula']
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $presenca['disciplina']
                                    ) ?>

                                </td>

                                <td>

                                    <?php if (
                                        $presenca['presente']
                                    ): ?>

                                        <span class="presente">

                                            ✓ Presente

                                        </span>

                                    <?php else: ?>

                                        <span class="falta">

                                            ✕ Falta

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="3">

                                Nenhum registro de frequência
                                encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<!-- =========================
     RODAPÉ
========================= -->

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