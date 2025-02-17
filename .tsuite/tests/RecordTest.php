<?php

    function test_account_1() {
        assertEquals(1, 1, '1 should be equal to 1');
    }

    function test_account_2() {
        assertEquals(1, 1, '1 should be equal to 1');
        assertEquals(2, 2, '2 should be equal to 2');
    }

    function test_account_3() {
        assertEquals(2, 2, '2 should be equal to 2');
    }

    function test_account_4() {
        assertEquals(5, 5, '5 should be equal to 5');
        assertTrue(true, 'true should be true');
        assertFalse(false, 'false should be false');
    }

    function test_account_5() {
        assertEquals(5, 5, '5 should be equal to 5');
        assertTrue(true, 'true should be true');
        assertFalse(false, 'false should be false');
        assertFalse(0, '0 should be false');
    }

?>