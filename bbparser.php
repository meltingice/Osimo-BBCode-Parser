<?
class OsimoBBParser{
	private $isOsimo;
	private $simple_bbcodes;
	private $fancy_bbcodes;
	private $allowedCSS;
	
	function OsimoBBParser(){
		$this->isOsimo = true;
		
		$this->loadDefaults();
	}
	
	private function loadDefaults(){
		$simple_defaults = array(
			"b"=>array(
				"search"=>"/\[b\](.+?)\[\/b\]/is",
				"replace"=>'<strong>$1</strong>'
			),
			"i"=>array(
				"search"=>"/\[i\](.+?)\[\/i\]/is",
				"replace"=>'<i>$1</i>'
			),
			"u"=>array(
				"search"=>"/\[u\](.+?)\[\/u\]/is",
				"replace"=>'<u>$1</u>'
			),
			"s"=>array(
				'search'=>"/\[s\](.+?)\[\/s\]/is",
				'replace'=>'<span style="text-decoration:line-through">$1</span>'
			),
			"url"=>array(
				"search"=>"/\[url\](.+?)\[\/url\]/is",
				"replace"=>'<a target="_blank" href="$1">$1</a>'
			),
			"img"=>array(
				"search"=>"/\[img\](.+?)\[\/img\]/is",
				"replace"=>'<img src="$1" />'
			),
			"quote"=>array(
				"search"=>"/\[quote\](.+?)\[\/quote\]/is",
				"replace"=>'<blockquote><span class="blockquote-title">Quote:</span><br/>$1</blockquote>'
			),
			'code'=>array(
				'search'=>"/\[code\](.+?)\[\/code\]/is",
				'replace'=>'<pre class="code"><code class="plain">$1</code></pre>'
			),
			'right'=>array(
				'search'=>"/\[right\](.+?)\[\/right\]/is",
				'replace'=>'<div style="text-align:right">$1</div>'
			),
			'center'=>array(
				'search'=>"/\[center\](.+?)\[\/center\]/is",
				'replace'=>'<div style="text-align:center">$1</div>'
			),
			'left'=>array(
				'search'=>"/\[left\](.+?)\[\/left\]/is",
				'replace'=>'<div style="text-align:left">$1</div>'
			),
			'list'=>array(
				'search'=>"/\[list\](.+?)\[\/list\]/is",
				'replace'=>'<ul>$1</ul>'
			),
			'[*]'=>array(
				'search'=>"/\[\*\]([^\n|\r]+)/is",
				'replace'=>'<li>$1</li>'
			),
			'table'=>array(
				'search'=>"/\[table\](.+?)\[\/table\]/is",
				'replace'=>'<table>$1</table>'
			),
			'row'=>array(
				'search'=>"/\[row\](.+?)\[\/row\]/is",
				'replace'=>'<tr>$1</tr>'
			),
			'cell'=>array(
				'search'=>"/\[cell\](.+?)\[\/cell\]/is",
				'replace'=>'<td>$1</td>'
			),
			'spoiler'=>array(
				'search'=>"/\[spoiler\](.+?)\[\/spoiler\]/is",
				'replace'=>'<div class="spoiler"><div class="spoiler_header" onclick="$(this).next().slideToggle(\'slow\')">Click to show/hide spoiler.</div><div class="spoiler_content" style="display:none">$1</div></div>'
			),
			'hide'=>array(
				'search'=>"/\[hide\](.+?)\[\/hide\]/is",
				'replace'=>'<span class="inline_spoiler_notify" onclick="if($(this).next().is(\':visible\')){ $(this).next().fadeOut(\'fast\'); } else { $(this).next().fadeIn(\'fast\'); }">Spoiler!</span><span class="inline_spoiler">$1</span>'
			)
		);
		
		$fancy_defaults = array(
			'email'=>array(
				'search'=>"/\[email=([^\]]+)\](.+?)\[\/email\]/is",
				'replace'=>'<a href="$1">$2</a>'
			),
			"url"=>array(
				"search"=>"/\[url=([^\]]+)\](.+?)\[\/url\]/is",
				"replace"=>'<a target="_blank" href="$1">$2</a>'
			),
			'size'=>array(
				'search'=>"/\[size=\s?([0-9]+)(?:px)?\](.+?)\[\/size\]/is",
				'replace'=>'<span style="font-size:$1px">$2</span>'
			),
			'font'=>array(
				'search'=>"/\[font=([^\]]+)\](.+?)\[\/font\]/is",
				'replace'=>'<span style="font-family:$1">$2</span>'
			),
			'color'=>array(
				'search'=>"/\[color=([^\]]+)\](.+?)\[\/color\]/is",
				'replace'=>'<span style="color:$1">$2</span>'
			),
			'quote'=>array(
				'search'=>"/\[quote=([^\]]+)\](.+?)\[\/quote\]/is",
				'replace'=>'<blockquote><span class="blockquote-title">Quote by $1:</span><br/>$2</blockquote>'
			),
			'align'=>array(
				'search'=>"/\[align=(left|right|center)\](.+?)\[\/align\]/is",
				'replace'=>'<div style="text-align: $1">$2</div>'
			),
			'spoiler'=>array(
				'search'=>"/\[spoiler=([^\]]+)\](.+?)\[\/spoiler\]/is",
				'replace'=>'<div class="spoiler"><div class="spoiler_header" onclick="$(this).next().slideToggle(\'slow\')">$1</div><div class="spoiler_content" style="display:none">$2</div></div>'
			),
			'hide'=>array(
				'search'=>"/\[hide=([^\]]+)\](.+?)\[\/hide\]/is",
				'replace'=>'<span class="inline_spoiler_notify" onclick="if($(this).next().is(\':visible\')){ $(this).next().fadeOut(\'fast\'); } else { $(this).next().fadeIn(\'fast\'); }">$1</span><span class="inline_spoiler">$2</span>'
			)
		);
		
		$this->addSimple($simple_defaults);
		$this->addFancy($fancy_defaults);
		
		/* Allowed CSS attributes for tables - prevents things like
		 * using position: absolute to cover the page contents. */
		$this->allowedCSS = array(
			'width',
			'height',
			'background-color',
			'color',
			'border',
			'border-color',
			'border-style',
			'border-width',
			'border-collapse',
			'padding',
			'vertical-align',
			/* these next 2 aren't CSS but are useful for tables */
			'cellpadding',
			'cellspacing',
			'align' // not a real CSS property - will allow for easy alignment
		);
	}
	
