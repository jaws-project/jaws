/**
 * OnBeforeUnload
 */
var navigate_away_message = '';
var unsavedChanges = false;
window.onbeforeunload = askNavigateAway;
function askNavigateAway() {
    if (unsavedChanges) {
        return navigate_away_message;
    } else {
        return;
    }
}

/**
 * Check if page is loaded
 */
function cpload()
{
    if (document.getElementById) { // DOM3 = IE5, NS6
        document.getElementById('hidepage').style.visibility = 'hidden';
    }
    else {
        if (document.layers) { // Netscape 4
            document.hidepage.visibility = 'hidden';
        }
        else { // IE 4
            document.all.hidepage.style.visibility = 'hidden';
        }
    }
}
