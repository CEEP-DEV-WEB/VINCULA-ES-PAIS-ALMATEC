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


/* Verifica os dados */

if (
    !isset($_POST["id"]) ||
    !isset($_POST["acao"])
) {
    die("Dados incompletos.");
}


$justificativa_id = (int) $_POST["id"];
$acao = $_POST["acao"];

$observacao = trim(
    $_POST["observacao"] ?? ""
);


/* Permite somente essas ações */

if (
    $acao !== "Aceita" &&
    $acao !== "Recusada"
) {
    die("Ação inválida.");
}


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


/*
|--------------------------------------------------------------------------
| Atualiza a justificativa
|--------------------------------------------------------------------------
|
| A justificativa só será atualizada se existir.
|
*/

$sql = "
    UPDATE justificativas

    SET
        status = ?,
        observacao_professor = ?

    WHERE id = ?
";

$stmt = $conexao->prepare($sql);

$stmt->execute([
    $acao,
    $observacao,
    $justificativa_id
]);


/* Volta para a página */

header(
    "Location: justificativas.php?id=" .
    ($_POST["ptd_id"] ?? "")
);

exit;