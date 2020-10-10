<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'App\\App' => $baseDir . '/app/App.php',
    'App\\Controllers\\BackupController' => $baseDir . '/app/Controllers/BackupController.php',
    'App\\Controllers\\DatabaseController' => $baseDir . '/app/Controllers/DatabaseController.php',
    'App\\Controllers\\HomeController' => $baseDir . '/app/Controllers/HomeController.php',
    'App\\Controllers\\PackageController' => $baseDir . '/app/Controllers/PackageController.php',
    'App\\Controllers\\PgmetricsController' => $baseDir . '/app/Controllers/PgmetricsController.php',
    'App\\Controllers\\ReplicationController' => $baseDir . '/app/Controllers/ReplicationController.php',
    'App\\Controllers\\ServiceController' => $baseDir . '/app/Controllers/ServiceController.php',
    'App\\Controllers\\TableController' => $baseDir . '/app/Controllers/TableController.php',
    'App\\Controllers\\UserController' => $baseDir . '/app/Controllers/UserController.php',
    'App\\Helpers\\DB' => $baseDir . '/app/Helpers/DB.php',
    'App\\Helpers\\Formatter' => $baseDir . '/app/Helpers/Formatter.php',
    'App\\Helpers\\Route' => $baseDir . '/app/Helpers/Route.php',
    'App\\Helpers\\Validator' => $baseDir . '/app/Helpers/Validator.php',
    'App\\Utils\\Command\\Command' => $baseDir . '/app/Utils/Command/Command.php',
    'App\\Utils\\Command\\CommandEngine' => $baseDir . '/app/Utils/Command/CommandEngine.php',
    'App\\Utils\\Command\\ICommandEngine' => $baseDir . '/app/Utils/Command/ICommandEngine.php',
    'App\\Utils\\Command\\SSHEngine' => $baseDir . '/app/Utils/Command/SSHEngine.php',
    'App\\Utils\\Distro\\Distro' => $baseDir . '/app/Utils/Distro/Distro.php',
    'App\\Utils\\Distro\\DistroInfo' => $baseDir . '/app/Utils/Distro/DistroInfo.php',
    'ArithmeticError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/ArithmeticError.php',
    'AssertionError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/AssertionError.php',
    'DivisionByZeroError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/DivisionByZeroError.php',
    'Error' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/Error.php',
    'JsonException' => $vendorDir . '/symfony/polyfill-php73/Resources/stubs/JsonException.php',
    'Normalizer' => $vendorDir . '/symfony/polyfill-intl-normalizer/Resources/stubs/Normalizer.php',
    'ParseError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/ParseError.php',
    'SessionUpdateTimestampHandlerInterface' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/SessionUpdateTimestampHandlerInterface.php',
    'Stringable' => $vendorDir . '/symfony/polyfill-php80/Resources/stubs/Stringable.php',
    'TypeError' => $vendorDir . '/symfony/polyfill-php70/Resources/stubs/TypeError.php',
    'UnhandledMatchError' => $vendorDir . '/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
    'ValueError' => $vendorDir . '/symfony/polyfill-php80/Resources/stubs/ValueError.php',
);