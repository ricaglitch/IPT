<?php

declare(strict_types=1);

ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');

/**
 * POST METHOD
 *
 * Send a JSON response and stop the script.
 */
function sendJson(array $data, int $statusCode = 200): never
{
    http_response_code($statusCode);

    echo json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );

    exit;
}

/**
 * Validate student data.
 */
function validateStudentData(array $data): array
{
    $errors = [];

    if (empty($data['idNumber']) || !is_string($data['idNumber'])) {
        $errors[] = 'ID Number is required and must be a string.';
    }

    if (empty($data['firstName']) || !is_string($data['firstName'])) {
        $errors[] = 'First Name is required and must be a string.';
    }

    if (empty($data['lastName']) || !is_string($data['lastName'])) {
        $errors[] = 'Last Name is required and must be a string.';
    }

    if (empty($data['age']) || !is_numeric($data['age']) || (int)$data['age'] < 1 || (int)$data['age'] > 120) {
        $errors[] = 'Age is required and must be a number between 1 and 120.';
    }

    if (empty($data['gender']) || !is_string($data['gender'])) {
        $errors[] = 'Gender is required and must be a string.';
    }

    if (empty($data['bloodType']) || !is_string($data['bloodType'])) {
        $errors[] = 'Blood Type is required and must be a string.';
    }

    return $errors;
}

/**
 * Get the highest student ID.
 */
function getHighestStudentId(array $students): int
{
    $maxId = 0;

    foreach ($students as $student) {
        $id = (int) ($student['id'] ?? 0);
        if ($id > $maxId) {
            $maxId = $id;
        }
    }

    return $maxId;
}

/*
|--------------------------------------------------------------------------
| 1. Allow POST requests only
|--------------------------------------------------------------------------
*/

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';

if ($requestMethod !== 'POST') {
    header('Allow: POST');

    sendJson([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    ], 405);
}

/*
|--------------------------------------------------------------------------
| 2. Read and parse JSON input
|--------------------------------------------------------------------------
*/

$inputContent = file_get_contents('php://input');

if ($inputContent === false || empty($inputContent)) {
    sendJson([
        'success' => false,
        'message' => 'Request body is empty.'
    ], 400);
}

try {
    $inputData = json_decode($inputContent,
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $exception) {
    sendJson([
        'success' => false,
        'message' => 'Invalid JSON in request body.'
    ], 400);
}

/*
|--------------------------------------------------------------------------
| 3. Validate input data
|--------------------------------------------------------------------------
*/

$errors = validateStudentData($inputData);

if (!empty($errors)) {
    sendJson([
        'success' => false,
        'errors' => $errors
    ], 400);
}

/*
|--------------------------------------------------------------------------
| 4. Load students.json
|--------------------------------------------------------------------------
*/

$jsonFile = _DIR_ . '/students.json';

if (!file_exists($jsonFile)) {
    sendJson([
        'success' => false,
        'message' => 'students.json was not found.'
    ], 500);
}

$jsonContent = file_get_contents($jsonFile);

if ($jsonContent === false) {
    sendJson([
        'success' => false,
        'message' => 'Unable to read students.json.'
    ], 500);
}

try {
    $jsonData = json_decode($jsonContent,
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $exception) {
    sendJson([
        'success' => false,
        'message' => 'students.json contains invalid JSON.'
    ], 500);
}

$students = $jsonData['students'] ?? null;

if (!is_array($students)) {
    sendJson([
        'success' => false,
        'message' => 'The students array is missing from students.json.'
    ], 500);
}
