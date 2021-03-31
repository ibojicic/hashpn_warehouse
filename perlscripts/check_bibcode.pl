#!/usr/bin/perl
#
# $Id: verifier.pl,v 1.3 2000/03/28 21:11:50 alberto Exp alberto $
#
# ADS Bibcode verifier script.
# Reads a list of bibcodes given either on the command line or in standard
# input, generates a query to an ADS server which verifies the existence
# of each bibcode, and outputs the list of verified bibcodes to standard
# output.
# 
# Examples:
#       verifier.pl 1999A+A...352...19R 2002abcde.123....1X 1998aspc..145..378E
#
# NOTE: to each bibcode verified by this script corresponds a URL of
#        http://$server/cgi-bin/bib_query?BIBCODE
# this is the most general ADS URL for the document identified by
# BIBCODE and is guaranteed to work in the future.
# Please make sure to use the canonical bibcode returned by the script.
#
# Requires the libwww-perl-5 available from http://www.linpro.no/lwp/
# Written by Alberto Accomazzi <aaccomazzi@cfa.harvard.edu>
#
# $Log: verifier.pl,v $
# Revision 1.3  2000/03/28 21:11:50  alberto
# Modified to return table listing input bibcodes vs.
# canonical bibcodes verified with ADS.
# Now defaults to a POST rather than GET from verifier script.
#
# Revision 1.2  1999/03/23 22:51:21  alberto
# Enforced checks on bibcode syntax.
#
# Revision 1.1  1999/03/23 22:46:19  alberto
# Initial revision
#
#

# customizeable variables:
$server = "adsabs.harvard.edu";
$database = "AST";
$debug = 0;

# this is the list of valid ADS database keys
@databases = qw( AST INST PHY ALL );

# 
($script = $0) =~ s:^.*/::;
$cgi = "/cgi-bin/verify";
$format = "PLAINTEXT";
$method = 'POST';
$version = sprintf("%s/%d.%02d", 
		   q$RCSfile: verifier.pl,v $ =~ /:\s*(\w+)/,
		   q$Revision: 1.3 $ =~ /(\d+)\.(\d+)/);

use LWP::UserAgent;
$ua = new LWP::UserAgent;

# customize user agent to carry this script's signature
$ua->agent("$version " . $ua->agent);

# read options from command line
while ($ARGV[0] =~ /^-\w/) {
    $_ = shift(@ARGV);
    if (/^-database/) {    
        $database = shift(@ARGV);
    } elsif (/^-debug/) {
        $debug++;
    } elsif (/^-get/) {
        $method = 'GET';
    } elsif (/^-url/) {
        $return = 'url';
    } else {
        die "$script: unknown option \"$_\"";
    }
}

# check to see if selected database appears in the list of legal ones
die "$script: unknown database \"$database\""
    unless (grep(/\Q$database\E/,@databases));

# get bibcodes either from command line or standard input
if (@ARGV) {
    @bibcodes = @ARGV;
} else {
    warn "$script: reading bibcodes from stdin...\n";
    @bibcodes = ();
    while (<STDIN>) {
	s/^\s+|\s+$//g;
	next unless /\S/;
	push(@bibcodes,$_);
    }
}

die "$script: no input bibcodes specified!\n" unless (@bibcodes);
warn "$script: read ", scalar(@bibcodes), " input bibcodes\n";
warn "$script: input bibcodes are: ", join(", ",@bibcodes), "\n" if ($debug);

# first check bibcodes validity
@bibcodes = &check_bibcodes(@bibcodes);
die "$script: no valid bibcodes found!\n" unless (@bibcodes);

my $query = &make_query(@bibcodes);
my $url = "http://$server$cgi";
if ($method eq 'GET') { 
    $url .= '?' . $query;
    $query = "";
}

warn "$script: target url is ", $url, "\n",
    "$script: method is ", $method, "\n" if ($debug);
warn "$script: content is ", $query, "\n" if ($debug and $method eq 'POST');
my $req = new HTTP::Request($method,$url);
if ($query) {
    $req->header('Content-Type','application/x-www-form-urlencoded');
    $req->header('Content-Length',length($query));
    $req->content($query);
}

my $res = $ua->request($req);
die "$script: HTTP request failed!\n" unless ($res->is_success);
warn "$script: content is: ", $res->content if ($debug);
@valid = &parse_content($res->content);

my $verified = 0;
while ($bibcode = shift(@bibcodes)) {
    my $valid = shift(@valid);
    print $bibcode, "\t", $valid, "\n";
    $verified++ if ($valid);
}
warn "$script: verified ", $verified, " bibcodes with ADS\n";


# this routine has become much more complicated now that we
# have to account for recognizing canonical bibcodes which may
# be different from input ones;  we should really do this all
# in XML...
sub parse_content {
    my @db_contents = split(/^ADS.*Bibcode Verification/,$_[0]);
    my $content = shift(@db_contents);
    my @valid = ();

    die "$script: error parsing response header from ADS verifier: $content\n"
	if ($content);

    while (defined($content = shift(@db_contents))) {
	my @lines = split(/\n/,$content);
	my $index = 0;
	while (defined($line = shift(@lines))) {
	    if ($line =~ /^YES,\s+(\S{19})/i) {
		$valid[$index++] = $1;
	    } elsif ($line =~ /^NO,\s+(\S{19})/i) {
		$valid[$index++] = "";
	    }
	}
    }
    return @valid;
}

# returns url-encoded query
sub make_query {
    my $query = "db_key=$database&data_type=$format";
    my $bib;

    while (defined($bib = shift)) {
        # escape ampersands in bibcodes since they go in URL
        $bib =~ s/[\&\+]/\%26/g;

        $query .= '&' . $bib;
    }
    return $query;
}

# returns valid bibcodes
sub check_bibcodes {
    my @valid = ();

    while (defined($bib = shift)) {
        unless ($bib =~ /(\d{4}\D\S{13}[A-Z.:])/) {
            warn "$script: invalid bibcode \"$bib\" skipped\n";
            next;
        }
	push(@valid,$bib);
    }
    return @valid;
}
