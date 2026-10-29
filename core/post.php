<?php

$submited_form = (isset($_POST['submit_button'])) ? true : false;

if (!$submited_form) 
{

    echo json_encode(array('status' => false, 'message' => 'The form was not submitted')); return; exit;

}

// ---

session_start();

// ---

$link = $_POST['link'];

if (!$link) 
{

    $_SESSION['form_failure'] = 'Enter the Link'; header("Location: {$_SERVER['HTTP_REFERER']}"); return;

}

require_once __DIR__ . '/downloader.php';

echo "<script type='text/javascript'> document.querySelector('.main__center-progress').style.display = 'block'; </script>";

try 
{

    list($download_status, $file, $error) = downloader::download($link);

} catch (Exception $exception) 
{

    $e = $exception->getMessage();

    error_log($e);

    $_SESSION['form_failure'] = 'Failed to download Video'; header("Location: {$_SERVER['HTTP_REFERER']}"); return;

}

if (!$download_status) 
{

    $_SESSION['download_failure'] = $error; header("Location: {$_SERVER['HTTP_REFERER']}"); return;

}

$_SESSION['done'] = array('file' => $file);

header("Location: {$_SERVER['HTTP_REFERER']}");