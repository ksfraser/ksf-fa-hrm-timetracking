<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Repository;
final class TimeEntryRepository {
    private string $table = 'hrm_time_entries';
    public function findAll(): array {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return array(); }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " ORDER BY work_date DESC, id DESC";
        $result = db_query($sql, 'Could not load time entries');
        if ($result === false) { return array(); }
        $rows = array();
        while (($row = db_fetch_assoc($result)) !== false && $row !== null) { $rows[] = $row; }
        return $rows;
    }
    public function findById(int $id): ?array {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return null; }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " WHERE id = " . (int) $id;
        $result = db_query($sql, 'Could not load time entry');
        if ($result === false) { return null; }
        $row = db_fetch_assoc($result);
        return $row === false ? null : $row;
    }
    public function findByEmployee(string $employeeId, string $fromDate = '', string $toDate = ''): array {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return array(); }
        $sql = "SELECT * FROM " . TB_PREF . $this->table . " WHERE employee_id = " . $this->escape($employeeId);
        if ($fromDate !== '') { $sql .= " AND work_date >= '" . $this->escape($fromDate) . "'"; }
        if ($toDate !== '') { $sql .= " AND work_date <= '" . $this->escape($toDate) . "'"; }
        $sql .= " ORDER BY work_date DESC";
        $result = db_query($sql, 'Could not load time entries by employee');
        if ($result === false) { return array(); }
        $rows = array();
        while (($row = db_fetch_assoc($result)) !== false && $row !== null) { $rows[] = $row; }
        return $rows;
    }
    public function save(array $data): int {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return 0; }
        $sql = "INSERT INTO " . TB_PREF . $this->table . " (
                employee_id, project_id, activity_code, hours, work_date, pay_impact, calculated_amount)
            VALUES ("
            . $this->escape($data['employee_id'] ?? '') . ", "
            . $this->escape($data['project_id'] ?? '') . ", "
            . $this->escape($data['activity_code'] ?? '') . ", "
            . $this->escape($data['hours'] ?? '0') . ", "
            . $this->escape($data['work_date'] ?? '') . ", "
            . $this->escape($data['pay_impact'] ?? '') . ", "
            . $this->escape($data['calculated_amount'] ?? '0') . ")";
        db_query($sql, 'Could not save time entry');
        return db_insert_id();
    }
    public function update(int $id, array $data): void {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return; }
        $sets = [];
        foreach (['employee_id', 'project_id', 'activity_code', 'hours', 'work_date', 'pay_impact', 'calculated_amount'] as $col) {
            if (array_key_exists($col, $data)) { $sets[] = "$col = " . $this->escape($data[$col]); }
        }
        if (empty($sets)) { return; }
        $sql = "UPDATE " . TB_PREF . $this->table . " SET " . implode(', ', $sets) . " WHERE id = " . (int) $id;
        db_query($sql, 'Could not update time entry');
    }
    public function delete(int $id): void {
        if (!function_exists('db_query') || !defined('TB_PREF')) { return; }
        $sql = "DELETE FROM " . TB_PREF . $this->table . " WHERE id = " . (int) $id;
        db_query($sql, 'Could not delete time entry');
    }
    private function escape($value): string {
        return function_exists('db_escape') ? db_escape($value) : addslashes((string) $value);
    }
}
