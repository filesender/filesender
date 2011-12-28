<script type="text/javascript">
if(window.File){
$("#supportFile").html('<?php echo lang("_YES"); ?>');
} else {
$("#supportFile").html('<?php echo lang("_NO"); ?>');
}

if(window.FileReader){
$("#supportFileReader").html('<?php echo lang("_YES"); ?>');
} else {
$("#supportFileReader").html('<?php echo lang("_NO"); ?>');
}


if(window.FileList){
$("#supportFileList").html('<?php echo lang("_YES"); ?>');
} else {
$("#supportFileList").html('<?php echo lang("_NO"); ?>');
}

if(window.Blob){
$("#supportBlob").html('<?php echo lang("_YES"); ?>');
} else {
$("#supportBlob").html('<?php echo lang("_NO"); ?>');
}
 
</script>
<p>The following HTML5 API's must be supported in your browser to allow FileSender to upload your files using HTML5.</p>
<table width="100%" border="0">
  <tr class="altcolor">
    <td><strong>API</strong></td>
    <td><strong>Supported</strong></td>
  </tr>
  <tr class="altcolor">
    <td>File API</td>
    <td><div id="supportFile"></div></td>
  </tr>
  <tr>
    <td><a href="http://dev.w3.org/2006/webapi/FileAPI/#filereader-interface">FileReader API</a></td>
    <td><div id="supportFileReader"></div></td>
  </tr>
  <tr class="altcolor">
    <td><a href="http://dev.w3.org/2006/webapi/FileAPI/#dfn-filelist" target="_blank">FileList API</a></td>
    <td><div id="supportFileList"></div></td>
  </tr>
  <tr>
    <td><a href="http://dev.w3.org/2006/webapi/FileAPI/#dfn-Blob" target="_blank">Blob API</a></td>
    <td><div id="supportBlob"></div></td>
  </tr>
</table>
<p>If you would like to upload large files you require a browser that supports all of the above API's.</p>
<p>See<a href="https://www.assembla.com/spaces/file_sender/wiki" target="_blank"> Filesender Project Site</a> for more details. </p>
