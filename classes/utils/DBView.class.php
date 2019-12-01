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

/**
 * Base class for database stored objects
 */
class DBView
{
    public static function columnDefinition_age(
        $dbtype,
                                                 $basecolname,
                                                 $viewcolname = ''
    ) {
        if (!strlen($viewcolname)) {
            $viewcolname = $basecolname . "_days_ago";
        }
        if ($dbtype == 'pgsql') {
            return ' , extract(day from now() - ' . $basecolname . ' ) as ' . $viewcolname . ' ';
        }
        if ($dbtype == 'mysql') {
            return ' , DATEDIFF(now(),' . $basecolname . ') as ' . $viewcolname . ' ';
        }
    }
    public static function columnDefinition_is_encrypted(
        $basecolname = 'additional_attributes',
                                                          $viewcolname = 'is_encrypted'
    ) {
        return "  , " . $basecolname . " LIKE '%encryption\":true%' as " . $viewcolname . " ";
    }
    public static function columnDefinition_as_number(
        $dbtype,
                                                       $basecolname,
                                                       $viewcolname = ''
    ) {
        if (!strlen($viewcolname)) {
            $viewcolname = $basecolname . "_as_number";
        }
        if ($dbtype == 'pgsql') {
            return ', cast( '. $basecolname . ' as bigint) as ' . $viewcolname;
        }
        if ($dbtype == 'mysql') {
            return ', cast( '. $basecolname . ' as unsigned) as ' . $viewcolname;
        }
    }
   public static function cast_as_number(
        $dbtype,
        $basecolname
    ) {
        if (!strlen($viewcolname)) {
            $viewcolname = $basecolname . "_as_number";
        }
        if ($dbtype == 'pgsql') {
            return ' cast( '. $basecolname . ' as bigint) ';
        }
        if ($dbtype == 'mysql') {
            return ' cast( '. $basecolname . ' as unsigned) ';
        }
    }    

    public static function columnDefinition_dbconstant( $dbconstantTableName,
                                                        $baseTableColumn,
                                                        $baseTableGeneratedColumn,
                                                        $baseTableName = 'base'
    ) {
        return ', (select description from '.$dbconstantTableName
              .' where '.$dbconstantTableName.'.id = '.$baseTableName.'.'.$baseTableColumn.' limit 1) as ' . $baseTableGeneratedColumn.' ';
    }
    
};