	public function addSimple($tag){
		if(is_array($tag)){
			foreach($tag as $name=>$code){
				$this->simple_bbcodes[$name] = $code;
			}
		}
		else{ //make an assumption...
			$this->simple_bbcodes[$tag] = array(
				"tag"=>$tag,
				"search"=>"/\[$tag\](.+)\[\/$tag\]/i",
				"replace"=>'<'.$tag.'>$1</'.$tag.'>'
			);
		}
	}
	
	public function addFancy($tag){
		if(is_array($tag)){
			foreach($tag as $name=>$code){
				$this->fancy_bbcodes[$name] = $code;
			}
		}
		else{
			return false;
		}
	}
	
	public function parse($content,&$count=false){
		$search = array();
		$replace = array();
		
		foreach($this->simple_bbcodes as $code){
			$search[] = $code['search'];
			$replace[] = $code['replace'];
		}
		
		foreach($this->fancy_bbcodes as $code){
			$search[] = $code['search'];
			$replace[] = $code['replace'];
		}
		
		if(get_magic_quotes_gpc()){
			$content = stripslashes($content);
		}
		
		/* nocode tag check - must do this first! */
		$content =  preg_replace_callback(
			'/\[nocode\](.+?)\[\/nocode\]/is',
			create_function(
				'$matches',
				'return str_replace(array("[","]"),array("&#91;","&#93;"),$matches[1]);'
			),
			$content
		);
		
		/* unfortunately we have to do fancy tables separately */
		$content = preg_replace_callback(
			"/\[(table)=([^\]]+)\](.+?)\[\/table\]/is",
			array($this,'parseTable'),
			$content
		);
		$content = preg_replace_callback(
			"/\[(row)=([^\]]+)\](.+?)\[\/row\]/is",
			array($this,'parseTable'),
			$content
		);
		$content = preg_replace_callback(
			"/\[(cell)=([^\]]+)\](.+?)\[\/cell\]/is",
			array($this,'parseTable'),
			$content
		);
		
		foreach($search as $pattern){
			while(preg_match($pattern,$content)){
				$content = preg_replace($search,$replace,$content,-1,$count);
			}
		}
		
		/* Handle smilies, will probably change later */
		if($this->isOsimo){
			$content = $this->processSmilies($content);
		}
		
		return nl2br($content);
	}
	
