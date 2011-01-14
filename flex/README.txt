FileSender SVN includes:

FLEX 4.0 Application (to be compiled) using command line compiler sdk
version 4.0.0

Example compile command: 

${FLEXSDK}bin/mxmlc -theme=${FLEXSDK}frameworks/themes/Halo/halo.swc \
 -static-link-runtime-shared-libraries=true  \
 -output ${CHECKOUT}www/swf/filesender.swf filesender.mxml

To upload large (>2GB) files requires Google Gears

See Developer documentation at www.filesender.org

chris@ricoshae.com
