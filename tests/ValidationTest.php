<?php

declare(strict_types=1);

class ValidationTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateModule(): void
    {
        $this->validateModule(__DIR__ . '/../WeatherWarning');
    }
}