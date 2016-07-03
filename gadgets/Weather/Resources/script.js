/**
 * Weather Javascript actions
 *
 * @category   Ajax
 * @package    Weather
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Use async mode, create Callback
 */
var WeatherCallback = {
    DeleteRegion: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('#weather_datagrid')[0].deleteItem();
            getDG();
            stopAction();
        }
        WeatherAjax.showResponse(response);
    },
    
    InsertRegion: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('#weather_datagrid')[0].addItem();
            $('#weather_datagrid')[0].setCurrentPage(0);
            getDG();
            stopAction();
        }
        WeatherAjax.showResponse(response);
    },

    UpdateRegion: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getDG();
            stopAction();
        }
        WeatherAjax.showResponse(response);
    },

    UpdateProperties: function(response) {
        WeatherAjax.showResponse(response);
    }
};

/**
 * Initiates Weather JS
 */
function initWeather()
{
    stopAction();
    $('#latitude').val('51.30');
    $('#longitude').val('00.08');
    initDataGrid('weather_datagrid', WeatherAjax);
    setGoogleMapImage();
}

/**
 * Clears the form
 */
function stopAction() 
{
    if (selectedRow) {
        $('#weather_datagrid')[0].unselectRow(selectedRow);
        selectedRow = null;
    }
    $('#id').val('');
    $('#title').val('');
    $('#fast_url').val('');
    $('#published').val(1);
    $('#title').focus();
}

/**
 * Edits a region
 */
function editRegion(rowElement, id)
{
    if (selectedRow) {
        $('#weather_datagrid')[0].unselectRow(selectedRow);
    }
    $('#weather_datagrid')[0].selectRow(rowElement);
    selectedRow = rowElement;

    var geoPos = WeatherAjax.callSync('GetRegion', id);
    $('#id').val(geoPos['id']);
    $('#title').val(geoPos['title'].defilter());
    $('#fast_url').val(geoPos['fast_url']);
    $('#latitude').val(geoPos['latitude']);
    $('#longitude').val(geoPos['longitude']);
    $('#published').val(geoPos['published']? 1 : 0);
    setGoogleMapImage();
}

/**
 * Adds/Updates the region
 */
function updateRegion()
{
    if (!$('#title').val() ||
        !$('#latitude').val() ||
        !$('#longitude').val())
    {
        alert(incompleteFields);
        return;
    }

    if ($('#id').val() == 0) {
        WeatherAjax.callAsync(
            'InsertRegion', [
                $('#title').val(),
                $('#fast_url').val(),
                $('#latitude').val(),
                $('#longitude').val(),
                $('#published').val()
            ]
        );
    } else {
        WeatherAjax.callAsync(
            'UpdateRegion', [
                $('#id').val(),
                $('#title').val(),
                $('#fast_url').val(),
                $('#latitude').val(),
                $('#longitude').val(),
                $('#published').val()
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
    $('#weather_datagrid')[0].selectRow(rowElement);
    if (confirm(confirmDelete)) {
        WeatherAjax.callAsync('DeleteRegion', id);
    } else {
        $('#weather_datagrid')[0].unselectRow(rowElement);
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
            $('#unit').val(),
            $('#update_period').val(),
            $('#date_format').val(),
            $('#api_key').val()
        ]
    );
}

/**
 * Calculates position of the element
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
 * Calculates coordinates of the clicked point and returns the appropriate map
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
                            parseFloat($('#latitude').val()),
                            parseFloat($('#longitude').val()));
    $('#latitude').val(geoPos[0]);
    $('#longitude').val(geoPos[1]);
    setGoogleMapImage();
}

/**
 * Updates the map with new position
 */
function setGoogleMapImage()
{
    $('#gmap').prop('src', base_script + '?gadget=Weather&action=GetGoogleMapImage' +
                    '&latitude=' + $('#latitude').val() + '&longitude=' + $('#longitude').val() +
                    '&zoom=' + ZoomLevel + '&size='  + ImageSize);
}

/**
 * Zooms in/out on the map
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
