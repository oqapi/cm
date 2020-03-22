<?php
require_once 'config.php';

date_default_timezone_set('UTC');
$protocol = explode(':', IRMA_SERVER_URL, 2)[0];

$sigrequests = [
    'email-signature' => [
        '@context' => 'https://irma.app/ld/request/signature/v2',
        'message' => [
            'nl' => 'Hierbij geef ik expliciete toestemming aan coronameting.nl om de antwoorden op te slaan en geanonimiseerd te gebruiken voor onderzoek.',
            'en' =>  'I explicitly grant consent to coronameting.nl to use my answers for research.',
        ],
        'disclose' => [
            [['pbdf.pbdf.email.email']],
        ],
    ]
];

$sprequests = [
    'email' => [
        '@context' => 'https://irma.app/ld/request/disclosure/v2',
        'disclose' => [
            [['pbdf.pbdf.email.email']],
        ],
    ]
];

function start_session($type, $lang) {
    global $sprequests, $sigrequests, $protocol;

    if (array_key_exists($type, $sprequests))
        $sessionrequest = $sprequests[$type];
    elseif (array_key_exists($type, $sigrequests))
        $sessionrequest = get_signature_request($type, $lang);
    else
        stop();

    $jsonsr = json_encode($sessionrequest);
    $api_call = array(
        $protocol => array(
            'method' => 'POST',
            'header' => "Content-type: application/json\r\n"
                . "Content-Length: " . strlen($jsonsr) . "\r\n"
                . "Authorization: " . API_TOKEN . "\r\n",
            'content' => $jsonsr
        )
    );

    $resp = file_get_contents(IRMA_SERVER_URL . '/session', false, stream_context_create($api_call));
    if (! $resp) {
        error();
    }
    return $resp;
}

function get_signature_request($type, $lang) {
    global $sigrequests;
    $request = $sigrequests[$type];

    // Signature requests do not support translatable strings, use chosen language
    $request['message'] = $sigrequests[$type]['message'][$lang];

    return $request;
}

function error() {
    http_response_code(500);
    echo 'Internal server error';
    exit();
}

function stop() {
    http_response_code(400);
    echo 'Invalid request';
    exit();
}

if (!isset($_GET['type']) || !isset($_GET['lang']))
    stop();

header('Access-Control-Allow-Origin: *');
echo start_session($_GET['type'], $_GET['lang']);
