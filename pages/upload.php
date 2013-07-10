<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* ---------------------------------
 * File Upload
 * ---------------------------------
 * 
 */

// --------------------------------------------------------
// file upload uses HTML5 and standard form based upload
// When using standard form based upload a iframe is used to send the form
// and an ajax call is used to check on the progress of the upload
// If the server is not able to return the information about the file upload then a default spinner is loaded
// --------------------------------------------------------

// check if a voucher and load into form if it is
$filestatus = "Available";
$voucherUID = "";
$senderemail = $useremail;

// get initial upload uid
$id = getGUID();
// set id for progress bar upload
// $id = md5(microtime() . rand());

// check if this is a vooucher
if($authvoucher->aVoucher())
{
    // clear aup session
    //unset ($_SESSION['aup'], $var);

    // get voucher information
    $voucherData =  $authvoucher->getVoucher();
    $voucherUID = $voucherData[0]["filevoucheruid"];
    $senderemail = array($voucherData[0]["fileto"]);
    // check if voucher is invalid (this should be an external function
    if($voucherData[0]["filestatus"] == "Voucher") {
        $filestatus = "Voucher";
    } else if($voucherData[0]["filestatus"] == "Voucher Cancelled" || $voucherData[0]["filestatus"] == "Closed")
    {
        ?>
        <p><?php echo lang("_VOUCHER_CANCELLED"); ?></p>
        <?php
        return;
    }
}
if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
    $token = urlencode($_COOKIE['SimpleSAMLAuthToken']);
} else {
    $token = "";
}

global $config;
// set flash upload vairiables
$flashVARS = "vid=".$voucherUID."&sid=".session_id()."&buttonBrowse=".lang("_BROWSE")."&buttonUpload=".lang("_SEND")."&buttonCancel=".lang("_CANCEL")."&siteURL=".$config["site_url"]."&token=".$token;
?>
<script type="text/javascript" src="lib/js/AC_OETags.js" language="javascript"></script>
<script type="text/javascript" src="js/upload.js"></script>
<script type="text/javascript">
//<![CDATA[
// all default settings
var uploadid = '<?php echo $id ?>';
var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
var minimumDate = <?php echo (time()+86400)*1000 ?>;
var maxFLASHuploadsize = <?php echo $config['max_flash_upload_size']; ?>;
var maxEmailRecipients = <?php echo $config['max_email_recipients']; ?>;
var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
var aup = '<?php echo $config['AuP'] ?>';
var bytesUploaded = 0;
var bytesTotal = 0;
var ext = '<?php echo $config['ban_extension']?>';
var banextensions = ext.split(",");
var previousBytesLoaded = 0;
var intervalTimer = 0;
var errmsg_disk_space = "<?php echo lang("_DISK_SPACE_ERROR"); ?>";
var filedata=new Array();
var nameLang = '<?php echo lang("_FILE_NAME"); ?>';
var sizeLang = '<?php echo lang("_SIZE"); ?>';

var vid='<?php if(isset($_REQUEST["vid"])){echo htmlspecialchars($_REQUEST["vid"]);}; ?>';

var groupid = '<?php echo getOpenSSLKey(); ?>';

<?php
    if (!$authvoucher->aVoucher()) {
        $userData = $authsaml->sAuth();
        echo "var trackingCode = '" . $functions->getTrackingCode($userData['saml_uid_attribute']) . "';";
    } else {
        echo "var trackingCode = '" . $functions->getTrackingCode() . "';";
    }

    if (isset($_REQUEST['vid'])) {
        echo "\nvar vid = '" . htmlspecialchars($_REQUEST['vid']) . "';\n";
    }
?>

