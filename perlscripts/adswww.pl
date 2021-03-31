#
# $Id: adswww.pl,v 1.15 2001/11/19 22:34:37 alberto Exp alberto $
#
# Library of functions implementing a command-line interface 
# to the ADS WWW abstract service.  To be used in conjunction
# with the libwww-perl library (LWP) available from
# http://www.linpro.no/lwp/
#
# Written by Alberto Accomazzi <aaccomazzi@cfa.harvard.edu>,
#                              http://cfa-www.harvard.edu/~alberto  
#
# Revision 1.13  1998/06/16 20:16:20  alberto
# enabled passing of input parameters to abstract_fields.
#
# Revision 1.12  1997/10/29 16:22:10  alberto
# Updated the abstract query fields to include object
# query selections and options.
#
# Revision 1.11  1997/03/19  20:12:08  alberto
# Added comment field searches, localized library-specific functions
# into low-level routines so that we can prepare for the use of
# libwww-perl5;  implemented iterative URL retrieval upon redirection
# response from server;  added parsing of tagged items returned by
# portable format via a library function.
#
# Revision 1.10  1997/02/16  19:09:17  alberto
# Version 0.6 introduces server selection option,
# updates query parameters to reflect new features in the
# abstract service interface, and improves translations of
# WAIS queries into ads ones.
#
# Revision 1.9  1997/02/14  20:47:32  alberto
# Fixed small typos, minor bugs.
#
# Revision 1.8  1996/04/19  20:43:17  alberto
# Added parsing of object tag in bib_parse,
# fixed evals to work under PERL 5.
#
# Revision 1.7  1996/04/19  20:21:05  alberto
# Added parsing of header information to parse_bib
# (this allows to know how many references were selected),
# set default variables to include email of maintainer,
# general code cleanup here and there.
#
# Revision 1.6  1995/10/24  19:22:56  alberto
# Implemented bib_query function.
#
# Revision 1.5  1995/10/22  05:45:28  alberto
# Updated fields used in version 1 of software,
# implemented parse_bib function.
#
# Revision 1.4  1995/08/22  18:08:55  alberto
# Added html_encapsulate and functions needed to
# implement HTML parsing and URL rewriting.
#
# Revision 1.3  1995/08/15  15:51:12  alberto
# Literal queries are now handled correctly
#
# Revision 1.2  1995/08/15  14:46:05  alberto
# Added all valid fields to the abstract_fields function,
# fixed problem with query creation for author, object and
# keyword fields.
#
# Revision 1.1  1995/08/14  20:54:01  alberto
# Initial revision
#
#

use LWP::UserAgent;

package ads;

CONFIG: {

    # IMPORTANT: please fill in email address of software user/maintainer 
    # so we can keep you updated on library changes
    $EMAIL = 'ibojicic@gmail.com';   # e.g. $EMAIL = 'john@doe.com';

    # Define ADS server to query
    $SERVER = 'adsabs.harvard.edu';	# main server at SAO (USA)
    # $SERVER = 'cdsads.u-strasbg.fr';  # mirror server at CDS (FR)
    # $SERVER = 'ads.nao.ac.jp';        # mirror server at NAO (JP)

    # There is no need to change anything from here on...
    $NAME = 'ADSWWW-lib';
    $VERSION = '0.9';
    $TIMEOUT = 360;
    *DBGOUT = *STDERR;		# debugging output goes to STDERR by default
    *DBGOUT = *STDOUT		# unless we are a CGI script
	if ($ENV{'GATEWAY_INTERFACE'} =~ /^CGI/);
}

# was this package configured?
die "$NAME: please set the variable \$EMAIL in adswww.pl\n"
    unless($EMAIL);

# variables containing the number of references generated by the query;
# they are set only when parsing the results using the parse_bib function
$ref_returned = 0;		# references actually returned
$ref_selected = 0;		# references selected by query
$ref_start    = 0;	        # starting counter for first reference
$ref_headers  = '';		# header information returned from a query
$debug = 0;

my $wwwagent = LWP::UserAgent->new 
    or die "cannot create LWP::UserAgent object: $!";
$wwwagent->timeout($TIMEOUT);
$wwwagent->from($EMAIL);
$wwwagent->agent("$NAME/$VERSION ($EMAIL)");


