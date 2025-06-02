<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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

// We need to take care of < 2.0a config before init, otherwise we'll get a classname conflict ...

$config_file = dirname(__FILE__).'/../../config/config.php';
$config = file_exists($config_file) ? file_get_contents($config_file) : null;

if($config && preg_match('`class\s+config\s+\{`i', $config)) {
    echo 'It seems you have an old config file, lets try to convert it automatically'."\n";
    
    $config = explode("\n", $config);
    
    // Eat up until loadConfig method
    while($config && !preg_match('`function\s+loadConfig\s*\(`', trim($config[0]))) array_shift($config);
    
    // Get lines until return statment
    $lines = array();
    while($config) {
        $line = array_shift($config);
        
        // Ignore array creation
        if(preg_match('`\$config\s*=\s*array\(\);`', trim($line))) continue;
        
        // Stop if return statment
        if(preg_match('`return\s*\$config\s*;`', trim($line))) break;
        
        $lines[] = $line;
    }
    
    // Got no lines ?
    if(!$lines) die('Couldn\'t fetch config from old file, please refer to the manual for how to migrate it by hand');
    
    // Remove empty lines at start/end
    while(!trim($lines[0])) array_shift($lines);
    while(!trim($lines[count($lines)-1])) array_pop($lines);
    
    // Final config
    $config = implode("\n", $lines);
    
    // Backup old file
    if(!copy($config_file, str_replace('config.php', 'config.'.date('YmdHis').'.php', $config_file)))
        die('Couldn\'t backup old config file, please refer to the manual for how to migrate it by hand');
    
    // Save to file
    if($fh = fopen($config_file, 'w')) {
        fwrite($fh, $config);
        fclose($fh);
        
    } else die('Couldn\'t save migrated config, please refer to the manual for how to migrate it by hand');
}


// Init once old config has been taken care of

require_once dirname(__FILE__).'/../../includes/init.php';

Logger::setProcess(ProcessTypes::UPGRADE);


// Start data upgrade

try {
    Upgrader::run();
} catch(Exception $e) {
    die($e->getMessage()."\n".$e->getTraceAsString());
}
