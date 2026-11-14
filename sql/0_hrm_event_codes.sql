-- ksf_FA_HRM_TimeTracking: event codes master table

CREATE TABLE IF NOT EXISTS `0_hrm_event_codes` (
  `id`             INT(11) NOT NULL AUTO_INCREMENT,
  `event_code`     VARCHAR(20)  NOT NULL DEFAULT '' COMMENT 'Ref code (REG, OT, HOL, SICK, VAC, COMM, etc.)',
  `event_name`     VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Human-readable name',
  `impact_multiplier` DECIMAL(5,4) NOT NULL DEFAULT 1.0000 COMMENT 'Multiplier applied to hours (e.g., OT=1.5, HOL=2.0)',
  `pay_type`       VARCHAR(20)  NOT NULL DEFAULT 'hourly' COMMENT 'hourly|salary|commission|none',
  `description`    TEXT,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_event_code` (`event_code`),
  KEY `idx_pay_type` (`pay_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default event codes
INSERT IGNORE INTO `0_hrm_event_codes` (`event_code`, `event_name`, `impact_multiplier`, `pay_type`, `description`) VALUES
('REG', 'Regular Time', '1.0000', 'hourly', 'Standard work hours'),
('OT', 'Overtime', '1.5000', 'hourly', 'Overtime hours at 1.5x'),
('OT2', 'Double Overtime', '2.0000', 'hourly', 'Double overtime hours at 2.0x'),
('HOL', 'Holiday', '2.0000', 'hourly', 'Holiday work at 2.0x'),
('SICK', 'Sick Leave', '1.0000', 'salary', 'Sick leave (paid)'),
('VAC', 'Vacation', '1.0000', 'salary', 'Vacation leave (paid)'),
('COMM', 'Commission', '1.0000', 'commission', 'Commission-based earnings'),
('BEREAVE', 'Bereavement', '1.0000', 'salary', 'Bereavement leave (paid)'),
('JURY', 'Jury Duty', '1.0000', 'salary', 'Jury duty (paid)');
