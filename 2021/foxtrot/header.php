<?php 
require_once("include/config.php");
require_once(DIR_FS."islogin.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<title>Foxtrot</title>

<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet" />
<!-- font-awesome -->
<link href="css/font-awesome.min.css" rel="stylesheet" />
<link href="css/style.css?1.0" rel="stylesheet" />
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link href="<?php echo SITE_CSS; ?>bootstrap-datepicker.min.css" rel="stylesheet"/>
<link href="<?php echo SITE_CSS; ?>jquery.multiselect.css" type="text/css" rel="stylesheet"/>
<!-- search with selection box-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.5.1/chosen.min.css"/>
<!-- <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" /> -->
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> -->
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="<?php echo SITE_JS; ?>bootstrap.min.js"></script>

<!-- <script src="js/jquery.min.js"></script> -->
<script src="<?php echo SITE_JS; ?>bootstrap-datepicker.min.js"></script>
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
<script src="<?php echo SITE_JS; ?>/jquery.priceformat.min.js"></script>
<!-- Datatables-->
<link rel="stylesheet" href="<?php echo SITE_PLUGINS; ?>datatables/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="<?php echo SITE_PLUGINS; ?>datatables/buttons.dataTables.min.css" />
<script src="<?php echo SITE_PLUGINS; ?>datatables/jquery.dataTables.js"></script>
<script type="text/javascript" src="<?php echo SITE_PLUGINS; ?>datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo SITE_PLUGINS; ?>datatables/dataTables.buttons.min.js"></script>
<script src="<?php echo SITE_PLUGINS; ?>datatables/jszip.min.js"></script>
<script src="<?php echo SITE_PLUGINS; ?>datatables/pdfmake.min.js"></script>
<script src="<?php echo SITE_PLUGINS; ?>datatables/vfs_fonts.js"></script>        
<script src="<?php echo SITE_PLUGINS; ?>datatables/buttons.html5.min.js"></script>  
<script src="<?php echo SITE_PLUGINS; ?>datatables/buttons.colVis.min.js"></script>    
<!-- 4/14/22 Checkboxes -->
<link rel="stylesheet" type="text/css" href="<?php echo SITE_PLUGINS; ?>datatables/extensions/Select/css/select.dataTables.css">
<!-- // Move the ajax file to plugins folder if it works // -->
<!-- <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script> -->
<script src="<?php echo SITE_PLUGINS; ?>datatables/extensions/Validate/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript" src="<?php echo SITE_PLUGINS; ?>datatables/extensions/Select/js/dataTables.select.js"></script>
     
<script src="<?php echo SITE_JS; ?>validator.js"></script>
<script src="<?php echo SITE_JS; ?>multipleselection.js"></script>
<script src="<?php echo SITE_JS; ?>jquery.multiselect.js"></script>
<script src="<?php echo SITE_JS; ?>custom.js"></script>
<script src="<?php echo SITE_PLUGINS; ?>bootbox/bootbox.min.js"></script>
<script src="<?php echo SITE_PLUGINS; ?>masked-input/jquery.maskedinput.min.js"></script>

<style>
.dropdown-submenu {
    position: relative;
}
.error{
	border: 1px solid red!important;
}
body .container{
	 width: 1300px;
}
.dropdown-submenu>.dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -6px;
    margin-left: -1px;
    -webkit-border-radius: 0 6px 6px 6px;
    -moz-border-radius: 0 6px 6px;
    border-radius: 0 6px 6px 6px;
}

/* .graphboxcontent{ */
    /*padding: 10px 0;*/
/* } */

.dropdown-submenu:hover>.dropdown-menu {
    display: block;
}

.dropdown-submenu>a:after {
    display: block;
    content: " ";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: #ccc;
    margin-top: 5px;
    margin-right: -10px;
}

.dropdown-submenu:hover>a:after {
    border-left-color: #fff;
}

.dropdown-submenu.pull-left {
    float: none;
}

.dropdown-submenu.pull-left>.dropdown-menu {
    left: -100%;
    margin-left: 10px;
    -webkit-border-radius: 6px 0 6px 6px;
    -moz-border-radius: 6px 0 6px 6px;
    border-radius: 6px 0 6px 6px;
}

