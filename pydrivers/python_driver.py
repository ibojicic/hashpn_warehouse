#! /usr/bin/python

# To change this template, choose Tools | Templates
# and open the template in the editor.

import socket
import paramiko
import sys
import os

__author__="ivan"
__date__ ="$Nov 25, 2012 9:16:59 AM$"

if __name__ == "__main__":
    pyscript = sys.argv[1]
    infile = sys.argv[2]
    outfile = sys.argv[3]
    print "dfjkdsfkjsfkjsh"
    pathtolib = "/usr/lib/mypylibs/mydrivers/"
    command = "python %s%s %s %s" % (pathtolib,pyscript,infile,outfile)
    
    clientname = socket.gethostname() 
    if clientname != "corona":
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.load_system_host_keys()
        client.connect('corona',username='tcooper',password='oldlodgeskins1868')
        stdin, stdout, stderr = client.exec_command(command)
        err = stderr.readlines()
        out = stdout.readlines()
        client.close()
    else:
        os.system(command)