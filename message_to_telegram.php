<?php

/**
 * Отправляет сообщение пользователю или группе в Телеграм
 *
 * Данная функция позволяет отправить сообщение любому пользователю
 * или любой группе (чату) в мессенджере Telegram. Для того, чтобы отправить
 * сообщение пользователю, сначала он должен сам написать что-нибудь боту
 * (достаточно сделать это один раз). В качестве параметров необходимо
 * передать текст сообщения, токен бота и ID пользователя или чата.
 *
 * @link https://kirkizh.ru/2018/04/telegram-php/
 * @author Valery Kirkizh <mail@figaroo.ru>
 *
 * @param string $text
 * @param string $token
 * @param string $chat_id
 */
function message_to_telegram($text, $token, $chat_id) {
	$ch = curl_init();
	curl_setopt_array(
		$ch,
		array(
			CURLOPT_URL => 'https://api.telegram.org/bot' . $token . '/sendMessage',
			CURLOPT_POST => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_POSTFIELDS => array(
				'chat_id' => $chat_id,
				'text' => $text,
			),
		)
	);
	curl_exec($ch);
}