// start document ready
$(function() {

    getDatePicker();

    // set dialog cancel upload
    $("#dialog-cancel").dialog({ autoOpen: false, height: 140, width: 350, modal: true,
        buttons: {
            'uploadconfirmyesBTN': function() {
                location.reload(true);

            },
            'uploadconfirmnoBTN': function() {
                $( this ).dialog( "close" );
            }
        }
    });

    $('.ui-dialog-buttonpane button:contains(uploadconfirmnoBTN)').attr("id","btn_uploadconfirmno");
    $('#btn_uploadconfirmno').html('<?php echo lang("_NO") ?>')
    $('.ui-dialog-buttonpane button:contains(uploadconfirmyesBTN)').attr("id","btn_uploadconfirmyes");
    $('#btn_uploadconfirmyes').html('<?php echo lang("_YES") ?>')

    // default auth error dialogue
    $("#dialog-autherror").dialog({ autoOpen: false, height: 240,width: 350, modal: true,title: "",
        buttons: {
            '<?php echo lang("_OK") ?>': function() {
                location.reload();
            }
        }
    });

    // default error message dialogue
    $("#dialog-default").dialog({ autoOpen: false, height: 140, height: 200, modal: true,title: "Error",
        buttons: {
            '<?php echo lang("_OK") ?>': function() {
                $("#dialog-default").html("");
                $( this ).dialog( "close" );
            }
        }
    });


    $('.ui-dialog-buttonpane').find('button:contains(uploadcancelBTN)').attr("id","btn_uploadcancel");
    $('#btn_uploadcancel').html('<?php echo lang("_CANCEL") ?>');

    // Display flash upload button
    $("#uploadstandard").show();


    // autocomplete
    var availableTags = [<?php  echo (isset($config["autocomplete"]) && $config["autocomplete"])?  $functions->uniqueemailsforautocomplete():  ""; ?>];

    function split( val ) {
        return val.split( /,\s*/ );
    }
    function extractLast( term ) {
        return split( term ).pop();
    }

    $( "#fileto" )
        // don't navigate away from the field on tab when selecting an item
        .bind( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                $( this ).data( "uiAutocomplete" ).menu.active ) {
                event.preventDefault();
            }
        })
        .autocomplete({
            minLength: 0,
            source: function( request, response ) {
                // delegate back to autocomplete, but extract the last term
                response( $.ui.autocomplete.filter(
                    availableTags, extractLast( request.term ) ) );
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                var terms = split( this.value );
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push( ui.item.value );
                // add placeholder to get the comma-and-space at the end
                terms.push( "" );
                this.value = terms;//.join( ", " );
                return false;
            }
        });
    // end autocomplete

    // end document ready
});

// toggle AUP display
function toggleTOG()
{
    if( $("#tog").is(":visible") ) {
        $("#tog").hide();
    } else {
        $("#tog").show();
    }
}

