<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after core.php
 *
 * This file should load/create any application wide configuration settings, such as
 * Caching, Logging, loading additional configuration files.
 *
 * You should also use this file to include any files that provide global functions/constants
 * that your application uses.
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.10.8.2117
 */

// Setup a 'default' cache configuration for use in the application.
Cache::config("default", ["engine" => "File"]);

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 *
 * App::build(array(
 *     'Model'                     => array('/path/to/models/', '/next/path/to/models/'),
 *     'Model/Behavior'            => array('/path/to/behaviors/', '/next/path/to/behaviors/'),
 *     'Model/Datasource'          => array('/path/to/datasources/', '/next/path/to/datasources/'),
 *     'Model/Datasource/Database' => array('/path/to/databases/', '/next/path/to/database/'),
 *     'Model/Datasource/Session'  => array('/path/to/sessions/', '/next/path/to/sessions/'),
 *     'Controller'                => array('/path/to/controllers/', '/next/path/to/controllers/'),
 *     'Controller/Component'      => array('/path/to/components/', '/next/path/to/components/'),
 *     'Controller/Component/Auth' => array('/path/to/auths/', '/next/path/to/auths/'),
 *     'Controller/Component/Acl'  => array('/path/to/acls/', '/next/path/to/acls/'),
 *     'View'                      => array('/path/to/views/', '/next/path/to/views/'),
 *     'View/Helper'               => array('/path/to/helpers/', '/next/path/to/helpers/'),
 *     'Console'                   => array('/path/to/consoles/', '/next/path/to/consoles/'),
 *     'Console/Command'           => array('/path/to/commands/', '/next/path/to/commands/'),
 *     'Console/Command/Task'      => array('/path/to/tasks/', '/next/path/to/tasks/'),
 *     'Lib'                       => array('/path/to/libs/', '/next/path/to/libs/'),
 *     'Locale'                    => array('/path/to/locales/', '/next/path/to/locales/'),
 *     'Vendor'                    => array('/path/to/vendors/', '/next/path/to/vendors/'),
 *     'Plugin'                    => array('/path/to/plugins/', '/next/path/to/plugins/'),
 * ));
 */

// カスタマイズ用ディレクトリの登録（このディレクトリに格納されたソースを優先して読み込む）
App::build([
    "Model" => [APP . "Custom" . DS . "Model" . DS],
    "Model/Behavior" => [APP . "Custom" . DS . "Model" . DS . "Behavior" . DS],
    "Model/Datasource" => [
        APP . "Custom" . DS . "Model" . DS . "Datasource" . DS,
    ],
    "Model/Datasource/Database" => [
        APP .
        "Custom" .
        DS .
        "Model" .
        DS .
        "Datasource" .
        DS .
        "Database" .
        DS,
    ],
    "Model/Datasource/Session" => [
        APP . "Custom" . DS . "Model" . DS . "Datasource" . DS . "Session" . DS,
    ],
    "Controller" => [APP . "Custom" . DS . "Controller" . DS],
    "Controller/Component" => [
        APP . "Custom" . DS . "Controller" . DS . "Component" . DS,
    ],
    "Controller/Component/Auth" => [
        APP .
        "Custom" .
        DS .
        "Controller" .
        DS .
        "Component" .
        DS .
        "Auth" .
        DS,
    ],
    "Controller/Component/Acl" => [
        APP . "Custom" . DS . "Controller" . DS . "Component" . DS . "Acl" . DS,
    ],
    "View" => [APP . "Custom" . DS . "View" . DS],
    "View/Helper" => [APP . "Custom" . DS . "View" . DS . "Helper" . DS],
    "Console" => [APP . "Custom" . DS . "Console" . DS],
    "Console/Command" => [
        APP . "Custom" . DS . "Console" . DS . "Command" . DS,
    ],
    "Lib" => [APP . "Custom" . DS . "Lib" . DS],
    "Locale" => [APP . "Custom" . DS . "Locale" . DS],
    "Vendor" => [APP . "Custom" . DS . "Vendor" . DS],
    "Plugin" => [APP . "Custom" . DS . "Plugin" . DS],
]);

/**
 * Custom Inflector rules can be set to correctly pluralize or singularize table, model, controller names or whatever other
 * string is passed to the inflection functions
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 */

/**
 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call
 * Uncomment one of the lines below, as you need. Make sure you read the documentation on CakePlugin to use more
 * advanced ways of loading plugins
 *
 * CakePlugin::loadAll(); // Loads all plugins at once
 * CakePlugin::load('DebugKit'); // Loads a single plugin named DebugKit
 */
CakePlugin::load("DebugKit"); // Loads a single plugin named DebugKit
CakePlugin::load("Search");
CakePlugin::load("BoostCake");

/**
 * You can attach event listeners to the request lifecycle as Dispatcher Filter . By default CakePHP bundles two filters:
 *
 * - AssetDispatcher filter will serve your asset files (css, images, js, etc) from your themes and plugins
 * - CacheDispatcher filter will read the Cache.check configure variable and try to serve cached content generated from controllers
 *
 * Feel free to remove or add filters as you see fit for your application. A few examples:
 *
 * Configure::write('Dispatcher.filters', array(
 *		'MyCacheFilter', //  will use MyCacheFilter class from the Routing/Filter package in your app.
 *		'MyPlugin.MyFilter', // will use MyFilter class from the Routing/Filter package in MyPlugin plugin.
 * 		array('callable' => $aFunction, 'on' => 'before', 'priority' => 9), // A valid PHP callback type to be called on beforeDispatch
 *		array('callable' => $anotherMethod, 'on' => 'after'), // A valid PHP callback type to be called on afterDispatch
 *
 * ));
 */
Configure::write("Dispatcher.filters", ["AssetDispatcher", "CacheDispatcher"]);

/**
 * Configures default file logging options
 */
App::uses("CakeLog", "Log");
CakeLog::config("debug", [
    "engine" => "File",
    "types" => ["notice", "info", "debug"],
    "file" => "debug",
]);
CakeLog::config("error", [
    "engine" => "File",
    "types" => ["warning", "error", "critical", "alert", "emergency"],
    "file" => "error",
]);

// iroha Board 設定ファイルをロード
Configure::load("ib_config");

// カスタマイズ用設定ファイルをロード
Configure::config(
    "default",
    new PhpReader(APP . "Custom" . DS . "Config" . DS)
);
Configure::load("config");
