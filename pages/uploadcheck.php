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
 * Upload check: detects browser HTML5 support and redirects to appropriate upload page (Flash or HTML5).
 * ---------------------------------
 */

// Check if the HTML5 support status is specified in the URL and if so, include the correct file.
if (isset($_REQUEST['html5'])) {
    if ($_REQUEST['html5'] == 'true') {
        require_once('multiupload.php');
    } else {
        require_once('upload.php');
    }

    return; // Needed to avoid infinite redirect loop.
}
?>

<script type="text/javascript">
    // URL parameter was not set. Check HTML5 support in javascript and set the parameter.
    $(function () {
        html5 = (window.File && window.FileReader && window.FileList && window.Blob && window.FormData) ? 'true' : 'false';

        // Redirect back to originating page with HTML5 support status added as a URL parameter.
        if (window.location.search.indexOf('?') == -1) {
            window.location.replace(window.location + '?html5=' + html5);
        } else {
            window.location.replace(window.location + '&html5=' + html5);
        }
    });
</script>
