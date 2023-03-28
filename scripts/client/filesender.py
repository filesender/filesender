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
    import re
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

# Globals
_PARSER = None


################################
# If error, print usage and exit
################################
def print_usage_and_exit(err_message):
    global _PARSER
    print("\n" + err_message + "\n")
    _PARSER.print_help()
    exit(1)


#####################################
# Read data from 'ini' and set values
#####################################
def get_config_data():
    # default settings
    base_url = None
    default_transfer_days_valid = 10
    username = None
    apikey = None

    try:
        config = configparser.ConfigParser()
        config.read(expanduser("~") + '/.filesender/filesender.py.ini')
        if 'system' in config:
            base_url = config['system'].get('base_url', base_url)
            default_transfer_days_valid = int(config['system'].get('default_transfer_days_valid',
                                                                   default_transfer_days_valid))
        if 'user' in config:
            username = config['user'].get('username')
            apikey = config['user'].get('apikey')
        # if we have found these in the config file they become optional arguments
        if username is None or apikey is None:
            print_usage_and_exit('Missing username or apikey in ini file!')

    except Exception as err:
        print_usage_and_exit('Error in ini file: error = {e}'.format(e=str(err)))

    return [base_url, default_transfer_days_valid, username, apikey]


######################################
# Set and check command-line arguments
######################################
def process_options(username):
    global _PARSER
    epilog_str = '''This script requires a configuration file called ".filesender/filesender.py.ini" in the user's \
    home directory: \n\
    Example: \n\
    [system] \n\
    base_url=http://localhost/filesender2/rest.php \n\
    default_transfer_days_valid=10 \n\
    [user] \n\
    username=username \n\
    apikey=c69d4c... '''

    _PARSER = argparse.ArgumentParser(epilog=epilog_str, formatter_class=argparse.RawTextHelpFormatter)
    _PARSER.add_argument("--hostname", help="destination host")
    _PARSER.add_argument("files", help="path to file(s) to send", nargs='+')
    _PARSER.add_argument("-v", "--verbose", action="store_true", help="verbose output")
    _PARSER.add_argument("-i", "--insecure", action="store_true", help="insecure flag to bypass ssl error warning")
    _PARSER.add_argument("-p", "--progress", action="store_true", help="progress flag")
    _PARSER.add_argument("-s", "--subject", help="filesender email subject")
    _PARSER.add_argument("-m", "--message", help="filesender email message")
    required_named = _PARSER.add_argument_group('required named arguments')
    required_named.add_argument("-r", "--recipients", required=True)

    # if username is not a valid email address then ensure user supplies a valid email address
    if not bool(re.match("([^@|\s]+@[^@]+\.[^@|\s]+)", username)):
        required_named.add_argument("-f", "--from_address", help="filesender email from address", required=True)
    args = _PARSER.parse_args()
    return args


##############################
# Reorganize data for API CALL
##############################
def flatten(d, parent_key=''):
    items = []
    for k, v in d.items():
        new_key = parent_key + '[' + k + ']' if parent_key else k
        if isinstance(v, MutableMapping):
            items.extend(flatten(v, new_key).items())
        else:
            items.append(new_key + '=' + v)
    items.sort()
    return items


