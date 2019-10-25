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
 * Database Foreign Key managing
 *
 * description is for showing the user a short item
 * tablename is the base table
 * indexname is the fk name for checking if it already exists
 * basecolumns are the colums in tablename that refer to the other table
 * reference is the table that tablename.basecolumns refer to
 * referencecolumns are the columns in the reference table that the basecolumns refer to
 *
 * For example, from https:*www.postgresql.org/docs/9.2/static/ddl-constraints.html
 *
 * CREATE TABLE t1 (
 *   a integer PRIMARY KEY,
 *   b integer,
 *   c integer,
 *   FOREIGN KEY (b, c) REFERENCES other_table (c1, c2)
 * );
 *
 * tablename = t1, basecolumns = b,c  reference = other_table, referencecolumns = c1,c2
 */
class DatabaseForeignKey
{
    protected $description = null;
    protected $tablename = null;
    protected $indexname = null;
    protected $basecolumns = null;
    protected $reference = null;
    protected $referencecolumns = null;
    protected $fkExists = 0;
    
    public function __construct($description, $tablename, $indexname, $basecolumns, $reference, $referencecolumns)
    {
        $this->description      = $description;
        $this->tablename        = $tablename;
        $this->indexname        = $indexname;
        $this->basecolumns      = $basecolumns;
        $this->reference        = $reference;
        $this->referencecolumns = $referencecolumns;
    }

    /**
     * Check if the FK exists in the database already
     */
    public function exists()
    {
        $this->fkExists = 0;
        $dbname = Config::get('db_database');
        $dbtype = Config::get('db_type');

        // We check the presence of the foreign key
        if ($dbtype == 'pgsql') {
            $statement = DBI::prepare("SELECT COUNT(1) as c FROM INFORMATION_SCHEMA.table_constraints "
                                    ." WHERE lower(constraint_name)=lower('".$this->indexname."')"
                                    ." AND lower(table_name)=lower('".$this->tablename."');");
        } else {
            $sql = "SELECT COUNT(1) as c FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS "
                 . " where CONSTRAINT_SCHEMA = '".$dbname."'"
                 . " AND table_name='".$this->tablename."'"
                 . "  and CONSTRAINT_NAME = '".$this->indexname ."';";
            Logger::info($sql);
            $statement = DBI::prepare($sql);
        }

        $statement->execute(array());
        $data = $statement->fetch();
        if ($data) {
            $this->fkExists = $data['c'];
        }
        
        Logger::info('fk ' . $this->description . ' already have fk ' . $this->fkExists);
        return $this->fkExists;
    }

    /**
     * SQL statement to create the FK
     */
    public function getCreationSQL()
    {
        return 'ALTER TABLE '.$this->tablename.' ADD CONSTRAINT '
              .$this->indexname.' FOREIGN KEY (' . $this->basecolumns . ') '
             . ' REFERENCES '.$this->reference.' (' . $this->referencecolumns . ')  '
             . ' on delete cascade on update restrict ;';
    }

    /**
     * Execute SQL to create the FK
     */
    public function create()
    {
        $sql = $this->getCreationSQL();
        Logger::info("fk SQL is " . $sql);
        ;
        DBI::exec($sql);
    }

    /**
     * Ensure that the FK is in the database. If there is no FK then it is
     * created. If the FK already exists then nothing happens.
     */
    public function ensure()
    {
        try {
            if (!$this->exists()) {
                $this->create();
            }
        } catch (DBIDuplicateException $e) {
            Logger::info("... fk already there!");
        }
    }
};
