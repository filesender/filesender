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
import requests
import time
import collections
import hmac
import hashlib
import urllib3
import os
import json

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

#settings
base_url = '[base_url]'
default_transfer_days_valid = 10

#argv
parser = argparse.ArgumentParser()
parser.add_argument("files", help="path to file(s) to send", nargs='+')
parser.add_argument("-v", "--verbose", action="store_true")
parser.add_argument("-p", "--progress", action="store_true")
parser.add_argument("-s", "--subject")
parser.add_argument("-m", "--message")
requiredNamed = parser.add_argument_group('required named arguments')
requiredNamed.add_argument("-u", "--username", required=True)
requiredNamed.add_argument("-a", "--apikey", required=True)
requiredNamed.add_argument("-r", "--recipients", required=True)
args = parser.parse_args()
debug = args.verbose
progress = args.progress

#configs
response = requests.get(base_url+'/info', verify=False)
upload_chunk_size = response.json()['upload_chunk_size']

if debug:
  print('base_url          : '+base_url)
  print('username          : '+args.username)
  print('apikey            : '+args.apikey)
  print('upload_chunk_size : '+str(upload_chunk_size)+' bytes')
  print('recipients        : '+args.recipients)
  print('files             : '+','.join(args.files))

##########################################################################

def flatten(d, parent_key=''):
  items = []
  for k, v in d.items():
    new_key = parent_key + '[' + k + ']' if parent_key else k
    if isinstance(v, collections.MutableMapping):
      items.extend(flatten(v, new_key).items())
    else:
      items.append(new_key+'='+v)
  items.sort()
  return items

def call(method, path, data, content=None, rawContent=None, options={}):
  data['remote_user'] = args.username
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
  bkey.extend(map(ord, args.apikey))
  data['signature'] = hmac.new(bkey, signed, hashlib.sha1).hexdigest()

  url = base_url+path+'?'+('&'.join(flatten(data)))
  headers = {
    "Accept": "application/json",
    "Content-Type": content_type
  }
  response = None
  if method == "get":
    response = requests.get(url, verify=False, headers=headers)
  elif method == "post":
    response = requests.post(url, data=inputcontent, verify=False, headers=headers)
  elif method == "put":
    response = requests.put(url, data=inputcontent, verify=False, headers=headers)
  elif method == "delete":
    response = requests.delete(url, verify=False, headers=headers)

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
      'options': options
    },
    None,
    {}
  )

def putChunk(f, chunk, offset):
  return call(
    'put',
    '/file/'+str(f['id'])+'/chunk/'+str(offset),
    { 'key': f['uid'] },
    None,
    chunk,
    { 'Content-Type': 'application/octet-stream' }
  )

def fileComplete(f):
  return call(
    'put',
    '/file/'+str(f['id']),
    { 'key': f['uid'] },
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

transfer = postTransfer(args.username, filesTransfer, args.recipients, subject=args.subject, message=args.message, expires=None, options=[])['created']
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
        putChunk(f, data, offset)

    #fileComplete
    if debug:
      print('fileComplete: '+path)
    fileComplete(f)
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
