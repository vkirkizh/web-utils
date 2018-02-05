<?php

/**
 * Выполняет склонение слов по числам
 *
 * Данная функция выбирает правильное числовое склонение слова из трёх возможных.
 * Например, код echo $n . ' ' . sklonyalka($n, 'комментарий', 'комментария', 'комментариев');
 * для $n = 21 выведет '21 комментарий', для $n = 142 выведет '142 комментария',
 * а для для $n = 18535 данный код выведет '18535 комментариев'.
 *
 * @link https://kirkizh.ru/2017/07/sklonyalka/
 * @author Valery Kirkizh <mail@figaroo.ru>
 *
 * @param int $n
 * @param string $v1
 * @param string $v2
 * @param string $v5
 * @return string
 */
function sklonyalka($n, $v1, $v2, $v5)
{
    return $n % 100 < 10 || $n % 100 > 20 ? ($n % 10 == 1 ? $v1 : ($n % 10 >= 2 && $n % 10 <= 4 ? $v2 : $v5)) : $v5;
}
