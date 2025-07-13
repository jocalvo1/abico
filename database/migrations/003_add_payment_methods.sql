-- Create payment methods table
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default payment methods
INSERT INTO `payment_methods` (`name`, `description`) VALUES
('Cash', 'Cash payment'),
('Online', 'Online payment or bank transfer'),
('Cheque', 'Payment by check');

-- Add payment_method_id to transactions table if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'transactions';
SET @columnname = 'payment_method_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `payment_method_id` INT NULL AFTER `payment_method`;')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add foreign key constraint if it doesn't exist
SET @constraint_name = 'fk_transaction_payment_method';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (CONSTRAINT_SCHEMA = @dbname)
      AND (TABLE_NAME = 'transactions')
      AND (CONSTRAINT_NAME = @constraint_name)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE transactions ADD CONSTRAINT ', @constraint_name, ' FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL ON UPDATE CASCADE;')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Create index for better performance if it doesn't exist
SET @index_name = 'idx_transaction_payment_method';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = 'transactions')
      AND (INDEX_NAME = @index_name)
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX ', @index_name, ' ON transactions (payment_method_id);')
));

PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;
