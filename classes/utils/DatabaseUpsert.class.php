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
 * Database upserts
 *
 * This handles the common case where you want to either insert or update
 * a value in the database and do not know or care if the tuple already exists.
 * 
 * In PostgreSQL this is handled by INSERT .ON CONFLICT DO UPDATE
 *   https://www.postgresql.org/docs/9.5/static/sql-insert.html
 * In MariaDB this is handled by INSERT ... ON DUPLICATE KEY UPDATE
 *   https://mariadb.com/kb/en/library/insert-on-duplicate-key-update/
 */
class DatabaseUpsert
{
    static function upsert( $insertSQL, $updateSQL )
    {
        $dbname = Config::get('db_database');
        $dbtype = Config::get('db_type');
        
        $sql = $insertSQL;
        if ($dbtype == 'pgsql') {
            $sql .= ' ON CONFLICT DO UPDATE ';
        } else if ($dbtype == 'mysql') {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
        } else {
            Logger::haltWithErorr("upsert is not programmed for your database type $dbtype");
        }
        $sql .= $updateSQL;

        Logger::info($sql);
        $statement = DBI::prepare($sql);
        $statement->execute();
    }
}
