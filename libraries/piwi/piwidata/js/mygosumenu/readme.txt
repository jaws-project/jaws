----------------
! ABOUT
----------------

mygosuMenu is a set of simple DHTML menus
Link: http://gosu.pl/dhtml/mygosumenu.html

This software has been released under a BSD-style licence. This essentially means free for any use,
with the one condition that the author of this software be credited in appropriate documentation.

Let me know if you find any of the menus useful. If you have any suggestions feel free to email me.
My email: cagret[at]yahoo.com

You can subscribe to new releases here: 
http://freshmeat.net/projects/mygosumenu

----------------
! MENU TYPES
----------------

#1.0 DropDownMenu1 - 1 level drop down menu (horizontal, vertical).
#1.1 DropDownMenuX - Drop down menu with unlimited nesting (horizontal, vertical).
#1.2 TreeMenu
#1.3 ClickShowHideMenu
#1.4 XulMenu - windows like menu, unlimited nesting (horizontal, vertical)
#1.5 DynamicTree & DynamicTreeBuilder

----------------
! NOTES
----------------

Some of the menus include additional file to support IE 5.0:
<script type="text/javascript" src="../ie5.js"></script>
If you want to support IE 5.0 then you have to set a proper path to file ie5.js,
if you don't wanna support this version of browser just remove that line.

--

When no doctype is specified, Internet Explorer runs in "quirks" mode.
It is for backward compatibility, and many css bugs appear. If you want
to avoid them, use a doctype, not necessary xhtml.

for example:
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
or
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

--

IE Bug #1 (nested tables):

Example not working on IE:

<table><tr><td><table><tr><td>
    <table id="menu">....</table>
    <script>.. init menu ... </script>
</td></tr></table></td></tr></table>

Example that works on IE:

<table><tr><td><table><tr><td>
    <table id="menu">....</table>
</td></tr></table></td></tr></table>
<script>.. init menu ... </script>

Difference:
Looks like initializing the menu on IE must be done after closing some tables.

So if you are using nested tables, initialize the menu at the end of the page
or use window.onload event:

<script>
window.onload = function() {
    .. init menu ..
}
</script>

----------------
! CHANGELOG
----------------

*** 1.5.3 ***

  - #1.5 DynamicTree, added an example with folders as links, see /1.5/tests/foldersAsLinks.html
  - #1.3 ClickShowHideMenu, added an example that highlights active item, see /1.3/tests/highlightActive.html

*** 1.5.2 ***

  - #1.5 DynamicTreeBuilder, a bug that could affect you if you had more than 20 records in a tree when starting editing.

*** 1.5.1 ***

  - #1.1 menu works with selectboxes on IE6
  - some bug fixes in #1.5 menu
  - some updates in readmes

*** 1.5.0 ***

  - Added #1.5 menu, DynamicTree & DynamicTreeBuilder

*** 1.4.1 ***

  - Added support for IE 5.0 & IE 5.5 in #1.0 / #1.1 / #1.4 menus
  - [js] fixed a bug that prevented #1.3 menu working on Konqueror
  - [html] fixed a bug with positioning in example 2 of #1.0 and #1.1 menu that appeared
    on some versions of IE6 (6.0.2600 on XP, 6.0.3790 on Windows Server 2003)

*** 1.4.0 ***

  - Fixed a bug in #1.0 menu that appeared on IE 5.5
  - Added new #1.4 menu and 2 examples

*** 1.3.5 ***
  
  - Fixed a bug in menu #1.3 that appeared on newest Mozilla 1.7 & Firefox 0.9
  - updated /1.1/DropDownMenuX.txt

*** 1.3.4 ***

  - fixed a few bugs in #1.0 menu
  - #1.1 menu has been rewritten, now it supports vertical menus, a few bugs has been fixed,
    new features added.

*** 1.3.3 ***

  - #1.0 menu has been rewritten, a few bugs fixed, new features added, see /1.0/DropDownMenu1.txt for more info
  - done some cleaning: file names etc

*** 1.3.2 ***

  - Added another example of #1.1 menu
    See /1.1/menu2.html