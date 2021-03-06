<?php
// Include some functions for handling possible errors.
include_once 'helper.php';

try {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        throw new Exception(json_encode([403, 'Forbidden']));
    }

    // Here is your custom code.
    // $client_data processing below is just for example.
    if (!isset($_POST['client_data'])) {
        throw new Exception(json_encode([400, 'Bad request']));
    }
    
    $clientData = (int) $_POST['client_data'];

    if ($clientData == 0) {
        $errData = [
            'client_data' => [
                0 => 'Error description.',
                1 => 'Another error description.',
            ]
        ];
        throw new Exception(errMsg(500, $errData));
    } else {
        $requestedData = [
            'Requested Data',
            'More Requested Data'
        ];
        die(json_encode([200, json_encode($requestedData)]));
    }
} catch (Exception $e) {
    die($e->getMessage());
}
