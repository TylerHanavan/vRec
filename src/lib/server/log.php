<?php declare(strict_types=1); // strict typing

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

    class Logger {

        private string $log_file;
        private int $uuid_length = 0;
        private string $uuid;
        private array $buffer = array();
        private int $bufferLimit = 100;
        private array $flushing = array();

        function __construct(string $log_file, int $uuid_length = 10) {
            $this->uuid_length = $uuid_length;
            $this->log_file = $log_file;
        }

        public function __destruct() {
            foreach($this->buffer as $path => $messages) {
                $this->flush_buffer($path);
            }
        }

        function log(string $log_message, string $log_level = 'INFO'): void {
            $uuid = $this->get_uuid();

            $log_message = "[$uuid - " . date("Y-m-d H:i:s.").gettimeofday()["usec"] . "] $log_level - " . $log_message . PHP_EOL;

            $this->buffer_log($this->log_file, $log_message);

        }

        function log_other_file(string $file, string $log_message, string $log_level = 'INFO'): void {
            $uuid = $this->get_uuid();

            $log_message = "[$uuid - " . date("Y-m-d H:i:s.").gettimeofday()["usec"] . "] $log_level - " . $log_message . PHP_EOL;

            $this->buffer_log($file, $log_message);

        }

        private function get_uuid(): string {
            if($this->uuid !== null) {
                return $this->uuid;
            }
            $nano = exec('date +%s%N');
            $this->uuid = substr(md5($nano . ''), 0, $this->uuid_length);
            return $this->uuid;
        }

        private function buffer_log(string $path, string $message): void {
            if(!isset($this->buffer[$path])) {
                $this->buffer[$path] = array();
            }
            $this->buffer[$path][] = $message;

            if(count($this->buffer[$path]) >= $this->bufferLimit && !$this->flushing[$path]) {
                $this->flush_buffer($path);
            }
        }

        private function flush_buffer(string $path): void {
            $cmd = "mkdir -p " . dirname($path) . "";
            $output = '';

            exec($cmd, $output);

            $cmd = "touch $path &";

            exec($cmd, $output);
            
            $this->flushing[$path] = true;
            $this->log("Flushing log buffer to $path");
            file_put_contents($path, $this->buffer[$path], FILE_APPEND);
            $this->buffer[$path] = array();
            $this->flushing[$path] = false;
        }

    }

?>
