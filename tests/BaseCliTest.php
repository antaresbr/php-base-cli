<?php declare(strict_types=1);

use Antares\Support\Options;
use PHPUnit\Framework\TestCase;

final class BaseCliTest extends TestCase
{
    private function getPrototypeArray()
    {
        return [
            'project' => ['type' => 'string'],
            'firstOption' => ['type' => 'string', 'nullable' => true, 'default' => 'first'],
            'secondOption' => ['type' => 'string'],
            'trueOption' => ['type' => 'boolean'],
            'falseOption' => ['type' => 'boolean'],
            'fruits' => ['type' => 'string|array'],
            'object' => ['type' => Options::class, 'nullable' => false],
        ];
    }

    private function getOptionsArray()
    {
        return [
            'project' => 'options',
            'secondOption' => 'second',
            'trueOption' => true,
            'falseOption' => false,
            'fruits' => ['apple', 'banana', 'mango', 'avocado', 'grape', 'cherries'],
            'object' => new Options(),
        ];
    }

    private function getWorkOptions()
    {
        return Options::make($this->getOptionsArray(), $this->getPrototypeArray());
    }

    public function testOptions_make_method()
    {
        $this->assertInstanceOf(Options::class, $this->getWorkOptions());
    }
}