######################################
# Execute network requests (API CALLS)
######################################
def call(site_info, method, path, data, content=None, raw_content=None, options={}):
    signed = bytes(method + '&' +
                   site_info['base_url'].replace('https://', '', 1).replace('http://', '', 1) + path +
                   '?' + ('&'.join(flatten(data))), 'ascii')

    content_type = options['Content-Type'] if 'Content-Type' in options else 'application/json'

    input_content = None
    if content is not None and content_type == 'application/json':
        input_content = json.dumps(content, separators=(',', ':'))
        signed += bytes('&' + input_content, 'ascii')
    elif raw_content is not None:
        input_content = raw_content
        signed += bytes('&', 'ascii')
        signed += input_content

    # print(signed)
    bkey = bytearray()
    bkey.extend(map(ord, site_info['apikey']))
    data['signature'] = hmac.new(bkey, signed, hashlib.sha1).hexdigest()

    url = site_info['base_url'] + path + '?' + ('&'.join(flatten(data)))
    headers = {
        "Accept": "application/json",
        "Content-Type": content_type
    }
    response = None
    if method == "get":
        response = requests.get(url, verify=not site_info['insecure'], headers=headers)
    elif method == "post":
        response = requests.post(url, data=input_content, verify=not site_info['insecure'], headers=headers)
    elif method == "put":
        response = requests.put(url, data=input_content, verify=not site_info['insecure'], headers=headers)
    elif method == "delete":
        response = requests.delete(url, verify=not site_info['insecure'], headers=headers)

    if response is None:
        raise Exception('Client error')

    code = response.status_code
    # print(url)
    # print(inputcontent)
    # print(code)
    # print(response.text)

    if code != 200:
        if method != 'post' or code != 201:
            raise Exception('Http error ' + str(code) + ' ' + response.text)

    if response.text == "":
        raise Exception('Http error ' + str(code) + ' Empty response')

    if method != 'post':
        return response.json()

    r = {'location': response.headers['Location'], 'created': response.json()}
    return r


########################
# API POST transfer call
########################
def post_transfer(site_info, files, expires=None, options=[]):
    if expires is None:
        expires = round(time.time()) + (site_info['default_transfer_days_valid'] * 24 * 3600)

    to = [x.strip() for x in site_info['recipients'].split(',')]

    return call(
        site_info,
        'post',
        '/transfer',
        {'remote_user': site_info['username'], 'timestamp': str(round(time.time()))},
        {
            'from': site_info['from_address'],
            'files': files,
            'recipients': to,
            'subject': site_info['subject'],
            'message': site_info['message'],
            'expires': expires,
            'aup_checked': 1,
            'options': options
        },
        None,
        {}
    )


####################################
# API PUT 'file chunk' transfer call
####################################
def put_chunk(site_info, t, f, chunk, offset):
    return call(
        site_info,
        'put',
        '/file/' + str(f['id']) + '/chunk/' + str(offset),
        {'key': f['uid'], 'roundtriptoken': t['roundtriptoken'],
         'remote_user': site_info['username'], 'timestamp': str(round(time.time()))},
        None,
        chunk,
        {'Content-Type': 'application/octet-stream'}
    )


#######################################
# API PUT 'file complete' transfer call
#######################################
def file_complete(site_info, t, f):
    return call(
        site_info,
        'put',
        '/file/' + str(f['id']),
        {'key': f['uid'], 'roundtriptoken': t['roundtriptoken'],
         'remote_user': site_info['username'], 'timestamp': str(round(time.time()))},
        {'complete': True},
        None,
        {}
    )


##################################
# API PUT 'transfer complete' call
##################################
def transfer_complete(site_info, transfer):
    return call(
        site_info,
        'put',
        '/transfer/' + str(transfer['id']),
        {'key': transfer['files'][0]['uid'],
         'remote_user': site_info['username'], 'timestamp': str(round(time.time()))},
        {'complete': True},
        None,
        {}
    )


############################
# API DELETE 'transfer' call
############################
def delete_transfer(site_info, transfer):
    return call(
        site_info,
        'delete',
        '/transfer/' + str(transfer['id']),
        {'key': transfer['files'][0]['uid'],
         'remote_user': site_info['username'], 'timestamp': str(round(time.time()))},
        None,
        None,
        {}
    )


