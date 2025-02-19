<?php
/**
 * This sniff prohibits the use of single line multi line comments
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Ryan Matthews <rmatthews@barracuda.com>
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   1.0.00
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 *  /* This is a single line multi line comment, which is prohibited. */
/* $hello = 'hello';
 * </code>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Ryan Matthews <rmatthews@barracuda.com>
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.0.00
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Barracuda_Sniffs_Commenting_DisallowSingleLineMultiCommentsSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_COMMENT);

    }// end register()


    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
		if (preg_match('/\/\*[^\n]*\*\//', $tokens[$stackPtr]['content']))
		{
            $error = 'Multi line comments are prohibited on single lines; found %s';
            $data  = array(trim($tokens[$stackPtr]['content']));
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }

    }// end process()
}
// end class

