// ** I18N

// Calendar RU language
// Translation: Sly Golovanov, http://golovanov.net, <sly@golovanov.net>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Ð²Ð¾ÑÐºÑÐµÑÐµÐ½ÑÐµ",
 "Ð¿Ð¾Ð½ÐµÐ´ÐµÐ»ÑÐ½Ð¸Ðº",
 "Ð²ÑÐ¾ÑÐ½Ð¸Ðº",
 "ÑÑÐµÐ´Ð°",
 "ÑÐµÑÐ²ÐµÑÐ³",
 "Ð¿ÑÑÐ½Ð¸ÑÐ°",
 "ÑÑÐ±Ð±Ð¾ÑÐ°",
 "Ð²Ð¾ÑÐºÑÐµÑÐµÐ½ÑÐµ");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Ð²ÑÐº",
 "Ð¿Ð¾Ð½",
 "Ð²ÑÑ",
 "ÑÑÐ´",
 "ÑÐµÑ",
 "Ð¿ÑÑ",
 "ÑÑÐ±",
 "Ð²ÑÐº");

// full month names
Calendar._MN = new Array
("ÑÐ½Ð²Ð°ÑÑ",
 "ÑÐµÐ²ÑÐ°Ð»Ñ",
 "Ð¼Ð°ÑÑ",
 "Ð°Ð¿ÑÐµÐ»Ñ",
 "Ð¼Ð°Ð¹",
 "Ð¸ÑÐ½Ñ",
 "Ð¸ÑÐ»Ñ",
 "Ð°Ð²Ð³ÑÑÑ",
 "ÑÐµÐ½ÑÑÐ±ÑÑ",
 "Ð¾ÐºÑÑÐ±ÑÑ",
 "Ð½Ð¾ÑÐ±ÑÑ",
 "Ð´ÐµÐºÐ°Ð±ÑÑ");

// short month names
Calendar._SMN = new Array
("ÑÐ½Ð²",
 "ÑÐµÐ²",
 "Ð¼Ð°Ñ",
 "Ð°Ð¿Ñ",
 "Ð¼Ð°Ð¹",
 "Ð¸ÑÐ½",
 "Ð¸ÑÐ»",
 "Ð°Ð²Ð³",
 "ÑÐµÐ½",
 "Ð¾ÐºÑ",
 "Ð½Ð¾Ñ",
 "Ð´ÐµÐº");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Ð ÐºÐ°Ð»ÐµÐ½Ð´Ð°ÑÐµ...";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"ÐÐ°Ðº Ð²ÑÐ±ÑÐ°ÑÑ Ð´Ð°ÑÑ:\n" +
"- ÐÑÐ¸ Ð¿Ð¾Ð¼Ð¾ÑÐ¸ ÐºÐ½Ð¾Ð¿Ð¾Ðº \xab, \xbb Ð¼Ð¾Ð¶Ð½Ð¾ Ð²ÑÐ±ÑÐ°ÑÑ Ð³Ð¾Ð´\n" +
"- ÐÑÐ¸ Ð¿Ð¾Ð¼Ð¾ÑÐ¸ ÐºÐ½Ð¾Ð¿Ð¾Ðº " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " Ð¼Ð¾Ð¶Ð½Ð¾ Ð²ÑÐ±ÑÐ°ÑÑ Ð¼ÐµÑÑÑ\n" +
"- ÐÐ¾Ð´ÐµÑÐ¶Ð¸ÑÐµ ÑÑÐ¸ ÐºÐ½Ð¾Ð¿ÐºÐ¸ Ð½Ð°Ð¶Ð°ÑÑÐ¼Ð¸, ÑÑÐ¾Ð±Ñ Ð¿Ð¾ÑÐ²Ð¸Ð»Ð¾ÑÑ Ð¼ÐµÐ½Ñ Ð±ÑÑÑÑÐ¾Ð³Ð¾ Ð²ÑÐ±Ð¾ÑÐ°.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"ÐÐ°Ðº Ð²ÑÐ±ÑÐ°ÑÑ Ð²ÑÐµÐ¼Ñ:\n" +
"- ÐÑÐ¸ ÐºÐ»Ð¸ÐºÐµ Ð½Ð° ÑÐ°ÑÐ°Ñ Ð¸Ð»Ð¸ Ð¼Ð¸Ð½ÑÑÐ°Ñ Ð¾Ð½Ð¸ ÑÐ²ÐµÐ»Ð¸ÑÐ¸Ð²Ð°ÑÑÑÑ\n" +
"- Ð¿ÑÐ¸ ÐºÐ»Ð¸ÐºÐµ Ñ Ð½Ð°Ð¶Ð°ÑÐ¾Ð¹ ÐºÐ»Ð°Ð²Ð¸ÑÐµÐ¹ Shift Ð¾Ð½Ð¸ ÑÐ¼ÐµÐ½ÑÑÐ°ÑÑÑÑ\n" +
"- ÐµÑÐ»Ð¸ Ð½Ð°Ð¶Ð°ÑÑ Ð¸ Ð´Ð²Ð¸Ð³Ð°ÑÑ Ð¼ÑÑÐºÐ¾Ð¹ Ð²Ð»ÐµÐ²Ð¾/Ð²Ð¿ÑÐ°Ð²Ð¾, Ð¾Ð½Ð¸ Ð±ÑÐ´ÑÑ Ð¼ÐµÐ½ÑÑÑÑÑ Ð±ÑÑÑÑÐµÐµ.";

