CREATE TABLE backups_executed (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, schedule_id INT NOT NULL, status INT NOT NULL, executed TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