function hidemessages()
{
    $("#fileto_msg").hide();
    $("#expiry_msg").hide();
    $("#maxemails_msg").hide();
    $("#file_msg").hide();
    $("#aup_msg").hide();
}
// --------------------------
// Validation functions
// --------------------------
function validateforflash(fname,fsize)
{
    // remove previous validation messages
    hidemessages();

    var validate = true;

    if(!validate_fileto() ){validate = false;}	// validate emails
    if(aup == '1') // check if AUP is required
    {
        if(!validate_aup() ){validate = false;}		// check AUP is selected
    }
    if(!validate_expiry() ){validate = false;}	// check date
    // validate with server
    if(validate) {
        $("#uploadbutton").find("a").attr("onclick", ""); // prevent double clicks to start extra uploads
        var query = $("#form1").serializeArray(), json = {};
        for (i in query) { json[query[i].name] = query[i].value; }
        // add file information fields
        json["fileoriginalname"] = fname;
        json["filesize"] = parseInt(fsize);
        json["vid"] = vid;
        json["filetrackingcode"] = trackingCode;
        json["filegroupid"] = groupid;

        $.ajax({
            type: "POST",
            url: "fs_upload.php?type=validateupload&vid="+vid,
            data: {myJson:  JSON.stringify(json)}
            ,success:function( data ) {
                if(data == "") {
                    alert("No response from server");
                    return;
                }
                if(data == "ErrorAuth")
                {
                    $("#dialog-autherror").dialog("open");
                    return;
                }
                var data =  parseJSON(data);
                if(data.errors)
                {
                    $.each(data.errors, function(i,result){
                        if(result == "err_token") {$("#dialog-tokenerror").dialog("open");} // token missing or error
                        if(result == "err_tomissing") { $("#fileto_msg").show();} // missing email data
                        if(result == "err_expmissing") { $("#expiry_msg").show();} // missing expiry date
                        if(result == "err_exoutofrange") { $("#expiry_msg").show();} // expiry date out of range
                        if(result == "err_invalidemail") { $("#fileto_msg").show();} // 1 or more emails invalid
                        if(result == "err_invalidfilename") { $("#file_msg").show();} //  invalid filename
                        if(result == "err_invalidextension") { $("#extension_msg").show();} //  invalid extension
                        if(result == "err_nodiskspace") { errorDialog(errmsg_disk_space);}
                    });
                    $("#uploadbutton").find("a").attr("onclick", "validate()"); // re-activate upload button
                }
                if(data.status && data.status == "complete")
                {
                    $("#fileToUpload").hide();// hide Browse
                    $("#selectfile").hide();// hide Browse message
                    $("#uploadbutton").hide(); // hide upload
                    $("#cancelbutton").show(); // show cancel
                    // show upload progress dialog

                    startTime = new Date().getTime();
                    // no error so use reuslt as current bytes uploaded for file resume
                    vid = data.vid;
                    // hide upload button
                    openProgressBar(fname);
                    getFlexApp("filesenderup").returnVoucher(vid)
                } else {
                    getFlexApp("filesenderup").returnMsg(false)
                }
            },error:function(xhr,err){
                alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
                alert("responseText: "+xhr.responseText);
            }
        })
    }
}

