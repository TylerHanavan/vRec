CREATE TABLE audit_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_event_type_id INT NOT NULL,
    user_id INT NOT NULL,
    batch_id INT DEFAULT 0,  -- Reserved for future use
    table_id INT NOT NULL,
    row_id INT NOT NULL, 
    event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    old_value TEXT,
    new_value TEXT,
    raw_audit_json TEXT,
    cms_path VARCHAR(1024),
    http_response_body TEXT,
    FOREIGN KEY (audit_event_type_id) REFERENCES audit_event_types(id)
);