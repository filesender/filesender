#!/usr/bin/env python3
#
# FileSender www.filesender.org
#
# Copyright (c) 2009-2019, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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
# *   Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
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

import argparse
try:
    import textwrap #used to format help description and epilog
    import requests
    import base64
    import time
    import re
    from collections.abc import Iterable
    from enum import Enum
    from collections.abc import MutableMapping
    import hmac
    import concurrent.futures
    import hashlib
    import urllib3
    import os
    import sys
    import json
    import configparser
    from os.path import expanduser
    from math import ceil
except Exception as e:
  print(type(e))
  print(e.args)
  print(e)
  print('')
  print('ERROR: A required dependency is not installed, please check your')
  print('distribution packages or run something like the following')
  print('')
  print('pip3 install requests urllib3 ')
  exit(1)
  
encryption_supported = True
try:
  from cryptography.hazmat.primitives.ciphers.aead import AESGCM
except Exception as e:
  encryption_supported = False
  
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

#settings
base_url = '[base_url]'
default_transfer_days_valid = 10
username = None
apikey = None
homepath = expanduser("~")

config = configparser.ConfigParser()
config.read(homepath + '/.filesender/filesender.py.ini')
if 'system' in config:
  base_url = config['system'].get('base_url', '[base_url]')
  default_transfer_days_valid = int(config['system'].get('default_transfer_days_valid', 10))
if 'user' in config:
  username = config['user'].get('username')
  apikey = config['user'].get('apikey')



#argv
parser = argparse.ArgumentParser(
    formatter_class=argparse.RawDescriptionHelpFormatter,
    description=textwrap.dedent(f'''\
      File Sender CLI client.
      Source code: https://github.com/filesender/filesender/blob/master/scripts/client/filesender.py
    '''),
    epilog=textwrap.dedent(f'''\
      A config file can be added to {homepath}/.filesender/filesender.py.ini to avoid having to specify username and apikey on the command line.

      Example (Config file is present): 
      python filesender.py -r reciever@example.com file1.txt''')
)
parser.add_argument("files", help="path to file(s) to send", nargs='+')
parser.add_argument("-v", "--verbose", action="store_true")
parser.add_argument("-i", "--insecure", action="store_true")
parser.add_argument("-p", "--progress", action="store_true")
parser.add_argument("-s", "--subject")
parser.add_argument("-m", "--message")
parser.add_argument("-g", "--guest", action="store_true")
parser.add_argument("-e","--encrypted")
parser.add_argument("--threads")
parser.add_argument("--timeout")
parser.add_argument("--retries")

requiredNamed = parser.add_argument_group('required named arguments')

# if we have found these in the config file they become optional arguments
if username is None:
  requiredNamed.add_argument("-u", "--username", required=True)
else:
  parser.add_argument("-u", "--username")
  
if apikey is None:
  requiredNamed.add_argument("-a", "--apikey", required=True)
else:
  parser.add_argument("-a", "--apikey")
  
requiredNamed.add_argument("-r", "--recipients", required=True)

# if username is not a valid email address then ensure user supplies a valid email address
if not bool(re.match("([^@|\s]+@[^@]+\.[^@|\s]+)", username)):
  requiredNamed.add_argument("-f", "--from_address", help="filesender email from address", required=True)

args = parser.parse_args()
debug = args.verbose
progress = args.progress
insecure = args.insecure
guest = args.guest
user_threads = args.threads
user_timeout = args.timeout
user_retries = args.retries
encrypted = args.encrypted

if args.username is not None:
  username = args.username
  
if args.apikey is not None:
  apikey = args.apikey

# If the user uses the filesender.py.ini file from the GUI (generated by clidownload.php) then the
# 'username' value in the ini file is impacted by the 'auth_sp_saml_uid_attribute' in the filesender
# config.php file.
#
# This script assumes that 'username' set in the filesender.py.ini file (set either by clidownload.php
# or manually) can be used as both a user login AND an email address.
# If 'username' is a non-email address then allow user to specify an email address
# as an argument (i.e. -f/--from_address) or else the script will fail.
from_address = username
if hasattr(args, 'from_address'):
  from_address = args.from_address