# Performs a query to the ADS WWW abstract service and returns
# the results (an HTML or plaintext document) and an error status.
# Input parameter is an associative array containing keyword -
# value pairs for the fields used by the ADS abstract service.

sub abstract_query {
    local(%query) = @_;
    local($script) = "abs_connect";

    return (&ads_query($script,%query));
}

# Performs a bibcode query to the ADS WWW abstract service and returns
# the results (an HTML or plaintext document) and an error status.
# Input parameter is an associative array containing keyword -
# value pairs for the fields used by the ADS bib_query.

sub bib_query {
    local(%query) = @_;
    local($script) = "bib_query";

    return (&ads_query($script,%query));
}

# Performs a data query to the ADS WWW abstract service and returns
# the results (an HTML or plaintext document) and an error status.
# Input parameter is an associative array containing keyword -
# value pairs for the fields used by the ADS data_query.

sub data_query {
    local(%query) = @_;
    local($script) = "data_query";

    return (&ads_query($script,%query));
}


# Does a generic query to the ADS abstract server
# (used to be a POST method, was switched to a GET since version 0.8.1)

sub ads_query {
    my $url = "http://$SERVER/cgi-bin/nph-" . shift(@_);
    my %query = @_;
    my $method = 'GET';
    my $content = "";
    my $result = 0;
    my $key;

    if ($debug) {
	foreach $key (sort keys(%query)) {
	    printf (DBGOUT "%-10.10s = %s\n", $key, $query{$key});
	}
	print DBGOUT "\n";
    }

    return ("",1) unless (%query);

    # override URL if one is specified
    $url = delete($query{'url'}) if ($query{'url'});
	
    # convert ADS query to a WWW encoded request
    $url .= '?';
    foreach $key (sort keys(%query)) {
        $url .= "$key=" . &url_escape($query{$key}) . '&'
                if ($query{$key});
    }
    chop($url);

    print DBGOUT "URL: $url\n" if ($debug);

    my $req = new HTTP::Request($method,$url) or
	die "cannot create HTTP::Request object: $!";
    my $res = $wwwagent->request($req) or
	die "cannot get request from www agent: $!";

    if ($res->is_success) {
	print "Query contents: ", $res->content, "\n" if ($debug);
	return ($res->content,0);
    } else {
	print "No output content retrieved!\n";
	return ("",1);
    }
}


# Parses the bibliographies generated by a query to the ADS abstract
# system requesting output in portable tagged format (Refer-like),
# and returns a list of bibcodes found.  Also, sets appropriate key-
# value pairs for the associative arrays passed to the subroutine:
#    %score         Document score (returned only by an abstract query)
#    %title         Title of paper
#    %author        Author list
#    %affiliation   Author affiliation
#    %journal       Journal name
#    %pubdate       Publication date
#    %keyword       Keyword list
#    %origin        Origin of abstract
#    %abstract      Abstract of bibliographic reference
#    %table         URL for elecronic data table, if available
#    %docurl        URL for electronic version of document, if available
#    %comment       Comment field
#    %object        (Astronomical) Objects discussed in reference
#    %item          Available items for this reference
#
# Call using the syntax:
#    1) after retrieving a list of abstracts from bib_query CGI script:
#       &parse_bib($text,*score,*title,*author,*pubdate,*journal,*affiliation,
#                  *keyword,*origin,*copyright,*abstract,*table,*docurl,
#                  *comment,*object,*item);
#    2) after retrieving a list of references from abs_connect CGI script:
#       &parse_bib($text,*score,*title,*author,*pubdate);
#
# The untagged headers returned by the CGI scripts are stored in the variable
# $adswww'ref_headers.  The following variables are also set:
#    $adswww::ref_selected  number of references selected by the query
#    $adswww::ref_returned  number of references returned by the script
#    $adswww::ref_start     starting number of the first reference returned   
# To return additional references in a loop, just increase the following
# variable before resubmitting the query:
#    $query{'start_nr'} += $adswww::ref_returned;

