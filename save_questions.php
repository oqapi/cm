<?php
require_once 'config.php';

# Our javascript detected a valid response so we should save the answers, but first we need a proof vrom our IRMA server 
# because we cannot rely on the javascript alone

    $irma_result =  $_POST['irma_result'];
    error_log('Got response: '.$_POST['irma_result']);
    $response_object = json_decode($irma_result);
    $token = $response_object->{'token'};


    $resp = file_get_contents(IRMA_SERVER_URL . '/session/'.$token.'/result');

    #error_log('irma response: ' . $resp);
    $response_object = json_decode($resp);
    $token = $response_object->{'proofStatus'};
    if ($token == "VALID") {
        #We now have real proof that the user is trustworthy
        #TODO: save questions to database
        echo '{"saved": true}';
    } else {
        echo '{"saved": false, "message": "Someone is doing nasty things!"}';
    }


