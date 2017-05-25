<?php
/**
 * _0 (ZERO) :: PHP Code Dump
 * ======================================================================
 * This is a collection of code I've written and used since I started writing
 * PHP code in 2009. The "library" exists as a proper collection since 2010,
 * when I made my first flat-file CMS.
 *
 * From then, the code inside this library grew up, it changed names, some
 * stuff became obsolete (and I'm sure some of it is still obsolete) and
 * experimental stuff found its way into the code, but it proved useful to
 * me, so it might be for you (or not).
 *
 * Most of the stuff I wrote as an exercise, so things might not always be
 * the best solution out there, I advise you to research a bit, when in doubt.
 * ----------------------------------------------------------------------
 *
 * [ DEPENDENCIES ] =====================================================
 * - PHP 5.3.x+;
 * - PDO (with MySQL/SQLite drivers, if you use database);
 * - SimpleXMLElement;
 * - GD (for image manipulation);
 * - ZipArchive;
 * - PHPMailer and SMTP, if using the helper methods;
 *
 * ----------------------------------------------------------------------
 *
 * [ LICENSE ] ==========================================================
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2010-2017 Fabio Yuiti Goto
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * ----------------------------------------------------------------------
 * @package     br.com.yuiti.zero
 * @author      Fabio Y. Goto <lab@yuiti.com.br>
 * @copyright   (c) 2010~2017 Fabio Y. Goto
 * @version     1.0.0
 * @license     MIT License
 */
class _0 {
    // 00: CLASS CONSTANTS AND PROPERTIES
    // ------------------------------------------------------------------

    /**
     * Framework version.
     * @var string
     */
    const ZERO_VERSION = "1.0.0";

    /**
     * General use variable.
     * @var mixed
     */
    public static $zero;


    // 01: STRING OPERATIONS
    // ------------------------------------------------------------------

    /**
     * Converts a string's encoding to UTF-8, if using different encoding.
     *
     * @param string $string
     *      String for testing and conversion
     * @return string
     *      Converted UTF-8 string
     */
    public static function stringEncs( $string )
    {
        $string = trim( $string );

        return ( !mb_check_encoding( $string, "UTF-8" ) )
            ? utf8_encode( $string ) : $string;
    }

    /**
     * Converts a string's encoding to ISO-8859-1, if using different encoding.
     *
     * @param string $string
     *      String for testing and conversion
     * @return string
     *      Converted ISO-8859-1 string
     */
    public static function stringDecs( $string )
    {
        $string = trim( $string );

        return ( mb_check_encoding( $string, "UTF-8" ) )
            ? utf8_decode( $string ) : $string;
    }

    /**
     * Checks if a string contains only alphanumeric and underscore characters.
     *
     * Valid characters include:
     * - Uppercase and lowercase letters, non-accented;
     * - Numbers;
     * - Underscore (_);
     *
     * @param string $string
     *      String to validate
     * @return bool
     *      True if valid, false if invalid
     */
    public static function stringCharValidate( $string )
    {
        // Validating characters
        $validate = preg_match( "/^([A-Za-z0-9_]+)$/", trim( $string ) );

        return ( $validate ) ? true : false;
    }

    /**
     * Checks a string for username-safe characters, in the same way as
     * `stringCharValidate()`, while also checking it for minimum and maximum
     * lengths.
     *
     * @param string $string
     *      String to validate
     * @param int $min
     *      Optional, minimum length allowed for the string, default: 5
     * @param int $max
     *      Optional, maximum length allowed for the string, default: 32
     * @return bool
     *      True if valid, false if invalid
     */
    public static function stringUsername( $string, $min = 5, $max = 32 )
    {
        $string = trim( $string );
        $len    = strlen( $string );
        $min    = ( is_numeric( $min ) && $min > 0 ) ? $min : 5;
        $max    = ( is_numeric( $max ) && $max > $min ) ? $max : 32;

        // Validate, compare lengths and return
        $validate = self::stringCharValidate( $string );

        return ( $validate && $len >= $min && $len <= $max ) ? true : false;
    }

    /**
     * Removes all non-numeric characters in a string. Also removes all commas
     * and dots, so don't use this function where these characters are needed
     * (like when dealing with currency values).
     *
     * @param string $string
     *      String to be cleaned
     * @return string
     *      String with numbers only
     */
    public static function stringNums( $string )
    {
        return preg_replace( "/([\D]+)/", "", trim( $string ) );
    }

    /**
     * Removes special, accented, characters from a string, while replacing
     * them with their non-accented versions (e.g.: ç => c, ä => a).
     *
     * @param string $string
     *      String to be cleaned
     * @return string
     *      Clean string
     */
    public static function stringRemoveSpecChars( $string )
    {
        $string = self::stringEncs( $string );
        // Convert special chars into HTML entities
        $string = htmlentities( $string, ENT_COMPAT, "UTF-8" );
        // Regex flag for entities
        $flag = "uml|acute|grave|circ|tilde|cedil|ring|slash|u";

        return preg_replace( "/&([A-Za-z])({$flag});/", "$1", $string );
    }

    /**
     * Removes all non-alphanumeric characters from a string, while keeping
     * spaces intact.
     *
     * @param string $string
     *      String to be cleaned
     * @return string
     *      Clean string
     */
    public static function stringClean( $string )
    {
        $string = self::stringRemoveSpecChars( $string );

        return preg_replace(
            "/([^A-Za-z0-9]+)/",
            "",
            html_entity_decode( $string )
        );
    }

    /**
     * Sanitizes a string to be used as file/folder name, removing invalid
     * characters, while also converting spaces into underscore characters.
     *
     * @param string $string
     *      String to be sanitized
     * @return string
     *      Clean string
     */
    public static function stringStrip( $string )
    {
        $string = self::stringRemoveSpecChars( $string );
        $string = html_entity_decode( $string );
        $string = str_replace( " ", "_", $string );

        return preg_replace( "/([\\/\?\<\>:\*\|%|`|´]+)/", "", $string );
    }

    /**
     * Replaces a single quote (') into double-single quotes ('') or vice-versa,
     * when `$reverse` is true.
     *
     * Created mostly for use on SQL query strings.
     *
     * @param string $string
     *      String to be replaced
     * @param bool $reverse
     *      Optional, direction of the replacement, default: false
     * @return string
     *      String with single-quotes properly replaced
     */
    public static function stringSingleQuotes( $string, $reverse = false )
    {
        return str_replace(
            ( $reverse ) ? "''" : "'",
            ( $reverse ) ? "'" : "''",
            trim( $string )
        );
    }

    /**
     * Converts integer numbers into letters, in the same way that spreadsheet
     * applications do with column naming (e.g.: a-z, aa-zz, etc.).
     *
     * @param int $int
     *      Number for conversion
     * @param bool $uppercase
     *      Optional, if the string should, or not, be returned as uppercase,
     *      default: false
     * @return string
     *      Converted string
     */
    public static function stringNumsToChar( $int, $uppercase = false )
    {
        if ( $int < 0 ) $int = 0;
        // Found this over the internet, it's needed
        $char = chr( ( $int % 26 ) + 97 );
        // Uses recursion, if more letters are needed
        if ( $int >= 26 ) {
            $next = self::stringNumsToChar( floor( $int / 26 ), $uppercase );
            $char = $next.$char;
        }
        return ( $uppercase ) ? strtoupper( $char ) : strtolower( $char );
    }

    /**
     * Generates an anchor item (<a>).
     *
     * @param string $html
     *      Html to be placed inside the anchor item
     * @param string $href
     *      Optional, href attribute, if empty a hash tag is used (#),
     *      default: null
     * @param string $target
     *      Optional, anchor target, default: _blank
     * @param string $id
     *      Optional, anchor id attribute, default: null
     * @param string $class
     *      Optional, anchor class, default: null
     * @return string
     *      Anchor element string
     */
    public static function stringLinkMake(
        $html,
        $href = null,
        $target = null,
        $id = null,
        $class = null
    ) {
        $anchor = array( "<a" );
        $anchor[] = ( "" == trim( $href ) )
            ? " href=\"#\""
            : " href=\"".trim( $href )."\"";
        $anchor[] = ( "" == trim( $id ) )
            ? "" : " id=\"".trim( $id )."\"";
        $anchor[] = ( "" == trim( $class ) )
            ? "" : " class=\"".trim( $class )."\"";
        $anchor[] = ( "" == trim( $target ) )
            ? " target=\"_self\"" : " target=\"".trim( $class )."\"";
        $anchor[] = ">";
        $anchor[] = trim( $html );
        $anchor[] = "</a>";
        return implode( "", $anchor );
    }

    /**
     * Slices a URL's query string and returns the sliced values, like:
     * - Given the following URL: www.site.com?id=10&sub30&test=14;
     * - And that the $vars parameter is 'sub';
     * - The returned string will be: ?id=10&sub=;
     *
     * IMPORTANT:
     * - This method uses $_SERVER['QUERY_STRING'] to get the query string from
     * the URL;
     * - This method was built for my own needs, not really safe for production;
     *
     * @param string $vars
     *      GET variable to be sliced in the query string
     * @return string
     *      Sliced query string
     */
    public static function stringLinkClip( $vars )
    {
        $list = array();
        parse_str( $_SERVER["QUERY_STRING"], $list );

        if ( count( $list ) < 1 )  return "?".$vars."=";
        if ( isset( $list[$vars] ) ) {
            unset( $list[$vars] );
            if ( count( $list ) < 1 ) return "?".$vars."=";
            $temp = array();
            foreach ( $list as $name => $data ) {
                $temp[] = "{$name}={$data}";
            }
            return "?".implode( "&", $temp )."&".$vars."=";
        } else {
            $temp = array();
            foreach ( $list as $name => $data ) {
                $temp[] = "{$name}={$data}";
            }
            return "?".implode( "&", $temp )."&".$vars."=";
        }
    }

    /**
     * Builds a meta refresh tag.
     *
     * @param int $time
     *      Optional, time in seconds for the delay, default: 0
     * @param string $data
     *      Optional, URL to redirect the users to
     * @return string
     *      Meta refresh tag, ready to print
     */
    public static function stringMetaRefresh( $time = 0, $data = '' )
    {
        // Checking time
        $time = ( is_numeric( $time ) && $time > 0 ) ? $time : 0;
        // Checking URL
        $data = ( trim( $data ) != "" ) ? ";url=".trim( $data ) : "";
        // Returning
        return "<meta http-equiv=\"refresh\" content=\"{$time}{$data}\">";
    }

    // 02: E-MAIL VALIDATION AND SENDING
    // ------------------------------------------------------------------

    /**
     * Checks if an e-mail address is valid or not.
     *
     * @param string $mail
     *      E-mail address to validate
     * @return bool
     *      True if valid, false if invalid
     */
    public static function mailTest( $mail )
    {
        $mail = trim( $mail );
        if ( "" === $mail ) {
            return false;
        }

        // Validation through regex
        $flag = "^([A-Za-z0-9\-\_]+)((\.|\+)([A-Za-z0-9\-\_]+))*@";
        $flag .= "[A-Za-z0-9\-\_]+(\.[A-Za-z0-9]+)*(\.[A-Za-z0-9]{2,7})$";
        $test = ( !preg_match( "/{$flag}/", $mail ) ) ? false : true;

        // Testing segments
        $list = explode( "@", $mail );
        if ( count( $list ) > 2 ) {
            return false;
        }
        if ( !isset( $list[1] ) || "" == trim( $list[1] ) ) {
            return false;
        }

        // Testing DNS
        $server = ( !checkdnsrr( $list[1], "MX" ) ) ? false : true;

        return ( $test && $server ) ? true : false;
    }

    /**
     * Masks an e-mails address, like:
     * - Input: mailaddress@mail.com;
     * - Output: m**********@mail.com;
     *
     * Returns boolean false, if the address is not valid.
     *
     * @param string $mail
     *      E-mail address to mask
     * @return string|bool
     *      Masked e-mail if valid, boolean false if invalid
     */
    public static function mailMask( $mail )
    {
        $mail = trim( $mail );
        if ( !self::mailTest( $mail ) ) {
            return false;
        }

        // Regex for masking
        $flag = "/^(.{1})(.*?)(@)(.*?)$/";
        preg_match( $flag, $mail, $list );

        // Mask and return
        return $list[1].preg_replace( "/(.)/", "*", $list[2] ).$list[3].$list[4];
    }

    /**
     * Prepares a basic header for PHP's `mail` function.
     *
     * The fields $to, $cc and $bcc can be either a string or an array,
     * containing e-mail addresses. Each address may come in these formats:
     * - address@mail.com;
     * - Name <address@mail.com>;
     *
     * The $from and $postfix_mail, though, can only accept a string containing
     * a single e-mail address.
     *
     * The $posfix and $postfix_mail are optional fields, meant to be used, only,
     * when the server requires that Postfix should be used. The $postfix_mail
     * address MUST have the same top-level domain as the server being used.
     *
     * IMPORTANT:
     * Each e-mail address and parameter must be checked and properly trimmed
     * beforehand, as this method doesn't do any of that.
     *
     * @param string $subject
     *      Mail subject text
     * @param string $from
     *      E-mail sender, also used in the Reply-To field
     * @param string|array $to
     *      Destiny e-mail address, can be either a string with a single address
     *      or an array with multiple addresses
     * @param string|array $cc
     *      Optional, carbon copy e-mail address, can be either a string with a
     *      single address or an array with multiple addresses
     * @param string|array $bcc
     *      Optional, blind carbon copy e-mail address, can be either a string
     *      with a single address or an array with multiple addresses
     * @param bool $text_mode
     *      Optional, if the e-mail header is for a plain-text or html mode,
     *      default: false
     * @param bool $postfix
     *      Optional, if Postfix should be used or not, default: false
     * @param string $postfix_mail
     *      Optional, but required if $postfix is true, e-mail address of the
     *      sender required by Postfix
     * @return string
     *      E-mail header
     */
    public static function mailHeader(
        $subject,
        $from,
        $to,
        $cc = null,
        $bcc = null,
        $text_mode = false,
        $postfix = false,
        $postfix_mail = ""
    ) {
        // Array
        $head = array();

        // MIME, Content-type, Priority and Subject
        $head[] = "MIME-VERSION: 1.0";
        $head[] = ( $text_mode )
            ? "Content-type: text/html; Charset=UTF-8"
            : "Content-type: text/plain; Charset=UTF-8";
        $head[] = "X-Priority: 1 (Normal)";
        $head[] = "X-MSMail-Priority: Normal";
        $head[] = "Subject: ".trim( $subject );

        // From field (if using postfix, uses the postfix_mail)
        $head[] = ( $postfix && $postfix_mail != "" )
            ? "From: ".$postfix_mail
            : "From: ".$from;

        // Building the to field
        $head[] = ( is_array( $to ) && count( $to ) > 0 )
            ? "To: ".implode( ",", $to ) : "To: {$to}";

        // Building the cc and bcc fields
        $head[] = ( is_array( $cc ) && count( $cc ) > 0 )
            ? "Cc: ".implode( ",", $cc ) : "Cc: {$cc}";
        $head[] = ( is_array( $bcc ) && count( $bcc ) > 0 )
            ? "Bcc: ".implode( ",", $bcc ) : "Bcc: {$bcc}";

        // Checking return-path
        $head[] = ( $postfix && $postfix_mail != "" )
            ? "Return-Path: ".$postfix_mail
            : "Return-Path: ".$from;

        // Checking reply-to
        $head[] = "Reply-To: ".$from;

        // Implode, return
        return implode( PHP_EOL, $head );
    }

    /**
     * Sends an e-mail using PHP's built-in `mail()` function.
     *
     * $postfix_mail should be used when the server requires Postfix, and must
     * be an address with the same top level domain as the server's.
     *
     * @param string $to
     *      E-mail destination address
     * @param string $subject
     *      E-mail subject
     * @param string $body
     *      E-mail copy body
     * @param string $head
     *      E-mail header
     * @param string $postfix_mail
     *      Optional, postfix e-mail address, if required
     * @return bool
     *      True if successfully sent, false if not
     */
    public static function mailSender(
        $to,
        $subject,
        $body,
        $head,
        $postfix_mail = ""
    ) {
        $to           = trim( $to );
        $subject      = trim( $subject );
        $body         = trim( $body );
        $head         = trim( $head );
        $postfix_mail = trim( $postfix_mail );

        // Test all arguments
        $test = array(
            0 => ( "" == $to ) ? false : true,
            1 => ( "" == $body ) ? false : true,
            2 => ( "" == $head ) ? false : true,
            3 => ( "" == $subject ) ? false : true,
            4 => self::mailTest( $to ),
            5 => self::mailTest( $postfix_mail )
        );

        // Checking validation and sending
        if ( $test[0] && $test[1] && $test[2] && $test[3] && $test[4] ) {
            if ( "" != $postfix_mail && $test[5] ) {
                $send = @ mail(
                    $postfix_mail,
                    $subject,
                    $body,
                    $head,
                    "-r".$postfix_mail
                );
            } else {
                $send = @ mail( $to, $subject, $body, $head );
            }

            return ( true === $send ) ? true : false;
        }

        return false;
    }

    // 03: FORM DATA CAPTURE AND MANIPULATION
    // ------------------------------------------------------------------

    /**
     * Fetches data from GET and POST requests.
     *
     * $fields can be either a string with a field name or an array containing
     * multiple field names.
     *
     * The method returns an associative array, where the field names are the
     * keys.
     *
     * IMPORTANT:
     * All array variables are serialized.
     *
     * @param array|string $fields
     *      Form field name or an array with form field names
     * @param bool $get
     *      Optional, if the method captures GET or POST variables, default> false
     * @return array
     *      Associative array with the field values
     */
    public static function formPull( $fields, $get = false )
    {
        $form = array();
        // If $fields is an array
        if ( is_array( $fields ) ) {
            foreach ( $fields as $field ) {
                if ( isset( $_POST[ $field ] ) || isset( $_GET[ $field ] ) ) {
                    $data           = ( true === $get ) ? $_GET[ $field ] : $_POST[ $field ];
                    $form[ $field ] = ( is_array( $data ) )
                        ? serialize( $data ) : trim( $data );
                }
            }
        } else {
            // If single-item and is set
            if ( isset( $_POST[ $fields ] ) || isset( $_GET[ $fields ] ) ) {
                $data            = ( true === $get ) ? $_GET[ $fields ] : $_POST[ $fields ];
                $form[ $fields ] = ( is_array( $data ) )
                    ? serialize( $data ) : trim( $data );
            }
        }

        return $form;
    }

    // 04: SLUG, HASH AND GUID GENERATORS/FORMATTERS
    // ------------------------------------------------------------------

    /**
     * Generates a MD5 hash for the declared string using mixed encoding and
     * encryption.
     *
     * A security salt is optional, but increases the difficulty in decoding
     * the hashset.
     *
     * @param string $string
     *      String to be turned into a hash
     * @param string $salt
     *      Optional, a security salt to increase difficulty on the resulting
     *      string, default: ""
     * @return string
     *      MD5 hashset for the input
     */
    public static function hashMake( $string, $salt = "" )
    {
        $salt   = ( trim( $salt ) != "" ) ? sha1( trim( $salt ) ) : "";
        $string = md5( base64_encode( trim( $string ).$salt ) );
        $string = md5( sha1( $salt.str_rot13( strrev( $string ) ) ).$salt );

        return $string;
    }

    /**
     * Generates a string, containing a pseudo-random slug, with the size
     * and type desired.
     *
     * The $type attribute defines which type of characters the return will have:
     * - 0: Alphanumeric slug, without vowels (like old-school game passwords);
     * - 1: Letters only, vowels and consonants;
     * - 2: Hexadecimal characters (0-9, A-F);
     * - 3: Numbers only;
     * - 4: Letters and numbers;
     *
     * @param int $size
     *      Optional, size of the string to be returned, default: 6
     * @param int $type
     *      Optional, type of the string to be returned, default: 0
     * @return string
     *      Generated slug
     */
    public static function slugMake( $size = 6, $type = 0 )
    {
        $size = ( is_numeric( $size ) && $size > 0 ) ? $size : 6;
        $type = ( is_numeric( $type ) && $type >= 0 && $type < 5 ) ? $type : 0;

        // Character list for types
        $list = array(
            0 => "0123456789BCDFGHJKLMNPQRSTVWXYZ",
            1 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            2 => "0123456789ABCDEF",
            3 => "0123456789",
            4 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
        );

        $slug = "";
        $nums = strlen( $list[ $type ] ) - 1;
        for ( $n = 0; $n < $size; $n ++ ) {
            $slug .= $list[ $type ][ mt_rand( 0, $nums ) ];
        }

        return $slug;
    }

    /**
     * Generates something like a Global Unique Identifier (GUID) from a string
     * or a random value (if a string isn't declared).
     *
     * The $wrap argument defines if the return value will be wrapped, or not,
     * in curly braces (not useful, but some GUID systems it, so...).
     *
     * This method DOES NOT FOLLOW any standard!
     *
     * @param string $string
     *      Optional, a string to be turned into a GUID, default is empty and
     *      uses the current date as variable
     * @param bool $wrap
     *      Optional, default: false, defines if the returned variable will
     *      be wrapped in curly braces or not
     * @return string
     *      The final GUID
     */
    public static function guidMake( $string = "", $wrap = false )
    {
        if ( trim( $string ) != "" ) {
            $id = trim( $string ).date( "YmdHis" );
        } else {
            $id = rand( 0, 255 ).date( "YmdHis" ).rand( 0, 255 );
        }
        $id = self::hashMake( $id );

        // Formatting Regex
        $flag = "[a-z0-9]";
        $flag = "({$flag}{8})({$flag}{4})({$flag}{4})({$flag}{4})({$flag}{12})";

        // Format and return
        $id = strtoupper( preg_replace( "/{$flag}/", "$1-$2-$3-$4-$5", $id ) );

        return ( true === $wrap ) ? "{{$id}}" : $id;
    }

    // 05: FILE PATH MANIPULATION AND ITERATION
    // ------------------------------------------------------------------

    /**
     * Removes the "\" character from a file path, exchanging it for the "/"
     * characters.
     *
     * This was made because I had some problems with Mozilla browsers, when
     * the path had "\" in it.
     *
     * @param string $path
     *      Path for substitution
     * @return string
     *      The proper path
     */
    public static function pathWash( $path )
    {
        return str_replace( "\\", "/", trim( $path ) );
    }

    /**
     * Checks if a path or URL has "/" as the last character and, if it doesn't,
     * inserts the character.
     *
     * @param string $path
     *      Path to be checked
     * @return string
     *      Final path
     */
    public static function pathRead( $path )
    {
        $path = self::pathWash( $path );

        return ( "/" != substr( $path, - 1 ) ) ? $path."/" : $path;
    }

