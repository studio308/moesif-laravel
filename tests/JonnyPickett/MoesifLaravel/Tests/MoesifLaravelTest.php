<?php namespace JonnyPickett\MoesifLaravel\Tests;

use JonnyPickett\MoesifLaravel\Tests\MoesifLaravelTestCase;

class MoesifLaravelTest extends MoesifLaravelTestCase
{
    /** @test */
    public function foo()
    {
        $res = $this->call('GET', 'test');
    }
}
