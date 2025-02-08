<?php declare(strict_types=1); // strict typing

    class Hook {
            
            private $function;
            private $conditions;
    
            function __construct($function, $conditions) {
                $this->function = $function;
                $this->conditions = $conditions;
            }
    
            function get_function() {
                return $this->function;
            }

            /**
             * Get the value of conditions
             * 
             * Examples:
             * logged_in = true
             * url = '/xhr/update_record'
             * 
             * @return mixed
             */
            function get_conditions() {
                return $this->conditions;
            }

            function can_call_hook($cur_conditions) {

                if($this->conditions == null)
                    return true;

                $can_call = true;

                foreach($this->conditions as $key => $value) {
                    if(!in_array($key, array_keys($cur_conditions)))
                        return false;

                    if(is_array($value)) {
                        if(!in_array($cur_conditions[$key], $value))
                            return false;
                    } else {
                        if($cur_conditions[$key] !== $value)
                            return false;
                    }
                }

                return $can_call;
            }


    }

?>
