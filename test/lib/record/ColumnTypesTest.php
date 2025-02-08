<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ColumnTypesTest extends TestCase
{
    public function test_constants(): void
    {
        $this->assertSame(0, ColumnTypes::INT);
        $this->assertSame(1, ColumnTypes::VARCHAR);
        $this->assertSame(2, ColumnTypes::BOOLEAN);
        $this->assertSame(3, ColumnTypes::DATE);
        $this->assertSame(4, ColumnTypes::TIMESTAMP);
        $this->assertSame(5, ColumnTypes::TEXT);
    }

    public function test_enums_mapping(): void
    {
        $expected = [
            ColumnTypes::INT => 'INT',
            ColumnTypes::VARCHAR => 'VARCHAR',
            ColumnTypes::BOOLEAN => 'BOOLEAN',
            ColumnTypes::DATE => 'DATE',
            ColumnTypes::TIMESTAMP => 'TIMESTAMP',
            ColumnTypes::TEXT => 'TEXT',
        ];
        $this->assertSame($expected, ColumnTypes::$enums);
    }

    public function test_translate_string_valid(): void
    {
        $this->assertSame(ColumnTypes::INT, ColumnTypes::translate_string('INT'));
        $this->assertSame(ColumnTypes::VARCHAR, ColumnTypes::translate_string('VARCHAR'));
        $this->assertSame(ColumnTypes::BOOLEAN, ColumnTypes::translate_string('BOOLEAN'));
        $this->assertSame(ColumnTypes::DATE, ColumnTypes::translate_string('DATE'));
        $this->assertSame(ColumnTypes::TIMESTAMP, ColumnTypes::translate_string('TIMESTAMP'));
        $this->assertSame(ColumnTypes::TEXT, ColumnTypes::translate_string('TEXT'));
    }

    public function test_translate_string_invalid(): void
    {
        $this->assertNull(ColumnTypes::translate_string('NON_EXISTENT'));
        $this->assertNull(ColumnTypes::translate_string(''));
        $this->assertNull(ColumnTypes::translate_string(null));
    }

    public function test_translate_id_valid(): void
    {
        $this->assertSame('INT', ColumnTypes::translate_id(ColumnTypes::INT));
        $this->assertSame('VARCHAR', ColumnTypes::translate_id(ColumnTypes::VARCHAR));
        $this->assertSame('BOOLEAN', ColumnTypes::translate_id(ColumnTypes::BOOLEAN));
        $this->assertSame('DATE', ColumnTypes::translate_id(ColumnTypes::DATE));
        $this->assertSame('TIMESTAMP', ColumnTypes::translate_id(ColumnTypes::TIMESTAMP));
        $this->assertSame('TEXT', ColumnTypes::translate_id(ColumnTypes::TEXT));
    }

    public function test_translate_id_invalid(): void
    {
        $this->assertNull(ColumnTypes::translate_id(999));
        $this->assertNull(ColumnTypes::translate_id(-1));
        $this->assertNull(ColumnTypes::translate_id(null));
    }
}
