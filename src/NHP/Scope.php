<?php
namespace NHP;
use NHP\AST;

final class Scope {
    private static $globalScope;
    private static $localScope;

    private function __construct() { }

    public static function globalScope(): Scope {
        return self::$globalScope ?? self::$globalScope = new self();
    }

    public static function localScope(): Scope {
        return self::$localScope ?? self::$localScope = new self();
    }
}
