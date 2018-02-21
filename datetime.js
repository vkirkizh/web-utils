/**
 * Выводит дату и время в человеко-понятном формате с учётом часового пояса
 *
 * Данная функция в качестве первого параметра принимает формат даты и времени
 * по аналогии с PHP-функцией date (см. http://php.net/manual/en/function.date.php),
 * а в качестве второго параметра - разницу во времени между текущим временем сервера
 * и временем некоторого события на сервере в секундах (например, временем отправки комментария),
 * и возвращает строку с отформатированным временем указанного события в человеко-понятной форме
 * с учётом часового пояса посетителя сайта.
 *
 * Пример (HTML-код, здесь xxxxx = текущее время сервера - время события на сервере):
 * <span class="fg-time" data-time="xxxxx" data-format="j F Y, H:i"></span>
 *
 * Пример (Javascript-код, используется jQuery):
 * $('.fg-time').each(function(){
 *      var time = $(this).data('time'),
 *           format = $(this).data('format');
 *      $(this).text(fgDate(format, time));
 * });
 *
 * @link https://kirkizh.ru/2014/09/datetime/
 * @author Valery Kirkizh <mail@figaroo.ru>
 * @version 1.0
 */

function fgDate(m, p){
	var C = [], s, y, k, z, f, d, e, a, u, b, w, t, l;
	C.tm = ["","января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря"];
	C.stm = ["","янв","фев","мар","апр","май","июн","июл","авг","сен","окт","ноя","дек"];
	C.td = ["","Понедельник","Вторник","Среда","Четверг","Пятница","Суббота","Воскресенье"];
	C.std = ["","Пн","Вт","Ср","Чт","Пт","Сб","Вс"];
	s = new Date();
	y = new Date((Math.floor(s.getTime() / 1000) - p) * 1000);
	k = y.getFullYear();
	m = " " + m + " ";
	m = m.replace(/Y/g, k);
	m = m.replace(/y/g, k.toString().substr(2, 2));
	if ( (k / 4) == Math.round(k / 4) ){
		m = m.replace(/L/g, "1");
	} else {
		m = m.replace(/L/g, "0");
	}
	z = y.getMonth() + 1;
	m = m.replace(/n/g, z);
	m = m.replace(/m/g, z < 10 ? "0" + z : z);
	switch (z) {
		case 1:
			f = 31;
			e = 0;
		break;
		case 2:
			f = ((k/4)==Math.round(k/4)?29:28);
			e = 31;
		break;
		case 3:
			f = 31;
			e = ((k/4)==Math.round(k/4)?60:59);
		break;
		case 4:
			f = 30;
			e = ((k/4)==Math.round(k/4)?91:90);
		break;
		case 5:
			f = 31;
			e = ((k/4)==Math.round(k/4)?121:120);
		break;
		case 6:
			f = 30;
			e = ((k/4)==Math.round(k/4)?152:151);
		break;
		case 7:
			f = 31;
			e = ((k/4)==Math.round(k/4)?182:181);
		break;
		case 8:
			f = 31;
			e = ((k/4)==Math.round(k/4)?213:212);
		break;
		case 9:
			f = 30;
			e = ((k/4)==Math.round(k/4)?234:233);
		break;
		case 10:
			f = 31;
			e = ((k/4)==Math.round(k/4)?264:263);
		break;
		case 11:
			f = 30;
			e = ((k/4)==Math.round(k/4)?295:294);
		break;
		case 12:
			f = 31;
			e = ((k/4)==Math.round(k/4)?335:334);
		break;
		default:
			return false;
		break;
	}
	m = m.replace(/F/g, C.tm[z]);
	m = m.replace(/M/g, C.stm[z]);
	m = m.replace(/t/g, f);
	a = y.getDate();
	m = m.replace(/j/g, a);
	m = m.replace(/d/g, a < 10 ? "0" + a : a);
	m = m.replace(/S/g, a == 3 || a == 23 ? "е" : "ое");
	d = e + a;
	m = m.replace(/z/g, d);
	m = m.replace(/W/g, Math.ceil(d / 7));
	u = y.getDay();
	if (u == 0) u = 7;
	m = m.replace(/w/g, u);
	m = m.replace(/l/g, C.td[u]);
	m = m.replace(/D/g, C.std[u]);
	b = y.getHours();
	w = b > 12 ? b - 12 : b;
	m = m.replace(/g/g, w);
	m = m.replace(/G/g, b);
	m = m.replace(/h/g, w < 10 ? "0" + w : w);
	m = m.replace(/H/g, b < 10 ? "0" + b : b);
	t = y.getMinutes();
	m = m.replace(/i/g, t < 10 ? "0" + t : t);
	l = y.getSeconds();
	m = m.replace(/s/g, l < 10 ? "0" + l : l);
	if (b > 12) {
		m = m.replace(/a/g, "pm");
		m = m.replace(/A/g, "PM");
	} else {
		m = m.replace(/a/g, "am");
		m = m.replace(/A/g, "AM");
	}
	return m.replace(/^\s*|\s*$/, '');
}
