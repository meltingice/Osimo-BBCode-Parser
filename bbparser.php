<?
class OsimoBBParser{
	private $isOsimo;
	private $simple_bbcodes;
	private $fancy_bbcodes;
	
	function OsimoBBParser(){
		$this->isOsimo = true;
		
		$this->loadDefaults();
	}
	
	private function loadDefaults(){
		$simple_defaults = array(
			"b"=>array(
				"search"=>"/\[b\](.+)\[\/b\]/i",
				"replace"=>'<strong>$1</strong>'
			),
			"i"=>array(
				"search"=>"/\[i\](.+)\[\/i\]/i",
				"replace"=>'<i>$1</i>'
			),
			"u"=>array(
				"search"=>"/\[u\](.+)\[\/u\]/i",
				"replace"=>'<u>$1</u>'
			),
			"s"=>array(
				'search'=>"/\[s\](.+)\[\/s\]/i",
				'replace'=>'<span style="text-decoration:line-through">$1</span>'
			),
			"url"=>array(
				"search"=>"/\[url\](.+)\[\/url\]/i",
				"replace"=>'<a href="$1">$1</a>'
			),
			"img"=>array(
				"search"=>"/\[img\](.+)\[\/img\]/i",
				"replace"=>'<img src="$1" />'
			),
			"quote"=>array(
				"search"=>"/\[quote\](.+)\[\/quote\]/i",
				"replace"=>'<blockquote><span class="blockquote-title">Quote:</span><br/>$1</blockquote>'
			),
			'code'=>array(
				'search'=>"/\[code\](.+)\[\/code\]/i",
				'replace'=>'<pre class="code"><code class="plain">$1</code></pre>'
			),
			'right'=>array(
				'search'=>"/\[right\](.+)\[\/right\]/i",
				'replace'=>'<div style="text-align:right">$1</div>'
			),
			'center'=>array(
				'search'=>"/\[center\](.+)\[\/center\]/i",
				'replace'=>'<div style="text-align:center">$1</div>'
			),
			'left'=>array(
				'search'=>"/\[left\](.+)\[\/left\]/i",
				'replace'=>'<div style="text-align:left">$1</div>'
			),
			'list'=>array(
				'search'=>"/\[list\](.+)\[\/list\]/is",
				'replace'=>'<ul>$1</ul>'
			),
			'[*]'=>array(
				'search'=>"/\[\*\]([^\n|\r]+)/i",
				'replace'=>'<li>$1</li>'
			)
		);
		
		$fancy_defaults = array(
			'email'=>array(
				'search'=>"/\[email=([^\]]+)\](.+)\[\/email\]/i",
				'replace'=>'<a href="$1">$2</a>'
			),
			"url"=>array(
				"search"=>"/\[url=([^\]]+)\](.+)\[\/url\]/i",
				"replace"=>'<a href="$1">$2</a>'
			),
			'size'=>array(
				'search'=>"/\[size=([0-9]+)\](.+)\[\/size\]/i",
				'replace'=>'<span style="font-size:$1px">$2</span>'
			),
			'font'=>array(
				'search'=>"/\[font=([^\]]+)\](.+)\[\/font\]/i",
				'replace'=>'<span style="font-family:$1">$2</span>'
			),
			'color'=>array(
				'search'=>"/\[color=([^\]]+)\](.+)\[\/color\]/i",
				'replace'=>'<span style="color:$1">$2</span>'
			),
			'quote'=>array(
				'search'=>"/\[quote=([^\]]+)\](.+)\[\/quote\]/i",
				'replace'=>'<blockquote><span class="blockquote-title">Quote by $1:</span><br/>$2</blockquote>'
			),
			'align'=>array(
				'search'=>"/\[align=(left|right|center)\](.+)\[\/align\]/i",
				'replace'=>'<div style="text-align: $1">$2</div>'
			)
		);
		
		$this->addSimple($simple_defaults);
		$this->addFancy($fancy_defaults);
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
				"search"=>"/\[$tag\](.+)\[\/$tag\]/gi",
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
		
		/* nocode tag check - must do this first! */
		$content =  preg_replace_callback(
			'/\[nocode\](.+)\[\/nocode\]/i',
			create_function(
				'$matches',
				'return str_replace(array("[","]"),array("&#91;","&#93;"),$matches[1]);'
			),
			$content
		);
		
		return nl2br(preg_replace($search,$replace,$content,-1,$count));
	}
}
?>