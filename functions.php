 <?php
	include 'core/config.php';      // Holds default information about business

	// To turn on debugging uncomment the followin line
	//define("DEBUG","TRUE");
	if(!defined("DEBUG")){define("DEBUG","FALSE");}
	
	function dbg_echo($Str){
		if(DEBUG == 'TRUE'){
			echo("<meta name=\"Debug\" content=\"".$Str."\">\n");
		}
	}

	// SEO Function to process keywords for each page
	// $DEFAULT_KEYPHRASE is defined in master config file
	// keywords are stored in a file called keywords.lst
	// structure is page_name=>keywords
	function GetKeywords(){
		dbg_echo(__METHOD__.' function called...');
		require 'keywords.lst';
		$page = get_currentPage();
		if($Keywords[$page]==NULL){
			if(isset($K)){
				dbg_echo('Returning preloaded keywords! ['.$K.']');
				return $K; }
			else{
				dbg_echo('No KeyPhrase Found or preloaded, Returning '.$DEFAULT_KEYPHRASE);
				return $DEFAULT_KEYPHRASE;
			}
		}
		else{
		dbg_echo('Page ['.$page.'] = keywords ['.$Keywords[$page].']');
		return $Keywords[$page];
		}
		dbg_echo(__METHOD__.' function completed successfully!');
	}    

	//function for printing the text in $pt on local printer
	function print_it($pt){
		dbg_echo(__METHOD__.' function called...');
			$handle = printer_open();
			printer_write($handle, $pt);
			printer_close($handle); 
		dbg_echo(__METHOD__.' function completed successfully!');
	}
	
	//
	function print_topic(){
		dbg_echo(__METHOD__.' function called...');
		$inf = extract_topic();
		$out = $inf['title'].'\n'.$inf['leadin'].'\n'.$inf['topic'];
		print_it($out);
		dbg_echo(__METHOD__.' function completed successfully!');
	}
	
	function file_it($file,$data){
		dbg_echo(__METHOD__.' function called...');
		$handle = fopen($file, 'w');
		fwrite($handle, $data.'\n');
		fclose($handle);
		dbg_echo(__METHOD__.' function completed successfully!');
	}
	
	//adds prototypejs sript to website where needed
	function add_proto_script(){
		$Snippet = '<script src="//ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js"></script>';
		$code_proto=True;
		return $Snippet;
	}

	// This library depends on Prototype. Before loading this module, you must load Prototype.
	function add_scriptaculous(){
		if(!$code_proto=True){
			add_proto_script();
		}
		$Snippet = '<script src="//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js"></script>';

		$Code_scriptac = True;
		return $Snippet;
	}
	
	function getPageURL() {
		dbg_echo(__METHOD__.' function called...');
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://".$_SERVER["SERVER_NAME"];
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= ":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["REQUEST_URI"];
		}
		dbg_echo(__METHOD__.' function completed returning <!--'.$pageURL.' -->');
		return $pageURL;
	}
	
	function get_currentPage(){
		dbg_echo(__METHOD__.' function called...');
		$CPage = $_SERVER["SCRIPT_NAME"];
		$Cut = strrpos($CPage,'/') + 1;
		$APage = substr($CPage,$Cut);
		dbg_echo('Returning current page - ['.$APage.']');

		dbg_echo(__METHOD__.' function completed successfully!');
		return $APage;
	}

	function pageref(){
		dbg_echo(__METHOD__.' function called...');
		require 'pages.php';

		$T = 0;
		$WPage = get_currentPage();
		for($X=0; $X < count($pages); $X +=1){
			if(strstr($WPage,$pages[$X])){
				$T = $X+1;
			}
		}
	$_SESSION['currentpage'] = $T;
	$_SESSION['currentitle'] = $titles[$T];
	dbg_echo("Page #".$T." Title is ".$titles[$T]);
	dbg_echo(__METHOD__.' function completed successfully!');
	return $T;
	}

	function email_page($to){
		dbg_echo(__METHOD__.' function called...');
		$pageURL = getPageURL();
		$info = extract_topic();
		$subject = "You may find this page about ".$info['title']." interesting";
		$body = "It was ".$info['leadin'];
		$body .= "<a href=\"".$pageURL."\">Check it out</a>";
		if (mail($to, $subject, $body)) {
			$Msg = "Your Request was successfully sent!";
		dbg_echo('returning message '.$Msg);
		}
		else 
		{
			$Msg = "Your email failed... PLEASE CALL!";
		}
		dbg_echo(__METHOD__.' function completed returning '.$Msg);
		return $Msg;   
	}

	function extract_topic($pg = "*",$single=0){
		dbg_echo(__METHOD__.' function called...');
		dbg_echo('Extracting '.$pg);
		if($pg == "*"){
			$Pg = getPageURL();
			if(strlen(trim($pg))<=0){
			$pg = "..".getenv('SCRIPT_NAME');    
			}
		}
		$leadin = "Extracted from '.$SiteName.'web site: http://".getenv('HTTP_HOST');
		$target = file_get_contents($pg);
		dbg_echo('page '.$pg.' contains...'.$target);
		$C = strpos($target, "\"explain\"") + 11;
		$A = strpos($target, "\"topic\"") + 8;
		$B = strpos(substr($target, $A), '<?');
		$title = trim(substr($target, $C, strpos(substr($target, $C), '</span>')));
		$topic = substr($target, $A, $B)."\n";
//        $topic = html_entity_decode($topic);
//        $topic = str_replace('<p>', '\n',$topic);
//        $topic = str_replace('</p>', '\n',$topic);
//        $topic = str_replace('<br>', '\n',$topic);
		$ex['leadin'] = $leadin;
		$ex['title'] = $title;
		$ex['topic'] = $topic;
		dbg_echo('returning '.var_dump($ex));

		if($single == 0){
		    dbg_echo(__METHOD__.' function completed returning '.$ex);
			return $ex;
		}else{
		    dbg_echo(__METHOD__.' function completed returning '.$ex['topic']);
			return $ex['topic'];
		}
	}
	
	function checkadminpw($Pw){
		dbg_echo(__METHOD__.' function called...');
		$prefs=file_get_contents('keyword.log');
		$admin[] = explode('|', $prefs,2);
		$_SESSION['user'] = $admin[0];
		if($Pw == $admin[1]){
		$_SESSION['Admin_user'] = 'TRUE';
		}
		else{
			$_SESSION['Admin_user'] = FALSE;
		}
		dbg_echo('returning '.$_SESSION['Admin_user']);
		dbg_echo(__METHOD__.' function completed successfully returning...');
		return $_SESSION['Admin_user'];
	}
	
	function list_pdf($item,$testing){
		dbg_echo(__METHOD__.' function called...');
		$TP = pageref();
		if($TP == 0){
		dbg_echo('$TP==0');
		return "<hr />";
		}
		else{
		dbg_echo('$TP=='.$TP);
		dbg_echo(__METHOD__.' function completed successfully returning...');
			return do_attachment($TP,$item,$testing);
		}
	}

	function do_attachment($Pg,$item,$layout=0,$Stk = 0){
		dbg_echo(__METHOD__.' function called...');
		$Dir = 'attachments/'.$Pg;
		$Files = scandir($Dir);
		if(count($Files) <= 2){
		dbg_echo('returning...');
			return '<hr />';
		}
			$X = $item+1;
			dbg_echo('Looking for item #'.$item.' on page '.$Pg.' which is '.$Files[$X]);
			if($Stk){$pdflist .="<p>";}
			$fileurl = 'attachments/'.$Pg.'/'.str_replace(" ","%20",$Files[$X]);
			if(substr_count($fileurl,"pdf")){
				if(!$layout){
					$pdflist .= pdf_link($X-1,$fileurl,$Files[$X]);}
				elseif($layout==1){
					$pdflist .= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')));}
				elseif($layout==2){
					$pdflist .= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')).'?');}
				elseif($layout==3){
					$pdflist .= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')-1).'&#39;t');}
				elseif($layout==4){
					$pdflist .= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')).'&hellip;');}
			}
			if($Stk){$pdflist .="</p>\n";}
		dbg_echo('<!-- '.__METHOD__.' function completed successfully! -->');
		return $pdflist;
	}

	function list_pdfs($Stacked = 1,$layout = 0,$Count = 0){
		dbg_echo(__METHOD__.' function called...');
		$TP = pageref();
		if($TP == 0){
		dbg_echo('returning...');
		return "<hr /><h4>".$P."</h4>";
		}
		else{
		dbg_echo('returning...');
			return do_attachments($TP,$layout,$Count);
		}
		dbg_echo(__METHOD__.' function FAILED!');
	}

	function has_attachments($P){
		dbg_echo(__METHOD__.' function called...');
		$Dir = 'attachments/'.$P;
		$Files = scandir($Dir);
		if(count($Files) <= 2){
		dbg_echo('returning FALSE, '.count($Files).' attachments found!');
			return false;
		}
		else{
		dbg_echo('returning TRUE, '.count($Files).' attachments found!');
			return true;
		}
	}

	function do_attachments($P,$layout,$Vertical= 1,$Cnt = 0){
		dbg_echo(__METHOD__.' function called...');
		$Dir = 'attachments/'.$P;
		$Files = scandir($Dir);
		if(count($Files) <= 2){
		dbg_echo('returning...');
			return '<hr /><h4>'.$P.'</h4>';
		}
		for($X=2; $X < count($Files); $X +=1){
			if($Vertical){
			$pdflist .="<p>";
			}
			if($Cnt){
			$pdflist .=" ".($X - 1).">> ";
			}
			$fileurl = 'attachments/'.$P.'/'.str_replace(" ","%20",$Files[$X]);
			dbg_echo('Processing item #'.$X.' on page '.$P.' which is '.$Files[$X]);
			if(substr_count($fileurl,"pdf")){
				if(!$layout){
					$pdflist .= pdf_link($X-1,$fileurl,$Files[$X]);}
				elseif($layout==1){
					$pdflist.= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')));}
				elseif($layout==2){
					$pdflist .= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')).'?');}
				elseif($layout==3){
					$pdflist .= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')-1).'&#39;t');}
				elseif($layout==4){
					$pdflist .= pdf_link($X-1,$fileurl,substr($Files[$X],0,strpos($Files[$X],'.')).'&hellip;');}
			}
			if($Vertical){
			$pdflist .="</p>\n";
			}
		}
		dbg_echo('returning...');
		return $pdflist;
	}

	function make_choice($Grp,$Val){
		dbg_echo(__METHOD__.' function called...');
		dbg_echo('returning '.$Val.' in group '.$Grp);
		return "<label for=\"".$Val."\">".$Val."</label><input name=".$Grp." value=\"".$Val."\" type=\"checkbox\">";
	}
	
	function contact_us($Msg){
		dbg_echo(__METHOD__.' function called...');
		dbg_echo('returning contact us '.$Msg.' set up');
		return "<a class=\"hilite\" href=\"contact-us.php\">".$Msg."</a>";
	}
	
	function email_us($Msg = "Email us Today for more information."){
		dbg_echo(__METHOD__.' function called...');
		dbg_echo('returning '.$Msg.' set up');
		return "<span class=\"hilite\"><a href=\"mailto:".$Eml."?subject=Looking for Appointment of further information\">".$Msg."</a></span>";
	}
	
	function call_us($Msg = "Give us a call Today!"){
		dbg_echo(__METHOD__.' function called...');
		dbg_echo('returning '.$Msg.' set up');
		return "<a class=\"hilite\" href=\"callto:'.$Phn'\">".$Msg."</a>";
	}

	function pdf_link($M,$P,$T){
		//$PDF_icon = "images/pdf-icon.png\"
		dbg_echo(__METHOD__.' function called...');
		$pdfx = "<p style=\"display:inline\">\n<img src=\"'.$PDF_icon.'\" height=\"14\" width=\"14\" />\n";
		$pdfx .= "<a class=\"hilite\" href=\"http://".$_SERVER["SERVER_NAME"] ."/".$P."\" target=\"_blank\">";
		$pdfx .= "<span id=\"PDF\" alt=\"Click to read the PDF file...\">".$T."</span>\n</a>\n";   

		dbg_echo('returned the following code...'. $pdfx);
	return $pdfx;
	}

	function pdf_icon($M,$P){
		// $PDF_icon = "images/pdf-icon.png"
		dbg_echo(__METHOD__.' function called...');
		$pdfx .= "&nbsp;<a class=\"hilite\" href=\"http://".$_SERVER["SERVER_NAME"] ."/".$P."\" target=\"_blank\">";
		$pdfx .= "<span id=\"PDF\" alt=\"Click to read the PDF file...\"><img src=\"'.$PDF_icon.'\" height=\"14\" width=\"14\" /></span>\n</a>\n";   

		dbg_echo('returning '.$pdfx);
	return $pdfx;
	}

	function pdf_xlink($M,$P,$T){
		dbg_echo(__METHOD__.' function called...');
		$pdfx = "<p style=\"display:inline\">\n<img src=\"images/pdf-icon.png\" height=\"14\" width=\"14\" />\n";
		$pdfx .= "<a class=\"hilite\" href=\"".$P."\" target=\"_blank\">";
		$pdfx .= "<span id=\"PDF\" alt=\"Click to read the PDF file...\">".$T."</span>\n</a>\n";	

		dbg_echo('returning '.$pdfx);
	return $pdfx;
	}

	function make_faq($N, $Q, $A){
		dbg_echo(__METHOD__.' function called...');
		$QN +=1;
		$faq = "<div id=\"ZTip".$N."\" class=\"zaptip\">Click to see the answer...</div>\n";
		$faq .= "<div class=\"faqheading\" onclick=\"Zapit('faq".$N."')\">\n";
		$faq .= "<span style=\"color:#0000ff\">".$N.") </span>";
		$faq .= "<span>".$Q."?</span></div>\n";
		$faq .= "<div  class=\"faqcontent\" id=\"faq".$N."\" style=\"display: none\">".$A."</div>\n";
		
		dbg_echo('returning...');
		return $faq;
	}

	function make_res($N, $Q, $A){
		dbg_echo(_METHOD__.' function called...');
		$QN +=1;
		$faq = "<li>";
		$faq .= "<div class=\"faqheading\" onclick=\"Zapit('res".$N."')\">\n";
		$faq .= "<span>".$Q."</span></div>\n";
		$faq .= "<div  class=\"faqcontent\" id=\"res".$N."\" style=\"display: none\">".$A."</div>\n";
		$faq .= "</li>";
		
		dbg_echo('returning...');
		return $faq;
	}

	function set_cred($N, $Q){
		dbg_echo(__METHOD__.' function called...');
		$faq = "<span style=\"cursor: pointer;\" onclick=\"Zapit('popres".$N."')\">\n";
		$faq .= $Q."</span>";
		
		dbg_echo('returning '.$faq);
		return $faq;
	}

	function pop_cred($N, $A){
		dbg_echo(__METHOD__.' function called...');
		$faq = "<div id=\"popres".$N."\" style=\"display: none\">".$A."</div>\n";
		
		dbg_echo('returning '.$faq);
	return $faq;
	}

	function make_vid($N, $Q, $A){
		dbg_echo(__METHOD__.' function called...');

		$faq = "<div class=\"vidhead\" style=\"cursor: pointer; display:inline\">\n";
		if($_SESSION['user_platform'] == 'mobile'){
					$faq .= "<a href=\"attachments/videos/".$A."/".$A.".html\" target = \"_BLANK\"><span>".$Q."</span> <img src=\"images/vidicon.png\" height=\"14px\" /></div></a>\n";
		}
		else{
		$faq .= "<!-- ".$_SESSION['user_platform']." -->"."<a href=\"attachments/videos/".$A."/".$A.".html\" target = \"_BLANK\"><span>".$Q."</span> <img src=\"images/vidicon.png\" height=\"14px\" /></div></a>\n";
		}

		dbg_echo('returned the following code...');
		return $faq;
	}

	function set_tube($N, $Q){
		dbg_echo(__METHOD__.' function called...');
		$faq = "<span style=\"cursor: pointer;\" onclick=\"Zapit('poptube".$N."')\">\n";
		$faq .= $Q."<img src=\"/images/vidicon.png\" height=\"14px\" />";
		$faq .= "</span>";
		
		dbg_echo('returning...');
		return $faq;
	}

	function pop_tube($N, $M){
		dbg_echo(__METHOD__.' function called...');
		$faq = "<div id=\"poptube".$N."\" style=\"display: none\">\n
				<iframe width=\"560\" height=\"315\" src=\"".$M."\" frameborder=\"0\" allowfullscreen></iframe>\n</div>\n";
		
		dbg_echo('returning...');
		return $faq;
	}

	function get_title(){
		dbg_echo(__METHOD__.' function called...');
		pageref();
		dbg_echo('returning $_SESSION[\'currentitle\'] = '.$_SESSION['currentitle']);
		return $_SESSION['currentitle'];
	}

	function setup_page($MPT = 1, $T = "", $K = "", $D = "", $C = "", $A = "", $S = ""){
		dbg_echo(__METHOD__.' function called...');
		$pageURL = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

		$useragent=$_SERVER['HTTP_USER_AGENT'];
 		if(strpos($_SERVER['HTTP_USER_AGENT'],"IE")!=0){
			$currbrow = explode(";",$_SERVER['HTTP_USER_AGENT']);
		}else{
			$currbrow = explode(' ',$_SERVER['HTTP_USER_AGENT']);}
		$_SESSION['BT'] = "Other";
		if(strpos($currbrow[1],"MSIE") != 0){
			$_SESSION['BT'] =  "IE";}
		echo "<!-- Browser type: ".$_SESSION['BT']." -->";
		$PT = get_title();
		echo "<!-- ".substr($useragent,0,40)." -->";
		$_SESSION['user_platform'] = 'normal';
		if(preg_match('/android.+mobile|Android|Tablet|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,40))){
		$_SESSION['user_platform'] = 'mobile';
		echo "<!-- ".$pageURL.'m'." -->";
//		header('Location: http://'.$pageURL.'m');
		}

		$output = "<!DOCTYPE html>\n
<html itemscope=\"itemscope\" itemtype=\"http://schema.org/WebPage\">\n
<head>\n
<!-- head section begins -->\n
<title>".$Comp." at ".$Loc."</title>\n
\n
<!-- meta section begins -->\n
<meta http-equiv="X-UA-Compatible" content="IE=edge">\n
<meta content="text/html; charset=UTF-8" http-equiv="content-type">\n
<meta name=\"keywords\" content=\"".GetKeywords(). "\" />\n
<meta name=\"description\" content=\"". $CompD. "\" />\n
<meta name=\"date\" content=\"". date("M d Y"). "\" />\n
<meta name=\"channel\" content=\"". $Chan. "\" />\n
<meta name=\"author\" content=\"". $Auth. "\" />\n
<meta name=\"section\" content=\"". $Sect. "\" />\n
<meta name=\"msvalidate.01\" content=\"65598AB7C8B7F3030CC15514B85A5F05\" />\n
<!--  meta section ends -->\n"; 

		dbg_echo(__METHOD__.'\n function returned the following code...\n');
	return $output;
	}

	function continue_page($MPT = 1, $T = "", $K = "", $D = "", $C = "", $A = "", $S = ""){
		dbg_echo(__METHOD__.' function called...');
		
		if($MPT==1){
			$output .="\n<!-- links section begins -->";
		if($_SESSION['BT'] ==  "IE"){
			$output .="\n<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/iedefault.css\" />";
		}else{
			$output .="\n<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/default.css\" />";
		} 
$output .="\n<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/jqslider1.css\" />
<link rel=\"icon\" href=\"http://". $_SERVER["SERVER_NAME"]."/favicon.ico\" type=\"image/x-icon\" />
<link rel=\"shortcut icon\" href=\"http://". $_SERVER["SERVER_NAME"]."/favicon.ico\" type=\"image/x-icon\" />
<!-- links section ends -->

<!-- Scripts section begins -->
<script type=\"text/javascript\" src=\"scripts/jquery-1.2.6.min.js\"> </script>\n".
"<script type=\"text/javascript\" src=\"scripts/testmobile.js\"> </script>
<script type=\"text/javascript\" src=\"scripts/jqslider1.js\"> </script>\n";
if($Gan == True){
		$output .= "<script src=\"https://www.google-analytics.com/urchin.js\" type=\"text/javascript\">\n";
		$output .= "</script>\n";
		$output .= "<script type=\"text/javascript\">\n";
		$output .= "_uacct = \"".$Gac."\";";
		$output .= "urchinTracker();\n </script>\n";
				};
$output .= "\n
<!-- Script section Ends -->\n
\n
<!-- head section ends -->\n</head>\n<body onLoad=\"";
 if($_SESSION['mobile'] == 'ignore'){
		$output.= "";
 }
 else{
		$output.= "checkmobile(1200);";
 }
$output .="\">\n
<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-33226312-1']);
  _gaq.push(['_trackPageview']);

  (function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>\n
<!-- body section begins -->\n";
		}
		elseif($MPT ==2) {
			$output .="\n<!-- links section begins -->";
			if($_SESSION['BT'] ==  "IE"){
			$output .="<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/iedefault.css\" />";
		}else{
			$output .="<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/default.css\" />";
		} 
$output .="\n<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/zipstyle.css\" />
<link rel=\"icon\" href=\"http://". $_SERVER["SERVER_NAME"]."/favicon.ico\" type=\"image/x-icon\" />
<link rel=\"shortcut icon\" href=\"http://". $_SERVER["SERVER_NAME"]."/favicon.ico\" type=\"image/x-icon\" />
<!-- links section ends -->

<!-- Scripts section begins -->
<script type=\"text/javascript\" src=\"scripts/zippy.js\"> </script>
<script type=\"text/javascript\" src=\"scripts/zapper.js\"> </script>
<script type=\"text/javascript\" src=\"scripts/jquery-1.2.6.min.js\"> </script>
";
if($Gan == True){
		$output .= "<script src=\"https://www.google-analytics.com/urchin.js\" type=\"text/javascript\">\n";
		$output .= "</script>\n";
		$output .= "<script type=\"text/javascript\">\n";
		$output .= "_uacct = \"".$Gac."\";";
		$output .= "urchinTracker();\n </script>\n";
				};
$output .= "<!-- Script section Ends -->

<!-- head section ends -->\n</head>\n

<body>
<!-- body section begins -->\n			
<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-33226312-1']);
  _gaq.push(['_trackPageview']);

  (function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>";		}

		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
		}

	function place_topbar($Tmp = 1,$links=0){
		dbg_echo(__METHOD__.' function called...');
		$pageURL .= "HTTP://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		$output = "    <div class=\"TopBar".$Tmp."\">
	<!-- topbar begins -->
	   <div class=\"TopOps\" align=\"center\">\n
		<!-- top buttons begin -->\n
		<div class=\"socialist\">\n
			<ul id=\"socialbuttons\">\n
				<li><a href=\"https://www.facebook.com/pages/BTN-Financial-Services/246055092166089\" target=\"_Blank\"><img width=\"20\" height=\"20\" style=\"margin-right: 1px;  border-image: initial;border: 0px solid;\" alt=\"Facebook\" src=\"images/fbb.png\" /></a></li>\n
				<li><a href=\"https://twitter.com/#!/BTNFinancial\" target=\"_Blank\"><img width=\"20\" height=\"20\" style=\"margin-right: 1px;  border-image: initial;border: 0px solid;\" alt=\"Facebook\" src=\"images/twb.png\" /></a></li>\n
				<li><a href=\"http://www.linkedin.com/company/2600931\" target=\"_Blank\"><img width=\"20\" height=\"20\" style=\"margin-right: 1px;  border-image: initial;border: 0px solid;\" alt=\"linkedin\" src=\"images/lib.png\" /></a></li>\n
				<li><a href=\"# \" onclick =\"javascript: window.open('mpage.php?pg=".$pageURL."', 'window_name', 'width = 550, height = 350');\" ><img src=\"images/emb.png\" alt=\"email us today\"></a></li>\n
				<li><a class=\"a2a_dd\" href=\"http://www.addtoany.com/share_save\"><img src=\"http://static.addtoany.com/buttons/favicon.png\" width=\"20\" height=\"20\" border=\"0\" alt=\"Share\"/></a>
<script type=\"text/javascript\" src=\"http://static.addtoany.com/menu/page.js\"></script></li>\n
			</ul>\n
		</div>\n
		<!-- top buttons end -->\n";
		if($links==0){
			$output .= file_get_contents('toplinks.php'). "        </div>\n";   
		}
		else {
			$output .= file_get_contents('toplinks2.php'). "        </div>\n";
		}
			$output .= "<!-- topbar ends -->\n
			</div>\n";

		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
		}

	function openpage($template=1){
		dbg_echo(__METHOD__.' function called...');
		   $output = "<div class=\"viewarea\" align=\"center\">\n
		   <div id=\"container\" align=\"center\">";
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
	}
		
	function closepage($Template=1){
		dbg_echo(__METHOD__.' function called...');
		$output = "\n<div class=\"BPad\">&nbsp;</div><br />
		\n</div>\n\n";
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
		}
	
	function closeTopic(){
		dbg_echo(__METHOD__.' function called...');
		$output = "<br />\n<p>&nbsp;</p>\n</div>\n";
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
	}
	
	function finishpage(){
		dbg_echo(__METHOD__.' function called...');
		$output = "</body>\n</html>";
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
	}
	
	function open_header($template=1){
		dbg_echo(__METHOD__.' function called using template'.$template);
		if($template == 1){
			$output = "    <div class=\"header\">";
		}
		elseif($template == 2){
			$output = "        <div class=\"header2\">";
		}
		
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
	}
	
	function place_title($Tmp=1){
		dbg_echo(__METHOD__.' function called using template'.$Tmp);
		if($Tmp == 1){
			$output = "<div id=\"Title\" align=\"center\">".file_get_contents('title.php')."</div>";
		}
		elseif($Tmp == 2){
				$output = "<div id=\"Title2\">".file_get_contents('title.php')."</div>";
		}
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
	}
	
	function close_header($Template=1){
		dbg_echo(__METHOD__.' function called...');
		$output = "\n</div>";
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
		}

	function place_logo($Tmp = 1, $Cmp = "", $Adr = "unknown", $Cty = "unknown", $Prv = "unknown", $Pfn = "unknown", $PC = "000", $Alt ="logo"){
		dbg_echo(__METHOD__.' function called using template'.$Tmp);
			if($Tmp == 1){
			$output = "    <div id=\"logo\">";
			}
			elseif($Tmp == 2){
			$output = "    <div id=\"logo2\">";
			}   
			$output .= "<a class=\"url\" href=\"http://".$_SERVER["SERVER_NAME"]."/\">
			<img src=\"images/logo.jpg\" alt=\"".$Alt."\" /></a>
			<div class=\"info\">
				<div id=\"hcard-business\" class=\"vcard\">
					<div class=\"adr\">
						<div class=\"org fn\">
							<a class=\"url\" href=\"http://".$_SERVER["SERVER_NAME"]."/\">".$Cmp."</a>
						</div>
						<div class=\"street-address\">".$Adr."</div>
						<div>
							<span class=\"locality\">".$Cty."</span> 
							<span class=\"region\" title=\"<abbr>".$Prv."</abbr>".$Pfn."\"></span>
							<span class=\"postal-code\">".$PC."</span>,
							<span class=\"country-name\">CAD</span>
						</div>
					</div>
				</div>
			</div>
		</div>";

		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;        
	}

	function place_banner(){
		dbg_echo(__METHOD__.' function called...');
		  $output = "<div id=\"slideshow\">".file_get_contents('slideshow.php')."\n</div>";
		dbg_echo(__METHOD__.' function returned the following code...');
		return $output;
	}
	
	function place_footer($template=1){
		dbg_echo(__METHOD__.' function called...');
	include 'core/config.php';

	 $ret = "<div class=\"FootBar\" id=\"copy\" align=\"center\">".$Adr." ".$Cty.", ".$Prv." ".$PC."&nbsp;&nbsp;    
	<a href=\"callto:".$PT."\"> ".$PT."</a>&nbsp;&nbsp; 
	Copyright&copy; ".$Cmp." All rights reserved.   <a href=\"disclaimer.php\">Disclaimer</a>&nbsp;|&nbsp; 
	<a href=\"policy.php\">Privacy policy</a>&nbsp;\n<span style=\"float:right\"><a href=\"admin\index.php\">.</a></span>\n
	</div>\n";
	dbg_echo(__METHOD__.' function completed returning...');
    return $ret;
	}
	
	function align_sidebar($Src){
		dbg_echo(__METHOD__.' function called...');
		include 'core/sidebar_layout.php';

		$break = Explode('/', $Src);
		$Pg = $break[count($break) - 1];
		$Z = 0; 
		
		reset($redirect);
		reset($submenu);
		
		for($Z=0; $Z < count($redirect); $Z +=1){
			if($Pg == current($redirect)){
				// return $Z,0
				$GLOBALS['Link'] = $Z+1;
				$GLOBALS['Sub'] = 0;
				return TRUE;
				exit();
			}
			else {
				for($X=0; $X < count($submenu[$Z]); $X +=1){
					if($Pg == key($submenu[$Z])){
						// return $Z,$X
						$GLOBALS['Link'] = $Z+1;
						$GLOBALS['Sub'] = $X+1;
						return TRUE;
						exit();
					}
					next($submenu[$Z]);
				}
			}
		next($redirect);
		}
		$GLOBALS['Link'] = 0;
		$GLOBALS['Sub'] = 0;
		dbg_echo('<!-- '.__METHOD__.' function completed successfully! -->');
		return TRUE;
	}
	
	function place_sidebar($Opn= 0, $Sub = 0){
		dbg_echo(__METHOD__.' function called...');
	include 'core/sidebar_layout.php';

		$Sub -= 1;
		$Opn -= 1;
		$menu = "\n<div class=\"sidebar\">\n"; 
		$OpTag = "None";
		
		reset($labels);
		reset($redirect);
		if($Opn >= 0){

		for($X=0; $X < $Opn; $X+=1){
			next($labels);
			next($redirect);
		}

		$menu .= "<a class=\"sub_head\" href=\"".current($redirect)."\">\n
			  <img src=\"scripts/zippy_minus.gif\" id=\"zip".current($labels)."\" alt=\"BTN Financial Services - ".key($labels)."\" />
			  <span class=\"sub_link\">".key($labels)."</span></a>
		<div style=\"display:block;\" id=\"".current($labels)."\" class=\"submenu\">";

		$OpTag = current($labels);

		$menu .= "<ul>\n";

		for($X=0; $X < count($submenu[$Opn]); $X +=1){
			if($X == $Sub){
				$menu .= "<li id=\"selsub\"><a href=\"".key($submenu[$Opn])."\">".current($submenu[$Opn])."</a></li>";
			}
			else {
				$menu .= "<li>\n<a href=\"".key($submenu[$Opn])."\">".current($submenu[$Opn])."</a>\n</li>\n";
			}
			next($submenu[$Opn]);
		}
		$menu .= "</ul>\n</div>\n";
		}
	   
		reset($labels);
		reset($redirect);
		$Y = 0;
		for($Z=0; $Z < count($labels); $Z +=1){
//            print current($labels).' - '.$OpTag;
			if(current($labels) != $OpTag){
				$menu .= "<a class=\"sblink\" href=\"".current($redirect)."\">
				<img src=\"scripts/zippy_plus.gif\" id=\"zip".current($labels)."\" alt=\"BTN Financial Services - ".key($labels)."\" />\n
				<span class=\"sub_link\">".key($labels)."</span></a>
			<div style=\"display:none;\" id=\"".current($labels)."\" class=\"submenu\">\n";
			$menu .= "<ul>\n";
			for($X=0; $X < count($submenu[$Y]); $X +=1){
				$menu .= "<li>\n<a href=\"".key($submenu[$Y])."\">".current($submenu[$Y])."</a>\n</li>\n";
				next($submenu[$Y]);
			}

			$menu .= "          </ul>\n
			</div>\n<br />";
		 }
			$Y += 1;
			next($labels);
			next($redirect);
		}
			$menu .= "</div>\n";
	
		dbg_echo('returning $menu');	
		return $menu;
		}
	
?>