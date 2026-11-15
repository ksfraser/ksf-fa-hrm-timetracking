<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\DTO;
final class TimeEntryDTO {
    public $id;
    public $employeeId;
    public $employeeName;
    public $project_id;
    public $project_name;
    public $activity_code;
    public $activity_name;
    public $hours;
    public $workDate;
    public $payImpact;
    public $calculatedAmount;
    public function __construct(int $id, string $employeeId = '', string $employeeName = '', string $projectId = '', string $projectName = '', string $activityCode = '', string $activityName = '', string $hours = '0', string $workDate = '', string $payImpact = '', string $calculatedAmount = '0') {
        $this->id = $id;
        $this->employeeId = $employeeId;
        $this->employeeName = $employeeName;
        $this->project_id = $projectId;
        $this->project_name = $projectName;
        $this->activity_code = $activityCode;
        $this->activity_name = $activityName;
        $this->hours = $hours;
        $this->workDate = $workDate;
        $this->payImpact = $payImpact;
        $this->calculatedAmount = $calculatedAmount;
    }
    public function getDisplayLabel(): string {
        $parts = array();
        if ($this->employeeName !== '') { $parts[] = $this->employeeName; }
        if ($this->project_name !== '') { $parts[] = $this->project_name; }
        if ($this->activity_code !== '') { $parts[] = $this->activity_code; }
        if ($this->hours !== '' && $this->hours !== '0') { $parts[] = $this->hours . 'h'; }
        return $parts === [] ? _('Time Entry #') . (string) $this->id : implode(' — ', $parts);
    }
    public function toArray(): array {
        return array(
            'id' => $this->id,
            'employee_id' => $this->employeeId,
            'employee_name' => $this->employeeName,
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'activity_code' => $this->activity_code,
            'activity_name' => $this->activity_name,
            'hours' => $this->hours,
            'work_date' => $this->workDate,
            'pay_impact' => $this->payImpact,
            'calculated_amount' => $this->calculatedAmount,
        );
    }
    public static function fromArray(array $data): self {
        return new self(
            (int) ($data['id'] ?? 0),
            (string) ($data['employee_id'] ?? ''),
            (string) ($data['employee_name'] ?? ''),
            (string) ($data['project_id'] ?? ''),
            (string) ($data['project_name'] ?? ''),
            (string) ($data['activity_code'] ?? ''),
            (string) ($data['activity_name'] ?? ''),
            (string) ($data['hours'] ?? '0'),
            (string) ($data['work_date'] ?? ''),
            (string) ($data['pay_impact'] ?? ''),
            (string) ($data['calculated_amount'] ?? '0')
        );
    }
}
