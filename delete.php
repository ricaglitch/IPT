<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$inputData = json_decode(file_get_contents("php://input"), true);

$idToDelete = $inputData['id'] ?? $_GET['id'] ?? null;

if (!$idToDelete) {
    http_response_code(400);
    echo json_encode(["error" => "Missing student ID"]);
    exit;
}

$file = 'student.json';
$students = json_decode(file_get_contents($file), true) ?? [];

$found = false;
$deletedStudent = null;


$newStudents = [];
foreach ($students as $student) {
    if ($student['id'] == $idToDelete) {
        $found = true;
        $deletedStudent = $student;
    } else {
        $newStudents[] = $student;
    }
}

if ($found) {
    file_put_contents($file, json_encode(array_values($newStudents), JSON_PRETTY_PRINT));
    http_response_code(200);
    echo json_encode([
        "message" => "Student deleted successfully",
        "data" => $deletedStudent
    ]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Student not found"]);
}
?>
