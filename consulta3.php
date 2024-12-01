<?php
require_once '../db/conexion.php';

$resultados = [];
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['genero'], $_GET['precio'], $_GET['idioma'])) {
    $genero = trim($_GET['genero']);
    $precio = floatval($_GET['precio']);
    $idioma = trim($_GET['idioma']);

    try {
        $query = "SELECT Name, gr.Genre_name, Price, Positive, Windows, Mac, Linux, l.Supported_languages
                  FROM games g
                  JOIN game_genres gg ON g.AppID = gg.AppID
                  JOIN genres gr ON gg.GenreID = gr.GenreID
                  JOIN game_languages gl ON g.AppID = gl.AppID
                  JOIN languages l ON gl.LanguageID = l.LanguageID
                  WHERE Price = :precio
                  AND gr.Genre_name = :genero
                  AND l.Supported_languages ILIKE '%' || :idioma || '%'
                  AND Windows = TRUE AND Mac = TRUE AND Linux = TRUE
                  ORDER BY Positive DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'precio' => $precio,
            'genero' => $genero,
            'idioma' => $idioma
        ]);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            $mensaje_error = "No se encontraron resultados para los parámetros ingresados.";
        }
    } catch (PDOException $e) {
        $mensaje_error = "Error en la consulta: " . htmlspecialchars($e->getMessage());
    }
} else {
    $mensaje_error = "Por favor, completa todos los campos del formulario.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juegos de un Género, Precio $$$, e Idioma Particular</title>
</head>
<body>
    <h1>Juegos de un Género, Precio $$$, e Idioma Particular</h1>
    <form method="GET">
        Género: <input type="text" name="genero" value="<?= htmlspecialchars($_GET['genero'] ?? '') ?>">
        Precio: <input type="number" name="precio" step="0.01" value="<?= htmlspecialchars($_GET['precio'] ?? '') ?>">
        Idioma: <input type="text" name="idioma" value="<?= htmlspecialchars($_GET['idioma'] ?? '') ?>">
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
                <th>Precio</th>
                <th>Positivas</th>
                <th>Windows</th>
                <th>Mac</th>
                <th>Linux</th>
                <th>Idiomas</th>
            </tr>
            <?php foreach ($resultados as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['genre_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['price'] ?? '0.00') ?></td>
                    <td><?= htmlspecialchars($row['positive'] ?? '0') ?></td>
                    <td><?= isset($row['windows']) && $row['windows'] ? 'Sí' : 'No' ?></td>
                    <td><?= isset($row['mac']) && $row['mac'] ? 'Sí' : 'No' ?></td>
                    <td><?= isset($row['linux']) && $row['linux'] ? 'Sí' : 'No' ?></td>
                    <td><?= htmlspecialchars($row['supported_languages'] ?? 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
   <?php include "../templates/footer.php"; ?>
</body>
</html>
