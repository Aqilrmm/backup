<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (hash_equals($sessionToken, $userToken)) {
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        if (!empty($message)) {
            file_put_contents("input.txt", $message);
        }
    }
}

header("Location: input.html");
exit;
?>
