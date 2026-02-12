-- Drop checked_* and acknowledged_* columns from t_form_transfer_header
-- Migration: 2026_02_09_141014_drop_form_transfer_checked_acknowledged_columns.php

ALTER TABLE `t_form_transfer_header`
    DROP COLUMN `checked_status`,
    DROP COLUMN `checked_by`,
    DROP COLUMN `checked_date`,
    DROP COLUMN `checked_status_remarks`,
    DROP COLUMN `acknowledged_status`,
    DROP COLUMN `acknowledged_by`,
    DROP COLUMN `acknowledged_date`,
    DROP COLUMN `acknowledged_status_remarks`;

-- Rollback (if needed):
-- ALTER TABLE `t_form_transfer_header`
--     ADD COLUMN `checked_status` VARCHAR(20) NULL,
--     ADD COLUMN `checked_by` VARCHAR(50) NULL,
--     ADD COLUMN `checked_date` DATETIME NULL,
--     ADD COLUMN `checked_status_remarks` VARCHAR(100) NULL,
--     ADD COLUMN `acknowledged_status` VARCHAR(20) NULL,
--     ADD COLUMN `acknowledged_by` VARCHAR(50) NULL,
--     ADD COLUMN `acknowledged_date` DATETIME NULL,
--     ADD COLUMN `acknowledged_status_remarks` VARCHAR(100) NULL;
