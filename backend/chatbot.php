<?php
// backend/chatbot.php - Endpoint for chatbot requests

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';

if (empty($prompt)) {
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

// Sanitize input
$prompt = trim($prompt);
$prompt = addslashes($prompt);

// Path to Python executable and script
$python_exe = 'C:/xampp/htdocs/Marketplace_449/devEnv/Scripts/python.exe';
$python_script = __DIR__ . '/charModel.py';

// Create a temporary Python script that imports and calls generate_text
$temp_script = tempnam(sys_get_temp_dir(), 'chat_');
$temp_script .= '.py';

$python_code = <<<'PYTHON'
import sys
sys.path.insert(0, 'C:/xampp/htdocs/Marketplace_449/backend')
from charModel import generate_text

prompt = """ . json_encode($prompt) . """
try:
    response = generate_text(prompt)
    print(response)
except Exception as e:
    print(f"Error: {str(e)}")
PYTHON;

file_put_contents($temp_script, $python_code);

// Execute Python script
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];

$process = proc_open($python_exe . ' ' . $temp_script, $descriptors, $pipes);

if (is_resource($process)) {
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);
    
    unlink($temp_script);
    
    if (!empty($output)) {
        echo json_encode(['response' => trim($output)]);
    } else {
        echo json_encode(['error' => trim($error)]);
    }
} else {
    unlink($temp_script);
    echo json_encode(['error' => 'Failed to execute Python script']);
}
?>
