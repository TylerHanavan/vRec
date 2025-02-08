CREATE TABLE audit_event_types (
    id INT AUTO_INCREMENT PRIMARY KEY,                     -- Primary key for event types
    event_type_name VARCHAR(255) NOT NULL,      -- Name of the event type, e.g., 'Added', 'Deleted'
    event_type_description TEXT                -- Description of the event type
);