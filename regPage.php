<?php
// TODO CHECK DOUBLE QUOTES -- FINISHED CHECKING


// **************************************************************
// ******************** HEADER **********************************
// check if logged in
include_once ("includes/pndb_config.php");
include_once ("includes/functions.php");
include_once("adminpro/adminpro_class.php");
$prot=new protect(False,False,True);
//if ($prot->showPage) header("Location: dbMainPage.php"); // if already logged in redirect to the main page
//$curUser = $prot->getUser(); //name of the logged user
//$isAdmin = $prot->userStatus(); //user priviledges 1 if admin
$pageName = "register"; //info for the header
$includescripts = includeJavaScript($pageName, $mydbConfig["javascripts"]); //include extra javascript
include("includes/header.php");
// ****************** END HEADER ********************************
// **************************************************************
$referer = $_SERVER["HTTP_REFERER"];
$linkurl = checkreferers($referer,WEBSITE_URL);


function checkreferers($referer,$baseurl)
{
	$accepted = array("objectInfoPage.php");
	foreach ($accepted as $acc) if (stripos($referer, $baseurl . $acc) !== False) return $referer;
	return $baseurl."dbMainPage.php";
}

?>
<section id='main_login'>
	<article class='module width_3_quarter_centered helixbackregister'>
		<?php if ($prot->loginMessage) echo "<h4 class='alert_warning'> ALERT </h4>"; ?>
		
		<?php include("sectionfrontpage/register.php"); ?>

	</article> <!-- end of <article class="module width_full"> -->
        



</section>

<?php
// ********* FOOTER ********************
include("includes/bottom.php");
//} //end of adminpro
?>
