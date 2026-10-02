<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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

/**
 * Unknown target type
 */
class ReportUnknownTargetTypeException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct(
            'report_unknown_target_type', // Message to give to the user
             array('type' => $type) // Real message to log
        );
    }
}

/**
 * Unknown format
 */
class ReportOwnershipRequiredException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $selector
     */
    public function __construct($selector)
    {
        parent::__construct(
            'report_ownership_required', // Message to give to the user
             array('selector' => $selector) // Real message to log
        );
    }
}

/**
 * Unknown format
 */
class ReportUnknownFormatException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $format
     */
    public function __construct($format)
    {
        parent::__construct(
            'report_unknown_format', // Message to give to the user
             array('format' => $format) // Real message to log
        );
    }
}

/**
 * No report data matching target
 */
class ReportNothingFoundException extends DetailedException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'report_nothing_found' // Message to give to the user
        );
    }
}

/**
 * Format not available due to requirement not met
 */
class ReportFormatNotAvailableException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $reason
     */
    public function __construct($reason)
    {
        parent::__construct(
            'report_format_not_available', // Message to give to the user
            array('reason' => $reason)
        );
    }
}