#configs
try:
  info_response = requests.get(base_url+'/info', verify=True)
  config_response = requests.get(base_url[0:-9]+'/filesender-config.js.php',verify=True)#for terasender config not in info.
except requests.exceptions.SSLError as exc:
  if not insecure:
    print('Error: the SSL certificate of the server you are connecting to cannot be verified:')
    print(exc)
    print('For more information, please refer to https://www.digicert.com/ssl/. If you are absolutely certain of the identity of the server you are connecting to, you can use the --insecure flag to bypass this warning. Exiting...')
    sys.exit(1)
  elif insecure:
    print('Warning: Error: the SSL certificate of the server you are connecting to cannot be verified:')
    print(exc)
    print('Running with --insecure flag, ignoring warning...')
    info_response = requests.get(base_url+'/info', verify=False)
    config_response = requests.get(base_url[-9]+'/filesender-config.js.php',verify=False)

upload_chunk_size = info_response.json()['upload_chunk_size']

try:
    regex_match = re.search(r"terasender_worker_count\D*(\d+)",config_response.text)
    worker_count = int(regex_match.group(1))
    regex_match = re.search(r"terasender_worker_start_must_complete_within_ms\D*(\d+)",config_response.text)
    worker_timeout = int(regex_match.group(1)) // 1000
    regex_match = re.search(r"terasender_worker_max_chunk_retries\D*(\d+)",config_response.text)
    worker_retries = int(regex_match.group(1))
    regex_match = re.search(r"terasender_enabled\W*(\w+)",config_response.text)
    terasender_enabled = regex_match.group(1) == "true"
    regex_match = re.search(r"max_transfer_files\D*(\d+)",config_response.text)
    max_transfer_files = int(regex_match.group(1))
except Exception as e:
    print("Failed to parse match")
    print(e)
    worker_count = 4
    worker_timeout = 180
    max_chunk_retries = 20
    terasender_enabled = False
    max_transfer_files = 30

if terasender_enabled:
  if user_threads:
    worker_count = min(int(user_threads), worker_count)
else:
  worker_count = 1

if user_timeout:
  worker_timeout = min(int(user_timeout), worker_timeout)
if user_retries:
  worker_retries  = min(int(user_retries), worker_retries)

if debug:
  print('base_url          : '+base_url)
  print('username          : '+username)
  print('apikey            : '+apikey)
  print('upload_chunk_size : '+str(upload_chunk_size)+' bytes')
  print('recipients        : '+args.recipients)
  print('files             : '+','.join(args.files))
  print('insecure          : '+str(insecure))



#Encyption Config
#These enums let us translate between the names in js and the values python wants whilst also being a nice match object.
class SupportedCryptTypes(Enum):
  """Enum of Encryption types implemented in this client"""
  AESGCM = "AES-GCM"

class SupportedHashTypes(Enum):
  """Enum of supported Hash Types in clinet"""
  SHA256 = "SHA-256"


