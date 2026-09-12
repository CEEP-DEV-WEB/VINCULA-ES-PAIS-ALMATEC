<?php

session_start();

require_once "../config/conexao.php";

// =====================================
// VERIFICA LOGIN
// =====================================

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// =====================================
// BUSCA RESPONSÁVEL
// =====================================

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

// =====================================
// IDENTIFICA ALUNO
// =====================================

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

// =====================================
// CONFIRMA VÍNCULO
// =====================================

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

// =====================================
// BUSCA ROTINA
// =====================================

$sql = "SELECT
            r.id,
            r.dia_semana,
            r.horario_inicio,
            r.horario_fim,
            r.sala,
            r.professor,
            r.atividade,
            d.nome AS disciplina

        FROM rotina r

        INNER JOIN disciplinas d
            ON r.disciplina_id = d.id

        WHERE r.aluno_id = ?

        ORDER BY
            CASE r.dia_semana
                WHEN 'Segunda-feira' THEN 1
                WHEN 'Terça-feira' THEN 2
                WHEN 'Quarta-feira' THEN 3
                WHEN 'Quinta-feira' THEN 4
                WHEN 'Sexta-feira' THEN 5
                WHEN 'Sábado' THEN 6
                WHEN 'Domingo' THEN 7
                ELSE 8
            END,
            r.horario_inicio";

$stmt = $conexao->prepare($sql);
$stmt->execute([$aluno_id]);

$rotina = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================
// ORGANIZA POR DIA
// =====================================

$dias = [
    'Segunda-feira',
    'Terça-feira',
    'Quarta-feira',
    'Quinta-feira',
    'Sexta-feira',
    'Sábado'
];

$rotina_por_dia = [];

foreach ($dias as $dia) {
    $rotina_por_dia[$dia] = [];
}

