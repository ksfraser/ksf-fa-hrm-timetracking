<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\Tests\Unit;
use Ksfraser\FrontAccounting\HRM\TimeTracking\DTO\TimeEntryDTO;
use PHPUnit\Framework\TestCase;
class TimeTrackingDtoTest extends TestCase {
    public function testTimeEntryDtoDisplayLabel(): void {
        $dto = new TimeEntryDTO(7, 'E123', 'Alice Smith', 'P1', 'Project Alpha', 'CODING', 'Software Development', '8.00', '2026-10-15', 'hourly', '120.00');
        $this->assertSame('Alice Smith — Project Alpha — CODING — 8.00h', $dto->getDisplayLabel());
    }
    public function testTimeEntryDtoMinimal(): void {
        $dto = new TimeEntryDTO(3, '', '', '', '', '', '', '', '', '', '');
        $this->assertSame('Time Entry #3', $dto->getDisplayLabel());
    }
    public function testTimeEntryToArray(): void {
        $dto = new TimeEntryDTO(7, 'E123', 'Alice Smith', 'P1', 'Project Alpha', 'CODING', 'Software Development', '8.00', '2026-10-15', 'hourly', '120.00');
        $array = $dto->toArray();
        $this->assertSame(7, $array['id']);
        $this->assertSame('E123', $array['employee_id']);
        $this->assertSame('Alice Smith', $array['employee_name']);
        $this->assertSame('P1', $array['project_id']);
        $this->assertSame('Project Alpha', $array['project_name']);
        $this->assertSame('CODING', $array['activity_code']);
        $this->assertSame('Software Development', $array['activity_name']);
        $this->assertSame('8.00', $array['hours']);
        $this->assertSame('2026-10-15', $array['work_date']);
        $this->assertSame('hourly', $array['pay_impact']);
        $this->assertSame('120.00', $array['calculated_amount']);
    }
    public function testTimeEntryFromArray(): void {
        $dto = TimeEntryDTO::fromArray(array(
            'id' => 9, 'employee_id' => 'E456', 'employee_name' => 'Bob Jones',
            'project_id' => 'P2', 'project_name' => 'Project Beta',
            'activity_code' => 'REG', 'activity_name' => 'Regular Time',
            'hours' => '40.00', 'work_date' => '2026-10-15', 'pay_impact' => 'hourly', 'calculated_amount' => '1600.00'
        ));
        $this->assertSame(9, $dto->id);
        $this->assertSame('E456', $dto->employeeId);
        $this->assertSame('Bob Jones', $dto->employeeName);
        $this->assertSame('P2', $dto->project_id);
        $this->assertSame('Project Beta', $dto->project_name);
        $this->assertSame('REG', $dto->activity_code);
        $this->assertSame('40.00', $dto->hours);
    }
}
