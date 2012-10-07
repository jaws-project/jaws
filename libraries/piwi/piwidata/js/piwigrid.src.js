var PiwiGrid = {

    init: function(tableElement, tableBody) {
        tableElement.rowsSize = 0;
        tableElement.firstPage = PiwiGrid.firstPage;
        tableElement.previousPage = PiwiGrid.previousPage;
        tableElement.nextPage = PiwiGrid.nextPage;
        tableElement.lastPage = PiwiGrid.lastPage;
        tableElement.getFirstPagerValues = PiwiGrid.getFirstPagerValues;
        tableElement.getPreviousPagerValues = PiwiGrid.getPreviousPagerValues;
        tableElement.getNextPagerValues = PiwiGrid.getNextPagerValues;
        tableElement.getLastPagerValues = PiwiGrid.getLastPagerValues;
        tableElement.updatePageCounter = PiwiGrid.updatePageCounter;
        tableElement.updatePageCounterView = PiwiGrid.updatePageCounterView;
        tableElement.addItem = PiwiGrid.addItem;
        tableElement.deleteItem = PiwiGrid.deleteItem;
        tableElement.addRow = PiwiGrid.addRow;
        tableElement.deleteRow = PiwiGrid.deleteRow;
        tableElement.repaint = PiwiGrid.repaint;
        tableElement.reset = PiwiGrid.reset;
        tableElement.fillWithArray = PiwiGrid.fillWithArray;
        tableElement.body = tableBody;
        tableElement.pagerStatus = 'pagerStatusOf_' + tableElement.id;
        tableElement.tablePagerStatus = 'pagerTableStatusOf_' + tableElement.id;
        tableElement.usePager = false;
        tableElement.usingCookies = true;
        tableElement.evenColor = 'white';
        tableElement.oddColor = '#edf3fe';
        tableElement.pageBy = 10;
        tableElement.currentPage = 0;
        tableElement.currentPageXHTML = '0-10';
        tableElement.pagerMode = 'NORMAL';
        tableElement.multipleSelection = false;
        tableElement.selectedAll = false;
        tableElement.getSelectedRows = PiwiGrid.getSelectedRows;
        tableElement.selectRow = PiwiGrid.selectRow;
        tableElement.unselectRow = PiwiGrid.unselectRow;
        tableElement.mouseOverRow = PiwiGrid.onMouseOver;
        tableElement.mouseOutRow = PiwiGrid.onMouseOut;
        tableElement.hidePageCounter = PiwiGrid.hidePageCounter;
        tableElement.showPageCounter = PiwiGrid.showPageCounter;
        tableElement.setCurrentPage = PiwiGrid.setCurrentPage;
        tableElement.cleanCurrentPage = PiwiGrid.cleanCurrentPage;
        tableElement.getCookieName = PiwiGrid.getCookieName;
        tableElement.getCurrentPage = PiwiGrid.getCurrentPage;
    },

    useMultipleSelection: function(tableElement, useMultiple) {
        tableElement.multipleSelection = useMultiple;
    },

    usePager: function(tableElement, pager) {
        tableElement.usePager = pager;
    },

    pagerMode: function(tableElement, mode) {
        tableElement.pagerMode = mode;
    },

    currentPage: function(tableElement, page) {
        //tableElement.currentPage = page;
        tableElement.setCurrentPage(page);
    },

    pageBy: function(tableElement, size) {
        tableElement.pageBy = size;
    },

    rowsSize: function(tableElement, rowsSize) {
        tableElement.rowsSize = rowsSize;
    },

    evenColor: function(tableElement, color) {
        tableElement.evenColor = color;
    },

    oddColor: function(tableElement, color) {
        tableElement.oddColor = color;
    },

    addRow: function(trElement) {
        var tbody = this.body;
        tbody.appendChild(trElement);
        this.addItem();
    },

    fillWithArray: function(inputArray) {
        var tbody = this.body;
        if (this.onLoadingData && inputArray.length > 0) {
            this.onLoadingData();
        }

        for (var y=0; y<inputArray.length; y++) {
            var row = document.createElement('tr');
            if (inputArray[y]['__ID__'] != undefined) {
                row.setAttribute('id', this.id + '_id_' + inputArray[y]['__ID__']);
            }
            if (this.multipleSelection) {
                var column = document.createElement('td');
                if (inputArray[y]['__KEY__'] != undefined) {
                    var check  = document.createElement('input');
                    check.setAttribute('type', 'checkbox');
                    check.setAttribute('name', (this.id + '_checkbox[]'));
                    check.setAttribute('className', (this.id + '_checkbox'));
                    check.setAttribute('class', (this.id + '_checkbox'));
                    check.setAttribute('value', inputArray[y]['__KEY__']);
                    check.onclick = function() {
                        PiwiGrid.selectRow(this);
                    }
                    column.appendChild(check);
                } else {
                    column.innerHTML = '&nbsp;';
                }
                row.appendChild(column);
            }

            for(key in inputArray[y]) {
                if (typeof(inputArray[y][key]) != 'function') {
                    if (key != '__KEY__' && key != '__ID__') {
                        var column = document.createElement('td');
                        column.innerHTML = inputArray[y][key];
                        row.appendChild(column);
                    }
                }
            }
            tbody.appendChild(row);
        }

        if (this.onLoadedData && inputArray.length > 0) {
            this.onLoadedData();
        }
    },

    deleteRow: function(trElement) {
        if (this.rowsSize > 0) {
            var tbody = this.body;
            tbody.removeChild(trElement);
            this.deleteItem();
        }
    },

    reset: function() {
        var tbody = this.body;
        var rows  = tbody.getElementsByTagName('tr');
        while(rows.length != 0) {
            tbody.deleteRow(0);
        }
    },

    repaint: function() {
        var tbody  = this.body;
        var rows   = tbody.getElementsByTagName('tr');
        var length = rows.length;
        var color  = this.evenColor;
        for(var i=0; i<length; i++) {
            rows[i].style.backgroundColor = color;
            if (i % 2 == 0) {
                color = this.oddColor;
            } else {
                color = this.evenColor;
            }
        }
    },

    updatePageCounterView: function() {
        switch(this.pagerMode) {
            case 'NORMAL':
            var counterStatus = document.getElementById(this.pagerStatus);
            var tableCounter  = document.getElementById(this.tablePagerStatus);
            if (parseInt(this.rowsSize) > parseInt(this.pageBy)) {
                counterStatus.innerHTML = ' ' + this.currentPageXHTML + ' (' + this.rowsSize + ') ';
                try {
                    tableCounter.style.display = 'table';
                } catch(e) {
                    tableCounter.style.display = 'block';
                }
            } else {
                tableCounter.style.display = 'none';
            }
            break;
        }
    },

    updatePageCounter: function() {
        if (this.getCurrentPage() > 0) {
            var first = this.getFirstPagerValues();
            var prev  = this.getPreviousPagerValues();
            var next  = this.getNextPagerValues();
            var last  = this.getLastPagerValues();
            if ((prev + 2*this.pageBy) == next) {
                this.currentPageXHTML = (next - this.pageBy + 1) + '&nbsp;-&nbsp;' + (parseInt(next));
            } else {
                if (next == this.rowsSize) {
                    this.setCurrentPage(prev);
                    this.currentPageXHTML = (prev + 1) + '&nbsp;-&nbsp;' + this.rowsSize;
                } else {
                    this.currentPageXHTML = (next + 1) + '&nbsp;-&nbsp;' + this.rowsSize;
                }
            }
        } else {
            this.currentPageXHTML = '1 &nbsp;-&nbsp;' + (parseInt(this.pageBy));
        }
        this.updatePageCounterView();
    },

    addItem: function() {
        this.rowsSize++;
        this.updatePageCounter();
    },

    deleteItem: function() {
        this.rowsSize--;
        this.updatePageCounter();
    },

    getFirstPagerValues: function() {
        return 0;
    },

    getPreviousPagerValues: function() {
        var result = parseInt(this.getCurrentPage()) - parseInt(this.pageBy);
        if (result < 0) {
            result = parseInt(this.getCurrentPage());
        }
        return result;
    },

    getNextPagerValues: function() {
        var result = parseInt(this.getCurrentPage()) + parseInt(this.pageBy);
        if (result >= this.rowsSize) {
            result = parseInt(this.getCurrentPage());
        }
        return result;
    },

    getLastPagerValues: function() {
        var result = this.rowsSize - this.rowsSize % parseInt(this.pageBy);
        if (result == this.rowsSize) {
            result = this.rowsSize - parseInt(this.pageBy);
        }
        return result;
    },

    firstPage: function() {
        var values = this.getFirstPagerValues();
        if (values != this.getCurrentPage()) {
            this.setCurrentPage(values);
            this.currentPageXHTML = (parseInt(this.getCurrentPage())+1) + '&nbsp;-&nbsp;' + (parseInt(values) + parseInt(this.pageBy));
            this.updatePageCounterView();
        }
    },

    previousPage: function() {
        var values = this.getPreviousPagerValues();
        if (values != this.getCurrentPage()) {
            this.setCurrentPage(values);
            this.currentPageXHTML = (parseInt(this.getCurrentPage())+1) + '&nbsp;-&nbsp;' + (parseInt(values) + parseInt(this.pageBy));
            this.updatePageCounterView();
        }
    },

    nextPage: function() {
        var values = this.getNextPagerValues();
        if (values != this.getCurrentPage()) {
            this.setCurrentPage(values);
            if ((parseInt(values) + parseInt(this.pageBy)) > this.rowsSize) {
                this.currentPageXHTML = (parseInt(this.getCurrentPage())+1) + '&nbsp;-&nbsp;' + this.rowsSize;
            } else {
                this.currentPageXHTML = (parseInt(this.getCurrentPage())+1) + '&nbsp;-&nbsp;' + (parseInt(values) +
                                                                                            parseInt(this.pageBy));
            }
            this.updatePageCounterView();
        }
    },

    lastPage: function() {
        var values = this.getLastPagerValues();
        if (values != this.getCurrentPage()) {
            this.setCurrentPage(values);
            this.currentPageXHTML = (parseInt(this.getCurrentPage())+1) + '&nbsp;-&nbsp;' + this.rowsSize;
            this.updatePageCounterView();
        }
    },

    getSelectedRows: function() {
        if (this.multipleSelection) {
            var gridBody = this.body;
            var cboxes   = gridBody.getElementsByTagName('input');
            var length   = cboxes.length;
            var realName = this.id + '_checkbox';
            var rows     = new Array();
            var counter  = 0;
            for(var i=0; i<length; i++) {
                var input     = cboxes[i];
                var className = input.getAttribute('class');
                if (className == realName) {
                    if (input.checked) {
                        rows[counter] = input.value;
                        counter++;
                    }
                }
            }
            return rows;
        }
    },

    multiSelect: function(gridTable) {
        if (gridTable.multipleSelection) {
            var gridBody = gridTable.body;
            var cboxes   = gridBody.getElementsByTagName('input');
            var length   = cboxes.length;
            var realName = gridTable.id + '_checkbox';
            for(var i=0; i<length; i++) {
                var input     = cboxes[i];
                var className = input.getAttribute('class');
                if (className == realName) {
                    if (gridTable.selectedAll) {
                        input.checked = false;
                        gridTable.selectRow(input);
                    } else {
                        input.checked = true;
                        gridTable.selectRow(input);
                    }
                }
            }
            if (gridTable.selectedAll) {
                gridTable.selectedAll = false;
            } else {
                gridTable.selectedAll = true;
            }
        }
    },

    selectRow: function(input) {
        if (input.checked == false) {
            this.unselectRow(input);
        } else {
            if (this.onRowSelected) {
                this.onRowSelected(input, input.parentNode.parentNode);
            }
            if (input.parentNode.parentNode.originalColor == undefined) {
                input.parentNode.parentNode.originalColor = input.parentNode.parentNode.style.backgroundColor;
            }
            input.parentNode.parentNode.style.backgroundColor = '#ffffcc';
            input.parentNode.parentNode.selected = true;
        }
    },

    unselectRow: function(input) {
        if (this.onRowUnselected) {
            this.onRowUnselected(input, inpurt.parentNode.parentNode);
        }
        input.parentNode.parentNode.style.backgroundColor = input.parentNode.parentNode.originalColor;
        input.parentNode.parentNode.selected = false;
    },

    onMouseOver: function(row) {
        if (!row.selected) {
            if (row.originalColor == undefined) {
                row.originalColor = row.style.backgroundColor;
            }
            row.style.backgroundColor = '#ffffcc';
            row.hasMouseOver = true;
        }
    },

    onMouseOut: function(row) {
        if (!row.selected) {
            row.style.backgroundColor = row.originalColor;
            row.hasMouseOver = false;
        }
    },

    hidePageCounter: function() {
        if (this.usePager) {
            var tableCounter  = document.getElementById(this.tablePagerStatus);
            tableCounter.style.display = 'none';
        }
    },

    showPageCounter: function() {
        if (this.usePager) {
            var tableCounter  = document.getElementById(this.tablePagerStatus);
            tableCounter.style.display = 'table';
        }
    },

    getCurrentPage: function() {
        if (this.usingCookies == true) {
            var cookieName = this.getCookieName() + "=";
            var cookies    = document.cookie.split(';');
            for(var i=0; i<cookies.length; i++) {
                var cookie = cookies[i];
                while (cookie.charAt(0) == ' ') cookie = cookie.substring(1, cookie.length);
                if (cookie.indexOf(cookieName) == 0) return cookie.substring(cookieName.length, cookie.length);
            }
        }
        return this.currentPage;
    },

    setCurrentPage: function(page) {
        this.currentPage = page;
        if (this.usingCookies == true) {
            //Expires on..
            var date = new Date();
            date.setTime(date.getTime()+(300000)); //5 minutes
            var expires = "; expires="+date.toGMTString();
            //Set cookie
            document.cookie = this.getCookieName() + "=" + page + expires + "; path=/";
        }
    },

    cleanCurrentPage: function() {
        var value   = "";
        var expires = "";
        //Set cookie
        document.cookie = this.getCookieName() + "=" + value + expires + "; path=/";
    },

    getCookieName: function() {
        return this.id;
    }
}
