<?php

session_start();

require_once "../config/conexao.php";

// Verifica se o responsável está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Busca o responsável
$sql = "SELECT id, telefone
        FROM responsaveis
        WHERE usuario_id = ?";

$stmt = $conexao->prepare($sql);
$stmt->execute([$usuario_id]);

$responsavel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$responsavel) {
    die("Responsável não encontrado.");
}

$responsavel_id = $responsavel['id'];

// Busca o nome do responsável
$sql = "SELECT nome
        FROM usuarios
        WHERE id = ?";

$stmt = $conexao->prepare($sql);
$stmt->execute([$usuario_id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$nome_responsavel = $usuario['nome'] ?? 'Responsável';

// Busca os alunos vinculados
$sql = "SELECT 
            a.id,
            a.matricula,
            u.nome
        FROM responsavel_aluno ra
        INNER JOIN alunos a 
            ON ra.aluno_id = a.id
        INNER JOIN usuarios u 
            ON a.usuario_id = u.id
        WHERE ra.responsavel_id = ?";

$stmt = $conexao->prepare($sql);
$stmt->execute([$responsavel_id]);

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Área do Responsável | Almatec</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <!-- BARRA SUPERIOR -->

    <header class="topo">

        <div class="topo-conteudo">

            <div class="logo-area">

                <div class="logo-icon">
                    A
                </div>

                <div>
                    <h1>ALMATEC</h1>
                    <span>Educação</span>
                </div>

            </div>


            <div class="usuario-area">

                <div class="usuario-info">

                    <span class="usuario-label">
                        Responsável
                    </span>

                    <strong>
                        <?= htmlspecialchars($nome_responsavel) ?>
                    </strong>

                </div>

                <div class="usuario-avatar">
                    <?= strtoupper(substr($nome_responsavel, 0, 1)) ?>
                </div>

            </div>

        </div>

    </header>


    <!-- LINHA INSTITUCIONAL -->

    <div class="faixa-institucional">

        <div class="faixa-azul"></div>
        <div class="faixa-vermelha"></div>
        <div class="faixa-branca"></div>

    </div>


    <!-- MENU -->

    <nav class="menu">

        <div class="menu-conteudo">

            <a href="dashboard.php" class="menu-link ativo">
                Início
            </a>

            <a href="resultados.php" class="menu-link">
                Resultados
            </a>

            <a href="rotina.php" class="menu-link">
                Rotina
            </a>

            <a href="presenca.php" class="menu-link">
                Presença
            </a>

            <a href="perfil.php" class="menu-link">
                Meu perfil
            </a>

            <a href="logout.php" class="menu-sair">
                Sair
            </a>

        </div>

    </nav>


    <!-- CONTEÚDO -->

    <main class="container">

        <!-- CABEÇALHO -->

        <section class="boas-vindas">

            <div>

                <span class="subtitulo">
                    ÁREA DO RESPONSÁVEL
                </span>

                <h2>
                    Olá, <?= htmlspecialchars($nome_responsavel) ?>!
                </h2>

                <p>
                    Acompanhe a vida escolar e o desenvolvimento
                    do aluno de forma simples e segura.
                </p>

            </div>

            <div class="boas-vindas-detalhe">

                <div class="circulo-azul"></div>

                <div class="circulo-vermelho"></div>

            </div>

        </section>


        <?php if (count($alunos) > 0): ?>


            <?php foreach ($alunos as $aluno): ?>

                <!-- ALUNO -->

                <section class="aluno-destaque">

                    <div class="aluno-identificacao">

                        <div class="aluno-avatar">
                            <?= strtoupper(substr($aluno['nome'], 0, 1)) ?>
                        </div>

                        <div>

                            <span>
                                ALUNO VINCULADO
                            </span>

                            <h3>
                                <?= htmlspecialchars($aluno['nome']) ?>
                            </h3>

                            <p>
                                Matrícula:
                                <strong>
                                    <?= htmlspecialchars($aluno['matricula']) ?>
                                </strong>
                            </p>

                        </div>

                    </div>

                    <div class="status-aluno">

                        <span class="status-ponto"></span>

                        Acompanhamento ativo

                    </div>

                </section>


                <!-- TÍTULO -->

                <div class="titulo-secao">

                    <div>

                        <span>
                            ACOMPANHAMENTO ESCOLAR
                        </span>

                        <h2>
                            Acesso rápido
                        </h2>

                    </div>

                    <p>
                        Consulte as principais informações escolares.
                    </p>

                </div>


                <!-- CARDS -->

                <section class="cards">


                    <!-- RESULTADOS -->

                    <a
                        href="resultados.php?aluno=<?= $aluno['id'] ?>"
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
                            Resultados
                        </h3>

                        <p>
                            Consulte notas, avaliações,
                            médias e desempenho escolar.
                        </p>

                        <span class="card-link">
                            Ver resultados
                        </span>

                    </a>


                    <!-- ROTINA -->

                    <a
                        href="rotina.php?aluno=<?= $aluno['id'] ?>"
                        class="card card-rotina"
                    >

                        <div class="card-topo">

                            <div class="icone">
                                📅
                            </div>

                            <span class="seta">
                                →
                            </span>

                        </div>

                        <h3>
                            Rotina escolar
                        </h3>

                        <p>
                            Acompanhe horários,
                            atividades e compromissos.
                        </p>

                        <span class="card-link">
                            Ver rotina
                        </span>

                    </a>


                    <!-- PRESENÇA -->

                    <a
                        href="presenca.php?aluno=<?= $aluno['id'] ?>"
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
                            Presença
                        </h3>

                        <p>
                            Consulte a frequência e
                            o histórico de presença.
                        </p>

                        <span class="card-link">
                            Ver presença
                        </span>

                    </a>


                    <!-- PERFIL -->

                    <a
                        href="perfil.php"
                        class="card card-perfil"
                    >

                        <div class="card-topo">

                            <div class="icone">
                                👤
                            </div>

                            <span class="seta">
                                →
                            </span>

                        </div>

                        <h3>
                            Meu perfil
                        </h3>

                        <p>
                            Visualize seus dados e
                            informações cadastrais.
                        </p>

                        <span class="card-link">
                            Ver perfil
                        </span>

                    </a>


                </section>


                <!-- INFORMAÇÃO -->

                <section class="informacao">

                    <div class="informacao-icone">
                        ℹ
                    </div>

                    <div>

                        <h3>
                            Acompanhamento escolar
                        </h3>

                        <p>
                            As informações apresentadas nesta área
                            são disponibilizadas pela instituição de ensino
                            para acompanhamento do aluno.
                        </p>

                    </div>

                </section>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- SEM ALUNO -->

            <section class="sem-aluno">

                <div class="sem-aluno-icone">
                    👨‍🎓
                </div>

                <h2>
                    Nenhum aluno vinculado
                </h2>

                <p>
                    Ainda não existe nenhum aluno associado
                    a este responsável.
                </p>

                <span>
                    Entre em contato com a instituição de ensino
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
            Sistema destinado ao acompanhamento da comunidade escolar.
        </p>

    </footer>


</body>

</html>