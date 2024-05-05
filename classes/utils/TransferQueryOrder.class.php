<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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


/**
 * Class containing transfer table sort options
 */
class TransferQueryOrder
{

    // These are the columns in an SQL table or view
    const COLUMN_CREATED = 'created';
    const COLUMN_EXPIRES = 'expires';
    const COLUMN_SIZE    = 'size';
    const COLUMN_RECIPIENTS = 'recipients';
    const COLUMN_ID = 'id';
    const COLUMN_FILE = 'file';
    const COLUMN_DOWNLOAD = 'download';

    // These are the only valid valules for CGI parameter to select sort ordering.
    // there must be a COLUMN_NAME plus _desc in these string values for all the above
    // otherwise functions like screenArrowHTML() will not work as expected.
    const SORT_EXPIRES_DESC = 'expires_desc';
    const SORT_EXPIRES_ASC  = 'expires_asc';
    const SORT_CREATED_DESC = 'created_desc';
    const SORT_CREATED_ASC  = 'created_asc';
    const SORT_SIZE_DESC    = 'size_desc';
    const SORT_SIZE_ASC     = 'size_asc';
    const SORT_RECIPIENTS_DESC = 'recipients_desc';
    const SORT_RECIPIENTS_ASC  = 'recipients_asc';
    const SORT_ID_DESC         = 'id_desc';
    const SORT_ID_ASC          = 'id_asc';
    const SORT_FILE_DESC       = 'file_desc';
    const SORT_FILE_ASC        = 'file_asc';
    const SORT_DOWNLOAD_DESC   = 'download_desc';
    const SORT_DOWNLOAD_ASC    = 'download_asc';
    
    protected $usedSort     = 'created_desc'; // default, this is explicitly cleaned from the $cgiparam
    protected $columnname   = 'created';
    protected $orderclause  = ' created DESC';
    protected $whereclause  = '';
    protected $viewname     = null;

    protected function __construct($cgiparam = 'transfersort')
    {
        // we validate $order by explicitly checking for constants
        // and then throw it away because it came from CGI
        $order = Utilities::arrayKeyOrDefault( $_GET, $cgiparam, 'created_desc' );
        
        switch($order) {
            case self::SORT_EXPIRES_DESC:
                $this->usedSort = $order;
                $this->columnname = self::COLUMN_EXPIRES;
                $this->orderclause = ' expires DESC';
                break;
            case self::SORT_EXPIRES_ASC:
                $this->usedSort = $order;
                $this->columnname = self::COLUMN_EXPIRES;
                $this->orderclause = ' expires ASC';
                break;
            case self::SORT_CREATED_DESC:
                $this->usedSort = $order;
                $this->columnname = self::COLUMN_CREATED;
                $this->orderclause = ' created DESC';
                break;
            case self::SORT_CREATED_ASC:
                $this->usedSort = $order;
                $this->columnname = self::COLUMN_CREATED;
                $this->orderclause = ' created ASC';
                break;
            case self::SORT_SIZE_DESC:
                $this->usedSort = $order;
                $this->viewname    = 'transferssizeview';
                $this->orderclause = ' size DESC, created DESC';
                $this->columnname  = self::COLUMN_SIZE;
                break;
            case self::SORT_SIZE_ASC:
                $this->usedSort = $order;
                $this->viewname    = 'transferssizeview';
                $this->orderclause = ' size ASC, created DESC ';
                $this->columnname  = self::COLUMN_SIZE;
                break;
            case self::SORT_RECIPIENTS_DESC:
                $this->usedSort = $order;
                $this->viewname    = 'transfersrecipientview';
                $this->orderclause = ' recipientemail DESC, created DESC ';
                $this->columnname  = self::COLUMN_RECIPIENTS;
                break;
            case self::SORT_RECIPIENTS_ASC:
                $this->usedSort = $order;
                $this->viewname    = 'transfersrecipientview';
                $this->orderclause = ' recipientemail ASC, created DESC ';
                $this->columnname  = self::COLUMN_RECIPIENTS;
                break;
            case self::SORT_ID_DESC:
                $this->usedSort = $order;
                $this->orderclause = ' id DESC, created DESC ';
                $this->columnname  = self::COLUMN_ID;
                break;
            case self::SORT_ID_ASC:
                $this->usedSort = $order;
                $this->orderclause = ' id ASC, created DESC ';
                $this->columnname  = self::COLUMN_ID;
                break;
            case self::SORT_FILE_DESC:
                $this->usedSort    = $order;
                $this->viewname    = 'transfersfilesview';
                $this->orderclause = ' filename DESC, created DESC ';
                $this->columnname  = self::COLUMN_FILE;
                break;
            case self::SORT_FILE_ASC:
                $this->usedSort    = $order;
                $this->viewname    = 'transfersfilesview';
                $this->orderclause = ' filename ASC, created DESC ';
                $this->columnname  = self::COLUMN_FILE;
                break;
            case self::SORT_DOWNLOAD_DESC:
                $this->usedSort    = $order;
                $this->orderclause = ' download_count DESC, created DESC ';
                $this->columnname  = self::COLUMN_DOWNLOAD;
                break;
            case self::SORT_DOWNLOAD_ASC:
                $this->usedSort    = $order;
                $this->orderclause = ' download_count ASC, created DESC ';
                $this->columnname  = self::COLUMN_DOWNLOAD;
                break;
                
            default:
                $this->usedSort = self::SORT_CREATED_DESC;
                $this->orderclause = ' created DESC';
                $this->columnname  = self::COLUMN_CREATED;
                break;
        }
    }
    
    public static function create($cgiparam = 'transfersort')
    {
        $obj = new TransferQueryOrder($cgiparam);
        return $obj;
    }
    public static function reverseSort( $v )
    {
        if( strstr( $v, '_desc' )) {
            return str_replace( '_desc', '_asc', $v );
        }
        return str_replace( '_asc', '_desc', $v );
    }

    public function getViewName()
    {
        return $this->viewname;
    }
    public function getWhereClause( $useAnd )
    {
        if( $this->whereclause == '' ) {
            return $this->whereclause;
        }
        $ret = '';
        if( $useAnd ) {
            $ret .= ' AND ';
        }
        $ret .= $this->whereclause;
        return $ret;
    }
    public function getOrderByClause()
    {
        return $this->orderclause;
    }
    public function getUsedSort()
    {
        return $this->usedSort;
    }
    public function clickableSortValue( $col )
    {
        if( $this->columnname == $col ) {
            return self::reverseSort($this->usedSort);
        }
        return $col . '_desc';
    }
    public function screenArrowHTML($sortcol)
    {
        if( $this->columnname != $sortcol ) {
            return "";
        }
        if( strstr( $this->usedSort, '_asc' )) {
            return "<i class='fa fa-angle-up'></i>";
        }
        return "<i class='fa fa-angle-down'></i>";
    }
}
