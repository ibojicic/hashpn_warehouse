#! /usr/bin/python


import os
os.environ['XDG_CONFIG_HOME'] = ".astropy/"
os.environ['XDG_CACHE_HOME'] = ".astropy/"


from math import floor
from math import sqrt
from sys import argv

os.environ['MPLCONFIGDIR'] = '/tmp'
import matplotlib
matplotlib.use('Agg')
import mylibs as my
import csv
import sys
from mycoordtrans import pix2coord


from myplotlibs import photFigure


__author__ = "ivan"
__date__ = "$Feb 27, 2011 10:19:39 AM$"

if __name__ == "__main__":

    use_fwhmpsf = 2.5 #CHECK

    use_sigma = "INDEF"
    
    inFile = argv[1]
    list = csv.reader(open(inFile,"r"),quoting=csv.QUOTE_NONNUMERIC,quotechar="'")
    data = dict((k, v) for k, v in list)
    f = open(sys.argv[2], 'w')
    tempcutout = "temp/tempcutout.fits"
    if (os.path.exists(tempcutout)): os.remove(tempcutout)
    
    use_annulus = data['aperture'] + 5. + floor(data['aperture'] / 5.)
    use_dannulus = sqrt( 3. * pow(data['aperture'], 2) + pow(use_annulus, 2)) - use_annulus

    tempcutsize = (use_annulus + use_dannulus) * data['cellsize'] * 1.2

    my.FITScutout(data['infile'], tempcutout, data['dra'], data['ddec'], tempcutsize, tempcutsize)

    my.myParams(tempcutout, data['outpath'], data['dra'], data['ddec'], use_fwhmpsf, use_sigma, use_annulus, use_dannulus, data['aperture'], '-2.15E9', 'INDEF', '-13.67', calgorithmval=data['calgor'])


    #FIRST SKY
    firstSky = my.myFitsky(tempcutout, data['outpath'])
    first_msky = my.parseIRAFOut(firstSky, 'msky')
    first_stdev = my.parseIRAFOut(firstSky, 'stdev')
    first_sskew = my.parseIRAFOut(firstSky, 'sskew')
    first_nsky = my.parseIRAFOut(firstSky, 'nsky')
    first_nsrej = my.parseIRAFOut(firstSky, 'nsrej')
    first_xinit = my.parseIRAFOut(firstSky, 'xinit')
    first_yinit = my.parseIRAFOut(firstSky, 'yinit')

    useMin = -60000.
    useMax = 60000.

    my.myParams(tempcutout, data['outpath'], data['dra'], data['ddec'], use_fwhmpsf, first_stdev[0], use_annulus, use_dannulus, data['aperture'], useMin, useMax, '-13.67', calgorithmval=data['calgor'])
    resFile = my.myPhot(tempcutout, data['outpath'])

    #SKY ESTIMATE
    f_msky = my.parseIRAFOut(resFile, 'msky')
    f_stdev = my.parseIRAFOut(resFile, 'stdev')
    f_sskew = my.parseIRAFOut(resFile, 'sskew')
    f_nsky = my.parseIRAFOut(resFile, 'nsky')
    f_nsrej = my.parseIRAFOut(resFile, 'nsrej')

    #OBS PARAMETERS
    f_itime = my.parseIRAFOut(resFile, 'itime')
    f_xairmass = my.parseIRAFOut(resFile, 'xairmass')
    f_ifilter = my.parseIRAFOut(resFile, 'ifilter')
    f_otime = my.parseIRAFOut(resFile, 'otime')
    f_rapert = my.parseIRAFOut(resFile, 'rapert')


    #FLUX ESTIMATE
    f_sum = my.parseIRAFOut(resFile, 'sum')
    f_area = my.parseIRAFOut(resFile, 'area')
    f_flux = my.parseIRAFOut(resFile, 'flux')

    f_mag = my.parseIRAFOut(resFile, 'mag')
    f_merr = my.parseIRAFOut(resFile, 'merr')

    #ERRORS
    f_cerror = my.parseIRAFOut(resFile, 'cerror')
    f_serror = my.parseIRAFOut(resFile, 'serror')
    f_perror = my.parseIRAFOut(resFile, 'perror')

    #CENTROID
    f_xinit = my.parseIRAFOut(resFile, 'xinit')
    f_yinit = my.parseIRAFOut(resFile, 'yinit')
    f_xcenter = my.parseIRAFOut(resFile, 'xcenter')
    f_ycenter = my.parseIRAFOut(resFile, 'ycenter')
    f_xshift = my.parseIRAFOut(resFile, 'xshift')
    f_yshift = my.parseIRAFOut(resFile, 'yshift')
    f_xerr = my.parseIRAFOut(resFile, 'xerr')
    f_yerr = my.parseIRAFOut(resFile, 'xerr')

    R_app = data['aperture'] * data['cellsize'] / 3600.
    R_dann = (use_annulus + use_dannulus) * data['cellsize'] / 3600.
    R_ann = use_annulus * data['cellsize'] / 3600.

    if f_msky[0] != 'INDEF':
        photResults = [str(data['msrNo']),str(data['field']),data['fits'],str(data['cellsize']),
                            data['mainid'], str(data['dra']), str(data['ddec']), data['calgor'],
                            f_msky[0], f_stdev[0], f_sskew[0], f_nsky[0], f_nsrej[0],
                            f_rapert[0], str(float(f_rapert[0]) * data['cellsize']),str(use_annulus),str(use_dannulus),
                            f_sum[0], f_area[0], f_flux[0], f_mag[0],
                            f_xinit[0], f_yinit[0], f_xcenter[0], f_ycenter[0], f_xshift[0], f_yshift[0],
                            f_xerr[0], f_yerr[0],f_merr[0], f_cerror[0], f_serror[0], f_perror[0]]

        fullResults = photResults
        csvResults = "'" + "','".join(fullResults) + "'\n"

        f.write(csvResults)

        centRA,centDEC = pix2coord(tempcutout, float(f_xcenter[0]), float(f_ycenter[0]))

        photFigure(tempcutout, data['outfile'], centRA, centDEC, R_ann, R_dann, R_app, data['majDiam'], \
                    -1, -1, 1, \
                    #anColour='magenta', apColour='red', diamColour = 'blue',
                    anColour='green', apColour='red', diamColour = 'blue', \
                    anLineSt = 'dotted',apLineSt = 'solid',diamLineSt = 'dashed', \
                    imExt = "png") #exclList[0]

f.close()



