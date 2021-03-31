#! /usr/bin/python

# To change this template, choose Tools | Templates
# and open the template in the editor.
import sys
from mycoordtrans import radec2hmsdms,radec2gal,hmsdms2radec,hmsdms2gal,gal2hmsdms,gal2radec

__author__="ivan"
__date__ ="$Nov 25, 2012 9:16:59 AM$"

if __name__ == "__main__":
    func = globals()[sys.argv[1]]
    crd1 = sys.argv[2]
    crd2 = sys.argv[3]
    filedown = open(sys.argv[4],'w')
    result = func(crd1,crd2)
    newline = "%s,%s\n" % (result[0],result[1])
    filedown.write(newline)
    filedown.close()

