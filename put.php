<?php
header("Content-Type: application/json; charset-utf-8");

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$inputData = json_decode(file_get_contents("php://input"), true);

if (!isset($inputData['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing student ID"]);
    exit;
}

$file = 'student.json';
$students = json_decode(file_get_contents($file), true) ?? [];

$found = false;
$updatedStudent = null;


foreach ($students as $key => $student) {
    if ($student['id'] == $inputData['id']) {
        if (isset($inputData['name'])) {
            $students[$key]['name'] = $inputData['name'];
        }
        if (isset($inputData['course'])) {
            $students[$key]['course'] = $inputData['course'];
        }
        
        $found = true;
        $updatedStudent = $students[$key];
        break;
    }
}

if ($found) {
    file_put_contents($file, json_encode($students, JSON_PRETTY_PRINT));
    http_response_code(200);
    echo json_encode([
        "message" => "Student updated successfully",
        "data" => $updatedStudent
    ]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Student not found"]);
}
?>
