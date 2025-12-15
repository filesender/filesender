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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 */
class DatabaseBulk
{
    protected $flat_data = [];
    protected $c = 0;
    protected $cmax = 50;
    protected $batch_size = 50;
    protected $tuple_count = 0;
    protected $columns = [];
    protected $placeholders  = "";
    protected $values_clause = "";
    protected $table_name = "";
    protected $sql = "";
    protected $stmt = null;
    protected $tuples_added = 0;
    public function __construct( $table_name, $columns, $tuple_count )
    {
        $this->table_name  = $table_name;
        $this->columns     = $columns;
        $this->tuple_count = $tuple_count;

        $this->cmax = ceil(60000 / count($this->columns));
        
    }

    private function prepareStmt()
    {
        $this->batch_size = $this->tuple_count;
        if( $this->batch_size > $this->cmax ) {
            $this->batch_size = $this->cmax;
        }
        if( $this->tuples_added + $this->batch_size >= $this->tuple_count )
        {
            $this->batch_size = $this->tuple_count - $this->tuples_added;
        }
        $this->placeholders  = implode(',', array_fill(0, count($this->columns), '?'));
        $this->values_clause = implode(',', array_fill(0, $this->batch_size, "(" . $this->placeholders . ")"));
        $this->sql = "INSERT INTO " . $this->table_name . "  (" . implode(',', $this->columns) . ") VALUES " . $this->values_clause;
        $this->stmt = DBI::prepare($this->sql);
    }
    
    public function begin()
    {
        DBI::beginTransaction();
        $this->prepareStmt();        
    }
    public function commit()
    {
        if( $this->c ) {
            $this->stmt->execute($this->flat_data);
        }
        DBI::commit();
    }

    public function add( $row )
    {
        array_push($this->flat_data, ...$row );
        $this->c++;
        $this->tuples_added++;
        Logger::debug("DatabaseBulk add() count " . $this->c . " of cmax " . $this->cmax . " added " . $this->tuples_added );
        if( $this->c >= $this->cmax )
        {
            $this->stmt->execute($this->flat_data);
            $this->flat_data = [];
            $this->c = 0;
            if( $this->tuples_added + $this->batch_size >= $this->tuple_count ) {
                $this->prepareStmt();        
            }
        }
    }
    
    
}
