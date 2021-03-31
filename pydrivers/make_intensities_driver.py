∏#! /usr/bin/python

#import glob
#import sys
from sys import argv
from myplotlibs import makeImage
from mylibs import FITScutout,FITScutoutMir
import csv
import os
from mystrings import id_generator


__author__="ivan"
__date__ ="$Feb 27, 2011 10:19:39 AM$"

if __name__ == "__main__":
    inFile = argv[1]
    list = csv.reader(open(inFile,"r"),quoting=csv.QUOTE_NONNUMERIC,quotechar="'")
    data = dict((k, v) for k, v in list)

    mrkrdata = []
    list = csv.reader(open(data['Markers'],"r"),quoting=csv.QUOTE_NONNUMERIC,quotechar="'")
    for line in list:
        comp = iter(line)
        mrkrs = dict(zip(comp,comp))
        mrkrdata.append(mrkrs)

    minbox = data['BoxSize']
    if data['Set'] == 'radioMASH3cm' or data['Set'] == 'radioMASH6cm' or data['Set'] == 'radioMASH13cm' or data['Set'] == 'radioMASH20cm':
        minbox = minbox / 2.
    box = [0, 0]
    if data['BoxSize'] < data['ImageSize'] : box = [minbox, minbox]

    tempIn = data['TEMP_DIR'] + id_generator() + 'inImage.fits'
    tempim= 'tmpdir/' + id_generator() + 'tempim'

    
    if data['Set'] == 'nvss' or data['Set'] == 'radiomash_3cm' or data['Set'] == 'radiomash_6cm' or data['Set'] == 'radiomash_13cm' or data['Set'] == 'radiomash_20cm' or data['Set'] == 'MGPS2':
        FITScutoutMir(data['SOURCE_DIR'] + data['Intimage'], tempIn, data['DRAJ2000'], data['DDECJ2000'], 2. * data['ImageSize'], 2. * data['ImageSize'])
    else:
        FITScutout(data['SOURCE_DIR'] + data['Intimage'], tempIn, data['DRAJ2000'], data['DDECJ2000'], 2. * data['ImageSize'], 2. * data['ImageSize']) #double

        '''
        makeIntImage(tempIn, data['OUT_DIR'] + data['OutImage'], data['DRAJ2000'], data['DDECJ2000'],
                    data['ImageSize'] / 3600., data['MajDiam'], data['MinDiam'], data['PA'], tempim,\
                    scatSymbols = mrkrdata, \
                    olayColour=data['MarkColour'], drawBox=box, addGrid = False,\
                    makeMainImage = data['m'], makeThumb = data['t'], makeSuperThumb = data['s'], makeZoomThumb = data['z'])#, addBeam=data['DrawBeam'])
    except:
       print "Something's wrong...skipping to the next one..."'''

    print tempIn

    makeImage(IntImage = tempIn, imSize = data['ImageSize'] / 3600., \
            out_name = data['OUT_DIR'] + data['OutImage'],
            zout_name = data['ZOUT_DIR'] + data['ZoutImage'],
            tempRGB = tempim, \
            Xcoord = data['DRAJ2000'], Ycoord = data['DDECJ2000'], majDiam = data['MajDiam'], \
            scatSymbols = mrkrdata, \
            drawBox=box, addGrid = False, \
            makeMainImage = data['m'], makeThumb = data['t'], makeSuperThumb = data['s'], makeZoomThumb = data['z'], makeZoomThumb = data['w'], makeOverlay = data['o'] )


    if (os.path.exists(tempIn)): os.remove(tempIn)
        



