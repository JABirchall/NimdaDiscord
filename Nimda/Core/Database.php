<?php
namespace Nimda\Core;

use Illuminate\Database\Capsule\Manager;
use Nimda\Configuration\Database as Config;
use Nimda\DB;

class Database
{
    public static $manager;

    public static function boot()
    {
        printf("Booting database driver - ");
        self::$manager = new Manager;
        self::$manager->addConnection(Config::$config['connections'][Config::$config['default']]);
        self::$manager->setAsGlobal();
        self::$manager->bootEloquent();

        $version = (Config::$config['default'] === 'sqlite') ? "sqlite_version()" : "version()";

        printf("%s version %s booted \n",Config::$config['default'], DB::select("select {$version} as version")[0]->version);
    }
}