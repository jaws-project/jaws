/**
 * Weather Javascript actions
 *
 * @category   Ajax
 * @package    Weather
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var WeatherCallback = {
    deleteregion: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('weather_datagrid').deleteItem();          
            getDG();
            stopAction();
        }
        showResponse(response);
    },
    
    insertregion: function(response) {
        if (response[0]['css'] == 'notice-message') {
            _('weather_datagrid').addItem();
            _('weather_datagrid').setCurrentPage(0);
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    updateregion: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    updateproperties: function(response) {
        showResponse(response);
    }

}

/**
 * Initiates Weather JS
 */
function initWeather()
{
    stopAction();
    _('latitude').value = '51.30';
    _('longitude').value = '00.08';
    initDataGrid('weather_datagrid', WeatherAjax);
    setGoogleMapImage();
}

/**
 * Clears the form
 */
function stopAction() 
{
    if (selectedRow) {
        _('weather_datagrid').unselectRow(selectedRow);
        selectedRow = null;
    }
    _('id').value = '';
    _('title').value = '';
    _('fast_url').value = '';
    _('published').value = 1;
    _('title').focus();
}

/**
 * Edits a region
 */
function editRegion(rowElement, id)
{
    if (selectedRow) {
        _('weather_datagrid').unselectRow(selectedRow);
    }
    _('weather_datagrid').selectRow(rowElement);
    selectedRow = rowElement;

    var geoPos = WeatherAjax.callSync('getregion', id);
    _('id').value        = geoPos['id'];
    _('title').value     = geoPos['title'].defilter();
    _('fast_url').value  = geoPos['fast_url'];
    _('latitude').value  = geoPos['latitude'];
    _('longitude').value = geoPos['longitude'];
    _('published').value = geoPos['published']? 1 : 0;
    setGoogleMapImage();
}

/**
 * Adds/Updates the region
 */
function updateRegion()
{
    if (_('title').value.blank() ||
        _('latitude').value.blank() ||
        _('longitude').value.blank())
    {
        alert(incompleteFields);
        return;
    }

    if (_('id').value == 0) {
        WeatherAjax.callAsync(
                        'insertregion',
                        _('title').value,
                        _('fast_url').value,
                        _('latitude').value,
                        _('longitude').value,
                        _('published').value);
    } else {
        WeatherAjax.callAsync(
                        'updateregion',
                        _('id').value,
                        _('title').value,
                        _('fast_url').value,
                        _('latitude').value,
                        _('longitude').value,
                        _('published').value);
    }
}

/**
 * Deletes the region
 */
function deleteRegion(rowElement, id)
{
    stopAction();
    _('weather_datagrid').selectRow(rowElement);
    if (confirm(confirmDelete)) {
        WeatherAjax.callAsync('deleteregion', id);
    } else {
        _('weather_datagrid').unselectRow(rowElement);
        selectedRow = null;
    }
}

/**
 * Updates the properties
 */
function updateProperties()
{
    WeatherAjax.callAsync('updateproperties',
                          _('unit').value,
                          _('update_period').value,
                          _('date_format').value,
                          _('api_key').value);
}

/**
 *  Calculates position of the element
 */
function findElementPosition(element)
{
    if (typeof(element.offsetParent ) != "undefined") {
        for (var posX = 0, posY = 0; element; element = element.offsetParent) {
            posX += element.offsetLeft;
            posY += element.offsetTop;
        }

        return [posX, posY];
    }

    return [element.x, element.y];
}

/**
 * Gets the geo position
 */
function GetGeoPosition(mx, my, iw, ih, clat, clng)
{
    var lng = iw/ZoomPixelsPerLonDegree[ZoomLevel];
    lng = clng + (mx*lng/iw - 0.5*lng);

    var cosRadLat = Math.cos(clat*Math.PI/180);
    var lat = ih * cosRadLat/ZoomPixelsPerLonDegree[ZoomLevel];
    lat =  clat + (0.5*lat - my*lat/ih);

    return [Math.round(lat*100)/100, Math.round(lng*100)/100];
}

/**
 *  Calculates coordinates of the clicked point and returns the appropriate map
 */
function getGoogleMap(ev, element)
{
    var geoPos,
        posX = 0,
        posY = 0,
        imgPos = findElementPosition(element);
    
    if (!ev) {
        var ev = window.event;
    }
    if (ev.pageX || ev.pageY) {
        posX = ev.pageX;
        posY = ev.pageY;
    } else if (ev.clientX || ev.clientY) {
        posX = ev.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        posY = ev.clientY + document.body.scrollTop  + document.documentElement.scrollTop;
    }
    posX = posX - imgPos[0];
    posY = posY - imgPos[1];

    //---------------------
    geoPos = GetGeoPosition(posX,
                            posY,
                            element.width,
                            element.height,
                            parseFloat(_('latitude').value),
                            parseFloat(_('longitude').value));
    _('latitude').value  = geoPos[0];
    _('longitude').value = geoPos[1];
    setGoogleMapImage();
}

/**
 *  Updates the map with new position
 */
function setGoogleMapImage()
{
    _('gmap').src = base_script + '?gadget=Weather&action=GetGoogleMapImage' +
                    '&latitude=' + _('latitude').value + '&longitude=' + _('longitude').value +
                    '&zoom=' + ZoomLevel + '&size='  + ImageSize;
}

/**
 *  Zooms in/out on the map
 */
function zoomMap(level)
{
    ZoomLevel = ZoomLevel + level;
    if (ZoomLevel < 1) {
        ZoomLevel = 1;
    }

    if (ZoomLevel > 10) {
        ZoomLevel = 10;
    }

    setGoogleMapImage();
}

var WeatherAjax = new JawsAjax('Weather', WeatherCallback);

var selectedRow = null,
    selectedRowColor = null;

/**
 * const list taken from google api
 */
const ImageSize = '352x288',
      ZoomPixelsPerLonDegree = [
        0.7111111111111111,
        1.4222222222222223,
        2.8444444444444446,
        5.688888888888889,
        11.377777777777778,
        22.755555555555556,
        45.51111111111111,
        91.02222222222223,
        182.04444444444445,
        364.0888888888889,
        728.1777777777778,
        1456.3555555555556,
        2912.711111111111,
        5825.422222222222,
        11650.844444444445,
        23301.68888888889,
        46603.37777777778,
        93206.75555555556,
        186413.51111111112];

var ZoomLevel = 1;
