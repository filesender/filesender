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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

require_once(FILESENDER_BASE.'/lib/random_compat/lib/random.php');


/**
 * Utility functions holder
 */
class Crypto
{

    /**
     * Generate a salt for use in crypto function that is $len
     * bytes long. The salt is encoded for easy storage and transmission
     * and the $len is the length of that encoded string rather than the
     * number of octets of entropy that you desire.
     *
     * Currently the string is encoded as base64 so you will be getting around
     * 75% of len as entropy from the function. The return value might be truncated
     * so base64 decoding is impossible. This is not an issue as it is designed to be
     * used only as a salt and never require decoding.
     */
    public static function generateSaltString($len = 32)
    {
        $v = random_bytes($len);
        $v = base64_encode($v);
        return substr($v, 0, $len);
    }

    /**
     * This uses the formula from page 7 of the OpenFortress security analysis
     * to get a PBKDF2 iteration count for an expected number of years of security
     * from brute force attack.
     */
    public static function getPBKDF2IterationCountForYear( $wantedyear )
    {
        if( $wantedyear < 2010 ) {
            throw new ConfigBadParameterException('getPBKDF2IterationCountForYear() called with invalid year: ' . $wantedyear );
        }
        $baseyear=2009;
        $nbase=1000;
        return ceil($nbase * pow(2.0, ( ($wantedyear - $baseyear)*2/3)));
    }

    
}
