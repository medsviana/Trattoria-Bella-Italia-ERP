<?php
// ===================== CONEXIÓN BBDD =====================
$conexion = mysqli_connect("localhost","root","","restaurante_italiano") 
    or die("Error al conectar con la base de datos");

// ===================== INICIALIZAR VARIABLES =====================
$compras_post = $_POST['compras'] ?? []; // Recibimos el array de cantidades
$lineas_compra = [];
$total_compra = 0;
$fecha_actual = date("d-m-Y");

// ===================== PROCESAR LOS DATOS =====================
$ids_validos = [];

// 1. Filtramos solo los productos donde la cantidad sea mayor a 0
foreach ($compras_post as $id => $cantidad) {
    $cantidad = intval($cantidad);
    if ($cantidad > 0) {
        $ids_validos[$id] = $cantidad;
    }
}

// 2. Si hay productos seleccionados, buscamos sus datos en la BD
if (!empty($ids_validos)) {
    $ids_str = implode(',', array_keys($ids_validos));
    $sql = "SELECT id_producto, nombre, precio FROM producto WHERE id_producto IN ($ids_str)";
    $resultado = mysqli_query($conexion, $sql);

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $id = $fila['id_producto'];
        $cantidad = $ids_validos[$id];
        $subtotal = $fila['precio'] * $cantidad;
        
        $lineas_compra[] = [
            'id_producto' => $id,
            'nombre' => $fila['nombre'],
            'precio' => $fila['precio'],
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
        
        $total_compra += $subtotal;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Resumen de Compras · Trattoria Bella Italia</title>
<link rel="stylesheet" href="estilo.css">
<style>
    .botones-container { margin-top: 20px; text-align: center; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;}
    .badge-info { background: #17a2b8; color: white; padding: 5px 15px; border-radius: 5px; font-weight: bold; display: inline-block; margin-bottom: 15px;}
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

<main class="facturacion"> <div class="comanda-container">
      <h2>Resumen de Pedido a Proveedores</h2>
      <p style="text-align: center; margin-bottom: 15px;"><strong>Fecha de solicitud:</strong> <?= $fecha_actual ?></p>
      
      <div style="text-align: center;">
        <span class="badge-info">ESTADO: BORRADOR DE COMPRA</span>
      </div>

      <?php if(count($lineas_compra) > 0): ?>
          <table>
            <thead>
              <tr>
                <th>Producto (Ingrediente)</th>
                <th style="text-align:center;">Cantidad</th>
                <th style="text-align:right;">Precio Coste (€)</th>
                <th style="text-align:right;">Subtotal (€)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($lineas_compra as $linea): ?>
                <tr>
                  <td><?= htmlspecialchars($linea['nombre']) ?></td>
                  <td style="text-align:center;"><?= $linea['cantidad'] ?></td>
                  <td style="text-align:right;"><?= number_format($linea['precio'], 2) ?> €</td>
                  <td style="text-align:right;"><?= number_format($linea['subtotal'], 2) ?> €</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" style="text-align:right; font-size:1.2em;"><strong>TOTAL ESTIMADO:</strong></td>
                <td style="text-align:right; font-size:1.2em; color:#c1121f;"><strong><?= number_format($total_compra, 2) ?> €</strong></td>
              </tr>
            </tfoot>
          </table>

          <div class="botones-container">
              <form action="#" method="POST">
                  <?php foreach($lineas_compra as $linea): ?>
                    <input type="hidden" name="compras_confirmadas[<?= $linea['id_producto'] ?>]" value="<?= $linea['cantidad'] ?>">
                  <?php endforeach; ?>
                  <button type="button" class="login-btn" onclick="alert('Funcionalidad de guardado en construcción.');">ENVIAR PEDIDO</button>
              </form>

              <form action="formulario_compras.php" method="POST">
                  <?php 
                  // Devolvemos ocultas todas las cantidades previas
                  foreach($compras_post as $id => $cantidad): ?>
                      <input type="hidden" name="compras_previas[<?= $id ?>]" value="<?= $cantidad ?>">
                  <?php endforeach; ?>
                  
                  <button type="submit" class="login-btn" style="background-color:#555; border-radius: 6px; padding: 12px 40px; min-width: 220px; display: inline-flex; justify-content: center; align-items: center;">
                      ← Volver y Editar
                  </button>
              </form>
          </div>

      <?php else: ?>
          <p style="text-align: center; padding: 20px; color: #c1121f;"><strong>No has seleccionado ningún producto para comprar.</strong></p>
          <div class="botones-container">
              <a href="formulario_compras.php" class="login-btn">← Volver al Listado de Compras</a>
          </div>
      <?php endif; ?>
  </div>

</main>

<footer>
  <div class="footer-container">
    <div class="footer-bottom">
      © 2026 Trattoria Bella Italia · Gestión de Almacén
    </div>
  </div>
</footer>

</body>
</html>