<?php declare(strict_types=1); // strict typing
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
    final class HookManager {
        
        private $hooks;
        private $hooksIndex;
        private $logger;

        private $debug = false;

        function __construct($logger) {
            $this->hooks = array();
            $this->hooksIndex = array();
            $this->logger = $logger;
        }
    
        /**
         * $hooks
         *      Array of Hook objects
         * 
         * $hooksIndex
         *    Array of conditions and values that map to the Hook objects
         *  Example:
         *     $hooksIndex = array(
         *        'url' => array(
         *          '/main.js',
         *          '/modal.js'  
         *         ),
         *        'layer' => 'js_load'
         *       )
         *    )
         * 
         */
        function add_hook($function, $conditions = null) {

            $hook = new Hook($function, $conditions);

            $this->hooks[] = $hook;

            if(is_array($hook->get_function()))
                $this->logger->log('HOOKMON - Registering hook: ' . json_encode($hook->get_function()));
            else
                $this->logger->log('HOOKMON - Registering hook: ' . $hook->get_function());

            foreach($conditions as $condition => $value) {
                if(!isset($this->hooksIndex[$condition]))
                    $this->hooksIndex[$condition] = array();
                if(is_array($value)) {
                    foreach($value as $v) {
                        if(!isset($this->hooksIndex[$condition][$v]))
                            $this->hooksIndex[$condition][$v] = array();
                        $this->hooksIndex[$condition][$v][] = $hook;
                    }
                } else {
                    if(!isset($this->hooksIndex[$condition][$value]))
                        $this->hooksIndex[$condition][$value] = array();
                    $this->hooksIndex[$condition][$value][] = $hook;
                }
            }

            foreach($this->get_default_hooks() as $condition => $value) {
                if(!isset($conditions[$condition])) {
                    if(!isset($this->hooksIndex[$condition]))
                        $this->hooksIndex[$condition] = array();
                    if(!isset($this->hooksIndex[$condition][$value]))
                        $this->hooksIndex[$condition][$value] = array();
                    $this->hooksIndex[$condition][$value][] = $hook;
                }
            }

        }
    
        function remove_hook($hook_name) {
    
            // TODO: Implement
        }
    
        function call_hook(&$parameters, $conditions) {
    
            if(!isset($parameters['_GET']) || $parameters['_GET'] == null)
                $parameters['_GET'] = array();
            if(!isset($parameters['_POST']) || $parameters['_POST'] == null)
                $parameters['_POST'] = array();

            if($conditions == null)
                $conditions = array();
    
            foreach ($this->get_hooks_callable($conditions) as $hook)
                if($hook->can_call_hook($conditions))
                    if(is_callable($hook->get_function())) {
                        if(is_array($hook->get_function()))
                            $this->logger->log('HOOKMON - Calling hook: ' . json_encode($hook->get_function()));
                        else
                            $this->logger->log('HOOKMON - Calling hook: ' . $hook->get_function());
                        call_user_func_array($hook->get_function(), array(&$parameters));
                    } else {
                        if(is_array($hook->get_function()))
                            $this->logger->log('HOOKMON - Tried calling hook but could not find function: ' . json_encode($hook->get_function()));
                        else
                            $this->logger->log('HOOKMON - Tried calling hook but could not find function: ' . $hook->get_function());
                    }
        }

        function get_hooks_for_condition($condition, $value) {
            $hooks = array();
            if(isset($this->hooksIndex[$condition]) && isset($this->hooksIndex[$condition][$value])) {
                $hooks = $this->hooksIndex[$condition][$value];
            }
            return $hooks;
        }

        private function compare_hooks($hook1, $hook2) {
            return $hook1->get_function() == $hook2->get_function() ? 0 : 1;
        }

        function get_hooks_callable($conditions) {
            $hooks = null;

            foreach($conditions as $condition => $value) {
                if(isset($this->hooksIndex[$condition]) && isset($this->hooksIndex[$condition][$value])) {
                    if($hooks == null) {
                        $hooks = $this->hooksIndex[$condition][$value];
                        if($this->debug) {
                            echo "Sizeof hooks(a) [$condition][$value]: ", sizeof($hooks), "<br />";
                            foreach($hooks as $hook) {
                                echo $hook->get_function(), " (layer for this one is ", $hook->get_conditions()['layer'], ")<br />";
                            }
                        }
                    }
                    else {
                        $hooks = array_uintersect($hooks, $this->hooksIndex[$condition][$value], array($this, 'compare_hooks'));
                        if($this->debug) {
                            echo "Sizeof hooks(b) [$condition][$value]: ", sizeof($hooks), "<br />";
                            foreach($hooks as $hook) {
                                echo $hook->get_function(), " (layer for this one is ", $hook->get_conditions()['layer'], ")<br />";
                            }
                        }
                    }
                }
            }

            if($this->debug) {

                echo "<br />Conditions<br />";

                foreach($conditions as $condition => $value) {
                    echo $condition, ' = ', $value, "<br />";
                }

                echo "Callable hooks<br />";

                foreach($hooks as $hook) {
                    echo "<strong>",$hook->get_function(), "</strong><br />";
                }

                echo "<br /><br /><br />";

            }

            /*foreach($this->get_default_hooks() as $condition => $value) {
                if(isset($this->hooksIndex[$condition]) && isset($this->hooksIndex[$condition][$value])) {
                    var_dump($this->hooksIndex[$condition][$value]);
                    echo "\n\n";
                    $hooks = array_uintersect($hooks, $this->hooksIndex[$condition][$value], array($this, 'compare_hooks'));
                }
            }*/

            return $hooks ?? array();
        }

        function get_default_hooks() {
            return array(
                'url' => null,
                'logged_in' => null,
                'layer' => null
            );
        }

    }

?>
