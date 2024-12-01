<?php
require_once '../db/conexion.php'; 

$resultados = [];
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['genero'], $_GET['so'])) {
    $genero = trim($_GET['genero']);
    $sistemaOperativo = trim($_GET['so']);

    if (!empty($genero) && !empty($sistemaOperativo)) {
        try {
            $query = "SELECT Name, Genres, Positive, Windows, Mac, Linux
                      FROM games g
                      JOIN game_genres gg ON g.AppID = gg.AppID
                      JOIN genres gr ON gg.GenreID = gr.GenreID
                      WHERE gr.Genre_name = :genero
                      AND Positive > 0
                      AND (
                          (:so = 'Windows' AND Windows = TRUE) OR
                          (:so = 'Mac' AND Mac = TRUE) OR
                          (:so = 'Linux' AND Linux = TRUE)
                      )";

            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'genero' => $genero,
                'so' => $sistemaOperativo
            ]);

            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($resultados);
            //die();


            if (empty($resultados)) {
                $mensaje_error = "No se encontraron resultados para el género '$genero' y el sistema operativo '$sistemaOperativo'.";
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error en la consulta: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $mensaje_error = "Por favor, ingresa un género y selecciona un sistema operativo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encontrar Juegos con un Género en Particular y un Sistema Operativo Compatible </title>
</head>
<body>
    <h1>Encontrar Juegos con un Género en Particular y un Sistema Operativo Compatible </h1>
    <form method="GET">
        Género: <input type="text" name="genero" value="<?= htmlspecialchars($_GET['genero'] ?? '') ?>">
        Sistema Operativo:
        <select name="so">
            <option value="Windows" <?= ($_GET['so'] ?? '') === 'Windows' ? 'selected' : '' ?>>Windows</option>
            <option value="Mac" <?= ($_GET['so'] ?? '') === 'Mac' ? 'selected' : '' ?>>Mac</option>
            <option value="Linux" <?= ($_GET['so'] ?? '') === 'Linux' ? 'selected' : '' ?>>Linux</option>
        </select>
        <button type="submit">Buscar</button>
    </form>

    <?php if (!empty($mensaje_error)): ?>
        <p style="color: red;"><?= htmlspecialchars($mensaje_error) ?></p>
    <?php endif; ?>

    <?php if (!empty($resultados)): ?>
        <table border="1">
            <tr>
                <th>Nombre</th>
                <th>Género</th>
                <th>Positivas</th>
                <th>Windows</th>
                <th>Mac</th>
                <th>Linux</th>
            </tr>
            <?php foreach ($resultados as $row): ?>
                <tr>
                    <td><?= isset($row["name"]) ? htmlspecialchars($row["name"]) : 'N/A' ?></td>
                    <td><?= isset($row["genres"]) ? htmlspecialchars($row["genres"]) : 'N/A' ?></td>
                    <td><?= isset($row["positive"]) ? htmlspecialchars($row['positive']) : '0' ?></td>
                    <td><?= isset($row["windows"]) && $row["windows"] ? 'Sí' : 'No' ?></td>
                    <td><?= isset($row["mac"]) && $row["mac"] ? 'Sí' : 'No' ?></td>
                    <td><?= isset($row["linux"]) && $row["linux"] ? 'Sí' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
   <?php include "../templates/footer.php"; ?>
</body>
</html>