sub parse_bib {
    return () if (! @_);
    local($document) = shift(@_);
    local($_);
    local(*score,*title,*author,*pubdate,*journal,*affiliation,*keyword,
	  *origin,*copyright,*abstract,*table,*docurl,*comment,*object,
	  *item) = @_;
    local(%field) = ('S', 'score',
		     'T', 'title',
		     'A', 'author',
		     'F', 'affiliation',
		     'J', 'journal',
		     'D', 'pubdate',
		     'K', 'keyword',
		     'G', 'origin',
		     'C', 'copyright',
                     'B', 'abstract',
		     'E', 'table',
		     'U', 'docurl',
		     'X', 'comment',
		     'O', 'object',
		     'I', 'item',
		     );
    local(@bibcodes,$bib,$tag);
    local($tags) = join('',keys(%field));

    # undefine these variables so that we can distinguish
    # between the case where no references were returned
    # and an error occurred
    undef($ref_returned);
    undef($ref_selected);
    undef($ref_start);

    foreach (split(/\n+/,$document)) {
	# skip empty lines
	next if (/^\s*$/);   
	s/\s+$//;
	
	if (s/^%R\s+(\S+)//) {
	    # bibcode
	    $bib = "$1";
	    push(@bibcodes,$bib);
	} elsif (s/^%([$tags])\s+//) {
	    # new tag
	    $tag = $1;
	    eval ("\$$field{$tag}"."{\$bib} = \$_");
	} elsif (s/^%(\w)\s+//) {
	    # unrecognized tag
	    print DBGOUT "parse_bib: unrecognized tag \"$1\"\n"
		if ($debug);
	} elsif ($tag && $field{$tag}) {
	    # must be continuation line; append to last tag
	    s/^\s*/ /;
	    eval ("\$$field{$tag}"."{\$bib} .= \$_");
	} else {
	    # header information; parse whatever looks parseable
	    # and save in variables global to this package
	    $ref_headers .= "$_\n";
	    if (/^retrieve\D+(\d+)\D+(\d+)\D+(\d+)/i) {
		$ref_returned = $1;
		$ref_start = $2;
		$ref_selected = $3;
	    }
	}
    }

    return(@bibcodes);
}


# Parses the available items out of the entry string returned by 
# an %I tag and returns an associative array containing the item
# tags as keys and the item descriptions as values

sub parse_items {
    local($string) = @_;
    local(%item);
    
    $string =~ s/^\s+|\s+$//g;
    foreach (split(/\s*\;\s*/,$string)) {
	next unless (/^(\w+):\s*(.*)$/);
	$item{$1} = "$2";
    }
    return (%item);
}


# Parses a (possibly fielded) WAIS query,
# throws away items which cannot be translated into an ADS
# fielded query (because they're not implemented),
# and translates the rest of the query into keyword-value
# pairs as used by the ADS abstract server.

