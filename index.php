<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING

// **************************************************************
// ******************** HEADER **********************************
// check if logged in
include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");
include_once("adminpro/adminpro_class.php");
$prot=new protect();
if ($prot->showPage) header("Location: dbMainPage.php"); // if already logged in redirect to the main page
//$curUser = $prot->getUser(); //name of the logged user
//$isAdmin = $prot->userStatus(); //user priviledges 1 if admin
$pageName = "frontpage"; //info for the header
include("includes/header.php");
//$includescripts = includeJavaScript($showmeny, $mydbConfig["javascripts"]); //include extra javascripts
// ****************** END HEADER ********************************
// **************************************************************
$referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : FALSE;
$linkurl = checkreferers($referer,WEBSITE_URL);

function checkreferers($referer,$baseurl)
{
    return "index.php";
	if($referer) {
		$accepted = array("objectInfoPage.php");
		foreach ($accepted as $acc) if (stripos($referer, $baseurl . $acc) !== False) return $referer;	
	}
	return $baseurl."dbMainPage.php";
}

?>
<section id='main_login'>
	<article class='module width_3_quarter_centered helixback'>
		<?php if ($prot->loginMessage) echo "<h4 class='alert_warning'> ALERT: ".$prot->loginMessage." </h4>"; ?>
                <?php if ($prot->errorMsg) echo "<h4 class='alert_error'> ".$prot->errorMsg." </h4>"; ?>

		<?php include("sectionfrontpage/login.php"); ?>

	</article> <!-- end of <article class="module width_full"> -->

	<article class='module width_3_quarter_centered'>
		<div class="module_content">
                    <h1>
			<p>
                            Welcome to the The University of Hong Kong/Australian Astronomical Observatory/Strasbourg 
                            Observatory H-alpha Planetary Nebula (HASH PN) database. HASH PN database is an interactive catalogue of 
                            imaging, spectroscopic and other observational data for Galactic PNe available to researchers and general public.
                        </p>

                        <p>
                            The access to the database is password protected - please register first via the 
                            <a href="http://202.189.117.101:8999/gpne/regPage.php?action=newreg">registration form</a>.
                        </p>

                        <p>
                            If you use this resource in a publication, please cite this paper: 
                            <a href="http://adsabs.harvard.edu/abs/2016arXiv160307042P">Parker, Boji&#269i&#263 & Frew 2016</a> 
                            and include the following acknowledgement:
                            "This research has made use of the HASH PN database at hashpn.space”.
                        </p>

                        <p>
                            HASH PN database is produced and maintained by Dr. Ivan Boji&#269i&#263, Prof. Quentin Parker and Dr. David Frew at
                            The University of Hong Kong. The online interface is best experienced on the latest version of Chrome or 
                            Firefox (IE is not supported). Please note that the current version of the HASH PN database is still in beta 
                            testing. If you have any troubles in registering or using the interface please <a href="mailto:hashpn.db@gmail.com">Contact us</a>.
                        </p>
                    </h1>
		</div>
		<!--</header> ??? obrisi -->
	</article> <!-- end of <article class="module width_full"> -->

</section>

<?php
// ********* FOOTER ********************
include("includes/bottom.php");
//} //end of adminpro
?>
