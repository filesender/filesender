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
  import requests
  import time
  from collections.abc import Iterable
  from collections.abc import MutableMapping
  import hmac
  import hashlib
  import urllib3
  import os
  import json
  import configparser
  from os.path import expanduser
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
parser = argparse.ArgumentParser()
parser.add_argument("files", help="path to file(s) to send", nargs='+')
parser.add_argument("-v", "--verbose", action="store_true")
parser.add_argument("-i", "--insecure", action="store_true")
parser.add_argument("-p", "--progress", action="store_true")
parser.add_argument("-s", "--subject")
parser.add_argument("-m", "--message")
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
args = parser.parse_args()
debug = args.verbose
progress = args.progress
insecure = args.insecure

if args.username is not None:
  username = args.username
  
if args.apikey is not None:
  apikey = args.apikey

  
#configs
try:
  response = requests.get(base_url+'/info', verify=True)
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
    response = requests.get(base_url+'/info', verify=False)
upload_chunk_size = response.json()['upload_chunk_size']

if debug:
  print('base_url          : '+base_url)
  print('username          : '+username)
  print('apikey            : '+apikey)
  print('upload_chunk_size : '+str(upload_chunk_size)+' bytes')
  print('recipients        : '+args.recipients)
  print('files             : '+','.join(args.files))
  print('insecure          : '+str(insecure))


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

def call(method, path, data, content=None, rawContent=None, options={}):
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

  url = base_url+path+'?'+('&'.join(flatten(data)))
  headers = {
    "Accept": "application/json",
    "Content-Type": content_type
  }
  response = None
  if method == "get":
    response = requests.get(url, verify=not insecure, headers=headers)
  elif method == "post":
    response = requests.post(url, data=inputcontent, verify=not insecure, headers=headers)
  elif method == "put":
    response = requests.put(url, data=inputcontent, verify=not insecure, headers=headers)
  elif method == "delete":
    response = requests.delete(url, verify=not insecure, headers=headers)

  if response is None:
    raise Exception('Client error')

  code = response.status_code
  #print(url)
  #print(inputcontent)
  #print(code)
  #print(response.text)

  if code!=200:
    if method!='post' or code!=201:
      raise Exception('Http error '+str(code)+' '+response.text)

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
  
  return call(
    'post',
    '/transfer',
    {},
    {
      'from': user_id,
      'files': files,
      'recipients': to,
      'subject': subject,
      'message': message,
      'expires': expires,
      'aup_checked':1,
      'options': options
    },
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

##########################################################################

#postTransfer
if debug:
  print('postTransfer')

files = {}
filesTransfer = []
for f in args.files:
  fn_abs = os.path.abspath(f)
  fn = os.path.basename(fn_abs)
  size = os.path.getsize(fn_abs)

  files[fn+':'+str(size)] = {
    'name':fn,
    'size':size,
    'path':fn_abs
  }
  filesTransfer.append({'name':fn,'size':size})

troptions = {'get_a_link':0}


transfer = postTransfer( username,
                         filesTransfer,
                         args.recipients,
                         subject=args.subject,
                         message=args.message,
                         expires=None,
                         options=troptions)['created']
#print(transfer)

try:
  for f in transfer['files']:
    path = files[f['name']+':'+str(f['size'])]['path']
    size = files[f['name']+':'+str(f['size'])]['size']
    #putChunks
    if debug:
      print('putChunks: '+path)
    with open(path, mode='rb', buffering=0) as fin:
      for offset in range(0,size+1,upload_chunk_size):
        if progress:
          print('Uploading: '+path+' '+str(offset)+'-'+str(min(offset+upload_chunk_size, size))+' '+str(round(offset/size*100))+'%')
        data = fin.read(upload_chunk_size)
        #print(data)
        putChunk(transfer, f, data, offset)

    #fileComplete
    if debug:
      print('fileComplete: '+path)
    fileComplete(transfer,f)
    if progress:
      print('Uploading: '+path+' '+str(size)+' 100%')


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
