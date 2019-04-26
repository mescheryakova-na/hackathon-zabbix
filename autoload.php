<?php

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'Project\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/lib/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

if(!function_exists('env')) {
    function env($key, $default = null)
    {
        static $config = false;

        if($config === false) {
            if (file_exists('./.env')) {
                $config = [];
                $envFileContent = file_get_contents('./.env');
                $lines = explode("\n", $envFileContent);
                foreach ($lines as $line) {
                    $line = trim($line);

                    if (in_array(substr($line, 0, 1) , [';', '#'])) {
                        continue;
                    }
                    if (preg_match("/([^\s]+)[\s]*=[\s]*([^\s]+)/si", $line, $match)) {
                        $config[$match[1]] = trim($match[2], '\'"');
                    }
                }
            }
        }
        if (isset($config) && isset($config[$key])) {
            return $config[$key];
        }
        return $default;
    }
}