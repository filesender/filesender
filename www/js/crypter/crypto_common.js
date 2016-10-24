if(typeof window === 'undefined') window = {}; // dummy window
if(!('filesender' in window)) window.filesender = {};

window.filesender.crypto_common = function () {
    return {
        crypto_chunk_size: 5 * 1024 * 1024,
        crypto_iv_len: 16,
        separateIvFromData: function (buf) {
            var $this = this;
            var iv = new Uint8Array(this.crypto_iv_len);
            var data = new Uint8Array(buf.length - this.crypto_iv_len);
            Array.prototype.forEach.call(buf, function (byte, i) {
                if (i < $this.crypto_iv_len) {
                    iv[i] = byte;
                } else {
                    data[i - $this.crypto_iv_len] = byte;
                }
            });
            return {iv: iv, data: data};
        },
        joinIvAndData: function (iv, data) {
            var $this = this;
            var buf = new Uint8Array(iv.length + data.length);
            Array.prototype.forEach.call(iv, function (byte, i) {
                buf[i] = byte;
            });
            Array.prototype.forEach.call(data, function (byte, i) {
                buf[$this.crypto_iv_len + i] = byte;
            });
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






















