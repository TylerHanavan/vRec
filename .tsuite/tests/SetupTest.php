<?php

    function test_setup_page() {
        assertEquals(1, 1, '1 should be equal to 1');
    }

    function test_setup_page_2() {
        assertEquals(1, 1, '1 should be equal to 1');
        assertEquals(2, 2, '2 should be equal to 2');
    }

    function test_setup_page_3() {
        assertEquals(2, 2, '2 should be equal to 2');
    }

    function test_setup_page_4() {
        assertEquals(5, 5, '5 should be equal to 5');
        assertTrue(true, 'true should be true');
        assertFalse(true, 'true should be true');
    }

    function test_setup_page_5() {
        assertEquals(5, 5, '5 should be equal to 5');
        assertTrue(false, 'true should be true');
        assertFalse(false, 'true should be true');
    }

?>