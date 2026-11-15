-- ksf_FA_HRM_TimeTracking: event codes are now managed in ksf_FA_CodeMaster
-- (0_ksf_code_master, code_type = 'hrm.activity_code').
-- This file is kept for backward compatibility; the table is intentionally
-- empty. All activity code definitions live in ksf_FA_CodeMaster.

CREATE TABLE IF NOT EXISTS `0_hrm_event_codes` (
  `id`            INT(11) NOT NULL AUTO_INCREMENT,
  `event_code`    VARCHAR(20)  NOT NULL DEFAULT '' COMMENT 'Deprecated - use ksf_FA_CodeMaster instead',
  `event_name`    VARCHAR(100) NOT NULL DEFAULT '',
  `impact_multiplier` DECIMAL(5,4) NOT NULL DEFAULT 1.0000,
  `pay_type`       VARCHAR(20)  NOT NULL DEFAULT 'hourly',
  `description`    TEXT,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
