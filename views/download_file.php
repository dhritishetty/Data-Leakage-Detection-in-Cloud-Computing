<?php
session_start();
if(isset($_GET['name']) && isset($_GET['download']) && $_GET['download'] == 'true') {
    $filename = $_GET['name'];
    $filepath = "../assets/files/".$filename;
    
    if(file_exists($filepath)) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if($ext == "pdf") {
            $contenttype = "application/pdf";
        } else {
            $contenttype = "application/force-download";
        }
        
        header("Content-Type: " . $contenttype);
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        readfile($filepath);
        exit();
    }
}
?>