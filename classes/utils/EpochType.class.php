<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2018, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * Handling DBConstantEpochType
 */
class EpochType
{
    public $epochType = DBConstantEpochType::HOUR;
    public $tt = 0;
    
    /**
     * Constructor
     *
     * @param DBConstantEpochType::HOUR etc $epochType The DBConstantEpochType resolution we want
     * @param integer $tt the unix timestamp
     *
     */
    public function __construct( $epochType, $tt )
    {
        $this->epochType = $epochType;

        $et = $epochType;
        $d = getdate($tt);
        $d['seconds'] = 0;
        
        if( $et == DBConstantEpochType::FIFTEEN_MINUTES ) {
            $m = $d['minutes'];
            $m = floor($m/15)*15;
            $d['minutes'] = $m;
        } else if( $et == DBConstantEpochType::HOUR ) {
            $d['minutes'] = 0;
        } else if( $et == DBConstantEpochType::DAY ) {
            $d['minutes'] = 0;
            $d['hours'] = 0;
        } else if( $et == DBConstantEpochType::WEEK ) {
            $d['minutes'] = 0;
            $d['hours'] = 0;
            $d['mday'] -= $d['wday'];
        } else if( $et == DBConstantEpochType::MONTH ) {
            $d['minutes'] = 0;
            $d['hours'] = 0;
            $d['mday'] = 1;
        } else if( $et == DBConstantEpochType::YEAR ) {
            $d['minutes'] = 0;
            $d['hours'] = 0;
            $d['mday'] = 1;
            $d['mon'] = 1;
        } else {
            throw new EpochTypeUnknownException($epochType);
        }
        
        $this->tt = mktime( $d['hours'],$d['minutes'],$d['seconds'],
                            $d['mon'],$d['mday'],$d['year']);
    }


    /**
     * If you start with an HOUR resolution you can broaden that to DAY
     * or week by passing the desired type here. If you pass null then the next
     * broader type is used.
     * If there is no next broader type then null is returned.
     * 
     * Note that this is a one way operation, the time value passed to the constructor
     * will be modified to be the first second on the time interval you have nominated.
     * So you can not go back from DAY to HOUR as the EpochType that is returned from this
     * function will have lost the exact time value in the cast.
     */
    public function broadenTo( $epochType = null )
    {
        if( !$epochType ) {
            $epochType = DBConstantEpochType::broaden( $this->epochType );
        }
        if( !$epochType ) {
            return null;
        }
        return new self( $epochType, $this->tt );
    }
    public function broaden()
    {
        return $this->broadenTo( null );
    }
}
