<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Tests\Unit;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\EventCodeRepository;
use Ksfraser\FrontAccounting\HRM\TimeTracking\Repository\TimeEntryRepository;
use PHPUnit\Framework\TestCase;
class EventCodeRepositoryTest extends TestCase {
    protected function setUp(): void { \DbStub::reset(); }
    public function testFindAllReturnsActiveRows(): void {
        \DbStub::setRows([[
            'id' => 1, 'event_code' => 'REG', 'event_name' => 'Regular', 'impact_multiplier' => '1.00',
            'pay_type' => 'hourly', 'description' => 'Regular hours', 'is_active' => '1',
        ]]);
        $repo = new EventCodeRepository();
        $rows = $repo->findAll();
        $this->assertCount(1, $rows);
        $this->assertSame('REG', $rows[0]['event_code']);
        $this->assertStringContainsString('is_active = 1', \DbStub::$lastSql);
        $this->assertStringContainsString('hrm_event_codes', \DbStub::$lastSql);
    }
    public function testFindAllDbFailureReturnsEmpty(): void {
        \DbStub::$queryResult = false;
        $repo = new EventCodeRepository();
        $this->assertSame([], $repo->findAll());
    }
    public function testFindByIdReturnsRow(): void {
        \DbStub::setRows([['id' => 2, 'event_code' => 'OT', 'event_name' => 'Overtime', 'impact_multiplier' => '1.50', 'pay_type' => 'hourly', 'description' => '', 'is_active' => '1']]);
        $repo = new EventCodeRepository();
        $row = $repo->findById(2);
        $this->assertSame(2, $row['id']);
        $this->assertStringContainsString('WHERE id = 2', \DbStub::$lastSql);
    }
    public function testFindByIdNotFoundReturnsNull(): void {
        \DbStub::setRows([]);
        $repo = new EventCodeRepository();
        $this->assertNull($repo->findById(99));
    }
    public function testFindByCodeReturnsRow(): void {
        \DbStub::setRows([['id' => 3, 'event_code' => 'HOL', 'event_name' => 'Holiday', 'impact_multiplier' => '2.00', 'pay_type' => 'hourly', 'description' => '', 'is_active' => '1']]);
        $repo = new EventCodeRepository();
        $row = $repo->findByCode('HOL');
        $this->assertSame('HOL', $row['event_code']);
        $this->assertStringContainsString("WHERE event_code = ", \DbStub::$lastSql);
    }
    public function testFindByCodeNotFoundReturnsNull(): void {
        \DbStub::setRows([]);
        $repo = new EventCodeRepository();
        $this->assertNull($repo->findByCode('NOPE'));
    }
    public function testSaveReturnsInsertId(): void {
        \DbStub::$insertId = 5;
        $repo = new EventCodeRepository();
        $id = $repo->save(['event_code' => 'OT2', 'event_name' => 'Double OT', 'impact_multiplier' => '2.00', 'pay_type' => 'hourly', 'description' => '', 'is_active' => 1]);
        $this->assertSame(5, $id);
        $this->assertStringContainsString('INSERT INTO', \DbStub::$lastSql);
        $this->assertStringContainsString('hrm_event_codes', \DbStub::$lastSql);
    }
    public function testUpdateCallsDbQuery(): void {
        $repo = new EventCodeRepository();
        $repo->update(1, ['impact_multiplier' => '1.75']);
        $this->assertStringContainsString('UPDATE', \DbStub::$lastSql);
        $this->assertStringContainsString('hrm_event_codes', \DbStub::$lastSql);
        $this->assertStringContainsString('WHERE id = 1', \DbStub::$lastSql);
    }
    public function testUpdateNoSetsDoesNothing(): void {
        $repo = new EventCodeRepository();
        $repo->update(1, []);
        $this->assertNull(\DbStub::$lastSql);
    }
}
