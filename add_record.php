<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$servername = "localhost"; // o la dirección de tu servidor
$username = "root"; 
$password = ""; 
$dbname = "pids";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos enviados por POST
$page = isset($_POST['page']) ? $_POST['page'] : '';
$chasis = isset($_POST['chasis']) ? $_POST['chasis'] : '';
$fan = isset($_POST['fan']) ? $_POST['fan'] : '';
$power = isset($_POST['power']) ? $_POST['power'] : '';
$rsp = isset($_POST['rsp']) ? $_POST['rsp'] : '';
$fc = isset($_POST['fc']) ? $_POST['fc'] : '';
$ima = isset($_POST['ima']) ? $_POST['ima'] : '';
$pid = isset($_POST['pid']) ? $_POST['pid'] : '';
$sysassy = isset($_POST['sysassy']) ? $_POST['sysassy'] : '';
$syshipot = isset($_POST['syshipot']) ? $_POST['syshipot'] : '';
$sysft = isset($_POST['sysft']) ? $_POST['sysft'] : '';
$test_station = isset($_POST['test_station']) ? $_POST['test_station'] : '';

// Lógica para seleccionar la tabla y columnas basadas en la página
switch ($page) {
    case 'SErbu':
        $table = 'serburecords';
        $sql = "INSERT INTO $table (chasis, fan, power, rsp, fc) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $chasis, $fan, $power, $rsp, $fc);
        break;
    case 'SFretta':
        $table = 'sfrettarecords';
        $sql = "INSERT INTO $table (chasis, fan, power) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $chasis, $fan, $power);
        break;
    case 'SInsbu':
        $table = 'sinsburecords';
        $sql = "INSERT INTO $table (chasis, fan, power) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $chasis, $fan, $power);
        break;
    case 'SPabu':
        $table = 'spaburecords';
        $sql = "INSERT INTO $table (chasis, fan, power, rsp, ima) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $chasis, $fan, $power, $rsp, $ima);
        break;
    case 'TestingPathPabu':
        $table = 'testpathrecords';
        $sql = "INSERT INTO $table (pid, sysassy, syshipot, sysft, test_station) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $pid, $sysassy, $syshipot, $sysft, $test_station);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Página no válida']);
        exit();
}

// Ejecutar la consulta y devolver el resultado en formato JSON
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registro añadido correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al añadir el registro']);
}

$stmt->close();
$conn->close();
?>
