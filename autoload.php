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

    $relativePath = str_replace('\\', '/', $relative_class);
    $relativePathParts = explode('/', $relativePath);
    $folderParts = array_slice($relativePathParts, 0, count($relativePathParts) -1);

    foreach($folderParts as $k => $folderPart)
    {
        $folderParts[$k] = strtolower(substr($folderPart, 0, 1)) . substr($folderPart,1);
    }
    if (count($folderParts)) {
        $folderParts[] = '';
    }
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    //$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    $file = $base_dir . implode('/', $folderParts) . $relativePathParts[count($relativePathParts) - 1] . '.php';

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
            $envFileName = __DIR__ . '/.env';
            if (file_exists($envFileName)) {
                $config = [];
                $envFileContent = file_get_contents($envFileName);
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