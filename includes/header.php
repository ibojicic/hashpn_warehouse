<!DOCTYPE html> <!-- PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">-->
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
        <meta http-equiv="Cache-Control" content="cache-control: private, max-age=0, no-cache" />

        <meta name="description" content="" />
        <meta name="generator" content="HTML-Kit" />
        <title><?php echo SITETITLE; ?></title>

        <link rel="stylesheet" type="text/css" href="css/login.css" />
        <link rel="stylesheet" type="text/css" href="css/galleriffic.css" />
        <link rel="stylesheet" type="text/css" href="css/datatables_custom.css" />
        <link rel="stylesheet" type="text/css" href="css/newlayout.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/paginator.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.8.22.custom.css" />
        <link rel="stylesheet" type="text/css" href="css/print.css"  media="print" />
        <link rel="stylesheet" type="text/css" href="css/flotplots.css"  media="screen" />
        <!--<link rel="stylesheet" href="css/whhg.css">-->
        <link rel="stylesheet" type="text/css" href="css/wallgallery.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/viewbar.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/mainpage.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/infobox.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/inputboxes.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/alerts.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/objectinfo.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="css/3dview.css" media="screen" />


        <script type="text/javascript" src="javascript/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="javascript/jquery.validate.js"></script>
        <script type="text/javascript" src="javascript/jquery-ui-1.8.22.custom.min.js"></script>
        <script type='text/javascript' src='javascript/jquery.equalHeight.js'></script>
        <?php if (isset($includescripts)) echo $includescripts ?>
        <script type="text/javascript" src='javascript/petfunctions.js'></script>

        <link type="text/css" rel="stylesheet" href="css/jquery.dropdown.min.css" />
        <script type="text/javascript" src="javascript/jquery.dropdown.min.js"></script>



    </head>
    <body>
        <?php
        if ($pageName != "frontpage" and $pageName != "register") {
            echo "<header id='header'>
                    <hgroup>
                        <h1 class='site_title'><a href='dbMainPage.php'>" . SITETITLE . " /</a></h1>
                        <h2 class='section_title'>" .
                            $mydbConfig['pageDisplay'][$pageName] . $mydbConfig['headervars']['position'] .
                        "</h2>
                        <div id='logouser'>
                            <div class='logo' id='logo'>
                                <a href='".$mydbConfig["servervars"][$server]["linklogo"]."' target='_blank'>
                                    <img src='images/".$mydbConfig["servervars"][$server]["imagelogo"]."' height ='35'></img></a>
                            </div>
                            <div class='user'>
                                <h4> $curUser </h4>
                                <a href='index.php?action=logout'><img src='images/icon_signout.png'></a>
                                <a href='dbUserPref.php'><img src='images/icn_settings.png'></a>
                                <!--<a href='dbStatsPage.php' target='_blank'><img src='images/icon_stats.png'></a>-->
                            </div>
                        </div>
                    </hgroup>
                </header>";
        }
        ?>






