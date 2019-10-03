<?php

$title = "CSS Class Extrator";
set_time_limit(0);

function xflush(){
	static $output_handler = null;
	if ($output_handler === null) $output_handler = @ini_get('output_handler');
	if ($output_handler == 'ob_gzhandler') return;
    flush();
    if (function_exists('ob_flush') AND function_exists('ob_get_length') AND ob_get_length() !== false){
        @ob_flush();
    }elseif (function_exists('ob_end_flush') AND function_exists('ob_start') AND function_exists('ob_get_length') AND ob_get_length() !== FALSE){
        @ob_end_flush();
        @ob_start();
    }
}

function value($string,$start,$end){
	$str = explode($start,$string);
	@$str = explode($end,$str[1]);
	return $str[0];
}

function removeSpecial($v){
	return preg_replace('/[^a-z0-9]/i', '', $v);
}
if (isset($_POST['css'])&&isset($_POST['preflix_class'])){
	xflush();
	#preg_match_all('/(\.?'.addcslashes(trim($_POST['preflix_class']), '-').'.*?)\s?\{/', $_POST['css'], $matches);
	#'/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/'
	preg_match_all(isset($_POST['isTag']) ? '/<i class\=\"([a-z0-9\s\.\:#_\-@,]+)\"/' : '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $_POST['css'], $matches);

	$rPrefix = isset($_POST['rPrefix']) && $_POST['rPrefix'] ? array_merge([(isset($_POST['isTag']) ? '':'.').$_POST['preflix_class']],[(isset($_POST['isTag']) ? removeSpecial($_POST['preflix_class']):'')]) : [];

	$extracts=[];
	foreach($matches[1] as $class){
		if(strpos($class,$_POST['preflix_class']) > -1 && (isset($_POST['isTag']) || strpos($class,':before') > -1 || strpos($class,':after') > -1)){
			foreach(explode(',',$class) as $c){
				if(removeSpecial($c) == removeSpecial($_POST['preflix_class'])) continue;
				$v = str_replace(array_merge([":before",":after"],$rPrefix),[""],$c);
				$extracts[] = trim(isset($_POST['isHTML']) ? str_replace(['.'], " ", $v) : $v);
			}
		}
	}
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>'.$title.'</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/font/octicons.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.2/css/all.min.css">
</head><body>

<div class="container h-100" id="app">
	<div class="mt-5 text-center" style="padding-top:'.(isset($extracts)&&$extracts?'7':'10').'%">
		<h4 class="mb-3"><i class="fas fa-search"></i> '.$title.'</h4>
		<form class="form-row justify-content-center align-items-center" method="post">
			
		'.(isset($extracts)&&!$extracts?'<div class="alert alert-info mb-3 ml-1 col-10 col-sm-9"><i class="octicon octicon-info"></i> No class results</div><br>':'').'
			<div class="col-10 col-sm-3 my-1">
				<input class="form-control" name="preflix_class" placeholder="Enter with prefix class to extract, ex: octicon-" required'.(isset($_POST['preflix_class']) ? ' value="'.$_POST['preflix_class'].'"' : '').'>
			</div>
			<div class="col-10 col-sm-2 my-1">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="isTag" name="isTag"'.(!empty($_POST)&&isset($_POST['isTag']) ? ' checked':'').'>
					<label class="custom-control-label" for="isTag">Is tag icon ?</label>
				</div>
			</div>
			<div class="col-10 col-sm-2 my-1">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="rPrefix" name="rPrefix"'.(!empty($_POST)&&!isset($_POST['rPrefix']) ? '':' checked').'>
					<label class="custom-control-label" for="rPrefix">Remove prefix ?</label>
				</div>
			</div>
			<div class="col-10 col-sm-2 my-1">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="isHTML" name="isHTML"'.(!empty($_POST)&&!isset($_POST['isHTML']) ? '':' checked').'>
					<label class="custom-control-label" for="isHTML">Format to html ?</label>
				</div>
			</div>
			<div class="col-10 col-sm-9 my-1">
				<textarea class="form-control" placeholder="Enter with css of library icon" name="css" required onfocus="this.select()"'.(isset($_POST['css']) ? '': ' autofocus').'>'.(isset($_POST['css']) ? $_POST['css'] : '').'</textarea>
			</div>
			<div class="col-10 col-sm-9 my-2">
				<button type="submit" class="btn btn-dark"><i class="octicon octicon-search"></i> Extract class names</button>
			</div>';
			if(isset($extracts)&&$extracts){
				echo '<script>arr=["'.implode('","',array_unique($extracts)).'"]</script><div class="col-10 col-sm-9 mt-5 my-1">
				<select onchange=\'re=document.getElementById("result"),vl=this.value;re.value=(vl==2?"[":"")+(vl!=3?"\"":"")+arr.join(vl!=3?"\",\"":"\n")+(vl!=3?"\"":"")+(vl==2?"]":"");re.focus()\' class="col-3 float-left custom-select">
					<option value="0" disabled>Formats</option>
					<option selected value="1">Array values</option>
					<option value="2">Array with brackets</option>
					<option value="3">List</option>
				</select>
				<button type="button" class="btn btn-dark float-right mb-2" data-clipboard data-clipboard-target="#result"><i class="far fa-copy"></i> Copie to clipboard</button>
				<textarea class="form-control" name="result" id="result" onfocus="this.select()" autofocus>"'.implode('","',array_unique($extracts)).'"</textarea>
			</div>';
				xflush();
			}
		echo '</form>
	</div>
</div>';

echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script><script>new ClipboardJS("[data-clipboard]")</script></body></html>';