<?php

session_start();

require_once "../config/conexao.php";

/* Verifica login */
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "professor") {
    header("Location: login.php");
    exit;
}


/* Verifica os dados recebidos */
if (
    !isset($_POST["ptd_id"]) ||
    !isset($_POST["notas"])
) {
    die("Dados incompletos.");
}

$ptd_id = (int) $_POST["ptd_id"];
$notas = $_POST["notas"];

$usuario_id = $_SESSION["usuario_id"];


/*
|--------------------------------------------------------------------------
| Busca o professor
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Verifica a permissão do professor
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT turma_id, disciplina_id
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
    die("Você não possui permissão para alterar essas notas.");
}

$turma_id = $vinculo["turma_id"];
$disciplina_id = $vinculo["disciplina_id"];


/*
|--------------------------------------------------------------------------
| Atualiza as notas
|--------------------------------------------------------------------------
*/

foreach ($notas as $aluno_id => $dados) {

    $aluno_id = (int) $aluno_id;

    $nota1 = (float) $dados["nota1"];
    $nota2 = (float) $dados["nota2"];
    $nota3 = (float) $dados["nota3"];
    $nota4 = (float) $dados["nota4"];


    /* Validação das notas */

    if (
        $nota1 < 0 || $nota1 > 10 ||
        $nota2 < 0 || $nota2 > 10 ||
        $nota3 < 0 || $nota3 > 10 ||
        $nota4 < 0 || $nota4 > 10
    ) {
        die("As notas devem estar entre 0 e 10.");
    }


    /*
    | Verifica se o aluno pertence à turma
    */

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


    /*
    | Verifica se já existe resultado
    */

    $sql = "
        SELECT id
        FROM resultados
        WHERE aluno_id = ?
        AND disciplina_id = ?
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->execute([
        $aluno_id,
        $disciplina_id
    ]);

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($resultado) {

        /* Atualiza */

        $sql = "
            UPDATE resultados

            SET
                nota1 = ?,
                nota2 = ?,
                nota3 = ?,
                nota4 = ?

            WHERE id = ?
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->execute([
            $nota1,
            $nota2,
            $nota3,
            $nota4,
            $resultado["id"]
        ]);

    } else {

        /* Cria o resultado */

        $sql = "
            INSERT INTO resultados
            (
                aluno_id,
                disciplina_id,
                nota1,
                nota2,
                nota3,
                nota4
            )

            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conexao->prepare($sql);

        $stmt->execute([
            $aluno_id,
            $disciplina_id,
            $nota1,
            $nota2,
            $nota3,
            $nota4
        ]);
    }
}


/* Volta para a página de notas */

header("Location: notas.php?id=" . $ptd_id . "&sucesso=1");
exit;