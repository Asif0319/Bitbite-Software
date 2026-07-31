<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$API_KEY = getenv('OPENROUTER_API_KEY');

if (!$API_KEY) {
    http_response_code(500);
    echo json_encode([
        'reply' => 'OpenRouter API key is not configured.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$userName    = $input['userName'] ?? 'User';
$goal        = $input['goal']     ?? 'Fat loss & toning';
$weight      = $input['weight']   ?? 70;
$calories    = $input['calories'] ?? 2000;
$budget      = $input['budget']   ?? 500;

$data = [
    'model' => 'ibm-granite/granite-4.1-8b',
    'messages' => [
        [
            'role' => 'system',
            'content' => "You are BitBite AI Coach for {$userName}. Goal: {$goal}. Weight: {$weight}kg. Calories: {$calories} kcal/day. Budget: {$budget} BDT. Be concise and suggest Bangladeshi foods."
        ],
        [
            'role' => 'user',
            'content' => $userMessage
        ]
    ]
];

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $API_KEY,
    'HTTP-Referer: https://yourwebsite.com',
    'X-Title: BitBite AI Coach'
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['choices'])) {
    echo json_encode(['reply' => 'Debug: ' . json_encode($response)]);
    exit;
}

echo json_encode([
    'reply' => $response['choices'][0]['message']['content'] ?? 'Error'
]);
?>
