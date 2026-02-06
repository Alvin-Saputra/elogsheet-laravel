CREATE TABLE IF NOT EXISTS `t_form_transfer_detail` (
    `id` VARCHAR(32) NOT NULL,
    `id_hdr` VARCHAR(32) NOT NULL,
    `oil_type` VARCHAR(45) NULL,
    `quantity` VARCHAR(45) NULL,
    `from_storage_tank_no` VARCHAR(45) NULL,
    `from_refinery_fractionation` VARCHAR(45) NULL,
    `from_other` VARCHAR(45) NULL,
    `to_storage_tank_no` VARCHAR(45) NULL,
    `to_refinery_fractionation` VARCHAR(45) NULL,
    `to_auto_filling_tank` INT NULL,
    `to_other` VARCHAR(45) NULL,
    `quality_m_and_i` DECIMAL(10,4) NULL,
    `quality_ffa` DECIMAL(10,4) NULL,
    `quality_lov_color_r` DECIMAL(10,4) NULL,
    `quality_lov_color_y` DECIMAL(10,4) NULL,
    `quality_cp_temp` DECIMAL(10,4) NULL,
    `quality_smp` DECIMAL(10,4) NULL,
    `quality_pv` DECIMAL(10,4) NULL,
    `quality_iv` DECIMAL(10,4) NULL,
    `remark` VARCHAR(45) NULL,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_form_transfer_detail_header`
        FOREIGN KEY (`id_hdr`) REFERENCES `t_form_transfer_header`(`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
