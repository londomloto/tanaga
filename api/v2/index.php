<?php
header('Content-Type: application/json');

print(json_encode(array(
    'success' => FALSE,
    'message' => "Invalid target"
)));