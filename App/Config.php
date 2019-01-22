<?php
namespace App;
/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{
    /**
     * Database host
     * @var string
     */
    const DB_HOST = 'localhost';
    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'motarjem1';
    /**
     * Database user
     * @var string
     */
    const DB_USER = 'coderguy';
    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = 'mgmehdi@159';
    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = true;

    const ENCRYPTION_KEY="c8tUbkNbXDQcuKL3";
    const ENCRYPTION_LENGTH=8;
    const BASE_URL="http://localhost:8080";
    const VERIFY_EMAIL_KEY="c8tUbkNbXDQcuKL3";
}