    /**
     * Iterates through a path/folder and returns the path to all items and
     * sub-items in an array.
     *
     * $file is optional, and defines if, besides folders, the path to all
     * files should be returned too.
     *
     * Useful when building ZIP files.
     *
     * @param string $path
     *      Path to the folder to be scanned and listed
     * @param bool $files
     *      Optional, default: false, defines if files will also be returned
     *      on the list
     * @return array
     *      Array with the paths to the folder and all of its subitems
     */
    public static function pathList( $path, $files = false )
    {
        $path = self::pathRead( $path );

        // Temporary array to store all paths
        $temp = array( $path );

        // Scan for sub-folders/files and use recursion to fetch all
        $scan = scandir( $path );
        foreach ( $scan as $item ) {
            if ( "." != $item && ".." != $item ) {
                if ( is_dir( $path.$item ) ) {
                    $children = self::pathList( $path.$item, $files );
                    if ( is_array( $children ) && count( $children ) > 0 ) {
                        $temp = array_merge( $temp, $children );
                    }
                } else {
                    if ( true === $files ) {
                        $temp[] = $path.$item;
                    }
                }
            }
        }

        // Cleans duplicates
        $temp = array_unique( $temp );

        // Generate new array and sort, to reset keys
        $list = array();
        sort( $temp );
        foreach ( $temp as $item ) {
            $list[] = $item;
        }

        return $list;
    }

    /**
     * This method is just a path slicer, written to be used as an auxiliary
     * function to slice URIs.
     *
     * Returns an array with all the fragments.
     *
     * You can set up a flag/offset to break the input string, so it only
     * returns what's after the offset, for example:
     * - The input path is: "X:/test/user/demo/data";
     * - By default, this method will return this array: 'X:', 'test', 'user',
     * 'demo', 'data';
     * - If you give the method the "user" flag, this will be returned: 'demo',
     * 'data';
     *
     * @param string $path
     *      Path or URL to be sliced
     * @param string $flag
     *      Optional, it's a flag to define an offset for the returned array
     * @return array
     *      All path slices
     */
    public static function pathSlicer( $path, $flag = "" )
    {
        $path = trim( $path );
        $flag = trim( $flag );

        // Checks if flag was declared and slices it
        if ( $flag != "" ) {
            $size = strlen( $path );
            $mark = strpos( $path, $flag ) + strlen( $flag );
            $path = trim( substr( $path, $mark, $size ), "/" );
        }

        // Checks for empty elements
        $list = array();
        foreach ( explode( "/", $path ) as $item ) {
            if ( trim( $item ) != "" ) {
                $list[] = trim( $item );
            }
        }

        // Returning
        return $list;
    }

    // 06: FILE MANIPULATION
    // ------------------------------------------------------------------

    /**
     * Alias for file_get_contents.
     *
     * @param string $file
     *      File name, with full path
     * @return string
     *      File contents
     */
    public static function fileRead( $file )
    {
        return file_get_contents( $file );
    }

    /**
     * Creates/writes a file to disk, as an alias to "fopen", "fwrite" and
     * "fclose".
     *
     * @param string $file
     *      File name with full path
     * @param string $data
     *      Optional, file contents
     * @param bool $overwrite
     *      Optional, if files with existing names should be overwritten or not
     * @return bool
     *      True on success, false on failure
     */
    public static function fileSave( $file, $data = "", $overwrite = true )
    {
        if ( false === $overwrite && file_exists( $file ) ) return false;

        $open = fopen( $file, "w+" );
        $flag = 0;

        if ( false !== $open ) {
            $flag += 1;
            if ( trim( $data ) != "" ) {
                if ( fwrite( $open, trim( $data ) ) === false ) return false;
            }
            if ( fclose( $open ) ) $flag += 1;
        }

        return ( $flag > 1 ) ? true : false;
    }

    /**
     * An alias for self::fileSave, checks whether a file exists and creates if
     * it doesn't.
     *
     * The only optional parameter in this case, though, is the file's content,
     * as this method ALWAYS overwrites files.
     *
     * @param string $file
     *      File name with full path
     * @param string $data
     *      Optional, file contents
     * @return bool Returns
     *      False if the file doesn't exist or can't be created, true otherwise
     */
    public static function fileTest( $file, $data = "" )
    {
        if ( file_exists( $file ) ) return false;
        return self::fileSave( $file, $data, true );
    }

    /**
     * Alias for unlink(), deletes the file declared.
     *
     * @param string $file
     *      File name with full path
     * @return bool
     *      True on success, false on failure
     */
    public static function fileWipe( $file )
    {
        return ( file_exists( $file ) ) ? unlink( $file ) : false;
    }

    /**
     * Returns an associative array containing:
     * - 'name': File name;
     * - 'type': File extension (lowercase);
     *
     * If file has no extension, 'type' will be FALSE.
     *
     * @param string $file
     *      File name to be split
     * @return array
     *      Associative array with file name and extension
     */
    public static function fileName( $file )
    {
        $file = pathinfo( $file );
        return array(
            "name" => $file['filename'],
            "type" => ( !isset( $file['extension'] ) )
                ? false : strtolower( $file['extension'] )
        );
    }

    /**
     * Generates a random name for a file, to be stored in $path, with the
     * extension in $type.
     *
     * The method checks if the generated random name exists and generates
     * another name in case of duplicates.
     *
     * @param string $path
     *      Path where the file will be saved
     * @param string $type
     *      File extension
     * @return string
     *      Generated file name
     */
    public static function fileRandomName( $path, $type )
    {
        $path = self::pathRead( $path );
        $name = self::slugMake( 6 ).".".strtolower( trim( $type ) );
        if ( file_exists( $path.$name) ) {
            while ( file_exists( $path.$name) ) {
                $name = self::slugMake( 6 ).".".strtolower( trim( $type ) );
            }
        }
        return $name;
    }

    // 07: FOLDER MANIPULATION
    // ------------------------------------------------------------------

    /**
     * Reads $path and return its contents.
     *
     * @param string $path
     *      Full path to the folder being scanned/read
     * @return array
     *      Array with the folder's contents
     */
    public static function folderRead( $path )
    {
        $path = self::pathRead( $path );
        $list = array();
        $scan = scandir( $path);
        sort( $scan );
        foreach ( $scan as $item ) {
            if ( $item != "." && $item != ".." ) $list[] = $item;
        }
        return $list;
    }

    /**
     * Advanced version of `folderRead()`, also serves as alias to PHP's `glob()`.
     *
     * $path and $mode parameters work together to define how you use this
     * method. $path should be a `glob()` compatible path pattern.
     *
     * The value in $mode should be one of these, with the corresponding $path
     * value examples below, as sub-items:
     * - 0: Default mode, returns all the objects found in the given path or
     * pattern;
     *      - "base\*": Returns all files and folders in 'base';
     * - 1: Only subfolders are returned;
     *      - "base\*\images\*": Returns all files/folders from the images
     *      folder inside any folder inside 'base';
     * - 2: Advanced mode, accepts grouping of file extensions, to limit what
     * is returned;
     *      - "base\{*.doc,*.rtf}": Returns only files with the 'doc' and 'rtf'
     *      extensions inside 'base';
     *
     * IMPORTANT:
     * - ALWAYS end patterns with the * wildcard, or any extension wildcard
     * (*.jpg or *.png), as if it's not declared, the method will only return
     * the value in $path;
     * - When usind $mode = 2, if searching for multiple file extensions, like
     * '{ *.jpg, *.png}', AVOID spaces between the extensions, and write them
     * as: '{ *.jpg,*.png}';
     * - Child items will ALWAYS be returned with the full path;
     *
     * @param string $path
     *      Path to be read/scanned, as a `glob()` pattern
     * @param int $mode
     *      Optional, sets the search mode for `glob()`, default: 0
     * @return array
     *      Array with folder contents and full path
     */
    public static function folderReadAdvanced( $path, $mode = 0 )
    {
        $path = trim( $path );
        $mode = ( is_numeric( $mode ) && $mode >= 0 && $mode < 3 ) ? $mode : 0;

        $list = array();
        switch ( $mode ) {
            case 1:
                $search = glob( $path, GLOB_ONLYDIR );
                break;
            case 2:
                $search = glob( $path, GLOB_BRACE );
                break;
            default:
                $search = glob( $path, GLOB_MARK );
                break;
        }

        if ( count( $search ) > 0 ) {
            foreach ( $search as $item ) {
                $list[] = $item;
            }
        }
        return $list;
    }

    /**
     * Counts the number of items inside a folder.
     *
     * You can set a minimum number of files inside the folder for the method
     * to return a truthy value.
     *
     * @param string $path
     *      Full path to the folder being scanned/read
     * @param int $nums
     *      Optional, the minimum required items inside the folder, default: 1
     * @return bool
     *      True if the minimum quantity is present, false if not
     */
    public static function folderScan( $path, $nums = 1 )
    {
        $nums = ( is_numeric( $nums ) && $nums > 0 ) ? $nums : 1;
        if ( !is_dir( trim( $path ) ) ) return false;
        $scan = self::folderRead( $path );
        return ( count( $scan ) >= $nums ) ? true : false;
    }

    /**
     * Counts the number of items inside the folder.
     *
     * @param string $path
     *      Path to be scanned
     * @return int
     *      Number of items inside
     */
    public static function folderNums( $path )
    {
        $scan = self::folderRead( $path );
        return count( $scan );
    }

    /**
     * Returns the folder size, based on file sizes, in bytes.
     *
     * @param string $path
     *      Full path to the folder being scanned/read
     * @return int
     *      Folder size, in bytes
     */
    public static function folderSize( $path )
    {
        set_time_limit( 0 );
        $scan = self::pathList( $path, true );
        $size = 0;
        foreach ( $scan as $item ) {
            if ( !is_dir( $item ) && file_exists( $item ) ) {
                $size += filesize( $item );
            }
        }
        return $size;
    }

    /**
     * Alias for `mkdir()`, creates a folder.
     *
     * @param string $path
     *      Full path with the folder being created
     * @return bool
     *      True on success, false on failure
     */
    public static function folderMake( $path )
    {
        $path = self::pathRead( $path );
        return ( @ mkdir( $path ) ) ? true : false;
    }

    /**
     * Checks if a folder exists and, if it doesn't, creates it.
     *
     * @param string $path
     *      Full path with the folder being created
     * @return bool
     *      True on success, false on failure
     */
    public static function folderTest( $path )
    {
        $path = self::pathRead( $path );
        if ( !is_dir( $path ) ) return @ mkdir( $path );
        return false;
    }

    /**
     * Deletes a folder and all child items in its file tree.
     *
     * @param string $path
     *      Full path with the folder being deleted
     * @return bool
     *      True on success, false on failure
     */
    public static function folderWipe( $path )
    {
        $path = self::pathRead( $path );
        if ( self::folderScan( $path, 1 ) ) {
            $scan = self::folderRead( $path );
            foreach ( $scan as $item ) {
                if ( is_dir( $path.$item ) ) {
                    self::folderWipe( $path.$item );
                } else {
                    unlink( $path.$item );
                }
            }
        }

        return @ rmdir( $path );
    }

    // 08: TIME AND DATE FORMATS
    // ------------------------------------------------------------------

    /**
     * Returns how much time has passed since a date, like Facebook and Twitter
     * does on their timelines.
     *
     * IMPORTANT:
     * Output is in brazilian-portuguese.
     *
     * @param int $time
     *      UNIX timestamp, like the one returned by PHP's `time()`
     * @return string
     *      String containing how much time has passed
     */
    public static function timePassed( $time )
    {
        if ( !is_numeric( $time ) ) return "Timestamp inválida.";

        $time = time() - $time;
        if ( $time < 0 ) return "Timestamp inválida.";

        // Verifying how much time has passed since then
        switch ( $time ) {
            // Now
            case 0:
                $text = "agora mesmo";
                break;
            // Seconds ago
            case ( $time >= 0 && $time < 30 ):
                $text = "h&aacute; alguns segundos";
                break;
            // Less than a minute
            case ( $time < 60 ):
                $text = "h&aacute; menos de um minuto";
                break;
            // A minute ago
            case ( $time < 120 ):
                $text = "h&aacute; um minuto";
                break;
            // X minutes ago
            case ( $time < ( 60 * 60 ) ):
                $text = "h&aacute; ".floor( $time / 60 )." minutos";
                break;
            // An hour ago
            case ( $time < ( 120 * 60 ) ):
                $text = "h&aacute; uma hora";
                break;
            // X hours ago
            case ( $time < ( 24 * 60 * 60 ) ):
                $text = "h&aacute; ".floor( $time / 3600 )." horas";
                break;
            // A day ago
            case ( $time < ( 48 * 60 * 60 ) ):
                $text = "h&aacute; um dia";
                break;
            // X days ago
            case ( $time < ( 7 * 24 * 60 * 60 ) ):
                $text = "h&aacute; ".floor( $time / 86400 )." dias";
                break;
            // A week ago
            case ( $time < ( 14 * 24 * 60 * 60 ) ):
                $text = "h&aacute; uma semana";
                break;
            // X weeks ago
            case ( $time < ( 30 * 24 * 60 * 60 ) ):
                $text = "h&aacute; ".floor( $time / ( 86400 * 7 ) )." semanas";
                break;
            // A month ago
            case ( $time < ( 60 * 24 * 60 * 60 ) ):
                $text = "h&aacute; um m&ecirc;s";
                break;
            // X months ago
            case ( $time < ( 365 * 24 * 60 * 60 ) ):
                $text = "h&aacute; ".floor( $time / ( 86400 * 30 ) )." meses";
                break;
            // A year ago
            case ( $time < ( 730 * 24 * 60 * 60 ) ):
                $text = "h&aacute; um ano";
                break;
            // X years ago
            default:
                $text = "h&aacute; ".floor( $time / ( 86400 * 365 ) )." anos";
                break;
        }
        return $text;
    }

    /**
     * Parses a date string and returns an array with the following index values:
     * - 0: Day;
     * - 1: Month;
     * - 2: Year;
     * - 3: Hours;
     * - 4: Minutes;
     * - 5: Day of the Week;
     *
     * Accepts any string that's also acceptable in strtotime(), including:
     * - "2014-01-22 10:29:14 am" (year-month-day hour:minute:seconds am/pm);
     * - "Wed, 22 Jan 2014 10:29:41 -0200" (RFC Date);
     * - "20140122102949" (year-month-day-hour-minute-seconds);
     *
     * IMPORTANT:
     * Output is in brazilian-portuguese.
     *
     * @param string $date
     *      String containing the date values
     * @param int $size
     *      Optional, string size to be returned, ranging from 0 (abbreviated)
     *      to 2 (full name), default: 0
     * @return array|bool
     *      Array with the fragmented date, or boolean false if invalid
     */
    public static function dateParser( $date, $size = 0 )
    {
        $date = strtotime( $date );
        if ( !$date ) false;
        $size = ( is_numeric( $size ) && $size >= 0 && $size < 3 ) ? $size : 0;

        $name = array(
            "mes"   => array(
                "abbr"  => array(
                    "Jan", "Fev", "Mar", "Abr", "Mai", "Jun",
                    "Jun", "Ago", "Set", "Out", "Nov", "Dez"
                ),
                "full"  => array(
                    "Janeiro", "Fevereiro", "Mar&ccedil;o", "Abril",
                    "Maio", "Junho", "Julho", "Agosto",
                    "Setembro", "Outubro", "Novembro", "Dezembro"
                )
            ),
            "dow"   => array(
                "abbr"  => array(
                    "Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "S&aacute;b"
                ),
                "full"  => array(
                    "Domingo", "Segunda-feira", "Ter&ccedil;a-feira",
                    "Quarta-feira", "Quinta-feira", "Sexta-feira",
                    "S&aacute;bado"
                )
            )
        );

        $list = array(
            date( "d", $date ),
            date( "m", $date ),
            date( "Y", $date ),
            date( "H", $date ),
            date( "i", $date ),
            date( "w", $date ),
        );

        // Checking return abbreviation for months
        if ( $size > 0 ) {
            $list[1] = ( $size > 1 )
                ? $name["mes"]["full"][ $list[1] - 1 ]
                : $name["mes"]["abbr"][ $list[1] - 1 ];
        }

        // Check abbreviation for days of the week
        $list[5] = ( $size > 1 )
            ? $name["dow"]["full"][ $list[5] ]
            : $name["dow"]["abbr"][ $list[5] ];

        // Returning
        return $list;
    }

    /**
     * Returns options for a select box, ranging from 1 to 31, representing all
     * the days possible in a month.
     *
     * @param string $vars
     *      Optional, day to mark as selected, default: null
     * @return array
     *      Array with all the options for the select box, ready to be imploded
     *      and printed on screen/form
     */
    public static function dateOptsDia( $vars = null )
    {
        $data = array();
        $opts = range( 1, 31 );
        $vars = ( trim( $vars ) != "" ) ? trim( $vars ) : date( "d" );

        // Filling array
        foreach ( $opts as $nums ) {
            $temp = sprintf( "%02s", $nums );
            $select = ( $vars == $temp ) ? "selected=\"selected\"" : "";
            $data[] = "<option value=\"{$temp}\" {$select}>{$temp}</option>";
        }
        return $data;
    }

    /**
     * Returns options for a select box, ranging from 1 to 12, representing all
     * the months.
     *
     * @param string $vars
     *      Optional, month to mark as selected, default: null
     * @return array
     *      Array with all the options for the select box, ready to be imploded
     *      and printed on screen/form
     */
    public static function dateOptsMes( $vars = null )
    {
        $data = array();
        $opts = range( 1, 12 );
        $vars = ( trim( $vars ) != "" ) ? trim( $vars ) : date( "m" );

        // Filling array
        foreach ( $opts as $nums ) {
            $temp = sprintf( "%02s", $nums );
            $select = ( $vars == $temp ) ? "selected=\"selected\"" : "";
            $data[] = "<option value=\"{$temp}\" {$select}>{$temp}</option>";
        }
        return $data;
    }

    /**
     * Returns options for a select box, representing the years between 1900
     * until 10 years from the current one.
     *
     * @param string $vars
     *      Optional, year to mark as selected, default: null
     * @return array
     *      Array with all the options for the select box, ready to be imploded
     *      and printed on screen/form
     */
    public static function dateOptsAno( $vars = null )
    {
        $data = array();
        $opts = range( 1900, date( "Y" ) + 10 );
        $vars = ( trim( $vars ) != "" ) ? trim( $vars ) : date( "Y" );

        // Filling array
        foreach ( $opts as $nums ) {
            $temp = sprintf( "%04s", $nums );
            $select = ( $vars == $temp ) ? "selected=\"selected\"" : "";
            $data[] = "<option value=\"{$temp}\" {$select}>{$temp}</option>";
        }
        return $data;
    }

    /**
     * Returns options for a select box, ranging from 00 to 23, representing the
     * hours.
     *
     * @param string $vars
     *      Optional, hour to mark as selected, default: null
     * @return array
     *      Array with all the options for the select box, ready to be imploded
     *      and printed on screen/form
     */
    public static function dateOptsHrs( $vars = null )
    {
        $data = array();
        $opts = range( 0, 23 );
        $vars = ( trim( $vars ) != "" ) ? trim( $vars ) : date( "H" );

        // Filling array
        foreach ( $opts as $nums ) {
            $temp = sprintf( "%02s", $nums );
            $select = ( $vars == $temp ) ? "selected=\"selected\"" : "";
            $data[] = "<option value=\"{$temp}\" {$select}>{$temp}</option>";
        }
        return $data;
    }

    /**
     * Returns options for a select box, ranging from 00 to 59, representing the
     * minutes in an hour.
     *
     * @param string $vars
     *      Optional, minutes to mark as selected, default: null
     * @return array
     *      Array with all the options for the select box, ready to be imploded
     *      and printed on screen/form
     */
    public static function dateOptsMin( $vars = "" )
    {
        $data = array();
        $opts = range( 0, 59 );
        $vars = ( trim( $vars ) != "" ) ? trim( $vars ) : date( "i" );

        // Filling array
        foreach ( $opts as $nums ) {
            $temp = sprintf( "%02s", $nums );
            $select = ( $vars == $temp ) ? "selected=\"selected\"" : "";
            $data[] = "<option value=\"{$temp}\" {$select}>{$temp}</option>";
        }

        // Returning
        return $data;
    }

