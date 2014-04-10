<?php

class GracefulDeathBuilder
{
    private $main;
    private $afterViolentDeath;
    private $afterNaturalDeath;
    private $reanimationPolicy;

    public function __construct($main)
    {
        $this->main = $main;
        $this->afterViolentDeath = function($status) {};
        $this->afterNaturalDeath = function($status) {};
        $this->reanimationPolicy = GracefulDeath::DO_NOT_REANIMATE;
    }

    public function afterViolentDeath($whatToDo)
    {
        $this->afterViolentDeath = $this->toClosure($whatToDo);
        return $this;
    }

    public function afterNaturalDeath($whatToDo)
    {
        $this->afterNaturalDeath = $this->toClosure($whatToDo);
        return $this;
    }

    public function afterDeath($whatToDo)
    {
        $this->afterViolentDeath = $this->toClosure($whatToDo);
        $this->afterNaturalDeath = $this->toClosure($whatToDo);
        return $this;
    }

    public function reanimationPolicy($policy)
    {
        $this->reanimationPolicy = $policy;
        return $this;
    }

    public function run()
    {
        return (
            new GracefulDeath(
                $this->main,
                $this->afterNaturalDeath,
                $this->afterViolentDeath,
                $this->reanimationPolicy
            )
        )->run();
    }

    private function toClosure($whatToDo)
    {
        if (is_integer($whatToDo) || is_string($whatToDo)) {
            return function() use($whatToDo) { exit($whatToDo); };
        }
        if (is_array($whatToDo) && count($whatToDo) === 2) {
            list($status, $message) = $whatToDo;
            return function() use($status, $message) {
                echo $message;
                exit($status);
            };
        }
        if (is_callable($whatToDo)) {
            return $whatToDo;
        }
    }
}