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
// BUSCA DADOS DO USUÁRIO
// =====================================

$sql = "SELECT
            id,
            nome,
            email,
            tipo
        FROM usuarios
        WHERE id = ?";

$stmt = $conexao->prepare($sql);
$stmt->execute([$usuario_id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuário não encontrado.");
}

// =====================================
// BUSCA DADOS DO RESPONSÁVEL
// =====================================

$sql = "SELECT
            id,
            telefone
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
// BUSCA ALUNOS VINCULADOS
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

        ORDER BY u.nome";

$stmt = $conexao->prepare($sql);
$stmt->execute([$responsavel_id]);

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================
// PRIMEIRA LETRA DO NOME
// =====================================

$inicial = strtoupper(
    substr($usuario['nome'], 0, 1)
);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Meu Perfil | Almatec</title>

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
           PERFIL PRINCIPAL
        ================================= */

        .perfil-principal {

            background: white;

            border-radius: 16px;

            padding: 35px;

            display: flex;

            align-items: center;

            gap: 25px;

            margin-bottom: 25px;

            border:
                1px solid #e9edf2;

            box-shadow:
                0 8px 30px rgba(0,0,0,.07);

        }

        .perfil-avatar {

            width: 90px;

            height: 90px;

            border-radius: 50%;

            background: #003b70;

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 36px;

            font-weight: bold;

            border:
                6px solid #eaf3fb;

            flex-shrink: 0;

        }

        .perfil-nome {

            color: #003b70;

            font-size: 25px;

        }

        .perfil-tipo {

            display: inline-block;

            margin-top: 6px;

            background: #fff0f1;

            color: #d71920;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: bold;

            text-transform: uppercase;

        }

        /* =================================
           DADOS
        ================================= */

        .secao-titulo {

            margin:

                35px 0 15px;

        }

        .secao-titulo span {

            color: #d71920;

            font-size: 11px;

            font-weight: bold;

            letter-spacing: 1.5px;

        }

        .secao-titulo h2 {

            color: #003b70;

            font-size: 22px;

            margin-top: 3px;

        }

        .dados-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;

        }

        .dado-card {

            background: white;

            border:
                1px solid #e9edf2;

            border-radius: 16px;

            padding: 22px;

            box-shadow:
                0 4px 15px rgba(0,0,0,.04);

        }

        .dado-card span {

            display: block;

            color: #687385;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1px;

            margin-bottom: 7px;

        }

        .dado-card strong {

            color: #003b70;

            font-size: 16px;

            word-break: break-word;

        }

        /* =================================
           ALUNOS
        ================================= */

        .alunos-lista {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;

        }

        .aluno-card {

            background: white;

            border:
                1px solid #e9edf2;

            border-radius: 16px;

            padding: 25px;

            box-shadow:
                0 4px 15px rgba(0,0,0,.04);

            position: relative;

            overflow: hidden;

        }

        .aluno-card::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            right: 0;

            height: 4px;

            background: #003b70;

        }

        .aluno-topo {

            display: flex;

            align-items: center;

            gap: 15px;

        }

        .aluno-avatar {

            width: 52px;

            height: 52px;

            border-radius: 50%;

            background: #eaf3fb;

            color: #003b70;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: bold;

            font-size: 20px;

        }

        .aluno-card h3 {

            color: #003b70;

            font-size: 17px;

        }

        .aluno-card p {

            color: #687385;

            font-size: 12px;

            margin-top: 3px;

        }

        .aluno-detalhes {

            margin-top: 20px;

            padding-top: 15px;

            border-top:
                1px solid #e9edf2;

        }

        .aluno-detalhes span {

            color: #687385;

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: 1px;

        }

        .aluno-detalhes strong {

            display: block;

            color: #003b70;

            margin-top: 3px;

            font-size: 14px;

        }

        /* =================================
           SEGURANÇA
        ================================= */

        .seguranca {

            margin-top: 25px;

            background: #eaf3fb;

            border:
                1px solid #d5e5f4;

            border-radius: 16px;

            padding: 22px 25px;

            display: flex;

            gap: 15px;

            align-items: flex-start;

        }

        .seguranca-icone {

            width: 35px;

            height: 35px;

            background: #003b70;

            color: white;

            border-radius: 50%;

            display: flex;

            justify-content: center;

            align-items: center;

            font-weight: bold;

            flex-shrink: 0;

        }

        .seguranca h3 {

            color: #003b70;

            font-size: 14px;

        }

        .seguranca p {

            color: #687385;

            font-size: 12px;

            margin-top: 3px;

            line-height: 1.6;

        }

        /* =================================
           VOLTAR
        ================================= */

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

            .perfil-principal {

                flex-direction: column;

                align-items: flex-start;

            }

            .dados-grid {

                grid-template-columns: 1fr;

            }

            .alunos-lista {

                grid-template-columns: 1fr;

            }

            .pagina-cabecalho h2 {

                font-size: 23px;

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

                <?= $inicial ?>

            </div>

        </div>

    </div>

</header>

<!-- =================================
     FAIXA
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

        <a href="resultados.php"
           class="menu-link">

            Resultados

        </a>

        <a href="rotina.php"
           class="menu-link">

            Rotina

        </a>

        <a href="presenca.php"
           class="menu-link">

            Presença

        </a>

        <a href="perfil.php"
           class="menu-link ativo">

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
            PERFIL DO RESPONSÁVEL
        </span>

        <h2>
            Meus dados
        </h2>

        <p>
            Consulte suas informações cadastradas
            no sistema Almatec.
        </p>

    </section>

    <!-- PERFIL -->

    <section class="perfil-principal">

        <div class="perfil-avatar">

            <?= $inicial ?>

        </div>

        <div>

            <h1 class="perfil-nome">

                <?= htmlspecialchars(
                    $usuario['nome']
                ) ?>

            </h1>

            <span class="perfil-tipo">

                Responsável

            </span>

        </div>

    </section>

    <!-- DADOS -->

    <div class="secao-titulo">

        <span>
            INFORMAÇÕES PESSOAIS
        </span>

        <h2>
            Dados cadastrados
        </h2>

    </div>

    <section class="dados-grid">

        <div class="dado-card">

            <span>
                Nome completo
            </span>

            <strong>

                <?= htmlspecialchars(
                    $usuario['nome']
                ) ?>

            </strong>

        </div>

        <div class="dado-card">

            <span>
                E-mail
            </span>

            <strong>

                <?= htmlspecialchars(
                    $usuario['email']
                ) ?>

            </strong>

        </div>

        <div class="dado-card">

            <span>
                Telefone
            </span>

            <strong>

                <?= !empty(
                    $responsavel['telefone']
                )
                    ? htmlspecialchars(
                        $responsavel['telefone']
                    )
                    : 'Não informado'
                ?>

            </strong>

        </div>

        <div class="dado-card">

            <span>
                Tipo de acesso
            </span>

            <strong>
                Responsável
            </strong>

        </div>

    </section>

    <!-- ALUNOS -->

    <div class="secao-titulo">

        <span>
            VÍNCULO ESCOLAR
        </span>

        <h2>
            Alunos vinculados
        </h2>

    </div>

    <section class="alunos-lista">

        <?php if (count($alunos) > 0): ?>

            <?php foreach ($alunos as $aluno): ?>

                <article class="aluno-card">

                    <div class="aluno-topo">

                        <div class="aluno-avatar">

                            <?= strtoupper(
                                substr(
                                    $aluno['nome'],
                                    0,
                                    1
                                )
                            ) ?>

                        </div>

                        <div>

                            <h3>

                                <?= htmlspecialchars(
                                    $aluno['nome']
                                ) ?>

                            </h3>

                            <p>
                                Aluno vinculado
                            </p>

                        </div>

                    </div>

                    <div class="aluno-detalhes">

                        <span>
                            Matrícula
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $aluno['matricula']
                            ) ?>

                        </strong>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <article class="aluno-card">

                <h3>
                    Nenhum aluno vinculado
                </h3>

                <p>
                    Não existem alunos associados
                    a este responsável.
                </p>

            </article>

        <?php endif; ?>

    </section>

    <!-- SEGURANÇA -->

    <section class="seguranca">

        <div class="seguranca-icone">
            i
        </div>

        <div>

            <h3>
                Informações da conta
            </h3>

            <p>

                Seus dados são utilizados para
                identificar o responsável e permitir
                o acesso às informações escolares
                dos alunos vinculados.

            </p>

        </div>

    </section>

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