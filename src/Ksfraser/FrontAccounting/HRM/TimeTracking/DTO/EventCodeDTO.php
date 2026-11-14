<?php
declare(strict_types=1);
namespace Ksfraser\FrontAccounting\HRM\TimeTracking\DTO;
final class EventCodeDTO {
    public $id;
    public $eventCode;
    public $eventName;
    public $impactMultiplier;
    public $payType;
    public $description;
    public $isActive;
    public function __construct(int $id, string $eventCode = '', string $eventName = '', string $impactMultiplier = '1.00', string $payType = 'hourly', string $description = '', bool $isActive = true) {
        $this->id = $id; $this->eventCode = $eventCode; $this->eventName = $eventName; $this->impactMultiplier = $impactMultiplier;
        $this->payType = $payType; $this->description = $description; $this->isActive = $isActive;
    }
    public function getDisplayLabel(): string {
        $parts = array();
        if ($this->eventCode !== '') { $parts[] = $this->eventCode; }
        if ($this->eventName !== '') { $parts[] = $this->eventName; }
        return $parts === [] ? _('Event #') . (string) $this->id : implode(' — ', $parts);
    }
    public function toArray(): array {
        return array('id' => $this->id, 'event_code' => $this->eventCode, 'event_name' => $this->eventName, 'impact_multiplier' => $this->impactMultiplier, 'pay_type' => $this->payType, 'description' => $this->description, 'is_active' => $this->isActive);
    }
    public static function fromArray(array $data): self {
        return new self((int) ($data['id'] ?? 0), (string) ($data['event_code'] ?? ''), (string) ($data['event_name'] ?? ''), (string) ($data['impact_multiplier'] ?? '1.00'), (string) ($data['pay_type'] ?? 'hourly'), (string) ($data['description'] ?? ''), !empty($data['is_active']));
    }
}
