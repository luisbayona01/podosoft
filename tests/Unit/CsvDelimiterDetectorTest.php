<?php

namespace Tests\Unit;

use App\Import\CsvDelimiterDetector;
use Tests\TestCase;

class CsvDelimiterDetectorTest extends TestCase
{
    /** @test */
    public function detects_comma_delimiter()
    {
        $detector = new CsvDelimiterDetector();

        $this->assertSame(',', $detector->detect("nombre,apellido,telefono\nCarlos,Perez,3001234567\n"));
    }

    /** @test */
    public function detects_semicolon_delimiter()
    {
        $detector = new CsvDelimiterDetector();

        $this->assertSame(';', $detector->detect("nombre;apellido;telefono\nMaria;Gomez;3009876543\n"));
    }

    /** @test */
    public function ignores_delimiters_inside_quoted_cells()
    {
        $detector = new CsvDelimiterDetector();

        $this->assertSame(';', $detector->detect("nombre;apellido;direccion\nMaria;Gomez;\"Calle 10, Cra 5\"\n"));
    }

    /** @test */
    public function defaults_to_comma_when_no_delimiter_is_found()
    {
        $detector = new CsvDelimiterDetector();

        $this->assertSame(',', $detector->detect("nombre\nCarlos\n"));
    }
}