<?php

/**
 * @param int $n
 * @param string $v1
 * @param string $v2
 * @param string $v5
 * @return string
 */
function sklonyalka($n, $v1, $v2, $v5)
{
    return $n % 100 < 10 || $n % 100 > 20
        ? ($n % 10 == 1
            ? $v1
            : ($n % 10 >= 2 && $n % 10 <= 4 ? $v2 : $v5))
        : $v5;
}
