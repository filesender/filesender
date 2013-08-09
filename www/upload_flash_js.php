<?php
?>
<script type="text/javascript">
    function validateforflash(fname,fsize)
    {
        // remove previous validation messages
        hideMessages();

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
                        getFlexApp("filesenderup").returnM^sg(false)
                    }
                },error:function(xhr,err){
                    alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
                    alert("responseText: "+xhr.responseText);
                }
            })
        }
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
        if (validateFileName(name))
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
                    if(data.status && data["gid"] && data.status == "complete"){window.location.href="index.php?s=complete&gid="+data["gid"];}
                    if(data.status && data["gid"] && data.status == "completev"){window.location.href="index.php?s=completev&gid="+data["gid"];}
                }
            },error:function(xhr,err){
                // error function to display error message e.g.404 page not found
                ajaxerror(xhr.readyState,xhr.status,xhr.responseText);
            }
        });
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

</script>
