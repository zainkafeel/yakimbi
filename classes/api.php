<?php

/* 

Yakimbi Test

*/

class api{
	private $usrid = '';
	/*
		construct;
	*/
	public function __construct() {
		global $usrid;
        $this->usrid = $usrid;
    }
	/*
	   Get Content from Fliker Api
	*/
	# uses libcurl to return the response body of a GET request on $url
    public function getResource($url){
          $chandle = curl_init();
          curl_setopt($chandle, CURLOPT_URL, $url);
          curl_setopt($chandle, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($chandle);
          curl_close($chandle);
          return $result;
    }
    /* 
		Do Search
	*/    
     public function do_search($tag) {
			global $api_key, $per_page;
	        $tag = urlencode($tag);
		    $url = "http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key={$api_key}&tags={$tag}&per_page={$per_page}";
        
          $feed = self::getResource($url);
          $xml = simplexml_load_string($feed);
          $display_html = "<h1>Total number of photos for <strong>{$tag}</strong>: <strong>{$xml->photos['total']}</strong> showing <strong>{$per_page}</strong></h1>";
        
        # http://www.flickr.com/services/api/misc.urls.html
        # http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}.jpg
        foreach ($xml->photos->photo as $photo) {
          $title = $photo['title'];
          $farmid = $photo['farm'];
          $serverid = $photo['server'];
          $id = $photo['id'];
          $secret = $photo['secret'];
          $owner = $photo['owner'];  
          $thumb_url = "http://farm{$farmid}.static.flickr.com/{$serverid}/{$id}_{$secret}_s.jpg";
          $page_url = "http://www.flickr.com/photos/{$owner}/{$id}";
          $uniq_id = hexdec(uniqid());
          $image_html= '<div class="images">
                            <div class="image_display">
                                <img alt="'.$title.'" src="'.$thumb_url.'" id="'.$uniq_id.'_img"/>
                            </div>
                        <a href="javascript:addtofav(\''.$thumb_url.'\',\''.$_GET['tag'].'\',\''.$uniq_id.'\',\''.$farmid.'\',\''.$serverid.'\',\''.$id.'\',\''.$secret.'\');">Add to Favourite</a>
                        <div id="'.$uniq_id.'"></div>
                        </div>
                        ';
          $display_html = $display_html.$image_html;
        }
		return $display_html;
        } # do_search
	
	/*
		fav_list function
	
	*/	
	public function fav_list($html = false){
		global $db;
		$usr = $this->usrid;
		$result = $db->query("SELECT * FROM `fav_images` where uid = '$usr' order by id DESC") or $db->raise_error();
		$image_html = '';
		$image_html .= '<h1>Total <strong><span id="total">'.mysql_num_rows($result).'</span></strong> Favourite</h1>';
		while($row = $db->fetch_array($result))
 		{
			$uniq_id = hexdec(uniqid());
			$image_html.='<div class="fav_images '.$uniq_id.'_outer">
							<div class="image_display">
								<a class="ajax" href="api.php?action=fav_view&id='.$row['id'].'"><img src="'.$row['image_url'].'"/></a>
							</div>
							<h2>tag: '.$row['tag'].'</h2>
							<a href="javascript:removefav(\''.$uniq_id.'_outer\',\''.$row['id'].'\');">remove from Favourite</a>
						</div>';
		  }
		   
		  if(!$html){
			$return_arr["process"] = 'sucess';
			$return_arr["display"] = $image_html;
			return json_encode($return_arr);
		 }else{
		 	return $image_html;
		 }
	}
	/*
	
	add2fav function
	
	*/
	public function add2fav(){
		global $db;
		
		$usr = $this->usrid;
				
		$return_arr = array();
		
		$return_arr["usr"] = $usr;
		
		$return_arr["url"] = $_POST['url'];
		
		$return_arr["farmid"] = $_POST['farmid'];
		
		$return_arr["serverid"] = $_POST['serverid'];
		
		$return_arr["imgid"] = $_POST['imgid'];
		
		$return_arr["secret"] = $_POST['secret'];
		
		$return_arr["tag"] = $_POST['tag'];
				
		$result = $db->query("SELECT * FROM `fav_images` where uid = '$usr' and `img_id` = '".$return_arr["imgid"]."'") or $db->raise_error();		
		
		if($db->num_rows($result) == 0){
		
		$db->query("INSERT INTO  `fav_images` (
		`id` ,		`uid` ,	`image_url` ,		`farm_id` ,		`server_id` , `img_id` , `secret` , `tag` , `createTime`
		)
		VALUES (
		NULL ,  '$usr',  '".$return_arr["url"]."', '".$return_arr["farmid"]."', '".$return_arr["serverid"]."', '".$return_arr["imgid"]."', '".$return_arr["secret"]."', '".$return_arr["tag"]."', CURRENT_TIMESTAMP
		);")  or $db->raise_error();
			
			$return_arr["process"] = 'sucess';
			$return_arr["display"] = self::fav_list(true);
				
		}else{
			$return_arr["process"] = 'dublicate';
		}
		return json_encode($return_arr);
	}
	
	/*
		fav_view
	*/
	
	public function fav_view(){
		global $db;		
		$usr = $this->usrid;
			
		$result = $db->query("SELECT * FROM `fav_images` where id = '".$_GET['id']."'") or $db->raise_error();
		$image_html = '';
		$image_html .= '<div class="fav-view">';
		
		  while($row =  $db->fetch_array($result))
		  {
			$uniq_id = hexdec(uniqid());
			$thumb_url = "http://farm".$row['farm_id'].".static.flickr.com/".$row['server_id']."/".$row['img_id']."_".$row['secret']."_z.jpg";
			$image_html.='<img src="'.$thumb_url.'" onload="javascript:colorbox_resize();" />';
			$id = $row['id'];
		  }
		  //Count For fav description
		  $result2 = $db->query("SELECT * FROM `fav_images_comments` where `fav_id` = '".$_GET['id']."'") or $db->raise_error();
		  if($db->num_rows($result2) > 0){
			while($row = $db->fetch_array($result2))
				{
				$desc = $row['comments'];
				$desc .= '<div id="removedesc"><a href="javascript:remove_desc(\''.$uniq_id.'\');">Remove Description</a></div>';
			  }
			}else{
				$desc = '<a href="javascript:add_desc(\''.$uniq_id.'\');">Add Description</a>';
			}    	
			$image_html .= '<div class="add_desc" id="'.$uniq_id.'" rel="'.$id.'">'.$desc.'</div></div>';
			return $image_html;
	}
	
	/*
	
	removedesc
	
	*/
	public function removedesc(){
		global $db;		
		$usr = $this->usrid;
		
		$return_arr = array();
		$return_arr["usr"] = $usr;
		$return_arr["id"] = $_POST['favid'];
		//Delete Query
		$db->query("Delete FROM `fav_images_comments` where `fav_id` = '".$return_arr["id"]."';")  or $db->raise_error();
		$return_arr["process"] = 'sucess';
		return json_encode($return_arr);
	}
	/*
	
	Remove Fav
	
	*/
	public function remove_fav(){
		global $db;		
		$usr = $this->usrid;
		
		$return_arr = array();

		$return_arr["usr"] = $usr;
		
		$return_arr["id"] = $_POST['id'];
		
		$db->query("DELETE fav_images, fav_images_comments
		FROM fav_images Left JOIN fav_images_comments ON fav_images.id = fav_images_comments.fav_id where fav_images.id = '".$return_arr["id"]."'");
		//mysql_query("Delete FROM `fav_images_comments` where `fav_id` = '".$return_arr["id"]."'");
		
		$result = $db->query("Select * FROM `fav_images` where uid = '".$return_arr["usr"]."';");
		$return_arr["count"] = $db->num_rows($result);		
		return json_encode($return_arr);
	}
	/*
	
	@save description
	
	*/
	public function savedesc(){
		global $db;		
		$usr = $this->usrid;
		
		$return_arr = array();

		$return_arr["usr"] = $usr;
		
		$return_arr["favid"] = $_POST['favid'];
		$return_arr["desc"] = $_POST['desc'];

		$db->query("INSERT INTO  `fav_images_comments` (`id` ,`fav_id` ,`comments` ,`createTime`)
		VALUES (
		NULL ,  '".$return_arr["favid"]."',  '".addslashes($return_arr["desc"])."', 
		CURRENT_TIMESTAMP
		);");	
		$return_arr["process"] = 'sucess';
		
		return json_encode($return_arr["process"]);
	}
	
}