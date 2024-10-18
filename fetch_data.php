<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost"; // o la dirección de tu servidor
$username = "root"; 
$password = ""; 
$dbname = "pids";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Obtener el parámetro 'page' desde la solicitud GET
$page = isset($_GET['page']) ? $_GET['page'] : '';

// Establecer el nombre de la tabla y las columnas a consultar según el valor de 'page'
switch ($page) {
    case 'SErbu':
        $table = 'serburecords';  // Tabla para SErbu
        $columns = 'chasis, fan, power, rsp, fc';  // Columnas de SErbu
        break;
    case 'SFretta':
        $table = 'sfrettarecords';  // Tabla para SFretta
        $columns = 'chasis, fan, power';  // Columnas de SFretta
        break;
    case 'SInsbu':
        $table = 'sinsburecords';   // Tabla para SInsbu
        $columns = 'chasis, fan, power';  // Columnas de SInsbu
        break;
    case 'SPabu':
        $table = 'spaburecords';    // Tabla para SPabu
        $columns = 'chasis, fan, power, rsp, ima';  // Columnas de SPabu
        break;
    case 'TestingPathPabu':
        $table = 'testingpathpabu';  // Tabla para TestingPathPabu
        $columns = 'pid, sysassy, syshipot, sysft, test_station';  // Columnas de TestingPathPabu
        break;
    default:
        $table = 'serburecords';  // Tabla por defecto si no se proporciona un valor válido
        $columns = 'chasis, fan, power, rsp, fc';  // Columnas por defecto
        break;
}

// Consulta SQL dinámica
$sql = "SELECT $columns FROM $table";
$result = $conn->query($sql);

// Mostrar datos en formato JSON
if ($result->num_rows > 0) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);  // Si no hay resultados, devolver un array vacío
}

$conn->close();
?>
