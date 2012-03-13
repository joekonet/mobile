<?php
require_once('inc/default.php');
head('');
?>
                  
                                   <?
                                    $mail_to="joe.kocovsky@gmail.com";

                                    $mailbody .= "Name: ".$_POST['sname']." \n";
                                    $mailbody .= "Email: ".$_POST['email']." \n";
                                    $mailbody .= "Comments: ".$_POST['comments']." \n";			
                                    mail($mail_to,"joekodotnet",$mailbody);
                                    ?>							
                        		
                                                      
                  <!-- begin left column -->
                  <div style="height:300px;width:900px;margin:auto;text-align:center;margin:20px 0;">
                        <p>Thank you for your interest. Your message has been received.<br />
                        <a href="javascript:history.back();"><strong>&laquo; Return</strong></a></p>
                  </div>
                  <!-- /end left column -->

<?php
foot();
?>  