sub wais2ads {
    return () unless @_;
    local($_) = ' ' . shift(@_) . ' ';
    local($global,$text,$h,$t);
    local(%waisfield) = ();
    local(%adsfield) = &abstract_fields();
    local(%adsfieldsep) = &abstract_fields_sep();
    local(%wais2ads_f) = &wais2ads_fields(@_);
    local(%wais2ads_a) = &wais2ads_aux(@_);

    # set flags for boolean query as default
    foreach $v (values(%wais2ads_a)) {
	$adsfield{$v . '_logic'} = 'BOOL';
    }

    # translate everything to lower case (searches are case-insensitive)
    tr/A-Z/a-z/;

    # get rid of some special characters so we can use it to play 
    # some tricks later on (they're not searcheable anyway)
    s/[^\w\(\)\'\"\=\,\-]/ /g;
    s/\s+/ /g;
    s/^\s*|\s*$/ /g;

    # translate literals expressions into single strings 
    # with "~" as a word separator
    while (/\'([^\']*)\'/) {
	$h = $`;
	$t = $';
	($_ = $1) =~ s/[\s\=]+/~/g;
	$_ = "$h:$_:$t";
    }
    while (/\"([^\"]*)\"/) {
	$h = $`;
	$t = $';
	($_ = $1) =~ s/[\s\=]+/~/g;
	$_ = "$h:$_:$t";
    }		    
    s/:/'/g;                                                               #'

    # parenthesis group terms to be searched in a field or in a
    # boolean expression;  we treat them as quotes, but no
    # = sign translation is done
    while (/\(([^\(\)]*)\)/) {
	$h = $`;
	$t = $';
	($_ = $1) =~ s/[\s]+/_/g;
	$_ = $h . $_ . $t;
    }

    # delete 'or' booleans (not needed)
    s/\s+or\s+/ /g;
    
    # translate boolean 'not' between fields into 'and'
    while (s/(^|\s+)not\s+(\w+)=([\w~\-\,\']+)/ and $2=not_$3/) {
	$adsfield{$wais2ads_a{$2} . '_req'} = 'YES' if ($wais2ads_a{$2});
    }

    # remove multiple ands
    1 while (s/\s+and\s+and\s+/ and /);

    # recognize and count instances of the boolean operator "and" 
    # beween fields (as opposed to "and" between words within a field)
    while (s/(\w+)=([\w~\-\,\']+)\s+and\s+(\w+)=([\w~\-\,\']+)/$1=$2 $3=$4/) {
	$adsfield{$wais2ads_a{$1} . '_req'} = 'YES' if ($wais2ads_a{$1});
	$adsfield{$wais2ads_a{$3} . '_req'} = 'YES' if ($wais2ads_a{$3});
    }

    # remove leading and trailing'and'
    s/^\s*and\s+/ /;	      
    s/\s+and\s*$/ /;

    # now split query into fields, if any are specified
    while (s/\s(\w+)=([\w~\-\,\']+)\s/ /) {
	if (grep(/^$1$/,keys(%wais2ads_a))) {
	    $waisfield{$1} .= "$2 ";
	} else {
	    $global .= "$1 $2 ";
	}
    }

    # whatever is left in the query has to be searched "globally" (see below)
    $global .= $_;

    # we're going to do a "global" search by searching terms in the
    # abstract field and in the author field
    # one may want to turn weights off for either or both these fields
    # to avoid generating scores that seem confusing to the user
    $waisfield{'ab'} .= $global;
    $waisfield{'au'} .= $global;

    # now create the ADS fielded queries
    foreach (keys(%wais2ads_a)) {

	$text = $waisfield{$_};
	$text =~ s/_/ /g;

	# translate WAIS use of "not" into ADS's syntax
	$text =~ s/\snot\s/ and not /g;

	# clean up text
	$text =~ s/\s+/ /g;
	$text =~ s/^\s+|\s+$//g;

	# add proper field separators
	$text =~ s/\s/$adsfieldsep{$wais2ads_f{$_}}/g;
	$text =~ s/\~/ /g;

	# finally, assign text to proper ADS search field
	$adsfield{$wais2ads_f{$_}} = $text;

    }
    
    return %adsfield;
}

# Escapes URL string 

sub url_escape {
    my $string = shift;
    my $pattern = '[\x00-\x2B/;<=>?\x7F-\xFF]';

    $string =~ s/($pattern)/sprintf("%%%02lx",unpack('C',$1))/ge;

    return $string;
}

# Returns URL contents

# Strips off header markup from an HTML page and translates
# relative URLs embedded in the page into absolute ones so that the
# resulting document can be safely encapsulated into another HTML page.
# Input url, if given, must be an absolute base url for current document.

sub html_encapsulate {
    local($page,$url) = @_;
    local($head,$base,$type,$host,$port,$path,@hrefs);
    #local($*) = 1;		# do multi-line matching

    # first strip off anything outside of HTML <BODY>
    $page =~ s#</BODY>(.|\n)*##i;
    $page =~ s#((.|\n)*)<BODY>##i; 
    $head = "$1";

    print DBGOUT "Head: $head\n" if ($debug);

    # look for <BASE> URL
    ($head =~ m#<BASE\s+HREF\s*=\s*\"?([^\">]*)\"?[^>]*>#i) && ($base = "$1");
    #local($*) = 0;		# reset matching
    $base = $url unless($base);
    return($page) unless($base);

    ($type,$host,$port,$path) = &urlparse($base);
    print DBGOUT "Base url: $base\nProtocol: $type\nHost: $host\n",
        "Port: $port\nPath: $path\n" if ($debug);

    @hrefs = &urlabs("$type://$host:$port",$path,'HREF',split(/<A/i,$page));
    $page = join('<A',@hrefs);
    @hrefs = &urlabs("$type://$host:$port",$path,'SRC',split(/<IMG/i,$page));
    $page = join('<IMG',@hrefs);
    @hrefs = &urlabs("$type://$host:$port",$path,'ACTION',split(/<FORM/i,$page));
    $page = join('<FORM',@hrefs);
}


# Convert relative URLs to absolute ones.
# Input is HTML document fragments starting with a tag="url" item.

sub urlabs {
    local($root,$path,$tag,@hrefs) = @_;
    local($n);
    #local($*) = 1;		# do multi-line matching

    for ($n = $[ + 1; $n <= $#hrefs; $n++) {
	# absolute urls are fine
	($hrefs[$n] =~ m|$tag\s*=\s*\"?\w+:|i) && next;
	# internal refs are fine
	($hrefs[$n] =~ m|$tag\s*=\s*\"?#|i) && next;
        # urls relative from root
	($hrefs[$n] =~ s|$tag\s*=\s*\"?/([^\">]*)\"?|$tag="$root/$1"|i) &&next;
	# urls relative from path 
	$hrefs[$n] =~ s|$tag\s*=\s*\"?([^/\"][^\">]*)\"?|$tag="$root$path$1"|i;
    }
    #local($*) = 0;

    @hrefs;
}


# Parses a full-qualified URL, return its segments

sub urlparse {
    local($url) = @_;
    local($type,$host,$port,$path,$request);
    local(%defport) = ('http', 80, 'gopher', 70);

    # both type and ":" may be missing; could have multiple ":" ...
    $url =~ m|^(\w*):*//(.*)| || return(undef,undef,undef,undef,undef);
    $type = $1;
    $type = "http" unless($type);
    $host = $2;
    $port = $defport{$type};
    $request = "/"; # default
    ($host =~ s|^([^/]+)(/.*)$|$1|) && ($request = $2);
    ($host =~ s/:(\d+)$//) && ($port = $1);
    ($path = $request) =~ s|[^/]*$||;
    ($type,$host,$port,$path,$request);
}


# These are the search fields as defined by the ADS WWW/CGI interface.
# They consist of 5 basic search fields, 28 fields used to control the
# query logic and weighting, and 8 fields used to define search parameters
# such as date range, journal set, and database.
# Here we set some defaults for all of them.

sub abstract_fields {
    local(%in) = @_;
    local($key,$value);
    local(%a);

    %a = ('author',      '',	# Author name(s)
	  'comment',	 '',	# SIMBAD comment
	  'object',      '',	# Object name(s)
	  'keyword',     '',	# NASA/STI Keywords
	  'title',       '',	# Words in the title
	  'text',        '',	# Words in the abstract + title

	  'aut_wt',      '1.0',	# default weight for author field 
	  'com_wt',      '1.0',	# default weight for comment field 
	  'obj_wt',      '1.0',	# default weight for object field 
	  'kwd_wt',      '1.0',	# default weight for keyword field 
	  'ttl_wt',      '0.3',	# default weight for title field 
	  'txt_wt',      '3.0',	# default weight for text field 
	  'aut_wgt',     'YES',	# Use author weight for scoring
	  'com_wgt',     'YES',	# Use comment weight for scoring
	  'kwd_wgt',     'YES',	# Use keyword weight for scoring
	  'obj_wgt',     'YES',	# Use object weight for scoring
	  'ttl_wgt',     'YES',	# Use title weight for scoring
	  'txt_wgt',     'YES',	# Use text weight for scoring
	  'aut_req',     '',	# author field required for selection
	  'com_req',     '',	# comment field required for selection
	  'kwd_req',     '',	# keyword field required for selection
	  'obj_req',     '',	# object field required for selection
	  'ttl_req',     '',	# title field required for selection
	  'txt_req',     '',	# text field required for selection
	  'aut_sco',     '',	# Use author for weighted scoring
	  'com_sco',     'WEI',	# Use comment for weighted scoring
	  'kwd_sco',     'WEI',	# Use keyword for weighted scoring
	  'obj_sco',     'WEI',	# Use object for weighted scoring
	  'ttl_sco',     'WEI',	# Use title for weighted scoring
	  'txt_sco',     'WEI',	# Use text for weighted scoring
	  'aut_syn',     'YES',	# use synonym lookup for author field
	  'com_syn',     'YES',	# use synonym lookup for comment field
	  'kwd_syn',     '',	# use synonym lookup for keyword field
	  'obj_syn',     '',	# use synonym lookup for object field
	  'ttl_syn',     'YES',	# use synonym lookup for title field
	  'txt_syn',     'YES',	# use synonym lookup for text field
	  'aut_logic',   'OR',	# aut. query logic ('OR','AND','SIMPLE','BOOL')
	  'com_logic',   'OR',	# comment query logic
	  'kwd_logic',   'OR',	# keyword query logic
	  'obj_logic',   'OR',	# object query logic
	  'ttl_logic',   'OR',	# title query logic
	  'txt_logic',   'OR',	# text query logic
	  'aut_xct',     'NO',  # exact author query
	  
	  'start_mon',   '',	# return papers published after this month
	  'start_year',  '',	# return papers published after this year
	  'end_mon',     '',	# return papers published before this month
	  'end_year',    '',	# return papers published before this year
	  'start_entry_day','',	# return papers entered after this day
	  'start_entry_mon','',	# return papers entered after this month
	  'start_entry_year','',# return papers entered after this year
	  'min_score',	 '',	# return papers with score higher than this
	  'jou_pick',	 'ALL',	# return papers from these journals:
				#    "ALL"  for all publications
				#    "NO"   for all refereed publications
				#    "YES"  for selected publications
	                        #    "EXCL" for non-refereed publications
	  'ref_stems',   '',	# selected journal stems
	  'query_type',	 'PAPERS', # return type:
				#    "PAPERS"  for all publications
				#    "REFS"    for all references
				#    "CITES"   for all citations

	  'iau_query',   '',    # query IAU index (local)
	  'lpi_query',   '',    # query LPI index (local)
	  'ned_query',   'YES', # query NED (remote)
	  'sim_query',   'YES', # query SIMBAD (remote)

	  'data_and',    'ALL',	# return references with:
				#    "ALL"  any entry
				#    "NO"   at least one of the following
				#    "YES"  all of the following
	  'nasa_abs',    '',	# NASA abstract
	  'orig_abs',    '',	# author abstract
	  'article',	 '',	# full text article
	  'article_link','',	# electronic article (HTML format)
	  'pds_link',	 '',	# links to PDS
	  'toc_link',    '',	# table of content links
	  'ref_link',    '',	# reference links
	  'citation_link','',	# citation links
	  'data_link',   '',	# data links
	  'simb_obj',    'YES',	# SIMBAD objects
	  'ned_obj',     'YES',	# NED objects
	  'mail_link',   '',	# mail links
	  'lib_link',    '',	# library links
	  'aut_note',    '',	# author notes

	  'group_and',   'ALL',	# return references in:
				#    "ALL"  any group
				#    "NO"   at least one of the following
				#    "YES"  all of the following
	  'group_sel',	 '',	# selected group name

	  'version',     '1',	# version for this set of input variables
	  'start_nr',    '1',   # return starting from this reference number
	  'nr_to_return','200', # maximum number of references to return
	  'select_start','1',	# select starting from this reference number
	  'select_nr',	 '500',	# maximum number of references to select
	  'db_key',      'AST', # name of the database to search:
	                        #    "AST"  for Astronomy and Astrophysics,
                                #    "INST" for Space instrumentation,
                                #    "PHY"  for Physics and Geophysics
	  'data_type',   '',	# data type to be returned:
				#    ""          for HTML,
				#    "PORTABLE"  for tagged refer style,
				#    "PLAINTEXT" for plain text,
				#    "BIBTEX"    for BibTeX format
	  );

    while (($key,$value) = each(%in)) {
	$a{$key} = $value;
    }
    
    return %a;
}

# Keys of the associative array defined above

sub abstract_fields_keys {
    local ($a);
    @a = ('author',		# Author name(s)
	  'comment',		# SIMBAD comment field
	  'object',		# Object name(s)
	  'keyword',		# NASA/STI Keywords
	  'title',		# Words in the title
	  'text',		# Words in the abstract + title

	  'aut_wt',		# default weight for author field 
	  'com_wt',		# default weight for comment field 
	  'obj_wt',		# default weight for object field 
	  'kwd_wt',		# default weight for keyword field 
	  'ttl_wt',		# default weight for title field 
	  'txt_wt',		# default weight for text field 
	  'aut_wgt',		# Use author weight for scoring
	  'com_wgt',		# Use comment weight for scoring
	  'kwd_wgt',		# Use keyword weight for scoring
	  'obj_wgt',		# Use object weight for scoring
	  'ttl_wgt',		# Use title weight for scoring
	  'txt_wgt',		# Use text weight for scoring
	  'aut_req',		# author field required for selection
	  'com_req',		# obj field required for selection
	  'kwd_req',		# keyword field required for selection
	  'obj_req',		# obj field required for selection
	  'ttl_req',		# title field required for selection
	  'txt_req',		# text field required for selection
	  'aut_sco',		# Use author for weighted scoring
	  'com_sco',		# Use comment for weighted scoring
	  'kwd_sco',		# Use keyword for weighted scoring
	  'obj_sco',		# Use object for weighted scoring
	  'ttl_sco',		# Use title for weighted scoring
	  'txt_sco',		# Use text for weighted scoring
	  'aut_syn',		# use synonym lookup for author field
	  'com_syn',		# use synonym lookup for comment field
	  'kwd_syn',		# use synonym lookup for keyword field
	  'obj_syn',		# use synonym lookup for object field
	  'ttl_syn',		# use synonym lookup for title field
	  'txt_syn',		# use synonym lookup for text field
	  'aut_logic',   	# author query logic
	  'com_logic',   	# keyword query logic
	  'kwd_logic',   	# keyword query logic
	  'obj_logic',   	# object query logic
	  'ttl_logic',   	# title query logic
	  'txt_logic',   	# text query logic
	  'aut_xct',		# exact author query

	  'start_mon',   	# return papers published after this month
	  'start_year',  	# return papers published after this year
	  'end_mon',     	# return papers published before this month
	  'end_year',    	# return papers published before this year
	  'start_entry_day',	# return papers entered after this day
	  'start_entry_mon',	# return papers entered after this month
	  'start_entry_year',	# return papers entered after this year
	  'min_score',	 	# return papers with score higher than this
	  'jou_pick',	 	# return papers from this journal set
	  'ref_stems',   	# selected journal stems
	  'query_type',		# return type (papers, citations, references)

	  'iau_query',          # query IAU index (local)
	  'lpi_query',          # query LPI index (local)
	  'ned_query',          # query NED (remote)
	  'sim_query',          # query SIMBAD (remote)

	  'data_and',    	# return references with property set
	  'nasa_abs',    	# NASA abstract
	  'orig_abs',    	# author abstract
	  'article',	 	# full text article
	  'article_link',	# electronic article (HTML format)
	  'pds_link',	 	# PDS data links
	  'toc_link',    	# table of content links
	  'ref_link',     	# reference links
	  'citation_link',	# citation links
	  'data_link',   	# data links
	  'simb_obj',    	# SIMBAD objects
	  'ned_obj',    	# NED objects
	  'mail_link',   	# mail links
	  'lib_link',   	# library links
	  'aut_note',    	# author notes
	  'group_and',   	# return references in:
	  'group_sel',	 	# selected group name

	  'version',     	# version for this set of input variables
	  'start_nr',           # return starting from this reference number
	  'nr_to_return',	# maximum number of references to return
	  'select_start',	# select starting from this reference number
	  'select_nr',		# maximum number of references to select
	  'db_key',		# name of the database to search
	  'data_type',		# data type to return
     );
}

# Word separators for the ADS search fields
sub abstract_fields_sep {
    local(%a);
    
    %a = ('author',  '; ',
	  'keyword', '; ',
	  'title',   ' ',
	  'text',    ' ',
	  'object',  '; ',
	  'comment', ' '
	  );
}

# stems used in auxiliary fields for abstract searches
sub abstract_fields_aux {
    local(%a);

    %a = ('author',  'aut',
	  'keyword', 'kwd',
	  'title',   'ttl',
	  'text',    'txt',
	  'object',  'obj',
	  'comment', 'com',
	  );
}

# Mapping of WAIS fields to ADS search fields
sub wais2ads_fields {
    # default array correlating the WAIS fields and the ADS fields
    # these mappings are somewhat arbitrary; there is no standard
    local(%a) = ('au', 'author',
		 'kw', 'keyword',
		 'ti', 'title',
		 'ab', 'text',
		 'ob', 'object',
		 'co', 'comment',
		 );

    if (@_) {
	return(@_);
    } else {
	return(%a);
    }
}

# Mapping of WAIS fields to ADS auxiliary search field stems
sub wais2ads_aux {
    local(%a);
    local(%w2a) = &wais2ads_fields(@_);
    local(%aa) = &abstract_fields_aux();
    local($_);

    foreach (keys(%w2a)) {
	$a{$_} = $aa{$w2a{$_}};
    }

    return(%a);
}


1;
