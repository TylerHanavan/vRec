<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class HookTest extends TestCase
{
    public function test_get_function() : void {

        $hook = new Hook('test1', array());
        $this->assertEquals(
            'test1',
            $hook->get_function()
        );

        $hook = new Hook('test2', array('layer' => 'page-load-pre', 'logged_in' => true));
        $this->assertEquals(
            'test2',
            $hook->get_function()
        );
    }

    public function test_get_conditions() : void {

        $arr1 = array('test' => 'test');
        $hook = new Hook('test1', $arr1);
        $this->assertEquals(
            $arr1,
            $hook->get_conditions()
        );
    }

    public function test_can_call_hook() : void {

        $hook = new Hook('test1', array('layer' => 'page-load-pre', 'logged_in' => true));
        $this->assertEquals(
            true,
            $hook->can_call_hook(array('layer' => 'page-load-pre', 'logged_in' => true)),
            'The hook should be callable when hook conditions match the current conditions perfectly'
        );
        $this->assertEquals(
            false,
            $hook->can_call_hook(array('layer' => 'page-load-pre', 'logged_in' => false)),
            'The hook should not be callable when hook conditions do not match the current conditions perfectly'
        );
        $this->assertEquals(
            false,
            $hook->can_call_hook(array('layer' => 'page-load-post', 'logged_in' => true)),
            'The hook should not be callable when hook conditions do not match the current conditions perfectly'
        );
        $this->assertEquals(
            false,
            $hook->can_call_hook(array('layer' => 'page-load-pre')),
            'The hook should not be callable when there is a missing current condition'
        );

        $hook = new Hook('test1', array('layer' => 'page-load-pre', 'logged_in' => array(true, false)));
        $this->assertEquals(
            false,
            $hook->can_call_hook(array('layer' => 'page-load-pre')),
            'The hook should not be callable when there is a missing current condition'
        );
        $this->assertEquals(
            true,
            $hook->can_call_hook(array('layer' => 'page-load-pre', 'logged_in' => true)),
            'The hook should be callable when hook conditions match the current conditions perfectly'
        );
        $this->assertEquals(
            true,
            $hook->can_call_hook(array('layer' => 'page-load-pre', 'logged_in' => false)),
            'The hook should be callable when hook conditions match the current conditions perfectly'
        );

        $hook = new Hook('test1', array('layer' => 'plugin_load_post'));
        $this->assertEquals(
            true,
            $hook->can_call_hook(array('layer' => 'plugin_load_post', 'url' => '/test/1/2')),
            'The hook should should be callable when the current conditions are a subset of the hook conditions'
        );

    }

}