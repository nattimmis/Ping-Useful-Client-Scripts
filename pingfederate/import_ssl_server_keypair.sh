#!/bin/bash


###################################################################################
# Copyright (C) 2016 ProofID Ltd
#
# Import a SSL certificate keyapir 
#
# @Author: Paul Heaney - pheaney@proofid.co.uk
#
# Usage: import_ssl_server_keypair.sh <p12 file> <password>
#
###################################################################################


PFBASE=https://localhost:9999
USERNAME=Administrator
PASSWORD=2Federate2

FLAGS="-k -s -u ${USERNAME}:${PASSWORD} --header X-XSRF-Header:\ PingFederate"

FILE=`base64 -i ${1}`

JSON_DATA=`cat <<JSON
{
  "fileData": "${FILE}}",
  "password": "${2}"
}
JSON`

echo ${FLAGS} | xargs curl -v -H "Content-Type: application/json" -X POST --data-binary "${JSON_DATA}" ${PFBASE}/pf-admin-api/v1/keyPairs/sslServer/import
