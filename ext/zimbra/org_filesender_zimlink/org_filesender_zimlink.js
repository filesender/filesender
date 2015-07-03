/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
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

// Zimlet Class
function org_filesender_zimlink() {}

// Make Zimlet class a subclass of ZmZimletBase class - this makes a Zimlet a Zimlet
org_filesender_zimlink.prototype = new ZmZimletBase();
org_filesender_zimlink.prototype.constructor = org_filesender_zimlink;

var org_filesender_zimlink_instance;

// Initialization stage
org_filesender_zimlink.prototype.init = function() {
    org_filesender_zimlink_instance = this;
};

// Detect mail compose view display
org_filesender_zimlink.prototype.onShowView = function(view) {
    // Nothing to do if no HTML5 support
    if(!AjxEnv.supportsHTML5File) return;
    
    // Nothing to do except for mail compose view
    if(view.indexOf(ZmId.VIEW_COMPOSE) < 0) return;
    
    // Handle several compose views
    appCtxt.getCurrentView().org_filesender_zimlink = {files: [], use_filesender: null};
    
    // Replace original attachment handler with our own, keep the old one to call it when file is small enough
    var zimlet = this;
    var original_submit_attachments = appCtxt.getCurrentView()._submitMyComputerAttachments;
    appCtxt.getCurrentView()._submitMyComputerAttachments = function(files, node, isInline) {
        
        if(!files) files = node.files;
        
        // Accumulate files for size computation and potential sending
        for(var i=0; i<files.length; i++) this.org_filesender_zimlink.files.push(files[i]);
        
        // Compute size of all attached files
        var size = 0;
        for(i=0; i<this.org_filesender_zimlink.files.length; i++) {
            var file = this.org_filesender_zimlink.files[i];
            size += file.size || file.fileSize /*Safari*/ || 0;
        }
        
        // Check if max exceeded
        var max_size = appCtxt.get(ZmSetting.MESSAGE_SIZE_LIMIT);
        if(
            (max_size != -1 /* means unlimited */) &&
            (size > max_size)
        ) {
            zimlet.popUseFileSenderDlg();
            
        } else {
            // Max not exceeded, run zimbra attachment handler
            original_submit_attachments.apply(appCtxt.getCurrentView(), arguments);
        }
    };
};

// Ask user wether to use filesender
org_filesender_zimlink.prototype.popUseFileSenderDlg = function() {
    var dialog = this.makeDlg(
        this.getMessage('use_filesender_dlg_title'),
        {width: 300, height: 300},
        '',
        [DwtDialog.OK_BUTTON, DwtDialog.CANCEL_BUTTON]
    );
    
    this.setDialogButton(
        dialog,
        DwtDialog.OK_BUTTON,
        'Oui',
        new AjxListener(this, function() {
            dialog.popdown();
            dialog.dispose();
            
            appCtxt.getCurrentView().org_filesender_zimlink.use_filesender = 'https://filesender-premium.renater.fr/';
            //appCtxt.getCurrentView().org_filesender_zimlink.use_filesender = this.getConfigProperty('serversUrlList');
            
            this.checkFileSenderAuthentication();
        }, dialog)
    );
    
    this.setDialogButton(
        dialog,
        DwtDialog.CANCEL_BUTTON,
        'Non',
        new AjxListener(this, function() {
            dialog.popdown();
            dialog.dispose();
            
            org_filesender_zimlink.showSizeExceededError();
        })
    );
    
    dialog.popup();
};

// Autentication data handler for user profile iframe response (FileSender iframe callback)
org_filesender_zimlink.filesender_user_profile_handler = function(profile) {
    if(!profile.remote_config) {
        org_filesender_zimlink_instance.showError('FileSender does not support remote user access');
        return;
    }
    
    var cfg = profile.remote_config.split('|');
    
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    zdata.remote_config = {url: cfg[0], uid: cfg[1], secret: cfg[2]};
    
    var authentication_data = JSON.parse(this.getUserProperty('authentication_data'));
    
    authentication_data[zdata.use_filesender].remote_config = zdata.remote_config;
    
    this.setUserProperty('authentication_data', JSON.stringify(authentication_data), true);
    
    org_filesender_zimlink_instance.iframe_dialog.popdown();
    org_filesender_zimlink_instance.iframe_dialog.dispose();
};

