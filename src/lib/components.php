<?php declare(strict_types=1); // strict typing

    function get_components($conn) {

        try {
            $query = "SELECT * FROM components";
            $stmt = $conn->prepare($query);

            $stmt->execute();

            $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $ret;
        } catch(PDOException $e) {
            return null;
        }
    }

    function replace_components($body) {
        global $conn;

        if($conn == null)
            $conn = get_database_connection();

        $components = get_components($conn);
        foreach($components as $component) {
            $pattern = $component['component_pattern'];
            $result = $component['component_result'];
            //echo "Testing Pattern $pattern against Body $body<br />";
            if(preg_match("/\{{2}[A-Za-z.\_]+\}{2}/", $pattern)) { // Match it to the form {{TEXT}}; Example: {{pages}}
                $pattern = preg_quote($pattern);
                //echo $pattern, '<br />';
                if(preg_match("/$pattern/", $body)) { // Does the $body contain $pattern?
                    //echo "Replacing $body with $result<br />";
                    $body = preg_replace("/$pattern/", $result, $body);
                }
            } /*else {
                echo "Testing to see if Pattern $pattern has arguments...<br />";
                if(preg_match("/\{{2}[A-Za-z0-9.\_]+(\s[A-Za-z0-9.\_]+\=\?)+\}{2}/", $pattern)) {
                    echo "Pattern $pattern has arguments...<br />";
                    if(preg_match_all('/\s([A-Za-z0-9.\_]+)=(".*?")/', $body, $matches, PREG_SET_ORDER)) {
                        echo "Replacing $body with $result<br />";
                        var_dump($matches);
                        $pattern = preg_quote($pattern);
                        echo "New Pattern after preg_quote: $pattern<br />";
                        $body = preg_replace("/$pattern/", $result, $body);
                        echo "New Body before updating arguments: $body<br />";
                        foreach($matches as $match) {
                            $key = $match[1];
                            $val = $match[2];
                            echo "Key:$key => Val:$val<br />";
                            $body = preg_replace("/\{$key\}/", $val, $body);
                        }
                        echo "New Body after updating arguments: $body<br />";
                    }
                }
            }*/
        }
        //echo 'Final returned body: ', $body, '<br /><br /><br />';
        return $body;
    }

    /*function replace_components($body) {
        global $components; // Make sure $components is accessible inside the function
        echo '<br /><br />';
        return preg_replace_callback(
            '/{{test2 text="(.*?)"}}/',
                                     function($matches) {
                                         $text = $matches[1]; // Extract the value of the text parameter
                                         return "<p>{$text}</p>"; // Replace the pattern with <p>{text}</p>
                                     },
                                     $body
        );
    }*/

    /*function replace_components($body) {
        echo '<br /><br />';
        return preg_replace_callback(
            '/{{(.*?)}}/',
            function($m) {
                global $components;
                echo "\$m[0] = {$m[0]}<br />";
                echo "\$m[1] = {$m[1]}<br />";
                foreach($components as $component) {
                    if(preg_match_all($component['component_pattern'], $m[0], $matches)) {
                        echo 'preg_matches<br /><br />';
                        var_dump($matches);
                    }
                }
                    return $m[1];
            }
            , $body);
    }*/

?>
