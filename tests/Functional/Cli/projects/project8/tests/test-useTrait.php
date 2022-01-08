<?php

trait MyTrait
{
    public function test()
    {
        $this->assertTrue(true);
    }
}

useTrait(MyTrait::class);
