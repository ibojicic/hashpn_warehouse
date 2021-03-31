#! /usr/bin/python

#import glob
#import os.path

import os
os.environ['XDG_CONFIG_HOME'] = ".astropy/"
os.environ['XDG_CACHE_HOME'] = ".astropy/"


from sys import argv
from mylibs import FITScutout,findPercentile
from mystrings import id_generator

from myplotlibs import makeImage
import csv

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

    tempRim = data['TEMP_DIR'] + id_generator() + 'tRim.fits'
    tempGim = data['TEMP_DIR'] + id_generator() + 'tGim.fits'
    tempBim = data['TEMP_DIR'] + id_generator() + 'tBim.fits'

    tempRGB= 'tmpdir/' + id_generator() + 'tempRGB'

    minbox = data['BoxSize']
    if data['Set'] == 'radioMASH3cm' or data['Set'] == 'radioMASH6cm' or data['Set'] == 'radioMASH13cm' or data['Set'] == 'radioMASH20cm':
        minbox = minbox / 2.
    box = [0, 0]
    
    if data['BoxSize'] < data['ImageSize'] : box = [minbox, minbox]

    if data['statbox'] < 180 and data['m'] == 'y':
        imlev = 'v'
        FITScutout(data['SOURCE_DIR'] + data['Rimage'], tempRim, data['DRAJ2000'], data['DDECJ2000'], data['statbox'], data['statbox'])
        levels_R_min,levels_R_max = findPercentile(tempRim,data['minR_v'],data['maxR_v'], data['minR_r'], data['maxR_r'])
        FITScutout(data['SOURCE_DIR'] + data['Gimage'], tempGim, data['DRAJ2000'], data['DDECJ2000'], data['statbox'], data['statbox'])
        levels_G_min,levels_G_max = findPercentile(tempGim,data['minG_v'],data['maxG_v'], data['minG_r'], data['maxG_r'])
        FITScutout(data['SOURCE_DIR'] + data['Bimage'], tempBim, data['DRAJ2000'], data['DDECJ2000'], data['statbox'], data['statbox'])
        levels_B_min,levels_B_max = findPercentile(tempBim,data['minB_v'],data['maxB_v'], data['minB_r'], data['maxB_r'])
    else:
        imlev = 'p'
        levels_R_min,levels_R_max = data['minR_r'],data['maxR_r']
        levels_G_min,levels_G_max = data['minG_r'],data['maxG_r']
        levels_B_min,levels_B_max = data['minB_r'],data['maxB_r']

    if (os.path.exists(tempRim)): os.remove(tempRim)
    if (os.path.exists(tempGim)): os.remove(tempGim)
    if (os.path.exists(tempBim)): os.remove(tempBim)

    FITScutout(data['SOURCE_DIR'] + data['Rimage'], tempRim, data['DRAJ2000'], data['DDECJ2000'], 2. * data['ImageSize'], 2. * data['ImageSize'])
    FITScutout(data['SOURCE_DIR'] + data['Gimage'], tempGim, data['DRAJ2000'], data['DDECJ2000'], 2. * data['ImageSize'], 2. * data['ImageSize'])
    FITScutout(data['SOURCE_DIR'] + data['Bimage'], tempBim, data['DRAJ2000'], data['DDECJ2000'], 2. * data['ImageSize'], 2. * data['ImageSize'])

    makeImage(rImage = tempRim,gImage = tempGim,bImage = tempBim, imSize = data['ImageSize'] / 3600., \
            out_name = data['OUT_DIR'] + data['OutImage'],
            zout_name = data['ZOUT_DIR'] + data['ZoutImage'],
            rgbCube_name = data['rgb_cube'], tempRGB = tempRGB,\
            Xcoord = data['DRAJ2000'], Ycoord = data['DDECJ2000'], majDiam = data['MajDiam'], \
            r_imSc_max = levels_R_max, g_imSc_max = levels_G_max, b_imSc_max = levels_B_max,  \
            r_imSc_min=levels_R_min, g_imSc_min=levels_G_min, b_imSc_min=levels_B_min, \
            scatSymbols = mrkrdata, \
            redoRGB = data['redorgb'], \
            drawBox=box, addGrid = False, imlevels = imlev,\
            makeMainImage = data['m'], makeThumb = data['t'], makeSuperThumb = data['s'], makeZoomThumb = data['z'], makeWallThumb = data['w'], makeOverlay = data['o'] )

    if (os.path.exists(tempRim)): os.remove(tempRim)
    if (os.path.exists(tempGim)): os.remove(tempGim)
    if (os.path.exists(tempBim)): os.remove(tempBim)

    #print "sleeping for 300 sec"
    #time.sleep(300)



