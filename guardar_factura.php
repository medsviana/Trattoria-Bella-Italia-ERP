<?php
require_once "conexion.php";
require_once "TCPDF/tcpdf.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_pedido = intval($_POST["id_pedido"]);
    $fecha = $_POST["fecha"];

    // -------------------
    // 1️⃣ Obtener datos del cliente
    // -------------------
    $sqlCliente = "SELECT pe.nombre, pe.apellidos, pe.email
                   FROM pedido p
                   JOIN cliente c ON p.id_cliente = c.id_cliente
                   JOIN persona pe ON c.id_persona = pe.id_persona
                   WHERE p.id_pedido = ?";

    $stmtCliente = mysqli_prepare($conexion, $sqlCliente);
    mysqli_stmt_bind_param($stmtCliente, "i", $id_pedido);
    mysqli_stmt_execute($stmtCliente);
    $resultadoCliente = mysqli_stmt_get_result($stmtCliente);
    $cliente = mysqli_fetch_assoc($resultadoCliente);

    if (!$cliente) {
        echo json_encode(["success" => false, "message" => "Cliente no encontrado"]);
        exit;
    }

    // -------------------
    // 2️⃣ Obtener platos del pedido
    // -------------------
    $sqlPlatos = "SELECT pl.nombre, pp.cantidad, pl.precio,
                  (pp.cantidad * pl.precio) AS subtotal
                  FROM pedido_plato pp
                  JOIN plato pl ON pp.id_plato = pl.id_plato
                  WHERE pp.id_pedido = ?";

    $stmtPlatos = mysqli_prepare($conexion, $sqlPlatos);
    mysqli_stmt_bind_param($stmtPlatos, "i", $id_pedido);
    mysqli_stmt_execute($stmtPlatos);
    $resultadoPlatos = mysqli_stmt_get_result($stmtPlatos);

    $platos = [];
    while ($fila = mysqli_fetch_assoc($resultadoPlatos)) {
        $platos[] = $fila;
    }

    if (count($platos) == 0) {
        echo json_encode(["success" => false, "message" => "El pedido no tiene platos"]);
        exit;
    }

    // -------------------
    // 3️⃣ Calcular total
    // -------------------
    $total = 0;
    foreach ($platos as $plato) {
        $total += $plato["subtotal"];
    }

    // -------------------
    // 4️⃣ Insertar factura
    // -------------------
    $sqlInsert = "INSERT INTO factura (id_pedido, fecha, total) VALUES (?, ?, ?)";
    $stmtInsert = mysqli_prepare($conexion, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, "isd", $id_pedido, $fecha, $total);
    if(!mysqli_stmt_execute($stmtInsert)){
        echo json_encode(["success"=>false,"message"=>mysqli_error($conexion)]);
        exit;
    }

    // -------------------
    // 5️⃣ Generar PDF
    // -------------------
    $pdf = new TCPDF();
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 12);

    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, "FACTURA", 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 8, "Pedido #: " . $id_pedido, 0, 1);
    $pdf->Cell(0, 8, "Fecha: " . $fecha, 0, 1);
    $pdf->Cell(0, 8, "Cliente: " . $cliente["nombre"] . " " . $cliente["apellidos"], 0, 1);
    $pdf->Cell(0, 8, "Email: " . $cliente["email"], 0, 1);

    $pdf->Ln(10);

    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->Cell(80, 10, "Plato", 1);
    $pdf->Cell(30, 10, "Cantidad", 1);
    $pdf->Cell(40, 10, "Precio (€)", 1);
    $pdf->Cell(40, 10, "Subtotal (€)", 1);
    $pdf->Ln();

    $pdf->SetFont('dejavusans', '', 12);
    foreach ($platos as $plato) {
        $pdf->Cell(80, 10, $plato["nombre"], 1);
        $pdf->Cell(30, 10, $plato["cantidad"], 1, 0, 'C');
        $pdf->Cell(40, 10, number_format($plato["precio"], 2) . " €", 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($plato["subtotal"], 2) . " €", 1, 0, 'R');
        $pdf->Ln();
    }

    $pdf->Ln(5);
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 10, "TOTAL: " . number_format($total, 2) . " €", 0, 1, 'R');

    // -------------------
    // Crear carpeta si no existe
    // -------------------
    $carpeta = __DIR__ . "/facturas";
    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    // Ruta absoluta para guardar
    $ruta = $carpeta . "/Factura_Pedido_" . $id_pedido . ".pdf";
    $pdf->Output($ruta, 'F');

    // Ruta relativa para el navegador
    $url_pdf = "facturas/Factura_Pedido_" . $id_pedido . ".pdf";

    if(file_exists($ruta)){
        echo json_encode(["success"=>true, "url_pdf"=>$url_pdf]);
    } else {
        echo json_encode(["success"=>false,"message"=>"El PDF no se ha creado"]);
    }
    exit;
}