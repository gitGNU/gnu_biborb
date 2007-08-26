<?php echo '<?xml version="1.0" encoding="utf-8"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
  <head>
    <meta http-equiv='content-type' content='text/html; charset=utf-8' />
    <meta name='robots' content='noindex,nofollow'/>
    <meta name='author' content='Guillaume Gardey'/>
    <link href='css/style.css' rel='stylesheet' type='text/css'/>
    <title>BibORB</title>
    <script type='text/javascript' src='./biborb.js'></script>
</head>
<body>

  <!-- The menu -->
  <div id='menu'>
    <span id='title'>BibORB</span>
      <span id='bibname'><?php if( isset($_SESSION['bibdb'])) { echo $_SESSION['bibdb']->getName();} ?> </span>
  <!-- 
      First menu item:
             Welcome
               Available bibliographies -->
    <ul>
      <li>
        <a title='<?php echo msg("INDEX_MENU_WELCOME_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=welcome'>
	  <?php echo msg("INDEX_MENU_WELCOME")?>
	</a>
        <ul>
          <li>
            <a title='<?php echo msg("INDEX_MENU_BIBS_LIST_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=select'>
	      <?php echo msg("INDEX_MENU_BIBS_LIST")?>
	    </a>
          </li>
        </ul>
      </li>
                 
    <!-- Menu to display a given bibliography
             -->
    <?php if (isset($_SESSION['bibdb'])) { ?>
      <li>
        <a title='<?php echo msg("BIBINDEX_MENU_DISPLAY_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=info_display'>
	  <?php echo msg("BIBINDEX_MENU_DISPLAY") ?>
	</a>
        <ul>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_DISPLAY_ALL_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=displayall'>
	      <?php echo msg("BIBINDEX_MENU_DISPLAY_ALL")?>
	    </a>
	  </li>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_DISPLAY_BY_GROUP_HELP") ?>' href='<?php echo _PHP_SELF_?>?mode=displaybygroup'>
	      <?php echo msg("BIBINDEX_MENU_DISPLAY_BY_GROUP") ?>
	    </a>
	  </li>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_BROWSE_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=browse&amp;start=0'>
	      <?php echo msg("BIBINDEX_MENU_BROWSE"); ?>
	    </a>
	  </li>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_DISPLAY_SEARCH_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=displaysearch'>
	      <?php echo msg("BIBINDEX_MENU_DISPLAY_SEARCH") ?>
	    </a>
	  </li>
	</ul>
      </li>

      <li>
	<a title='<?php echo msg("BIBINDEX_MENU_TOOLS_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=displaytools'>
	  <?php echo msg("BIBINDEX_MENU_TOOLS") ?>
	</a>
	<ul>
	  <li/>
	</ul>
      </li>

      <li>
	<a title='<?php echo msg("BIBINDEX_MENU_BASKET_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=info_basket'>
	  <?php echo msg("BIBINDEX_MENU_BASKET") ?>
	</a>
	<ul>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_BASKET_DISPLAY_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=displaybasket'>
	      <?php msg("BIBINDEX_MENU_BASKET_DISPLAY") ?>
	    </a>
	  </li>
	  <?php  if( DISABLE_AUTHENTICATION ) { ?>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_BASKET_GROUP_HELP") ?>' class='admin' href='<?php echo _PHP_SELF_ ?>?mode=groupmodif'>
	      <?php echo msg("BIBINDEX_MENU_BASKET_GROUP") ?>
	    </a>
	  </li>
	  <?php } ?>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_BASKET_EXPORT_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=exportbasket'>
	      <?php echo msg("BIBINDEX_MENU_BASKET_EXPORT") ?>
	    </a>
	  </li>
          <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_BASKET_BIBTEX_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=exportbaskettobibtex'>
	      <?php echo msg("BIBINDEX_MENU_BASKET_BIBTEX") ?>
	    </a>
	  </li>
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_BASKET_HTML_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?mode=exportbaskettohtml'>
	      <?php msg("BIBINDEX_MENU_BASKET_HTML") ?>
	    </a>
	  </li>   
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_BASKET_RESET_HELP") ?>' href='<?php echo _PHP_SELF_ ?>?action=resetbasket'>
	      <?php echo msg("BIBINDEX_MENU_BASKET_RESET") ?>
	    </a>
	  </li>
	</ul>
      </li>

      <?php } ?> 
                 
    <!-- Second menu item:
              Manager
              Login         
              Logout -->
      <li>
        <a title='<?php echo msg("INDEX_MENU_MANAGER_HELP")?>' href='<?php echo _PHP_SELF_ ?>?mode=info_manager'>
	  <?php echo msg("INDEX_MENU_MANAGER")?>
	</a>
        <ul>
          <?php if (DISABLE_AUTHENTICATION || array_key_exists('user',$_SESSION)) { ?>
          <li>
            <a title='<?php echo msg("INDEX_MENU_LOGIN_HELP")?>' href='<?php echo _PHP_SELF_ ?>?mode=login'>
	      <?php echo msg("INDEX_MENU_LOGIN")?>
	    </a>
          </li>
          <?php }     
            if (DISABLE_AUTHENTICATION || array_key_exists('user',$_SESSION)) { ?>
          <li>
            <a href='<?php echo _PHP_SELF_ ?>?mode=preferences' title='<?php echo msg("INDEX_MENU_PREFERENCES_HELP")?>'>
	      <?php echo msg("INDEX_MENU_PREFERENCES")?>
	    </a>
          </li>
          <?php }    
            if ( DISABLE_AUTHENTICATION || array_key_exists('user',$_SESSION)) { ?>
          <li>
            <a title='<?php echo msg("INDEX_MENU_LOGOUT_HELP")?>' href='<?php echo _PHP_SELF_ ?>?mode=welcome&action=logout'>
	      <?php echo msg("INDEX_MENU_LOGOUT")?>
	    </a>
          </li>
          <?php } ?> 
	  <li>
	    <a title='<?php echo msg("BIBINDEX_MENU_ADMIN_ADD_HELP") ?>' class='admin' href='<?php echo _PHP_SELF_ ?>?mode=selectRefType'>
	      <?php echo msg("BIBINDEX_MENU_ADMIN_ADD") ?>
	    </a>
	  </li>
        </ul>
      </li>
    </ul>
  </div> <!-- End of the menu -->

  <!-- main panel -->
  <div id='main'>
    <!-- main title -->  
    <h2 id='main_title'><?php echo $pageTitle?></h2>
  
    <!-- errors -->
    <?php if ($_SESSION['errorManager']->hasErrors()) { ?>
    <div id='error'>
      <?php foreach ($_SESSION['errorManager']->_errorStack as $aError) { ?>
      Error:
      <pre><?php echo $aError['string'] ?></pre>
      <pre><?php print_r($aError['context'],true)?></pre>
      <?php } ?>
    </div>
    <?php } ?>
  
    <!-- warnings -->
    <?php if ($_SESSION['errorManager']->hasWarnings()) { ?>
    <div id='warning'>
      <?php foreach ($_SESSION['errorManager']->_warningStack as $aWarning) { ?>
      Warning:
      <pre><?php echo $aWarning['string'] ?></pre>
      <pre><?php print_r($aWarning['context'],true)?></pre>
      <?php } ?>
    </div>
    <?php } ?>

    <div id='content'>
      <?php echo $pageContent ?>  
    </div>
  </div>
  
<!--        if (isset($iMessage))
            $aHtml .= "\n<div id='message'>{$iMessage}</div>";
        if (isset($iContent))
            $aHtml .= "\n<div id='content'>{$iContent}</div>";
        $aHtml .= "\n</div>";-->
  
</body>
</html>
