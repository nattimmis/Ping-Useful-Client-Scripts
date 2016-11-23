#!/bin/bash


###################################################################################
# Copyright (C) 2016 ProofID Ltd
#
# Script to export a keypair as a p12
#
# @Author: Paul Heaney - pheaney@proofid.co.uk
#
###################################################################################


PABASE=https://localhost:9001
USERNAME=Administrator
PASSWORD=2Federate

FLAGS="-k -s -u ${USERNAME}:${PASSWORD} --header X-XSRF-Header:\ PingAccess"

case $1 in
	export)
		JSON_DATA=`cat <<JSON
{
  "id": "${2}", "password": "${3}"
}
JSON`
		echo ${FLAGS} | xargs curl -H "Content-Type: application/json" --data-binary "${JSON_DATA}" ${PABASE}/pa-admin-api/v2/keyPairs/$2/pkcs12 > $4
		;;
	list)
		echo "Listing ${FLAGS}"
		echo ${FLAGS} | xargs curl ${PABASE}/pa-admin-api/v2/keyPairs
		;;
	*)
		echo "Usage: $0 [ list | export <id> <password> ]"
esac

