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

if (!defined('FILESENDER_BASE'))        // Require environment (fatal)
    die('Missing environment');

/**
 * Missing file exception
 */
class ConfigFileMissingException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $file path of the required file
     */
    public function __construct($file) {
        parent::__construct(
            'config_file_missing', // Message to give to the user
            array('file' => $file) // Details to log
        );
    }
}

/**
 * Bad parameter exception
 */
class ConfigBadParameterException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $key name of the bad parameter
     */
    public function __construct($key) {
        parent::__construct(
            'config_bad_parameter', // Message to give to the user
            array('parameter' => $key) // Details to log
        );
    }
}

/**
 * Missing parameter exception
 */
class ConfigMissingParameterException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $key name of the missing parameter
     */
    public function __construct($key,$notetoadmin = '') {
        parent::__construct(
            'config_missing_parameter', // Message to give to the user
            array('parameter' => $key, 'noteToAdmin' => $notetoadmin) // Details to log
        );
    }
}

/**
 * Override disabled exception
 */
class ConfigOverrideDisabledException extends DetailedException {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'config_override_disabled'
        );
    }
}

/**
 * Validation of parameter override failed exception
 */
class ConfigOverrideValidationFailedException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $key name of the parameter
     * @param string $validator name of the validator
     */
    public function __construct($key, $validator) {
        parent::__construct(
            'config_override_validation_failed', // Message to give to the user
            null,
            array('parameter' => $key, 'validator' => $validator) // Public info
        );
    }
}

/**
 * Override of parameter not allowed exception
 */
class ConfigOverrideNotAllowedException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $key name of the parameter
     */
    public function __construct($key) {
        parent::__construct(
            'config_override_not_allowed', // Message to give to the user
            array('parameter' => $key) // Details to log
        );
    }
}
