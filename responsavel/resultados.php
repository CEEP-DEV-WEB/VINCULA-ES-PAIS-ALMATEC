<?php

session_start();

require_once "../config/conexao.php";

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Busca o responsável
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

// Verifica qual aluno será exibido
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

// Confirma que o aluno pertence ao responsável
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
$stmt->execute([$responsavel_id, $aluno_id]);

$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Aluno não encontrado ou não está vinculado a este responsável.");
}

// Busca resultados
$sql = "SELECT
            d.nome AS disciplina,
            r.nota1,
            r.nota2,
            r.nota3,
            r.nota4
        FROM resultados r
        INNER JOIN disciplinas d
            ON r.disciplina_id = d.id
        WHERE r.aluno_id = ?
        ORDER BY d.nome";

$stmt = $conexao->prepare($sql);
$stmt->execute([$aluno_id]);

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcula médias
$total_medias = 0;

foreach ($resultados as &$resultado) {

    $media = (
        $resultado['nota1'] +
        $resultado['nota2'] +
        $resultado['nota3'] +
        $resultado['nota4']
    ) / 4;

    $resultado['media'] = $media;

    $total_medias += $media;
}

unset($resultado);

$media_geral = count($resultados) > 0
    ? $total_medias / count($resultados)
    : 0;

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Resultados | Almatec</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

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

        .resumo-resultados {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .resumo-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            border: 1px solid #e9edf2;
            box-shadow: 0 4px 15px rgba(0,0,0,.04);
        }

        .resumo-card span {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #687385;
        }

        .resumo-card strong {
            display: block;
            font-size: 30px;
            color: #003b70;
            margin-top: 5px;
        }

        .tabela-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e9edf2;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
        }

        .tabela-titulo {
            padding: 22px 25px;
            border-bottom: 1px solid #e9edf2;
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
            min-width: 700px;
        }

        th {
            background: #003b70;
            color: white;
            padding: 15px;
            font-size: 12px;
            text-align: center;
        }

        th:first-child {
            text-align: left;
        }

        td {
            padding: 17px 15px;
            border-bottom: 1px solid #e9edf2;
            text-align: center;
            color: #344054;
            font-size: 14px;
        }

        td:first-child {
            text-align: left;
            font-weight: bold;
            color: #003b70;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .media {
            font-weight: bold;
            color: #003b70;
        }

        .media-boa {
            color: #198754;
        }

        .media-baixa {
            color: #d71920;
        }

        .legenda {
            padding: 18px 25px;
            background: #eaf3fb;
            color: #687385;
            font-size: 12px;
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

        @media (max-width: 800px) {

            .resumo-resultados {
                grid-template-columns: 1fr;
            }

            .pagina-cabecalho h2 {
                font-size: 23px;
            }

        }

    </style>

</head>

<body>

<header class="topo">

    <div class="topo-conteudo">

        <div class="logo-area">

            <div class="logo-icon">A</div>

            <div>
                <h1>ALMATEC</h1>
                <span>Educação</span>
            </div>

        </div>

        <div class="usuario-area">

            <div class="usuario-avatar">
                <?= strtoupper(substr($_SESSION['usuario_nome'], 0, 1)) ?>
            </div>

        </div>

    </div>

</header>

<div class="faixa-institucional">

    <div class="faixa-azul"></div>
    <div class="faixa-vermelha"></div>
    <div class="faixa-branca"></div>

</div>

<nav class="menu">

    <div class="menu-conteudo">

        <a href="dashboard.php"
           class="menu-link">
            Início
        </a>

        <a href="resultados.php?aluno=<?= $aluno_id ?>"
           class="menu-link ativo">
            Resultados
        </a>

        <a href="rotina.php?aluno=<?= $aluno_id ?>"
           class="menu-link">
            Rotina
        </a>

        <a href="presenca.php?aluno=<?= $aluno_id ?>"
           class="menu-link">
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

<main class="container">

    <a href="dashboard.php" class="voltar">
        ← Voltar para o início
    </a>

    <section class="pagina-cabecalho">

        <span>DESEMPENHO ESCOLAR</span>

        <h2>
            Resultados de <?= htmlspecialchars($aluno['nome']) ?>
        </h2>

        <p>
            Matrícula:
            <strong>
                <?= htmlspecialchars($aluno['matricula']) ?>
            </strong>
        </p>

    </section>

    <section class="resumo-resultados">

        <div class="resumo-card">

            <span>Disciplinas</span>

            <strong>
                <?= count($resultados) ?>
            </strong>

        </div>

        <div class="resumo-card">

            <span>Média geral</span>

            <strong>
                <?= number_format($media_geral, 2, ',', '.') ?>
            </strong>

        </div>

        <div class="resumo-card">

            <span>Situação</span>

            <strong>
                <?= $media_geral >= 6 ? 'Aprovado' : 'Atenção' ?>
            </strong>

        </div>

    </section>

    <section class="tabela-container">

        <div class="tabela-titulo">

            <h3>
                Notas por disciplina
            </h3>

            <p>
                Acompanhe o desempenho do aluno em cada etapa.
            </p>

        </div>

        <div class="tabela-scroll">

            <table>

                <thead>

                    <tr>

                        <th>Disciplina</th>

                        <th>1ª Etapa</th>

                        <th>2ª Etapa</th>

                        <th>3ª Etapa</th>

                        <th>4ª Etapa</th>

                        <th>Média</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (count($resultados) > 0): ?>

                        <?php foreach ($resultados as $resultado): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($resultado['disciplina']) ?>
                                </td>

                                <td>
                                    <?= number_format($resultado['nota1'], 2, ',', '.') ?>
                                </td>

                                <td>
                                    <?= number_format($resultado['nota2'], 2, ',', '.') ?>
                                </td>

                                <td>
                                    <?= number_format($resultado['nota3'], 2, ',', '.') ?>
                                </td>

                                <td>
                                    <?= number_format($resultado['nota4'], 2, ',', '.') ?>
                                </td>

                                <td class="media
                                    <?= $resultado['media'] >= 6
                                        ? 'media-boa'
                                        : 'media-baixa' ?>">

                                    <?= number_format(
                                        $resultado['media'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="6">
                                Nenhum resultado cadastrado.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="legenda">

            <strong>Informação:</strong>
            a média apresentada é calculada automaticamente
            com base nas quatro etapas cadastradas.

        </div>

    </section>

</main>

<footer class="rodape">

    <div class="rodape-conteudo">

        <div>

            <strong>ALMATEC</strong>

            <p>
                Sistema de acompanhamento escolar
            </p>

        </div>

        <div class="rodape-direita">
            <span>Educação • Bahia</span>
        </div>

    </div>

    <div class="rodape-linha"></div>

    <p class="copyright">
        Sistema destinado ao acompanhamento da comunidade escolar.
    </p>

</footer>

</body>

</html>