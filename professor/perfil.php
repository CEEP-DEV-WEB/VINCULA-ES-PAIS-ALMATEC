<?php
session_start();
require_once "../config/conexao.php";

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'professor') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

/* ==========================================
   BUSCAR DADOS DO PROFESSOR
========================================== */

$sql = "
    SELECT 
        p.id AS professor_id,
        u.nome,
        u.email,
        u.tipo
    FROM professores p
    INNER JOIN usuarios u ON u.id = p.usuario_id
    WHERE p.usuario_id = ?
";

$stmt = $conexao->prepare($sql);
$stmt->execute([$usuario_id]);

$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    die("Professor não encontrado.");
}


/* ==========================================
   BUSCAR TURMAS E DISCIPLINAS
========================================== */

$sql = "
    SELECT
        t.identificacao AS turma,
        d.nome AS disciplina
    FROM professor_turma_disciplina ptd
    INNER JOIN turmas t 
        ON t.id = ptd.turma_id
    INNER JOIN disciplinas d 
        ON d.id = ptd.disciplina_id
    WHERE ptd.professor_id = ?
    ORDER BY t.serie, t.identificacao, d.nome
";

$stmt = $conexao->prepare($sql);
$stmt->execute([$professor['professor_id']]);

$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* PRIMEIRA LETRA DO NOME */

$primeiraLetra = strtoupper(
    substr($professor['nome'], 0, 1)
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu Perfil - ALMATEC</title>

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


        /* ==============================
           TOPO
        ============================== */

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


        /* ==============================
           USUÁRIO
        ============================== */

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


        /* ==============================
           FAIXA INSTITUCIONAL
        ============================== */

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


        /* ==============================
           MENU
        ============================== */

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

        .menu-sair:hover {
            color: #a51f1f;
        }


        /* ==============================
           CONTAINER
        ============================== */

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


        /* ==============================
           CARD PERFIL
        ============================== */

        .perfil-card {
            background: white;

            border-radius: 12px;

            padding: 30px;

            margin-bottom: 25px;

            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .perfil-topo {
            display: flex;
            align-items: center;

            gap: 20px;

            margin-bottom: 30px;
        }

        .perfil-avatar {
            width: 80px;
            height: 80px;

            border-radius: 50%;

            background: #1646a0;
            color: white;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 30px;
            font-weight: bold;
        }

        .perfil-topo h2 {
            font-size: 23px;
            margin-bottom: 6px;
        }

        .perfil-topo p {
            color: #777;
        }


        /* ==============================
           DADOS
        ============================== */

        .dados {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 20px;
        }

        .dado {
            background: #f7f8fa;

            padding: 18px;

            border-radius: 8px;

            border-left: 4px solid #1646a0;
        }

        .dado span {
            display: block;

            font-size: 12px;

            color: #777;

            margin-bottom: 7px;
        }

        .dado strong {
            font-size: 16px;

            color: #333;

            word-break: break-word;
        }


        /* ==============================
           TURMAS
        ============================== */

        .titulo-card {
            margin-bottom: 20px;

            font-size: 21px;

            color: #222;
        }

        .turmas-lista {
            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(250px, 1fr));

            gap: 15px;
        }

        .turma-card {
            background: #f7f8fa;

            padding: 18px;

            border-radius: 8px;

            border-left: 4px solid #1646a0;
        }

        .turma-card strong {
            display: block;

            margin-bottom: 6px;

            color: #333;
        }

        .turma-card span {
            color: #777;

            font-size: 14px;
        }

        .sem-turmas {
            color: #777;
            padding: 10px 0;
        }


        /* ==============================
           RODAPÉ
        ============================== */

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


        /* ==============================
           RESPONSIVO
        ============================== */

        @media (max-width: 700px) {

            .dados {
                grid-template-columns: 1fr;
            }

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
                    <?= htmlspecialchars($professor['nome']) ?>
                </strong>

            </div>


            <div class="usuario-avatar">

                <?= $primeiraLetra ?>

            </div>

        </div>

    </div>


    <div class="faixa-institucional">

        <div class="faixa-azul"></div>

        <div class="faixa-vermelha"></div>

        <div class="faixa-branca"></div>

    </div>

</header>



<!-- ==============================
     MENU
============================== -->

<nav class="menu">

    <div class="menu-conteudo">


        <a href="dashboard.php" class="menu-link">
            Início
        </a>


        <a href="turmas.php" class="menu-link">
            Minhas turmas
        </a>


        <a href="perfil.php" class="menu-link ativo">
            Meu perfil
        </a>


        <a href="logout.php" class="menu-link menu-sair">
            Sair
        </a>


    </div>

</nav>



<!-- ==============================
     CONTEÚDO
============================== -->

<main class="container">


    <h1 class="titulo-secao">
        Meu perfil
    </h1>


    <p class="subtitulo">
        Consulte suas informações como professor.
    </p>



    <!-- ==========================
         INFORMAÇÕES DO PROFESSOR
    =========================== -->

    <div class="perfil-card">


        <div class="perfil-topo">


            <div class="perfil-avatar">

                <?= $primeiraLetra ?>

            </div>


            <div>

                <h2>
                    <?= htmlspecialchars($professor['nome']) ?>
                </h2>

                <p>
                    Professor ALMATEC
                </p>

            </div>


        </div>



        <div class="dados">


            <div class="dado">

                <span>
                    Nome completo
                </span>

                <strong>
                    <?= htmlspecialchars($professor['nome']) ?>
                </strong>

            </div>



            <div class="dado">

                <span>
                    E-mail
                </span>

                <strong>
                    <?= htmlspecialchars($professor['email']) ?>
                </strong>

            </div>



            <div class="dado">

                <span>
                    Tipo de usuário
                </span>

                <strong>
                    Professor
                </strong>

            </div>


        </div>


    </div>



    <!-- ==========================
         TURMAS E DISCIPLINAS
    =========================== -->

    <div class="perfil-card">


        <h2 class="titulo-card">
            Minhas disciplinas e turmas
        </h2>


        <?php if (count($turmas) > 0): ?>


            <div class="turmas-lista">


                <?php foreach ($turmas as $turma): ?>


                    <div class="turma-card">


                        <strong>
                            <?= htmlspecialchars($turma['turma']) ?>
                        </strong>


                        <span>
                            <?= htmlspecialchars($turma['disciplina']) ?>
                        </span>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <p class="sem-turmas">
                Nenhuma turma ou disciplina vinculada ao professor.
            </p>


        <?php endif; ?>


    </div>


</main>



<!-- ==============================
     RODAPÉ
============================== -->

<footer class="rodape">

    <div class="rodape-conteudo">

        <span>
            © <?= date('Y') ?> ALMATEC
        </span>

        <span>
            Sistema de Gestão Escolar
        </span>

    </div>

</footer>


</body>

</html>