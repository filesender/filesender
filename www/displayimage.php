<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
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
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
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
 * Displays banner image config/banner.png 
 * - if it doesnt exist then use from www/banner.png 
 * --------------------------------------
 * Displays an image based on custom image location or default image location
 * config/banner.jpg or default banner.jpg
 * custom overides default file
 * have to use this function as the config file banner is outside the web folder 
 */
$filesenderbase = dirname(dirname(__FILE__));

$customimage = "$filesenderbase/config/banner.png";
$defaultimage = "$filesenderbase/www/banner.png"; 

displayimage($customimage,$defaultimage);

function displayimage($customimage,$defaultimage)
{
	$displayimage = "";

	// check if default image exists  
	if(file_exists($defaultimage) && is_file($defaultimage)) {
	$displayimage = $defaultimage;
	}

	// check if custom image exists
	if(file_exists($customimage) && is_file($customimage)) {

	// if custom exists then overwrite default display image  
	 $displayimage = $customimage; 
	}
	if (!$displayimage == "") 
	{

	// Make sure the file is an image
		$imgData = getimagesize($displayimage);
		if($imgData) {
		// Set the appropriate content-type
		// and provide the content-length.
	
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
		header("Content-Type: image/jpg");
		header("Content-length: " . filesize($displayimage));
		
		// Print the image data
		readfile($displayimage);

		}  
	}
	return;
}
?>