function openProgressBar(fname) {
    $("#dialog-uploadprogress").dialog({
        title: "<?php echo lang("_UPLOAD_PROGRESS") ?>: " + fname,
        minWidth: 400,
        minHeight: 250,
        buttons: {
            'Pause': function () {
                //TODO
            },
            'Suspend': function () {
                //TODO
            },
            'Cancel Upload': function() {
                $( "#dialog-confirm" ).dialog({
                    resizable: false,
                    height:140,
                    modal: true,
                    buttons: {
                        "Yes": function() {
                            location.reload();
                        },
                        "No": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
            }
        }
    });
}

//Validate AUP
function validate_aup()
{
    if(	$("#aup").is(":checked"))
    {
        $("#aup_msg").hide();
        return true;
    } else {
        $("#aup_msg").show();
        return false;
    }
}

// validate extension - returns true if valid
function validateextension(filename)
{
    for ( var i=0, len=banextensions.length; i<len; ++i ){
        if(filename.split('.').pop() == banextensions[i])
        {
            return false;
        }
    }
    return true;
}

// flex file information check
function fileInfo(name,size)
{
    $("#uploadbutton").hide();
    fileMsg("");
    if(size < 1)
    {
        getFlexApp("filesenderup").returnMsg("hideupload");
        $("#fileInfoView").hide();
        fileMsg("<?php echo lang("_INVALID_FILESIZE_ZERO") ?>");
        return false;
    }
    if(size > maxFLASHuploadsize)
    {
        fileMsg("<?php echo lang("_INVALID_TOO_LARGE_1") ?> " + readablizebytes(maxFLASHuploadsize) + ". <?php echo lang("_INVALID_SIZE_USEHTML5") ?> ");
        $("#fileInfoView").hide();
        return false;
    }
    if (validatefilename(name))
    {
        $("#fileInfoView").show();
        $("#n").val(name);
        $("#total").val(size);
        $("#fileName").val(name);
        $("#fileName").html(nameLang + ": " + name);
        $("#fileSize").html(sizeLang + ": " + readablizebytes(size));
        $("#uploadbutton").show();
    } else {
        $("#fileInfoView").hide();
        $("#uploadbutton").hide();
    }
}


function uploadcomplete(name,size)
{
    $("#fileName").val(name);
    // ajax form data to fs_upload.php
    $.ajax({
        type: "POST",
        url: "fs_upload.php?type=uploadcomplete&vid="+vid//,
        //data: {myJson:  JSON.stringify(json)}
        ,success:function( data ) {

            var data =  parseJSON(data);

            if(data.errors)
            {
                $.each(data.errors, function(i,result){
                    if(result == "err_token") { $("#dialog-tokenerror").dialog("open");} // token missing or error
                    if(result == "err_cannotrenamefile") { window.location.href="index.php?s=uploaderror";} //
                    if(result == "err_emailnotsent") { window.location.href="index.php?s=emailsenterror";} //
                    if(result == "err_filesizeincorrect") { window.location.href="index.php?s=filesizeincorrect";} //
                })
            } else {
                if(data.status && data.status == "complete"){window.location.href="index.php?s=complete";}
                if(data.status && data.status == "completev"){window.location.href="index.php?s=completev";}
            }
        },error:function(xhr,err){
            // error function to display error message e.g.404 page not found
            ajaxerror(xhr.readyState,xhr.status,xhr.responseText);
        }
    });
}

function uploaderror(name,size)
{
    errorDialog("<?php echo lang("_ERROR_UPLOADING_FILE") ?> "+name+":"+size);
}

// check browser type
function getFlexApp(appName)
{
    if (navigator.appName.indexOf ("Microsoft") !=-1)
    {
        if(window[appName] == undefined)
        {
            return document[appName];
        } else {
            return window[appName];
        }
    }
    else
    {
        return document[appName];
    }
}

function validatefilename(name)
{

    if(!validateextension(name))
    {
        fileMsg("<?php echo lang("_INVALID_FILE_EXT")." ".lang("_SELECT_ANOTHER_FILE") ?>");
        return false;
    }
    if (/^[^\\\/:;\*\?\"<>|]+(\.[^\\\/:;\*\?\"<>|]+)*$/.test(name))
    {
        return true;
    } else {
        fileMsg("<?php echo lang("_INVALID_FILE_NAME") ?>");
        return false;
    }
}

function validate()
{
    getFlexApp("filesenderup").returnMsg("validatebeforeupload");
}

function errorDialog(msg)
{
    $("#dialog-default").html(msg);
    $("#dialog-default").dialog("open")
}


function keepMeAlive()
{
    $.ajax({
        url: "keepalive.php" + '?x=' + escape(new Date()),
        success: function(data) {
        }
    });
}
</script>

<div id="box"> <?php echo '<div id="pageheading">'.lang("_UPLOAD").'</div>'; ?>
    <form id="form1" enctype="multipart/form-data" method="post" action="fs_uploadit.php">
        <table width="100%" border="0">
            <tr>
                <td width="130" class=" mandatory" id="upload_to"><?php echo lang("_TO") ; ?>:</td>
                <td colspan="2" valign="middle"><input name="fileto" title="<?php echo lang("_EMAIL_SEPARATOR_MSG") ; ?>" type="text" id="fileto" onchange="validate_fileto()"/>
                    <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
                    <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang("_MAXEMAILS"); ?> <?php echo $config['max_email_recipients'] ?>.</div>
                </td>
                <td colspan="2" rowspan="4" align="center" valign="top"><table width="100%" border="0">
                        <tr>
                            <td width="25"><img src="images/num_1.png" alt="1" width="25" height="25" hspace="6" border="0" align="left" /></td>
                            <td align="left"><span class="forminstructions"><?php echo lang("_STEP1"); ?></span></td>
                        </tr>
                        <tr>
                            <td><img src="images/num_2.png" alt="2" width="25" height="25" hspace="6" border="0" align="left" /></td>
                            <td align="left"><span class="forminstructions"><?php echo lang("_STEP2"); ?></span></td>
                        </tr>
                        <tr>
                            <td><img src="images/num_3.png" alt="3" width="25" height="25" hspace="6" border="0" align="left" /></td>
                            <td align="left"><span class="forminstructions"><?php echo lang("_STEP3"); ?></span></td>
                        </tr>
                        <tr>
                            <td><img src="images/num_4.png" alt="4" width="25" height="25" hspace="6" border="0" align="left" /></td>
                            <td align="left"><span class="forminstructions"><?php echo lang("_STEP4"); ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">&nbsp;</td>
                        </tr>
                    </table></td>
            </tr>
            <tr>
                <td class=" mandatory" id="upload_from"><?php echo lang("_FROM"); ?>:</td>
                <td colspan="2"><?php
                    if ( count($senderemail) > 1 ) {
                        echo "<select name=\"filefrom\" id=\"filefrom\">\n";
                        foreach($senderemail as $email) {
                            echo "<option>$email</option>\n";
                        }
                        echo "</select>\n";
                    } else {
                        echo "<div id=\"visible_filefrom\">".$senderemail[0]."</div>" . "<input name=\"filefrom\" type=\"hidden\" id=\"filefrom\" value=\"" . $senderemail[0] . "\" />\n";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="" id="upload_subject"><?php echo lang("_SUBJECT"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
                <td colspan="2"><input name="filesubject" type="text" id="filesubject" />
                </td>
            </tr>
            <tr>
                <td class="" id="upload_message"><?php echo lang("_MESSAGE"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
                <td colspan="2"><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
            </tr>
            <tr>
                <td class=" mandatory" id="upload_expirydate"><?php echo lang("_EXPIRY_DATE"); ?>:
                    <input type="hidden" id="fileexpirydate" name="fileexpirydate" value="<?php echo date($lang['datedisplayformat'],strtotime("+".$config['default_daysvalid']." day"));?>" /></td>
                <td colspan="2"><input id="datepicker" name="datepicker" title="<?php echo lang('_DP_dateFormat'); ?>" onchange="validate_expiry()" />
                    <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div>
                </td>
                <td colspan="2" align="center" valign="top"></td>
            </tr>
            <tr>
                <td class=" mandatory"><div id="selectfile"><?php echo lang("_SELECT_FILE"); ?>:</div></td>
                <td colspan="2"><div id="uploadstandard" style="display:none">
                        <script language="JavaScript" type="text/javascript">
                            <!--
                            // Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
                            var hasProductInstall = DetectFlashVer(6, 0, 65);

                            // Version check based upon the values defined in globals
                            var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
                            if(!html5) {
                                if ( hasProductInstall && !hasRequestedVersion ) {
                                    // DO NOT MODIFY THE FOLLOWING FOUR LINES
                                    // Location visited after installation is complete if installation is required
                                    var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
                                    var MMredirectURL = window.location;
                                    document.title = document.title.slice(0, 47) + " - Flash Player Installation";
                                    var MMdoctitle = document.title;
                                    AC_FL_RunContent(
                                        "src", "lib/swf/playerProductInstall",
                                        "FlashVars", "<?php echo $flashVARS ?>",
                                        "width", "300",
                                        "height", "30",
                                        "align", "middle",
                                        "id", "filesenderup",
                                        "quality", "high",
                                        "bgcolor", "#ffffff",
                                        "name", "filesenderup",
                                        "allowScriptAccess","sameDomain",
                                        "type", "application/x-shockwave-flash",
                                        "pluginspage", "http://www.adobe.com/go/getflashplayer"
                                    );
                                } else if (hasRequestedVersion) {
                                    // if we've detected an acceptable version
                                    // embed the Flash Content SWF when all tests are passed
                                    AC_FL_RunContent(
                                        "src", "swf/filesenderup",
                                        "FlashVars", "<?php echo $flashVARS ?>",
                                        "width", "300",
                                        'wmode',"transparent",
                                        "height", "30",
                                        "align", "middle",
                                        "id", "filesenderup",
                                        "quality", "high",
                                        "bgcolor", "#ffffff",
                                        "name", "filesenderup",
                                        "allowScriptAccess","sameDomain",
                                        "type", "application/x-shockwave-flash",
                                        "pluginspage", "http://www.adobe.com/go/getflashplayer"
                                    );

                                } else {  // flash is too old or we can't detect the plugin
                                    var alternateContent = '<div id="errmessage" align="center"><br />This application requires Flash for uploading files.<br /><br />'
                                        + 'To install Flash Player go to <a href="http://www.adobe.com" target="_blank">www.adobe.com<a>.<br /> <br /> '
                                        + '</div>';
                                    $("#content").html(alternateContent);
                                }
                            }
                            // -->
                        </script>
                        <div id="uploadstandardspinner" style="padding-top:10px;display:none"><img src="images/ajax-loader-sm.gif" alt="" border="0" align="left" style="padding-right:6px" /><?php echo lang("_UPLOADING_WAIT"); ?></div>
                        <br />
                    </div>
                    <div id="file_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE"); ?></div>
                    <div id="extension_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE_EXT"); ?></div>
                </td>
                <td colspan="2" align="center" valign="top"><div id="html5text"></div></td>
            </tr>
            <tr id="fileInfoView" style="display:none">
                <td></td>
                <td colspan="2">
                    <div>
                        <div id="fileName"></div>
                        <div id="fileSize"></div>
                    </div>
                </td>
                <td colspan="2" align="center" valign="top">&nbsp;</td>
            </tr>
            <?php if ($config["AuP"]) {?>
                <tr>
                    <td class=""></td>
                    <td><input name="aup" type="checkbox" id="aup" onchange="validate_aup()" <?php echo ($config["AuP_default"] ) ? 'checked="checked"' : ""; ?> <?php echo (isset($_SESSION["aup"]) && !$authvoucher->aVoucher() ) ? 'checked="checked"' : ""; ?> value="true" />
                    </td>
                    <td>
                        <div id="aup_label" onclick="toggleTOG()" style="cursor:pointer;"><?php echo lang("_ACCEPTTOC"); ?> [<font color="#666666"><?php echo lang("_SHOWHIDE"); ?></font>]</div>
                        <div id="aup_msg" class="validation_msg" style="display: none"><?php echo lang("_AGREETOC"); ?></div>
                        <div id="tog" style="display:none"> <?php echo lang("_AUPTERMS"); ?> </div>
                    </td>
                    <td colspan="2" align="center" valign="top">&nbsp;</td>
                </tr>
            <?php } ?>
            <tr>
                <td></td>
                <td colspan="2"><div class="menu" id="uploadbutton" style="display:none"><a href="#" onclick="validate()"><?php echo lang("_SEND"); ?></a></div></td>
            </tr>

        </table>
        <input type="hidden" id="filevoucheruid" name="filevoucheruid" value="<?php echo $voucherUID; ?>" />
        <input type="hidden" name="vid" id="vid" value="<?php echo $voucherUID; ?>" />
        <input type="hidden" name="total" id="total" value="" />
        <input type="hidden" name="n" id="n" value="" />
        <input type="hidden" id="filestatus" name="filestatus" value="<?php echo $filestatus; ?>" />
        <input type="hidden" name="loadtype" id="loadtype" value="standard" />
        <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION["s-token"])) ?  $_SESSION["s-token"] : "";?>" />
    </form>
</div>
<div id="dialog-default" style="display:none" title=""> </div>
<div id="dialog-cancel" style="display:none" title="<?php echo lang("_CANCEL_UPLOAD"); ?>"><?php echo lang("_ARE_YOU_SURE"); ?></div>

<div id="dialog-uploadprogress" style="display:none;">
    <div style="width:100%; height:42px; margin:auto;">
        <div id="spinner" ></div>
        <div id="bar" style="width:90%; float:right;" >
            <div id="progress_container" class="fileBox">
                <span class="filebox_string" id="progress_string" style="text-align: center"></span>
                <div class="progress_bar" id="progress_bar"></div>
            </div>
        </div>
    </div>

    <p id="totalUploaded"></p>
    <p id="averageUploadSpeed"></p>
    <p id="timeRemaining"></p>
</div>

<div id="dialog-autherror" title="<?php echo lang($lang["_MESSAGE"]); ?>" style="display:none"><?php echo lang($lang["_AUTH_ERROR"]); ?></div>
