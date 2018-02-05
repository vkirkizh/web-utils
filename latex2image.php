<?php

/**
 * Преобразовывает формулу LaTeX в ссылку на изображение
 *
 * Данная функция принимает на вход формулу в формате LaTeX и выдаёт ссылку
 * на графическое представление данной формулы.
 * Пример формулы: \Huge\frac{1}{\sigma\sqrt{2\pi}}\exp\left(-\frac{(x-\mu)^2}{2\sigma^2}\right)
 *
 * @link https://kirkizh.ru/2017/01/latex/
 * @author Valery Kirkizh <mail@figaroo.ru>
 *
 * @param string $text
 * @return string
 */
function latex2image($text)
{
    return 'http://chart.apis.google.com/chart?cht=tx&chl=' . urlencode($text);
}
