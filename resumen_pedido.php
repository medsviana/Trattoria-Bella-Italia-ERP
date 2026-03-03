<?php
// ===================== CONEXIÓN BBDD =====================
$conexion = mysqli_connect("localhost","root","","restaurante_italiano") 
    or die("Error al conectar con la base de datos");

// ===================== INICIALIZAR VARIABLES =====================
$productos = $_POST['producto'] ?? [];
$lineas = [];
$total = 0;
$fecha_pedido = date("d-m-Y");
$id_pedido = null;
$es_confirmado = false; 

// ===================== CASO 1: Viene de formulariocomandas.php (NUEVA COMANDA) =====================
if (!empty($productos) && !isset($_GET['id'])) {
    
    // 1. Obtener precios de la BBDD
    $ids = implode(',', array_keys($productos));
    $result = mysqli_query($conexion,"SELECT * FROM plato WHERE id_plato IN ($ids)");
    $productos_db = [];
    while($fila = mysqli_fetch_assoc($result)){
        $productos_db[$fila['id_plato']] = $fila;
    }

    // 2. Calcular los subtotales
    foreach($productos as $id => $cantidad){
        $cantidad = intval($cantidad);
        if($cantidad > 0 && isset($productos_db[$id])){
            $p = $productos_db[$id];
            $subtotal = $p['precio'] * $cantidad;
            $lineas[] = [
                'id' => $p['id_plato'],
                'producto' => $p['nombre'],
                'cantidad' => $cantidad,
                'precio' => $p['precio'],
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }
    }

    // 3. ¡LA MAGIA! GUARDAR EL PEDIDO INMEDIATAMENTE COMO PENDIENTE
    if ($total > 0) {
        $id_cliente_generico = 1; 
        
        // Creamos la cabecera del pedido
        $sql_insert = "INSERT INTO pedido (id_cliente, fecha) VALUES ($id_cliente_generico, NOW())";
        mysqli_query($conexion, $sql_insert);
        
        // Recuperamos el ID que le acaba de asignar la base de datos
        $id_pedido = mysqli_insert_id($conexion); 

        // Insertamos los platos de este pedido
        foreach ($lineas as $l) {
            $sql_linea = "INSERT INTO pedido_plato (id_pedido, id_plato, cantidad) VALUES ($id_pedido, {$l['id']}, {$l['cantidad']})";
            mysqli_query($conexion, $sql_linea);
        }
        
        // Como acaba de nacer, no está confirmado (no tiene factura)
        $es_confirmado = false; 
    }
}

// ===================== CASO 2: Viene de gestion_pedidos.php (VER EXISTENTE) =====================
elseif(isset($_GET['id'])){
    $id_pedido = intval($_GET['id']);
    
    $sql_lineas = "SELECT pp.cantidad, p.nombre, p.precio, p.id_plato
                   FROM pedido_plato pp
                   INNER JOIN plato p ON pp.id_plato = p.id_plato
                   WHERE pp.id_pedido = $id_pedido";
    $res_lineas = mysqli_query($conexion, $sql_lineas);
    while ($fila = mysqli_fetch_assoc($res_lineas)){
        $subtotal = $fila['precio'] * $fila['cantidad'];
        $lineas[] = [
            'id' => $fila['id_plato'] ?? 0,
            'producto' => $fila['nombre'] ?? '',
            'cantidad' => $fila['cantidad'] ?? 0,
            'precio' => $fila['precio'] ?? 0,
            'subtotal' => $subtotal
        ];
        $total += $subtotal;
    }

    $sql_pedido = "SELECT p.fecha, (SELECT COUNT(*) FROM factura f WHERE f.id_pedido = p.id_pedido) as procesado 
                   FROM pedido p WHERE p.id_pedido = $id_pedido";
    $res_pedido = mysqli_query($conexion, $sql_pedido);
    if($fila_pedido = mysqli_fetch_assoc($res_pedido)){
        $fecha_pedido = date("d-m-Y", strtotime($fila_pedido['fecha']));
        $es_confirmado = ($fila_pedido['procesado'] > 0);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Resumen de Pedido · Trattoria Bella Italia</title>
<link rel="stylesheet" href="estilo.css">
<style>
    .botones-container { margin-top: 20px; text-align: center; }
    .botones-container .login-btn { display: inline-block; margin: 5px 10px; cursor:pointer; }
    .estado-badge { display: inline-block; padding: 5px 15px; border-radius: 5px; font-weight: bold; margin-bottom: 15px; }
    .badge-verde { background: #d4edda; color: #155724; }
    .badge-rojo { background: #f8d7da; color: #721c24; }
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

<main class="facturacion">

<?php if(count($lineas) > 0 && $id_pedido): ?>
  <h2>Resumen de la Comanda</h2>

  <p><strong>ID Pedido:</strong> #<?= $id_pedido ?> | <strong>Fecha:</strong> <?= $fecha_pedido ?></p>
  <?php if($es_confirmado): ?>
      <div class="estado-badge badge-verde">ESTADO: CONFIRMADO ✅</div>
  <?php else: ?>
      <div class="estado-badge badge-rojo">ESTADO: PENDIENTE (Enviado a Cocina) ⏳</div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio (€)</th>
        <th>Subtotal (€)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($lineas as $l): ?>
        <tr>
          <td><?= htmlspecialchars($l['producto'] ?? '') ?></td>
          <td><?= intval($l['cantidad'] ?? 0) ?></td>
          <td><?= number_format($l['precio'] ?? 0,2) ?></td>
          <td><?= number_format($l['subtotal'] ?? 0,2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3"><strong>TOTAL</strong></td>
        <td><strong><?= number_format($total,2) ?> €</strong></td>
      </tr>
    </tfoot>
  </table>

  <div class="botones-container">
      
      <?php if(!$es_confirmado): ?>
      <form action="gestion_pedidos.php" method="POST" style="display:inline-block;">
          <input type="hidden" name="pedidos_check[]" value="<?= $id_pedido ?>">
          <input type="submit" name="procesar_pedidos" value="CONFIRMAR PAGO Y FACTURAR" class="login-btn">
      </form>
      <?php endif; ?>

      <a href="formulario_comandas.php" class="login-btn" style="background-color:#555;">← Volver a Comanda</a>
      <a href="gestion_pedidos.php" class="login-btn" style="background-color:#333;">Ir a Gestión de Pedidos →</a>
  </div>

<?php else: ?>
  <p>No se ha seleccionado ningún producto o el pedido no existe.</p><br>
  <div class="botones-container">
      <a href="formulario_comandas.php" class="login-btn">← Volver a Comanda</a>
      <a href="gestion_pedidos.php" class="login-btn" style="background-color:#333;">Ir a Gestión de Pedidos →</a>
  </div>
<?php endif; ?>

</main>

<footer>
  <div class="footer-container">
    <div class="footer-bottom">
      © 2026 Trattoria Bella Italia · Auténtica cocina italiana
    </div>
  </div>
</footer>

</body>
</html>