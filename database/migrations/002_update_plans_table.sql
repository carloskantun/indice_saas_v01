-- Actualización de la tabla plans
ALTER TABLE plans
    CHANGE COLUMN price price_monthly DECIMAL(10,2) DEFAULT 0,
    CHANGE COLUMN max_users users_max INT DEFAULT 3,
    CHANGE COLUMN max_units units_max INT DEFAULT 1,
    CHANGE COLUMN max_businesses businesses_max INT DEFAULT 1,
    CHANGE COLUMN max_companies companies_max INT DEFAULT 1,
    CHANGE COLUMN features modules_included JSON,
    ADD COLUMN storage_max_mb INT DEFAULT 100 AFTER businesses_max,
    CHANGE COLUMN status is_active TINYINT(1) DEFAULT 1;
