<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('TB_PREF')) {
    define('TB_PREF', '0_');
}

/**
 * Test doubles for FrontAccounting runtime functions.
 * Defined once per suite (guarded) so all Unit test files in a module share
 * the same stub, avoiding redeclaration across files.
 *
 * DbStub provides a per-query result-set queue: each db_query() call consumes
 * the next entry from queryResultSets, and db_fetch_assoc() walks that set.
 * Use setQueryResultSets([[row...], [row...], ...]) when multiple repository
 * queries occur in one code path (e.g. PayrollCalc::calculatePayroll).
 */
if (!function_exists('db_query')) {
    function db_query($sql, $error_msg = '') {
        \DbStub::$lastSql = $sql;
        \DbStub::$queryCalls++;
        \DbStub::$currentSetIndex++;
        \DbStub::$fetchIndex = 0;
        return \DbStub::$queryResult;
    }
}
if (!function_exists('db_fetch_assoc')) {
    function db_fetch_assoc($result) {
        if (\DbStub::$currentSetIndex < 0 || \DbStub::$currentSetIndex >= count(\DbStub::$queryResultSets)) {
            return false;
        }
        $rows = \DbStub::$queryResultSets[\DbStub::$currentSetIndex];
        if (\DbStub::$fetchIndex < count($rows)) {
            return $rows[\DbStub::$fetchIndex++];
        }
        return false;
    }
}
if (!function_exists('db_insert_id')) {
    function db_insert_id() {
        return \DbStub::$insertId++;
    }
}
if (!function_exists('db_escape')) {
    function db_escape($value) {
        \DbStub::$escaped[] = $value;
        return addslashes((string) $value);
    }
}
if (!function_exists('_')) {
    function _($msg) {
        return $msg;
    }
}
if (!function_exists('hook_invoke_all')) {
    function hook_invoke_all($ext, $method, &$data, $opts = null) {
        \DbStub::$hookCalls[] = [$ext, $method, $data, $opts];
        if (\DbStub::$hookResult !== null && is_array($data)) {
            $data['result'] = \DbStub::$hookResult;
        }
    }
}
if (!class_exists('hooks')) {
    class hooks {
        public function __construct() {}
    }
}
if (!class_exists('DbStub')) {
    class DbStub {
        public static $lastSql = null;
        public static $queryCalls = 0;
        public static $queryResult = true;
        public static $queryResultSets = [];
        public static $currentSetIndex = -1;
        public static $fetchIndex = 0;
        public static $insertId = 100;
        public static $escaped = [];
        public static $hookCalls = [];
        public static $hookResult = null;
        public static function reset(): void {
            self::$lastSql = null;
            self::$queryCalls = 0;
            self::$queryResult = true;
            self::$queryResultSets = [];
            self::$currentSetIndex = -1;
            self::$fetchIndex = 0;
            self::$insertId = 100;
            self::$escaped = [];
            self::$hookCalls = [];
            self::$hookResult = null;
        }
        public static function setRows(array $rows): void {
            self::$queryResultSets = [$rows];
            self::$currentSetIndex = -1;
            self::$fetchIndex = 0;
        }
        public static function setQueryResultSets(array $sets): void {
            self::$queryResultSets = $sets;
            self::$currentSetIndex = -1;
            self::$fetchIndex = 0;
        }
    }
}
