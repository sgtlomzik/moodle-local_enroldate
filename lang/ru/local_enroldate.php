<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Russian strings for local_enroldate.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bulksection'] = 'Или введите список (email/логины)';
$string['enrolenddate'] = 'Окончание обучения';
$string['enrolenddate_help'] = 'Явно указанная дата окончания имеет приоритет над продолжительностью обучения выше. Оставьте оба поля пустыми, чтобы зачислить без даты окончания.';
$string['enrolstatus'] = 'Состояние';
$string['enrolusers'] = 'Зачислить на курс';
$string['forcegradehistory'] = 'Показывать завершённые зачисления в отчётах по оценкам';
$string['forcegradehistory_desc'] = 'Если зачислить пользователя на уже завершившийся период, он пропадает из отчётов по оценкам, поскольку по умолчанию в них показываются только активные зачисления. При включённой настройке плагин устанавливает общесистемный параметр «Показывать только активных участников» (grade_report_showonlyactiveenrol) в значение «Нет», чтобы такие пользователи оставались видимыми. Выключите настройку, чтобы сохранить собственное значение этого параметра ядра.';
$string['nomaskenrol'] = 'Метод «Ручное зачисление» не активен в этом курсе.';
$string['notfoundusers'] = 'Пользователи не найдены: {$a}';
$string['nousersselected'] = 'Пользователи для зачисления не выбраны.';
$string['pluginname'] = 'Зачисление с выбором даты';
$string['privacy:metadata'] = 'Плагин «Зачисление с выбором даты» не хранит персональных данных. Созданные им зачисления хранятся стандартным плагином ручного зачисления.';
$string['role'] = 'Назначить роль';
$string['search'] = 'Найти';
$string['searchquery'] = 'Имя, фамилия или email';
$string['searchsection'] = 'Поиск пользователя';
$string['searchtruncated'] = 'Показаны только первые {$a} совпадений. Уточните запрос, чтобы найти других пользователей.';
$string['selectfromresults'] = 'Выберите пользователей из списка:';
$string['settings'] = 'Настройки доступа';
$string['startdate'] = 'Дата начала обучения';
$string['successenrol'] = 'Пользователей успешно зачислено: {$a}';
$string['userlist'] = 'Список пользователей';