    /**
     * Returns options for a select box, ranging from "Dom" to "Sáb" (brazilian
     * portuguese equivalent to "Sun" to "Sat"), representing the days of the
     * week.
     *
     * The shown value is in portuguese, but the "real" value passed to the
     * form must be in english, so PHP date parsers will be able to tell which
     * day it is.
     *
     * @param string $vars
     *      Optional, day of week to mark as selected, default: null
     * @return array
     *      Array with all the options for the select box, ready to be imploded
     *      and printed on screen/form
     */
    public static function dateOptsDow( $vars = null )
    {
        $data = array();
        $opts = array(
            "en" => array( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ),
            "pt" => array( "Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "S&aacuteb;" )
        );

        $vars = ( trim( $vars ) != "" ) ? trim( $vars ) : date( "D" );

        for ( $i = 0; $i < count( $opts["en"] ); $i++ ) {
            $select = ( $vars == $opts["en"][$i] ) ? "selected=\"selected\"" : "";

            // Building option
            $data[] = "<option value=\"{$opts["en"][$i]}\" "
                      .$select.">{$opts["pt"][$i]}</option>";
        }

        // Returning
        return $data;
    }

    /**
     * Find the current age of a person by the birthdate, returns the current
     * age.
     *
     * Accepts any string that's also acceptable in strtotime(), including:
     * - "2014-01-22 10:29:14 am" (year-month-day hour:minute:seconds am/pm);
     * - "Wed, 22 Jan 2014 10:29:41 -0200" (RFC Date);
     * - "20140122102949" (year-month-day-hour-minute-seconds);
     *
     * @param string $birthday
     *      The string referring to the birthdate
     * @return int|bool
     *      The current age of the person or boolean false if date is invalid
     */
    public static function findAgesByTime( $birthday )
    {
        if ( $birthday === false || $birthday === 0 ) return false;
        $date = explode( '-', date( 'Y-m-d', $birthday ) );
        $y_diff = date( 'Y' ) - $date[0];
        $m_diff = date( 'm' ) - $date[1];
        $d_diff = date( 'd' ) - $date[2];
        if ( $d_diff < 0 || $m_diff < 0 ) $y_diff -= 1;
        return $y_diff;
    }

    // 09: ARRAY MANAGEMENT/ORDERING
    // ------------------------------------------------------------------

    /**
     * Alias for "sort", "rsort", "ksort" and "krsort", orders array by
     * values or keys, according to the parameters in the method.
     *
     * $reverse, makes the method sort the array in the descending order, when
     * true.
     *
     * $keys, orders the array by its keys, instead of values, when true.
     *
     * @param array $array
     *      Sortable array
     * @param bool $reverse
     *      Optional, sets descending order on
     * @param bool $keys
     *      Optional, sorts arrays by keys
     * @return array
     *      Sorted array
     */
    public static function arraySort( $array, $reverse = false, $keys = false )
    {
        if ( true === $reverse ) {
            ( true === $keys ) ? krsort( $array ) : rsort( $array );
        } else {
            ( true === $keys ) ? ksort( $array ) : sort( $array );
        }
        return $array;
    }

    /**
     * Basically an alias for array_unique(), sorts an array, removes duplicate
     * values and resets the key values.
     *
     * Recommended use for arrays where the keys are numbers instead of strings.
     *
     * @param array $array
     *      Array to be cleaned
     * @return array
     *      Cleaned array
     */
    public static function arrayWash( $array )
    {
        sort( $array );
        $array = array_unique( $array );
        $list = array();
        foreach ( $array as $item ) {
            $list[] = $item;
        }
        return $list;
    }

    /**
     * Splits an array into several smaller groups, containing the exact number
     * of members as defined.
     *
     * What it does, basically:
     * - With the array: ( A, B, C, D, E, F, G );
     * - Set the $group_size to '3';
     * - The returned array will be: ( ( A, B, C ), ( D, E, F ), ( G ) );
     *
     * @param array
     *      Array to be split
     * @param int $group
     *      Optional, max members allowed on each group
     * @param bool $sort
     *      Optional, if values should be sorted, or not
     * @param bool $reverse
     *      Optional, if values should be sorted in descending order or not
     * @return array
     *      Split array
     */
    public static function arrayGroup(
        $array,
        $group = 3,
        $sort = false,
        $reverse = false
    ) {
        if ( $sort ) {
            $array = self::arraySort( $array, $reverse );
        }

        $group = ( is_numeric( $group ) && $group >= 3 ) ? $group : 3;

        $list = array();
        $init = 0;
        $last = $group;
        $nums = ceil( count( $array ) / $group );

        for ( $l = 0; $l < $nums; $l ++ ) {
            $temp = array();
            for ( $n = $init; $n < $last; $n ++ ) {
                if ( isset( $array[$n] ) ) {
                    $temp[] = $array[$n];
                }
            }
            $list[] = $temp;
            $init += $group;
        }

        return $list;
    }

    // 10: ITEM GETTERS
    // ------------------------------------------------------------------

    /**
     * Gets an item/folder from $path by its name or ordinal identifier (the
     * array index + 1).
     *
     * If getting the data by its name, the method returns the item id (ordinal).
     *
     * If getting the data by its id, the method returns the name of the item
     * with the corresponding ordinal.
     *
     * Uses `itemPullById` and `itemPullByName` to scan $path and find/return
     * the data desired.
     *
     * The ordinal number is always relative to the alphabetical order.
     *
     * IMPORTANT:
     * Remember that, when searching by Id, the index value should be equal to
     * the array value + 1 (so it always starts form 1).
     *
     * @param string $path
     *      Path where the file/folder is located
     * @param int|string $vars
     *      The file/folder index or name
     * @param bool $by_id
     *      Optional, toggles search by Id, which returns the file/folder name,
     *      by default it receives a name and returns its Id, default: false
     * @param bool $is_file
     *      Optional, toggles search for a file or folder, default: true
     * @return int|string|bool
     *      File or folder index/name, or boolean false if non-existent
     */
    public static function itemPull(
        $path,
        $vars,
        $by_id = false,
        $is_file = true
    ) {
        $by_id = ( true === $by_id ) ? true : false;
        $is_file = ( true === $is_file ) ? true : false;
        if ( true == $by_id ) {
            return self::itemPullByID( $path, $vars, $is_file );
        } else {
            return self::itemPullByName( $path, $vars, $is_file );
        }
    }

    /**
     * Returns a file/folder name from a path.
     *
     * The $id is the (array index + 1) of the file, relative to the alphabetical
     * order in the folder. This was thought, so it always starts from 1.
     *
     * @param string $path
     *      Path where the file/folder is located
     * @param int $id
     *      Index value of the file/folder, in alphabetical order, equivalent
     *      to its array index + 1
     * @param bool $is_file
     *      Optional, toggles search for a file or folder, default: true
     * @return string|bool
     *      Name of the file, if found, or boolean false if not
     */
    public static function itemPullByID( $path, $id, $is_file = true )
    {
        $path = self::pathRead( $path );
        if ( !is_numeric( $id ) || $id < 1 ) return false;
        $scan = self::folderRead( $path );
        sort( $scan );

        // Searching for the item
        foreach ( $scan as $item ) {
            if ( true === $is_file && is_file( $path.$item ) ) {
                $f = ( isset( $f ) ) ? $f + 1 : 1;
                if ( $f == $id ) return $item;
            } elseif ( false === $is_file && is_dir( $path.$item ) ) {
                $n = ( isset( $n ) ) ? $n + 1 : 1;
                if ( $n == $id ) return $item;
            }
        }
        return false;
    }

    /**
     * Returns a file/folder index from a path. The value is equivalent to
     * (array index + 1), relative to its placement in alphabetical order inside
     * the folder. So it will always starts from 1, instead of 0.
     *
     * @param string $path
     *      Path where the file/folder is located
     * @param string $name
     *      Name of the file/folder whose index we want
     * @param bool $is_file
     *      Optional, toggles search for a file or folder, default: true
     * @return int|bool
     *      Index of the file, if found, or boolean false if not
     */
    public static function itemPullByName( $path, $name, $is_file = true )
    {
        $path = self::pathRead( $path );
        if ( trim( $name ) == "" ) return false;
        $scan = self::folderRead( $path );
        sort( $scan );

        // Searching for the item
        foreach ( $scan as $item ) {
            if ( true === $is_file && is_file( $path.$item ) ) {
                $f = ( isset( $f ) ) ? $f + 1 : 1;
                if ( $item == $name ) return $f;
            } elseif ( false === $is_file && is_dir( $path.$item ) ) {
                $n = ( isset( $n ) ) ? $n + 1 : 1;
                if ( $item == $name ) return $n;
            }
        }
        return false;
    }

    // 11: PDO AND SQL TOOLS
    // ------------------------------------------------------------------

    /**
     * Initializes a PDO object. Works only with MySQL and SQLite for now.
     *
     * IMPORTANT:
     * When using SQLite, the $path variable should have the full path to the
     * file where the database will be stored.
     *
     * @param string $type
     *      Database type, 'mysql' or 'sqlite'
     * @param string $path
     *      Database URL (MySQL) or file path (SQLite)
     * @param string $name
     *      Optional, database name, required when using MySQL only,
     *      default: null
     * @param string $user
     *      Optional, database user, required when using MySQL only,
     *      default: null
     * @param string $pass
     *      Optional, database password, required when using MySQL only,
     *      default: null
     * @return PDO|bool
     *      PDO Object if successful, or boolean false in case of failure
     */
    public static function dbInit(
        $type,
        $path,
        $name = "",
        $user = "",
        $pass = ""
    ) {
        $path = trim( $path );
        $type = trim( $type );
        if ( $path == "" ) return false;
        if ( $type != "sqlite" && $type != "mysql" ) return false;
        if ( $type == "mysql" && ( $name == "" || $user == "" ) ) return false;

        // Building PDO commands
        $cmds = ( $type == "sqlite" )
            ? "sqlite:{$path}"
            : "mysql:host={$path};dbname={$name}";

        // Connect and return using try-catch
        try {
            return new PDO( $cmds, $user, $pass );
        } catch( PDOException $erro ) {
            die( "Erro ao inicializar PDO: {$erro->getMessage()}" );
        } catch( Exception $erro ) {
            die( "Erro: {$erro->getMessage()}" );
        }
    }

    /**
     * Checks the type of driver being used in a PDO object and returns it.
     *
     * @param PDO $handle
     *      PDO object handle
     * @return string
     *      Driver name, lowercase (mysql, sqlite, etc.)
     */
    public static function dbTest( $handle )
    {
        return $handle->getAttribute( PDO::ATTR_DRIVER_NAME );
    }

    /**
     * Generates a SQL query for INSERT or UPDATE operations, according to the
     * parameters.
     *
     * The $data parameter should be an associative array, where the keys are
     * the column name to be filled/updated in the database.
     *
     * @param array $data
     *      Associative array containing the data to be saved, keys are the
     *      column names for the values
     * @param string $table
     *      Table name
     * @param bool $update
     *      Optional, toggles generation of an UPDATE query, instead of the
     *      default CREATE, default: false
     * @param string $primary_key
     *      Optional, column name to be used as primary key, required when
     *      $update is set to true, default: null
     * @param string $primary_value
     *      Optional, required when $update is set to true and $primary_key is
     *      defined, it's the value of the primary key for the UPDATE query,
     *      default: null
     * @return string|bool
     *      SQL query, ready to be used, or boolean false if any data is invalid
     */
    public static function dbQueryBuild(
        $data,
        $table,
        $update = false,
        $primary_key = null,
        $primary_value = null
    ) {
        // Field arrays
        $keys = array();
        $vals = array();

        $update = ( false === $update ) ? false : true;
        if ( !is_array( $data ) || count( $data ) < 1 ) return false;
        if ( trim( $table ) == "" ) return false;
        if (
            $update
            && ( trim( $primary_key ) == "" || trim( $primary_value == "" ) )
        ) {
            return false;
        }

        // Building the fields
        foreach ( $data as $k => $v ) {
            // If the field isn't empty
            if ( trim( $v ) != "" ) {
                // Checking $keys
                $keys[] = ( $update )
                    ? "`{$k}`='".self::stringDecs( $v )."'"
                    : "`{$k}`";
                // Checking $vals
                if ( !$update ) $vals[] = "'".self::stringDecs( $v )."'";
            }
        }

        // If no key-pairs were found
        if ( count( $keys ) == 0 && count( $vals ) == 0 ) return false;

        // Building the query
        $query = ( $update )
            ? "UPDATE `{$table}` SET ".implode( ", ", $keys )
              ." WHERE `{$primary_key}`='{$primary_value}';"
            : "INSERT INTO `{$table}`(".implode( ", ", $keys ).")"
              ." VALUES(".implode( ", ", $vals ).");";
        return $query;
    }

    /**
     * Checks if a table exists in the database with PDO.
     *
     * Requires PDO with MySQL or SQLite drivers.
     *
     * @param string $name
     *      Table name to search for
     * @param PDO $handle
     *      PDO object handle
     * @return bool
     *      True if table exists, false if don't
     */
    public static function dbTablesTest( $name, $handle )
    {
        $name = trim( $name );
        if ( $name == "" ) return false;

        // If handle isn't a PDO object
        if ( !is_object( $handle ) && get_class( $handle ) != "PDO" ) {
            return false;
        }

        $type = self::dbTest( $handle );

        // Checking test query
        switch ( $type ) {
            case "sqlite":
                $cmds = "SELECT COUNT(*) FROM `sqlite_master` ";
                $cmds.= "WHERE `type`='table' AND `name`='{$name}';";
                break;
            case "mysql":
                $cmds = "SELECT COUNT(*) FROM `information_schema`.`tables` ";
                $cmds.= "WHERE `table_name`='{$name}';";
                break;
            default:
                return false;
                break;
        }

        $fetch = $handle->query( $cmds )->fetchColumn();
        return ( $fetch > 0 ) ? true : false;
    }

    /**
     * Checks if a table exists in the database and, if it doesn't, use the
     * SQL query in the $command parameter to create it.
     *
     * Requires PDO with MySQL or SQLite drivers.
     *
     * @param string $name
     *      Table name to check
     * @param string $command
     *      SQL command for CREATE TABLE
     * @param PDO $handle
     *      PDO object handle
     * @return bool
     *      True is successful, false if not
     */
    public static function dbTablesInit( $name, $command, $handle )
    {
        $name = trim( $name );
        $command = trim( $command );
        if ( $name == "" || $command == "" ) return false;

        $test = self::dbTablesTest( $name, $handle );
        if ( false === $test ) {
            $init = $handle->exec( $command );
            return ( false === $init || $init < 1 ) ? false : true;
        }
        return false;
    }

    /**
     * Fetches the table names and creation commands for the tables in the
     * database.
     *
     * Optionally extracts the contents too.
     *
     * Requires PDO with MySQL or SQLite drivers.
     *
     * @param PDO $handle
     *      PDO object handle
     * @param bool $commands_only
     *      Optional, defines if the method should return only the CREATE
     *      commands, default: false
     * @return array|bool
     *      Associative array with tables data, or boolean false if nothing
     *      is found
     */
    public static function dbPullTables( $handle, $commands_only = false )
    {
        $type = self::dbTest( $handle );
        switch ( $type ) {
            case "sqlite":
                return self::dbPullTablesSQLite( $handle, $commands_only );
                break;
            case "mysql":
                return self::dbPullTablesMySQL( $handle, $commands_only );
                break;
            default;
                return false;
        }
    }

    /**
     * Pulls table names and creation commands in a MySQL database.
     *
     * Optionally extracts the contents too.
     *
     * Requires PDO with MySQL driver.
     *
     * @param PDO $handle
     *      PDO object handle, with the MySQL driver
     * @param bool $commands_only
     *      Optional, defines if the method should return only the CREATE
     *      commands, default: false
     * @return array|bool
     *      Associative array with tables data, or boolean false if nothing
     *      is found
     */
    public static function dbPullTablesMySQL(
        $handle,
        $commands_only = false
    ) {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );
        if ( !is_object( $handle ) && get_class( $handle ) != "PDO" ) {
            return false;
        }

        // Return array
        $tables = array(
            "name" => array(),
            "cmds" => array(),
            "data" => array()
        );

        // Query to extract tables
        $query = "SHOW TABLES;";
        $fetch = $handle->query( $query )->fetchAll( PDO::FETCH_ASSOC );

        // If fetch returns results
        if ( count( $fetch ) > 0 ) {
            // Loops through results
            foreach ( $fetch as $item ) {
                // Extracting table names
                foreach ( $item as $keys => $name ) {
                    // Pulling create commands
                    $cmds = "SHOW CREATE TABLE `{$name}`;";

                    // Extract command
                    $push = $handle->query( $cmds )->fetch();

                    // Add table name to the proper array
                    $tables["name"][] = $name;

                    // Add create command
                    $tables["cmds"][] = $push["Create Table"].";";

                    // Adds content, when $commandsOnly is FALSE
                    if ( false === $commands_only ) {
                        // Pull data from tables
                        $contents = self::dbPullData( $handle, $name );

                        // If commands are ok
                        if ( false !== $contents ) {
                            $tables["data"][] = $contents;
                        } else {
                            $tables["data"][] = "";
                        }
                    }
                }
            }
            return $tables;
        }
        return false;
    }

    /**
     * Pulls table names and creation commands in a SQLite database.
     *
     * Optionally extracts the contents too.
     *
     * Requires PDO with SQLite driver.
     *
     * @param PDO $handle
     *      PDO object handle, with the SQLite driver
     * @param bool $commands_only
     *      Optional, defines if the method should return only the CREATE
     *      commands, default: false
     * @return array|bool
     *      Associative array with tables data, or boolean false if nothing
     *      is found
     */
    public static function dbPullTablesSQLite(
        $handle,
        $commands_only = false
    ) {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );
        if ( !is_object( $handle ) && get_class( $handle ) != "PDO" ) {
            return false;
        }

        // Return array
        $tables = array();

        // Query to verify if tables existence
        $query = "SELECT COUNT( `tbl_name` ) FROM `sqlite_master`;";
        $fetch = $handle->query( $query )->fetchColumn();

