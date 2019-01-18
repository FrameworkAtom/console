<?php

namespace Console;

require __DIR__ . '/../../framework/src/Core/Helpers/Misc.php';


use Atom\Encryption\Encrypter;
use Atom\Environment\Config;

class Commander
{

    protected static $_instance = null;

    protected static $command;

    protected static $args;

    protected static $base_path;

    public static function load($args, $base)
    {
        static::$command = $args[1];
        unset($args[0], $args[1]);
        static::$args = $args;
        static::$base_path = $base;
        static::create_config();
    }

    public static function run()
    {
        if (static::$command == 's' || static::$command == 'serve')
            static::serve();

        if ((static::$command == 'g:ler' || static::$command == 'generate:controller') && (isset(static::$args[3]) && (static::$args[3] == '--resource')))
            static::createResource(static::$args[2]);

        if ((static::$command == 'g:ler' || static::$command == 'generate:controller') && (isset(static::$args[3]) && static::$args[3] == '--resource') && (isset(static::$args[4]) && startsWith('--model=', static::$args[4]))) {
            $model = explode('=', static::$args[4])[1];
            static::createResource(static::$args[2], $model);
        }

        if ((static::$command == 'g:ler' || static::$command == 'generate:controller') && !isset(static::$args[3]))
            static::create('Controller', static::$args[2]);

        if (static::$command == 'g:del' || static::$command == 'generate:model')
            static::create('Model', static::$args[2]);

        if (static::$command == 'g:ware' || static::$command == 'generate:middleware')
            static::create('Middleware', static::$args[2]);

        if (static::$command == 'secure')
            static::secure();
    }

    private static function serve()
    {
        echo "\n=> Booting Atom\n";
        echo "=> Atom 1.0.0 application starting in development\n";
        echo "Atom starting in single mode...\n";
        echo "* Environment: development\n";
        echo "* Listening on http://127.0.0.1:2019\n";
        echo "Use Ctrl-C to stop\n";

        exec("php -S 127.0.0.1:2019 -t public");
    }

    private static function create($type, $name)
    {
        if ($type == 'Controller') {
            $goto = static::$base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $name . '.php';
            $template = __DIR__ . '/Templates/Controller.php';
            $msg = "\n=> Controller created successfully.\n";
        } elseif ($type == 'Model') {
            $goto = static::$base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $name . '.php';
            $template = __DIR__ . '/Templates/Model.php';
            $msg = "\n=> Model created successfully.\n";
        } elseif ($type == 'Middleware') {
            $goto = static::$base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'middlewares' . DIRECTORY_SEPARATOR . $name . '.php';
            $template = __DIR__ . '/Templates/Middleware.php';
            $msg = "\n=> Middleware created successfully.\n";
        } elseif ($type == 'ResourceController') {
            $goto = static::$base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $name . '.php';
            $template = __DIR__ . '/Templates/ResourceController.php';
            $msg = "\n=> Controller created successfully.\n";
        }

        if (!file_exists($goto)) {
            $contents = file_get_contents($template);
            $contents = str_replace('_name_', $name, $contents);

            if (file_put_contents($goto, $contents) != false)
                echo $msg;
            else
                echo "\n=> Error during {$name} creation.\n";

        } else {
            echo "\n=> {$name} already exists.\n";
        }
    }
    
    public static function createResource($controller, $model = null)
    {
        if (is_null($model)) {
            $model = explode('Controller', $controller)[0];
        }

        $contents = "\nrouter()->resource('{$model}', '{$controller}');\n";

        static::create('ResourceController', $controller);
        static::create('Model', $model);

        if (file_put_contents(static::$base_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.php', $contents, FILE_APPEND) != false)
            echo "\n=> Route created successfully.\n";
        else
            echo "\n=> Error during route creation.\n";
    }

    public static function secure($force = true)
    {
        $env = new Config(static::$base_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.json');
        $config = $env->config();

        if (!isset($config->app_name)) {
            $config->app_name = "Atom Application";
        }

        if ($force) {
            $config->app_key = utf8_encode(Encrypter::generateKey());
        } else {
            if (isset($config->app_key) && $config->app_key != "") {
                $secure = true;
            } else {
                $config->app_key = utf8_encode(Encrypter::generateKey());
            }
        }

        if (!isset($config->locale))
            $config->locale = "en";

        if (!isset($config->database)) {
            $database = (object) null;
            $database->host = "localhost";
            $database->port = "3306";
            $database->db_name = "database_name";
            $database->user = "root";
            $database->password = "";
            $config->database = $database;
        }

        $secure = $env->create($config);

        if ($secure)
            echo "\nApplication key set successfully.\n";
        else
            echo "\nError during key generation.\n";
    }

    public static function create_config()
    {
        $env = static::$base_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.json';
        $example = static::$base_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.example.json';

        if (!file_exists($env)) {
            copy($example, $env);
        }
    }

}