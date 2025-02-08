<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class RecordTest extends TestCase
{
    public function test_get_record_name() : void {

        $record = new Record('test', null);
        $this->assertEquals(
            'test',
            $record->get_record_name()
        );
    }

    public function test_set_record_name() : void {

        $record = new Record('test', null);
        $record->set_record_name('test2');
        $this->assertEquals(
            'test2',
            $record->get_record_name()
        );
    }

    public function test_order_by() : void {

        $record = new Record('test', null);
        $record->order_by('field1', 'ASC');
        $this->assertEquals(
            [['field' => 'field1', 'direction' => 'ASC']],
            $record->get_order_by()
        );

        $record->order_by('field2', 'DESC');
        $this->assertEquals(
            [['field' => 'field1', 'direction' => 'ASC'], ['field' => 'field2', 'direction' => 'DESC']],
            $record->get_order_by()
        );
    }

    public function test_field() : void {
            
            $record = new Record('test', null);
            $record->field('field1', ColumnTypes::INT, 11);
            $this->assertEquals(
                array('field1' => array('type' => ColumnTypes::INT, 'value' => 11, 'length' => null)),
                $record->get_fields()
            );
    
            $record->field('field2', ColumnTypes::VARCHAR, 'VARCHAR TEST1', 255);
            $this->assertEquals(
                ['field1' => ['type' => ColumnTypes::INT, 'value' => 11, 'length' => null], 'field2' => ['type' => ColumnTypes::VARCHAR, 'value' => 'VARCHAR TEST1', 'length' => 255]],
                $record->get_fields()
            );
    }

    public function test_limit() : void {
            
            $record = new Record('test', null);
            $this->assertEquals(
                0,
                $record->get_limit()
            );

            $record->limit(10);
            $this->assertEquals(
                10,
                $record->get_limit()
            );
            
            $record->limit(200);
            $this->assertEquals(
                200,
                $record->get_limit()
            );

            $record->limit(0);
            $this->assertEquals(0, $record->get_limit());
        
            $record->limit(null);
            $this->assertEquals(0, $record->get_limit());
        
            $this->expectException(InvalidArgumentException::class);
            $record->limit(-5);
    }

    public function test_get_field_property() : void {
                
            $record = new Record('test', null);
            $record->field('field1', ColumnTypes::INT, 11);
            $this->assertEquals(
                ColumnTypes::INT,
                $record->get_field_property('field1', 'type')
            );
            $this->assertEquals(
                11,
                $record->get_field_property('field1', 'value')
            );
            $this->assertEquals(
                null,
                $record->get_field_property('field1', 'nullable')
            );
            $record->set_field_property('field1', 'nullable', true);
            $this->assertEquals(
                true,
                $record->get_field_property('field1', 'nullable')
            );

            $record->field('field2', ColumnTypes::VARCHAR, 'VARCHAR TEST1', 255);
            $this->assertEquals(
                ColumnTypes::VARCHAR,
                $record->get_field_property('field2', 'type')
            );
            $this->assertEquals(
                'VARCHAR TEST1',
                $record->get_field_property('field2', 'value')
            );
            $this->assertEquals(
                255,
                $record->get_field_property('field2', 'length')
            );
            $this->assertEquals(
                null,
                $record->get_field_property('field2', 'nullable')
            );
            $record->set_field_property('field2', 'nullable', true);
            $this->assertEquals(
                true,
                $record->get_field_property('field2', 'nullable')
            );
    }

    public function test_set_fields(): void {
        
        $record = new Record('test', null);

        $fields = [
            'field1' => ['type' => ColumnTypes::INT, 'value' => 11, 'length' => null],
            'field2' => ['type' => ColumnTypes::VARCHAR, 'value' => 'test', 'length' => 255]
        ];
        
        $record->set_fields($fields);
        $this->assertEquals($fields, $record->get_fields());
    }





}