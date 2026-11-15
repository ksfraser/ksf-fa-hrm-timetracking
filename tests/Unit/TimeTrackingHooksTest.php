<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Tests\Unit;
use ksfraser\RelatedTo\DTO\RelatedRecordDto;
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../hooks.php';
class TimeTrackingHooksTest extends TestCase {
    protected function setUp(): void { \DbStub::reset(); }
    private function hooks(): \hooks_ksf_FA_HRM_TimeTracking {
        return new \hooks_ksf_FA_HRM_TimeTracking();
    }
    public function testRespondersConstant(): void {
        $responders = \hooks_ksf_FA_HRM_TimeTracking::RESPONDERS;
        $this->assertTrue(in_array('RELATED_TO_FIELDS', $responders, true));
        $this->assertTrue(in_array('TIMEENTRY_RESOLVE', $responders, true));
        $this->assertTrue(in_array('TIMEENTRY_FIELDS', $responders, true));
    }
    public function testRelatedToFieldsTimeEntryType(): void {
        $data = ['module' => 'ksf_FA_HRM_TimeTracking', 'type' => 'time_entry', 'fields' => []];
        $this->hooks()->RELATED_TO_FIELDS($data);
        $this->assertSame('Employee', $data['fields']['employeeId']);
        $this->assertSame('Project', $data['fields']['project_id']);
        $this->assertSame('Activity Code', $data['fields']['activity_code']);
        $this->assertSame('Pay Impact', $data['fields']['payImpact']);
    }
    public function testRelatedToFieldsOtherTypeNoOp(): void {
        $data = ['module' => 'x', 'type' => 'payroll', 'fields' => ['a' => 1]];
        $this->hooks()->RELATED_TO_FIELDS($data);
        $this->assertSame(['a' => 1], $data['fields']);
    }
    public function testRelatedToFieldsMissingModuleError(): void {
        $data = ['type' => 'time_entry'];
        $this->hooks()->RELATED_TO_FIELDS($data);
        $this->assertArrayHasKey('error', $data);
    }
    public function testTimeEntryResolve(): void {
        \DbStub::setRows([['id' => 9, 'employee_id' => 'E1', 'employee_name' => 'Sam', 'project_id' => 'P1', 'project_name' => 'Project Alpha', 'activity_code' => 'CODING', 'activity_name' => 'Software Development', 'hours' => '8', 'work_date' => '2026-01-02', 'pay_impact' => '', 'calculated_amount' => '0']]);
        $dto = new RelatedRecordDto('time_entry', '0', 'time_entry', '0', null);
        $data = ['time_entry_id' => 9, 'dto' => $dto];
        $this->hooks()->TIMEENTRY_RESOLVE($data);
        $this->assertArrayHasKey('result', $data);
        $this->assertSame('Sam', $data['result']['fields']['employeeId']);
        $this->assertSame('Project Alpha', $data['result']['fields']['project_id']);
        $this->assertSame('CODING', $data['result']['fields']['activity_code']);
        $this->assertSame('Sam — Project Alpha — CODING — 8h', $data['result']['label']);
        $this->assertSame($dto, $data['result']['dto']);
    }
    public function testTimeEntryResolveMissingIdError(): void {
        $data = ['dto' => new RelatedRecordDto('time_entry', '0', 'time_entry', '0', null)];
        $this->hooks()->TIMEENTRY_RESOLVE($data);
        $this->assertArrayHasKey('error', $data);
    }
    public function testTimeEntryResolveNonNumericError(): void {
        $data = ['time_entry_id' => 'abc', 'dto' => new RelatedRecordDto('time_entry', '0', 'time_entry', '0', null)];
        $this->hooks()->TIMEENTRY_RESOLVE($data);
        $this->assertArrayHasKey('error', $data);
    }
    public function testTimeEntryResolveMissingDtoError(): void {
        $data = ['time_entry_id' => 9];
        $this->hooks()->TIMEENTRY_RESOLVE($data);
        $this->assertArrayHasKey('error', $data);
    }
    public function testTimeEntryFields(): void {
        $data = ['fields' => []];
        $this->hooks()->TIMEENTRY_FIELDS($data);
        $this->assertSame('Employee', $data['fields']['employeeId']);
        $this->assertSame('Project', $data['fields']['project_id']);
        $this->assertSame('Activity Code', $data['fields']['activity_code']);
        $this->assertSame('Pay Impact', $data['fields']['payImpact']);
    }
}
