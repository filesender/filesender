if (typeof window === 'undefined')
    window = {}; // dummy window for use in webworkers
if (!('filesender' in window))
    window.filesender = {};
if (!('ui' in window.filesender)) {
    window.filesender.ui = {};
    window.filesender.ui.log = function(e) {
        console.log(e);
    }
}
window.filesender.log = function( msg ) {
    console.log( msg );
}

window.filesender.crypto_common = function () {
    return {
        crypto_chunk_size: window.filesender.config.upload_chunk_size, // 5 MB default
        crypto_iv_len: window.filesender.config.crypto_iv_len, // default 16 bytes
                
        separateIvFromData: function (buf) {
            var iv = buf.subarray(0, this.crypto_iv_len);
            var data = buf.subarray(this.crypto_iv_len, buf.length);
            
            return {iv: iv, data: data};
        },
        joinIvAndData: function (iv, data) {
            var buf = new Uint8Array(iv.length + data.length);
            
            buf.set(iv, 0);
            buf.set(data, this.crypto_iv_len);

            return buf;
        },
        convertStringToArrayBufferView: function (str)
        {
            var bytes = new Uint8Array(str.length);
            for (var iii = 0; iii < str.length; iii++)
            {
                bytes[iii] = str.charCodeAt(iii);
            }
            return bytes;
        },
        convertArrayBufferToHexaDecimal: function (buffer)
        {
            var data_view = new DataView(buffer);
            var iii, len, hex = '', c;

            for (iii = 0, len = data_view.byteLength; iii < len; iii += 1)
            {
                c = data_view.getUint8(iii).toString(16);
                if (c.length < 2)
                {
                    c = '0' + c;
                }
                hex += c;
            }
            return hex;
        },
        convertArrayBufferViewtoString: function (buffer)
        {
            var str = "";
            for (var iii = 0; iii < buffer.byteLength; iii++)
            {
                str += String.fromCharCode(buffer[iii]);
            }
            return str;
        }
        
    };
};
