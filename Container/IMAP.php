<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Jeroen Houben <jeroen@terena.nl>                             |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once "Auth/Container.php";
require_once "PEAR.php";

/*
 * Storage driver for fetching login data from an IMAP server
 *
 * This class is based on LDAP containers, but it very simple.
 * By default it connects to localhost:143 
 * The constructor will first check if the host:port combination is 
 * actually reachable.
 * It then tries to create an IMAP stream (without opening a mailbox)
 * To use this storage containers, you have to use the
 * following syntax:
 *
 * <?php
 * ...
 * $params = array(
 * 'host'       => 'mail.example.com',
 * 'port'       => 143,
 * );
 * $myAuth = new Auth('IMAP', $params);
 * ....
 * 
 *
 *
 * @author   Jeroen Houben <jeroen@terena.nl>
 * @package  Auth
 * @version  $Revision$
 */
class Auth_Container_IMAP extends Auth_Container
{
    /**
     * Options for the class
     * @var array
     */
    var $options = array();

    /**
     * Constructor of the container class
     *
     * @param  $params, associative hash with host,port,basedn and userattr key
     * @return object Returns an error object if something went wrong
     */
    function Auth_Container_IMAP($params)
    {
        $this->_setDefaults();
        
        // set parameters (if any)
        if (is_array($params)) {
            $this->_parseOptions($params);
        }
        
        $this->_checkServer();
        return true;
    }

    /**
     * Set some default options
     *
     * @access private
     */
    function _setDefaults()
    {
        $this->options['host'] = 'localhost';
        $this->options['port'] = 143;
    }


    /**
     * Check if the given server and port are reachable
     *
     * @access private
     */
    function _checkServer($timeout=20) {
        $fp = @fsockopen ($this->options['host'], $this->options['port'], $errno, $errstr, $timeout);
        if ($fp) {
            fclose($fp);
        } else {
            return PEAR::raiseError("Error connecting to IMAP server ".$this->options['host'].":".$this->options['port'], 41, PEAR_ERROR_DIE);
        }
    }

    /**
     * Parse options passed to the container class
     *
     * @access private
     * @param  array
     */
    function _parseOptions($array)
    {
        foreach ($array as $key => $value) {
            $this->options[$key] = $value;
        }
    }

    /**
     * Try to open a IMAP stream using $username / $password
     *
     * @param  string Username
     * @param  string Password
     * @return boolean
     */
    function fetchData($username, $password)
    {
        $conn = @imap_open ('{'.$this->options['host'].':'.$this->options['port'].'}', $username, $password, OP_HALFOPEN);
        if ($conn) {
            $this->activeUser = $username;
            imap_close($conn);
            return true;
        } else {
            $this->activeUser = '';
            return false;
        }
    }
}
?>