foreach ($rotina as $item) {

    if (isset($rotina_por_dia[$item['dia_semana']])) {

        $rotina_por_dia[$item['dia_semana']][] = $item;
    }
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Rotina Escolar | Almatec</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        /* =================================
           CABEÇALHO
        ================================= */

        .pagina-cabecalho {

            background: white;

            border-radius: 16px;

            padding: 30px;

            margin-bottom: 25px;

            box-shadow:
                0 8px 30px rgba(0,0,0,.07);

            border-left:
                6px solid #d71920;

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

        /* =================================
           RESUMO
        ================================= */

        .resumo-rotina {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;

            margin-bottom: 30px;

        }

        .resumo-card {

            background: white;

            border-radius: 16px;

            padding: 23px;

            border:
                1px solid #e9edf2;

            box-shadow:
                0 4px 15px rgba(0,0,0,.04);

        }

        .resumo-card span {

            color: #687385;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1px;

        }

        .resumo-card strong {

            display: block;

            color: #003b70;

            font-size: 29px;

            margin-top: 5px;

        }

        /* =================================
           DIA
        ================================= */

        .dia {

            margin-bottom: 25px;

        }

        .dia-cabecalho {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 12px;

        }

        .dia-indicador {

            width: 8px;

            height: 35px;

            background: #d71920;

            border-radius: 10px;

        }

        .dia-cabecalho h3 {

            color: #003b70;

            font-size: 20px;

        }

        .dia-cabecalho span {

            color: #687385;

            font-size: 12px;

        }

        /* =================================
           AULA
        ================================= */

        .aula {

            background: white;

            border-radius: 16px;

            border:
                1px solid #e9edf2;

            padding: 22px 25px;

            margin-bottom: 12px;

            display: grid;

            grid-template-columns:
                130px 1fr auto;

            gap: 20px;

            align-items: center;

            box-shadow:
                0 4px 15px rgba(0,0,0,.035);

            transition: .2s;

        }

        .aula:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 22px rgba(0,0,0,.07);

        }

        /* HORÁRIO */

        .horario {

            color: #003b70;

            font-weight: bold;

            font-size: 14px;

        }

        .horario small {

            display: block;

            color: #687385;

            font-size: 11px;

            font-weight: normal;

            margin-top: 3px;

        }

        /* DISCIPLINA */

        .disciplina {

            border-left:
                2px solid #d71920;

            padding-left: 16px;

        }

        .disciplina h4 {

            color: #003b70;

            font-size: 16px;

        }

        .disciplina p {

            color: #687385;

            font-size: 12px;

            margin-top: 4px;

        }

        /* SALA */

        .aula-info {

            text-align: right;

            color: #687385;

            font-size: 11px;

        }

        .aula-info strong {

            display: block;

            color: #003b70;

            font-size: 12px;

            margin-bottom: 3px;

        }

        /* ATIVIDADE */

        .atividade {

            grid-column: 2 / 4;

            background: #eaf3fb;

            border-radius: 9px;

            padding: 10px 13px;

            color: #344054;

            font-size: 12px;

        }

        .atividade strong {

            color: #003b70;

        }

        /* SEM AULAS */

        .sem-aulas {

            background: white;

            border-radius: 16px;

            border:
                1px solid #e9edf2;

            padding: 25px;

            color: #687385;

            font-size: 13px;

        }

        /* VOLTAR */

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

        /* =================================
           RESPONSIVO
        ================================= */

        @media (max-width: 800px) {

            .resumo-rotina {

                grid-template-columns: 1fr;

            }

            .aula {

                grid-template-columns: 1fr;

                gap: 12px;

            }

            .aula-info {

                text-align: left;

            }

            .atividade {

                grid-column: auto;

            }

        }

    </style>

</head>

<body>

<!-- =================================
     TOPO
================================= -->

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

<!-- =================================
     FAIXA INSTITUCIONAL
================================= -->

<div class="faixa-institucional">

    <div class="faixa-azul"></div>

    <div class="faixa-vermelha"></div>

    <div class="faixa-branca"></div>

</div>

<!-- =================================
     MENU
================================= -->

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
           class="menu-link ativo">

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

<!-- =================================
     CONTEÚDO
================================= -->

<main class="container">

    <a href="dashboard.php"
       class="voltar">

        ← Voltar para o início

    </a>

    <!-- CABEÇALHO -->

    <section class="pagina-cabecalho">

        <span>
            ROTINA ESCOLAR
        </span>

        <h2>

            Rotina de
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

    <section class="resumo-rotina">

        <div class="resumo-card">

            <span>
                Aulas cadastradas
            </span>

            <strong>
                <?= count($rotina) ?>
            </strong>

        </div>

        <div class="resumo-card">

            <span>
                Dias com atividades
            </span>

            <strong>

                <?php

                $dias_com_aula = 0;

                foreach ($rotina_por_dia as $aulas) {

                    if (count($aulas) > 0) {
                        $dias_com_aula++;
                    }

                }

                echo $dias_com_aula;

                ?>

            </strong>

        </div>

    </section>

    <!-- =================================
         ROTINA POR DIA
    ================================= -->

    <?php foreach (
        $rotina_por_dia
        as $dia => $aulas
    ): ?>

        <section class="dia">

            <div class="dia-cabecalho">

                <div class="dia-indicador"></div>

                <div>

                    <h3>
                        <?= $dia ?>
                    </h3>

                    <span>

                        <?= count($aulas) ?>

                        aula(s) programada(s)

                    </span>

                </div>

            </div>

            <?php if (count($aulas) > 0): ?>

                <?php foreach ($aulas as $aula): ?>

                    <article class="aula">

                        <div class="horario">

                            <?= date(
                                'H:i',
                                strtotime(
                                    $aula['horario_inicio']
                                )
                            ) ?>

                            -
                            <?= date(
                                'H:i',
                                strtotime(
                                    $aula['horario_fim']
                                )
                            ) ?>

                            <small>
                                Horário da aula
                            </small>

                        </div>

                        <div class="disciplina">

                            <h4>

                                <?= htmlspecialchars(
                                    $aula['disciplina']
                                ) ?>

                            </h4>

                            <p>

                                Professor:
                                <?= htmlspecialchars(
                                    $aula['professor']
                                ) ?>

                            </p>

                        </div>

                        <div class="aula-info">

                            <strong>

                                <?= htmlspecialchars(
                                    $aula['sala']
                                ) ?>

                            </strong>

                            Sala

                        </div>

                        <div class="atividade">

                            <strong>
                                Atividade:
                            </strong>

                            <?= htmlspecialchars(
                                $aula['atividade']
                            ) ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="sem-aulas">

                    Nenhuma aula ou atividade
                    cadastrada para este dia.

                </div>

            <?php endif; ?>

        </section>

    <?php endforeach; ?>

</main>

<!-- =================================
     RODAPÉ
================================= -->

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