<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Class containing all tags (constants) for audit logs
 */
class LogEvent{
    /* GENERAL */
   const UPLOAD                 = 'UPLOAD';
   const FAILED                 = 'FAILED';
   const DOWNLOAD               = 'DOWNLOAD';
   const UPLOADED               = 'UPLOADED';
   const LOG_CREATED            = 'LOG_CREATED';
   
   
   /* USER */
   const USER_ACTIVATED         = 'USER_ACTIVATED';
   const USER_INACTIVE          = 'USER_INACTIVE';
   const USER_PURGED            = 'USER_PURGED';
   
   /* FILE */
   const FILE_UPDATED           = 'FILE_UPDATED';
   const FILE_EXPIRED           = 'FILE_EXPIRED';
   const FILE_MOVED             = 'FILE_MOVED';
   const FILE_DELETED           = 'FILE_DELETED';
   
   /* GUESTVOUCHER */
   const GUESTVOUCHER_CREATED   = 'GUESTVOUCHER_CREATED';
   const GUESTVOUCHER_SENT      = 'GUESTVOUCHER_SENT';
   const GUESTVOUCHER_USED      = 'GUESTVOUCHER_USED';
   const GUESTVOUCHER_EXPIRED   = 'GUESTVOUCHER_EXPIRED';
   const GUESTVOUCHER_CANCEL    = 'GUESTVOUCHER_CANCEL';
   const GUESTVOUCHER_CLOSED    = 'GUESTVOUCHER_CLOSED';
   
   /* TRANSFER */
   const TRANSFER_START         = 'TRANSFER_START';
   const TRANSFER_END           = 'TRANSFER_END';
   const TRANSFER_CLOSED        = 'TRANSFER_CLOSED';
   const TRANSFER_DELETED       = 'TRANSFER_DELETED';
   
   /* UPLOAD */
   const UPLOAD_START           = 'UPLOAD_START';
   const UPLOAD_END             = 'UPLOAD_END';
   
   /* DOWNLOAD */
   const DOWNLOAD_START         = 'DOWNLOAD_START';
   const DOWNLOAD_END           = 'DOWNLOAD_END';
   const DOWNLOAD_RESUME        = 'DOWNLOAD_RESUME';
   
}

/**
 * Class containing different protocols as string constants
 */
class Protocols {
    const IPV4 = "ipv4";
    const IPV6 = "ipv6";
}

/**
 * Class containing error codes
 */
class Errors{
    const NO_IP = "none";
}
