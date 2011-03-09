<?php 

class GoogleSearch extends Main{
	
	public function __construct(){
		
	}
	
	public function searchWeb($keyword, $page){
		return $this->callApi($keyword, "web", $page);
	}
	
	public function searchImages($keyword, $page){
		return $this->callApi($keyword, "images", $page);
	}
	
	public function searchBooks($keyword, $page){
		return $this->callApi($keyword, "books", $page);
	}
	
	public function searchVideos($keyword, $page){
		return $this->callApi($keyword, "video", $page);
	}
	
	public function searchBlogs($keyword, $page){
		return $this->callApi($keyword, "blogs", $page);
	}
	
	public function searchNews($keyword, $page){
		return $this->callApi($keyword, "news", $page);
	}
	
	
	
	
	private function callApi($keyword, $type, $page){
		
		$keyword=urlencode($keyword);
	
		 $url = "http://ajax.googleapis.com/ajax/services/search/$type?v=1.0&rsz=large&start=$page&q=$keyword";
		 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, "http://www.mysite.com/index.html");
		$body = curl_exec($ch);
		curl_close($ch);
		return json_decode($body);
	}
}