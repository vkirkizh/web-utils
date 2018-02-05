<?php

/**
 * Проверяет корректность телефонного номера из России или Казахстана
 *
 * Данная функция принимает на вход мобильный или стационарный телефонный номер
 * России или Казахстана в произвольном формате (например, +79999999999, 89999999999,
 * 999 999 99 99, +7 (999) 999-99-99 и т.д.) и, в случае, если это действительно
 * корректный телефонный номер, возвращает его в едином формате +7xxxxxxxxxx, или же,
 * если переданный текст телефонным номером не является, возвращает false.
 *
 * @link https://kirkizh.ru/2017/07/phone-validate/
 * @author Valery Kirkizh <mail@figaroo.ru>
 *
 * @param string $tel
 * @return string|bool
 */
function validate_russian_phone_number($tel)
{
    $tel = trim((string)$tel);
    if (!$tel) return false;
    $tel = preg_replace('#[^0-9+]+#uis', '', $tel);
    if (!preg_match('#^(?:\\+?7|8|)(.*?)$#uis', $tel, $m)) return false;
    $tel = '+7' . preg_replace('#[^0-9]+#uis', '', $m[1]);
    if (!preg_match('#^\\+7[0-9]{10}$#uis', $tel, $m)) return false;
    return $tel;
}
