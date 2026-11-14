<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Service;
use Ksfraser\FrontAccounting\HRM\TimeTracking\DTO\EventCodeDTO;
use Ksfraser\FrontAccounting\HRM\TimeTracking\DTO\TimeEntryDTO;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\EventCodeRepository;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\TimeEntryRepository;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
final class TimeTrackingService {
    private $eventRepo;
    private $timeRepo;
    public function __construct(?EventCodeRepository $eventRepo = null, ?TimeEntryRepository $timeRepo = null) {
        $this->eventRepo = $eventRepo ?? new EventCodeRepository();
        $this->timeRepo = $timeRepo ?? new TimeEntryRepository();
    }
    public function resolveTimeEntryById(int $id, ?RelatedRecordDto $dto = null): array {
        $row = $this->timeRepo->findById($id);
        if ($row === null) {
            return array('dto' => new RelatedRecordDto('time_entry', '0', 'time_entry', '0', null), 'fields' => $this->timeEntryFieldMap(), 'labels' => $this->timeEntryLabelMap(), 'url' => '#', 'label' => _('Time Entry #') . (string) $id);
        }
        $t = TimeEntryDTO::fromArray($row);
        $dto = $dto ?? new RelatedRecordDto('time_entry', (string) $t->id, 'time_entry', (string) $t->id, null);
        return array('dto' => $dto, 'fields' => array('employeeId' => $t->employeeName, 'eventCode' => $t->eventCode, 'hours' => $t->hours, 'workDate' => $t->workDate, 'payImpact' => $t->payImpact), 'labels' => $this->timeEntryLabelMap(), 'url' => '/modules/ksf_FA_HRM_TimeTracking/pages/time_entry_form.php?action=edit&time_entry_id=' . $t->id, 'label' => $t->getDisplayLabel());
    }
    public function resolveEventCodeById(int $id, ?RelatedRecordDto $dto = null): array {
        $row = $this->eventRepo->findById($id);
        if ($row === null) {
            return array('dto' => new RelatedRecordDto('event_code', '0', 'event_code', '0', null), 'fields' => $this->eventCodeFieldMap(), 'labels' => $this->eventCodeLabelMap(), 'url' => '#', 'label' => _('Event #') . (string) $id);
        }
        $e = EventCodeDTO::fromArray($row);
        $dto = $dto ?? new RelatedRecordDto('event_code', (string) $e->id, 'event_code', (string) $e->id, null);
        return array('dto' => $dto, 'fields' => array('eventCode' => $e->eventCode, 'eventName' => $e->eventName, 'impactMultiplier' => $e->impactMultiplier, 'payType' => $e->payType), 'labels' => $this->eventCodeLabelMap(), 'url' => '/modules/ksf_FA_HRM_TimeTracking/pages/event_code_form.php?action=edit&event_code_id=' . $e->id, 'label' => $e->getDisplayLabel());
    }
    public function listAllTimeEntries(): array {
        $rows = $this->timeRepo->findAll();
        $results = array();
        foreach ($rows as $row) {
            $t = TimeEntryDTO::fromArray($row);
            $results[] = array('id' => $t->id, 'dto' => new RelatedRecordDto('time_entry', (string) $t->id, 'time_entry', (string) $t->id, null), 'fields' => array('employeeId' => $t->employeeName, 'eventCode' => $t->eventCode, 'hours' => $t->hours, 'workDate' => $t->workDate, 'payImpact' => $t->payImpact), 'labels' => $this->timeEntryLabelMap(), 'url' => '/modules/ksf_FA_HRM_TimeTracking/pages/time_entry_form.php?action=edit&time_entry_id=' . $t->id, 'label' => $t->getDisplayLabel());
        }
        return $results;
    }
    public function listAllEventCodes(): array {
        $rows = $this->eventRepo->findAll();
        $results = array();
        foreach ($rows as $row) {
            $e = EventCodeDTO::fromArray($row);
            $results[] = array('id' => $e->id, 'dto' => new RelatedRecordDto('event_code', (string) $e->id, 'event_code', (string) $e->id, null), 'fields' => array('eventCode' => $e->eventCode, 'eventName' => $e->eventName, 'impactMultiplier' => $e->impactMultiplier, 'payType' => $e->payType), 'labels' => $this->eventCodeLabelMap(), 'url' => '/modules/ksf_FA_HRM_TimeTracking/pages/event_code_form.php?action=edit&event_code_id=' . $e->id, 'label' => $e->getDisplayLabel());
        }
        return $results;
    }
    public function calculatePayImpact(string $eventCode, float $hours, float $baseRate): array {
        $event = $this->eventRepo->findByCode($eventCode);
        $multiplier = $event !== null ? (float) ($event['impact_multiplier'] ?? 1.00) : 1.00;
        $payType = $event !== null ? (string) ($event['pay_type'] ?? 'hourly') : 'hourly';
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
        return array('eventCode' => _('Event Code'), 'eventName' => _('Event Name'), 'impactMultiplier' => _('Impact Multiplier'), 'payType' => _('Pay Type'));
    }
}
