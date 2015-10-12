/**
 * Weather Javascript actions
 *
 * @category   Ajax
 * @package    Weather
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var WeatherCallback = {
    DeleteRegion: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('weather_datagrid').deleteItem();          
            getDG();
            stopAction();
        }
        showResponse(response);
    },
    
    InsertRegion: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('weather_datagrid').addItem();
            $('weather_datagrid').setCurrentPage(0);
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    UpdateRegion: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getDG();
            stopAction();
        }
        showResponse(response);
    },

    UpdateProperties: function(response) {
        showResponse(response);
    }

}

/**
 * Initiates Weather JS
 */
function initWeather()
{
    stopAction();
    $('latitude').value = '51.30';
    $('longitude').value = '00.08';
    initDataGrid('weather_datagrid', WeatherAjax);
    setGoogleMapImage();
}

/**
 * Clears the form
 */
function stopAction() 
{
    if (selectedRow) {
        $('weather_datagrid').unselectRow(selectedRow);
        selectedRow = null;
    }
    $('id').value = '';
    $('title').value = '';
    $('fast_url').value = '';
    $('published').value = 1;
    $('title').focus();
}

/**
 * Edits a region
 */
function editRegion(rowElement, id)
{
    if (selectedRow) {
        $('weather_datagrid').unselectRow(selectedRow);
    }
    $('weather_datagrid').selectRow(rowElement);
    selectedRow = rowElement;

    var geoPos = WeatherAjax.callSync('GetRegion', id);
    $('id').value        = geoPos['id'];
    $('title').value     = geoPos['title'].defilter();
    $('fast_url').value  = geoPos['fast_url'];
    $('latitude').value  = geoPos['latitude'];
    $('longitude').value = geoPos['longitude'];
    $('published').value = geoPos['published']? 1 : 0;
    setGoogleMapImage();
}

/**
 * Adds/Updates the region
 */
function updateRegion()
{
    if (!$('title').val() ||
        !$('latitude').val() ||
        !$('longitude').val())
    {
        alert(incompleteFields);
        return;
    }

    if ($('id').value == 0) {
        WeatherAjax.callAsync(
            'InsertRegion', [
                $('title').value,
                $('fast_url').value,
                $('latitude').value,
                $('longitude').value,
                $('published').value
            ]
        );
    } else {
        WeatherAjax.callAsync(
            'UpdateRegion', [
                $('id').value,
                $('title').value,
                $('fast_url').value,
                $('latitude').value,
                $('longitude').value,
                $('published').value
            ]
        );
    }
}

/**
 * Deletes the region
 */
function deleteRegion(rowElement, id)
{
    stopAction();
    $('weather_datagrid').selectRow(rowElement);
    if (confirm(confirmDelete)) {
        WeatherAjax.callAsync('DeleteRegion', id);
    } else {
        $('weather_datagrid').unselectRow(rowElement);
        selectedRow = null;
    }
}

/**
 * Updates the properties
 */
function updateProperties()
{
    WeatherAjax.callAsync(
        'UpdateProperties', [
            $('unit').value,
            $('update_period').value,
            $('date_format').value,
            $('api_key').value
        ]
    );
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
                            parseFloat($('latitude').value),
                            parseFloat($('longitude').value));
    $('latitude').value  = geoPos[0];
    $('longitude').value = geoPos[1];
    setGoogleMapImage();
}

/**
 *  Updates the map with new position
 */
function setGoogleMapImage()
{
    $('gmap').src = base_script + '?gadget=Weather&action=GetGoogleMapImage' +
                    '&latitude=' + $('latitude').value + '&longitude=' + $('longitude').value +
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
