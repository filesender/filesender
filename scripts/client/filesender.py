#!/usr/bin/env python3
"""
#
# FileSender www.filesender.org
#
# Copyright (c) 2009-2019, AARNet, Belnet, HEAnet, SURF, UNINETT
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are met:
#
# *   Redistributions of source code must retain the above copyright
#     notice, this list of conditions and the following disclaimer.
# *   Redistributions in binary form must reproduce the above copyright
#     notice, this list of conditions and the following disclaimer in the
#     documentation and/or other materials provided with the distribution.
# *   Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
#     names of its contributors may be used to endorse or promote products
#     derived from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
# FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
# DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
# SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
# CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
# OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
"""
# pylint: disable=C0301

import sys
import os
import argparse
try:
    import textwrap #used to format help description and epilog
    import requests
    import base64
    import time
    import re
    from enum import Enum
    from collections.abc import MutableMapping
    import hmac
    import concurrent.futures
    import hashlib
    import urllib3
    import json
    from pathlib import Path
    import configparser
    from os.path import expanduser
    from math import ceil
    from typing import Optional
except ModuleNotFoundError as e:
    print(type(e))
    print(e.args)
    print(e)
    print('')
    print('ERROR: A required dependency is not installed, please check your')
    print('distribution packages or run something like the following')
    print('')
    print('pip3 install requests urllib3 ')
    sys.exit(1)

ENCRYPTION_SUPPORTED = True
try:
    from cryptography.hazmat.primitives.ciphers.aead import AESGCM
except ModuleNotFoundError as e:
    ENCRYPTION_SUPPORTED = False

class SupportedCryptTypes(Enum):
    """Enum of Encryption types implemented in this client"""
    AESGCM = "AES-GCM"

class SupportedHashTypes(Enum):
    """Enum of supported Hash Types in client"""
    SHA256 = "SHA-256"

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

