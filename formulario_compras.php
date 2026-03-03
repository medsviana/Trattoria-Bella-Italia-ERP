<?php
// ===================== CONEXIÓN BBDD =====================
$conexion = mysqli_connect("localhost","root","","restaurante_italiano")
    or die("Error al conectar con la base de datos");

$productos = [];
$sql = "SELECT id_producto, nombre, precio FROM producto";
$resultado = mysqli_query($conexion, $sql);

while ($fila = mysqli_fetch_array($resultado)) {
    $productos[] = $fila;
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Compras</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        .botones-container { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-top: 30px; }
    </style>
</head>
<body>

    <header>
        <div class="header-container">
            <a href="index.php">
            <div class="logo">
                <div class="logo-icon">🍝</div>
                <span>Trattoria Bella Italia</span>
            </div>
            </a>
        </div>
    </header>

    <div class="comanda-container">
        <h1>SOLICITUD DE COMPRA</h1>

        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Precio Coste</th>
                        <th>Cantidad a Pedir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Recogemos las compras previas si venimos del botón "Volver"
                    $compras_previas = $_POST['compras_previas'] ?? [];

                    if (empty($productos)) {
                        echo "<tr><td colspan='4' style='text-align:center;'>No hay productos registrados en la BD.</td></tr>";
                    } else {
                        foreach ($productos as $prod) {
                            $id = $prod['id_producto'];
                            
                            // Si existe una cantidad previa para este ID, la usamos. Si no, ponemos 0.
                            $cantidad_defecto = isset($compras_previas[$id]) ? $compras_previas[$id] : 0;

                            echo "<tr>";
                            echo "<td>" . $id . "</td>";
                            echo "<td>" . htmlspecialchars($prod['nombre']) . "</td>";
                            echo "<td>" . number_format($prod['precio'], 2) . " €</td>";
                            
                            echo "<td style='text-align:center;'>
                                    <input type='number'
                                           name='compras[" . $id . "]'
                                           value='" . $cantidad_defecto . "' min='0'>
                                  </td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
            
            <div class="botones-container">
                <button type="submit" formaction="resumen_compras.php" class="login-btn" style="background-color: #555;">VER RESUMEN</button>
                <button type="submit" formaction="almacen.php" formmethod="get" class="login-btn" style="background-color: #333;">VOLVER AL ALMACÉN</button>
            </div>
        </form>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-bottom">© 2026 Trattoria Bella Italia</div>
        </div>
    </footer>
</body>
</html>