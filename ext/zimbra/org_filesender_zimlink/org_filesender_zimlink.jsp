<%@ page language="java" import="javax.servlet.http.HttpServletRequest"%>
<%@ page language="java" import="org.apache.commons.httpclient.*"%>
<%@ page language="java" import="org.apache.commons.httpclient.methods.*"%>
<%@ page language="java" import="java.net.URLEncoder"%>
<%@ page language="java" import="java.io.BufferedReader"%>
<%@ page language="java" import="java.io.InputStream"%>
<%@ page language="java" import="java.io.InputStreamReader"%>
<%@ page language="java" import="org.apache.commons.io.IOUtils"%>
<%@ page language="java" import="java.io.IOException"%>
<%@ page language="java" import="java.io.UnsupportedEncodingException"%>
<%@ page language="java" import="javax.crypto.spec.SecretKeySpec"%>
<%@ page language="java" import="javax.crypto.Mac"%>
<%@ page language="java" import="org.apache.commons.codec.binary.Hex"%>
<%@ page language="java" import="org.apache.commons.httpclient.methods.ByteArrayRequestEntity"%>

<%
    String command = request.getParameter("command");
    String filesender_url = request.getParameter("filesender_url");
    String uid = request.getParameter("uid");
    String secret = request.getParameter("secret");
    
    String ws_url = filesender_url + "rest.php";
    String url = "";
    String signed_url = "";
    String resp = "{\"code\":400,\"isJson\":false,\"response\":\"Bad+request\"}";
    
    try {
        if(!command.equals("") && !filesender_url.equals("") && !uid.equals("") && !secret.equals("")) {
            if(command.equals("get_quota")) {
                url = ws_url + "/user/@me/quota";
                
                signed_url = getSignedJsonRequestUrl("get", url, uid, secret, null);
                
                resp = getJson(signed_url);
                
            } else if(command.equals("create_transfer")) {
                String json = getJsonRequestBody(request);
                
                url = ws_url + "/transfer";
                
                signed_url = getSignedJsonRequestUrl("post", url, uid, secret, json);
                
                resp = postJson(signed_url, json);
                
            } else if(command.equals("upload_chunk")) {
                String file_id = request.getParameter("file_id");
                String offset = request.getParameter("offset");
                String file_size = request.getParameter("file_size");
                
                if(!file_id.equals("") && !offset.equals("") && !file_size.equals("")) {
                    byte[] binary = getBinaryRequestBody(request);
                    
                    url = ws_url + "/file/" + file_id + "/chunk/" + offset;
                    
                    signed_url = getSignedBinaryRequestUrl("put", url, uid, secret, binary);
                    
                    resp = putBinary(signed_url, binary, file_size, offset);
                }
                
                
            } else if(command.equals("complete_file")) {
                String file_id = request.getParameter("file_id");
                
                if(!file_id.equals("")) {
                    String json = "{\"complete\":true}";
                    
                    url = ws_url + "/file/" + file_id;
                    
                    signed_url = getSignedJsonRequestUrl("put", url, uid, secret, json);
                    
                    resp = putJson(signed_url, json);
                }
                
            } else if(command.equals("complete_transfer")) {
                String transfer_id = request.getParameter("transfer_id");
                
                if(!transfer_id.equals("")) {
                    String json = "{\"complete\":true}";
                    
                    url = ws_url + "/transfer/" + transfer_id;
                    
                    signed_url = getSignedJsonRequestUrl("put", url, uid, secret, json);
                    
                    resp = putJson(signed_url, json);
                }
            }
        }
    } catch (Exception e) {
        e.printStackTrace();
        resp = "{\"code\":400,\"isJson\":false,\"response\":\"" + URLEncoder.encode(e.toString(), "UTF-8") + "\"}";
    }
%>

<%= resp %>

<%!public String getJsonRequestBody(HttpServletRequest request) throws IOException { // Get json body from received request
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
            bufferedReader.close();
        }
    }
    
    body = stringBuilder.toString();
    return body;
}
%>

<%!public byte[] getBinaryRequestBody(HttpServletRequest request) throws IOException { // Get json body from received request
    return IOUtils.toByteArray(request.getInputStream());
}
%>

