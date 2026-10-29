<?php

abstract class downloader 
{

    public static function download($url) 
    {

        $destination_path = __DIR__ . '/../videos';

        $deactivated = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        if (!function_exists('exec') || in_array('exec', $deactivated, true)) { throw new Exception('exec() function does not exist on this server, install it'); }

        $url = trim($url);

        if (!filter_var($url, FILTER_VALIDATE_URL)) { throw new Exception('Invalid Link'); }

        $schema = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (!in_array($schema, ['http', 'https'], true)) { throw new Exception('Only http and https allowed'); }
    
        $where_command = PHP_OS_FAMILY === 'Windows' ? 'where yt-dlp 2>NUL' : 'command -v yt-dlp 2>/dev/null';
        
        exec($where_command, $output_where_command, $code_where_command);

        if ($code_where_command !== 0 || empty($output_where_command)) { throw new Exception('yt-dlp not found on the server'); }
    
        if (!is_dir($destination_path) && !mkdir($destination_path, 0775, true) && !is_dir($destination_path)) { throw new Exception('Failed to create the videos destination folder'); }

        if (!is_writable($destination_path)) { throw new Exception('Destination Folder is not writable'); }
    
        $template = rtrim($destination_path, '/\\') . DIRECTORY_SEPARATOR . '%(id)s.%(ext)s';
    
        $command = implode(' ', array(
            'yt-dlp',
            '--playlist-items=1',
            '--no-progress',
            '--restrict-filenames',
            '--print after_move:filepath',
            '-o ' . escapeshellarg($template),
            '--',

            escapeshellarg($url),

            '2>&1'
        ));
    
        set_time_limit(0);

        exec($command, $command_output, $code_command);
    
        $text_output = trim(implode("\n", $command_output));
    
        $file = trim((string) end($command_output));

        if ($file === '' || !is_file($file)) { throw new Exception("Download already done, but file not found - {$text_output}"); }
    
        return array(true, $file, null);

    }

    public static function unlink_file($file) 
    {

        $un = unlink($file);

        if (!$un) { error_log("Failed to unlink File: {$file}"); }

    }

}