<?php
function head($title){
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml">
      <head profile="http://gmpg.org/xfn/11">

            <title>joekodotnet</title>
            <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
            <meta http-equiv="Content-Language" content="en-us" />
            <meta name="robots" content="all" />
            <meta http-equiv="imagetoolbar" content="false" />
            <meta name="MSSmartTagsPreventParsing" content="true" />
            <link rel="Shortcut Icon" href="/favicon.ico" type="image/x-icon" />
            
            <link rel="stylesheet" href="/css/lightbox.css" media="screen" type="text/css" />              
            <style type="text/css" media="all">@import url( /css/style.css );</style>
            
            <script type="text/javascript" src="/mint/?js" ></script>
            <script type="text/javascript" src="/js/prototype.js"></script>
            <script type="text/javascript" src="/js/lightbox.js"></script>   
            <script type="text/javascript" src="/js/validate.js"></script>                             
                                                          
      </head>
      
      <body id="<? echo $title;?>">
      
            <div id="container">
            
                  <!-- begin header -->
                  <div id="header">
                  
                        <!-- begin logo -->
                        <a href="http://joeko.net" title="joekodotnet"><img id="logo" src="/img/joekodotnet.gif" alt="joekodotnet" width="351px" height="56px" /></a>
                        <!-- /end logo -->
                        
                        <!-- begin utility nav -->
                        <div id="utility-nav">
                              <ul id="utility-menu">                   
                                    <li id="resume-nav"><a href="/resume.php" title="Resume" class="lbOn"><span>Resume</span></a></li>                                              
                                    <li id="contact-nav"><a href="/contact.php" title="Contact" class="lbOn"><span>Contact</span></a></li>                                              
                              </ul>              
                        </div>                  
                        <!-- /end utility nav -->
                        
                        <!-- begin global nav -->
                        <div id="global-nav">
                              <ul id="menu">                   
                                    <li id="home"><a href="/index.php" title="Home"><span>Home</span></a></li>                                              
                                    <li id="work"><a href="/work.php" title="Work"><span>Work</span></a></li>                                              
                                    <li id="blog"><a href="/blog/" title="Blog"><span>Blog</span></a></li>
                              </ul>              
                        </div>                  
                        <!-- /end global nav -->
                  
                  </div>
                  <!-- /end header -->  
                                                    
<? }
function foot(){
?>                                                    
                  <!-- begin footer -->
                  <div id="footer">                        
                  	<!-- <a href="http://expressionengine.com" onclick="target='_blank'" title="http://expressionengine.com"><img id="expressionEngine" src="/img/ee.gif" width="130px" height="24px" alt="Powered By ExpressionEngine" /></a> -->
                        <div id="build-nav">
                              <ul id="build-menu">                   
                                    <li id="xhtml"><a href="http://validator.w3.org/check?uri=referer" title="Valid XHTML Strict" onclick="target='_blank'"><span>XHTML Strict</span></a></li>                                              
                                    <li id="css"><a href="http://jigsaw.w3.org/css-validator/validator?uri=http://joeko.net/css/style.css" title="Valid CSS" onclick="target='_blank'"><span>CSS</span></a></li>                                              
                              </ul>
                        </div>
                  	<p>&copy;<? echo date('Y') ?> <a href="http://joeko.net" title="joekodotnet">joeko.net</a></p>                        
                  </div>
                  <!-- /end footer -->    
                                                  
            </div>
            <!-- /end container -->
      
      </body>
</html>       
<? } ?>           