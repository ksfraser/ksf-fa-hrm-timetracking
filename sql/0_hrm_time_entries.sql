-- ksf_FA_HRM_TimeTracking: time entries

CREATE TABLE IF NOT EXISTS `0_hrm_time_entries` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `employee_id` VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'Ref to crm_persons.id or employee ref code',
  `event_code`  VARCHAR(20)  NOT NULL DEFAULT '' COMMENT 'Ref to 0_hrm_event_codes.event_code',
  `hours`       DECIMAL(6,2) NOT NULL DEFAULT 0 COMMENT 'Hours worked',
  `work_date`   DATE         NOT NULL COMMENT 'Date work was performed',
  `pay_impact`  VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'Calculated pay impact description',
  `calculated_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Calculated pay amount',
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_event_code` (`event_code`),
  KEY `idx_work_date` (`work_date`),
  KEY `idx_employee_date` (`employee_id`, `work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
