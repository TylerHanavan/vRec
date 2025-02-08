CREATE TABLE worker_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    namespace VARCHAR(256),
    plugin_file VARCHAR(256),
    func VARCHAR(256),
    frequency INT
);