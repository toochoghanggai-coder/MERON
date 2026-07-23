CREATE TABLE IF NOT EXISTS tax_rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tax_rate_name VARCHAR(50) NOT NULL,
  tax_rate DECIMAL(5,2) NOT NULL
);

INSERT INTO tax_rates
  (tax_rate_name, tax_rate)
VALUES
  ('標準税率 10%', 10.00),
  ('軽減税率 8%', 8.00),
  ('非課税 0%', 0.00);
