<?php
/**
 * Barracuda_Sniffs_Functions_MultiLineFunctionDeclarationSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PEAR_Sniffs_Functions_FunctionDeclarationSniff', true) === false) {
    $error = 'Class PEAR_Sniffs_Functions_FunctionDeclarationSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Barracuda_Sniffs_Functions_MultiLineFunctionDeclarationSniff.
 *
 * Ensure single and multi-line function declarations are defined correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: 1.5.0RC4
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Barracuda_Sniffs_Functions_FunctionDeclarationSniff extends PEAR_Sniffs_Functions_FunctionDeclarationSniff
{


    /**
     * Processes multi-line declarations.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     * @param array                $tokens    The stack of tokens that make up
     *                                        the file.
     *
     * @return void
     */
    public function processMultiLineDeclaration(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    {
        // We need to work out how far indented the function
        // declaration itself is, so we can work out how far to
        // indent parameters.
        $functionIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($tokens[$i]['code'] === T_WHITESPACE) {
            $functionIndent = strlen($tokens[$i]['content']);
        }

        // The closing parenthesis must be on a new line, even
        // when checking abstract function definitions.
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        $prev         = $phpcsFile->findPrevious(
            T_WHITESPACE,
            ($closeBracket - 1),
            null,
            true
        );

        if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
            if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                $error = 'The closing parenthesis of a multi-line function declaration must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'CloseBracketLine');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($closeBracket);
                }
            }
        }

        // If this is a closure and is using a USE statement, the closing
        // parenthesis we need to look at from now on is the closing parenthesis
        // of the USE statement.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                $open         = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
                $closeBracket = $tokens[$open]['parenthesis_closer'];

                $prev = $phpcsFile->findPrevious(
                    T_WHITESPACE,
                    ($closeBracket - 1),
                    null,
                    true
                );

                if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
                    if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                        $error = 'The closing parenthesis of a multi-line use declaration must be on a new line';
                        $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'UseCloseBracketLine');
                        if ($fix === true) {
                            $phpcsFile->fixer->addNewlineBefore($closeBracket);
                        }
                    }
                }
            }//end if
        }//end if

        // Each line between the parenthesis should be indented 4 spaces.
        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $lastLine    = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($i === $tokens[$stackPtr]['parenthesis_closer']
                    || ($tokens[$i]['code'] === T_WHITESPACE
                        && (($i + 1) === $closeBracket
                            || ($i + 1) === $tokens[$stackPtr]['parenthesis_closer']))
                ) {
                    // Closing braces need to be indented to the same level
                    // as the function.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + $this->indent);
                }

                // We changed lines, so this should be a whitespace indent token.
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line function declaration not indented correctly; expected %s spaces but found %s';
                    $data  = array(
                        $expectedIndent,
                        $foundIndent,
                    );

                    $fix = $phpcsFile->addFixableError($error, $i, 'Indent', $data);
                    if ($fix === true) {
                        $spaces = str_repeat(' ', $expectedIndent);
                        if ($foundIndent === 0) {
                            $phpcsFile->fixer->addContentBefore($i, $spaces);
                        } else {
                            $phpcsFile->fixer->replaceToken($i, $spaces);
                        }
                    }
                }

                $lastLine = $tokens[$i]['line'];
            }//end if

            if ($tokens[$i]['code'] === T_ARRAY || $tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                // Skip arrays as they have their own indentation rules.
                if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                    $i = $tokens[$i]['bracket_closer'];
                } else {
                    $i = $tokens[$i]['parenthesis_closer'];
                }

                $lastLine = $tokens[$i]['line'];
                continue;
            }
        }//end for

        if (isset($tokens[$stackPtr]['scope_opener']) === true) {

            // any scope opener, we get the next token (should be EOL)
            $next = $tokens[($closeBracket + 1)];

            // if the token is EOL, then no error
            if ($next['content'] === $phpcsFile->eolChar)
            {
                $length = -1;
            } elseif ($next['code'] == T_OPEN_CURLY_BRACKET) {
                $length = 0;
            } else {
                $length = strlen($next['content']);
            }

            // any length means a problem, even zero
            if ($length >= 0) {
                $data = array($length);
                $code = 'NewLineBeforeOpenBrace';

                $error = 'There must be a newline before the opening brace of a multi-line function declaration; found ';

                // if whitespace, then report it
                if ($length > 0) {
                    $error .= '%s spaces';
                    $code   = 'SpaceBeforeOpenBrace';
                }
                // otherwise, no space but still brace on same line
                else
                {
                    $error .= ' opening brace';
                }

                $fix = $phpcsFile->addFixableError($error, ($closeBracket + 1), $code, $data);
                if ($fix === true) {

                    // remove whitespace
                    if ($length > 0)
                    {
                        $phpcsFile->fixer->replaceToken($closeBracket + 1, '');
                    }

                    // add the EOL token
                    $phpcsFile->fixer->addContent($closeBracket, $phpcsFile->eolChar);
                }

                return;
            }//end if

            // And just in case they do something funny before the brace...
            $next = $phpcsFile->findNext(
                T_WHITESPACE,
                ($closeBracket + 1),
                null,
                true
            );

            if ($next !== false && $tokens[$next]['code'] !== T_OPEN_CURLY_BRACKET) {
                $error = 'There must be a single space between the closing parenthesis and the opening brace of a multi-line function declaration';
                $phpcsFile->addError($error, $next, 'NoSpaceBeforeOpenBrace');
            }
        }//end if

        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $this->processBracket($phpcsFile, $openBracket, $tokens, 'function');

        if ($tokens[$stackPtr]['code'] !== T_CLOSURE) {
            return;
        }

        $use = $phpcsFile->findNext(T_USE, ($tokens[$stackPtr]['parenthesis_closer'] + 1), $tokens[$stackPtr]['scope_opener']);
        if ($use === false) {
            return;
        }

        $openBracket = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1), null);
        $this->processBracket($phpcsFile, $openBracket, $tokens, 'use');

        // Also check spacing.
        if ($tokens[($use - 1)]['code'] === T_WHITESPACE) {
            $gap = strlen($tokens[($use - 1)]['content']);
        } else {
            $gap = 0;
        }

    }//end processMultiLineDeclaration()


    /**
     * Processes the contents of a single set of brackets.
     *
     * @param PHP_CodeSniffer_File $phpcsFile   The file being scanned.
     * @param int                  $openBracket The position of the open bracket
     *                                          in the stack passed in $tokens.
     * @param array                $tokens      The stack of tokens that make up
     *                                          the file.
     * @param string               $type        The type of the token the brackets
     *                                          belong to (function or use).
     *
     * @return void
     */
    public function processBracket(PHP_CodeSniffer_File $phpcsFile, $openBracket, $tokens, $type='function')
    {
        $errorPrefix = '';
        if ($type === 'use') {
            $errorPrefix = 'Use';
        }

        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

        // The open bracket should be the last thing on the line.
        if ($tokens[$openBracket]['line'] !== $tokens[$closeBracket]['line']) {
            $next = $phpcsFile->findNext(T_WHITESPACE, ($openBracket + 1), null, true);
            if ($tokens[$next]['line'] !== ($tokens[$openBracket]['line'] + 1)) {
                $error = 'The first parameter of a multi-line '.$type.' declaration must be on the line after the opening bracket';
                $phpcsFile->addError($error, $next, $errorPrefix.'FirstParamSpacing');
            }
        }

        // Each line between the brackets should contain a single parameter.
        $lastCommaLine = null;
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            // Skip brackets, like arrays, as they can contain commas.
            if (isset($tokens[$i]['parenthesis_opener']) === true) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === T_COMMA) {
                if ($lastCommaLine !== null && $lastCommaLine === $tokens[$i]['line']) {
                    $error = 'Multi-line '.$type.' declarations must define one parameter per line';
                    $phpcsFile->addError($error, $i, $errorPrefix.'OneParamPerLine');
                } else {
                    // Comma must be the last thing on the line.
                    $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
                    if ($tokens[$next]['line'] !== ($tokens[$i]['line'] + 1)) {
                        $error = 'Commas in multi-line '.$type.' declarations must be the last content on a line';
                        $phpcsFile->addError($error, $next, $errorPrefix.'ContentAfterComma');
                    }
                }

                $lastCommaLine = $tokens[$i]['line'];
            }
        }

    }//end processBracket()

    /**
     * Processes single-line declarations.
     *
     * Just uses the Generic BSD-Allman brace sniff.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     * @param array                $tokens    The stack of tokens that make up
     *                                        the file.
     *
     * @return void
     */
    public function processSingleLineDeclaration(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    {
        if (class_exists('Generic_Sniffs_Functions_OpeningFunctionBraceBsdAllmanSniff', true) === false) {
            throw new PHP_CodeSniffer_Exception('Class Generic_Sniffs_Functions_OpeningFunctionBraceBsdAllmanSniff not found');
        }

        $sniff = new Generic_Sniffs_Functions_OpeningFunctionBraceBsdAllmanSniff();

        $sniff->process($phpcsFile, $stackPtr);

    }//end processSingleLineDeclaration()


}//end class

?>
