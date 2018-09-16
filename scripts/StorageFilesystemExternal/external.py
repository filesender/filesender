#!/usr/bin/python -u
# encoding=utf8

import sys
import time

reload(sys)
sys.setdefaultencoding('utf8')

########## FILESENDER ##########

def fs_readChunk():
        filename=sys.argv[2]
        offset=int(sys.argv[3])
        length=int(sys.argv[4])

        #data = readchunk from external

        sys.stdout.write(data)
        sys.stdout.flush()


def fs_writeChunk():
        filename=sys.argv[2]
        offset=int(sys.argv[3])
        length=int(sys.argv[4])
        size=int(sys.argv[5])

        #write file at offset and print number of bytes written

        sys.stdout.write(str(length))
        sys.stdout.flush()


def fs_deleteFile():
        filename=sys.argv[2]

        #delete file


def fs_completeFile():
        filename=sys.argv[2]
        size=int(sys.argv[3])

        #close file (if needed)


########## MAIN ##########

method_name = sys.argv[1]
possibles = globals().copy()
possibles.update(locals())
method = possibles.get(method_name)
if not method:
        raise NotImplementedError("Method %s not implemented" % method_name)
method()
