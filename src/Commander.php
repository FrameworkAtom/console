<?php

namespace Console;


class Commander
{

    protected static $_instance = null;

    protected static $command;

    protected static $args;

    public static function load($args)
    {
        static::$command = $args[1];
        unset($args[0], $args[1]);
        static::$args = $args;
    }

    public static function serve()
    {
        echo "=> Booting Atom";
        echo "=> Atom 1.0.0 application starting in development";
        echo "Atom starting in single mode...";
        echo "* Environment: development";
        echo "* Listening on http://0.0.0.0:2019";
        echo "Use Ctrl-C to stop";

        exec("php -S 0.0.0.0:2019 -t public");
    }

}