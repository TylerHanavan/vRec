<?php 
declare(strict_types=1);

    final class PluginManager {
        private static $instance = null;
        private array $plugins = [];
        private string $plugin_directory;
        private Logger $logger;
        private HookManager $hookman;

        private function __construct(string $plugin_directory, Logger $logger, HookManager $hookman) {
            $this->plugin_directory = $plugin_directory;
            $this->logger = $logger;
            $this->hookman = $hookman;
            $this->loadPlugins();
        }

        public static function getInstance(string $plugin_directory, Logger $logger, HookManager $hookman): PluginManager {
            if(($plugin_directory == null || $plugin_directory == '' || $logger == null || $hookman == null) && self::$instance == null) {
                throw new Exception('Plugin directory not set');
            }
            if (self::$instance == null) {
                $logger->log('Created plugin manager instance');
                self::$instance = new PluginManager($plugin_directory, $logger, $hookman);
            }
            return self::$instance;
        }

        private function loadPlugins(): void {
            $this->logger->log('Loading plugins');
            $pluginDir = $this->plugin_directory;
            $plugins = scandir($pluginDir);
            foreach ($plugins as $plugin) {
                if ($plugin == '.' || $plugin == '..') continue;
        
                $this->logger->log('Trying to load plugin ' . $plugin);
                $pluginPath = $pluginDir . '/' . $plugin;
        
                if (is_dir($pluginPath)) {
                    $pluginClass = "{$plugin}\\{$plugin}Plugin";
                    try {
                        require_once $pluginPath . '/' . $plugin . 'Plugin.php';
                        $this->plugins[] = new $pluginClass($this->hookman, $this->logger);
                        $this->logger->log('Successfully loaded ' . $plugin);
                    } catch (Exception $e) {
                        $this->logger->log('Failed to load ' . $plugin . ': ' . $e->getMessage());
                    }
                }
            }
            $this->logger->log('Finished loading plugins');
        }

        public function getPlugins(): array {
            return $this->plugins;
        }
    }

?>