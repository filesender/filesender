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

/* --------------------------------------
 * Allows overriding of banner or favicon by placing new versions in config/.
 * --------------------------------------
 * Displays an image based on custom image location or default image location.
 * Call using href="displayimage.php?type=imagetype" (e.g. banner).
 * If they exist, custom files will override default versions.
 * Have to use this function as config/ is outside the web folder.
 */
$filesenderBase = dirname(dirname(__FILE__));

if (isset($_REQUEST["type"])) {
    if ($_REQUEST["type"] == "banner") {
        displayImage("$filesenderBase/config/banner.png", "$filesenderBase/www/banner.png");
    } else if ($_REQUEST["type"] == "favicon") {
        displayImage("$filesenderBase/config/favicon.ico", "$filesenderBase/www/favicon.ico");
    }
}

function displayImage($customImage, $defaultImage) {
    $displayImage = "";

    // Check if default image exists.
    if(file_exists($defaultImage) && is_file($defaultImage)) {
        $displayImage = $defaultImage;
    }

    // Check if custom image exists.
    if(file_exists($customImage) && is_file($customImage)) {
        $displayImage = $customImage; // Overwrite default display image
    }

    if ($displayImage != "") {
        // Make sure the file is an image.
        $imgData = getimagesize($displayImage);
        if($imgData) {
            $lastModDate = gmdate("D, d M Y H:i:s", filemtime($displayImage))." GMT";

            // See if this is a conditional get.
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastModDate) {
                header("HTTP/1.0 304 Not Modified");
                exit;
            }

            // Set the appropriate HTTP headers.
            header("Pragma: public");
            header("Last-Modified: ".$lastModDate);
            header("Content-Type: image/jpg");
            header("Content-length: " . filesize($displayImage));

            // Print the image data.
            readfile($displayImage);
        }
    }
}
