<?php
class Barracuda_Sniffs_Commenting_SpaceAfterCommentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        return array(T_COMMENT);

    } // end register()


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

		$valid = false;

		if (preg_match('|//\s|', $tokens[$stackPtr]['content']))
		{
			$valid = true;
		}

		if (preg_match('|\*[\s/]|', $tokens[$stackPtr]['content']))
		{
			$valid = true;
		}

		if ($valid === false)
		{
            $error = 'A space is required at the start of the comment %s';
            $data  = array(trim($tokens[$stackPtr]['content']));
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }

    }// end process()
}
// end class

