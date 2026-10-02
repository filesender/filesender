<?php

class Barracuda_Sniffs_ControlStructures_NoInlineAssignmentSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
		return array(
			T_IF,
			T_ELSEIF,
		);

	} // end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
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

		$end_position = $tokens[$stackPtr]['parenthesis_closer'];

		// states: -1 = normal, 0 = start function call (probably), 1 = in function
		$function = -1;

		for ($position = $stackPtr; $position < $end_position; $position++)
		{
			if ($tokens[$position]['type'] == 'T_STRING')
			{
				$function = 0;
				continue;
			}

			if ($function === 0)
			{
				if ($tokens[$position]['type'] == 'T_OPEN_PARENTHESIS')
				{
					$function = 1;
					continue;
				}
			}
			elseif ($function !== 1)
			{
				$function = -1;
				if ($tokens[$position]['type'] == 'T_EQUAL')
				{
					$error = 'Inline assignment not allowed in if statements';
					$phpcsFile->addError($error, $stackPtr, 'IncDecLeft');
					return;
				}
			}
		}
    }
}

