<%@ page language="java" import="org.apache.commons.httpclient.HttpClient"%>
<%@ page language="java" import="org.apache.commons.httpclient.Header"%>
<%@ page language="java" import="org.apache.commons.httpclient.methods.PostMethod"%>
<%@ page language="java" import="org.apache.commons.httpclient.methods.PutMethod"%>
<%@ page language="java" import="java.net.URLEncoder"%>

<%
    String command = request.getParameter('command');
    String filesender_url = request.getParameter('filesender_url');
    String uid = request.getParameter('uid');
    String secret = request.getParameter('secret');
    
    String ws_url = filesender_url + 'rest.php';
    String url = '';
    String signed_url = '';
    String response = '{"code":400,"isJson":false,"response":"Bad+request"}';
    
    if(!command.equals('') && !filesender_url.equals('') && !uid.equals('') && !secret.equals('')) {
        if(command.equals('create_transfer')) {
            String json = getJsonRequestBody(request);
            
            url = ws_url + '/transfer';
            
            signed_url = getSignedJsonRequestUrl('post', url, uid, secret, json);
            
            response = postJson(signed_url, json);
            
        } else if(command.equals('upload_chunk')) {
            String file_id = request.getParameter('file_id');
            String offset = request.getParameter('offset');
            
            if(!file_id.equals('') && !offset.equals('')) {
                byte[] binary = getBinaryRequestBody(request);
                
                url = ws_url + '/file/' + file_id + '/offset/' + offset;
                
                signed_url = getSignedBinaryRequestUrl('put', url, uid, secret, binary);
                
                response = putBinary(signed_url, binary);
            }
            
            
        } else if(command.equals('complete_file')) {
            String file_id = request.getParameter('file_id');
            
            if(!file_id.equals('')) {
                String json = '{"complete":true}';
                
                url = ws_url + '/file/' + file_id;
                
                signed_url = getSignedJsonRequestUrl('put', url, uid, secret, json);
                
                response = putJson(signed_url, json);
            }
            
        } else if(command.equals('complete_transfer')) {
            String transfer_id = request.getParameter('transfer_id');
            
            if(!transfer_id.equals('')) {
                String json = '{"complete":true}';
                
                url = ws_url + '/transfer/' + transfer_id;
                
                signed_url = getSignedJsonRequestUrl('put', url, uid, secret, json);
                
                response = putJson(signed_url, json);
            }
        }
    }
    
    try {
        String url = 'http://search.twitter.com/search.json?q=' + query;
        
        if(action.contains('GET')) {
            response = makeHttpGET(url);
            
        } else if(action.contains('POST')) {
            response = makeHttpPOST("https://api.facebook.com/restserver.php");
            
        }
        
    } catch (Exception e) {
        response = e.toString();
    }
%>

<%= response %>

<%!public String getJsonRequestBody(HttpRequest request) { // Get json body from received request
    String body = null;
    StringBuilder stringBuilder = new StringBuilder();
    BufferedReader bufferedReader = null;
    
    try {
        InputStream inputStream = request.getInputStream();
        if (inputStream != null) {
            bufferedReader = new BufferedReader(new InputStreamReader(inputStream));
            char[] charBuffer = new char[128];
            int bytesRead = -1;
            while ((bytesRead = bufferedReader.read(charBuffer)) > 0) {
                stringBuilder.append(charBuffer, 0, bytesRead);
            }
        } else {
            stringBuilder.append("");
        }
    } catch (IOException ex) {
        throw ex;
    } finally {
        if (bufferedReader != null) {
            try {
                bufferedReader.close();
            } catch (IOException ex) {
                throw ex;
            }
        }
    }
    
    body = stringBuilder.toString();
    return body;
}%>

<%!public byte[] getBinaryRequestBody(HttpRequest request) { // Get json body from received request
    return IOUtils.toByteArray(request.getInputStream());
}%>

