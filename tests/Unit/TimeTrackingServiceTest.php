<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Tests\Unit;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\TimeEntryRepository;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Service\TimeTrackingService;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
use PHPUnit\Framework\TestCase;
class TimeTrackingServiceTest extends TestCase {
    protected function setUp(): void { \DbStub::reset(); }
    private function timeRow($id = 9, $name = 'Sam', $code = 'REG', $hours = '8') {
        return ['id' => $id, 'employee_id' => 'E1', 'employee_name' => $name, 'event_code' => $code,
            'hours' => $hours, 'work_date' => '2026-01-02', 'pay_impact' => '', 'calculated_amount' => '0'];
    }
    public function testResolveTimeEntryByIdFoundWithEventName(): void {
        \DbStub::setRows([$this->timeRow(9, 'Sam', 'REG', '8')]);
        \DbStub::$hookResult = ['fields' => ['name' => 'Regular Time']];
        $service = new TimeTrackingService();
        $result = $service->resolveTimeEntryById(9);
        $this->assertSame('Sam', $result['fields']['employeeId']);
        $this->assertSame('REG', $result['fields']['eventCode']);
        $this->assertSame('Regular Time', $result['fields']['eventName']);
        $this->assertSame('8', $result['fields']['hours']);
        $this->assertSame('2026-01-02', $result['fields']['workDate']);
        $this->assertSame('', $result['fields']['payImpact']);
        $this->assertSame('Sam — REG — 8h', $result['label']);
        $this->assertSame('/modules/ksf_FA_HRM_TimeTracking/pages/time_entry_form.php?action=edit&time_entry_id=9', $result['url']);
        $this->assertInstanceOf(RelatedRecordDto::class, $result['dto']);
        $this->assertSame('9', $result['dto']->rhsId);
        \DbStub::$hookResult = null;
    }
    public function testResolveTimeEntryByIdNoEventNameWhenSilent(): void {
        \DbStub::setRows([$this->timeRow(9, 'Sam', 'REG', '8')]);
        $service = new TimeTrackingService();
        $result = $service->resolveTimeEntryById(9);
        $this->assertSame('', $result['fields']['eventName']);
    }
    public function testResolveTimeEntryByIdNotFound(): void {
        \DbStub::setRows([]);
        $service = new TimeTrackingService();
        $result = $service->resolveTimeEntryById(99);
        $this->assertSame('Time Entry #99', $result['label']);
        $this->assertSame('#', $result['url']);
    }
    public function testListAllTimeEntries(): void {
        \DbStub::setRows([$this->timeRow(1, 'Sam', 'REG', '8'), $this->timeRow(2, 'Kim', 'OT', '4')]);
        $service = new TimeTrackingService();
        $results = $service->listAllTimeEntries();
        $this->assertCount(2, $results);
        $this->assertSame(1, $results[0]['id']);
        $this->assertSame('Sam', $results[0]['fields']['employeeId']);
        $this->assertSame('8', $results[0]['fields']['hours']);
        $this->assertSame(2, $results[1]['id']);
        $this->assertSame('OT', $results[1]['fields']['eventCode']);
        $this->assertInstanceOf(RelatedRecordDto::class, $results[0]['dto']);
    }
    public function testListAllTimeEntriesEmpty(): void {
        \DbStub::setRows([]);
        $service = new TimeTrackingService();
        $this->assertSame([], $service->listAllTimeEntries());
    }
    public function testResolveEventCodeByIdDelegatesToCodeMaster(): void {
        \DbStub::$hookResult = ['dto' => new RelatedRecordDto('code_master', '1', 'code_master', '1', null), 'fields' => ['code' => 'REG', 'name' => 'Regular']];
        $service = new TimeTrackingService();
        $result = $service->resolveEventCodeById(1);
        $this->assertArrayHasKey('fields', $result);
        $this->assertSame('REG', $result['fields']['code']);
        $this->assertCount(1, \DbStub::$hookCalls);
        $this->assertSame('ksf_FA_CodeMaster', \DbStub::$hookCalls[0][0]);
        $this->assertSame('CODEMASTER_RESOLVE', \DbStub::$hookCalls[0][1]);
        $this->assertSame('hrm.event_code', \DbStub::$hookCalls[0][2]['code_type']);
        $this->assertSame('1', \DbStub::$hookCalls[0][2]['code_value']);
    }
    public function testResolveEventCodeByIdNotFoundWhenSilent(): void {
        $service = new TimeTrackingService();
        $result = $service->resolveEventCodeById(1);
        $this->assertSame('Event Code not found', $result['label']);
    }
    public function testListAllEventCodesDelegatesToCodeMaster(): void {
        \DbStub::$hookResult = [['code' => 'REG', 'name' => 'Regular'], ['code' => 'OT', 'name' => 'Overtime']];
        $service = new TimeTrackingService();
        $result = $service->listAllEventCodes();
        $this->assertCount(2, $result);
        $this->assertSame('REG', $result[0]['code']);
        $this->assertCount(1, \DbStub::$hookCalls);
        $this->assertSame('ksf_FA_CodeMaster', \DbStub::$hookCalls[0][0]);
        $this->assertSame('CODEMASTER_LIST', \DbStub::$hookCalls[0][1]);
        $this->assertSame('hrm.event_code', \DbStub::$hookCalls[0][2]['code_type']);
    }
    public function testListAllEventCodesEmptyWhenSilent(): void {
        $service = new TimeTrackingService();
        $this->assertSame([], $service->listAllEventCodes());
    }
    public function testCalculatePayImpactRegular(): void {
        \DbStub::$hookResult = ['fields' => ['name' => 'Regular']];
        $result = (new TimeTrackingService())->calculatePayImpact('REG', 10.0, 20.0);
        \DbStub::$hookResult = null;
        $this->assertSame(1.00, $result['multiplier']);
        $this->assertSame(10.0, $result['hours']);
        $this->assertSame(10.0, $result['impactedHours']);
        $this->assertSame(200.0, $result['amount']);
        $this->assertSame('hourly', $result['payType']);
    }
    public function testCalculatePayImpactOvertime(): void {
        \DbStub::$hookResult = ['fields' => ['name' => 'Overtime']];
        $result = (new TimeTrackingService())->calculatePayImpact('OT', 10.0, 20.0);
        \DbStub::$hookResult = null;
        $this->assertSame(1.50, $result['multiplier']);
        $this->assertSame(15.0, $result['impactedHours']);
        $this->assertSame(300.0, $result['amount']);
    }
    public function testCalculatePayImpactDoubleOT(): void {
        \DbStub::$hookResult = ['fields' => ['name' => 'Double OT']];
        $result = (new TimeTrackingService())->calculatePayImpact('OT2', 4.0, 10.0);
        \DbStub::$hookResult = null;
        $this->assertSame(2.00, $result['multiplier']);
        $this->assertSame(8.0, $result['impactedHours']);
        $this->assertSame(80.0, $result['amount']);
    }
    public function testCalculatePayImpactUnknownCodeDefaultsToOne(): void {
        \DbStub::$hookResult = ['fields' => ['name' => 'Unknown']];
        $result = (new TimeTrackingService())->calculatePayImpact('XXX', 4.0, 10.0);
        \DbStub::$hookResult = null;
        $this->assertSame(1.00, $result['multiplier']);
        $this->assertSame(40.0, $result['amount']);
    }
    public function testTimeEntryFieldMapEqualsLabelMap(): void {
        $service = new TimeTrackingService();
        $this->assertSame($service->timeEntryLabelMap(), $service->timeEntryFieldMap());
    }
    public function testTimeEntryLabelMap(): void {
        $service = new TimeTrackingService();
        $this->assertSame([
            'employeeId' => 'Employee',
            'eventCode' => 'Event Code',
            'hours' => 'Hours',
            'workDate' => 'Work Date',
            'payImpact' => 'Pay Impact',
        ], $service->timeEntryLabelMap());
    }
    public function testEventCodeFieldMapEqualsLabelMap(): void {
        $service = new TimeTrackingService();
        $this->assertSame($service->eventCodeLabelMap(), $service->eventCodeFieldMap());
    }
    public function testEventCodeLabelMap(): void {
        $service = new TimeTrackingService();
        $this->assertSame([
            'code' => 'Code',
            'name' => 'Name',
            'description' => 'Description',
            'usage_instructions' => 'Usage Instructions',
        ], $service->eventCodeLabelMap());
    }
}
