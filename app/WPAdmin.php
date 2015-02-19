<?php
	class WPAdmin{

		private $config;
		private $mysqli;

		public function __construct(){
			$this->config = Config::$configurations;
			$this->mysqli = new mysqli(
				Config::$configurations['DATABASE']['HOST'],
				Config::$configurations['DATABASE']['USER'],
				Config::$configurations['DATABASE']['PASS'],
				Config::$configurations['DATABASE']['BASE']
			);
			if (mysqli_connect_errno()) trigger_error(mysqli_connect_error());
			$this->mysqli->query("SET NAMES 'utf8'");
			$this->mysqli->query('SET character_set_connection=utf8');
			$this->mysqli->query('SET character_set_client=utf8');
			$this->mysqli->query('SET character_set_results=utf8');
		}
		
		public function _get_posts($start=0, $end=3, $limit_str=100){
			$config    = $this->config;
			$sql       = "select * from wp_posts where post_type = 'post' and post_status = 'publish' order by post_date desc limit " . $start ." , ". $end;
			$query     = $this->mysqli->query($sql);
			$i         = 0;
			while ($data = $query->fetch_array()){
				$posts[$i]['abstract'] = $this->_fomat_abstract($data['post_content'], $limit_str);
				$posts[$i]['title']    = $data['post_title'];
				$posts[$i]['date']     = $this->_format_date($data['post_date']);
				$posts[$i]['picture']  = $this->_get_picture($data['ID']);
				$posts[$i]['perma']    = $this->_get_perma_link($data['post_name']);
				$posts[$i]['name']     = $data['post_name'];
				$i++;
			}
			return $posts;
		}

		public function _get_perma_link($post_name){
			return $this->config['DATABASE']['HOME'] . '/' . $post_name;
		}

		public function _get_picture($post_id){
			$sql = "SELECT attach.guid AS src_img FROM wp_posts post INNER JOIN wp_posts attach ON post.ID = attach.post_parent WHERE post.post_type = 'post' AND post.post_status = 'publish' AND attach.post_type = 'attachment' and post.ID = ".$post_id;
			$query = $this->mysqli->query($sql);
			$data = $query->fetch_array();
			$content = $data['src_img'];
			return $content;
		}

		public function _fomat_abstract($str, $limit_str){
			$nstr = strip_tags($str);
			$nstr = substr($nstr, 0, strrpos(substr($nstr, 0, $limit_str), ' ')) . '...';
			return $nstr;
		}

		public function _print_blog($page){
			$config = $this->config;
			$items_by_page = $config['BLOG']['ITEMS_BY_PAGE'];
			$limit_str = $config['BLOG']['LIMIT_ABSTRACT'];
			$page  = $page-1;
			$start = $page*$items_by_page;
			$end   = $items_by_page;
			$sql_count = "select count(*) as total from wp_posts where post_type = 'post' and post_status = 'publish'";
			$query_count = $this->mysqli->query($sql_count);
			$data_count = $query_count->fetch_array();
			$sql = "select * from wp_posts where post_type = 'post' and post_status = 'publish' order by post_date desc limit " . $start ." , ". $end;
			$query = $this->mysqli->query($sql);
			$i = 1;
			while ($data = $query->fetch_array()) {
				$abstract = strip_tags($data['post_content']);
				$abstract = substr($abstract, 0, strrpos(substr($abstract, 0, $limit_str), ' ')) . '...';
				$return .= "<a href=\"./oblog/" . $data['post_name'] . "\">";
					$return .= "<div class=\"col-xs-12 col-sm-12 col-md-6 col-lg-6\">";
						$return .= "<div class=\"blog-box\">";
							$return .= "<h2>" . $data['post_title'] . "</h2>";
						    $return .= "<div class=\"blog-date\">Publicado em: " . $this->_format_date($data['post_date']) . "</div>";
						    $return .= "<div class=\"blog-cathegory\">". $this->_get_blog_categories($data['ID']) ."</div>";
						    $return .= "<div class=\"blog-tag\">". $this->_get_blog_tags($data['ID']) ."</div>";
					    	$return .= "<div class=\"blog-abstract\">" . $abstract . "</div>";
				    	$return .= "</div>";
				    $return .= "</div>";
				$return .= "</a>";
			}
			echo $return;
		}

		public function _get_blog_inside($item){
			$sql = "select * from wp_posts where post_type = 'post' and post_status = 'publish' and post_name = '". $item . "'";
			$query = $this->mysqli->query($sql);
			$data = $query->fetch_array();
			$content = $data['post_content'];
			$content = str_replace(']]>', ']]&gt;', $content);
			$content = str_replace("\r", "<br />", $content);
			$data['post_content'] = $content;
			return $data;
		}

		public function _format_date($date){
			$explode    = explode(' ', $date);
			$date_aux   = $explode[0];
			$time_aux   = $explode[1];
			$explode2   = explode('-', $date_aux);
			$date_year  = $explode2[0];
			$date_month = $explode2[1];
			$date_day   = $explode2[2];
			return $date_day . "/" . $date_month . "/" . $date_year . " - " . $time_aux;
		}

		public function _get_blog_categories($post_id){
			if(empty($post_id)){ return; }
			$sql = "SELECT wp_terms.name FROM wp_terms INNER JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id INNER JOIN wp_term_relationships wpr ON wpr.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id INNER JOIN wp_posts p ON p.ID = wpr.object_id WHERE taxonomy = 'category' AND p.post_type = 'post' and wpr.object_id = ". $post_id;
			$query = $this->mysqli->query($sql);
			if($query->num_rows == 0){
				return "<ul><li>Sem categoria</li></ul>";
			}
			$return  = "";
			$return .= "<ul>";
			$return .= "<h2>Categoria(s): </h2>";
			$i = 0;
			while ($data = $query->fetch_array()) {
				if($i + 1 == $query->num_rows){
					$sep = "";
				}
				else{ 
					$sep = ",";
				}
				$i++;
				$return .= "<li>".$data['name'].$sep."</li>";
			}
			$return .= "</ul>";
			return $return;
		}

		public function _get_blog_tags($post_id){
			if(empty($post_id)){ return; }
			$sql = "SELECT wp_terms.name FROM wp_terms INNER JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id INNER JOIN wp_term_relationships wpr ON wpr.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id INNER JOIN wp_posts p ON p.ID = wpr.object_id WHERE taxonomy = 'post_tag' AND p.post_type = 'post' and wpr.object_id = ". $post_id;
			$query = $this->mysqli->query($sql);
			if($query->num_rows == 0){
				return "<ul><li>Sem Tags</li></ul>";
			}
			$return  = "";
			$return .= "<ul>";
			$return .= "<h2>Tag(s): </h2>";
			$i = 0;
			while ($data = $query->fetch_array()) {
				if($i + 1 == $query->num_rows){
					$sep = "";
				}
				else{ 
					$sep = ",";
				}
				$i++;
				$return .= "<li>".$data['name'].$sep."</li>";
			}
			$return .= "</ul>";
			return $return;
		}
	}
	$wp_admin = new WPAdmin();
?>