Calendar._TT["PREV_YEAR"] = "ÐÐ° Ð³Ð¾Ð´ Ð½Ð°Ð·Ð°Ð´ (ÑÐ´ÐµÑÐ¶Ð¸Ð²Ð°ÑÑ Ð´Ð»Ñ Ð¼ÐµÐ½Ñ)";
Calendar._TT["PREV_MONTH"] = "ÐÐ° Ð¼ÐµÑÑÑ Ð½Ð°Ð·Ð°Ð´ (ÑÐ´ÐµÑÐ¶Ð¸Ð²Ð°ÑÑ Ð´Ð»Ñ Ð¼ÐµÐ½Ñ)";
Calendar._TT["GO_TODAY"] = "Ð¡ÐµÐ³Ð¾Ð´Ð½Ñ";
Calendar._TT["NEXT_MONTH"] = "ÐÐ° Ð¼ÐµÑÑÑ Ð²Ð¿ÐµÑÐµÐ´ (ÑÐ´ÐµÑÐ¶Ð¸Ð²Ð°ÑÑ Ð´Ð»Ñ Ð¼ÐµÐ½Ñ)";
Calendar._TT["NEXT_YEAR"] = "ÐÐ° Ð³Ð¾Ð´ Ð²Ð¿ÐµÑÐµÐ´ (ÑÐ´ÐµÑÐ¶Ð¸Ð²Ð°ÑÑ Ð´Ð»Ñ Ð¼ÐµÐ½Ñ)";
Calendar._TT["SEL_DATE"] = "ÐÑÐ±ÐµÑÐ¸ÑÐµ Ð´Ð°ÑÑ";
Calendar._TT["DRAG_TO_MOVE"] = "ÐÐµÑÐµÑÐ°ÑÐºÐ¸Ð²Ð°Ð¹ÑÐµ Ð¼ÑÑÐºÐ¾Ð¹";
Calendar._TT["PART_TODAY"] = " (ÑÐµÐ³Ð¾Ð´Ð½Ñ)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "ÐÐµÑÐ²ÑÐ¹ Ð´ÐµÐ½Ñ Ð½ÐµÐ´ÐµÐ»Ð¸ Ð±ÑÐ´ÐµÑ %s";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "ÐÐ°ÐºÑÑÑÑ";
Calendar._TT["TODAY"] = "Ð¡ÐµÐ³Ð¾Ð´Ð½Ñ";
Calendar._TT["TIME_PART"] = "(Shift-)ÐºÐ»Ð¸Ðº Ð¸Ð»Ð¸ Ð½Ð°Ð¶Ð°ÑÑ Ð¸ Ð´Ð²Ð¸Ð³Ð°ÑÑ";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%e %b, %a";

Calendar._TT["WK"] = "Ð½ÐµÐ´";
Calendar._TT["TIME"] = "ÐÑÐµÐ¼Ñ:";
