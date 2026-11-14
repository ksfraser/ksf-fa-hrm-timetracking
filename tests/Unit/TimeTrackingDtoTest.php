<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Tests\Unit;
use Ksfraser\FrontAccounting\HRM\TimeTracking\DTO\EventCodeDTO;
use Ksfraser\FrontAccounting\HRM\TimeTracking\DTO\TimeEntryDTO;
use PHPUnit\Framework\TestCase;
class TimeTrackingDtoTest extends TestCase {
    public function testEventCodeDtoDisplayLabel(): void {
        $dto = new EventCodeDTO(1, 'OT', 'Overtime', '1.5000', 'hourly', '', true);
        $this->assertSame('OT — Overtime', $dto->getDisplayLabel());
    }
    public function testTimeEntryDtoDisplayLabel(): void {
        $dto = new TimeEntryDTO(7, 'E123', 'Alice Smith', 'OT', 'Overtime', '8.00', '2026-10-15', 'hourly', '120.00');
        $this->assertSame('Alice Smith — OT — 8.00h', $dto->getDisplayLabel());
    }
    public function testTimeEntryDtoMinimal(): void {
        $dto = new TimeEntryDTO(3, '', '', '', '', '', '', '', '');
        $this->assertSame('Time Entry #3', $dto->getDisplayLabel());
    }
    public function testEventCodeToArray(): void {
        $dto = new EventCodeDTO(1, 'OT', 'Overtime', '1.5000', 'hourly', '', true);
        $array = $dto->toArray();
        $this->assertSame('OT', $array['event_code']);
        $this->assertSame('Overtime', $array['event_name']);
        $this->assertSame('1.5000', $array['impact_multiplier']);
        $this->assertSame('hourly', $array['pay_type']);
    }
    public function testTimeEntryFromArray(): void {
        $dto = TimeEntryDTO::fromArray(array('id' => 9, 'employee_id' => 'E456', 'employee_name' => 'Bob Jones', 'event_code' => 'REG', 'event_name' => 'Regular Time', 'hours' => '40.00', 'work_date' => '2026-10-15', 'pay_impact' => 'hourly', 'calculated_amount' => '1600.00'));
        $this->assertSame(9, $dto->id);
        $this->assertSame('E456', $dto->employeeId);
        $this->assertSame('Bob Jones', $dto->employeeName);
        $this->assertSame('REG', $dto->eventCode);
        $this->assertSame('40.00', $dto->hours);
    }
}
