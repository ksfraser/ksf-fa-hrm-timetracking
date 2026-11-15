<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Service;
use Ksfraser\FrontAccounting\HRM\TimeTracking\DTO\TimeEntryDTO;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\TimeEntryRepository;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
final class TimeTrackingService {
    private $timeRepo;
    public function __construct(?TimeEntryRepository $timeRepo = null) {
        $this->timeRepo = $timeRepo ?? new TimeEntryRepository();
    }
    public function resolveTimeEntryById(int $id, ?RelatedRecordDto $dto = null): array {
        $row = $this->timeRepo->findById($id);
        if ($row === null) {
            return array('dto' => new RelatedRecordDto('time_entry', '0', 'time_entry', '0', null), 'fields' => $this->timeEntryFieldMap(), 'labels' => $this->timeEntryLabelMap(), 'url' => '#', 'label' => _('Time Entry #') . (string) $id);
        }
        $t = TimeEntryDTO::fromArray($row);
        // Resolve activity code through CodeMaster
        $activityResolved = null;
        if ($t->activity_code !== '') {
            $activityPayload = array(
                'code_type' => 'hrm.activity_code',
                'code_value' => $t->activity_code,
                'dto' => new RelatedRecordDto('code_master', '0', 'code_master', '0', null),
            );
            if (function_exists('hook_invoke_all')) {
                hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_RESOLVE', $activityPayload);
            }
            $activityResolved = $activityPayload['result'] ?? null;
        }
        $dto = $dto ?? new RelatedRecordDto('time_entry', (string) $t->id, 'time_entry', (string) $t->id, null);
        return array(
            'dto'    => $dto,
            'fields' => array(
                'employeeId' => $t->employeeName,
                'project_id' => $t->project_name,
                'activity_code' => $t->activity_code,
                'activity_name' => $activityResolved !== null && is_array($activityResolved) && isset($activityResolved['fields']['name']) ? $activityResolved['fields']['name'] : '',
                'hours' => $t->hours,
                'workDate' => $t->workDate,
                'payImpact' => $t->payImpact,
            ),
            'labels' => $this->timeEntryLabelMap(),
            'url'    => '/modules/ksf_FA_HRM_TimeTracking/pages/time_entry_form.php?action=edit&time_entry_id=' . $t->id,
            'label'  => $t->getDisplayLabel(),
            'activity_resolved' => $activityResolved,
        );
    }
    public function listAllTimeEntries(): array {
        $rows = $this->timeRepo->findAll();
        $results = array();
        foreach ($rows as $row) {
            $t = TimeEntryDTO::fromArray($row);
            $activityResolved = null;
            if ($t->activity_code !== '') {
                $activityPayload = array(
                    'code_type' => 'hrm.activity_code',
                    'code_value' => $t->activity_code,
                    'dto' => new RelatedRecordDto('code_master', '0', 'code_master', '0', null),
                );
                if (function_exists('hook_invoke_all')) {
                    hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_RESOLVE', $activityPayload);
                }
                $activityResolved = $activityPayload['result'] ?? null;
            }
            $results[] = array(
                'id'    => $t->id,
                'dto'   => new RelatedRecordDto('time_entry', (string) $t->id, 'time_entry', (string) $t->id, null),
                'fields' => array(
                    'employeeId' => $t->employeeName,
                    'project_id' => $t->project_name,
                    'activity_code' => $t->activity_code,
                    'activity_name' => $activityResolved !== null && is_array($activityResolved) && isset($activityResolved['fields']['name']) ? $activityResolved['fields']['name'] : '',
                    'hours' => $t->hours,
                    'workDate' => $t->workDate,
                    'payImpact' => $t->payImpact,
                ),
                'labels' => $this->timeEntryLabelMap(),
                'url'    => '/modules/ksf_FA_HRM_TimeTracking/pages/time_entry_form.php?action=edit&time_entry_id=' . $t->id,
                'label'  => $t->getDisplayLabel(),
            );
        }
        return $results;
    }
    public function calculatePayImpact(string $activityCode, float $hours, float $baseRate): array {
        $multiplier = 1.00;
        $payType = 'hourly';
        $activityPayload = array(
            'code_type' => 'hrm.activity_code',
            'code_value' => $activityCode,
            'dto' => new RelatedRecordDto('code_master', '0', 'code_master', '0', null),
        );
        if (function_exists('hook_invoke_all')) {
            hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_RESOLVE', $activityPayload);
        }
        $resolved = $activityPayload['result'] ?? null;
        if ($resolved !== null && is_array($resolved) && isset($resolved['fields'])) {
            // impact_multiplier stored in usage_instructions or activity_code value
            $codeValue = (string) $activityCode;
            $multipliers = array('REG' => 1.00, 'OT' => 1.50, 'OT2' => 2.00, 'HOL' => 2.00);
            $multiplier = $multipliers[$codeValue] ?? 1.00;
        }
        $impactedHours = $hours * $multiplier;
        $amount = $impactedHours * $baseRate;
        return array(
            'activityCode' => $activityCode,
            'multiplier' => $multiplier,
            'payType' => $payType,
            'hours' => $hours,
            'impactedHours' => $impactedHours,
            'baseRate' => $baseRate,
            'amount' => $amount,
        );
    }
    public function timeEntryFieldMap(): array { return $this->timeEntryLabelMap(); }
    public function timeEntryLabelMap(): array {
        return array(
            'employeeId' => _('Employee'),
            'project_id' => _('Project'),
            'activity_code' => _('Activity Code'),
            'hours' => _('Hours'),
            'workDate' => _('Work Date'),
            'payImpact' => _('Pay Impact'),
        );
    }
    public function eventCodeFieldMap(): array { return $this->eventCodeLabelMap(); }
    public function eventCodeLabelMap(): array {
        return array('code' => _('Code'), 'name' => _('Name'), 'description' => _('Description'), 'usage_instructions' => _('Usage Instructions'));
    }
}
