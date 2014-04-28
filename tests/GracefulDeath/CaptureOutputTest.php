<?php

class CaptureOutputTest extends GracefulDeathBaseTest
{
    public function testChildStandardOutputIsEchoedOnFatherStandardOutput()
    {
        $this->runFixture('printOutputOnStdout.php --what OUTPUT', function($stdout, $stderr) {
            $this->assertEquals('OUTPUT', $stdout);
            $this->assertEmpty($stderr);
        });
    }

    public function testChildStandardErrorIsEchoedOnFatherStandardError()
    {
        $this->runFixture('printOutputOnStderr.php --what OUTPUT', function($stdout, $stderr) {
            $this->assertEmpty($stdout);
            $this->assertEquals('OUTPUT', $stderr);
        });
    }

    public function testCouldAvoidToPrintChildOutputWithOption()
    {
        $this->runFixture('doNotEchoOutput.php', function($stdout, $stderr) {
            $this->assertEmpty($stdout);
            $this->assertEmpty($stderr);
        });
    }

    public function testChildStandardOutputIsCapturedAndGivenToRetryPolicyForEvaluation()
    {
        GracefulDeath::around(function() {
            file_put_contents('php://stdout', 'OUTPUT');
            $this->raiseFatalError();
        })
        ->reanimationPolicy(function($status, $lifeCounter, $stdout, $stderr) {
            $this->assertEquals('OUTPUT', $stdout);
            return false;
        })
        ->doNotEchoOutput()
        ->run();
    }

    public function testChildStandardErrorIsCapturedAndGivenToRetryPolicyForEvaluation()
    {
        GracefulDeath::around(function() {
            $this->raiseAndReportFatalError();
        })
        ->reanimationPolicy(function($status, $lifeCounter, $stdout, $stderr) {
            $this->assertStringStartsWith('PHP Fatal error:', trim($stderr));
            return false;
        })
        ->doNotEchoOutput()
        ->run();
    }
}
