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


/* Verifica dados */

if (
    !isset($_POST["ptd_id"]) ||
    !isset($_POST["data"]) ||
    !isset($_POST["presenca"])
) {
    die("Dados incompletos.");
}


$ptd_id = (int) $_POST["ptd_id"];

$data = $_POST["data"];

$presencas = $_POST["presenca"];

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


/* Verifica o vínculo */

$sql = "
    SELECT
        turma_id,
        disciplina_id

    FROM professor_turma_disciplina

    WHERE id = ?
    AND professor_id = ?
";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    $ptd_id,
    $professor_id
]);

$vinculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vinculo) {
    die("Você não possui permissão para esta turma.");
}


$turma_id = $vinculo["turma_id"];

$disciplina_id = $vinculo["disciplina_id"];


/* Salva cada aluno */

foreach ($presencas as $aluno_id => $presente) {

    $aluno_id = (int) $aluno_id;

    $presente = (int) $presente;


    /* Verifica se o aluno pertence à turma */

    $sql = "
        SELECT id
        FROM alunos
        WHERE id = ?
        AND turma_id = ?
    ";

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        $aluno_id,
        $turma_id
    ]);

    if (!$stmt->fetch()) {
        continue;
    }


    /* Verifica se já existe registro */

    $sql = "
        SELECT id
        FROM presencas

        WHERE aluno_id = ?
        AND disciplina_id = ?
        AND data_aula = ?
    ";

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        $aluno_id,
        $disciplina_id,
        $data
    ]);

    $registro = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($registro) {

        /* Atualiza */

        $sql = "
            UPDATE presencas

            SET presente = ?

            WHERE id = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->execute([
            $presente,
            $registro["id"]
        ]);

    } else {

        /* Cria */

        $sql = "
            INSERT INTO presencas
            (
                aluno_id,
                disciplina_id,
                data_aula,
                presente
            )

            VALUES (?, ?, ?, ?)
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->execute([
            $aluno_id,
            $disciplina_id,
            $data,
            $presente
        ]);
    }
}


/* Volta para a página */

header(
    "Location: faltas.php?id=" .
    $ptd_id .
    "&data=" .
    urlencode($data) .
    "&sucesso=1"
);

exit;