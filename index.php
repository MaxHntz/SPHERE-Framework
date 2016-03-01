<?php
namespace SPHERE;

use MOC\V\Core\AutoLoader\AutoLoader;
use SPHERE\Common\Main;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\APCuHandler;
use SPHERE\System\Cache\Handler\CookieHandler;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Cache\Handler\OpCacheHandler;
use SPHERE\System\Cache\Handler\SmartyHandler;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Config\ConfigFactory;
use SPHERE\System\Config\Reader\IniReader;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Setup: Php
 */
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');
session_start();
session_write_close();
set_time_limit(240);
ob_implicit_flush();

/**
 * Setup: Loader
 */
require_once(__DIR__ . '/Library/MOC-V/Core/AutoLoader/AutoLoader.php');
AutoLoader::getNamespaceAutoLoader('MOC\V', __DIR__ . '/Library/MOC-V');
AutoLoader::getNamespaceAutoLoader('SPHERE', __DIR__ . '/', 'SPHERE');
AutoLoader::getNamespaceAutoLoader('Markdownify', __DIR__ . '/Library/Markdownify/2.1.6/src');
AutoLoader::getNamespaceAutoLoader('phpFastCache', __DIR__ . '/Library/PhpFastCache/4.3.6/src');

$Main = new Main();

if (false) {
    $CacheConfig = (new ConfigFactory())->createReader(__DIR__ . '/System/Cache/Configuration.ini', new IniReader());
    (new CacheFactory())->createHandler(new CookieHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new MemcachedHandler(), $CacheConfig, 'Memcached')->clearCache();
    (new CacheFactory())->createHandler(new APCuHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new MemoryHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new OpCacheHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new TwigHandler(), $CacheConfig)->clearCache();
    (new CacheFactory())->createHandler(new SmartyHandler(), $CacheConfig)->clearCache();
}

$DebuggerConfig = (new ConfigFactory())->createReader(__DIR__ . '/System/Debugger/Configuration.ini', new IniReader());
if ($DebuggerConfig->getConfig()->getContainer('Debugger')->getContainer('Enabled')->getValue()) {
    Debugger::$Enabled = true;
} else {
    Debugger::$Enabled = false;
}

$Main->runPlatform();
