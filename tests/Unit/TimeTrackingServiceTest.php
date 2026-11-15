<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Tests\Unit;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\TimeEntryRepository;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Service\TimeTrackingService;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
use PHPUnit\Framework\TestCase;
class TimeTrackingServiceTest extends TestCase {
    protected function setUp(): void { \DbStub::reset(); }
    private function timeRow($id = 9, $name = 'Sam', $project = 'P1', $projectName = 'Project Alpha', $activity = 'CODING', $activityName = 'Software Development', $hours = '8') {
        return ['id' => $id, 'employee_id' => 'E1', 'employee_name' => $name,
            'project_id' => $project, 'project_name' => $projectName,
            'activity_code' => $activity, 'activity_name' => $activityName,
            'hours' => $hours, 'work_date' => '2026-01-02', 'pay_impact' => '', 'calculated_amount' => '0'];
    }
    public function testResolveTimeEntryByIdFoundWithActivityName(): void {
        \DbStub::setRows([$this->timeRow(9, 'Sam', 'P1', 'Project Alpha', 'CODING', 'Software Development', '8')]);
        \DbStub::$hookResult = ['fields' => ['name' => 'Software Development']];
        $service = new TimeTrackingService();
        $result = $service->resolveTimeEntryById(9);
        $this->assertSame('Sam', $result['fields']['employeeId']);
        $this->assertSame('Project Alpha', $result['fields']['project_id']);
        $this->assertSame('CODING', $result['fields']['activity_code']);
        $this->assertSame('Software Development', $result['fields']['activity_name']);
        $this->assertSame('8', $result['fields']['hours']);
        $this->assertSame('2026-01-02', $result['fields']['workDate']);
        $this->assertSame('', $result['fields']['payImpact']);
        $this->assertSame('Sam — Project Alpha — CODING — 8h', $result['label']);
        $this->assertSame('/modules/ksf_FA_HRM_TimeTracking/pages/time_entry_form.php?action=edit&time_entry_id=9', $result['url']);
        $this->assertInstanceOf(RelatedRecordDto::class, $result['dto']);
        $this->assertSame('9', $result['dto']->rhsId);
        \DbStub::$hookResult = null;
    }
    public function testResolveTimeEntryByIdNoEventNameWhenSilent(): void {
        \DbStub::setRows([$this->timeRow(9, 'Sam', 'P1', 'Project Alpha', 'CODING', 'Software Development', '8')]);
        $service = new TimeTrackingService();
        $result = $service->resolveTimeEntryById(9);
        $this->assertSame('', $result['fields']['activity_name']);
    }
    public function testResolveTimeEntryByIdNotFound(): void {
        \DbStub::setRows([]);
        $service = new TimeTrackingService();
        $result = $service->resolveTimeEntryById(99);
        $this->assertSame('Time Entry #99', $result['label']);
        $this->assertSame('#', $result['url']);
    }
    public function testListAllTimeEntries(): void {
        \DbStub::setRows([$this->timeRow(1, 'Sam', 'P1', 'Project Alpha', 'CODING', 'Software Development', '8'), $this->timeRow(2, 'Kim', 'P2', 'Project Beta', 'REG', 'Regular Time', '4')]);
        $service = new TimeTrackingService();
        $results = $service->listAllTimeEntries();
        $this->assertCount(2, $results);
        $this->assertSame(1, $results[0]['id']);
        $this->assertSame('Sam', $results[0]['fields']['employeeId']);
        $this->assertSame('Project Alpha', $results[0]['fields']['project_id']);
        $this->assertSame('8', $results[0]['fields']['hours']);
        $this->assertSame(2, $results[1]['id']);
        $this->assertSame('REG', $results[1]['fields']['activity_code']);
        $this->assertInstanceOf(RelatedRecordDto::class, $results[0]['dto']);
    }
    public function testListAllTimeEntriesEmpty(): void {
        \DbStub::setRows([]);
        $service = new TimeTrackingService();
        $this->assertSame([], $service->listAllTimeEntries());
    }
    public function testTimeEntryFieldMapEqualsLabelMap(): void {
        $service = new TimeTrackingService();
        $this->assertSame($service->timeEntryLabelMap(), $service->timeEntryFieldMap());
    }
    public function testTimeEntryLabelMap(): void {
        $service = new TimeTrackingService();
        $this->assertSame([
            'employeeId' => 'Employee',
            'project_id' => 'Project',
            'activity_code' => 'Activity Code',
            'hours' => 'Hours',
            'workDate' => 'Work Date',
            'payImpact' => 'Pay Impact',
        ], $service->timeEntryLabelMap());
    }
}