<%!public String hmacSha1(String value, String key) { // Compute SHA-1 HMAC signature
    try {
        // Get an hmac_sha1 key from the raw key bytes
        byte[] keyBytes = key.getBytes();           
        SecretKeySpec signingKey = new SecretKeySpec(keyBytes, 'HmacSHA1');

        // Get an hmac_sha1 Mac instance and initialize with the signing key
        Mac mac = Mac.getInstance('HmacSHA1');
        mac.init(signingKey);

        // Compute the hmac on input data bytes
        byte[] rawHmac = mac.doFinal(value.getBytes());

        // Convert raw bytes to Hex
        byte[] hexBytes = new Hex().encode(rawHmac);

        //  Covert array of Hex bytes to a String
        return new String(hexBytes, 'UTF-8');
    } catch (Exception e) {
        throw new RuntimeException(e);
    }
}%>

<%!public String hmacSha1Binary(String value, byte[] binary, String key) { // Compute SHA-1 HMAC signature with binary data
    try {
        // Get an hmac_sha1 key from the raw key bytes
        byte[] keyBytes = key.getBytes();           
        SecretKeySpec signingKey = new SecretKeySpec(keyBytes, 'HmacSHA1');

        // Get an hmac_sha1 Mac instance and initialize with the signing key
        Mac mac = Mac.getInstance('HmacSHA1');
        mac.init(signingKey);

        // Compute the hmac on input data bytes
        mac.update(value.getBytes());
        byte[] rawHmac = mac.doFinal(binary);

        // Convert raw bytes to Hex
        byte[] hexBytes = new Hex().encode(rawHmac);

        //  Covert array of Hex bytes to a String
        return new String(hexBytes, 'UTF-8');
    } catch (Exception e) {
        throw new RuntimeException(e);
    }
}%>

<%!public String getSignedJsonRequestUrl(String method, String url, String uid, String secret, String json) { // Sign a json request (method must be lowercase)
    url += '?remote_user=' + uid + '&timestamp=' + (System.currentTimeMillis() / 1000);
    
    String signed = method + '&' + url.replace('http://', '').replace('https://', '') + '&' + json;
    
    return url + '&signature=' + hmacSha1(signed, secret);
}%>

<%!public String getSignedBinaryRequestUrl(String method, String url, String uid, String secret, byte[] binary) { // Sign a json request (method must be lowercase)
    url += '?remote_user=' + uid + '&timestamp=' + (System.currentTimeMillis() / 1000);
    
    String signed = method + '&' + url.replace('http://', '').replace('https://', '') + '&';
    
    return url + '&signature=' + hmacSha1Binary(signed, binary, secret);
}%>

<%!public String postJson(String url, String json) { // Make HTTP POST request to an url with a binary payload
    StringRequestEntity request = new StringRequestEntity(json, 'application/json', 'UTF-8');
    
    PostMethod method = new PostMethod(url);
    method.setRequestHeader('Content-type', 'application/json');
    method.setRequestEntity(request);
    
    return makeRequest(method);
}%>

<%!public String putJson(String url, String json) { // Make HTTP PUT request to an url with a json payload
    StringRequestEntity request = new StringRequestEntity(json, 'application/json', 'UTF-8');
    
    PutMethod method = new PutMethod(url);
    method.setRequestHeader('Content-type', 'application/json');
    method.setRequestEntity(request);
    
    return makeRequest(method);
}%>

<%!public String putBinary(String url, byte[] binary) { // Make HTTP PUT request to an url with a binary payload
    PutMethod method = new PutMethod(url);
    method.setRequestHeader('Content-type', 'application/octet-stream');
    method.setRequestEntity(new ByteArrayEntity(binary));
    
    return makeRequest(method);
}%>

<%!public String makeRequest(HttpMethod method) { // Make HTTP request of given type and handle response
    String response = '';
    HttpClient client = new HttpClient();
    
    try {
        int code = client.executeMethod(method);
        String success = 'false';
        if(code == 200 || code == 201) {
            success = 'true';
        }
        
        String ct = 'text/plain';
        HeaderElement[] he = method.getResponseHeaders('Content-type').getElements();
        if(he.length) {
            ct = he[0].getValue();
        }
        
        response = method.getResponseBodyAsString();
        
        String isJson = 'true';
        if(!ct.equals('application/json')) {
            response = '"' + URLEncoder.encode(response, 'UTF-8') + '"';
            isJson = 'false';
        }
        
        response = '{"success":' + success + ',"code":' + code + ',"isJson":"' + isJson + '","response":' + response + '}';
        
    } catch (Exception e) {
        response = e.toString();
    }
    
    return response;
}%>
