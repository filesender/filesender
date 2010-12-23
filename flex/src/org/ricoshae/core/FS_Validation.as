/*
 *  Filsender www.filesender.org
 *      
 *  Copyright (c) 2009-2010, Aarnet, HEAnet, UNINETT
 * 	All rights reserved.
 *
 * 	Redistribution and use in source and binary forms, with or without
 *	modification, are permitted provided that the following conditions are met:
 *	* 	Redistributions of source code must retain the above copyright
 *   		notice, this list of conditions and the following disclaimer.
 *   	* 	Redistributions in binary form must reproduce the above copyright
 *   		notice, this list of conditions and the following disclaimer in the
 *   		documentation and/or other materials provided with the distribution.
 *   	* 	Neither the name of Aarnet, HEAnet and UNINETT nor the
 *   		names of its contributors may be used to endorse or promote products
 *   		derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Aarnet, HEAnet and UNINETT ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Aarnet, HEAnet or UNINETT BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

package org.ricoshae.core
{
	
	import mx.core.FlexGlobals;
	import mx.controls.Alert;
	
	public class FS_Validation
	{
		
		private static function isValidEmail( str:String ):Boolean 
		{  
			
			var config:Object;
			config = FlexGlobals.topLevelApplication.config;
			// first one is default in config
    		//var emailExp:RegExp = new RegExp("[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?");    
     		//var emailExp:RegExp = /[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}/;
			//var emailExp:RegExp = /([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}/;
			var emailExp:RegExp = new RegExp(config["emailRegEx"].toString());
			//var emailExp:RegExp = /([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}/;
			//var emailExp:RegExp =new RegExp("[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}");
			//var emailExp:RegExp = new RegExp("[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}");
			//var emailExp:RegExp = new RegExp("/^[a-z.+-]+@\w[\w.-]+\.[\w.-]*[a-z][a-z]$/i");		   
			//var emailExp:RegExp = /^[a-z][\w.-]+@\w[\w.-]+\.[\w.-]*[a-z][a-z]$/i;   
			//Alert.show(config["emailRegEx"].toString() + ":" + emailExp.test(str.toLowerCase()));
     		return emailExp.test(str.toLowerCase());     
      	} 
      	//
      	
      	//
		public static function isValidEmailList(emailList : String, separator : String = ",") : Boolean
			{
            var addresses : Array = emailList.split(separator);
            for each (var email : String in addresses){
                if (!isValidEmail(email.replace(/\s/, "")))return false;
            }
            return true;
        }  
		
		

	}
}