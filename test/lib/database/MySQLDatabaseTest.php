<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

    final class MySQLDatabaseTest extends TestCase {
        
        public function test_get_create_table_query() : void {

            $database = $this->get_database();

            $table_name = 'test_table';
            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => null, 'length' => 255),
                'test_field2' => array('type' => ColumnTypes::INT, 'value' => null, 'length' => null),
            ));

            $this->assertEquals(
                'CREATE TABLE test_table (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, test_field1 VARCHAR(255), test_field2 INT)',
                $database->get_create_table_query($record),
                'The CREATE TABLE query should be correct with two fields'
            );
        }
        
        public function test_get_create_table_query_2() : void {

            $database = $this->get_database();

            $table_name = 'table1';
            $record = new Record($table_name, array(
                'fffield1' => array('type' => ColumnTypes::VARCHAR, 'value' => null, 'length' => 102),
            ));

            $this->assertEquals(
                'CREATE TABLE table1 (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, fffield1 VARCHAR(102))',
                $database->get_create_table_query($record),
                'The CREATE TABLE query should be correct with one field'
            );
        }

        public function test_get_column_quote_character() : void {

            $database = $this->get_database();
    
            $this->assertEquals(
                '\'',
                $database->get_column_quote_character(ColumnTypes::VARCHAR),
                'The quote character for VARCHAR should be a single quote'
            );
    
            $this->assertEquals(
                '\'',
                $database->get_column_quote_character(ColumnTypes::TEXT),
                'The quote character for TEXT should be a single quote'
            );
    
            $this->assertEquals(
                '\'',
                $database->get_column_quote_character(ColumnTypes::TIMESTAMP),
                'The quote character for TIMESTAMP should be a single quote'
            );
    
            $this->assertEquals(
                '\'',
                $database->get_column_quote_character(ColumnTypes::DATE),
                'The quote character for DATE should be a single quote'
            );
    
            $this->assertEquals(
                '',
                $database->get_column_quote_character(ColumnTypes::INT),
                'The quote character for INT should be an empty string'
            );
    
            $this->assertEquals(
                '',
                $database->get_column_quote_character(ColumnTypes::BOOLEAN),
                'The quote character for BOOLEAN should be an empty string'
            );

        }

        public function test_get_column_requires_length() : void {

            $database = $this->get_database();
    
            $this->assertEquals(
                true,
                $database->get_column_requires_length(ColumnTypes::VARCHAR),
                'VARCHAR should require a length'
            );

            $this->assertEquals(
                false,
                $database->get_column_requires_length(ColumnTypes::INT),
                'INT should not require a length'
            );

            $this->assertEquals(
                false,
                $database->get_column_requires_length(ColumnTypes::BOOLEAN),
                'BOOLEAN should not require a length'
            );

            $this->assertEquals(
                false,
                $database->get_column_requires_length(ColumnTypes::TEXT),
                'TEXT should not require a length'
            );

            $this->assertEquals(
                false,
                $database->get_column_requires_length(ColumnTypes::DATE),
                'DATE should not require a length'
            );

            $this->assertEquals(
                false,
                $database->get_column_requires_length(ColumnTypes::TIMESTAMP),
                'TIMESTAMP should not require a length'
            );

        }

        public function test_get_insert_record_query() : void {

            $database = $this->get_database();

            $table_name = 'test_table';
            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => 'test_value1', 'length' => 255),
                'test_field2' => array('type' => ColumnTypes::INT, 'value' => 123, 'length' => null),
            ));

            $this->assertEquals(
                "INSERT INTO test_table (test_field1, test_field2) VALUES ('test_value1', 123)",
                $database->get_insert_record_query($record),
                'The INSERT query should be correct with two fields'
            );
        }

        public function test_get_records_query() : void {

            $database = $this->get_database();
    
            $table_name = 'test_table';
            $record = new Record($table_name, null);
    
            /**
             * Test SELECT * query with no criteria
             */
            $this->assertEquals(
                'SELECT * FROM test_table',
                $database->get_records_query($record),
                'The SELECT * query should be correct with no criteria'
            );

            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => 'test_value1', 'length' => 255),
                'test_field2' => array('type' => ColumnTypes::INT, 'value' => 123, 'length' => null),
            ));

            /**
             * Test SELECT * query with multiple WHERE criteria - VARCHAR and INT
             */
            $this->assertEquals(
                'SELECT * FROM test_table WHERE test_field1 = \'test_value1\' AND test_field2 = 123',
                $database->get_records_query($record),
                'The SELECT * query should be correct with two WHERE criteria'
            );

            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => 'test_value1', 'length' => 255),
                'test_field2' => array('type' => ColumnTypes::INT, 'value' => 123, 'length' => null),
            ));

            $record->order_by('test_field2', 'DESC');

            /**
             * Test SELECT * query with multiple WHERE criteria - VARCHAR and INT and ORDER BY DESC
             */
            $this->assertEquals(
                'SELECT * FROM test_table WHERE test_field1 = \'test_value1\' AND test_field2 = 123 ORDER BY test_field2 DESC',
                $database->get_records_query($record),
                'The SELECT * query should be correct with two WHERE criteria and ORDER BY DESC'
            );

            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => 'test_value1', 'length' => 255),
                'test_field2' => array('type' => ColumnTypes::INT, 'value' => 123, 'length' => null),
            ));

            $record->order_by('test_field2', 'DESC')->order_by('test_field1', 'ASC');

            /**
             * Test SELECT * query with multiple WHERE criteria - VARCHAR and INT and ORDER BY DESC and ASC
             */
            $this->assertEquals(
                'SELECT * FROM test_table WHERE test_field1 = \'test_value1\' AND test_field2 = 123 ORDER BY test_field2 DESC, test_field1 ASC',
                $database->get_records_query($record),
                'The SELECT * query should be correct with two WHERE criteria and ORDER BY DESC and ASC'
            );

            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => 'test_value1', 'length' => 255),
                'test_field2' => array('type' => ColumnTypes::INT, 'value' => 123, 'length' => null),
            ));

            $record->order_by('test_field2', 'DESC');
            $record->limit(300);

            /**
             * Test SELECT * query with multiple WHERE criteria - VARCHAR and INT and ORDER BY DESC and LIMIT 300
             */
            $this->assertEquals(
                'SELECT * FROM test_table WHERE test_field1 = \'test_value1\' AND test_field2 = 123 ORDER BY test_field2 DESC LIMIT 300',
                $database->get_records_query($record),
                'The SELECT * query should be correct with two WHERE criteria and ORDER BY DESC and LIMIT 300'
            );

            $record = new Record($table_name, null);

            $record->limit(300);

            /**
             * Test SELECT * query with LIMIT 300
             */
            $this->assertEquals(
                'SELECT * FROM test_table LIMIT 300',
                $database->get_records_query($record),
                'The SELECT * query should be correct with LIMIT 300'
            );

        }

        public function test_get_records() : void {

            // Create a mock for the PDO class
            $pdoMock = $this->createMock(PDO::class);

            // Create a mock for the PDOStatement class
            $stmtMock = $this->createMock(PDOStatement::class);

            // Set up the expectation for the prepare method
            $pdoMock->expects($this->once())
                    ->method('prepare')
                    ->with($this->equalTo('SELECT * FROM test_table WHERE test_field1 = \'test_value1\' AND test_field2 = 123'))
                    ->willReturn($stmtMock);

            // Set up the expectation for the execute method
            $stmtMock->expects($this->once())
                    ->method('execute')
                    ->willReturn(true);

            // Set up the expectation for the fetch method
            $stmtMock->expects($this->once())
                    ->method('fetchAll')
                    ->with($this->equalTo(PDO::FETCH_ASSOC))
                    ->willReturn([[
                        'test_field1' => 'test_value1',
                        'test_field2' => 123
                    ]]);

            $database = $this->get_database();

            $database->connect($pdoMock);

            $table_name = 'test_table';
            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => 'test_value1', 'length' => 255),
                'test_field2' => array('type' => ColumnTypes::INT, 'value' => 123, 'length' => null),
            ));

            $records = $database->get_records($record);

            $this->assertEquals('test_value1', $records[0]->get_field('test_field1')['value'], 'The value of test_field1 should be test_value1');
            $this->assertEquals(123, $records[0]->get_field('test_field2')['value'], 'The value of test_field2 should be 123');

        }

        public function test_get_records_2() : void {

            // Create a mock for the PDO class
            $pdoMock = $this->createMock(PDO::class);

            // Create a mock for the PDOStatement class
            $stmtMock = $this->createMock(PDOStatement::class);

            // Set up the expectation for the prepare method
            $pdoMock->expects($this->once())
                    ->method('prepare')
                    ->with($this->equalTo('SELECT * FROM test_table WHERE test_field1 = \'test\''))
                    ->willReturn($stmtMock);

            // Set up the expectation for the execute method
            $stmtMock->expects($this->once())
                    ->method('execute')
                    ->willReturn(true);

            // Set up the expectation for the fetch method
            $stmtMock->expects($this->once())
                    ->method('fetchAll')
                    ->with($this->equalTo(PDO::FETCH_ASSOC))
                    ->willReturn([[
                        'test_field1' => 'test',
                        'test_field2' => 123
                    ], [
                        'test_field1' => 'test',
                        'test_field2' => 101
                    ], [
                        'test_field1' => 'test',
                        'test_field2' => 84
                    ]]);

            $database = $this->get_database();

            $database->connect($pdoMock);

            $table_name = 'test_table';
            $record = new Record($table_name, array(
                'test_field1' => array('type' => ColumnTypes::VARCHAR, 'value' => 'test', 'length' => 255)
            ));

            $records = $database->get_records($record);

            $this->assertEquals('test', $records[0]->get_field('test_field1')['value'], 'The value of test_field1 should be test');
            $this->assertEquals(123, $records[0]->get_field('test_field2')['value'], 'The value of test_field2 should be 123');
            
            $this->assertEquals('test', $records[1]->get_field('test_field1')['value'], 'The value of test_field1 should be test');
            $this->assertEquals(101, $records[1]->get_field('test_field2')['value'], 'The value of test_field2 should be 101');
            
            $this->assertEquals('test', $records[2]->get_field('test_field1')['value'], 'The value of test_field1 should be test');
            $this->assertEquals(84, $records[2]->get_field('test_field2')['value'], 'The value of test_field2 should be 84');
            

        }

        public function test_describe_record() : void {
                
            // Create a mock for the PDO class
            $pdoMock = $this->createMock(PDO::class);

            // Create a mock for the PDOStatement class
            $stmtMock = $this->createMock(PDOStatement::class);

            // Set up the expectation for the prepare method
            $pdoMock->expects($this->once())
                    ->method('prepare')
                    ->with($this->equalTo('DESCRIBE pages'))
                    ->willReturn($stmtMock);

            // Set up the expectation for the execute method
            $stmtMock->expects($this->once())
                    ->method('execute')
                    ->willReturn(true);

            // Set up the expectation for the fetch method
            $stmtMock->expects($this->once())
                    ->method('fetchAll')
                    ->with($this->equalTo(PDO::FETCH_ASSOC))
                    ->willReturn([[
                        'Field' => 'id',
                        'Type' => 'int',
                        'Null' => 'NO',
                        'Key' => 'PRI',
                        'Default' => null,
                        'Extra' => 'auto_increment'
                    ], [
                        'Field' => 'uri',
                        'Type' => 'varchar(400)',
                        'Null' => 'NO',
                        'Key' => '',
                        'Default' => null,
                        'Extra' => ''
                    ], [
                        'Field' => 'title',
                        'Type' => 'varchar(400)',
                        'Null' => 'NO',
                        'Key' => '',
                        'Default' => null,
                        'Extra' => ''
                    ], [
                        'Field' => 'description',
                        'Type' => 'varchar(400)',
                        'Null' => 'YES',
                        'Key' => '',
                        'Default' => null,
                        'Extra' => ''
                    ], [
                        'Field' => 'body',
                        'Type' => 'varchar(5000)',
                        'Null' => 'YES',
                        'Key' => '',
                        'Default' => null,
                        'Extra' => ''
                    ]]);

            $database = $this->get_database();

            $database->connect($pdoMock);

            $record = $database->describe_record('pages');
            
            $this->assertTrue($record->get_field('id') != null, 'The id field should not be null');
            $this->assertEquals(ColumnTypes::INT, $record->get_field('id')['type'], 'The type of the id field should be INT');
            $this->assertEquals(null, $record->get_field('id')['length'], 'The length of the id field should be null');

            $this->assertTrue($record->get_field('uri') != null, 'The uri field should not be null');
            $this->assertEquals(ColumnTypes::VARCHAR, $record->get_field('uri')['type'], 'The type of the uri field should be VARCHAR');
            $this->assertEquals(400, $record->get_field('uri')['length'], 'The length of the uri field should be 400');

            $this->assertTrue($record->get_field('title') != null, 'The title field should not be null');
            $this->assertEquals(ColumnTypes::VARCHAR, $record->get_field('title')['type'], 'The type of the title field should be VARCHAR');
            $this->assertEquals(400, $record->get_field('title')['length'], 'The length of the title field should be 400');

            $this->assertTrue($record->get_field('description') != null, 'The description field should not be null');
            $this->assertEquals(ColumnTypes::VARCHAR, $record->get_field('description')['type'], 'The type of the description field should be VARCHAR');
            $this->assertEquals(400, $record->get_field('description')['length'], 'The length of the description field should be 400');

            $this->assertTrue($record->get_field('body') != null, 'The body field should not be null');
            $this->assertEquals(ColumnTypes::VARCHAR, $record->get_field('body')['type'], 'The type of the body field should be VARCHAR');
            $this->assertEquals(5000, $record->get_field('body')['length'], 'The length of the body field should be 5000');

        }

        public function test_show_record_definitions() : void {

            // Create a mock for the PDO class
            $pdoMock = $this->createMock(PDO::class);

            // Create a mock for the PDOStatement class
            $stmtMock = $this->createMock(PDOStatement::class);

            // Set up the expectation for the prepare method
            $pdoMock->expects($this->once())
                    ->method('prepare')
                    ->with($this->equalTo('SHOW TABLES'))
                    ->willReturn($stmtMock);

            // Set up the expectation for the execute method
            $stmtMock->expects($this->once())
                    ->method('execute')
                    ->willReturn(true);

            // Set up the expectation for the fetch method
            $stmtMock->expects($this->once())
                    ->method('fetchAll')
                    ->with($this->equalTo(PDO::FETCH_ASSOC))
                    ->willReturn([[
                        'Tables_in_sedicms' => 'accounts',
                    ], [
                        'Tables_in_sedicms' => 'backup_schedules',
                    ],[
                        'Tables_in_sedicms' => 'backups_executed',
                    ],[
                        'Tables_in_sedicms' => 'components',
                    ],[
                        'Tables_in_sedicms' => 'page_attributes',
                    ],[
                        'Tables_in_sedicms' => 'pages',
                    ],]);

            $database = $this->get_database();

            $database->connect($pdoMock);

            $record_definitions = $database->show_record_definitions();

            $this->assertEquals('accounts', $record_definitions[0], 'The first record definition should be accounts');
            $this->assertEquals('backup_schedules', $record_definitions[1], 'The second record definition should be backup_schedules');
            $this->assertEquals('backups_executed', $record_definitions[2], 'The third record definition should be backups_executed');
            $this->assertEquals('components', $record_definitions[3], 'The fourth record definition should be components');
            $this->assertEquals('page_attributes', $record_definitions[4], 'The fifth record definition should be page_attributes');
            $this->assertEquals('pages', $record_definitions[5], 'The sixth record definition should be pages');

        }

        public function get_database() : MySQLDatabase {
            return new MySQLDatabase(array(
                'username  ' => 'root',
                'password' => 'password',
                'servername' => 'localhost',
                'dbname' => 'sedicms'
            ));
        }

    }

?>