	private function parseTable($matches){
		$return_css = '';
		$return_attr = '';
		$css = explode(';',$matches[2]);
		if(count($css)>0){
			foreach($css as $prop){
				$data = explode(':',$prop);
				$css_name = strtolower($data[0]);
				if(in_array($css_name,$this->allowedCSS)){
					if($matches[1]=='table'){
						if($css_name == 'cellpadding'||$css_name == 'cellspacing'){
							$return_attr .= $css_name.'="'.$data[1].'" ';
							continue;
						}
						
						if($css_name == 'align'){
							if($data[1] == 'left'){
								$return_css .= 'margin-right: auto;';
							}
							elseif($data[1] == 'center'){
								$return_css .= 'margin-right: auto;margin-left: auto;';
							}
							elseif($data[1] == 'right'){
								$return_css  .= 'margin-left: auto;';
							}
							continue;
						}
					}
					
					$return_css .= $prop;
					if(substr($prop,strlen($prop)-1) != ';'){
						$return_css .= ';';
					}
				}
			}
		}
		if($matches[1]=='row'){ $ele_name = 'tr'; }
		elseif($matches[1]=='cell'){ $ele_name = 'td'; }
		else{ $ele_name = 'table'; }
		return "<$ele_name $return_attr style=\"$return_css\">{$matches[3]}</$ele_name>";
	}
	
	/*
	 * This was copied directly from the old BBCode parser...
	 * this will probably be completely changed in the future
	 */
	private function processSmilies($post)
	{
		if(!isset($_SESSION['osimo']['options']['smileySet']))
		{
			$query = "SELECT value FROM config WHERE name='current-smilies' LIMIT 1";
			$result = mysql_query($query);
			if($result)
			{
				$_SESSION['osimo']['options']['smileySet'] = reset(mysql_fetch_row($result));
			}
		}
		
		/* If smiley BBCode isn't cached, then do so */
		if(!is_array($_SESSION['osimo']['smilies']))
		{
			$query = "SELECT code,image FROM smilies WHERE smileySet='".$_SESSION['osimo']['options']['smileySet']."'";
			$result = mysql_query($query);
			
			if($result)
			{
				$i=0;
				while(list($code,$image)=mysql_fetch_row($result))
				{
					$_SESSION['osimo']['smilies']['name'][$i] = $image;
					$_SESSION['osimo']['smilies']['code'][$i] = $code;
					$i++;
				}
			}
		}
		
		if(is_array($_SESSION['osimo']['smilies']))
		{
			$j=0;
			foreach($_SESSION['osimo']['smilies']['name'] as $smiley)
			{
				$replace[$j] = "<img src=\"".OSIMOPATH."os-content/smilies/".$_SESSION['osimo']['options']['smileySet']."/$smiley\" />";
				$j++;
			}
			
			$post = str_replace($_SESSION['osimo']['smilies']['code'],$replace,$post);
		}
		
		return $post;
	}
}
?>