/* temp changes - 16-10-2018*/
.sitelogo a{padding: 0 0 0 15px;}
.headermenu .navbar-inverse ul.nav {
    width: 86%;
    float: right;
}
.headermenu {
    padding: 2px 0;
}
.userinfo {
    margin: -40px 0px 0 -560px;/*margin: -40px 0px 0 220px;/* 89 when use menu icon*/
}
.contentmain{
    padding: 75px 0 90px;
}
</style>
</head>
<body>
<?php 
$instance_header = new header_class();
?>
<header style="<?php /*if(isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add_new' || $_GET['action'] == 'add_sponsor' || $_GET['action'] == 'edit_sponsor' || $_GET['action'] == 'add_product' || $_GET['action'] == 'edit_product' || $_GET['action'] == 'add' || $_GET['action'] == 'edit_transaction' || $_GET['action'] == 'add_batches' || $_GET['action'] == 'edit_batches')){ echo 'display : none !important';}*/ ?> ">
<div class="sectionwrapper headerwrapper">
  <div class="container">
    <!--<div class="headertop">
      <div class="sitelogo"><a href="home.php" title="Foxtrot"><img src="images/sitelogo.png" alt="Foxtrot" height="53.68"/></a></div>
      <div class="headertopright">
		<a href="#" class="userinfo"><img src="images/Help-desk.png" alt="Chat/Help" title="Chat/Help" height="30" width="50" style="padding-bottom: 5px;" /></a>
                
		<div class="userlogin">
			<ul class="nav navbar-nav" style="padding: 3px 0 0 0;">
                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php if(isset($_SESSION['user_name'])){echo 'Hello '.$_SESSION['user_name']." ";}?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo SITE_URL; ?>user_profile.php?action=edit&id=<?php echo $_SESSION['user_id'];?>">User Profile</a></li>
						<li><a href="sign-out.php">Logout</a></li>
                    </ul>
                 </li>              
             </ul>
        </div>
	  </div>
    </div>
	<div class="headermenu">
		<nav class="navbar navbar-inverse">
		  <div class="container-fluid">
			<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
			<ul class="nav navbar-nav">
			  <li class="active menuhome"><a href="home.php"><i class="fa fa-home"></i></a></li>
			  <?php  
				$menu = $instance_header->menu_select();
		        foreach($menu as $menukey=>$menudata)
                {
                ?>   
                <li> 
					<a <?php if(!empty($menudata['submenu'])){  ?> class="dropdown-toggle"  data-toggle="dropdown"  <?php } ?>href="<?php echo $menudata['link_page']; ?>"><?php echo $menudata['link_text']; ?> <i class="<?php echo $menudata['class']; ?>"></i></a>
					<?php if(!empty($menudata['submenu'])){  ?>
						<ul class="dropdown-menu multi-level" >
						<?php 
							foreach($menudata['submenu'] as $subkey=>$subdata)
							{ 
							?>
                                <?php if(empty($subdata['submenu'])){  ?>    
								<li><a href="<?php echo $subdata['link_page'] ?>"><?php echo $subdata['link_text']; ?></a></li>
                                <?php }else{  ?>
                                <li class="dropdown-submenu"><a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo $subdata['link_page'] ?>"><?php echo $subdata['link_text']; ?></a>
                                <ul class="dropdown-menu">
                                   <?php 
									foreach($subdata['submenu'] as $sub_k=>$sub_v)
									{ 
									?>    
										<li><a href="<?php echo $sub_v['link_page'] ?>"><?php echo $sub_v['link_text']; ?></a></li>
									<?php 
                                    }
									?>
								</ul>
                                </li>
                                <?php } ?>
							<?php 
                            } 
							?>
						</ul>
					<?php } 
						?>
	              </li>
                <?php }?>
			</ul>
		  </div>
		  </div>
		</nav>
	</div>-->
    <!--<div class="headertop">
      <div class="sitelogo"><a href="home.php" title="Foxtrot"><img src="images/sitelogo.png" alt="Foxtrot" height="53.68"/></a></div>
      <div class="headertopright">
		<a href="#" class="userinfo"><img src="images/Help-desk.png" alt="Chat/Help" title="Chat/Help" height="30" width="50" style="padding-bottom: 5px;" /></a>
                
		<div class="userlogin">
			<ul class="nav navbar-nav" style="padding: 3px 0 0 0;">
                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php if(isset($_SESSION['user_name'])){echo 'Hello '.$_SESSION['user_name']." ";}?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo SITE_URL; ?>user_profile.php?action=edit&id=<?php echo $_SESSION['user_id'];?>">User Profile</a></li>
						<li><a href="sign-out.php">Logout</a></li>
                    </ul>
                 </li>              
             </ul>
        </div>
	  </div>
    </div>-->
    <style>
        .navbar-nav {
            float: initial;
        }
    </style>
	<div class="headermenu">
		<nav class="navbar navbar-inverse">
		  <div class="container-fluid">
			<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
            <div class="sitelogo"><a href="home.php" title="Foxtrot"><img src="images/sitelogo.png" alt="Foxtrot" height="53.68"/></a></div>
			<ul class="nav navbar-nav" style="margin: 30px 0px 0px 0px;">
			  <li class="active menuhome"><a href="home.php"><i class="fa fa-home"></i></a></li>
			  <?php  
				$menu = $instance_header->menu_select();
				// print_r($menu);
		        foreach($menu as $menukey=>$menudata)
                {
                ?>   
                <li> 
					<a <?php if(!empty($menudata['submenu'])){  ?> class="dropdown-toggle"  data-toggle="dropdown"  <?php } ?>href="<?php echo $menudata['link_page']; ?>"><?php echo $menudata['link_text']; ?> <i class="<?php echo $menudata['class']; ?>"></i></a>
					<?php if(!empty($menudata['submenu'])){  ?>
						<ul class="dropdown-menu multi-level" >
						<?php 
							foreach($menudata['submenu'] as $subkey=>$subdata)
							{ 
								//print_r($subdata);
							?>
                                <?php if(empty($subdata['submenu'])){  ?>    
								<li><a href="<?php echo $subdata['link_page'] ?>"><?php echo $subdata['link_text']; ?></a></li>
                                <?php }else{  ?>
                                <li class="dropdown-submenu"><a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo $subdata['link_page'] ?>"><?php echo $subdata['link_text']; ?></a>
                                <ul class="dropdown-menu 3333">
                                   <?php 
									foreach($subdata['submenu'] as $sub_k=>$sub_v)
									{ 
									?>    
										<li><a href="<?php echo $sub_v['link_page'] ?>"><?php echo $sub_v['link_text']; ?></a></li>
									<?php 
                                    }
									?>
								</ul>
                                </li>
                                <?php } ?>
							<?php 
                            } 
							?>
						</ul>
					<?php } 
						?>
	              </li>
                <?php }?>
                <!--<a href="#" class="userinfo"><img src="images/Help-desk.png" alt="Chat/Help" title="Chat/Help" height="30" width="50" style="padding-bottom: 5px;" /></a>-->
                <li class="dropdown" style="float: right;">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #ef7623;font-weight: 500;font-size: 18px;padding: 0;text-transform: capitalize;"><?php if(isset($_SESSION['user_name'])){echo $_SESSION['user_name']." ";}?>
                    </a>
                    <ul class="dropdown-menu">
                        <?php
                            if(isset($_SESSION['user_is_admin']) and $_SESSION['user_is_admin']){
                                $user_profile_url = SITE_URL.'user_profile.php?action=view';
                            }else{
                                $user_profile_url = SITE_URL.'user_profile.php?action=edit&id='.$_SESSION['user_id'];
                            }
                            ?>
                        <li><a href="<?php echo $user_profile_url;?>">User Profile</a></li>
						<li><a href="sign-out.php">Logout</a></li>
                    </ul>
                </li> 
			</ul>
           </div>
		  </div>
		</nav>
	</div>
  </div>
</div>
</header>
<div class="contentmain" style="<?php /*if(isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add_new' || $_GET['action'] == 'add_sponsor' || $_GET['action'] == 'edit_sponsor' || $_GET['action'] == 'add_product' || $_GET['action'] == 'edit_product' || $_GET['action'] == 'add' || $_GET['action'] == 'edit_transaction' || $_GET['action'] == 'add_batches' || $_GET['action'] == 'edit_batches')){ echo 'padding : 0px !important';}*/ ?>">
<script type="text/javascript">
$(document).ready(function() {
    $('input:text:visible:first:not(#from_date,#beginning_date,#clearing_business_cutoff_date,#payroll_date)', this).focus();
});
</script>
