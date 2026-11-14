<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\DTO;
final class TimeEntryDTO {
    public $id;
    public $employeeId;
    public $employeeName;
    public $eventCode;
    public $eventName;
    public $hours;
    public $workDate;
    public $payImpact;
    public $calculatedAmount;
    public function __construct(int $id, string $employeeId = '', string $employeeName = '', string $eventCode = '', string $eventName = '', string $hours = '0', string $workDate = '', string $payImpact = '', string $calculatedAmount = '0') {
        $this->id = $id; $this->employeeId = $employeeId; $this->employeeName = $employeeName; $this->eventCode = $eventCode;
        $this->eventName = $eventName; $this->hours = $hours; $this->workDate = $workDate; $this->payImpact = $payImpact;
        $this->calculatedAmount = $calculatedAmount;
    }
    public function getDisplayLabel(): string {
        $parts = array();
        if ($this->employeeName !== '') { $parts[] = $this->employeeName; }
        if ($this->eventCode !== '') { $parts[] = $this->eventCode; }
        if ($this->hours !== '' && $this->hours !== '0') { $parts[] = $this->hours . 'h'; }
        return $parts === [] ? _('Time Entry #') . (string) $this->id : implode(' — ', $parts);
    }
    public function toArray(): array {
        return array('id' => $this->id, 'employee_id' => $this->employeeId, 'employee_name' => $this->employeeName, 'event_code' => $this->eventCode, 'event_name' => $this->eventName, 'hours' => $this->hours, 'work_date' => $this->workDate, 'pay_impact' => $this->payImpact, 'calculated_amount' => $this->calculatedAmount);
    }
    public static function fromArray(array $data): self {
        return new self((int) ($data['id'] ?? 0), (string) ($data['employee_id'] ?? ''), (string) ($data['employee_name'] ?? ''), (string) ($data['event_code'] ?? ''), (string) ($data['event_name'] ?? ''), (string) ($data['hours'] ?? '0'), (string) ($data['work_date'] ?? ''), (string) ($data['pay_impact'] ?? ''), (string) ($data['calculated_amount'] ?? '0'));
    }
}
