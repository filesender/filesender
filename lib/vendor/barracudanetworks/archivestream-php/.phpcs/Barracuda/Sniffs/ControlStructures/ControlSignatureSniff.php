<?php

if (class_exists('Squiz_Sniffs_ControlStructures_ControlSignatureSniff', true) === false)
{
    throw new PHP_CodeSniffer_Exception('Class Squiz_Sniffs_ControlStructures_ControlSignatureSniff not found');
}

// subclasses squiz controlstructures sniff to allow dropped braces and prevent same-line ones
class Barracuda_Sniffs_ControlStructures_ControlSignatureSniff extends Squiz_Sniffs_ControlStructures_ControlSignatureSniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array('PHP');

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[($stackPtr + 1)]) === false) {
            return;
        }

        // Single space after the keyword.
        if (in_array($tokens[$stackPtr]['code'], array(T_CATCH, T_IF, T_WHILE, T_FOR, T_FOREACH, T_ELSEIF, T_SWITCH))) {
            $found = 1;
            if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
                $found = 0;
            } else if ($tokens[($stackPtr + 1)]['content'] !== ' ') {
                if (strpos($tokens[($stackPtr + 1)]['content'], $phpcsFile->eolChar) !== false) {
                    $found = 'newline';
                } else {
                    $found = strlen($tokens[($stackPtr + 1)]['content']);
                }
            }

            if ($found !== 1) {
                $error = 'Expected 1 space after %s keyword; %s found';
                $data  = array(
                          strtoupper($tokens[$stackPtr]['content']),
                          $found,
                         );

                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword', $data);
                if ($fix === true) {
                    if ($found === 0) {
                        $phpcsFile->fixer->addContent($stackPtr, ' ');
                    } else {
                        $phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
                    }
                }
            }
        }

        // Single newline after the keyword.
        if (in_array($tokens[$stackPtr]['code'], array(T_TRY, T_DO, T_ELSE))
            && isset($tokens[$stackPtr]['scope_opener']) === true
        ) {
            $opener = $tokens[$stackPtr]['scope_opener'];
            $found = ($tokens[$opener]['line'] - $tokens[$stackPtr]['line']);
            if ($found !== 1) {
                $error = 'Expected 1 newline after % keyword; %s found';
                $data  = array(
                            strtoupper($tokens[$stackPtr]['content']),
                            $found,
                            );
                $fix   = $phpcsFile->addFixableError($error, $opener, 'NewlineAfterKeyword', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($stackPtr + 1); $i < $opener; $i++) {
                        if ($found > 0 && $tokens[$i]['line'] === $tokens[$opener]['line']) {
                            break;
                        }

                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->addContent($stackPtr, $phpcsFile->eolChar);
                    $phpcsFile->fixer->endChangeset();
                }
            }//end if
        }//end if

        // Single newline after closing parenthesis.
        if (isset($tokens[$stackPtr]['parenthesis_closer']) === true
            && isset($tokens[$stackPtr]['scope_opener']) === true
        ) {
            $closer  = $tokens[$stackPtr]['parenthesis_closer'];
            $opener  = $tokens[$stackPtr]['scope_opener'];
            $found = ($tokens[$opener]['line'] - $tokens[$closer]['line']);
            if ($found !== 1) {
                $error = 'Expected 1 newline after closing parenthesis; %s found';
                $fix   = $phpcsFile->addFixableError($error, $opener, 'NewlineAfterCloseParenthesis', array($found));
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($closer + 1); $i < $opener; $i++) {
                        if ($found > 0 && $tokens[$i]['line'] === $tokens[$opener]['line']) {
                            break;
                        }

                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->addContent($closer, $phpcsFile->eolChar);
                    $phpcsFile->fixer->endChangeset();
                }
            }//end if
        }//end if

        // Single newline after opening brace.
        if (isset($tokens[$stackPtr]['scope_opener']) === true) {
            $opener = $tokens[$stackPtr]['scope_opener'];
            for ($next = ($opener + 1); $next < $phpcsFile->numTokens; $next++) {
                $code = $tokens[$next]['code'];

                if ($code === T_WHITESPACE) {
                    continue;
                }

                // Skip all empty tokens on the same line as the opener.
                if ($tokens[$next]['line'] === $tokens[$opener]['line']
                    && (isset(PHP_CodeSniffer_Tokens::$emptyTokens[$code]) === true
                    || $code === T_CLOSE_TAG)
                ) {
                    continue;
                }

                // We found the first bit of a code, or a comment on the
                // following line.
                break;
            }

            $found = ($tokens[$next]['line'] - $tokens[$opener]['line']);
            if ($found !== 1) {
                $error = 'Expected 1 newline after opening brace; %s found';
                $data  = array($found);
                $fix   = $phpcsFile->addFixableError($error, $opener, 'NewlineAfterOpenBrace', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($opener + 1); $i < $next; $i++) {
                        if ($found > 0 && $tokens[$i]['line'] === $tokens[$next]['line']) {
                            break;
                        }

                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->addContent($opener, $phpcsFile->eolChar);
                    $phpcsFile->fixer->endChangeset();
                }
            }
        } else if ($tokens[$stackPtr]['code'] === T_WHILE) {
            // Zero spaces after parenthesis closer.
            $closer = $tokens[$stackPtr]['parenthesis_closer'];
            $found  = 0;
            if ($tokens[($closer + 1)]['code'] === T_WHITESPACE) {
                if (strpos($tokens[($closer + 1)]['content'], $phpcsFile->eolChar) !== false) {
                    $found = 'newline';
                } else {
                    $found = strlen($tokens[($closer + 1)]['content']);
                }
            }

            if ($found !== 0) {
                $error = 'Expected 0 spaces before semicolon; %s found';
                $data  = array($found);
                $fix   = $phpcsFile->addFixableError($error, $closer, 'SpaceBeforeSemicolon', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken(($closer + 1), '');
                }
            }
        }//end if

        // Only want to check multi-keyword structures from here on.
        if ($tokens[$stackPtr]['code'] === T_DO) {
            if (isset($tokens[$stackPtr]['scope_closer']) === false) {
                return;
            }

            $closer = $tokens[$stackPtr]['scope_closer'];
        } else if ($tokens[$stackPtr]['code'] === T_ELSE
            || $tokens[$stackPtr]['code'] === T_ELSEIF
            || $tokens[$stackPtr]['code'] === T_CATCH
        ) {
            $closer = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPtr - 1), null, true);
            if ($closer === false || $tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET) {
                return;
            }
        } else {
            return;
        }

        // Single space after closing brace.
        if ($tokens[$stackPtr]['code'] === T_DO) {
            $found = 1;
            if ($tokens[($closer + 1)]['code'] !== T_WHITESPACE) {
                $found = 0;
            } else if ($tokens[($closer + 1)]['content'] !== ' ') {
                if (strpos($tokens[($closer + 1)]['content'], $phpcsFile->eolChar) !== false) {
                    $found = 'newline';
                } else {
                    $found = strlen($tokens[($closer + 1)]['content']);
                }
            }

            if ($found !== 1) {
                $error = 'Expected 1 space after closing brace; %s found';
                $data  = array($found);
                $fix   = $phpcsFile->addFixableError($error, $closer, 'SpaceAfterCloseBrace', $data);
                if ($fix === true) {
                    if ($found === 0) {
                        $phpcsFile->fixer->addContent($closer, ' ');
                    } else {
                        $phpcsFile->fixer->replaceToken(($closer + 1), ' ');
                    }
                }
            }
        }

        // Single newline after closing brace.
        if ($tokens[$stackPtr]['code'] !== T_DO) {
            for ($next = ($closer + 1); $next < $phpcsFile->numTokens; $next++) {
                $code = $tokens[$next]['code'];

                if ($code === T_WHITESPACE) {
                    continue;
                }

                // Skip all empty tokens on the same line as the closer.
                if ($tokens[$next]['line'] === $tokens[$closer]['line']
                    && (isset(PHP_CodeSniffer_Tokens::$emptyTokens[$code]) === true
                    || $code === T_CLOSE_TAG)
                ) {
                    continue;
                }

                // We found the first bit of a code, or a comment on the
                // following line.
                break;
            }

            $found = ($tokens[$next]['line'] - $tokens[$closer]['line']);
            if ($found !== 1) {
                $error = 'Expected 1 newline after closing brace; %s found';
                $data  = array($found);
                $fix   = $phpcsFile->addFixableError($error, $closer, 'NewlineAfterCloseBrace', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($closer + 1); $i < $next; $i++) {
                        if ($found > 0 && $tokens[$i]['line'] === $tokens[$next]['line']) {
                            break;
                        }

                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->addContent($closer, $phpcsFile->eolChar);
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }

    }//end process()
}