        // If tables exists
        if ( $fetch > 0 ) {
            // Data query
            $query = "SELECT `tbl_name` FROM `sqlite_master`;";
            $fetch = $handle->query( $query );

            // Extracting data
            while ( $line = $fetch->fetch( PDO::FETCH_ASSOC ) ) {
                // Getting table name
                $tables["name"][] = $line["tbl_name"];

                // Getting creation query
                $cmds = "SELECT `sql` FROM `sqlite_master` ";
                $cmds.= "WHERE `tbl_name`='{$line['tbl_name']}' ";
                $cmds.= "AND type='table';";

                // If command query returns false, skip this
                if ( false === $handle->query( $cmds ) ) break;
                $pull = $handle->query( $cmds )->fetch( PDO::FETCH_COLUMN );

                // Add create command
                $tables["cmds"][] = $pull.";";

                // Adds content, when $commands_only is FALSE
                if ( false === $commands_only ) {
                    // Pull data from tables
                    $contents = self::dbPullData( $handle, $line["tbl_name"] );

                    // If commands are ok
                    if ( false !== $contents ) {
                        $tables["data"][] = $contents;
                    } else {
                        $tables["data"][] = "";
                    }
                }
            }
            return $tables;
        }
        return false;
    }

    /**
     * Pulls data from tables in MySQL or SQLite databases.
     *
     * It's intended use is for both `dbPullTablesMySQL` and `dbPullTablesSQLite`.
     *
     * Requires PDO with SQLite driver.
     *
     * @param PDO $handle
     *      PDO object handle, with MySQL or SQLite drivers
     * @param string $table
     *      Table name to extract data
     * @return string|bool
     *      String with the table data or boolean false on failure or empty table
     */
    public static function dbPullData( $handle, $table )
    {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );

        // Return array
        $return = array();

        // Checking data
        $query = "SELECT COUNT(*) FROM `{$table}`;";
        $count = $handle->query( $query )->fetchColumn();

        // If data is found
        if ( $count > 0 ) {
            // Data query
            $query = "SELECT * FROM `{$table}`;";
            $fetch = $handle->query( $query );

            // Looping through all entries
            while ( $line = $fetch->fetch( PDO::FETCH_ASSOC ) ) {
                // Key and Value arrays
                $keys = array();
                $vals = array();

                // Filling keys and values
                foreach ( $line as $name => $item ) {
                    $keys[] = "`{$name}`";
                    $vals[] = "'".self::stringSingleQuotes(
                        self::stringEncs( $item )
                    )."'";
                }

                // Building query and adding to return
                $cmds = "INSERT INTO `{$table}` (".implode( ", ", $keys ).") ";
                $cmds.= "VALUES (".implode( ", ", $vals ).");";
                $return[] = $cmds;
            }
            return implode( "\r\n", $return );
        }
        return false;
    }

    /**
     * Converts the data output from `dbPullTables`, `dbPullTablesMySQL` and
     * `dbPullTablesSQLite` and creates a string, for saving.
     *
     * @param array $data
     *      Content extracted from the database by the class' methods
     * @return string
     *      String with SQL data ready to be saved.
     */
    public static function dbPullSave( $data )
    {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );

        // Return array
        $file = array();

        // Counting tables
        $nums = count( $data['name'] );

        // Generating header
        $head = "-- BACKUP CREATION DATE: '".date( 'r' )."'";

        // Adding to file
        $file[] = self::stringEncs( $head )."\r\n\r\n";

        // Adding commands and contents
        for ( $n = 0; $n < $nums; $n++ ) {
            // Table header
            $head = "-- COMMANDS FOR TABLE: '".$data['name'][$n]."'";
            $file[] = "\r\n".self::stringEncs( $head )."\r\n";
            $file[] = self::stringEncs( $data['cmds'][$n] )."\r\n";

            // If there are commands, add'em
            if ( isset( $data['data'][$n] ) && $data['data'][$n] != '' ) {
                $head = "\r\n-- CONTENTS FOR TABLE: '".$data['name'][$n]."'";
                $file[] = self::stringEncs( $head )."\r\n";
                $file[] = self::stringEncs( $data['data'][$n] );
            }
        }

        $file = implode( "\r\n", $file );
        return $file;
    }

    /**
     * Converts MySQL dumps into SQLite compatible dumps and vice-versa.
     *
     * IMPORTANT:
     * Build to use only within this class, experimental.
     *
     * @param string $data
     *      SQL dump string
     * @param string $type
     *      Input dump type (mysql or sqlite)
     * @return string
     *      Converted output
     */
    public static function dbPullConverts( $data, $type )
    {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );

        // Checks $type
        $type = ( "sqlite" == trim( $type ) ) ? "sqlite" : "mysql";

        // According to driver, execute conversion
        if ( $type == "mysql" ) {
            // MySQL regex flags
            $flag = array(
                0   => "#(int|INT|TINYINT|tinyint|SMALLINT|smallint|"
                       ."MEDIUMINT|mediumint|BIGINT|bigint)(\([0-9]+\))#",
                1   => "#([\t\s\r\n]+)(\))([\s|\t]?ENGINE)([\sA-Za-z0-9\=\_]+)(;)#",
                2   => "#,([\r\n\s]+)PRIMARY\sKEY\s\(`([A-Za-z0-9_]+)`\)#",
                3   => "#(NULL\s[PRIMARY\sKEY\s?]?AUTO_INCREMENT)#"
            );

            // Replacing data
            $data = preg_replace( $flag[0], "INTEGER", $data );
            $data = preg_replace( $flag[1], "\r\n);", $data );
            $data = preg_replace( $flag[2], "", $data );
            $data = preg_replace( $flag[3], "NULL PRIMARY KEY", $data );
        } elseif ( $type == "sqlite" ) {
            // SQLite regex flags
            $flag = array(
                "#NULL PRIMARY KEY#",
                "#([\r\n]+)([\s\t]+)?\);#"
            );

            // Replacing data
            $data = preg_replace(
                $flag[0],
                "NULL PRIMARY KEY AUTO_INCREMENT",
                $data
            );
            $data = preg_replace(
                $flag[1],
                "\r\n) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
                $data
            );
        }

        return $data;
    }

    // 12: DATA PAGINATION
    // ------------------------------------------------------------------
    
    /**
     * @param string $item_count
     *      Total number of items to be paginated
     * @param string $page
     *      Current page being viewed
     * @param string $page_size
     *      Page size, or results per page
     * @param string $link_prefix
     *      Link prefix, what comes before the page number
     *      (e.g.: http://test.com/page/[page-number])
     * @param string $link_suffix
     *      Optional, anything that comes after the link and page number,
     *      default: null
     * @param bool $show_pager
     *      Optional, toggles visibility of previous/next links, default: true
     * @param bool $show_edges
     *      Optional, toggles visibility of first/last links, default: true
     * @param bool $show_numbers
     *      Optional, toggles numeric pagination on/off, default: true
     * @param int $side_numbers
     *      Optional, maximum number of numeric links on each side of the
     *      current page, when navigating long lists, default: 2
     * @param string $body_wrap
     *      Optional, tag name of the main navigation wrapper, default: ul
     * @param string $link_wrap
     *      Optional tag name of each link in the navigation, default: li
     * @param string $body_class
     *      Optional, class of the main navigation wrapper, default: paginate
     * @param string $link_class
     *      Optional, class of each link in the navigation, default: paginate-item
     * @param string $active_class
     *      Optional, active link class, default: active
     * @return string
     *      Pagination element or an empty string
     */
    public static function paginateBase(
        $item_count,
        $page,
        $page_size,
        $link_prefix,
        $link_suffix    = null,
        $show_pager     = true,
        $show_edges     = true,
        $show_numbers   = true,
        $side_numbers   = 2,
        $body_wrap      = "ul",
        $link_wrap      = "li",
        $body_class     = "paginate",
        $link_class     = "paginate-item",
        $active_class   = "active"
    ) {
        // Calculate pagination variables
        $page = ( is_numeric( $page ) && $page > 1 ) ? $page : 1;
        $page_size = ( is_numeric( $page_size ) && $page_size > 0 )
            ? $page_size : 10;
        $total = ceil( $item_count / $page_size );
        if ( $total == 1 ) return "";
        if ( trim( $link_prefix ) == "" ) return "";
        
        // Check classes
        $class_wrap = ( $body_class != "" ) ? " class=\"{$body_class}\"" : "";
        $class_link = ( $link_class != "" ) ? " class=\"{$link_class}\"" : "";
        $class_curr = ( $link_class != "" && $active_class != "" )
            ? " class=\"{$link_class} {$active_class}\"" : "";
        
        // Define pagination values
        $page_init = 1;
        $page_prev = ($page > 1) ? $page - 1 : 1;
        $page_next = ($page < $total) ? $page + 1 : $total;
        
        // Navigation array
        $navi = array();
        
        $navi[] = "<{$body_wrap}{$class_wrap}>";
        
        // First Page
        if ( $show_edges ) {
            $navi[] = "<{$link_wrap}{$class_link}>";
            $navi[] = "<a href=\"{$link_prefix}{$page_init}{$link_suffix}\">";
            $navi[] = "&laquo; Primeira";
            $navi[] = "</a>";
            $navi[] = "</{$link_wrap}>";
        }
        
        // Previous Page
        if ( $show_pager ) {
            $navi[] = "<{$link_wrap}{$class_link}>";
            $navi[] = "<a href=\"{$link_prefix}{$page_prev}{$link_suffix}\">";
            $navi[] = "&lsaquo; Anterior";
            $navi[] = "</a>";
            $navi[] = "</{$link_wrap}>";
        }
    
        // Numeric Links
        if ( $show_numbers ) {
            // Define start/finish
            if ( $total > 5 ) {
                if ( $page <= 2 ) {
                    $nums_init = 1;
                    $nums_last = 5;
                } elseif ( $page >= $total - 1 ) {
                    $nums_init = $total - ( $side_numbers * 2 );
                    $nums_last = $total;
                } else {
                    $nums_init = $page - $side_numbers;
                    $nums_last = $page + $side_numbers;
                }
            } else {
                $nums_init = 1;
                $nums_last = $total;
            }
            // Build
            for ( $i = $nums_init; $i <= $nums_last; $i++ ) {
                $navi[] = ( $i == $page )
                    ? "<{$link_wrap}{$class_curr}>"
                    : "<{$link_wrap}{$class_link}>";
                $navi[] = "<a href=\"{$link_prefix}{$i}{$link_suffix}\">";
                $navi[] = $i;
                $navi[] = "</a>";
                $navi[] = "</{$link_wrap}>";
            }
        }
    
        // Next Page
        if ( $show_pager ) {
            $navi[] = "<{$link_wrap}{$class_link}>";
            $navi[] = "<a href=\"{$link_prefix}{$page_next}{$link_suffix}\">";
            $navi[] = "Pr&oacute;xima &rsaquo;";
            $navi[] = "</a>";
            $navi[] = "</{$link_wrap}>";
        }
    
        // Last Page
        if ( $show_edges ) {
            $navi[] = "<{$link_wrap}{$class_link}>";
            $navi[] = "<a href=\"{$link_prefix}{$total}{$link_suffix}\">";
            $navi[] = "&Uacute;ltima &raquo;";
            $navi[] = "</a>";
            $navi[] = "</{$link_wrap}>";
        }
        
        $navi[] = "</{$body_wrap}>";
        
        return implode( "", $navi );
    }
    
    /**
     * @param string $item_count
     *      Total number of items to be paginated
     * @param string $page_size
     *      Page size, or results per page
     * @param string $page
     *      Optional, current page being viewed, will be marked as selected,
     *      default: null
     * @return string
     *      Pagination options
     */
    public static function paginateOpts(
        $item_count,
        $page_size,
        $page = null
    ) {
        // Calculate pagination variables
        $page_size = ( is_numeric( $page_size ) && $page_size > 0 )
            ? $page_size : 10;
        $total = ceil( $item_count / $page_size );
        
        // Return array
        $opts = array();
        
        // Build options
        for ( $i = 1; $i <= $total; $i++ ) {
            $select = ( $i === $page ) ? " selected=\"selected\"" : "";
            $opts[] = "<option value=\"{$i}\" {$select}>{$i}</option>";
        }
        
        // Cleaning extra spaces
        $opts = preg_replace( "/\s{2}/", " ", implode( "", $opts ) );
        $opts = preg_replace( "/\s\>/", ">", $opts );
        return $opts;
    }

    // 13: ZIP FILE PACKING/UNPACKING
    // ------------------------------------------------------------------

    /**
     * Compresses the content in $path into a single ZIP file.
     *
     * You can set the name of the saved file or leave as default, which follows
     * the following pattern:
     * [year]-[month]-[day]-[hour]-[minute].zip
     *
     * @param string $path
     *      Folder to be zipped
     * @param string $save
     *      Path to save the zipped file
     * @param string $file
     *      Optional, ZIP file name
     * @param bool $overwrite
     *      Optional, sets overwriting mode for files with the same name
     * @return bool
     *      True if successful, false if not
     */
    public static function zipFilePack(
        $path,
        $save,
        $file = "",
        $overwrite = true
    ) {
        $path = self::pathRead( $path );
        $save = self::pathRead( $save );

        $file = ( trim( $file ) != "" )
            ? trim( $file ).".zip"
            : date( "Y-m-d-h-i" ).".zip";

        $list = array(
            "path"  => array(),
            "file"  => array()
        );

        $scan = self::pathList( $path, true );
        foreach ( $scan as $item ) {
            if ( is_dir( $item ) ) {
                $list["path"][] = str_replace( $path, "", $item );
            } else {
                // Str_replace here causes errors, so we won't use
                $list["file"][] = $item;
            }
        }

        $list["path"] = self::arrayWash( $list["path"] );
        $list["file"] = self::arrayWash( $list["file"] );

        sort( $list["path"] );
        sort( $list["file"] );

        $nums = count( $list["path"] ) + count( $list["file"] );

        if ( $nums > 0 ) {
            $zipFile = new ZipArchive();

            $zipMode = ( file_exists( $save.$file ) )
                ? ZipArchive::OVERWRITE
                : ZipArchive::CREATE;

            if ( file_exists( $save.$file ) && false === $overwrite ) {
                return false;
            }

            if ( $zipFile->open( $save.$file, $zipMode ) !== true ) {
                // If not possible to open ZIP file, return false
                return false;
            }

            // Filling all paths
            foreach ( $list["path"] as $item ) {
                // Adding folder
                $zipFile->addEmptyDir( $item );
            }

            // Filling files
            foreach ( $list["file"] as $item ) {
                $zipFile->addFile(
                    $item,
                    str_replace( $path, "", $item )
                );
            }

            // Closing
            return $zipFile->close();
        }

        return false;
    }

    /**
     * Unpacks a ZIP file into the $path folder.
     *
     * If $keep_struct is set to false, all file structures will be flattened,
     * and files will be saved into the same folder.
     *
     * @param string $file
     *      ZIP filepath
     * @param string $path
     *      Path to the folder where the ZIP file will be unpacked
     * @param bool $keep_struct
     *      Optional, when false, extracts all files to the same folder,
     *      default: true
     * @return bool
     *      True if successful, false if not
     */
    public static function zipFileUnpack( $file, $path, $keep_struct = true )
    {
        // Setting time limit to 0, to avoid timeout problems
        set_time_limit( 0 );
        // Setting memory limit, to avoid problems
        ini_set( "memory_limit", "64M" );

        $file = trim( $file );
        $path = self::pathRead( $path );

        if ( !file_exists( $file ) || !is_dir( $path ) ) return false;
        $keep_struct = ( false === $keep_struct ) ? false : true;

        // Initializing
        $init = new ZipArchive();

        // If opening is successful
        if ( true === $init->open( $file ) ) {
            // Checking structure
            if ( false === $keep_struct ) {
                // Extract and ignore structure
                for ( $i = 0; $i < $init->numFiles; $i++ ) {
                    // Pulling file name
                    $fileName = $init->getNameIndex( $i );
                    // Pull file data
                    $fileData = pathinfo( $fileName );
                    // Checking if it is a folder
                    if ( substr( $fileName, -1 ) !== "/" ) {
                        // Pull content via stream
                        $data = $init->getStream( $fileName );
                        $data = stream_get_contents( $data );
                        // Saving file
                        self::fileSave( $path.$fileData["basename"], $data );
                    }
                }
            } else {
                // Extracting
                $init->extractTo( $path );
            }
            return ( false === $init->close() ) ? false : true;
        }
        return false;
    }

    // 14: XML MANAGEMENT
    // ------------------------------------------------------------------

    /**
     * Creates and returns a SimpleXMLElement object.
     *
     * @param string $node
     *      Optional, XML file root node, default: info
     * @return SimpleXMLElement|bool
     *      SimpleXMLElement object or boolean false if non existent
     */
    public static function xmlMake( $node = "" )
    {
        if ( !class_exists( "SimpleXMLElement" ) ) return false;
        $head = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
        $node = ( trim( $node ) != "" ) ? "<".trim( $node )."/>" : "<info/>";
        return new SimpleXMLElement( $head.$node, null, false );
    }

    /**
     * Opens an XML file, returning it as a SimpleXMLElement.
     *
     * @param string $file
     *      Full path to the XML file
     * @return SimpleXMLElement|bool
     *      SimpleXMLElement object or boolean false if invalid
     */
    public static function xmlOpen( $file )
    {
        if ( !class_exists( "SimpleXMLElement" ) ) return false;
        $file = trim( $file );
        if ( !file_exists( $file ) ) return false;
        return new SimpleXMLElement( $file, null, true );
    }

    /**
     * Converts a SimpleXMLElement object into a string, with DOMDocument.
     *
     * @param SimpleXMLElement $data
     *      SimpleXMLElement object
     * @return string|bool
     *      String with the XML data, or boolean false on failure/invalid
     */
    public static function xmlText( $data )
    {
        if ( !class_exists( "DOMDocument" ) ) return false;
        if ( !class_exists( "SimpleXMLElement" ) ) return false;
        $xmls = new DOMDocument( "1.0", "UTF-8" );
        $xmls->preserveWhiteSpace = false;
        $xmls->formatOutput = true;
        $xmls->loadXML( $data->asXML() );
        return $xmls->saveXML();
    }

    /**
     * Saves a SimpleXMLElement object to a file, in the desired path.
     *
     * @param SimpleXMLElement $data
     *      SimpleXMLElement object
     * @param string $file
     *      Full path to the file element to be saved by the method
     * @param bool $overwrite
     *      Optional, toggles overwriting files with the same name,
     *      default: false
     * @return bool
     *      True on success, false on failure
     */
    public static function xmlSave( $data, $file, $overwrite = false )
    {
        $data = self::xmlText( $data );
        $file = trim( $file );
        $overwrite = ( false === $overwrite ) ? false : true;
        return self::fileSave( $file, $data, $overwrite );
    }

    /**
     * This method is meant to be used together with 'xmlSortNode' and
     * 'xmlSortArgs'.
     *
     * Receives an array, extracted from a SimpleXMLElement object using the
     * 'xpath' method, properly formatting it, together with 'xmlSortNode', so
     * that it can be saved into an XML File.
     *
     * This is meant to be used when you have an XML file where the child nodes
     * from the root all have the same name/attributes, like "posts->post" or
     * similar things.
     *
     * Instructions on how to use it:
     * - Take, for an example, an XML file with the root node "events", where
     * you need all the child nodes named "event" ordered by the "id" attribute;
     * - First, you need to apply 'xpath' in the SimpleXML object, extracting
     * all the child nodes into an array, using: $xml->xpath('/events/event');
     * - After extracting, pass the array through 'xmlSortArgs', using the
     * proper flag ('id', with $isAttr set to TRUE), and the order (ASC or DESC),
     * so the XML file will be properly sorted;
     * - After 'xmlSortArgs', you use this method to properly order and format
     * the XML array into a XML object;
     *
     * In code, the order would be:
     * - 1: $data = $xml->xpath( '/events/event' ); // Extract with xpath
     * - 2: $data = Zero::xmlSortArgs( $data, 'id', true ); // Sort child nodes by
     * id attribute;
     * - 3: $data = Zero::xmlSortMain( 'event', $data, $object );
     *
     * IMPORTANT TO NOTE:
     * If your xpath is "/[root]/[node]", the value in $node equals to [node],
     * meaning that the empty SimpleXML $object must come with the [root] node
     * pre-declared;
     *
     * @param string $node
     *      Name of the child nodes to be added on root
     * @param array $data
     *      Array, with data extracted from a SimpleXML object
     * @param SimpleXMLElement $object
     *      Empty SimpleXML object, with the root node declared
     * @return SimpleXMLElement|bool
     *      SimpleXML object, properly filled with the sorted data, or boolean
     *      false, if invalid node
     */
    public static function xmlSortMain( $node, $data, $object )
    {
        /**
         * Recursive closure, properly formats and sorts the XML data in
         * $contents into the $object data.
         *
         * @param SimpleXMLElement $object
         *      SimpleXML node object
         * @param SimpleXMLElement $contents
         *      SimpleXML object array, to be inserted into $object
         * @return SimpleXMLElement|bool
         *      SimpleXML node object, with proper sorting and filled
         */
        $sort_node = function( $object, $contents ) use ( &$sort_node ) {
            // Add child nodes and content
            foreach ( $contents->children() as $keys => $vals ) {
                /**
                 * @var $keys string
                 * @var $vals SimpleXMLElement
                 */
                // Has child nodes?
                if ( $vals->count() > 0 ) {
                    $sort_node( $object->addChild( $keys ), $vals );
                } else {
                    $item = $object->addChild( $keys, $vals );

                    // Add attributes to single item
                    foreach ( $vals->attributes() as $attr => $vars ) {
                        $item->addAttribute( $attr, $vars );
                    }
                }
            }

            // Add attributes to main node
            foreach ( $contents->attributes() as $keys => $vals ) {
                $object->addAttribute( $keys, $vals );
            }

            return $object;
        };

        // Checking node
        $node = trim( $node );

        // If $node is empty, kills process
        if ( $node == '' ) return false;

        // Loops through all child nodes in the array to insert in the object
        foreach ( $data as $keys => $vals ) {
            /**
             * @var $keys string
             * @var $vals SimpleXMLElement
             */
            // Checking number of child nodes for the current node
            $nums = $vals->count();

            // Adding node
            $nodeData = ( $nums > 0 )
                // If has children, adds the empty node to be filled
                ? $object->addChild( $node )
                // If no children, or text only, adds the node with data
                : $object->addChild( $node, $vals );

            // Adding attributes first
            foreach ( $vals->attributes() as $name => $attr ) {
                $nodeData->addAttribute( $name, $attr );
            }

            // Adding child nodes
            if ( $nums > 0 ) {
                foreach ( $vals->children() as $name => $contents ) {
                    /**
                     * @var $name string
                     * @var $contents SimpleXMLElement
                     */
                    // If this node has child nodes
                    if ( $contents->count() > 0 ) {
                        // Adds the empty node
                        $sort_node( $nodeData->addChild( $name ), $contents );
                    } else {
                        $temp = $nodeData->addChild( $name, $contents );

                        // Add attributes
                        foreach ( $contents->attributes() as $attr => $vars ) {
                            $temp->addAttribute( $attr, $vars );
                        }
                    }
                }
            }
        }

        // Return XML data
        return $object;
    }

    /**
     * Sorts the nodes in an array extracted from a SimpleXMLElement object,
     * by using the `xpath` method.
     *
     * Companion to `xmlSortMail`.
     *
     * This method should be used BEFORE `xmlSortMain`, if used together, so
     * it can properly sort the child nodes for a properly ordered XML.
     *
     * @param array $data
     *      Array with smaller SimpleXMLElement objects, extracted from a full
     *      SimpleXMLElement object by xpath
     * @param string $flag
     *      Attribute or child node name reference, will be used as primary key
     * @param bool $is_attr
     *      Optional, defines if $flag will be considered an attribute or a
     *      node name, default: false
     * @param bool $reverse
     *      Optional, toggles descending order for the nodes, default: false
     * @return array|bool
     *      Properly sorted SimpleXML data array, for use with `xmlSortMain`,
     *      boolean false if no flag was provided
     */
    public static function xmlSortArgs(
        $data,
        $flag,
        $is_attr = false,
        $reverse = false
    ) {
        $flag = trim( $flag );
        if ( $flag == "" ) return false;

        $reverse = ( false === $reverse ) ? false : true;
        $is_attr = ( false === $is_attr ) ? false : true;
        $flag = ( $is_attr ) ? "['{$flag}']" : "->{$flag}";

        $args = ( $reverse )
            ? "\$b".$flag.", \$a".$flag
            : "\$a".$flag.", \$b".$flag;
        usort(
            $data,
            create_function( "\$a, \$b", "return strcmp( {$args} );" )
        );

        return $data;
    }

    // 15: SIMPLE CAPTCHA
    // ------------------------------------------------------------------

    /**
     * Generates a simple captcha image, returning an array with the base64
     * encoded image and a string, with the code, respectively.
     *
     * Using a TrueType font is optional, and the full path to the file must
     * be provided, if using one.
     *
     * Setting $wrap to true, wraps the base64 string in an img tag, ready to
     * be printed on screen.
     *
     * IMPORTANT:
     * The image generated will have its dimensions locked on 80 x 20.
     *
     * @param bool $wrap
     *      Optional, defines wrapping for the base64 string in an img tag,
     *      default: false
     * @param string $font
     *      Optional, full path to a TrueType font file for the image's text
     * @return array
     *      Array containing the base64 encoded image captcha and the code as
     *      a string
     */
    public static function captchaMake( $wrap = false, $font = "" )
    {
        $captcha = self::imgsMake( 80, 20, "png" );

        // Defines colors for the captcha
        $w = imagecolorallocate( $captcha, 255, 255, 255 );
        $g = imagecolorallocate( $captcha, 196, 196, 196 );
        $b = imagecolorallocate( $captcha,   0,   0,   0 );

        // Fills image with white
        imagefill( $captcha, 0, 0, $w );

        // Drawing the gradient
        $grad = 255;
        for ( $i = 0; $i < 20; $i++ ) {
            $temp = imagecolorallocate( $captcha, $grad, $grad, $grad );
            imageline( $captcha, 0, $i + 1, 80, $i + 1, $temp );
            $grad -= 2;
        }

        // Drawing vertical lines
        for ( $i = 0; $i < 8; $i++ ) {
            $rand_x = rand( 5, 75 );
            imageline( $captcha, $rand_x, 0, $rand_x, 20, $g );
        }

        // Adding noise
        for ( $i = 0; $i < 49; $i++ ) {
            $rand_x = rand( 1, 79 );
            $rand_y = rand( 1, 19 );
            imagesetpixel( $captcha, $rand_x, $rand_y, $b );
        }

        // Generating code
        $code = self::slugMake();

        // If font file is a valid one
        if ( trim( $font ) != "" && file_exists( $font ) ) {
            $data = imagettfbbox( 11, 0, $font, $code );

            $pos_x = ( 80 - ( $data[0] + $data[2] ) ) / 2;
            $pos_y = ( 20 - ( $data[1] + $data[7] ) ) / 2;

            imagettftext( $captcha, 11, 0, $pos_x, $pos_y, $b, $font, $code );
        } else {
            // Define font size
            $font = 5;
            $size = strlen( $code );
            $char_w = imagefontwidth( $font );
            $char_h = imagefontheight( $font );

            $text_w = $char_w * $size;
            $pos_x = ( 80 - $text_w ) / 2;
            $pos_y = ( 20 - $char_h ) / 2;

            // Drawing string
            imagestring( $captcha, $font, $pos_x, $pos_y, $code, $b );
        }

        // Using output buffer to "get" the image
        ob_start();
        imagepng( $captcha );
        $data = ob_get_contents();
        ob_end_clean();

        $imgs = "data:image/png;base64,".base64_encode( $data );
        $imgs = ( false === $wrap ) ? $imgs : "<img src=\"{$imgs}\" alt=\"\">";

        // Returning
        return array(
            $imgs,
            $code
        );
    }

    /**
     * Works the same way as `captchaMake()` but, instead of generating a single
     * code, draws a sum operation on the image (e.g.: 5+3) and returns the
     * string with the result together with it, for comparison.
     *
     * Using a TrueType font is optional, and the full path to the file must
     * be provided, if using one.
     *
     * Setting $wrap to true, wraps the base64 string in an img tag, ready to
     * be printed on screen.
     *
     * IMPORTANT:
     * The image generated will have its dimensions locked on 80 x 20.
     *
     * @param bool $wrap
     *      Optional, defines wrapping for the base64 string in an img tag,
     *      default: false
     * @param string $font
     *      Optional, full path to a TrueType font file for the image's text
     * @return array
     *      Array containing the base64 encoded image captcha and the result as
     *      a string
     */
    public static function captchaSums( $wrap = false, $font = "" )
    {
        $captcha = self::imgsMake( 80, 20, "png" );

        // Defines colors for the captcha
        $w = imagecolorallocate( $captcha, 255, 255, 255 );
        $g = imagecolorallocate( $captcha, 196, 196, 196 );
        $b = imagecolorallocate( $captcha,   0,   0,   0 );

        imagefill( $captcha, 0, 0, $w );

        // Drawing the gradient
        $grad = 255;
        for ( $i = 0; $i < 20; $i++ ) {
            $temp = imagecolorallocate( $captcha, $grad, $grad, $grad );
            imageline( $captcha, 0, $i + 1, 80, $i + 1, $temp );
            $grad -= 2;
        }

        // Drawing vertical lines
        for ( $i = 0; $i < 8; $i++ ) {
            $rand_x = rand( 5, 75 );
            imageline( $captcha, $rand_x, 0, $rand_x, 20, $g );
        }

        // Adding noise
        for ( $i = 0; $i < 49; $i++ ) {
            $rand_x = rand( 1, 79 );
            $rand_y = rand( 1, 19 );
            imagesetpixel( $captcha, $rand_x, $rand_y, $b );
        }

        $x = rand( 1, 9 );
        $y = rand( 1, 9 );
        $text = "{$x} + {$y}";
        $code = $x + $y;

        // If font file is a valid one
        if ( trim( $font ) != "" && file_exists( $font ) ) {
            // Build the text box
            $data = imagettfbbox( 11, 0, $font, $text );

            // Defining position
            $pos_x = ( 80 - ( $data[0] + $data[2] ) ) / 2;
            $pos_y = ( 20 - ( $data[1] + $data[7] ) ) / 2;

            // Drawing
            imagettftext( $captcha, 11, 0, $pos_x, $pos_y, $b, $font, $text );
        } else {
            // Define font size
            $font = 5;

            // Define string length
            $size = strlen( $text );

            // Define character dimensions
            $char_w = imagefontwidth( $font );
            $char_h = imagefontheight( $font );

            // Defines size and position
            $text_w = $char_w * $size;
            $pos_x = ( 80 - $text_w ) / 2;
            $pos_y = ( 20 - $char_h ) / 2;

            // Drawing string
            imagestring( $captcha, $font, $pos_x, $pos_y, $text, $b );
        }

        // Using output buffer to "get" the image
        ob_start();
        imagepng( $captcha );
        $data = ob_get_contents();
        ob_end_clean();

        $imgs = 'data:image/png;base64,'.base64_encode( $data );
        $imgs = ( false === $wrap ) ? $imgs : "<img src=\"{$imgs}\" alt=\"\">";

        // Returning
        return array(
            $imgs,
            $code
        );
    }

    // 16: IMAGE UPLOAD, RESIZING, FILTERS AND CSS POSITIONING
    // ------------------------------------------------------------------

    /**
     * Generates inline CSS for image tags. Use it when resizing/positioning
     * the image.
     *
     * Full path to the image file must be provided for this to properly work.
     *
     * If the image file is smaller than the target values, no resizing will
     * be applied.
     *
     * @param string $file
     *      Full path to the image file
     * @param int $target_w
     *      Optional, target maximum width for the image, default: 720
     * @param int $target_h
     *      Optional, target maximum height for the image, default: 480
     * @param bool $center
     *      Optional, if the image should, or not, be centered
     * @return string
     *      Inline CSS styles for the image
     */
    public static function imgsPosition(
        $file,
        $target_w = 720,
        $target_h = 480,
        $center = false
    ) {
        // Getting/defining final dimensions
        $size = self::imgsResizeNums( $file, $target_w, $target_h );

        $styles = array();
        $styles[] = "width:{$size[2]}px";
        $styles[] = "height:{$size[3]}px";
        if ( true === $center ) {
            $margin_l = round( $size[2] / 2 );
            $margin_t = round( $size[3] / 2 );
            $styles[] = "position:absolute";
            $styles[] = "top:50%";
            $styles[] = "left:50%";
            $styles[] = "margin: -{$margin_t}px auto auto -{$margin_l}px";
        } else {
            $styles[] = "position:relative";
        }
        return " style=\"".implode( ";", $styles )."\"";
    }

    /**
     * Calculates the ideal, proportional, values for resizing an image,
     * according to the desired width/height targets.
     *
     * Returns an array containing the old width, old height, new width, new
     * height and resize ratio, respectively.
     *
     * If the image file is smaller than the target values, no resizing will
     * be applied, so the original values will be applied to both old and
     * new values.
     *
     * @param string $file
     *      Full path to the image file
     * @param int $target_w
     *      Optional, target maximum width for the image, default: 720
     * @param int $target_h
     *      Optional, target maximum height for the image, default: 480
     * @return array
     *      Array with old and new values, and also the resize ratio
     */
    public static function imgsResizeNums(
        $file,
        $target_w = 720,
        $target_h = 480
    ) {
        $size = getimagesize( $file );
        $target_w = ( is_numeric( $target_w ) && $target_w > 0 ) ? $target_w : 640;
        $target_h = ( is_numeric( $target_h ) && $target_h > 0 ) ? $target_h : 480;
        $ratio = ( $size[0] > $size[1] )
            ? $target_w / $size[0]
            : $target_h / $size[1];

        // Returning values
        return array(
            // Original width
            $size[0],
            // Original height
            $size[1],
            // Resized width
            round( $size[0] * $ratio ),
            // Resized height
            round( $size[1] * $ratio ),
            // Resize ratio
            $ratio
        );
    }

    /**
     * Defines the optimal dimensions when resizing an image, according to the
     * original dimensions and the new, desired, dimensions.
     *
     * The $option parameter is optional, and defines what type of resizing will
     * be used, according to the following list:
     * - 0: automatic (default), calculated according to the new desired values;
     * - 1: exact, the image will have the exact desired new dimensions;
     * - 2: portrait, resizing according to the height of the file;
     * - 3: landscape: resize according to the width of the file;
     * - 4: resize and crop the image, according to the desired new dimensions;
     *
     * Returns an associative array with the optimal width and height values
     * (opt_w and opt_h), respectively.
     *
     * @param int $img_w
     *      Original image width
     * @param int $img_h
     *      Original image height
     * @param int $new_w
     *      Desired width for the new image
     * @param int $new_h
     *      Desired height for the new image
     * @param int $option
     *      Optional, one of the resizing modes, default:  0
     * @return array
     *      Associative array with the optimal new values
     */
    public static function imgsDimensions(
        $img_w,
        $img_h,
        $new_w,
        $new_h,
        $option = 0
    ) {
        /**
         * Defines the ideal dimensions for the image, when cropping, returns
         * an associative array with the optimal width and height values
         * (opt_w and opt_h), respectively.
         *
         * @param $old_w
         *      Old image width
         * @param $old_h
         *      Old image height
         * @param $new_w
         *      New image width
         * @param $new_h
         *      New image height
         * @return array
         *      Associative array with optimal values
         */
        $resize_crop = function( $old_w, $old_h, $new_w, $new_h )
        {
            // Define ratio
            $ratio_w = $old_w / $new_w;
            $ratio_h = $old_h / $new_h;
            $ratio_f = ( $ratio_h < $ratio_w ) ? $ratio_h : $ratio_w;
            return array(
                "opt_w" => ceil( $old_w / $ratio_f ),
                "opt_h" => ceil( $old_h / $ratio_f )
            );
        };

        /**
         * Defines the "auto/default" values, when resizing images, so it will
         * resize without distorting the image.
         *
         * It automatically defines the optimal width/height for the image.
         *
         * @param $old_w
         *      Old image width
         * @param $old_h
         *      Old image height
         * @param $new_w
         *      New image width
         * @param $new_h
         *      New image height
         * @return array
         *      Associative array with optimal values
         */
        $resize_auto = function( $old_w, $old_h, $new_w, $new_h )
        {
            $size = array();
            if ( $old_h < $old_w ) {
                // Landscape
                $size["opt_w"] = $new_w;
                $size["opt_h"] = ceil( $new_w * ( $old_h / $old_w ) );
            } elseif ( $old_h > $old_w ) {
                // Portrait
                $size["opt_w"] = ceil( $new_h * ( $old_w / $old_h ) );
                $size["opt_h"] = $new_h;
            } else {
                // Square images
                $size["opt_w"] = ( $new_h > $new_w ) ? $new_w : $new_h;
                $size["opt_h"] = ( $new_h > $new_w ) ? $new_w : $new_h;;
            }
            return $size;
        };

        // Return array
        $size = array();

        // Checking option
        $option = ( is_numeric( $option ) && $option >= 0 && $option < 5 )
            ? $option : 0;

        // Defines resizing mode
        switch ( $option ) {
            case 1:
                // Exact
                $size["opt_w"] = $new_w;
                $size["opt_h"] = $new_h;
                break;
            case 2:
                // Portrait
                $size["opt_w"] = ceil( $new_h * ( $img_w / $img_h ) );
                $size["opt_h"] = $new_h;
                break;
            case 3:
                // Landscape
                $size["opt_w"] = $new_w;
                $size["opt_h"] = ceil( $new_w * ( $img_h / $img_w ) );
                break;
            case 4:
                // Crop
                $size = $resize_crop( $img_w, $img_h, $new_w, $new_h );
                break;
            default:
                // Auto
                $size = $resize_auto( $img_w, $img_h, $new_w, $new_h );
                break;
        }

        // Return values
        return $size;
    }

    /**
     * Returns an image resource for the current file, according to one of the
     * image types allowed, being: jpg/jpeg, gif and png.
     *
     * Full path to the image file, as well as the extension, must be declared.
     *
     * @param string $file
     *      Full path to the image file
     * @param string $type
     *      Image extension type, can be jpg, jpeg, gif or png
     * @return resource|bool
     *      If both image file and type are valid, returns a resource, if not
     *      valid, returns a boolean value
     */
    public static function imgsOpen( $file, $type )
    {
        switch ( $type ) {
            case "jpg":
            case "jpeg":
                $imgs = @ imagecreatefromjpeg( $file );
                break;
            case "gif":
                $imgs = @ imagecreatefromgif( $file );
                break;
            case "png":
                $imgs = @ imagecreatefrompng( $file );
                break;
            default:
                $imgs = false;
                break;
        }

        return $imgs;
    }

    /**
     * Initializes a new image resource.
     *
     * If the $is_png type is declared, it also enables alpha blending, so
     * transparent images can be properly processed.
     *
     * @param int $img_w
     *      Desired width for the new image
     * @param int $img_h
     *      Desired height for the new image
     * @param bool $is_png
     *      Optional, if the image file is a PNG or not, default: false
     * @return resource
     *      Image resource
     */
    public static function imgsMake( $img_w, $img_h, $is_png = false )
    {
        // Initializing resource
        $imgs = imagecreatetruecolor( $img_w, $img_h );
        // If PNG, enables alpha blending
        if ( true === $is_png ) {
            imagealphablending( $imgs, false );
            imagesavealpha( $imgs, true );
        }
        return $imgs;
    }

    /**
     * Crops an image and returns the cropped resource.
     *
     * WARNING:
     * Doesn't work nicely with transparent GIF files.
     *
     * @param resource $resource
     *      Image resource being edited
     * @param string $type
     *      Type of image being edited
     * @param int $opt_w
     *      Ideal width for cropping
     * @param int $opt_h
     *      Ideal height for cropping
     * @param int $new_w
     *      New desired width
     * @param int $new_h
     *      New desired height
     * @return resource
     *      Cropped image resource
     */
    public static function imgsCrop(
        $resource,
        $type,
        $opt_w,
        $opt_h,
        $new_w,
        $new_h
    ) {
        // Defining crop center
        $img_x = ( $opt_w / 2 ) - ( $new_w / 2 );
        $img_y = ( $opt_h / 2 ) - ( $new_h / 2 );

        // Create new resource
        $imgs = self::imgsMake( $new_w, $new_h, $type );

        // If GIF or PNG, preserve transparency
        if  ( $type == "png" || $type == "gif" ) {
            if ( $type == "png" ) {
                imagealphablending( $imgs, false );
                imagesavealpha( $imgs, true );
                $tint = imagecolorallocatealpha(
                    $imgs,
                    255,
                    255,
                    255,
                    127
                );
                imagefilledrectangle(
                    $imgs,
                    0,
                    0,
                    $opt_w,
                    $opt_h,
                    $tint
                );
            } elseif ( $type == "gif" ) {
                $imgs = self::imgsGifs(
                    $imgs,
                    $resource,
                    $opt_w,
                    $opt_h
                );
            }
        }

        imagecopyresampled(
            $imgs,
            $resource,
            0,
            0,
            $img_x,
            $img_y,
            $new_w,
            $new_h,
            $new_w,
            $new_h
        );
        return $imgs;
    }

    /**
     * Makes sure that transparency in resized GIF will be preserved.
     *
     * Works only on non-animated GIFS.
     *
     * @param resource $resource
     *      Image resource being edited
     * @param resource $source
     *      Original image resource
     * @param int $width
     *      Optimal width for the edited image
     * @param int $height
     *      Optimal height for the edited image
     * @return resource
     *      Image resource properly edited
     */
    public static function imgsGifs( $resource, $source, $width, $height )
    {
        // Defining transparency index color
        $transparency = imagecolortransparent( $source );

        // Counting colors in palette
        $colors = imagecolorstotal( $source );

        // Verifying transparency, applying blending (?)
        if ( $transparency != ( -1 ) ) {
            if ( $transparency >= 0 && $transparency < $colors ) {
                $tint = imagecolorsforindex( $source, $transparency );
            }
        }

        // If has transparency
        if ( !empty( $tint ) ) {
            // Allocating color to image
            $new_color = imagecolorallocate(
                $resource,
                $tint["red"],
                $tint["green"],
                $tint["blue"]
            );
            // Applying transparency
            $new_color_set = imagecolortransparent( $resource, $new_color );
            // Filling image
            imagefilledrectangle(
                $resource,
                0,
                0,
                $width,
                $height,
                $new_color_set
            );
        }

        // Return data
        return $resource;
    }

    /**
     * Checks whether the image resource being edited is an animated GIF file
     * or not.
     *
     * @param string $data
     *      Path for the image file being edited
     * @return bool
     *      True if animated, false if not
     */
    public static function imgsIsAnimated( $data )
    {
        // Getting the binary contents
        $data = file_get_contents( $data );

        // Animated frames flag (if higher than 1 in the end, is animated)
        $flag = 0;

        // Checking with regex
        $flag += preg_match_all(
            "#\x00\x21\xF9\x04.{4}\x00(\x2C|\z21)#s",
            $data,
            $matches
        );

        // Retuning value
        return ( $flag > 1 ) ? true : false;
    }

    /**
     * Applies a watermark in the image resource.
     *
     * Watermark must be a transparent PNG file, smaller than the current
     * image, for better effect.
     *
     * @param resource $resource
     *      Image resource being edited
     * @param string $mark
     *      Full path to the PNG watermark file
     * @return resource
     *      Image resource with the watermark applied
     */
    public static function imgsMark( $resource, $mark )
    {
        $mark = imagecreatefrompng( $mark );
        $size = array( imagesx( $mark ), imagesy( $mark ) );
        imagealphablending( $resource, true );
        imagealphablending( $mark, true );
        imagesavealpha( $mark, true );
        $pos_x = imagesx( $resource ) - $size[0] - 10;
        $pos_y = imagesy( $resource ) - $size[1] - 10;
        imagecopy( $resource, $mark, $pos_x, $pos_y, 0, 0, $size[0], $size[1] );
        return $resource;
    }

    /**
     * Saves an image resource in a file.
     *
     * Full path to the file must be provided, and also the file type. File
     * type is confirmed with bitwise comparisons.
     *
     * Image quality is optional, and can be provided for PNG and JPG files, but
     * not for GIF.
     *
     * @param resource $resource
     *      Image resource to be saved
     * @param string $file
     *      Full path to the file to be saved, with file name and extension
     * @param string $type
     *      Image type and extension
     * @param int $quality
     *      Optional, image quality, in percent, ranging from 0 ~ 100,
     *      default: 100
     * @return bool
     *      True if successful, false if not
     */
    public static function imgsSave( $resource, $file, $type, $quality = 100 )
    {
        // Return flag (false by default)
        $flag = false;

        // Saving, according to image type
        switch ( $type ) {
            case "jpg":
            case "jpeg":
                if ( imagetypes() & IMG_JPG ) {
                    $flag = imagejpeg( $resource, $file, $quality );
                }
                break;
            case "gif":
                if ( imagetypes() & IMG_GIF ) $flag = imagegif( $resource, $file );
                break;
            case "png":
                $scale_quality = round( ( $quality / 100 ) * 9 );
                $scale_reverse = 9 - $scale_quality;

                // Checking bit-by-bit the file format
                if ( imagetypes() & IMG_PNG ) {
                    $flag = imagepng( $resource, $file, $scale_reverse );
                }
                break;
            default:
                // Defines error
                $flag = false;
                break;
        }

        // Destroy temporary resource
        imagedestroy( $resource );

        // Returning
        return $flag;
    }

    /**
     * Uploads an image file to the path provided, while also giving you the
     * possibility of resizing and watermarking the image.
     *
     * $option can be set to one the resizing types from:
     * - 0: automatic (default), calculated according to the new desired values;
     * - 1: exact, the image will have the exact desired new dimensions;
     * - 2: portrait, resizing according to the height of the file;
     * - 3: landscape: resize according to the width of the file;
     * - 4: resize and crop the image, according to the desired new dimensions;
     *
     * $watermark must be a transparent PNG file, for better results.
     *
     * IMPORTANT:
     * This method won't resize GIF files (yet) and won't send more than one
     * file. Use the mass send method for that.
     *
     * @param array $file
     *      An array, equivalent to the $_FILES post data, but for a single file
     * @param string $path
     *      Full path to the folder to save the image to
     * @param bool $rename
     *      Optional, if the file should be renamed to a random slug or not,
     *      default: false
     * @param bool $resize
     *      Optional, defines if resizing will be used or not, default: true
     * @param int $option
     *      Optional, resizing type option to be used, default: 0
     * @param int $new_w
     *      Optional, defines the new target width, if resizing the image,
     *      default: 720
     * @param int $new_h
     *      Optional, defines the new target height, if resizing the image,
     *      default: 720
     * @param bool $watermark
     *      Optional, when true it activates watermarking for the image being
     *      uploaded, $watermark_file must be provided, default: false
     * @param string $watermark_file
     *      Optional, full path to a transparent PNG file, to be used as a
     *      watermark for the image, default: null
     * @return bool|string
     *      Uploaded file name or boolean false on failure
     */
    public static function imgsSend(
        $file,
        $path,
        $rename = false,
        $resize = true,
        $option = 0,
        $new_w = 720,
        $new_h = 720,
        $watermark = false,
        $watermark_file = null
    ) {
        set_time_limit( 0 );
        ini_set( "memory_limit", "128M" );

        $path = self::pathRead( $path );
        $new_w = ( is_numeric( $new_w ) && $new_w > 0 ) ? $new_w : 720;
        $new_h = ( is_numeric( $new_h ) && $new_h > 0 ) ? $new_h : 720;

        $file_name = $file["name"];
        $temp_name = $file["tmp_name"];
        $type = str_replace( ".", "", strtolower( strrchr( $file_name, "." ) ) );
        if ( true === $rename ) {
            $save_name = self::fileRandomName( $path, $type );
        } else {
            $split = self::fileName( $file_name );
            $save_name = self::stringStrip( $split['name'] ).".".$split['type'];
        }

        $resize = ( true === $resize ) ? true : false;
        $watermark = ( true === $watermark ) ? true : false;
        $animated = ( self::imgsIsAnimated( $temp_name ) ) ? true : false;

        // If image will be resized and isn't animated
        if ( $resize && !$animated ) {
            $imgs = self::imgsOpen( $temp_name, $type );
            if ( false === $imgs ) return false;

            $img_w = imagesx( $imgs );
            $img_h = imagesy( $imgs );
            $new_w = ( is_numeric( $new_w ) ) ? $new_w : 720;
            $new_h = ( is_numeric( $new_h ) ) ? $new_h : 720;

            $option = ( is_numeric( $option ) && $option >= 0 && $option < 5 )
                ? $option : 0;
            $size = self::imgsDimensions(
                $img_w,
                $img_h,
                $new_w,
                $new_h,
                $option
            );
            $imgs_resize = imagecreatetruecolor( $size["opt_w"], $size["opt_h"] );

            // If GIF or PNG, preserve transparency
            if  ( $type == "png" || $type == "gif" ) {
                if ( $type == "png" ) {
                    imagealphablending( $imgs_resize, false );
                    imagesavealpha( $imgs_resize, true );

                    $tint = imagecolorallocatealpha(
                        $imgs_resize,
                        255,
                        255,
                        255,
                        127
                    );

                    imagefilledrectangle(
                        $imgs_resize,
                        0,
                        0,
                        $size["opt_w"],
                        $size["opt_h"],
                        $tint
                    );
                } elseif ( $type == "gif" ) {
                    $imgs_resize = self::imgsGifs(
                        $imgs_resize,
                        $imgs,
                        $size["opt_w"],
                        $size["opt_h"]
                    );
                }
            }

            imagecopyresampled(
                $imgs_resize,
                $imgs,
                0,
                0,
                0,
                0,
                $size["opt_w"],
                $size["opt_h"],
                $img_w,
                $img_h
            );

            if ( $option == 4 ) {
                $imgs_resize = self::imgsCrop(
                    $imgs_resize,
                    $type,
                    $size["opt_w"],
                    $size["opt_h"],
                    $img_w,
                    $img_h
                );
            }

            if ( true === $watermark && file_exists( $watermark_file ) ) {
                $imgs_resize = self::imgsMark( $imgs_resize, $watermark_file );
            }

            $save = self::imgsSave(
                $imgs_resize,
                $path.$save_name,
                $type,
                $quality = 100
            );
        } else {
            // If image won't be resized and isn't animated
            if (
                true === $watermark
                && file_exists( $watermark_file )
                && !$animated
            ) {
                $imgs_resize = self::imgsOpen( $temp_name, $type );

                // If GIF, or PNG, keep transparency
                if ( $type == "png" || $type == "gif" ) {
                    if ( $type == "png" ) {
                        // PNG transparency
                        imagesavealpha( $imgs_resize, true );
                    } elseif ( $type == "gif" ) {
                        // GIF transparency
                        $imgs_resize = self::imgsGifs(
                            $imgs_resize,
                            $imgs_resize,
                            imagesx( $imgs_resize ),
                            imagesy( $imgs_resize )
                        );
                    }
                }

                $imgs_resize = self::imgsMark( $imgs_resize, $watermark_file );

                $save = self::imgsSave(
                    $imgs_resize,
                    $path.$save_name,
                    $type,
                    $quality = 100
                );
            }  else {
                // Saving copy of file, if animated
                $save = move_uploaded_file( $temp_name, $path.$save_name );
            }
        }

        return ( $save !== false ) ? $save_name : false;
    }

    /**
     * Method for uploading large quantities of images.
     *
     * It's basically a loop that uses `imgsSend()` on each of the files in
     * the $files parameter (or the $_FILES global array).
     *
     * Returns an array with each image's upload status or boolean false, when
     * an upload is unsuccessful.
     *
     * @param array $list
     *      Array with files to be uploaded, equivalent to the $_FILES post
     *      global, but with multiple files only
     * @param string $path
     *      Full path to the folder where the images will be saved to
     * @param bool $rename
     *      Optional, if the file should be renamed to a random slug or not,
     *      default: false
     * @param bool $resize
     *      Optional, defines if resizing will be used or not, default: true
     * @param int $option
     *      Optional, resizing type option to be used, default: 0
     * @param int $new_w
     *      Optional, defines the new target width, if resizing the image,
     *      default: 720
     * @param int $new_h
     *      Optional, defines the new target height, if resizing the image,
     *      default: 720
     * @param bool $watermark
     *      Optional, when true it activates watermarking for the image being
     *      uploaded, $watermark_file must be provided, default: false
     * @param string $watermark_file
     *      Optional, full path to a transparent PNG file, to be used as a
     *      watermark for the image, default: null
     * @return array
     *      Array with the status for each file in the same order as the upload
     */
    public static function imgsMassSend(
        $list,
        $path,
        $rename = false,
        $resize = true,
        $option = 0,
        $new_w = 720,
        $new_h = 720,
        $watermark = false,
        $watermark_file = null
    ) {
        set_time_limit( 0 );
        ini_set( "memory_limit", "128M" );

        $path = self::pathRead( $path );

        $new_w = ( is_numeric( $new_w ) && $new_w > 0 ) ? $new_w : 720;
        $new_h = ( is_numeric( $new_h ) && $new_h > 0 ) ? $new_h : 720;

        $rename = ( true === $rename ) ? true : false;
        $resize = ( true === $resize ) ? true : false;

        $watermark = ( true === $watermark ) ? true : false;

        $nums = count( $list["name"] );

        $data = array();

        // Uploading images
        for ( $n = 0; $n < $nums; $n++ ) {
            if ( isset( $list["name"][$n] ) && trim( $list["name"][$n] != "" ) ) {
                // Generating a temporary array, similar to a single $_FILES
                $temp = array(
                    "name"      => $list["name"][$n],
                    "tmp_name"  => $list["tmp_name"][$n]
                );

                $name = self::imgsSend(
                    $temp,
                    $path,
                    $rename,
                    $resize,
                    $option,
                    $new_w,
                    $new_h,
                    $watermark,
                    $watermark_file
                );

                $data[] = $name;

                unset( $temp );
            } else {
                $data[] = false;
            }
        }

        return $data;
    }

    /**
     * Generates a thumbnail for the image in $file located in $path.
     *
     * Thumbnail is a cropped, square image file with the "_mini" suffix.
     *
     * @param string $path
     *      Image file location
     * @param string $file
     *      Image file name
     * @param int $target
     *      Optional, maximum/expected width/height for the image thumb,
     *      default: 120
     * @return string|bool
     *      Thumbnail file name if successful, boolean false if not
     */
    public static function imgsMini( $path, $file, $target = 120 )
    {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );

        $path = self::pathRead( $path );
        $file = trim( $file );
        $name = self::fileName( $file );

        $save_name = $name["name"]."_mini.".$name["type"];
        $target = ( is_numeric( $target ) && $target > 0 ) ? $target : 120;
        $imgs = self::imgsOpen( $path.$file, $name["type"] );

        if ( false !== $imgs ) {
            $img_w = imagesx( $imgs );
            $img_h = imagesy( $imgs );

            $size = self::imgsDimensions( $img_w, $img_h, $target, $target, 4 );

            $imgs_resize = self::imgsMake(
                $size["opt_w"],
                $size["opt_h"],
                $name["type"]
            );

            if  ( $name["type"] == "png" || $name["type"] == "gif" ) {
                if ( $name["type"] == "png" ) {
                    imagealphablending( $imgs_resize, false );
                    imagesavealpha( $imgs_resize, true );

                    $tint = imagecolorallocatealpha(
                        $imgs_resize,
                        255,
                        255,
                        255,
                        127
                    );

                    imagefilledrectangle(
                        $imgs_resize,
                        0,
                        0,
                        $size["opt_w"],
                        $size["opt_h"],
                        $tint
                    );
                } elseif ( $name["type"] == "gif" ) {
                    $imgs_resize = self::imgsGifs(
                        $imgs_resize,
                        $imgs,
                        $size["opt_w"],
                        $size["opt_h"]
                    );
                }
            }

            imagecopyresampled(
                $imgs_resize,
                $imgs,
                0,
                0,
                0,
                0,
                $size["opt_w"],
                $size["opt_h"],
                $img_w,
                $img_h
            );

            $imgs_resize = self::imgsCrop(
                $imgs_resize,
                $name["type"],
                $size["opt_w"],
                $size["opt_h"],
                $target,
                $target
            );

            $save = self::imgsSave(
                $imgs_resize,
                $path.$save_name,
                $name["type"],
                $quality = 100
            );

            return ( $save !== false ) ? $save_name : false;
        }

        return false;
    }

    /**
     * Applies image filters to the image $file located in $path.
     *
     * The $filter parameter can be one from the list below. Some filters can
     * have an extra parameter, which is declared as $option, description on
     * how to use it and if required follows:
     * - 0: Grayscale image filter, no $option needed;
     * - 1: Sepia image filter, no $option needed;
     * - 2: High contrast grayscale filter, no $option needed;
     * - 3: High contrast sepia filter, no $option needed;
     * - 4: Negative color filter, no $option needed;
     * - 5: Monochrome colorize filter, converts the image to grayscale, then
     * overlays the image with a color, $option is required and it must be a
     * valid, 3 or 6 characters, RGB HEX color value;
     * - 6: Colorize filter, overlays the original image with a color, $option
     * is required and it must be a valid, 3 or 6 characters, RGB HEX color value;
     * - 7: Brightness filter, increases/decreases the brightness value for the
     * image. $option required, and must be an integer ranging from -255 to 255;
     * - 8: Contrast filter, increases/decreases the contrast value, $option is
     * required and must be an integer, ranging from -100 to 100;
     * - 9: Gaussian blur filter, applies gaussian blur in the image, $option is
     * used to define intensity, which may range from 1 to 80;
     * - 10: Selective blur filter, applies a simple blur in the image file,
     * $option is used to define intensity, and must be an integer, ranging
     * from 1 to 80;
     * - 11: Image smoothing filter, makes the image "smoother", like a light
     * blur, $option handles smoothing intensity, and must be an integer ranging
     * from -8 to 8;
     * - 12: Pixelates the image, $option handles the pixel block size, starting
     * from 2;
     * - 13: Edge detect, highlights the edges in an image;
     * - 14: Emboss, applies a simple emboss filter in the image;
     * - 15: Mean removal, uses mean removal method to give the image a sketchy
     * and more sharpened look;
     *
     * Returns the file name if successful, or boolean false on failure.
     *
     * @param string $path
     *      Full path to the folder where the image is located
     * @param string $file
     *      Image file name
     * @param int $filter
     *      Optional, image filter type, according to the list in description,
     *      default: 0
     * @param int|string $option
     *      Optional, argument for the image filter, default: null
     * @param bool $overwrite
     *      Optional, defines overwriting for the original file, if false, the
     *      filtered image will be saved with a suffix, default: false
     * @return string|bool
     *      File name if successful, boolean false on failure
     */
    public static function imgsFilter(
        $path,
        $file,
        $filter = 0,
        $option = null,
        $overwrite = false
    ) {
        set_time_limit( 0 );
        ini_set( "memory_limit", "128M" );

        $path = self::pathRead( $path );
        $file = trim( $file );
        if ( !file_exists( $path.$file ) ) return false;
        $name = self::fileName( $file );
        $imgs = self::imgsOpen( $path.$file, $name["type"] );
        $save_name = $file;

        // If overwrite is false
        if ( false === $overwrite ) {
            $save_name = $name["name"]."-".self::slugMake().".".$name["type"];
            if ( file_exists( $path.$save_name ) ) {
                while ( file_exists( $path.$save_name ) ) {
                    $save_name = $name["name"]."-".self::slugMake().".".$name["type"];
                }
            }
        }

        // Checking filter option
        $filter = ( is_numeric( $filter ) && $filter >= 0 && $filter < 16 )
            ? $filter : 0;

        // Applying filter using switch conditionals
        switch ( $filter ) {
            // SEPIA FILTER
            case 1:
                imagefilter( $imgs, IMG_FILTER_GRAYSCALE );
                imagefilter( $imgs, IMG_FILTER_COLORIZE, 100, 50, 0 );
                break;
            // HIGH CONTRAST GRAYSCALE
            case 2:
                imagefilter( $imgs, IMG_FILTER_GRAYSCALE );
                imagefilter( $imgs, IMG_FILTER_BRIGHTNESS, 20 );
                imagefilter( $imgs, IMG_FILTER_CONTRAST, -80 );
                break;
            // HIGH CONTRAST SEPIA
            case 3:
                imagefilter( $imgs, IMG_FILTER_GRAYSCALE );
                imagefilter( $imgs, IMG_FILTER_BRIGHTNESS, 20 );
                imagefilter( $imgs, IMG_FILTER_CONTRAST, -80 );
                imagefilter( $imgs, IMG_FILTER_COLORIZE, 100, 50, 0 );
                break;
            // NEGATIVE FILTER
            case 4:
                imagefilter( $imgs, IMG_FILTER_NEGATE );
                break;
            // MONOCHROME COLORIZE FILTER
            case 5:
                imagefilter( $imgs, IMG_FILTER_GRAYSCALE );
                // Validating color
                if ( false === self::colorsValidate( $option ) ) {
                    imagedestroy( $imgs );
                    return false;
                }
                $t = self::colorsHexToDecimal( $option );
                imagefilter(
                    $imgs,
                    IMG_FILTER_COLORIZE,
                    $t["r"],
                    $t["g"],
                    $t["b"]
                );
                break;
            // COLORIZE FILTER
            case 6:
                // Validating color
                if ( false === self::colorsValidate( $option ) ) {
                    imagedestroy( $imgs );
                    return false;
                }
                $t = self::colorsHexToDecimal( $option );
                imagefilter(
                    $imgs,
                    IMG_FILTER_COLORIZE,
                    $t["r"],
                    $t["g"],
                    $t["b"]
                );
                break;
            // BRIGHTNESS FILTER
            case 7:
                // Checking brightness values
                $t = ( is_numeric( $option ) && $option >= -255 && $option <= 255 )
                    ? $option : 0;
                imagefilter( $imgs, IMG_FILTER_BRIGHTNESS, $t );
                break;
            // CONTRAST FILTER
            case 8:
                // Checking contrast value
                $t = ( is_numeric( $option ) && $option >= -100 && $option <= 100 )
                    ? $option : 0;
                // Inverting the sign of the contrast, to avoid of confusion.
                // Default: higher the number, less contrast
                // Converted: higher the number, more contrast
                $t *= -1;
                imagefilter( $imgs, IMG_FILTER_CONTRAST, $t );
                break;
            // GAUSSIAN BLUR FILTER
            case 9:
                // Caution: higher blur level runs slower and uses more memory
                $t = ( is_numeric( $option ) && $option >= 1 && $option <= 50 )
                    ? $option : 1;
                for ( $i = 0; $i < $t; $i++ ) {
                    imagefilter( $imgs, IMG_FILTER_GAUSSIAN_BLUR );
                }
                break;
            // SELECTIVE BLUR FILTER
            case 10:
                // Caution: higher blur level runs slower and uses more memory
                $t = ( is_numeric( $option ) && $option >= 1 && $option <= 50 )
                    ? $option : 1;
                // Applying gaussian blue filter
                for ( $i = 0; $i < $t; $i++ ) {
                    imagefilter( $imgs, IMG_FILTER_SELECTIVE_BLUR );
                }
                break;
            // IMAGE SMOOTHING FILTER
            case 11:
                // Checking smoothing level
                $t = ( is_numeric( $option ) && $option >= -8 && $option <= 8 )
                    ? $option : 1;
                imagefilter( $imgs, IMG_FILTER_SMOOTH, $t );
                break;
            // PIXELATION FILTER
            case 12:
                // Checking pixel block size
                $t = ( is_numeric( $option ) && $option > 1 )
                    ? $option : 2;
                imagefilter( $imgs, IMG_FILTER_PIXELATE, $t, true );
                break;
            // EDGE DETECT FILTER
            case 13:
                imagefilter( $imgs, IMG_FILTER_EDGEDETECT );
                break;
            // EMBOSS
            case 14:
                imagefilter( $imgs, IMG_FILTER_EMBOSS );
                break;
            // MEAN REMOVAL (SKETCHY EFFECT) FILTER
            case 15:
                imagefilter( $imgs, IMG_FILTER_MEAN_REMOVAL );
                break;
            // DEFAULT: GRAYSCALE FILTER
            default:
                // Applying filter
                imagefilter( $imgs, IMG_FILTER_GRAYSCALE );
                break;
        }

        $save = self::imgsSave( $imgs, $path.$save_name, $name["type"], 100 );
        return ( false === $save ) ? false : $save_name;
    }

    // 17: FILE UPLOADS
    // ------------------------------------------------------------------

    /**
     * Uploads a single file from $_FILES in $path.
     *
     * @param array $file
     *      File array, basically $_FILES with a single file or an equivalent
     *      array
     * @param string $path
     *      Full path to the upload folder
     * @return string|bool
     *      Uploaded file name if successful, false on failure
     */
    public static function uploadFile( $file, $path )
    {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );

        $path = self::pathRead( $path );
        $data = self::fileName( $file["name"] );
        $name = strtolower( self::stringClean( $data["name"] ) );
        $name.= "-".time().".".$data["type"];

        if ( move_uploaded_file( $file["tmp_name"], $path.$name ) ) return $name;
        return false;
    }

    /**
     * Single file upload, with more options.
     *
     * @param array $file
     *      File array, basically $_FILES with a single file or an equivalent
     *      array
     * @param string $path
     *      Full path to the upload folder
     * @param int $size
     *      Optional, max file size in megabytes, default: 4
     * @param bool $overwrite
     *      Optional, if the uploaded file should overwrite an existing file
     *      or not, default: false
     * @return string|bool
     *      Uploaded file name if successful, false on failure
     */
    public static function uploadFileFull(
        $file,
        $path,
        $size = 4,
        $overwrite = false
    ) {
        set_time_limit( 0 );
        ini_set( "memory_limit", "64M" );

        $mb = 1024 * 1024;
        $size = ( is_numeric( $size ) && $size > 0 ) ? $size * $mb : 4 * $mb;

        // Comparing size and, if within the limits, upload it
        if ( $file["size"] <= $size ) {
            $path = self::pathRead( $path );
            $data = self::fileName( $file["name"] );
            $name = strtolower( self::stringClean( $data["name"] ) )
                    .".".$data["type"];

            if ( false === $overwrite && file_exists( $path.$name ) ) {
                return false;
            }
            return ( move_uploaded_file( $file["tmp_name"], $path.$name ) )
                ? $name : false;
        }

        return false;
    }

    // 18: PLACEHOLDER IMAGES
    // ------------------------------------------------------------------

    /**
     * Returns a base64 encoded PNG image, which may, or not, be wrapped in
     * an img tag.
     *
     * The generated placeholder image is a simple, light-gray, image with
     * borders and two lines cross-cutting it in an X.
     *
     * @param int $img_w
     *      Optional, placeholder width, default: 100
     * @param int $img_h
     *      Optional, placeholder height, default: 100
     * @param bool $wrap
     *      Optional, defines wrapping for the base64 string in an img tag,
     *      default: false
     * @return string
     *      Base64 encoded image wrapped, or not, in an 'img' tag
     */
    public static function placeholderBase(
        $img_w = 100,
        $img_h = 100,
        $wrap = true
    ) {
        // Defining dimensions
        $img_w = ( is_numeric( $img_w ) && $img_w > 0 ) ? $img_w : 100;
        $img_h = ( is_numeric( $img_h ) && $img_h > 0 ) ? $img_h : 100;

        $imgs = imagecreatetruecolor( $img_w, $img_h );
        imageantialias( $imgs, true );

        $bg = imagecolorallocate( $imgs, 216, 216, 216 );
        $ln = imagecolorallocate( $imgs, 130, 130, 130 );

        imagefill( $imgs, 0, 0, $bg );
        imageline( $imgs, 0, 0, $img_w - 1, $img_h - 1, $ln );
        imageline( $imgs, $img_w - 1, 0, 0, $img_h - 1, $ln );
        imageline( $imgs, 0, 0, $img_w - 1, 0, $ln );
        imageline( $imgs, 0, 0, 0, $img_h - 1, $ln );
        imageline( $imgs, 0, $img_h - 1, $img_w - 1, $img_h - 1, $ln );
        imageline( $imgs, $img_w - 1, 0, $img_w - 1, $img_h - 1, $ln );

        // Using output buffer to "get" the image
        ob_start();
        imagepng( $imgs );
        $data = ob_get_contents();
        ob_end_clean();

        $imgs = "data:image/png;base64,".base64_encode( $data );
        return ( $wrap ) ? "<img src=\"{$imgs}\" alt=\"\">" : $imgs;
    }

    /**
     * Returns a base64 encoded PNG image, which may, or not, be wrapped in
     * an img tag.
     *
     * The generated placeholder image is a simple image with coloured or
     * grayscale noise.
     *
     * @param int $img_w
     *      Optional, placeholder width, default: 100
     * @param int $img_h
     *      Optional, placeholder height, default: 100
     * @param bool $grayscale
     *      Optional, toggles grayscale for noise, default: false
     * @param bool $wrap
     *      Optional, defines wrapping for the base64 string in an img tag,
     *      default: false
     * @return string
     *      Base64 encoded image wrapped, or not, in an 'img' tag
     */
    public static function placeholderNoise(
        $img_w = 100,
        $img_h = 100,
        $grayscale = false,
        $wrap = true
    ) {
        $img_w = ( is_numeric( $img_w ) && $img_w > 0 ) ? $img_w : 100;
        $img_h = ( is_numeric( $img_h ) && $img_h > 0 ) ? $img_h : 100;

        $imgs = imagecreatetruecolor( $img_w, $img_h );

        $bg = imagecolorallocate( $imgs, 216, 216, 216 );
        imagefill( $imgs, 0, 0, $bg );

        $colors = array();
        for ( $i = 0; $i < 16; $i++ ) {
            if ( $grayscale === true ) {
                $t_r = rand( 196, 255 );
                $t_g = rand( 196, 255 );
                $t_b = rand( 196, 255 );
            } else {
                $t_r = rand( 128, 255 );
                $t_g = rand( 128, 255 );
                $t_b = rand( 128, 255 );
            }

            $colors[] = imagecolorallocate( $imgs, $t_r, $t_g, $t_b );
        }

        for ( $y = 0; $y < $img_h; $y++ ) {
            for ( $x = 0; $x < $img_w; $x++ ) {
                $t_c = rand( 0, count( $colors ) - 1 );
                imagesetpixel( $imgs, $x, $y, $colors[$t_c] );
            }
        }

        // Using output buffer to "get" the image
        ob_start();
        imagepng( $imgs );
        $data = ob_get_contents();
        ob_end_clean();

        $imgs = "data:image/png;base64,".base64_encode( $data );
        return ( $wrap ) ? "<img src=\"{$imgs}\" alt=\"\">" : $imgs;
    }

    /**
     * Returns a base64 encoded PNG image, which may, or not, be wrapped in
     * an img tag.
     *
     * The generated placeholder image has more options for file format, text
     * and font, background and foreground color.
     *
     * @param int $img_w
     *      Optional, placeholder width, default: 100
     * @param int $img_h
     *      Optional, placeholder height, default: 100
     * @param string $bg_color
     *      Optional, background color, must be a RGB HEX color value,
     *      default: ""
     * @param string $tx_color
     *      Optional, foreground color, must be a RGB HEX color value,
     *      default: ""
     * @param string $text
     *      Optional, text to display in the image, defaults to image dimensions,
     *      default: ""
     * @param string $font
     *      Optional, full path to the TrueType font to be used to render
     *      the text, default: ""
     * @param string $type
     *      Optional, image type, default: png
     * @param bool $wrap
     *      Optional, defines wrapping for the base64 string in an img tag,
     *      default: false
     * @return string
     *      Base64 encoded image wrapped, or not, in an 'img' tag
     */
    public static function placeholderText(
        $img_w = 100,
        $img_h = 100,
        $bg_color = "",
        $tx_color = "",
        $text = "",
        $font = "",
        $type = "",
        $wrap = true
    ) {
        $img_w = ( is_numeric( $img_w ) && $img_w > 0 ) ? $img_w : 100;
        $img_h = ( is_numeric( $img_h ) && $img_h > 0 ) ? $img_h : 100;

        if ( trim( $tx_color ) != "" && self::colorsValidate( $tx_color ) ) {
            $tx_color = self::colorsHexToDecimal( $tx_color );
        } else {
            if ( trim( $bg_color ) != "" && self::colorsValidate( $bg_color ) ) {
                $tx_color = self::colorsHexInverter( $bg_color );
                $tx_color = self::colorsHexToDecimal( $tx_color );
            } else {
                $tx_color = self::colorsHexToDecimal( "888" );
            }
        }

        $bg_color = ( self::colorsValidate( $bg_color ) ) ? $bg_color : "ddd";
        $bg_color = self::colorsHexToDecimal( $bg_color );

        if ( trim( $type ) != "" ) {
            switch ( $type ) {
                case "jpg":
                case "jpeg":
                    $type = "jpg";
                    break;
                case "gif":
                    $type = "gif";
                    break;
                default:
                    $type = "png";
                    break;
            }
        } else {
            $type = "png";
        }

        $imgs = imagecreatetruecolor( $img_w, $img_h );
        imageantialias( $imgs, true );
        $bg_color = imagecolorallocate(
            $imgs,
            $bg_color["r"],
            $bg_color["g"],
            $bg_color["b"]
        );
        $tx_color = imagecolorallocate(
            $imgs,
            $tx_color["r"],
            $tx_color["g"],
            $tx_color["b"]
        );

        imagefill( $imgs, 0, 0, $bg_color );

        if ( trim( $text ) != "" ) {
            $text = trim( $text );
        } else {
            $text = $img_w." X ".$img_h;
        }

        if ( trim( $font ) != "" && file_exists( $font ) ) {
            $font_size = 100;
            $text_length = $img_w / 2;

            $data = imagettfbbox( $font_size, 0, $font, $text );
            $txt_w = $data[0] + $data[2];
            $txt_h = $data[1] + $data[7];

            if ( $txt_w > $text_length ) {
                while ( $txt_w > $text_length ) {
                    $font_size -= 1;
                    $data = imagettfbbox( $font_size, 0, $font, $text );
                    $txt_w = $data[0] + $data[2];
                    $txt_h = $data[1] + $data[7];
                }
            }

            $txt_w = ( ( $img_w - $txt_w ) / 2 );
            $txt_h = ( ( $img_h - $txt_h ) / 2 );

            imagettftext(
                $imgs,
                $font_size,
                0,
                $txt_w,
                $txt_h,
                $tx_color,
                $font,
                $text
            );
        } else {
            $font_size = 3;

            $char_w = imagefontwidth( $font_size );
            $char_h = imagefontheight( $font_size );

            $text_w = $char_w * strlen( $text );
            $position_x = ceil( ( $img_w - $text_w ) / 2 );
            $position_y = ceil( ( $img_h - $char_h ) / 2 );

            imagestring(
                $imgs,
                $font_size,
                $position_x,
                $position_y,
                $text,
                $tx_color
            );
        }

        // Using output buffer to "get" the image
        ob_start();
        switch ( $type ) {
            case "jpg":
                imagejpeg( $imgs );
                break;
            case "gif":
                imagegif( $imgs );
                break;
            default:
                imagepng( $imgs );
                break;
        }
        $data = ob_get_contents();
        ob_end_clean();

        $imgs = "data:image/png;base64,".base64_encode( $data );
        return ( $wrap ) ? "<img src=\"{$imgs}\" alt=\"\">" : $imgs;
    }

    // 19: COLOR CONVERSION
    // ------------------------------------------------------------------

    /**
     * Validates a RGB HEX color string.
     *
     * @param string $color
     *      A valid RGB hex color, 3 or 6 characters, with or without hash sign
     * @return bool
     *      True if valid, false if invalid
     */
    public static function colorsValidate( $color ) {
        $color = strtolower( trim( $color ) );
        $regex = "#^\#*([a-f0-9]{3}|[a-f0-9]{6});*$#";
        $match = preg_match( $regex, $color );

        return ( $match > 0 ) ? true : false;
    }

    /**
     * Generates random RGB HEX color, without the hash sign.
     *
     * @return string
     *      Ready to use RGB HEX color
     */
    public static function colorsRandom() {
        $varR = sprintf( "%02s", rand( 0, 255 ) );
        $varG = sprintf( "%02s", rand( 0, 255 ) );
        $varB = sprintf( "%02s", rand( 0, 255 ) );

        return $varR.$varG.$varB;
    }

    /**
     * Converts a RGB HEX color, 3 or 6 characters, into its decimal counterpart.
     *
     * Returns an associative array with 'r', 'g' and 'b' keys, containing values
     * ranging from 0 ~ 255.
     *
     * @param string $color
     *      RGB HEX value string
     * @return array|bool
     *      Associative array with RGB values if valid, boolean false if invalid
     */
    public static function colorsHexToDecimal( $color ) {
        $color = preg_replace( "#([\W]+)#", "", trim( $color ) );
        if ( preg_match( "#^(?=(?:.{3}|.{6})$)[a-fA-F0-9]*$#", $color ) < 1 ) {
            return false;
        }
        if ( strlen( $color ) == 3 ) {
            $varR = hexdec( substr( $color, 0, 1 ).substr( $color, 0, 1 ) );
            $varG = hexdec( substr( $color, 1, 1 ).substr( $color, 1, 1 ) );
            $varB = hexdec( substr( $color, 2, 1 ).substr( $color, 2, 1 ) );
        } else {
            $varR = hexdec( substr( $color, 0, 2 ) );
            $varG = hexdec( substr( $color, 2, 2 ) );
            $varB = hexdec( substr( $color, 4, 2 ) );
        }

        return array(
            "r" => $varR,
            "g" => $varG,
            "b" => $varB
        );
    }

    /**
     * Converts a RGB HEX color, 3 or 6 characters, into percentage values.
     *
     * Returns an associative array with 'r', 'g' and 'b' keys, containing float
     * values, ranging from 0 ~ 1.
     *
     * If the $int value is set to `true`, the values in the returned array will
     * be full percentage values, ranging from 0 ~ 100.
     *
     * @param string $color
     *      RGB HEX color value string
     * @param bool $int
     *      Optional, defines if the method should be return full percentage
     *      values or float values, default: false
     * @return array
     *      Associative array, with the 'r', 'g' and 'b' color values
     */
    public static function colorsHexToPercent( $color, $int = false ) {
        $int        = ( true === $int ) ? true : false;
        $color      = self::colorsHexToDecimal( $color );
        $color["r"] = number_format( ( $color["r"] / 255 ), 6 );
        $color["g"] = number_format( ( $color["g"] / 255 ), 6 );
        $color["b"] = number_format( ( $color["b"] / 255 ), 6 );
        if ( true === $int ) {
            $color["r"] = round( $color["r"] * 100 );
            $color["g"] = round( $color["g"] * 100 );
            $color["b"] = round( $color["b"] * 100 );
        }

        return $color;
    }

    /**
     * Converts a RGB HEX color, 3 or 6 characters, into CMYK values.
     *
     * Returns an associative array with 'c', 'm', 'y' and 'k' keys, containing
     * float values, ranging from 0 ~ 1.
     *
     * If the $int value is set to `true`, the values in the returned array will
     * be full percentage values, ranging from 0 ~ 100.
     *
     * @param string $color
     *      RGB HEX color value string
     * @param bool $int
     *      Optional, defines if the method should be return full percentage
     *      values or float values, default: false
     * @return array
     *      Associative array, with the 'c', 'm', 'y' and 'k' color values
     */
    public static function colorsHexToProcess( $color, $int = false ) {
        $int = ( true === $int ) ? true : false;
        $color = self::colorsHexToPercent( $color, false );

        // Calculating base values (K goes first because defines the rest)
        $pK = min( 1 - $color["r"], 1 - $color["g"], 1 - $color["b"] );
        $pC = ( 1 - $color["r"] - $pK ) / ( 1 - $pK );
        $pM = ( 1 - $color["g"] - $pK ) / ( 1 - $pK );
        $pY = ( 1 - $color["b"] - $pK ) / ( 1 - $pK );

        if ( $pC === false ) {
            $pC = 0;
        }
        if ( $pM === false ) {
            $pM = 0;
        }
        if ( $pY === false ) {
            $pY = 0;
        }

        return array(
            "c" => ( $int ) ? round( $pC * 100 ) : $pC,
            "m" => ( $int ) ? round( $pM * 100 ) : $pM,
            "y" => ( $int ) ? round( $pY * 100 ) : $pY,
            "k" => ( $int ) ? round( $pK * 100 ) : $pK
        );
    }

    /**
     * Inverts the value of a RGB HEX color value.
     *
     * @param string $color
     *      RGB HEX color value string
     * @return string
     *      Inverted RGB HEX color value
     */
    public static function colorsHexInverter( $color )
    {
        $color = self::colorsHexToDecimal( $color );
        $cR = 255 - $color["r"];
        $cG = 255 - $color["g"];
        $cB = 255 - $color["b"];
        return self::colorsDecimalToHex( $cR, $cG, $cB );
    }

    /**
     * Converts decimal RGB color values ( 0 ~ 255 ) into a RGB HEX color value.
     *
     * @param int $r
     *      Red value, integer, from 0 to 255
     * @param int $g
     *      Green value, integer, from 0 to 255
     * @param int $b
     *      Blue value, integer, from 0 to 255
     * @return string
     *      String with the RGB HEX value
     */
    public static function colorsDecimalToHex( $r, $g, $b )
    {
        return sprintf( "%02s", dechex( trim( $r ) ) )
               .sprintf( "%02s", dechex( trim( $g ) ) )
               .sprintf( "%02s", dechex( trim( $b ) ) );
    }

    /**
     * Converts RGB percentual values into a RGB HEX value.
     *
     * RGB values should be integers, ranging from 0 ~ 100, unless the $float
     * flag is set to True, which makes the method only accept values ranging
     * from 0 ~ 1.
     *
     * @param int $r
     *      Red color value, integer (0 ~ 100) or float (0 ~ 1)
     * @param int $g
     *      Green color value, integer (0 ~ 100) or float (0 ~ 1)
     * @param int $b
     *      Blue color value, integer (0 ~ 100) or float (0 ~ 1)
     * @param bool $float
     *      Optional, specifies if the method should accept integers or floats
     *      as RGB values
     * @return string
     *      String with the RGB HEX value
     */
    public static function colorsPercentToHex( $r, $g, $b, $float = false )
    {
        $float = ( $float === true ) ? true : false;

        $r = ( $float ) ?  $r : $r / 100;
        $g = ( $float ) ?  $g : $g / 100;
        $b = ( $float ) ?  $b : $b / 100;

        return self::colorsDecimalToHex(
            round( $r * 255 ),
            round( $g * 255 ),
            round( $b * 255 )
        );
    }

    /**
     * Converts CMYK color values into a RGB HEX value, highly experimental.
     *
     * CMYK values should be integers, ranging from 0 ~ 100, unless the $float
     * flag is set to True, which makes the method only accept values ranging
     * from 0 ~ 1.
     *
     * @param int $c
     *      Cyan color value, integer (0 ~ 100) or float (0 ~ 1)
     * @param int $m
     *      Magenta color value, integer (0 ~ 100) or float (0 ~ 1)
     * @param int $y
     *      Yellow color value, integer (0 ~ 100) or float (0 ~ 1)
     * @param int $k
     *      Black color value, integer (0 ~ 100) or float (0 ~ 1)
     * @param bool $float
     *      Optional, specifies if the method should accept integers or floats
     *      as CMYK values
     * @return string
     *      String with the RGB HEX value
     */
    public static function colorsProcessToHex( $c, $m, $y, $k, $float = false )
    {
        $float = ( $float === true ) ? true : false;

        $c = ( $float ) ? $c : $c / 100;
        $m = ( $float ) ? $m : $m / 100;
        $y = ( $float ) ? $y : $y / 100;
        $k = ( $float ) ? $k : $k / 100;

        $r = 255 * ( 1 - $c ) * ( 1 - $k );
        $g = 255 * ( 1 - $m ) * ( 1 - $k );
        $b = 255 * ( 1 - $y ) * ( 1 - $k );

        return self::colorsDecimalToHex( $r, $g, $b );
    }

    /**
     * Converts decimal RGB values (0 ~ 255) into HSV color values.
     *
     * Returns an array containing the 'h', 's' and 'v' color values, following
     * these standards:
     * - 'h', integer, ranging from 0 ~ 359;
     * - 's', integer, ranging from 0 ~ 100;
     * - 'v', integer, ranging from 0 ~ 100;
     *
     * @param int $r
     *      Red value, integer, from 0 to 255
     * @param int $g
     *      Green value, integer, from 0 to 255
     * @param int $b
     *      Blue value, integer, from 0 to 255
     * @return array
     *      Associative array with the 'h', 's' and 'v' color values
     */
    public static function colorsRGBHSV( $r, $g, $b )
    {
        // Convert all RGB to percentual float (0 ~ 1)
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        // Defining min and max value from RGB values
        $min = min( $r, $g, $b );
        $max = max( $r, $g, $b );

        // Delta from max - min
        $dif = $max - $min;

        // Defining brightness value
        $v = $max * 100;

        // Checking saturation and value
        if ( $dif == 0 ) {
            // When delta is zero, hue and saturation are also zero
            $h = 0;
            $s = 0;
        } else {
            // Defining saturation
            $s = 100 * ( $dif / $max );

            // Calculating hue base
            switch ( $min ) {
                case $r:
                    $h = 3 - ( ( $g - $b ) / $dif );
                    break;
                case $g:
                    $h = 5 - ( ( $b - $r ) / $dif );
                    break;
                default:
                    $h = 1 - ( ( $r - $g ) / $dif );
                    break;
            }

            // Defining final hue value
            $h = 60 * $h;

            // Avoid numbers larger than 359
            if ( $h >= 359.5) $h = 0;
        }

        // Returning
        return array(
            "h" => ( int ) floor( $h ),
            "s" => ( int ) floor( $s ),
            "v" => ( int ) floor( $v )
        );
    }

    /**
     * Converts HSV color values into RGB decimal (0 ~ 255) values.
     *
     * HSV values accepted follow these standards.
     * - 'h', integer, ranging from 0 ~ 359;
     * - 's', integer, ranging from 0 ~ 100;
     * - 'v', integer, ranging from 0 ~ 100;
     *
     * @param int $h
     *      Hue value, integer, ranging from 0 ~ 359
     * @param int $s
     *      Saturation value, integer, ranging from 0 ~ 100
     * @param int $v
     *      Brightness value, integer, ranging from 0 ~ 100
     * @return array
     *      Associative array with the 'r', 'g' and 'b' color values
     */
    public static function colorsHSVRGB( $h, $s, $v )
    {
        // Checking all parameters
        if ( $h > 359 ) $h = 0;
        if ( $h < 0 ) $h = 359;

        // Convert to float
        $h = $h / 360;
        $s = $s / 100;
        $v = $v / 100;

        // When saturation is zero, the color is in grayscale mode
        if ( $s == 0 ) {
            return array(
                "r" => ( int ) floor( $v * 255 ),
                "g" => ( int ) floor( $v * 255 ),
                "b" => ( int ) floor( $v * 255 )
            );
        }

        // Building color variables
        $i = floor( $h * 6 );
        $f = ( $h * 6 ) - $i;
        $p = $v * ( 1 - $s );
        $q = $v * ( 1 - $f * $s );
        $t = $v * ( 1 - ( 1 - $f ) * $s );

        // Defining base RGB values
        switch ( $i % 6 ) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
            default:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
        }

        // Returning
        return array(
            "r" => ( int ) floor( $r * 255 ),
            "g" => ( int ) floor( $g * 255 ),
            "b" => ( int ) floor( $b * 255 )
        );
    }
    
    // 20:
    // ------------------------------------------------------------------
    
    /**
     * Fetches the Gravatar image associated to the e-mail address.
     *
     * @param string $mail
     *      E-mail address associated to the avatar
     * @param int $size
     *      Optional, defines the avatar width/height, in pixels, default: 40
     * @param bool $wrap
     *      Optional, toggles wrapping the url in an `img` tag, default: true
     * @return string
     *      The image URL or the img tag with the image
     */
    public static function gravatarPull( $mail, $size = 40, $wrap = true )
    {
        $mail = md5( strtolower( trim( $mail ) ) );
        $size = ( is_numeric( $size ) && $size >= 20 && $size <= 200 )
            ? $size : 40;
        $link = "http://www.gravatar.com/avatar/";
        if ( $wrap === true ) {
            return "<img src=\"{$link}{$mail}?s={$size}&d=identicon\" alt=\"\">";
        } else {
            return $link.$mail."?s=".$size;
        }
    }
    
    /**
     * Checks if a video embed code/URL belongs to a video hosted on Dailymotion
     * and returns the video ID.
     *
     * @param string $code
     *      Embed code or video URL
     * @return string|bool
     *      Video ID or boolean false, if invalid
     */
    public static function videosGetIDDailymotion( $code )
    {
        $code = trim( $code );
        if ( "" == $code ) return false;
        $list = null;
        // Testing for iframe
        if ( strpos( $code, "iframe" ) ) {
            $patt = "/dailymotion\.com\/embed\/video\/([a-z0-9]*)\/*/";
            if ( preg_match( $patt, $code, $list ) > 0 ) return $list[1];
        }
        // Testing for short URL
        if ( strpos( $code, "dai.ly" ) ) {
            $patt = "/dai\.ly\/([a-z0-9\_\-]+)\/*/";
            if ( preg_match( $patt, $code, $list ) ) return $list[1];
        }
        // Testing for full URL
        if ( strpos( $code, "dailymotion.com" ) ) {
            $patt = "/dailymotion\.com\/video\/([0-9a-z]+)(\_){1}/";
            if ( preg_match( $patt, $code, $list ) ) return $list[1];
        }
        return false;
    }
    
    /**
     * Checks if a video embed code/URL belongs to a video hosted on YouTube
     * and returns the video ID.
     *
     * @param string $code
     *      Embed code or video URL
     * @return string|bool
     *      Video ID or boolean false, if invalid
     */
    public static function videosGetIDYoutube( $code )
    {
        $code = trim( $code );
        if ( "" == $code ) return false;
        $list = null;
        // Regex patterns for Youtube
        $iframe = array(
            "/youtube\.com\/embed\/(.{11})/",
            "/v/\(.{11})/",
            "/apiplayer\?video_id\=(.{11})/"
        );
        // Testing for iframe
        if ( strpos( $code, "iframe" ) || strpos( $code, "object" ) ) {
            foreach ( $iframe as $patt ) {
                if ( preg_match( $patt, $code, $list ) > 0 ) return $list[1];
            }
        }
        // Testing for short URL
        if ( strpos( $code, "youtu.be" ) ) {
            preg_match( "/youtu\.be\/(.{11})/", $code, $list );
            return $list[1];
        }
        // Testing for query strings
        parse_str( parse_url( $code, PHP_URL_QUERY ), $list );
        if ( isset( $list["v"] ) && trim( $list["v"] ) != "" ) return $list["v"];
        return false;
    }
    
    /**
     * Checks if a video embed code/URL belongs to a video hosted on Vimeo
     * and returns the video ID.
     *
     * @param string $code
     *      Embed code or video URL
     * @return string|bool
     *      Video ID or boolean false, if invalid
     */
    public static function videosGetIDVimeo( $code )
    {
        $code = trim( $code );
        if ( "" == $code ) return false;
        $list = null;
        // Testing for iframe
        if ( strpos( $code, "iframe" ) ) {
            $patt = "/player\.vimeo\.com\/video\/([0-9]*)\/*/";
            if ( preg_match( $patt, $code, $list ) > 0 ) return $list[1];
        }
        // Testing for full URL
        if ( strpos( $code, "vimeo.com" ) ) {
            $patt = "/vimeo\.com\/(.*\/)?([0-9]*)/";
            if ( preg_match( $patt, $code, $list ) ) return $list[2];
        }
        return false;
    }
    
    /**
     * Generates an iframe object for a Dailymotion video by using the video id.
     *
     * @param string $code
     *      Video id
     * @param int $width
     *      Optional, iframe width, default: 320
     * @param int $height
     *      Optional, iframe height, default: 240
     * @return string
     *      String with the iframe object
     */
    public static function videosFrameDailymotion(
        $code,
        $width = 320,
        $height = 240
    ) {
        $code = trim( $code );
        $width = ( is_numeric( $width ) && $width > 0 ) ? $width : 320;
        $height = ( is_numeric( $height ) && $height > 0 ) ? $height : 240;
        // Building iframe
        $frame = "<iframe width=\"{$width}\" height=\"{$height}\"";
        $frame.= "src=\"//www.dailymotion.com/embed/video/{$code}\" ";
        $frame.= "frameborder=\"0\" allowfullscreen></iframe>";
        return $frame;
    }
    
    /**
     * Generates an iframe object for a YouTube video by using the video id.
     *
     * @param string $code
     *      Video id
     * @param int $width
     *      Optional, iframe width, default: 320
     * @param int $height
     *      Optional, iframe height, default: 240
     * @return string
     *      String with the iframe object
     */
    public static function videosFrameYoutube(
        $code,
        $width = 320,
        $height = 240
    ) {
        $code = trim( $code );
        $width = ( is_numeric( $width ) && $width > 0 ) ? $width : 320;
        $height = ( is_numeric( $height ) && $height > 0 ) ? $height : 240;
        // Building iframe
        $frame = "<iframe width=\"{$width}\" height=\"{$height}\"";
        $frame.= "src=\"https://www.youtube.com/embed/{$code}?rel=0\" ";
        $frame.= "frameborder=\"0\" allowfullscreen></iframe>";
        return $frame;
    }
    
    /**
     * Generates an iframe object for a Vimeo video by using the video id.
     *
     * @param string $code
     *      Video id
     * @param int $width
     *      Optional, iframe width, default: 320
     * @param int $height
     *      Optional, iframe height, default: 240
     * @return string
     *      String with the iframe object
     */
    public static function videosFrameVimeo(
        $code,
        $width = 320,
        $height = 240
    ) {
        $code = trim( $code );
        $width = ( is_numeric( $width ) && $width > 0 ) ? $width : 320;
        $height = ( is_numeric( $height ) && $height > 0 ) ? $height : 240;
        // Building iframe
        $frame = "<iframe width=\"{$width}\" height=\"{$height}\"";
        $frame.= "src=\"https://player.vimeo.com/video/{$code}\" ";
        $frame.= "frameborder=\"0\" allowfullscreen></iframe>";
        return $frame;
    }
    
    /**
     * Fetches a thumbnail of a video from Dailymotion by using the video id.
     *
     * Thumbnail size may be 'small' or 'large'.
     *
     * @param string $code
     *      Video id
     * @param string $size
     *      Optional, thumbnail size, can be 'small' or 'large', default: small
     * @return string
     *      Thumbnail URL
     */
    public static function videosImageDailymotion( $code, $size = "small" )
    {
        $code = trim( $code );
        $link = "https://api.dailymotion.com/video/".$code;
        $link.= "?fields=";
        $size = ( "small" == $size ) ? "thumbnail_small_url" : "thumbnail_large_url";
        $pull = json_decode( file_get_contents( $link.$size ) );
        return $pull->$size;
    }
    
    /**
     * Fetches a thumbnail of a video from YouTube by using the video id.
     *
     * Thumbnail size may be 'small' or 'large'.
     *
     * @param string $code
     *      Video id
     * @param string $size
     *      Optional, thumbnail size, can be 'small' or 'large', default: small
     * @return string
     *      Thumbnail URL
     */
    public static function videosImageYoutube( $code, $size = "small" )
    {
        $code = trim( $code );
        $link = "http://img.youtube.com/vi/";
        $size = ( "small" == $size ) ? "1.jpg" : "0.jpg";
        return $link.$code."/".$size;
    }
    
    /**
     * Fetches a thumbnail of a video from Vimeo by using the video id.
     *
     * Thumbnail size may be 'small' or 'large'.
     *
     * @param string $code
     *      Video id
     * @param string $size
     *      Optional, thumbnail size, can be 'small' or 'large', default: small
     * @return string
     *      Thumbnail URL
     */
    public static function videosImageVimeo( $code, $size = "small" )
    {
        $code = trim( $code );
        $link = "http://vimeo.com/api/v2/video/{$code}.php";
        $pull = unserialize( file_get_contents( $link ) );
        $size = ( "small" == $size )
            ? $pull[0]["thumbnail_small"]
            : $pull[1]["thumbnail_large"];
        return $size;
    }
    
    /**
     * Checks if a string belongs to a YouTube, Vimeo or Dailymotion video id.
     *
     * Returns a string with the lowercase name of the service.
     *
     * IMPORTANT:
     * Dailymotion check must always come in last, to avoid problems with Vimeo.
     *
     * @param string $id
     *      Video id
     * @return string|bool
     *      String with the service name (lowercase) or boolean false
     */
    public static function videosIDTest( $id )
    {
        $id = trim( $id );
        if ( "" == $id ) return false;
        // HTTP header validation regex
        $validate = "/([A-Z]{4}\/1\.(0|1)\s[0-9]{3}\s(OK))/";
        
        # YOUTUBE
        $flag = "https://www.youtube.com/oembed?format=json";
        $flag.= "\&url=http://www.youtube.com/watch?v=";
        $head = get_headers( $flag.$id );
        if ( preg_match( $validate, $head[0] ) > 0 && false !== $head ) {
            return "youtube";
        }
        
        # VIMEO
        $flag = "https://vimeo.com/api/oembed.xml";
        $flag.= "?url=https://vimeo.com/";
        $head = get_headers( $flag.$id );
        if ( preg_match( $validate, $head[0] ) > 0 && false !== $head ) {
            return "vimeo";
        }
        
        # DAILYMOTION
        $flag = "https://api.dailymotion.com/video/";
        $head = get_headers( $flag.$id );
        if ( preg_match( $validate, $head[0] ) > 0 && false !== $head ) {
            return "dailymotion";
        }
        
        return false;
    }
    
    /**
     * Tests a string with an embed code or URL, and checks if it belongs to a
     * YouTube, Vimeo or Dailymotion video.
     *
     * Returns a string with the lowercase name of the service.
     *
     * IMPORTANT:
     * Dailymotion check must always come in last, to avoid problems with Vimeo.
     *
     * @param string $code
     *      Video embed code or URL
     * @return string|bool
     *      String with the service name (lowercase) or boolean false
     */
    public static function videosCodeTest( $code )
    {
        $code = trim( $code );
        if ( "" == $code ) return false;
        
        // If $code is an iframe or object tag
        if ( strpos( $code, "iframe" ) || strpos( $code, "object" ) ) {
            
            # YOUTUBE
            $flag = array(
                "/youtube\.com\/embed\/(.{11})/",
                "/v\/(.{11})/",
                "/apiplayer\?video_id\=(.{11})/"
            );
            foreach ( $flag as $patt ) {
                if ( preg_match( $patt, $code, $list ) > 0 ) return "youtube";
            }
            
            # VIMEO
            $flag = "/player\.vimeo\.com\/video\/([0-9]*)\/*/";
            if ( preg_match( $flag, $code, $list ) > 0 ) return "vimeo";
            
            # DAILYMOTION
            $flag = "/dailymotion\.com\/embed\/video\/([a-z0-9]*)\/*/";
            if ( preg_match( $flag, $code, $list ) > 0 ) return "dailymotion";
            
        } else {
            # YOUTUBE
            if ( preg_match( '/youtu\.be\/(.{11})/', $code, $list ) > 0 ) {
                return "youtube";
            }
            
            # VIMEO
            $flag = "/vimeo\.com\/(.*\/)?([0-9]*)/";
            if ( preg_match( $flag, $code, $list ) > 0 ) return "vimeo";
            
            # DAILYMOTION
            $flag = "/dai\.ly\/([a-z0-9\_\-]+)\/*/";
            if ( preg_match( $flag, $code, $list ) ) return "dailymotion";
            $flag = "/dailymotion\.com\/video\/([0-9a-z]+)(\_){1}/";
            if ( preg_match( $flag, $code, $list ) ) return "dailymotion";
            
            # YOUTUBE FALLBACK (QUERY STRING ONLY)
            parse_str( parse_url( $code, PHP_URL_QUERY ), $list );
            if ( isset( $list["vimeo"] ) && strlen( $list["v"] ) == 11 ) {
                return "youtube";
            }
        }
        return false;
    }
    
    /**
     * Builds a query string for use in `googleMapsShow()` to build an iframe
     * with a map location.
     *
     * @param string $address
     *      Street name
     * @param int $number
     *      Optional, house/building number, default: null
     * @param string $district
     *      Optional, district/neighbourhood name, default: null
     * @param string $city
     *      Optional, city/town name, default: null
     * @param string $state
     *      Optional, state name, default: null
     * @param string $country
     *      Optional, country name, default: null
     * @return string
     *      String to be embedded in googleMapsShow method
     */
    public static function googleMapsData(
        $address,
        $number = null,
        $district = null,
        $city = null,
        $state = null,
        $country = null
    ) {
        $data = array();
        $data[] = trim( $address );
        if ( isset( $number ) && is_numeric( $number ) ) $data[0].= ", ".$number;
        if ( trim( $district ) != "" ) $data[] = trim( $district );
        if ( trim( $city ) != "" ) $data[] = trim( $city );
        if ( trim( $state ) != "" ) $data[] = trim( $state );
        if ( trim( $country ) != "" ) $data[] = trim( $country );
        return implode( " - ", $data );
    }
    
    /**
     * Builds an iframe with a map location pointing to the address given in
     * $address. You can either write by hand or use `googleMapsData()` method
     * to define the place.
     *
     * @param string $address
     *      Address of the location
     * @param int $width
     *      Optional, iframe width, default: 640
     * @param int $height
     *      Optional, iframe height, default: 640
     * @return string|bool
     *      String with the iframe object, or boolean false on failure
     */
    public static function googleMapsShow(
        $address,
        $width = 640,
        $height = 480
    ) {
        if ( trim( $address ) != "" ) {
            $width = ( is_numeric( $width ) && $width > 0 )
                ? $width : 640;
            $height = ( is_numeric( $height ) && $height > 0 )
                ? $height : 480;
            $frame = "<iframe width=\"{$width}\" height=\"{$height}\""
                     ." frameborder=\"0\" scrolling=\"no\" marginheight=\"0\""
                     ." marginwidth=\"0\" src=\"https://maps.google.com/maps"
                     ."?q=".trim( $address )."&output=embed\"></iframe>";
            return $frame;
        }
        return false;
    }
    
    // 21: DOCUMENT VERIFICATION
    // ------------------------------------------------------------------
    
    /**
     * Validates the CPF (Cadastro de Pessoa Física) document number (Natural
     * Person Register).
     *
     * @param string $docs
     *      CPF (Natural Person) number, for validation
     * @return bool
     *      True if valid, false if not
     */
    public static function docsTestPF( $docs )
    {
        $docs = self::stringNums( $docs );
        if ( "" == $docs ) return false;
        if ( strlen( $docs ) < 11 ) $docs = sprintf( "%011s", $docs );
        
        // Check repetitions
        for ( $n = 0; $n < 10; $n++ ) {
            $test = preg_match( "/[{$n}]{11}/", $docs );
            if ( $test > 0 ) return false;
        }
    
        // Testing first digit
        $sums = 0;
        for ( $n = 0; $n < 9; $n++ ) $sums += $docs[$n] * ( 10 - $n );
        $vals = 11 - ( $sums % 11 );
        if ( $vals === 10 || $vals == 11 ) $vals = 0;
        if ( $docs[9] != $vals ) return false;
        
        // Testing second digit
        $sums = 0;
        for ( $n = 0; $n < 10; $n++ ) $sums += $docs[$n] * ( 11 - $n );
        $vals = 11 - ( $sums % 11 );
        if ( $vals === 10 || $vals == 11 ) $vals = 0;
        if ( $docs[10] != $vals ) return false;
        
        // Is valid
        return true;
    }
    
    /**
     * Validates the CNPJ (Cadastro Nacional de Pessoa Jurídica) document number
     * (Legal Entity Registry).
     *
     * @param string $docs
     *      CNPJ (Legal Entity) number, for validation
     * @return bool
     *      True if valid, false if not
     */
    public static function docsTestPJ( $docs )
    {
        $docs = self::stringNums( $docs );
        if ( "" == $docs ) return false;
        if ( strlen( $docs ) < 14 ) $docs = sprintf( "%014s", $docs );
    
        // Check repetitions
        for ( $n = 0; $n < 10; $n++ ) {
            $test = preg_match( "/[{$n}]{14}/", $docs );
            if ( $test > 0 ) return false;
        }
    
        // Testing first digit
        $sums = 0;
        $vals = 5;
        for ( $n = 0; $n < 12; $n++ ) {
            $sums += ( $docs[$n] * $vals );
            $vals = ( $vals - 1 === 1 ) ? 9 : $vals - 1;
        }
        $vals = ( $sums % 11 < 2 ) ? 0 : 11 - ( $sums % 11 );
        if ( $docs[12] != $vals ) return false;
    
        // Testing second digit
        $sums = 0;
        $vals = 6;
        for ( $n = 0; $n < 13; $n++ ) {
            $sums += ( $docs[$n] * $vals );
            $vals = ( $vals - 1 === 1 ) ? 9 : $vals - 1;
        }
        $vals = ( $sums % 11 < 2 ) ? 0 : 11 - ( $sums % 11 );
        if ( $docs[13] != $vals ) return false;
    
        // Is valid
        return true;
    }
    
    /**
     * Formats the CPF (Natural Person Registry) document number, while also
     * validating it.
     *
     * @param string $docs
     *      Document number for validation and formatting
     * @return bool|string
     *      Validated and formatted number if valid, boolean false if invalid
     */
    public static function docsFormatPF( $docs )
    {
        $docs = self::stringNums( $docs );
        if ( "" == $docs ) return false;
        if ( strlen( $docs ) < 14 ) $docs = sprintf( "%011s", $docs );
    
        // Validating
        if ( !self::docsTestPF( $docs ) ) return false;
    
        // Format and return
        return preg_replace(
            "/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/",
            "$1.$2.$3/$4-$5",
            $docs
        );
    }
    
    /**
     * Formats the CNPJ (Legal Entity Registry) document number, while also
     * validating it.
     *
     * @param string $docs
     *      Document number for validation and formatting
     * @return bool|string
     *      Validated and formatted number if valid, boolean false if invalid
     */
    public static function docsFormatPJ( $docs )
    {
        $docs = self::stringNums( $docs );
        if ( "" == $docs ) return false;
        if ( strlen( $docs ) < 14 ) $docs = sprintf( "%014s", $docs );
        
        // Validating
        if ( !self::docsTestPJ( $docs ) ) return false;
        
        // Format and return
        return preg_replace(
            "/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})/",
            "$1.$2.$3-$4",
            $docs
        );
    }
    
    // 22: THIRD PARTY LIBRARY HELPERS
    // ------------------------------------------------------------------
    
    /**
     * PHPMailer class helper function, meant to simplify the process of
     * using it.
     *
     * The $from parameter should be an associative array, containing the fields
     * 'name' and 'mail', for sender name and e-mail address respectively. It's
     * used in the 'Reply-To' header, so it can be different from the $mail
     * address value, for cases where the authentication address is different
     * from the sender's.
     *
     * The $to parameter should be a regular array, containing one or more
     * associative arrays following the same pattern as $from (each should have
     * a 'name' and 'mail' fields).
     *
     * Both $cc and $bcc fields accept a regular array of e-mail addresses.
     *
     * The method "kills" itself, if no PHPMailer or SMTP class are found.
     *
     * @param string $name
     *      Sender name
     * @param string $mail
     *      Username (e-mail address) for authentication, it's the "main" sender
     *      of the e-mail
     * @param string $pass
     *      Password for the e-mail address used in authentication
     * @param string $host
     *      SMTP host address for authentication
     * @param array $from
     *      Values for the 'Reply-To' header, can either be the same e-mail
     *      address as in $mail, or a different, when the $mail address is only
     *      necessary for authentication/sending
     * @param array $to
     *      Recipient e-mail list
     * @param string $subject
     *      E-mail subject
     * @param string $body
     *      E-mail body
     * @param array $cc
     *      Optional, array with e-mail addresses for the CC field, default: null
     * @param array $bcc
     *      Optional, array with e-mail addresses for the BCC field, default: null
     * @param string $attach
     *      Optional, file attachment with its full path, default: null
     * @param int $port
     *      Optional, SMTP port, default: 587
     * @return bool
     *      True on success, false on failure
     */
    public static function phpMailerHelper(
        $name,
        $mail,
        $pass,
        $host,
        $from,
        $to,
        $subject,
        $body,
        $cc = null,
        $bcc = null,
        $attach = null,
        $port = null
    ) {
        if ( !class_exists( 'PHPMailer' ) ) die( 'PHPMailer class not found!' );
        if ( !class_exists( 'SMTP' ) ) die( 'SMTP class not found!' );
        
        // Creating new instance of PHPMailer
        $mailer = new PHPMailer();
        
        // Set mailer to use SMTP
        $mailer->isSMTP();
        
        // Checking $mail, $pass and $host
        $name = trim( $name );
        $mail = trim( $mail );
        $pass = trim( $pass );
        $host = trim( $host );
        
        // Defining host, authentication, user, password, encryption and port
        $mailer->Host = $host;
        $mailer->SMTPAuth = true;
        $mailer->Username = $mail;
        $mailer->Password = $pass;
        $mailer->SMTPSecure = 'ssl';
        $mailer->Port = ( is_numeric( $port ) && $port > 0 ) ? $port : 587;
        
        // Defines from, return-path and from name
        $mailer->From = $mail;
        $mailer->Sender = $mail;
        $mailer->FromName = $name;
        
        // Adding recipients
        foreach ( $to as $recipient ) {
            if ( isset( $recipient['name'] ) && $recipient['name'] != '' ) {
                $mailer->addAddress( $recipient['mail'], $recipient['name'] );
            } else {
                $mailer->addAddress( $recipient['mail'] );
            }
        }
        
        // Defines reply-to information
        $mailer->addReplyTo( $from['mail'], $from['name'] );
        
        // Adding carbon copies
        if ( is_array( $cc ) && count( $cc ) > 0 ) {
            foreach ( $cc as $item ) {
                $mailer->addCC( $item );
            }
        }
        
        // Adds blind carbon copies
        if ( is_array( $cc ) && count( $bcc ) > 0 ) {
            foreach ( $bcc as $item ) {
                $mailer->addBCC( $item );
            }
        }
        
        // Checking attachment
        if ( trim( $attach ) != '' && file_exists( $attach ) ) {
            $mailer->addAttachment( $attach );
        }
        
        $mailer->isHTML( true );
        $mailer->Subject    = $subject;
        $mailer->Body       = $body;
        return ( !$mailer->send() ) ? false : true;
    }
    
    // 23: MISC STUFF
    // ------------------------------------------------------------------
    
    /**
     * Calculates page rendering time, using the static $zero variable as counter.
     *
     * You need to use this method two times for it to work:
     * - The first time you use this method should be in the document's head,
     * right after including "_0", it will only return boolean true;
     * - The second time you use this method should be at the end of the
     * document (or where you want to show the value), where it will return
     * the current time in seconds, as a string;
     *
     * @return bool|string
     *      Boolean true when first used, and a string with the render time, in
     *      seconds, when used for the second time
     */
    public static function pageTime()
    {
        if ( self::$zero == 0 || self::$zero == null ) {
            self::$zero = microtime( true );
            return true;
        } else {
            $time = microtime( true ) - self::$zero;
            self::$zero = null;
            return (string) number_format( $time, 8 );
        }
    }
    
    /**
     * Generates options for a simple YES/NO select box in a form.
     *
     * If $text is defined as true, the method uses the Y/N character for the
     * values, instead of 1/0.
     *
     * IMPORTANT:
     * Option labels are in brazilian-portuguese.
     *
     * @param string $vars
     *      Optional, value to mark as selected, default: null
     * @param bool $text
     *      Optional, if values should be a string character (Y/N), instead of
     *      number (1/0), default: false
     * @return string
     *      Select box options string
     */
    public static function optsBool( $vars = null, $text = false )
    {
        $vars = trim( $vars );
        $opts = array(
            "Sim" => ( true === $text ) ? "Y" : "1",
            "Não" => ( true === $text ) ? "N" : "0"
        );
        $list = array();
        
        // Building options
        foreach ( $opts as $name => $vals ) {
            $select = ( $vals == $vars ) ? " selected=\"selected\"" : "";
            $list[] = "<option value=\"{$vals}\" {$select}>{$name}</option>";
        }
        
        // Cleaning extra spaces
        $list = preg_replace( "/\s{2}/", " ", implode( "", $list ) );
        $list = preg_replace( "/\s\>/", ">", $list );
        return $list;
    }
    
    /**
     * Generates options for select boxes containing the names of all Brazilian
     * states, with its abbreviation as value.
     *
     * @param string $vars
     *      Optional, state value to mark as 'selected', default: null
     * @return array
     *      Select box options string
     */
    public static function optsStates( $vars = null )
    {
        $vars = trim( $vars );
        $opts = array(
            "AC" => "Acre",
            "AL" => "Alagoas",
            "AP" => "Amap&aacute;",
            "AM" => "Amazonas",
            "BA" => "Bahia",
            "CE" => "Cear&aacute;",
            "DF" => "Distrito Federal",
            "ES" => "Espírito Santo",
            "GO" => "Goi&aacute;s",
            "MA" => "Maranh&atilde;o",
            "MT" => "Mato Grosso",
            "MS" => "Mato Grosso do Sul",
            "MG" => "Minas Gerais",
            "PA" => "Par&aacute;",
            "PB" => "Para&iacute;ba",
            "PR" => "Paran&aacute;",
            "PE" => "Pernambuco",
            "PI" => "Piau&iacute;",
            "RJ" => "Rio de Janeiro",
            "RN" => "Rio Grande do Norte",
            "RS" => "Rio Grande do Sul",
            "RO" => "Rond&ocirc;nia",
            "RR" => "Roraima",
            "SC" => "Santa Catarina",
            "SP" => "S&atilde;o Paulo",
            "SE" => "Sergipe",
            "TO" => "Tocantins"
        );
        $list = array();
        
        // Building options
        foreach ( $opts as $name => $vals ) {
            $select = ( $vals == $vars ) ? " selected=\"selected\"" : "";
            $list[] = "<option value=\"{$name}\" {$select}>{$vals}</option>";
        }
    
        // Cleaning extra spaces
        $list = preg_replace( "/\s{2}/", " ", implode( "", $list ) );
        $list = preg_replace( "/\s\>/", ">", $list );
        return $list;
    }
    
    /**
     * Reads and returns basic info from an ID3v1 tag, on an MP3 file.
     *
     * Only works with V1, and returns data as an associative array.
     *
     * If no info is found, or the tag is invalid, returns an empty array.
     *
     * @param string $file
     *      MP3 file name, with full address
     * @return array
     *      Associative array with file info
     */
    public static function mp3TagRead( $file )
    {
        $data = array(
            'title'     => '',
            'artist'    => '',
            'album'     => '',
            'year'      => '',
            'genre'     => ''
        );
        if ( !file_exists( $file ) ) return $data;
        
        // Reading and checking if ID3v1 tag is present
        $file = file_get_contents( $file );
        if ( substr( $file, -128, -125 ) == "TAG" ) {
            // Filling the data
            $data['title']  = substr( $file, -125, -95 );
            $data['artist'] = substr( $file,  -95, -65 );
            $data['album']  = substr( $file,  -65, -35 );
            $data['year']   = substr( $file,  -35, -31 );
            $data['genre']  = substr( $file,   -1,   1 );
        }
        return $data;
    }
    
    // 24: MARKUP PARSERS
    // ------------------------------------------------------------------
    
    /**
     * Experimental markup parser, for a simple markup inspired by markdown and
     * bbcode.
     *
     * Instructions are as follows:
     * - Line break: \\;
     * - Horizontal rule: ----- (5 times or more the minus sign);
     * - Page break: [pagebreak];
     * - Headers: = (1 ~ 6x) [text] = (1 ~ 6x);
     * - Unordered lists: * (1 ~ 3x, for nesting)[space][text];
     * - Ordered lists: # (1 ~ 3x, for nesting)[space][text];
     * - Bold: * 3 times before and after;
     * - Strong: * 2 times before and after;
     * - Italic: / 2 times before and after;
     * - Emphasized: / 2 times before and after
     * - Insert text: _ 3 times before and after;
     * - Underlined text: _ 2 times before and after;
     * - Deleted text/strikethrough: - 2 times before and after;
     * - Simple anchor: [[www.address.com]];
     * - Address with text: [[www.link.com.br|Text for the Anchor]];
     * - Image: {{image.png}};
     * - Image with alt text: {{image.png|Alt Text}};
     * - Citation: [cite]Text[/cite];
     * - Blockquote: [blockquote]Text[/blockquote];
     * - Iframe: <<address.com|[width]x[height]>>;
     *
     * Text without markup is considered paragraph, no need to break two lines.
     *
     * Returns the parsed string.
     *
     * @param string $text
     *      Text in the custom markup language
     * @return string
     *      Parsed text
     */
    public static function zeroMarkupParser( $text )
    {
        // Applying line breaks before and after the text, as security measure
        $data = "\r\n".$text."\r\n";
        // Common flags
        $base = "(.*?)";
        $line = "(\r\n?|\n)";
        // Removing tabulations
        $text = preg_replace( "#(\t)#", "", $data );
        // Regex flags (order is important for my markup, so don't change!)
        $flag = array(
            // Horizontal Rule
            "^(-{5,})"                    => "<hr>",
            // Line break
            "(\\\{2})"                    => "<br>",
            // Lists (initial flags)
            "^(\*{3}\s{1}){$base}{$line}"
                => "<ul><ul><ul><li>$2</li></ul></ul></ul>\n",
            "^(\*{2}\s{1}){$base}{$line}"
                => "<ul><ul><li>$2</li></ul></ul>\n",
            "^(\*{1}\s{1}){$base}{$line}"
                => "<ul><li>$2</li></ul>\n",
            "^(\#{3}\s{1}){$base}{$line}"
                => "<ol><ol><ol><li>$2</li></ol></ol></ol>\n",
            "^(\#{2}\s{1}){$base}{$line}"
                => "<ol><ol><li>$2</li></ol></ol>\n",
            "^(\#{1}\s{1}){$base}{$line}"
                => "<ol><li>$2</li></ol>\n",
            // List Cleaning
            "(<\/ul>){3}\n(<ul>){3}"    => "",
            "(<\/ul>){2}\n(<ul>){2}"    => "",
            "(<\/ul>\n<ul>)"            => "",
            "(<\/li><ul>)"              => "</li><li><ul>",
            "(<\/ul><li>)"              => "</ul></li><li>",
            "(<ul>){3}"                 => "<ul><li><ul><li><ul>",
            "(<ul>){2}"                 => "<ul><li><ul>",
            "(<\/ul>){3}"               => "</ul></li></ul></li></ul>",
            "(<\/ul>){2}"               => "</ul></li></ul>",
            "(<\/ol>){3}\n(<ol>){3}"    => "",
            "(<\/ol>){2}\n(<ol>){2}"    => "",
            "(<\/ol>\n<ol>)"            => "",
            "(<\/li><ol>)"              => "</li><li><ol>",
            "(<\/ol><li>)"              => "</ol></li><li>",
            "(<ol>){3}"                 => "<ol><li><ol><li><ol>",
            "(<ol>){2}"                 => "<ol><li><ol>",
            "(<\/ol>){3}"               => "</ol></li></ol></li></ol>",
            "(<\/ol>){2}"               => "</ol></li></ol>",
            // Headings
            "^(={6}){$base}(={6})"      => "<h6>$2</h6>",
            "^(={5}){$base}(={5})"      => "<h5>$2</h5>",
            "^(={4}){$base}(={4})"      => "<h4>$2</h4>",
            "^(={3}){$base}(={3})"      => "<h3>$2</h3>",
            "^(={2}){$base}(={2})"      => "<h2>$2</h2>",
            "^(={1}){$base}(={1})"      => "<h1>$2</h1>",
            // Typography and formatting
            "(\*{3}){$base}(\*{3})"     => "<b>$2</b>",
            "(\*{2}){$base}(\*{2})"     => "<strong>$2</strong>",
            "(\/{3}){$base}(\/{3})"     => "<i>$2</i>",
            "(\/{2}){$base}(\/{2})"     => "<em>$2</em>",
            "(_{3}){$base}(_{3})"       => "<ins>$2</ins>",
            "(_{2}){$base}(_{2})"       => "<u>$2</u>",
            "(-{2}){$base}(-{2})"       => "<del>$2</del>",
            "(\^{2}){$base}(\^{2})"     => "<sup>$2</sup>",
            "(\~{2}){$base}(\~{2})"     => "<sub>$2</sub>",
            // Iframe, anchors and images
            "<{2}{$base}\|([0-9]+)x([0-9]+)>{2}"
                => "<iframe src=\"http://$1\" frameborder=\"0\" "
                    ."width=\"$2\" height=\"$3\"></iframe>",
            "(\[{2}){$base}\|{$base}(\]{2})"
                => "<a href=\"http://$2\" target=\"_blank\">$3</a>",
            "(\[{2}){$base}(\]{2})"
                => "<a href=\"http://$2\" target=\"_blank\">$2</a>",
            "(\{{2}){$base}\|{$base}(\}{2})"
                => "<img src=\"http://$2\" alt=\"$3\">",
            "(\{{2}){$base}(\}{2})"
                => "<img src=\"http://$2\" alt=\"\">",
            // Citações and blockquotes
            "\[cite\]{$base}\[\/cite\]"   => "<cite>$1</cite>",
            "\[blockquote\]{$base}\[\/blockquote\]"
                => "<blockquote>$1</blockquote>",
            // Pagebreak
            "^\[pagebreak\]"            => "<!--PAGEBREAK-->"
        );
        // Applying regex
        foreach ( $flag as $patt => $code ) {
            $text = preg_replace( "#{$patt}#m", $code, $text );
        }
        // Exploding lines to build paragraphs
        $list = explode( "\r\n", trim( $text ) );
        // Return array
        $return = array();
        // Regex flag, to make it avoid tags in paragraphs
        $flag = "#(<(\!--(.*?)|p|ul|ol|li|h[0-9]+|blockquote|hr|iframe(.*?)";
        $flag.= "|br|dd|dt|dl|img(.*?)|figure)>)#";
        // Applying regex
        foreach ( $list as $item ) {
            if ( trim( $item ) == "" ) {
                $return[] = "<p>&nbsp;</p>";
            } else {
                $return[] = ( preg_match( $flag, $item ) < 1 )
                    ? "<p>".trim( $item )."</p>"
                    : $item;
            }
        }
        return implode( "\r\n", $return );
    }
}