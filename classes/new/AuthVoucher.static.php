<?php
/**
 *  Methods for checking if user is authenticated by
 *  guest voucher
 *
 */
class AuthVoucher  // these static methods might be moved to Utilities
{
    /**
     *  Check voucher exists and is available, returns true or false.
     *  @returns boolean true/false: true if
     */
    static public function aVoucher()
    {
        if (isset($_REQUEST['vid'])) {
            $vid = $_REQUEST['vid'];
            
            if (preg_match(Config::get('voucherRegEx'), $vid)
                && strlen($vid) == Config::get('voucherUIDLength')) {
                $statement = DBI::prepare('SELECT COUNT(*) FROM files WHERE filevoucheruid = :filevoucheruid');
                $statement->execute(array(':filevoucheruid' => $vid));
                $count = $statement->fetchColumn();

                return $count == 1;
            }
        }
        return false;
    }

    
    /**
     * Get voucher information.
     * TODO: Move this to Functions maybe?
     * @returns voucher-data as array or string "error"
     */
    static public function getVoucher()
    {
        if (isset($_REQUEST['vid'])) {
            $vid = $_REQUEST['vid'];

            if (preg_match(Config::get('voucherRegEx'), $vid) && 
                strlen($vid) == Config::get('voucherUIDLength')) {

                $statement = DBI::prepare("SELECT * FROM files WHERE filevoucheruid = :filevoucheruid");
                $statement->execute(array(':filevoucheruid' => $vid));
                $result = $statement->fetchAll();
                
                $returnArray = array();
                $returnArray["SessionID"] = session_id();

                foreach ($result as $row) {
                    array_push($returnArray, $row);
                }

                return $returnArray;
            }
        }
        return "error";
    }
}