// Check if we have authentication data for selected FileSender, get it if not
org_filesender_zimlink.prototype.checkFileSenderAuthentication = function() {
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    
    var authentication_data = JSON.parse(this.getUserProperty('authentication_data'));
    
    if(authentication_data[zdata.use_filesender]) {
        // Auth data already known, attach and exit
        appCtxt.getCurrentView().org_filesender_zimlink.remote_config = authentication_data[zdata.use_filesender];
        return;
    }
    
    // No auth data, popup iframe
    
    // Get info about FileSender instance
    var fs_url = zdata.use_filesender;
    if(fs_url.substr(-1) != '/') url += '/';
    var info_url = fs_url + 'rest.php/info';
    
    var proxyUrl = [ZmZimletBase.PROXY, AjxStringUtil.urlComponentEncode(info_url)].join('');
    
    var res = AjxRpc.invoke(null, proxyUrl, null, null, true);
    
    if(!res.success) {
        this.showError('Info getter error');
        return;
    }
    
    var info = JSON.parse(res.text);
    
    var user_url = fs_url + 'rest.php/user?iframe_callback=org_filesender_zimlink.filesender_user_profile_handler';
    
    var domain = fs_url.match(/^(https?:\/\/[^/]+)/)[1];
    
    var logon_url = info.logon_url.match(/^\//) ? domain + info.logon_url : info.logon_url;
    
    var dialog = this.makeDlg(
        this.getMessage('get_filesender_authentication_dlg_title'),
        {width: 800, height: 600},
        '<iframe src="' + logo_url.replace(/__target__/, AjxStringUtil.urlComponentEncode(user_url)) + '" name=""></iframe>',
        [DwtDialog.CANCEL_BUTTON]
    );
    
    this.setDialogButton(
        dialog,
        DwtDialog.CANCEL_BUTTON,
        'Annuler',
        new AjxListener(this, function() {
            dialog.popdown();
            dialog.dispose();
            
            org_filesender_zimlink.showSizeExceededError();
        })
    );
    
    dialog.popup();
    
    org_filesender_zimlink_instance.iframe_dialog = dialog;
};

/*
 * In the Compose view, add the text in parameter at the end of the body, before the signature
 * Params:
 * downloadlink : String containing the filesender download link
 */
org_filesender_zimlink.prototype.addDownloadLink = function(downloadlink) {
	var controller = appCtxt.getCurrentController();
	var view = appCtxt.getCurrentView();
	var i = 0;
	//Original mail body
	var html = [view.getUserText()];
	//Add the download link at the end of the body
	html[i++] = "<br>";
	html[i++] = this.getMessage("downloadlink_label") + downloadlink;
	
	//Add params with keepAttachments to false to clean attachments
	var params = {
			keepAttachments: false,
			action:			controller._action,
			msg:			controller._msg,
			extraBodyText:	html.join("")
	};
	//Reset the body content
	view.resetBody(params);
};

/*
 * Generic function to create a dialog box
 * Params :
 * title : String containing the title of the dialog box
 * size : Array with the params width and height to set the size of the dialog box
 * content : String containing the html inserted in the dialog box
 * listenerYes : Listener object for the Yes button 
 * listenerNo : Listener object for the No button 
 * standardButtons : Array with the list of standardButtons (possible value is DwtDialog.OK_BUTTON, DwtDialog.CANCEL_BUTTON or both).
 * 
 * call example : filesenderZimlet.makeDlg("title", {width:300,height:300}, "content", [DwtDialog.OK_BUTTON, DwtDialog.CANCEL_BUTTON])
 */
org_filesender_zimlink.prototype.makeDlg = function(title, size, content, standardButtons) {
	//Create the frame
	var view = new DwtComposite(this.getShell());
	view.setSize(size.width, size.height);
	view.getHtmlElement().style.overflow = "auto";
	//Add html content in the frame
	view.getHtmlElement().innerHTML = content;

	//pass the title, view and buttons information and create dialog box
	var dialog = this._createDialog({title:title, view:view, standardButtons: standardButtons});
	
	return dialog;
};

/*
 * Customize a ZmDialog button
 * Params :
 * dialog : ZmDialog object
 * buttonId : String containing the id of the button (ex: DwtDialog.OK_BUTTON)
 * text : String containing the displayed text of the button
 * listener : AjxListener object to add to the button
 */
org_filesender_zimlink.prototype.setDialogButton = function(dialog, buttonId, text, listener) {
	var button = dialog.getButton(buttonId);
	button.setText(text);
	dialog.setButtonListener(buttonId, listener);
}

/*
 * Generic function to show an error message
 * Params:
 * msg : String containing the msg in html format to display
 */
org_filesender_zimlink.prototype.showError = function(msg) {
	var msgDlg = appCtxt.getMsgDialog();
	msgDlg.setMessage(msg, DwtMessageDialog.CRITICAL_STYLE);
	msgDlg.popup();
};

// Popup size exceeded error
org_filesender_zimlink.showSizeExceededError = function() {
    var msgDlg = appCtxt.getMsgDialog();
    var errorMsg = AjxMessageFormat.format(ZmMsg.attachmentSizeError, AjxUtil.formatSize(appCtxt.get(ZmSetting.MESSAGE_SIZE_LIMIT)));
    msgDlg.setMessage(errorMsg, DwtMessageDialog.WARNING_STYLE);
    msgDlg.popup();
};
