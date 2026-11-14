<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Repository;
final class EventCodeRepository {
    private string $table = 'hrm_event_codes';
    public function findAll(): array {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return array(); }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " WHERE is_active = 1 ORDER BY event_code ASC";
        $result = db_query($sql, 'Could not load event codes');
        if ($result === false) { return array(); }
        $rows = array();
        while (($row = db_fetch_assoc($result)) !== false && $row !== null) { $rows[] = $row; }
        return $rows;
    }
    public function findById(int $id): ?array {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return null; }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " WHERE id = " . (int) $id;
        $result = db_query($sql, 'Could not load event code');
        if ($result === false) { return null; }
        $row = db_fetch_assoc($result);
        return $row === false ? null : $row;
    }
    public function findByCode(string $code): ?array {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return null; }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " WHERE event_code = " . $this->escape($code);
        $result = db_query($sql, 'Could not load event code by code');
        if ($result === false) { return null; }
        $row = db_fetch_assoc($result);
        return $row === false ? null : $row;
    }
    public function save(array $data): int {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return 0; }
        $sql = "INSERT INTO " . TB_PREF . $this->table . " (
                event_code, event_name, impact_multiplier, pay_type, description, is_active)
            VALUES ("
            . $this->escape($data['event_code'] ?? '') . ", "
            . $this->escape($data['event_name'] ?? '') . ", "
            . $this->escape($data['impact_multiplier'] ?? '1.00') . ", "
            . $this->escape($data['pay_type'] ?? 'hourly') . ", "
            . $this->escape($data['description'] ?? '') . ", "
            . (isset($data['is_active']) ? (int)$data['is_active'] : 1) . ")";
        db_query($sql, 'Could not save event code');
        return db_insert_id();
    }
    public function update(int $id, array $data): void {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return; }
        $sets = [];
        foreach (['event_code', 'event_name', 'impact_multiplier', 'pay_type', 'description', 'is_active'] as $col) {
            if (array_key_exists($col, $data)) {
                $val = ($col === 'is_active') ? (int)$data[$col] : $this->escape($data[$col]);
                $sets[] = "$col = $val";
            }
        }
        if (empty($sets)) { return; }
        $sql = "UPDATE " . TB_PREF . $this->table . " SET " . implode(', ', $sets) . " WHERE id = " . (int) $id;
        db_query($sql, 'Could not update event code');
    }
    private function escape($value): string {
        return function_exists('db_escape') ? db_escape($value) : addslashes((string) $value);
    }
}
