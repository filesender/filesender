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

// --------------------------------
// Exception classes for database errors.
// --------------------------------
class DbException extends Exception
{
}

class DbConnectException extends DbException
{
}

// --------------------------------
// Functions for database connection. Uses PDO to allow multiple database types.
// Uses config db_ settings or override using single line Config::get('dsn').
// --------------------------------
class DB
{
    private static $instance = null;
    public $connection = null;

    public static function getInstance()
    {
        // Check for both equality and type.
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function prepare($query)
    {
        if (!is_string($query) || empty($query)) {
            logEntry('Invalid query ' . $query, 'E_ERROR');
            displayError(lang('_ERROR_CONTACT_ADMIN'), 'Invalid query: ' . $query);
            exit;
        }

        $connection = $this->connect();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection->prepare($query);
    }

    public function execute(PDOStatement $statement)
    {
        try {
            $statement->execute();
        } catch (PDOException $e) {
            logEntry($e->getMessage(), 'E_ERROR');
            displayError(lang('_ERROR_CONTACT_ADMIN'), $e->getMessage());
            exit;
        }

        return $statement;
    }

    // --------------------------------
    // Initialises a database connection from config settings.
    // --------------------------------
    public function connect()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $dsn = $this->initDSN();

        try {
            $this->connection = new PDO($dsn, Config::get('db_username'), Config::get('db_password'));
        } catch (PDOException $e) {
            logEntry($e->getMessage(), 'E_ERROR');
            displayError(lang('_ERROR_CONTACT_ADMIN'), $e->getMessage());
            exit;
        }

        return $this->connection;
    }

    // --------------------------------
    // Set up and return the data source name.
    // --------------------------------
    public function initDSN()
    {
        // Use single line DSN if it exists.
        if (Config::exists('dsn')) {
            if (!Config::exists('db_username') || !Config::exists('db_password')) {
                throw new DbException ('Incomplete parameter specification for database, username and password are required');
            }

            return Config::get('dsn');
        }

        // Default to postgresql if no db_type is specified in config.
        $db_type = Config::get('db_type');
        if (!$db_type) $db_type = 'pgsql';

        // Sanity check.
        if (!Config::exists('db_host') || ! Config::exists('db_database') 
            || !Config::exists('db_username') || ! Config::exists('db_password')) {
            throw new DbException ('Incomplete parameter specification for database, please check your config.php');
        }

        // Create data source name for PDO.
        $dbType = Config::get('db_type');
        $dbHost = Config::get('db_host');
        $dbName = Config::get('db_database');

        return "$dbType:host=$dbHost;dbname=$dbName";
    }
}

