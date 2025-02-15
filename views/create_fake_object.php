<?php
require_once("../server/connect.php");
require_once("../session.php");
require_once("hasAccessUser.php");
include_once("library.php");

function createFakeObject($file_id, $user_id, $filename) {
    global $conn;
    
    $originalFilePath = "../assets/files/" . $filename;
    
    if (!file_exists($originalFilePath)) {
        return false;
    }
    
    $pathInfo = pathinfo($originalFilePath);
    $fakeFileName = $pathInfo['filename'] . '_fake.' . $pathInfo['extension'];
    $fakeFilePath = "../assets/files/" . $fakeFileName;
    
    $originalContent = file_get_contents($originalFilePath);
    $fakeContent = generateFakeContent($originalContent);
    
    return true;
}

function generateFakeContent($originalContent) {
    $lines = explode("\n", $originalContent);
    $fakeContent = "";
    
    foreach ($lines as $line) {
        if (mt_rand(1, 100) <= 20) {
            $line .= " (modified)";
        }
        $fakeContent .= $line . "\n";
    }
    
    return $fakeContent;
}
?>