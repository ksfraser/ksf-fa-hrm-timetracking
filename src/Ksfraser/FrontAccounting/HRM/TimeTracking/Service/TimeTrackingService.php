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
        $dto = $dto ?? new RelatedRecordDto('time_entry', (string) $t->id, 'time_entry', (string) $t->id, null);
        // Resolve event code through CodeMaster
        $eventResolved = null;
        if ($t->eventCode !== '') {
            $eventPayload = array(
                'code_type' => 'hrm.event_code',
                'code_value' => $t->eventCode,
                'dto' => new RelatedRecordDto('code_master', '0', 'code_master', '0', null),
            );
            if (function_exists('hook_invoke_all')) {
                hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_RESOLVE', $eventPayload);
            }
            $eventResolved = $eventPayload['result'] ?? null;
        }
        return array(
            'dto'    => $dto,
            'fields' => array(
                'employeeId' => $t->employeeName,
                'eventCode' => $t->eventCode,
                'eventName' => $eventResolved !== null && is_array($eventResolved) && isset($eventResolved['fields']['name']) ? $eventResolved['fields']['name'] : '',
                'hours' => $t->hours,
                'workDate' => $t->workDate,
                'payImpact' => $t->payImpact,
            ),
            'labels' => $this->timeEntryLabelMap(),
            'url'    => '/modules/ksf_FA_HRM_TimeTracking/pages/time_entry_form.php?action=edit&time_entry_id=' . $t->id,
            'label'  => $t->getDisplayLabel(),
            'event_resolved' => $eventResolved,
        );
    }
    public function resolveEventCodeById(int $id, ?RelatedRecordDto $dto = null): array {
        // Event codes now live in CodeMaster (code_type = 'hrm.event_code').
        // The $id is treated as a code_value lookup.
        $eventPayload = array(
            'code_type' => 'hrm.event_code',
            'code_value' => (string) $id,
            'dto' => $dto ?? new RelatedRecordDto('code_master', '0', 'code_master', '0', null),
        );
        if (function_exists('hook_invoke_all')) {
            hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_RESOLVE', $eventPayload);
        }
        $result = $eventPayload['result'] ?? null;
        if ($result === null || !is_array($result)) {
            return array('dto' => new RelatedRecordDto('event_code', '0', 'event_code', '0', null), 'fields' => $this->eventCodeFieldMap(), 'labels' => $this->eventCodeLabelMap(), 'url' => '#', 'label' => _('Event Code not found'));
        }
        return $result;
    }
    public function listAllTimeEntries(): array {
        $rows = $this->timeRepo->findAll();
        $results = array();
        foreach ($rows as $row) {
            $t = TimeEntryDTO::fromArray($row);
            $results[] = array(
                'id'    => $t->id,
                'dto'   => new RelatedRecordDto('time_entry', (string) $t->id, 'time_entry', (string) $t->id, null),
                'fields' => array(
                    'employeeId' => $t->employeeName,
                    'eventCode' => $t->eventCode,
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
    public function listAllEventCodes(): array {
        // Event codes are managed in CodeMaster; delegate
        $payload = array('code_type' => 'hrm.event_code');
        if (function_exists('hook_invoke_all')) {
            hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_LIST', $payload);
        }
        return $payload['result'] ?? array();
    }
    public function calculatePayImpact(string $eventCode, float $hours, float $baseRate): array {
        // Look up event code multiplier from CodeMaster
        $multiplier = 1.00;
        $payType = 'hourly';
        $eventPayload = array(
            'code_type' => 'hrm.event_code',
            'code_value' => $eventCode,
            'dto' => new RelatedRecordDto('code_master', '0', 'code_master', '0', null),
        );
        if (function_exists('hook_invoke_all')) {
            hook_invoke_all('ksf_FA_CodeMaster', 'CODEMASTER_RESOLVE', $eventPayload);
        }
        $resolved = $eventPayload['result'] ?? null;
        if ($resolved !== null && is_array($resolved) && isset($resolved['fields'])) {
            // impact_multiplier is stored in usage_instructions or we need a separate field
            // For now, use a default based on code value
            $codeValue = (string) $eventCode;
            $multipliers = array('REG' => 1.00, 'OT' => 1.50, 'OT2' => 2.00, 'HOL' => 2.00);
            $multiplier = $multipliers[$codeValue] ?? 1.00;
        }
        $impactedHours = $hours * $multiplier;
        $amount = $impactedHours * $baseRate;
        return array(
            'eventCode' => $eventCode,
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
        return array('employeeId' => _('Employee'), 'eventCode' => _('Event Code'), 'hours' => _('Hours'), 'workDate' => _('Work Date'), 'payImpact' => _('Pay Impact'));
    }
    public function eventCodeFieldMap(): array { return $this->eventCodeLabelMap(); }
    public function eventCodeLabelMap(): array {
        return array('code' => _('Code'), 'name' => _('Name'), 'description' => _('Description'), 'usage_instructions' => _('Usage Instructions'));
    }
}