#############################
# Query API to get chunk size
#############################
def get_upload_chunk_size(base_url, insecure):
    # configs
    try:
        response = requests.get(base_url + '/info', verify=True)
    except requests.exceptions.SSLError as exc:
        if not insecure:
            print('Error: the SSL certificate of the server you are connecting to cannot be verified:')
            print(exc)
            print('For more information, please refer to https://www.digicert.com/ssl/. \
                If you are absolutely certain of the identity of the server you are connecting to, you can use the \
                --insecure flag to bypass this warning. Exiting...')
            exit(1)
        elif insecure:
            print('Warning: Error: the SSL certificate of the server you are connecting to cannot be verified:')
            print(exc)
            print('Running with --insecure flag, ignoring warning...')
            response = requests.get(base_url + '/info', verify=False)
    if response.status_code is not 200:
        print("Error from server: status_code = {s}.".format(s=response.status_code))
        exit(1)
    upload_chunk_size = response.json()['upload_chunk_size']
    return upload_chunk_size


################
# Upload file(s)
################
def execute_upload(args, site_info):
    files = {}
    files_transfer = []
    for f in args.files:
        fn_abs = os.path.abspath(f)
        fn = os.path.basename(fn_abs)
        size = os.path.getsize(fn_abs)

        files[fn + ':' + str(size)] = {
            'name': fn,
            'size': size,
            'path': fn_abs
        }
        files_transfer.append({'name': fn, 'size': size})

    troptions = {'get_a_link': 0}

    transfer = post_transfer(site_info,
                             files_transfer,
                             expires=None,
                             options=troptions)['created']
    try:
        for f in transfer['files']:
            path = files[f['name'] + ':' + str(f['size'])]['path']
            size = files[f['name'] + ':' + str(f['size'])]['size']
            # putChunks
            if args.verbose:
                print('putChunks: ' + path)
            with open(path, mode='rb', buffering=0) as fin:
                for offset in range(0, size + 1, site_info['upload_chunk_size']):
                    if args.progress:
                        print('Uploading: ' + path + ' ' + str(offset) + '-' + str(
                            min(offset + site_info['upload_chunk_size'], size)) + ' ' +
                              str(round(offset / size * 100)) + '%')
                    data = fin.read(site_info['upload_chunk_size'])
                    # print(data)
                    put_chunk(site_info, transfer, f, data, offset)

            # fileComplete
            if args.verbose:
                print('fileComplete: ' + path)
            file_complete(site_info, transfer, f)
            if args.progress:
                print('Uploading: ' + path + ' ' + str(size) + ' 100%')

        # transferComplete
        if args.verbose:
            print('transferComplete')
        transfer_complete(site_info, transfer)
        if args.progress:
            print('Upload Complete')

    except Exception as inst:
        print(type(inst))
        print(inst.args)
        print(inst)

        # deleteTransfer
        if args.verbose:
            print('deleteTransfer')
        delete_transfer(site_info, transfer)


######
# MAIN
######
def main():
    [base_url, default_transfer_days_valid, username, apikey] = get_config_data()
    args = process_options(username)
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

    debug = args.verbose
    insecure = args.insecure

    upload_chunk_size = get_upload_chunk_size(base_url, insecure)

    site_info = {'base_url': base_url,
                 'default_transfer_days_valid': default_transfer_days_valid,
                 'username': username,
                 'apikey': apikey,
                 'from_address': from_address,
                 'insecure': insecure,
                 'recipients': args.recipients,
                 'subject': args.subject,
                 'message': args.message,
                 'upload_chunk_size': upload_chunk_size}

    if debug:
        print('base_url          : ' + base_url)
        print('username          : ' + username)
        print('apikey            : ' + apikey)
        print('upload_chunk_size : ' + str(upload_chunk_size) + ' bytes')
        print('recipients        : ' + args.recipients)
        print('files             : ' + ','.join(args.files))
        print('insecure          : ' + str(insecure))
        print('from_address      : ' + from_address)

    # postTransfer
    if debug:
        print('postTransfer')

    execute_upload(args, site_info)

    if debug:
        print("DONE!")


############
# START HERE
############
if __name__ == '__main__':
    main()
