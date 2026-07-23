#!/usr/bin/env php
<?php

/**
 * Allows to get the content of a php-fpm environment configuration file
 */
class PhpFpmEnvironment {
    /**
     * The temporary directory, used in TMP, TEMP and TMPDIR environment variables
     * @var string
     */
    const TMP = '/tmp';

    /**
     * The path where to find executables, where sbin should be excluded if you don't run PHP as root.
     * @var string
     */
    const PATH = '/usr/local/bin:/usr/bin:/bin';

    /**
     * The environment variables to discard
     * @var Array
     */
    const VARIABLES_TO_DISCARD = [
        '_',           // The caller executable script, not pertinent
        'HOME',        // Set correctly by php-fpm
        'TERM',        // Not pertinent in server context
        'MYSQL_ENV_MYSQL_ROOT_PASSWORD', // from --link …:mysql
    ];

    /**
     * Gets an environment array from the current process environment,
     * with PATH and temp variablesfiltered.
     *
     * @return Array
     */
    public static function getEnvironmentVariables () {
        $variables = [];

        foreach ($_ENV as $key => $value) {
            if (!static::mustIgnoreVariable($key)) {
	            $variables[$key] = $value;
            }
        }

        static::addHardcodedEnvironmentVariables($variables);

        return $variables;
    }

    /**
     * Adds hardcoded and always wanted environment variables
     * (path, temporary directory) to the specified array.
     *
     * @paran array $variables the array to add the variables to
     */
    public static function addHardcodedEnvironmentVariables (&$variables) {
        static::addTempEnvironmentVariables ($variables);
        static::addPathEnvironmentVariables ($variables);
    }

    /**
     * Adds temporary directory environment variables to the specified array.
     *
     * @paran array $variables the array to add the variables to
     */
    public static function addTempEnvironmentVariables (&$variables) {
        $variables['TMP'] = static::TMP;
        $variables['TEMP'] = static::TMP;
        $variables['TMPDIR'] = static::TMP;
    }

    /**
     * Adds temporary directory environment variables to the specified array.
     *
     * @paran array $variables the array to add the variables to
     */
    public static function addPathEnvironmentVariables (&$variables) {
        $variables['PATH'] = static::PATH;
    }

    /**
     * Determines if the variable name must be ignored
     *
     * @return bool true if the variable must be ignored; otherwise, false.
     */
    public static function mustIgnoreVariable ($variableName) {
        return in_array($variableName, static::VARIABLES_TO_DISCARD);
    }

    /**
     * Prints the environment
     */
    public static function printConfig () {
        $variables = static::getEnvironmentVariables();

        foreach ($variables as $key => $value) {
            echo 'env["', $key, '"] = "', $value, '"', PHP_EOL;
        }
    }
}

PhpFpmEnvironment::printConfig();
