<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Tests\Unit;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\TimeEntryRepository;
use PHPUnit\Framework\TestCase;
class TimeEntryRepositoryTest extends TestCase {
    protected function setUp(): void { \DbStub::reset(); }
    public function testFindAllReturnsRows(): void {
        \DbStub::setRows([[
            'id' => 1, 'employee_id' => 'E1', 'event_code' => 'REG', 'hours' => '8',
            'work_date' => '2026-01-02', 'pay_impact' => '', 'calculated_amount' => '0',
        ]]);
        $repo = new TimeEntryRepository();
        $rows = $repo->findAll();
        $this->assertCount(1, $rows);
        $this->assertSame('E1', $rows[0]['employee_id']);
        $this->assertStringContainsString('hrm_time_entries', \DbStub::$lastSql);
    }
    public function testFindAllDbFailureReturnsEmpty(): void {
        \DbStub::$queryResult = false;
        $repo = new TimeEntryRepository();
        $this->assertSame([], $repo->findAll());
    }
    public function testFindByIdReturnsRow(): void {
        \DbStub::setRows([['id' => 4, 'employee_id' => 'E1', 'event_code' => 'REG', 'hours' => '8', 'work_date' => '2026-01-02', 'pay_impact' => '', 'calculated_amount' => '0']]);
        $repo = new TimeEntryRepository();
        $row = $repo->findById(4);
        $this->assertSame(4, $row['id']);
        $this->assertStringContainsString('WHERE id = 4', \DbStub::$lastSql);
    }
    public function testFindByIdNotFoundReturnsNull(): void {
        \DbStub::setRows([]);
        $repo = new TimeEntryRepository();
        $this->assertNull($repo->findById(99));
    }
    public function testFindByEmployeeWithDateRange(): void {
        \DbStub::setRows([[
            'id' => 1, 'employee_id' => 'E1', 'event_code' => 'REG', 'hours' => '8', 'work_date' => '2026-01-02', 'pay_impact' => '', 'calculated_amount' => '0',
        ]]);
        $repo = new TimeEntryRepository();
        $rows = $repo->findByEmployee('E1', '2026-01-01', '2026-01-31');
        $this->assertCount(1, $rows);
        $this->assertStringContainsString("WHERE employee_id = ", \DbStub::$lastSql);
        $this->assertStringContainsString("AND work_date >= ", \DbStub::$lastSql);
        $this->assertStringContainsString("AND work_date <= ", \DbStub::$lastSql);
    }
    public function testFindByEmployeeEmpty(): void {
        \DbStub::setRows([]);
        $repo = new TimeEntryRepository();
        $this->assertSame([], $repo->findByEmployee('E1'));
    }
    public function testSaveReturnsInsertId(): void {
        \DbStub::$insertId = 12;
        $repo = new TimeEntryRepository();
        $id = $repo->save(['employee_id' => 'E1', 'event_code' => 'REG', 'hours' => '8', 'work_date' => '2026-01-02', 'pay_impact' => '', 'calculated_amount' => '0']);
        $this->assertSame(12, $id);
        $this->assertStringContainsString('INSERT INTO', \DbStub::$lastSql);
        $this->assertStringContainsString('hrm_time_entries', \DbStub::$lastSql);
    }
    public function testUpdateCallsDbQuery(): void {
        $repo = new TimeEntryRepository();
        $repo->update(3, ['hours' => '6']);
        $this->assertStringContainsString('UPDATE', \DbStub::$lastSql);
        $this->assertStringContainsString('hrm_time_entries', \DbStub::$lastSql);
        $this->assertStringContainsString('WHERE id = 3', \DbStub::$lastSql);
    }
    public function testUpdateNoSetsDoesNothing(): void {
        $repo = new TimeEntryRepository();
        $repo->update(3, []);
        $this->assertNull(\DbStub::$lastSql);
    }
    public function testDeleteCallsDbQuery(): void {
        $repo = new TimeEntryRepository();
        $repo->delete(9);
        $this->assertStringContainsString('DELETE FROM', \DbStub::$lastSql);
        $this->assertStringContainsString('hrm_time_entries', \DbStub::$lastSql);
        $this->assertStringContainsString('WHERE id = 9', \DbStub::$lastSql);
    }
}