class FileSenderCLI:
    """Actual class for CLI tool"""

    #settings
    base_url = '[base_url]'
    default_transfer_days_valid = 10
    username = None
    apikey = None

    #config
    worker_count = 4
    worker_timeout = 180
    max_chunk_retries = 20
    terasender_enabled = False
    max_transfer_files = 30

    files = {}
    transfer = None

    def __init__(self):
        """Constructor that sets all the config vars"""
        homepath = expanduser("~")

        config = configparser.ConfigParser()
        config.read([homepath + '/.filesender/filesender.py.ini', './filesender.py.ini'])
        if 'system' in config:
            self.base_url = config['system'].get('base_url', '[base_url]')
            self.default_transfer_days_valid = int(config['system'].get('default_transfer_days_valid', 10))
        if 'user' in config:
            self.username = config['user'].get('username')
            self.apikey = config['user'].get('apikey')

        #argv
        parser = argparse.ArgumentParser(
            formatter_class=argparse.RawDescriptionHelpFormatter,
            description=textwrap.dedent('''\
                File Sender CLI client.
                Source code: https://github.com/filesender/filesender/blob/master/scripts/client/filesender.py
            '''),
            epilog=textwrap.dedent('''\
                A config file can be added to {homepath}/.filesender/filesender.py.ini to avoid having to specify base URL, username and apikey on the command line.

                Example (Config file is present):
                python filesender.py -r recipient@example.com file1.txt''')
        )
        parser.add_argument("files", help="path to file(s) to send", nargs='*',default="")
        parser.add_argument("-v", "--verbose", action="store_true")
        parser.add_argument("-i", "--insecure", action="store_true")
        parser.add_argument("-p", "--progress", action="store_true")
        parser.add_argument("-s", "--subject")
        parser.add_argument("-m", "--message")
        parser.add_argument("-g", "--guest", action="store_true")
        parser.add_argument("-e", "--encrypted")
        parser.add_argument("-d", "--download" )
        parser.add_argument("-o","--output_dir" )
        parser.add_argument("--days", type=int)
        parser.add_argument("--threads")
        parser.add_argument("--timeout")
        parser.add_argument("--retries")

        required_named = parser.add_argument_group('required named arguments')

        # if we have found these in the config file they become optional arguments
        if self.username is None:
            required_named.add_argument("-u", "--username")
        else:
            parser.add_argument("-u", "--username")

        if self.apikey is None:
            required_named.add_argument("-a", "--apikey")
        else:
            parser.add_argument("-a", "--apikey")

        required_named.add_argument("-r", "--recipients", default="")

        # Do not change this seemingly out-of-place concat as it avoid getting this test messed up by clidownload.php
        if self.base_url == "[" + "base_url" + "]":
            required_named.add_argument("-b", "--base_url", required=True)
        else:
            parser.add_argument("-b", "--base_url")

        # if username is not a valid email address then ensure user supplies a valid email address
        if self.username is None or not bool(re.match(r'([^@|\s]+@[^@]+\.[^@|\s]+)', self.username)):
            required_named.add_argument("-f", "--from_address", help="filesender email from address", required=True)

        args = parser.parse_args()
        self.debug = args.verbose
        self.progress = args.progress
        self.insecure = args.insecure
        self.guest = args.guest
        self.user_threads = args.threads
        self.user_timeout = args.timeout
        self.user_retries = args.retries
        self.encrypted = args.encrypted
        self.transfer_timeout = args.days
        self.download_link = args.download
        self.download_folder = args.output_dir
        self.recipients = args.recipients
        self.arg_files = args.files
        self.subject=args.subject
        self.message=args.message

        if args.username is not None:
            self.username = args.username

        if args.apikey is not None:
            self.apikey = args.apikey

        if args.base_url is not None:
            self.base_url = args.base_url

        # If the user uses the filesender.py.ini file from the GUI (generated by clidownload.php) then the
        # 'username' value in the ini file is impacted by the 'auth_sp_saml_uid_attribute' in the filesender
        # config.php file.
        #
        # This script assumes that 'username' set in the filesender.py.ini file (set either by clidownload.php
        # or manually) can be used as both a user login AND an email address.
        # If 'username' is a non-email address then allow user to specify an email address
        # as an argument (i.e. -f/--from_address) or else the script will fail.
        self.from_address = self.username
        if hasattr(args, 'from_address'):
            self.from_address = args.from_address

        if not all([self.apikey, self.base_url, (self.username or self.from_address),self.arg_files,self.recipients]) and not self.download_link:
            missing_fields:list[str] = []
            if not self.apikey:
                missing_fields.append("-a, --api-key")
            if not self.base_url:
                missing_fields.append("-b, --base_url")
            if not self.username:
                missing_fields.append("-u, --username")
            if not self.recipients:
                missing_fields.append("-r, --recipients")
            if not self.arg_files:
                missing_fields.append("[FILE]")

            print(f"Missing required parameter, please provide the following: \n {','.join(missing_fields)}\n or -d --download with a valid download link")
            sys.exit(1)

        #configs
        try:
            info_response = requests.get(self.base_url+'/info', verify=True, timeout=60)
            top_url = re.sub("rest.php$", "", self.base_url)
            config_response = requests.get(top_url+'/filesender-config.js.php', verify=True, timeout=60) # for terasender config not in info.
        except requests.exceptions.SSLError as exc:
            if not self.insecure:
                print('Error: the SSL certificate of the server you are connecting to cannot be verified:')
                print(exc)
                print('For more information, please refer to https://www.digicert.com/ssl/. If you are absolutely certain of the identity of the server you are connecting to, you can use the --insecure flag to bypass this warning. Exiting...')
                sys.exit(1)
            elif self.insecure:
                print('Warning: Error: the SSL certificate of the server you are connecting to cannot be verified:')
                print(exc)
                print('Running with --insecure flag, ignoring warning...')
                info_response = requests.get(self.base_url+'/info', verify=False, timeout=60)
                config_response = requests.get(top_url+'/filesender-config.js.php', verify=False, timeout=60)

        self.upload_chunk_size = int(info_response.json()['upload_chunk_size'])

        try:
            regex_match = re.search(r"terasender_worker_count\D*(\d+)",config_response.text)
            self.worker_count = int(regex_match.group(1))
            regex_match = re.search(r"terasender_worker_start_must_complete_within_ms\D*(\d+)",config_response.text)
            self.worker_timeout = int(regex_match.group(1)) // 1000
            regex_match = re.search(r"terasender_worker_max_chunk_retries\D*(\d+)",config_response.text)
            self.worker_retries = int(regex_match.group(1))
            regex_match = re.search(r"terasender_enabled\W*(\w+)",config_response.text)
            self.terasender_enabled = regex_match.group(1) == "true"
            regex_match = re.search(r"max_transfer_files\D*(\d+)",config_response.text)
            self.max_transfer_files = int(regex_match.group(1))
            regex_match = re.search(r"max_transfer_days_valid\D*(\d+)",config_response.text)
            self.max_transfer_days_valid = int(regex_match.group(1))
            self.upload_crypted_chunk_size = self.upload_chunk_size + 32
        except TypeError as e:
            print("Failed to parse match, type error")
            print(e)
        except re.error as e:
            print("Failed to parse match, invalid regex")
            print(e)

        if self.terasender_enabled:
            if self.user_threads:
                self.worker_count = min(int(self.user_threads), self.worker_count)
        else:
            self.worker_count = 1

        if self.user_timeout:
            self.worker_timeout = min(int(self.user_timeout), self.worker_timeout)
        if self.user_retries:
            self.worker_retries = min(int(self.user_retries), self.worker_retries)

        if self.debug:
            print('base_url          : '+self.base_url)
            print('username          : '+self.username)
            print('apikey            : '+self.apikey)
            print('upload_chunk_size : '+str(self.upload_chunk_size)+' bytes')
            print('recipients        : '+self.recipients)
            print('files             : '+','.join(self.arg_files))
            print('insecure          : '+str(self.insecure))

        if self.encrypted and not self.download_link:
            if not ENCRYPTION_SUPPORTED:
                print("Failed to import 'cryptography' library, cannot proceed with encrypted transfer.")
                print("\npip3 install cryptography")
                sys.exit(1)

            encryption_details = {}
            try:
                #Regex parsing of filesender-config.js.php to get correct requirements.
                encryption_details["password_mixed_case_required"] = (re.search(
                    r"encryption_password_must_have_upper_and_lower_case: (\w+)",config_response.text).group(1)) == 'true'
                encryption_details["password_numbers_required"] = (re.search(
                    r"encryption_password_must_have_numbers: (\w+)",config_response.text).group(1)) == 'true'
                encryption_details["password_special_required"] = (re.search(
                    r"encryption_password_must_have_special_characters: (\w+)",config_response.text).group(1)) == 'true'

                encryption_details["iv_len"] = int(re.search(r"crypto_iv_len\D+(\d+)", config_response.text).group(1))
                encryption_details["password_hash_iterations"] = int(
                    re.search(r"encryption_password_hash_iterations_new_files\D+(\d+)", config_response.text).group(1))

                encryption_details["crypt_type"] = SupportedCryptTypes(re.search(r"crypto_crypt_name: '(.+)'",config_response.text).group(1))
                encryption_details["hash_name"] = SupportedHashTypes(re.search(r"crypto_hash_name: '(.+)'",config_response.text).group(1))

                #upload_chunk_base64_mode = (re.search(
                #    r"encryption_encode_encrypted_chunks_in_base64_during_upload: (\w+)",config_response.text).group(1)) == 'true'
            except KeyError as e:
                print("Failed to parse encryption requirements, encryption cannot be supported.")
                if self.debug:
                    print(e)
                sys.exit(1)

            encryption_details["password"] = self.encrypted
            encryption_details["password_encoding"] = 'none'
            encryption_details["password_version"] = "1"

            if encryption_details["crypt_type"] == SupportedCryptTypes.AESGCM:
                encryption_details["key_version"] = "3"

            if encryption_details["password_mixed_case_required"]:
                has_lower = any(map((lambda x: x.islower()) ,encryption_details["password"]))
                has_upper = any(map((lambda x: x.isupper()) ,encryption_details["password"]))
                if not (has_lower and has_upper):
                    print("Password does not meet mixed case required")
                    sys.exit(1)

            if encryption_details["password_numbers_required"]:
                if not any(map(lambda x: x.isdigit(),encryption_details["password"])):
                    print("Password does not meet number requirement")
                    sys.exit(1)

            if encryption_details["password_special_required"]:
                if all(map(lambda x: x.isalnum(),encryption_details["password"])):
                    print("Password does not meet special character requirement")
                    sys.exit(1)
            self.encryption_details = encryption_details

    ##########################################################################

    def flatten(self, d, parent_key=''):
        """Flatten object and sort"""
        items = []
        for k, v in d.items():
            new_key = parent_key + '[' + k + ']' if parent_key else k
            if isinstance(v, MutableMapping):
                items.extend(self.flatten(v, new_key))
            elif v is not None:
                items.append(new_key+'='+v)
        items.sort()
        return items

    def call(self,method, path, data, content=None, raw_content=None, options=None, try_count=0):
        """call the api"""
        if options is None:
            options={}
        init_data = {}
        for k in data:
            init_data[k] = data[k]
        data['remote_user'] = self.username
        data['timestamp'] = str(round(time.time()))
        signed = bytes(method+'&'+self.base_url.replace('https://','',1).replace('http://','',1)+path+'?'+('&'.join(self.flatten(data))), 'ascii')

        content_type = options['Content-Type'] if 'Content-Type' in options else 'application/json'

        inputcontent = None
        if content is not None and content_type == 'application/json':
            inputcontent = json.dumps(content,separators=(',', ':'))
            signed += bytes('&'+inputcontent, 'ascii')
        elif raw_content is not None:
            inputcontent = raw_content
            signed += bytes('&', 'ascii')
            signed += inputcontent

        #print(signed)
        bkey = bytearray()
        bkey.extend(map(ord, self.apikey))
        data['signature'] = hmac.new(bkey, signed, hashlib.sha1).hexdigest()
            #print("signed: " + str(signed))
        url = self.base_url+path+'?'+('&'.join(self.flatten(data)))
        headers = {
            "Accept": "application/json",
            "Content-Type": content_type
        }

        response = None
        try:
            if method == "get":
                response = requests.get(url, verify=not self.insecure, headers=headers, timeout=self.worker_timeout)
            elif method == "post":
                response = requests.post(url, data=inputcontent, verify=not self.insecure, headers=headers, timeout=self.worker_timeout)
            elif method == "put":
                response = requests.put(url, data=inputcontent, verify=not self.insecure, headers=headers, timeout=self.worker_timeout)
            elif method == "delete":
                response = requests.delete(url, verify=not self.insecure, headers=headers, timeout=self.worker_timeout)
        except Exception as _exc:
            if self.progress or self.debug:
                print("Failure when attempting to call: " + url)
                print("Retry attempt " + str((try_count + 1)))
            if self.debug:
                print(_exc)
            if try_count < self.worker_retries:
                time.sleep(5)
                return self.call(method=method, path=path, data=init_data,
                                 content=content, raw_content=raw_content,
                                 options=options, try_count=try_count + 1)
            raise _exc
        if response is None:
            raise ValueError('Client error')

        code = response.status_code
        #print(url)
        #sys.exit(1)
        #print(code)
        #print(response.text)

        if code!=200:
            if method!='post' or code!=201:
                if try_count > self.worker_retries:
                    raise ValueError('Http error '+str(code)+' '+response.text)

                if response.status_code == 500 and "auth_remote_signature_check_failed" in response.text:
                    raise ValueError("Authentication failed, check API token")

                if self.progress or self.debug:
                    print("Failure when attempting to call: " + url)
                    print("Retry attempt " + str((try_count + 1)))
                if self.debug:
                    print("Fail Reason: " + str(code))
                    print(response.text)
                time.sleep(5)
                return self.call(method=method, path=path, data=init_data,
                                 content=content, raw_content=raw_content,
                                 options=options, try_count=try_count + 1)

        if response.text=="":
            raise ValueError('Http error '+str(code)+' Empty response')

        if method!='post':
            return response.json()

        r = {}
        r['location']=response.headers['Location']
        r['created']=response.json()
        return r

    def post_transfer(self,files, recipients, subject=None, message=None, expires=None, options=None):
        """api: post transfer"""
        if expires is None:
            expires = round(time.time()) + (self.default_transfer_days_valid*24*3600)

        if options is None:
            options = []

        to = [x.strip() for x in recipients.split(',')]
        transfer_content = {
            'from': self.from_address,
            'files': files,
            'recipients': to,
            'subject': subject,
            'message': message,
            'expires': expires,
            'aup_checked':1,
            'options': options
        }
        if self.encrypted:
            transfer_content['encryption'] = True
            transfer_content['encryption_key_version']              = self.encryption_details['key_version']
            transfer_content['encryption_password_encoding']        = self.encryption_details['password_encoding']
            transfer_content['encryption_password_version']         = self.encryption_details['password_version']
            transfer_content['encryption_password_hash_iterations'] = self.encryption_details['password_hash_iterations']
        return self.call(
            'post',
            '/transfer',
            {},
            transfer_content,
            None,
            {}
        )

    def put_chunk(self,t, f, chunk, offset):
        """api: put file chunk"""
        return self.call(
            'put',
            '/file/'+str(f['id'])+'/chunk/'+str(offset),
            { 'key': f['puid'], 'roundtriptoken': t['roundtriptoken'] },
            None,
            chunk,
            { 'Content-Type': 'application/octet-stream' }
        )

    def file_complete(self,t,f):
        """api: put file complete"""
        return self.call(
            'put',
            '/file/'+str(f['id']),
            { 'key': f['puid'], 'roundtriptoken': t['roundtriptoken'] },
            { 'complete': True },
            None,
            {}
        )

    def transfer_complete(self,transfer):
        """api: transfer complete"""
        return self.call(
            'put',
            '/transfer/'+str(transfer['id']),
            { 'key': transfer['files'][0]['puid'] },
            { 'complete': True },
            None,
            {}
        )

    def delete_transfer(self,transfer):
        """api: delete transfer"""
        return self.call(
            'delete',
            '/transfer/'+str(transfer['id']),
            { 'key': transfer['files'][0]['puid'] },
            None,
            None,
            {}
        )

    def get_files_in_transfer(self,transfer_token) -> list[dict]:
        """api: Fetch the list of file information in a given transfer"""
        return self.call(
            'get',
            '/transfer/fileidsextended',
            {'token':transfer_token},
            None,
            None,
            {},
        )

    def download_file(self,token, file_info: dict, download_key: Optional[bytes], attempt: int = 0):
        """api: Download a given file to disk"""
        try:
            if attempt > 10:
                if file_info["encrypted"]:
                    print("    Unable to download file, was the password incorrect?")
                else:
                    print("    Unable to download file.")
                sys.exit(1)
            download_url = self.base_url.replace("rest.php","download.php")
            path = file_info['name'].split("/")
            download_file_name = path[-1]
            prefix = token
            if self.download_folder is not None:
                prefix = self.download_folder
            path = os.path.join(prefix,*path[:-1])
            Path(path).mkdir(parents=True, exist_ok=True)
            local_file_path = os.path.join(path, download_file_name)

            print(f"Downloading file \"{file_info['name']}\" to \"{local_file_path}\"")

            if file_info["encrypted"]:
                chunk_no = 0
                # calculation taken from crypto_app.js, the end overlaps with the start of the next range
                # because it uses the non encrypted byte ranges but requets the full crypted chunk size.
                # var endoffset     = 1 * (chunkid * chunksz + (1*$this.upload_crypted_chunk_size)-1);

                with open(local_file_path, mode='wb') as f:
                    for i in range(0,file_info['size'],self.upload_chunk_size):
                        end_offset = min(1 * (chunk_no * self.upload_chunk_size + (1*self.upload_crypted_chunk_size)-1),
                                         (1*file_info['size']) + 32 - 1)
                        download = requests.get(download_url,params={"token":token,"files_ids":file_info['id']},
                                                headers={ "Range": f"bytes={i}-{end_offset}"},verify=not self.insecure,timeout=None)
                        download.raise_for_status()
                        f.write(self.decrypt_chunk(download.content,chunk_no,file_info,download_key))
                        chunk_no += 1
            else:
                with requests.get(download_url,params={"token":token,"files_ids":file_info['id']},stream=True,verify=not self.insecure,timeout=None) as download:
                    download.raise_for_status()
                    with open(local_file_path, mode='wb') as f:
                        for chunk in download.iter_content(chunk_size=20_000):
                            f.write(chunk)
        except Exception as e:
            print(f"  Retrying on file {file_info['name']}")
            if self.debug:
                print(e)
            self.download_file(token,file_info,download_key,attempt+1)

    def post_guest(self,user_id, recipient, subject=None, message=None, expires=None, options=None):
        """api: post guest"""
        if expires is None:
            expires = round(time.time()) + (self.default_transfer_days_valid*24*3600)

        if options is None:
            options = []

        return self.call(
            'post',
            '/guest',
            {},
            {
                'from': user_id,
                'recipient': recipient,
                'subject': subject,
                'message': message,
                'expires': expires,
                'aup_checked':1,
                'options': options
            },
            None,
            {}
        )

    def send_file_chunk(self,transfer_file_index, offset):
        """send_file_chunk"""
        transfer_file = self.transfer['files'][transfer_file_index]
        filepath = self.files[transfer_file['name']+':'+str(transfer_file['size'])]['path']
        #put_chunks
        if self.debug:
            print('put_chunks: '+filepath)
        with open(filepath, mode='rb', buffering=0) as fin:
            fin.seek(offset)
            data = fin.read(self.upload_chunk_size)
            if self.encrypted:
                data = self.encrypt_chunk(data,
                                 ceil(offset/self.upload_chunk_size),
                                 transfer_file['name']+':'+str(transfer_file['size']))
            self.put_chunk(self.transfer, transfer_file, data, offset)
        return transfer_file_index


    def release_list(self,a):
        """Clear/free list"""
        del a[:]
        del a

    def generate_key(self):
        """generate_key for encryption"""
        return hashlib.pbkdf2_hmac(
                self.encryption_details["hash_name"].name,
                self.encryption_details["password"].encode('ascii'),
                self.encryption_details['salt'] ,
                self.encryption_details['password_hash_iterations'] ,
                256 // 8
            )

    def decrypt_chunk(self,chunk:bytes,chunk_no:int,file_info:dict,key:bytes):
        """Returns a non-encrypted chunk for the file"""

        if file_info['key-version'] != 3:
            raise NotImplementedError("Only AES-GCM is currently supported.")

        cipher = AESGCM(key)

        aead = base64.b64decode(file_info['fileaead'])
        iv = base64.b64decode(file_info['fileiv']) + chunk_no.to_bytes(4, byteorder="little")
        return cipher.decrypt(iv,chunk[len(iv):],aead)

    def encrypt_chunk(self,data,chunkid,file_key):
        """encrypt chunk"""
        if self.encryption_details["crypt_type"] == SupportedCryptTypes.AESGCM:
            return self.encrypt_chunk_aesgcm(data,chunkid,file_key)
        return None

    def encrypt_chunk_aesgcm(self,data,chunkid,files_key):
        """encryptchunk with AESGCM"""
        aead = self.files[files_key]['aead'].encode('ascii')
        iv = self.files[files_key]["iv_bytes"]
        cipher = AESGCM(self.encryption_details["Key"])

        fulliv = iv + chunkid.to_bytes(4, byteorder = 'little')
        #aead is used as additional data to properly calculate the tag.
        #The full IV (IV + Chunk ID) is used as a preamble to the file for verification.
        cipher_text = cipher.encrypt(fulliv,data,aead)
        return fulliv + cipher_text

    def deconstruct_download_link(self,download_link:str) -> tuple[str, str]:
        """Return the base path and access token from a provided download link"""

        components = download_link.split("?")
        assert len(components) == 2
        query_params = {comp.split('=')[0]: comp.split("=")[-1] for comp in components[1].split("&")}
        if "token" not in query_params:
            print("Error: Unable to find download token in url, please wrap the download string in ' or \" quotes ")
            sys.exit(1)
        return (components[0], query_params["token"])

    def download_transfer(self,download_link):
        """Save all files in a given transfer to the local disk."""
        (download_base_url,download_token) = self.deconstruct_download_link(download_link)
        if download_base_url not in self.base_url:
            print(f"Error: Download requested for non configured filesender instance {download_base_url}. This client is configured for {self.base_url}")
            sys.exit(1)
        file_list = self.get_files_in_transfer(download_token)
        download_size = sum(map(lambda x: x['size'],file_list))
        downloaded_total = 0
        download_key = None
        if file_list[0]['encrypted']:
            if not ENCRYPTION_SUPPORTED:
                print("Failed to import 'cryptography' library, cannot proceed with encrypted transfer.")
                print("\npip3 install cryptography")
                sys.exit(1)

            encryption_details = {}
            encryption_details["hash_name"] = SupportedHashTypes("SHA-256")
            encryption_details["password"] = self.encrypted
            encryption_details['salt'] = file_list[0]['key-salt'].encode('ascii')
            encryption_details['password_hash_iterations'] = file_list[0]['password-hash-iterations']
            self.encryption_details = encryption_details
            download_key = self.generate_key()
        for file in file_list:
            if self.progress:
                print(f"Downloading: {file['name']}")
            self.download_file(download_token,file, download_key)
            if self.progress:
                downloaded_total += file['size']
                print(f"Complete: {file['name']}")
                print(f"Total transfer {round((downloaded_total/download_size)*100)}% complete")

    ##########################################################################

    def process(self):
        """Process CLI command"""
        #post_transfer
        if self.download_link:
            self.download_transfer(self.download_link)
            sys.exit(0)

        if self.debug:
            print('post_transfer')

        if self.transfer_timeout is not None:
            assert self.transfer_timeout <= self.max_transfer_days_valid, f"(--days) value needs to be less than {self.max_transfer_days_valid} days"
            self.transfer_timeout = round(time.time()) + (self.transfer_timeout*24*3600)

        if self.guest:
            print('creating new guest ' + self.recipients)
            troptions = {'get_a_link':0}

            r = self.post_guest(self.username,
                                self.recipients,
                                subject=self.subject,
                                message=self.message,
                                expires=self.transfer_timeout,
                                options=troptions)
            sys.exit(0)

        file_list = []
        i = 0
        while i < len(self.arg_files):
            fn_abs = os.path.abspath(self.arg_files[i])
            if os.path.isdir(fn_abs):
                self.arg_files.extend(
                    map(lambda s: fn_abs+os.sep+s,
                        os.listdir(self.arg_files[i])))
            else:
                file_list.append(fn_abs)
                if len(file_list) > self.max_transfer_files:
                    print("You have exceeded the maximum number of files allowed in a transfer.")
                    sys.exit(1)
            i+=1

        file_root_path = ''
        if len(file_list)>1:
            file_root_path = os.path.commonpath(file_list)
            if self.debug and len(file_root_path) > 1:
                print("root path to all files is: "+file_root_path)

        files_transfer = []
        for fn_abs in file_list:
            fn = os.path.basename(fn_abs)
            if len(file_root_path) > 1:
                fn = fn_abs[len(file_root_path)+1:].replace("\\","/")

            size = os.path.getsize(fn_abs)

            if size == 0 and not os.path.isdir(fn_abs):
                print(f"Error: empty file '{fn_abs}' cannot continue")
                sys.exit(1)

            self.files[fn+':'+str(size)] = {
                'name':fn,
                'size':size,
                'path':fn_abs
            }
            file_transfer_object = {'name':fn,'size':size}

            if self.encrypted:
                if self.encryption_details["crypt_type"] == SupportedCryptTypes.AESGCM:
                    #we need to generate an IV and AEAD for each file.
                    #file_transfer_object is used to transfer the files to the filesender instance
                    #files is so we can reference the iv and aead later when needed.
                    iv_bytes = os.urandom(self.encryption_details["iv_len"]-4)
                    iv = base64.b64encode(iv_bytes).decode("ascii")
                    file_transfer_object["iv"] = iv

                    aead_string = "{"
                    aead_string += '"aeadversion":1,'
                    aead_string += '"chunkcount":'+str(ceil(size/self.upload_chunk_size))+','
                    aead_string += '"chunksize":'+str(self.upload_chunk_size)+','
                    aead_string += '"iv":'+'"'+iv+'"'+','
                    aead_string += '"aeadterminator":1'
                    aead_string += '}'
                    file_transfer_object["aead"] = base64.b64encode(aead_string.encode("ascii")).decode("ascii")
                    self.files[fn+':'+str(size)]["iv_bytes"] = iv_bytes
                    self.files[fn+':'+str(size)]['aead'] = aead_string
            files_transfer.append(file_transfer_object)
        self.release_list(file_list) # don't need it anymore

        troptions = {'get_a_link':0}
        self.transfer = self.post_transfer(files_transfer,
                                           self.recipients,
                                           subject=self.subject,
                                           message=self.message,
                                           expires=self.transfer_timeout,
                                           options=troptions)['created']
        file_index_queue = []
        if self.encrypted:
            self.encryption_details['salt'] = self.transfer['salt'].encode('ascii')
            self.encryption_details['Key'] = self.generate_key()

        offset_queue = []
        try:
            for index_of_file,f in enumerate(self.transfer['files']):
                #Prepare files for transfer
                if f['size'] == 0: #f size can be 0 because of directories, these can be safely skipped.
                    continue
                self.transfer['files'][index_of_file]['p_total_chunks'] = ceil(int(f['size'])/self.upload_chunk_size)
                self.transfer['files'][index_of_file]['p_progressed_chunks'] = 0
                #path = self.files[f['name']+':'+str(f['size'])]['path']
                size = self.files[f['name']+':'+str(f['size'])]['size']
                #put_chunks

                for i in range(0,size,self.upload_chunk_size):
                    file_index_queue.append(index_of_file)
                    offset_queue.append(i)
            #begin transfer
            with concurrent.futures.ThreadPoolExecutor(max_workers=self.worker_count) as e:
                fut = list(map(lambda fi,o: e.submit(self.send_file_chunk,fi,o),file_index_queue,offset_queue))
                for r in concurrent.futures.as_completed(fut):
                    self.transfer['files'][r.result()]['p_progressed_chunks'] += 1
                    if self.progress:
                        print('Uploading: '+self.transfer['files'][r.result()]['name']+' '+str(min(round(
                            self.transfer['files'][r.result()]['p_progressed_chunks']/
                            self.transfer['files'][r.result()]['p_total_chunks']*100),100))+'%')

                    if self.transfer['files'][r.result()]['p_progressed_chunks'] >= self.transfer['files'][r.result()]['p_total_chunks']:
                        self.file_complete(self.transfer,self.transfer['files'][r.result()])
                        if self.progress:
                            print(f"{self.transfer['files'][r.result()]['name']} complete")

            #transfer_complete
            if self.debug:
                print('transfer_complete')
            self.transfer_complete(self.transfer)
            if self.progress:
                print('Upload Complete')

        except Exception as inst:
            print(type(inst))
            print(inst.args)
            print(inst)

            #delete_transfer
            if self.debug:
                print('delete_transfer')
            self.delete_transfer(self.transfer)

##########################################################################

cli = FileSenderCLI()
cli.process()
