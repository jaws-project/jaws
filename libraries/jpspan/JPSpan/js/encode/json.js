/**@
* include 'serialize.js';
*/
// $Id: php.js,v 1.3 2004/11/12 15:41:10 harryf Exp $

function JPSpan_Encode_JSON() {
    this.Serialize = new JPSpan_Serialize(this);
};

JPSpan_Encode_JSON.prototype = {

    contentType: 'text/plain; charset=utf-8',
    
    encode: function(data) {
        return JSON.stringify(data);
    },
    
    encodeInteger: function(v) {
    },
    
    encodeDouble: function(v) {
    },
    
    encodeString: function(v) {
    },
    
    encodeNull: function() {
    },
    
    encodeTrue: function() {
    },
    
    encodeFalse: function() {
    },
    
    encodeArray: function(v, Serializer) {
    },
    
    encodeObject: function(v, Serializer, cname) {
    },
    
    encodeError: function(v, Serializer, cname) {
    }
};
