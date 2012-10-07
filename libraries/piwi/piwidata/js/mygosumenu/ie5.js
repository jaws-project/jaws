// +----------------------------------------------------------------+
// | Array functions that are missing in IE 5.0                     |
// | Author: Cezary Tomczak [www.gosu.pl]                           |
// | Free for any use as long as all copyright messages are intact. |
// +----------------------------------------------------------------+

// Removes the last element from an array and returns that element.
if (!Array.prototype.pop) {
    Array.prototype.pop = function() {
        var last;
        if (this.length) {
            last = this[this.length - 1];
            this.length -= 1;
        }
        return last;
    };
}

// Adds one or more elements to the end of an array and returns the new length of the array.
if (!Array.prototype.push) {
    Array.prototype.push = function() {
        for (var i = 0; i < arguments.length; ++i) {
            this[this.length] = arguments[i];
        }
        return this.length;
    };
}

// Removes the first element from an array and returns that element.
if (!Array.prototype.shift) {
    Array.prototype.shift = function() {
        var first;
        if (this.length) {
            first = this[0];
            for (var i = 0; i < this.length - 1; ++i) {
                this[i] = this[i + 1];
            }
            this.length -= 1;
        }
        return first;
    };
}

// Adds one or more elements to the front of an array and returns the new length of the array.
if (!Array.prototype.unshift) {
    Array.prototype.unshift = function() {
        if (arguments.length) {
            var i, len = arguments.length;
            for (i = this.length + len - 1; i >= len; --i) {
                this[i] = this[i - len];
            }
            for (i = 0; i < len; ++i) {
                this[i] = arguments[i];
            }
        }
        return this.length;
    };
}

// Adds and/or removes elements from an array.
if (!Array.prototype.splice) {
    Array.prototype.splice = function(index, howMany) {
        var elements = [], removed = [], i;
        for (i = 2; i < arguments.length; ++i) {
            elements.push(arguments[i]);
        }
        for (i = index; (i < index + howMany) && (i < this.length); ++i) {
            removed.push(this[i]);
        }
        for (i = index + howMany; i < this.length; ++i) {
            this[i - howMany] = this[i];
        }
        this.length -= removed.length;
        for (i = this.length + elements.length - 1; i >= index + elements.length; --i) {
            this[i] = this[i - elements.length];
        }
        for (i = 0; i < elements.length; ++i) {
            this[index + i] = elements[i];
        }
        return removed;
    };
}