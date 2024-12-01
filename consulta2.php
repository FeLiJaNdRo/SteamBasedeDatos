<?php
require_once '../db/conexion.php';

$resultados = [];
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['precio_min'], $_GET['precio_max'], $_GET['so'])) {
    $precio_min = floatval($_GET['precio_min']);
    $precio_max = floatval($_GET['precio_max']);
    $sistemaOperativo = trim($_GET['so']);

    try {
        $query = "SELECT Name, c.Category_name, gr.Genre_name, Price, Windows, Mac, Linux
                  FROM games g
                  JOIN game_categories gc ON g.AppID = gc.AppID
                  JOIN categories c ON gc.CategoryID = c.CategoryID
                  JOIN game_genres gg ON g.AppID = gg.AppID
                  JOIN genres gr ON gg.GenreID = gr.GenreID
                  WHERE Price BETWEEN :precio_min AND :precio_max
                  AND (
                      (:so = 'Windows' AND Windows = TRUE) OR
                      (:so = 'Mac' AND Mac = TRUE) OR
                      (:so = 'Linux' AND Linux = TRUE)
                  )";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'precio_min' => $precio_min,
            'precio_max' => $precio_max,
            'so' => $sistemaOperativo
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
    <title>Encontrar los Juegos de Acuerdo a un Rango de Precio $$$ y un Sistema Operativo Compatible  </title>
</head>
<body>
    <h1>Encontrar los Juegos de Acuerdo a un Rango de Precio $$$ y un Sistema Operativo Compatible  </h1>
    <form method="GET">
        Precio Mínimo: <input type="number" name="precio_min" step="0.01" value="<?= htmlspecialchars($_GET['precio_min'] ?? '') ?>">
        Precio Máximo: <input type="number" name="precio_max" step="0.01" value="<?= htmlspecialchars($_GET['precio_max'] ?? '') ?>">
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
                <th>Categoría</th>
                <th>Género</th>
                <th>Precio</th>
                <th>Windows</th>
                <th>Mac</th>
                <th>Linux</th>
            </tr>
            <?php foreach ($resultados as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['genre_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['price'] ?? '0.00') ?></td>
                    <td><?= isset($row['windows']) && $row['windows'] ? 'Sí' : 'No' ?></td>
                    <td><?= isset($row['mac']) && $row['mac'] ? 'Sí' : 'No' ?></td>
                    <td><?= isset($row['linux']) && $row['linux'] ? 'Sí' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
   <?php include "../templates/footer.php"; ?>
</body>
</html>
