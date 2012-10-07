// ** I18N
// Calendar PL language
// Author: Artur Filipiak, <imagen@poczta.fm>
// January, 2004
// Encoding: UTF-8
Calendar._DN = new Array
("Niedziela", "PoniedziaÅek", "Wtorek", "Åroda", "Czwartek", "PiÄtek", "Sobota", "Niedziela");

Calendar._SDN = new Array
("N", "Pn", "Wt", "År", "Cz", "Pt", "So", "N");

Calendar._MN = new Array
("StyczeÅ", "Luty", "Marzec", "KwiecieÅ", "Maj", "Czerwiec", "Lipiec", "SierpieÅ", "WrzesieÅ", "PaÅºdziernik", "Listopad", "GrudzieÅ");

Calendar._SMN = new Array
("Sty", "Lut", "Mar", "Kwi", "Maj", "Cze", "Lip", "Sie", "Wrz", "PaÅº", "Lis", "Gru");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "O kalendarzu";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"WybÃ³r daty:\n" +
"- aby wybraÄ rok uÅ¼yj przyciskÃ³w \xab, \xbb\n" +
"- aby wybraÄ miesiÄc uÅ¼yj przyciskÃ³w " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + "\n" +
"- aby przyspieszyÄ wybÃ³r przytrzymaj wciÅniÄty przycisk myszy nad ww. przyciskami.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"WybÃ³r czasu:\n" +
"- aby zwiÄkszyÄ wartoÅÄ kliknij na dowolnym elemencie selekcji czasu\n" +
"- aby zmniejszyÄ wartoÅÄ uÅ¼yj dodatkowo klawisza Shift\n" +
"- moÅ¼esz rÃ³wnieÅ¼ poruszaÄ myszkÄ w lewo i prawo wraz z wciÅniÄtym lewym klawiszem.";

Calendar._TT["PREV_YEAR"] = "Poprz. rok (przytrzymaj dla menu)";
Calendar._TT["PREV_MONTH"] = "Poprz. miesiÄc (przytrzymaj dla menu)";
Calendar._TT["GO_TODAY"] = "PokaÅ¼ dziÅ";
Calendar._TT["NEXT_MONTH"] = "Nast. miesiÄc (przytrzymaj dla menu)";
Calendar._TT["NEXT_YEAR"] = "Nast. rok (przytrzymaj dla menu)";
Calendar._TT["SEL_DATE"] = "Wybierz datÄ";
Calendar._TT["DRAG_TO_MOVE"] = "PrzesuÅ okienko";
Calendar._TT["PART_TODAY"] = " (dziÅ)";
Calendar._TT["MON_FIRST"] = "PokaÅ¼ PoniedziaÅek jako pierwszy";
Calendar._TT["SUN_FIRST"] = "PokaÅ¼ NiedzielÄ jako pierwszÄ";
Calendar._TT["CLOSE"] = "Zamknij";
Calendar._TT["TODAY"] = "DziÅ";
Calendar._TT["TIME_PART"] = "(Shift-)klik | drag, aby zmieniÄ wartoÅÄ";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y.%m.%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "wk";
