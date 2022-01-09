<?php

macro('myMacro', function () {
    test(function () {
        $this->assertTrue(true);
    });
});

useMacro('myMacro');