<%!public String hmacSha1(String value, byte[] binary, String key) throws RuntimeException, IOException { // Compute SHA-1 HMAC signature with binary data
    try {
        // Get an hmac_sha1 key from the raw key bytes
        byte[] keyBytes = key.getBytes();           
        SecretKeySpec signingKey = new SecretKeySpec(keyBytes, "HmacSHA1");

        // Get an hmac_sha1 Mac instance and initialize with the signing key
        Mac mac = Mac.getInstance("HmacSHA1");
        mac.init(signingKey);

        // Compute the hmac on input data bytes
        mac.update(value.getBytes());
        
        if(binary != null)
            mac.update(binary);
        
        byte[] rawHmac = mac.doFinal();

        // Convert raw bytes to Hex
        byte[] hexBytes = new Hex().encode(rawHmac);

        //  Covert array of Hex bytes to a String
        return new String(hexBytes, "UTF-8");
    } catch (Exception e) {
        throw new RuntimeException(e);
    }
}
%>

<%!public String getSignedJsonRequestUrl(String method, String url, String uid, String secret, String json) throws IOException { // Sign a json request (method must be lowercase)
    url += "?remote_user=" + uid + "&timestamp=" + (System.currentTimeMillis() / 1000);
    
    String signed = method + "&" + url.replace("http://", "").replace("https://", "");
    
    if(json != null) {
        signed += "&" + json;
    }
    
    return url + "&signature=" + hmacSha1(signed, null, secret);
}
%>

<%!public String getSignedBinaryRequestUrl(String method, String url, String uid, String secret, byte[] binary) throws IOException { // Sign a json request (method must be lowercase)
    url += "?remote_user=" + uid + "&timestamp=" + (System.currentTimeMillis() / 1000);
    
    String signed = method + "&" + url.replace("http://", "").replace("https://", "") + "&";
    
    return url + "&signature=" + hmacSha1(signed, binary, secret);
}
%>

<%!public String getJson(String url) throws Exception { // Make HTTP GET request to an url with a binary payload
    GetMethod method = new GetMethod(url);
    
    return makeRequest(method);
}
%>

<%!public String postJson(String url, String json) throws Exception { // Make HTTP POST request to an url with a binary payload
    StringRequestEntity request = new StringRequestEntity(json, "application/json", "UTF-8");
    
    PostMethod method = new PostMethod(url);
    method.setRequestHeader("Content-type", "application/json");
    method.setRequestEntity(request);
    
    return makeRequest(method);
}
%>

<%!public String putJson(String url, String json) throws Exception { // Make HTTP PUT request to an url with a json payload
    StringRequestEntity request = new StringRequestEntity(json, "application/json", "UTF-8");
    
    PutMethod method = new PutMethod(url);
    method.setRequestHeader("Content-type", "application/json");
    method.setRequestEntity(request);
    
    return makeRequest(method);
}
%>

<%!public String putBinary(String url, byte[] binary, String fileSize, String offset) throws Exception { // Make HTTP PUT request to an url with a binary payload
    PutMethod method = new PutMethod(url);
    method.setRequestHeader("X-Filesender-File-Size", fileSize);
    method.setRequestHeader("X-Filesender-Chunk-Offset", offset);
    method.setRequestHeader("X-Filesender-Chunk-Size", String.valueOf(binary.length));
    method.setRequestEntity(new ByteArrayRequestEntity(binary, "application/octet-stream"));
    
    return makeRequest(method);
}
%>

<%!public String makeRequest(HttpMethod method) throws Exception { // Make HTTP request of given type and handle response
    String out = "";
    HttpClient client = new HttpClient();
    
    int code = client.executeMethod(method);
    String success = "false";
    if(code == 200 || code == 201) {
        success = "true";
    }
    
    out = method.getResponseBodyAsString();
    
    String isJson = "true";
    Header ct = method.getResponseHeader("Content-type");
    if(ct == null || !ct.getValue().equals("application/json")) {
        out = "\"" + URLEncoder.encode(out, "UTF-8") + "\"";
        isJson = "false";
    }
    
    return "{\"success\":" + success + ",\"code\":" + code + ",\"isJson\":" + isJson + ",\"response\":" + out + "}";
}
%>