if encrypted:
  if not encryption_supported:
    print("Failed ot import 'cryptography' library, cannot proceed with encrypted transfer.")
    print("\npip3 install cryptography")
    exit(1)
    
  encryption_details = {}
  try:
    #Regex parsing of filesender-config.js.php to get correct requirements.
    encryption_details["password_mixed_case_requied"] = (re.search(
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
    
    upload_chunk_base64_mode = (re.search(
      r"encryption_encode_encrypted_chunks_in_base64_during_upload: (\w+)",config_response.text).group(1)) == 'true'
  except Exception as e:
    print("Failed to parse encryption requirements, encryption cannot be supported.")
    if debug:
      print(e)
    sys.exit(1)
  
  encryption_details["password"] = encrypted
  encryption_details["password_encoding"] = 'none'
  encryption_details["password_version"] = "1"
  
  if encryption_details["crypt_type"] == SupportedCryptTypes.AESGCM: 
    encryption_details["key_version"] = "3"



  if encryption_details["password_mixed_case_requied"]:
    has_lower = any(map((lambda x: x.islower()) ,encryption_details["password"]))
    has_upper = any(map((lambda x: x.isupper()) ,encryption_details["password"]))
    if not (has_lower and has_upper):
      print("Password does not meet mixed case required")
      exit(1)

  if encryption_details["password_numbers_required"]:
    if not any(map(lambda x: x.isdigit(),encryption_details["password"])):
      print("Password does not meet number requirment")
      exit(1)

  if encryption_details["password_special_required"]:
    if all(map(lambda x: x.isalnum(),encryption_details["password"])):
      print("Password does not meet special character requiremnet")
      exit(1)




##########################################################################

def flatten(d, parent_key=''):
  items = []
  for k, v in d.items():
    new_key = parent_key + '[' + k + ']' if parent_key else k
    if isinstance(v, MutableMapping):
      items.extend(flatten(v, new_key).items())
    else:
      items.append(new_key+'='+v)
  items.sort()
  return items

def call(method, path, data, content=None, rawContent=None, options={}, tryCount=0):
  initData = {}
  for k in data:
    initData[k] = data[k]
  data['remote_user'] = username
  data['timestamp'] = str(round(time.time()))
  flatdata=flatten(data)
  signed = bytes(method+'&'+base_url.replace('https://','',1).replace('http://','',1)+path+'?'+('&'.join(flatten(data))), 'ascii')

  content_type = options['Content-Type'] if 'Content-Type' in options else 'application/json'

  inputcontent = None
  if content is not None and content_type == 'application/json':
    inputcontent = json.dumps(content,separators=(',', ':'))
    signed += bytes('&'+inputcontent, 'ascii')
  elif rawContent is not None:
    inputcontent = rawContent
    signed += bytes('&', 'ascii')
    signed += inputcontent

  #print(signed)
  bkey = bytearray()
  bkey.extend(map(ord, apikey))
  data['signature'] = hmac.new(bkey, signed, hashlib.sha1).hexdigest()
  #print("signed: " + str(signed))
  url = base_url+path+'?'+('&'.join(flatten(data)))
  headers = {
    "Accept": "application/json",
    "Content-Type": content_type
  }

  response = None
  try:
    if method == "get":
      response = requests.get(url, verify=not insecure, headers=headers, timeout=worker_timeout)
    elif method == "post":
      response = requests.post(url, data=inputcontent, verify=not insecure, headers=headers, timeout=worker_timeout)
    elif method == "put":
      response = requests.put(url, data=inputcontent, verify=not insecure, headers=headers, timeout=worker_timeout)
    elif method == "delete":
      response = requests.delete(url, verify=not insecure, headers=headers, timeout=worker_timeout)
  except Exception as _exc:
    if progress or debug:
      print("Failure when attempting to call: " + url)
      print("Retry attempt " + str((tryCount + 1)))
    if debug:
      print(_exc)
    if tryCount < worker_retries:
      time.sleep(300)
      return call(method=method, path=path, data=initData,
                  content=content, rawContent=rawContent,
                   options=options, tryCount=tryCount + 1)
    raise _exc
  if response is None:
    raise Exception('Client error')

  code = response.status_code
  #print(url)
  #exit(1)
  #print(code)
  #print(response.text)

  if code!=200:
    if method!='post' or code!=201:
      if tryCount > worker_retries:
        raise Exception('Http error '+str(code)+' '+response.text)
      else:
        if progress or debug:
          print("Failure when attempting to call: " + url)
          print("Retry attempt " + str((tryCount + 1)))
        if debug:
          print("Fail Reason: " + str(code))
          print(response.text)          
        time.sleep(300)
        return call(method=method, path=path, data=initData,
                  content=content, rawContent=rawContent,
                   options=options, tryCount=tryCount + 1)

  if response.text=="":
    raise Exception('Http error '+str(code)+' Empty response')

  if method!='post':
    return response.json()

  r = {}
  r['location']=response.headers['Location']
  r['created']=response.json()
  return r

def postTransfer(user_id, files, recipients, subject=None, message=None, expires=None, options=[]):

  if expires is None:
    expires = round(time.time()) + (default_transfer_days_valid*24*3600)

  to = [x.strip() for x in recipients.split(',')]
  transferContent = {
      'from': from_address,
      'files': files,
      'recipients': to,
      'subject': subject,
      'message': message,
      'expires': expires,
      'aup_checked':1,
      'options': options
  }
  if encrypted:
      transferContent['encryption'] = True
      transferContent['encryption_key_version'] =  encryption_details['key_version']
      transferContent['encryption_password_encoding'] =  encryption_details['password_encoding']
      transferContent['encryption_password_version'] =  encryption_details['password_version']
      transferContent['encryption_password_hash_iterations'] =  encryption_details['password_hash_iterations']
  return call(
    'post',
    '/transfer',
    {},
    transferContent,
    None,
    {}
  )

def putChunk(t, f, chunk, offset):
  return call(
    'put',
    '/file/'+str(f['id'])+'/chunk/'+str(offset),
    { 'key': f['uid'], 'roundtriptoken': t['roundtriptoken'] },
    None,
    chunk,
    { 'Content-Type': 'application/octet-stream' }
  )

def fileComplete(t,f):
  return call(
    'put',
    '/file/'+str(f['id']),
    { 'key': f['uid'], 'roundtriptoken': t['roundtriptoken'] },
    { 'complete': True },
    None,
    {}
  )

def transferComplete(transfer):
  return call(
    'put',
    '/transfer/'+str(transfer['id']),
    { 'key': transfer['files'][0]['uid'] },
    { 'complete': True },
    None,
    {}
  )

def deleteTransfer(transfer):
  return call(
    'delete',
    '/transfer/'+str(transfer['id']),
    { 'key': transfer['files'][0]['uid'] },
    None,
    None,
    {}
  )

def postGuest(user_id, recipient, subject=None, message=None, expires=None, options=[]):

  if expires is None:
    expires = round(time.time()) + (default_transfer_days_valid*24*3600)

  return call(
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

def send_file_chunk(transfer_file_index, offset):
  transfer_file = transfer['files'][transfer_file_index]
  filepath = files[transfer_file['name']+':'+str(transfer_file['size'])]['path']
  #putChunks
  if debug:
    print('putChunks: '+path)
  with open(filepath, mode='rb', buffering=0) as fin:
    fin.seek(offset)
    data = fin.read(upload_chunk_size)
    if encrypted:
      data = encrypt_chunk(data,
                           ceil(offset/upload_chunk_size),
                           transfer_file['name']+':'+str(transfer_file['size']))
    putChunk(transfer, transfer_file, data, offset)
  return transfer_file_index


def release_list(a):
  del a[:]
  del a

def generate_key():
  return hashlib.pbkdf2_hmac(
        encryption_details["hash_name"].name, 
        encryption_details["password"].encode('ascii'), 
        encryption_details['salt'] ,
        encryption_details['password_hash_iterations'] , 
        256 // 8
    )

def encrypt_chunk(data,chunkid,fileKey):
  match encryption_details["crypt_type"]:
    case SupportedCryptTypes.AESGCM:
      return encrypt_chunk_aesgcm(data,chunkid,fileKey)

def encrypt_chunk_aesgcm(data,chunkid,files_key):
  aead = files[files_key]['aead'].encode('ascii')
  iv = files[files_key]["iv_bytes"]
  cipher = AESGCM(encryption_details["Key"])

  fulliv = iv + chunkid.to_bytes(4, byteorder = 'little')
  #aead is used as additional data to properly calculate the tag.
  #The full IV (IV + Chunk ID) is used as a preamble to the file for verification.
  cipher_text = cipher.encrypt(fulliv,data,aead)
  return fulliv + cipher_text


##########################################################################

#postTransfer
if debug:
  print('postTransfer')

if guest:
  print('creating new guest ' + args.recipients)
  troptions = {'get_a_link':0}
  r = postGuest( username,
                 args.recipients,
                 subject=args.subject,
                 message=args.message,
                 expires=None,
                 options=troptions)
  exit(0)

fileList = []
i = 0
while i < len(args.files):
  fn_abs = os.path.abspath(args.files[i])
  if(os.path.isdir(fn_abs)):
    args.files.extend(
      map(lambda s: fn_abs+os.sep+s,
        os.listdir(args.files[i])))
  else:
    fileList.append(fn_abs)
    if (len(fileList) > max_transfer_files):
      print("You have exceeded the maximum number of files allowed in a transfer.")
      exit(1)
  i+=1

fileRootPath = ''
if len(fileList)>1:
  fileRootPath = os.path.commonpath(fileList)
  if debug and len(fileRootPath) > 1:
    print("root path to all files is: "+fileRootPath)

files = {}
filesTransfer = []
for fn_abs in fileList:
  fn = os.path.basename(fn_abs)
  if len(fileRootPath) > 1:
    fn = fn_abs[len(fileRootPath)+1:].replace("\\","/")

  size = os.path.getsize(fn_abs)

  files[fn+':'+str(size)] = {
    'name':fn,
    'size':size,
    'path':fn_abs
  }
  file_transfer_object = {'name':fn,'size':size}

  if encrypted:
    if encryption_details["crypt_type"] == SupportedCryptTypes.AESGCM:
      #we need to generate an IV and AEAD for each file.
      #file_transfer_object is used to transfer the files to the filesender instance
      #files is so we can reference the iv and aead later when needed.
      iv_bytes =  os.urandom(encryption_details["iv_len"]-4)
      iv = base64.b64encode(iv_bytes).decode("ascii")
      file_transfer_object["iv"] = iv

      aead_string = "{"
      aead_string += '"aeadversion":1,'
      aead_string += '"chunkcount":' + str(ceil( size / upload_chunk_size ))  +','
      aead_string += '"chunksize":'   + str(upload_chunk_size)   +',' 
      aead_string += '"iv":'          + '"' + iv + '"' + ','
      aead_string += '"aeadterminator":1' 
      aead_string += '}'
      file_transfer_object["aead"] = base64.b64encode(aead_string.encode("ascii")).decode("ascii")
      files[fn+':'+str(size)]["iv_bytes"] = iv_bytes
      files[fn+':'+str(size)]['aead'] = aead_string
  filesTransfer.append(file_transfer_object)
release_list(fileList) # don't need it anymore

troptions = {'get_a_link':0}

transfer = postTransfer( username,
                         filesTransfer,
                         args.recipients,
                         subject=args.subject,
                         message=args.message,
                         expires=None,
                         options=troptions)['created']
fileIndexQueue = []
if encrypted:
  encryption_details['salt'] = transfer['salt'].encode('ascii')
  encryption_details['Key'] = generate_key()

offsettQueue = []
try:
  for indexOfFile,f in enumerate(transfer['files']):
    #Prepare files for transfer
    if f['size'] == 0: #f size can be 0 because of directories, these can be safely skipped.
      continue
    transfer['files'][indexOfFile]['p_total_chunks'] = ceil(f['size']/upload_chunk_size)
    transfer['files'][indexOfFile]['p_progressed_chunks'] = 0
    path = files[f['name']+':'+str(f['size'])]['path']
    size = files[f['name']+':'+str(f['size'])]['size']
    #putChunks
    
    for i in range(0,size,upload_chunk_size):
      fileIndexQueue.append(indexOfFile)
      offsettQueue.append(i)
  #begin transfer
  with concurrent.futures.ThreadPoolExecutor(max_workers=worker_count) as e:
    fut = list(map(lambda fi,o: e.submit(send_file_chunk,fi,o),fileIndexQueue,offsettQueue))
    for r in concurrent.futures.as_completed(fut):
      transfer['files'][r.result()]['p_progressed_chunks'] += 1
      if progress:
        print('Uploading: '+transfer['files'][r.result()]['name']+' '+str(min(round(
          transfer['files'][r.result()]['p_progressed_chunks']/
          transfer['files'][r.result()]['p_total_chunks']*100),100))+'%')
      
      if transfer['files'][r.result()]['p_progressed_chunks'] >= transfer['files'][r.result()]['p_total_chunks']:
        fileComplete(transfer,transfer['files'][r.result()])
        if progress:
          print(f"{transfer['files'][r.result()]['name']} complete")

  #transferComplete
  if debug:
    print('transferComplete')
  transferComplete(transfer)
  if progress:
    print('Upload Complete')

except Exception as inst:
  print(type(inst))
  print(inst.args)
  print(inst)

  #deleteTransfer
  if debug:
    print('deleteTransfer')
  deleteTransfer(transfer)
sys.exit(0)
