#!/usr/bin/perl
#
# $Id: bibquery.pl,v 1.2 1996/04/19 20:44:56 alberto Exp alberto $
#
# Test program for the ADS WWW library.
# Reads a list of bibcodes from the command line or standard input,
# retrieves the references from the ADS abstract service, parses
# them into associative arrays, and prints them out properly formatted.
#
# Example:
#    bibquery.pl '1994ApJ...420..551D' '1994ApJ...420..558B'
#
# Written by Alberto Accomazzi <alberto@cfa.harvard.edu>,
#                              http://cfa-www.harvard.edu/~alberto  
#
# $Log: bibquery.pl,v $
# Revision 1.2  1996/04/19  20:44:56  alberto
# Fixed a couple of typos, added object field retrieval
# with adswww version 0.5.
#
# Revision 1.1  1995/10/24  19:24:00  alberto
# Initial revision
#
#
#

unshift(@INC, $libloc) if ($libloc = $ENV{'LIBWWW_PERL'});
push(@INC,"/opt/local/lib/perl") if (-d "/opt/local/lib/perl");

require "adswww.pl";

# query astronomy and astrophysics database by default
$query{'db_key'} = 'AST';

while (($_ = $ARGV[0]) =~ /^\-/) {
    shift(@ARGV);
    if (/^\-debug/) {
	$debug = 1;
	$ads'debug = 1;                              #'
    } elsif (/^\-db/) {
	&Usage("missing argument for \"-db\" option")
	    unless ($query{'db_key'} = shift(@ARGV));
    } else {
	&Usage("unknown option $_");
    }
}

if (@ARGV) {
    @bibs = @ARGV;
} else {
    while (<STDIN>) {
	s/^\s+|\s+$//g;
	push(@bibs,$_) if ($_);
    }
}

die "$0: no input bibcodes specified!\n" unless (@bibs);
print STDERR "$0: bibcodes: @bibs\n" if ($debug);

# create entry for bibcodes to be queried
$query{'bibcode'} = join(';',@bibs);

# force refer style output
$query{'data_type'} = 'PORTABLE';

# now issue the WWW query
print STDERR "$0: retrieving references...";
($result,$status) = &ads'bib_query(%query);      #'
print STDERR "done!\n";

# did an error occurr?
die "$0: ADS query returned the following error status: $status\n" .
    "$0: HTTP error message follows:\n$result"
    if ($status);

print STDERR "$0: retrieved document follows:\n$result\n" if ($debug);

print STDERR "$0: parsing references...";
@references = &ads'parse_bib($result,*score,*title,*author,*pubdate,    #'
                             *journal,*affiliation,*keywords,*origin,
                             *copyright,*abstract,*table,*url,*comment,
                             *object,*item);
print STDERR "done!\n";

# print out results
foreach (@references) {
    &PrintBib($_);
}


sub PrintBib {
    local($b) = @_;
    return unless($b);

    print "Bibliographic Code: $b\n";
    print "Score:              $score{$b}\n"       if ($score{$b});
    print "Title:              $title{$b}\n"       if ($title{$b});
    print "Authors:            $author{$b}\n"      if ($author{$b});
    print "Affiliation:        $affiliation{$b}\n" if ($affiliation{$b});
    print "Journal:            $journal{$b}\n"     if ($journal{$b});
    print "Publication Date:   $pubdate{$b}\n"     if ($pubdate{$b});
    print "Objects:            $object{$b}\n"      if ($object{$b});
    print "Keywords:           $keywords{$b}\n"    if ($keywords{$b});
    print "Copyright:          $copyright{$b}\n"   if ($copyright{$b});
    print "Origin:             $origin{$b}\n"      if ($origin{$b});
    print "Abstract:           $abstract{$b}\n"    if ($abstract{$b});
    print "Table URL:          $table{$b}\n"       if ($table{$b});
    print "Document URL:       $url{$b}\n"         if ($url{$b});
    print "Comment:            $comment{$b}\n"     if ($comment{$b});
    print "Available Items:    $item{$b}\n"        if ($item{$b});
    print "\n";
}


sub Usage {
    print STDERR "$0: @_\n" if (@_);
    print STDERR <<"EOF";
Usage: $0 [-debug] [-db database] [bibcode ...]
If no bibcodes are specified on the command line, they are read from STDIN.
By default, the ADS Astrnomy and Astrophysics databse will be queried.
Option -db can be used to select a different database ("PHY" or "INST");
EOF

    exit(1);
}
